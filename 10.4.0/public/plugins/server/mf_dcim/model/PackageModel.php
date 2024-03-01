<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 灵活机型模型(暂时保留这个Model,过几个版本后废弃,20240208)
 * @use server\mf_dcim\model\PackageModel
 */
class PackageModel extends Model{

	protected $name = 'module_mf_dcim_package';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
        'group_id'      => 'int',
        'cpu_option_id' => 'int',
        'cpu_num'       => 'int',
        'mem_option_id' => 'int',
        'mem_num'       => 'int',
        'disk_option_id'=> 'int',
        'disk_num'      => 'int',
        'bw'            => 'int',
        'ip_num'        => 'int',
        'description'   => 'string',
        'mem_max'       => 'int',
        'mem_max_num'   => 'int',
        'disk_max_num'  => 'int',
        'order'         => 'int',
        'hidden'        => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2023-11-09
     * @title 创建机型规格
     * @desc  创建机型规格
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 型号名称 require
     * @param   int group_id - 分组ID require
     * @param   int cpu_option_id - 处理器配置ID require
     * @param   int cpu_num - 处理器数量 require
     * @param   int mem_option_id - 内存配置ID require
     * @param   int mem_num - 内存数量 require
     * @param   int disk_option_id - 磁盘配置ID require
     * @param   int disk_num - 磁盘数量 require
     * @param   int bw - 带宽 require
     * @param   int ip_num - IP数量 require
     * @param   string description - 简单描述
     * @param   array optional_memory_id - 可选配内存ID
     * @param   int mem_max 0 最高容量
     * @param   int mem_max_num 0 最大槽位
     * @param   array optional_disk_id - 可选配硬盘ID
     * @param   int disk_max_num 0 硬盘最大数量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 灵活机型ID
     */
    public function packageCreate($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $cpu = OptionModel::field('id,value,rel_type')->find($param['cpu_option_id']);
        if(empty($cpu) || $cpu['rel_type'] != OptionModel::CPU){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cpu_option_not_found')];
        }
        $memory = OptionModel::field('id,value,rel_type')->find($param['mem_option_id']);
        if(empty($memory) || $memory['rel_type'] != OptionModel::MEMORY){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_option_not_found')];
        }
        $disk = OptionModel::field('id,value,rel_type')->find($param['disk_option_id']);
        if(empty($disk) || $disk['rel_type'] != OptionModel::DISK){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_option_not_found')];
        }
        $optionalMemory = [];
        $optionDisk = [];
        if(!empty($param['optional_memory_id'])){
            $optionalMemory = OptionModel::whereIn('id', $param['optional_memory_id'])
                            ->where('rel_type', OptionModel::MEMORY)
                            ->column('value');
            if(count($optionalMemory) != count($param['optional_memory_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
            }
        }
        if(!empty($param['optional_memory_id'])){
            $optionDisk = OptionModel::whereIn('id', $param['optional_disk_id'])
                            ->where('rel_type', OptionModel::DISK)
                            ->column('value');
            if(count($optionDisk) != count($param['optional_disk_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
            }
        }

        $productId = $ProductModel->id;
        $param['create_time'] = time();
        $param['order'] = $param['order'] ?? 0;
        
        $duration = DurationModel::where('product_id', $productId)->column('id');

        $this->startTrans();
        try{
            $package = $this->create($param, ['product_id','name','group_id','cpu_option_id','cpu_num','mem_option_id','mem_num','disk_option_id','disk_num','bw','ip_num','description','mem_max','mem_max_num','disk_max_num','create_time','order']);

            $packageOptionLink = [];
            if(!empty($param['optional_memory_id'])){
                foreach($param['optional_memory_id'] as $v){
                    $packageOptionLink[] = [
                        'package_id'        => $package->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::MEMORY,
                    ];
                }
            }
            if(!empty($param['optional_disk_id'])){
                foreach($param['optional_disk_id'] as $v){
                    $packageOptionLink[] = [
                        'package_id'        => $package->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::DISK,
                    ];
                }
            }
            if(!empty($packageOptionLink)){
                $PackageOptionLinkModel = new PackageOptionLinkModel();
                $PackageOptionLinkModel->insertAll($packageOptionLink);
            }

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => PriceModel::TYPE_PACKAGE,
                        'rel_id'        => $package->id,
                        'duration_id'   => $v,
                        'price'         => $param['price'][$v],
                    ];
                }
            }
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $description = lang_plugins('mf_dcim_log_create_package_success', [
            '{product}' => 'product#'.$productId.'#'.$ProductModel->name.'#',
            '{name}'    => $param['name'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$package->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-11-09
     * @title 灵活机型列表
     * @desc  灵活机型列表
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @return  int list[].id - 灵活机型ID
     * @return  string list[].name - 型号名称
     * @return  string list[].description - 简单描述
     * @return  string list[].cpu - 处理器
     * @return  string list[].memory - 内存
     * @return  string list[].disk - 硬盘
     * @return  int list[].bw - 带宽
     * @return  int list[].ip_num - IP数量
     * @return  int count - 总条数
     */
    public function packageList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id'])){
            $param['orderby'] = 'pkg.id';
        }
        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['pkg.product_id', '=', $param['product_id']];
        }

        $list = [];
        $count = $this
                ->alias('pkg')
                ->where($where)
                ->count();
        if($count == 0){
            return ['list'=>$list, 'count'=>$count ];
        }

        $list = $this
                ->alias('pkg')
                ->field('pkg.id,pkg.name,pkg.description,o1.value cpu,o2.value memory,o3.value disk,pkg.bw,pkg.ip_num,pkg.order,pkg.hidden')
                ->leftJoin('module_mf_dcim_option o1', 'pkg.cpu_option_id=o1.id')
                ->leftJoin('module_mf_dcim_option o2', 'pkg.mem_option_id=o2.id')
                ->leftJoin('module_mf_dcim_option o3', 'pkg.disk_option_id=o3.id')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
            
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-11-09
     * @title 灵活机型详情
     * @desc 灵活机型详情
     * @author hh
     * @version v1
     * @param   int id - 灵活机型ID require
     * @return  int id - 灵活机型ID
     * @return  int product_id - 商品ID
     * @return  int group_id - 分组ID
     * @return  int cpu_option_id - 处理器配置ID
     * @return  int cpu_num - 处理器数量
     * @return  int mem_option_id - 内存配置ID
     * @return  int mem_num - 内存数量
     * @return  int disk_option_id - 硬盘配置ID
     * @return  int disk_num - 硬盘数量
     * @return  int bw - 带宽
     * @return  int ip_num - IP数量
     * @return  string description - 简单描述
     * @return  int mem_max - 最高容量
     * @return  int mem_max_num - 最大槽位
     * @return  int disk_max_num - 最大数量
     * @return  int create_time - 创建时间
     * @return  int update_time - 修改时间
     * @return  array optional_memory_id - 可选配内存ID
     * @return  array optional_disk_id - 可选配硬盘ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 周期价格
     */
    public function packageIndex($param){
        $package = $this->find($param['id']);
        if(empty($package)){
            return (object)[];
        }

        $package = $package->toArray();
        $package['optional_memory_id'] = PackageOptionLinkModel::where('package_id', $param['id'])->where('option_rel_type', OptionModel::MEMORY)->column('option_id');
        $package['optional_disk_id'] = PackageOptionLinkModel::where('package_id', $param['id'])->where('option_rel_type', OptionModel::DISK)->column('option_id');

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_dcim_price p', 'p.rel_type="'.PriceModel::TYPE_PACKAGE.'" AND p.rel_id='.$param['id'].' AND d.id=p.duration_id')
                    ->where('d.product_id', $package['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();
        $package['duration'] = $duration;

        return $package;
    }

    /**
     * 时间 2023-11-09
     * @title 修改机型规格
     * @desc  修改机型规格
     * @author hh
     * @version v1
     * @param   int id - 灵活机型ID require
     * @param   string name - 型号名称 require
     * @param   int group_id - 分组ID require
     * @param   int cpu_option_id - 处理器配置ID require
     * @param   int cpu_num - 处理器数量 require
     * @param   int mem_option_id - 内存配置ID require
     * @param   int mem_num - 内存数量 require
     * @param   int disk_option_id - 磁盘配置ID require
     * @param   int disk_num - 磁盘数量 require
     * @param   int bw - 带宽 require
     * @param   int ip_num - IP数量 require
     * @param   string description - 简单描述
     * @param   array optional_memory_id - 可选配内存ID
     * @param   int mem_max 0 最高容量
     * @param   int mem_max_num 0 最大槽位
     * @param   array optional_disk_id - 可选配硬盘ID
     * @param   int disk_max_num 0 硬盘最大数量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 灵活机型ID
     */
    public function packageUpdate($param){
        $package = $this->find($param['id']);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
        }
        $cpu = OptionModel::field('id,value,rel_type')->find($param['cpu_option_id']);
        if(empty($cpu) || $cpu['rel_type'] != OptionModel::CPU){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cpu_option_not_found')];
        }
        $memory = OptionModel::field('id,value,rel_type')->find($param['mem_option_id']);
        if(empty($memory) || $memory['rel_type'] != OptionModel::MEMORY){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_option_not_found')];
        }
        $disk = OptionModel::field('id,value,rel_type')->find($param['disk_option_id']);
        if(empty($disk) || $disk['rel_type'] != OptionModel::DISK){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_option_not_found')];
        }
        $optionalMemory = [];
        $optionDisk = [];
        if(!empty($param['optional_memory_id'])){
            $optionalMemory = OptionModel::whereIn('id', $param['optional_memory_id'])
                            ->where('rel_type', OptionModel::MEMORY)
                            ->column('value');
            if(count($optionalMemory) != count($param['optional_memory_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
            }
        }
        if(!empty($param['optional_memory_id'])){
            $optionDisk = OptionModel::whereIn('id', $param['optional_disk_id'])
                            ->where('rel_type', OptionModel::DISK)
                            ->column('value');
            if(count($optionDisk) != count($param['optional_disk_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
            }
        }

        $productId = $package['product_id'];
        $param['update_time'] = time();
        $param['order'] = $param['order'] ?? 0;
        
        $duration = DurationModel::where('product_id', $productId)->select();

        $oldPrice = PriceModel::field('duration_id,price')->where('rel_type', PriceModel::TYPE_PACKAGE)->where('rel_id', $package->id)->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');

        $oldOptionalMemory = PackageOptionLinkModel::alias('pol')
                            ->join('module_mf_dcim_option o', 'pol.option_id=o.id')
                            ->where('pol.package_id', $package->id)
                            ->where('pol.option_rel_type', OptionModel::MEMORY)
                            ->column('o.value');
        
        $oldOptionalDisk = PackageOptionLinkModel::alias('pol')
                            ->join('module_mf_dcim_option o', 'pol.option_id=o.id')
                            ->where('pol.package_id', $package->id)
                            ->where('pol.option_rel_type', OptionModel::DISK)
                            ->column('o.value');

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], ['name','group_id','cpu_option_id','cpu_num','mem_option_id','mem_num','disk_option_id','disk_num','bw','ip_num','description','mem_max','mem_max_num','disk_max_num','update_time','order']);

            $packageOptionLink = [];
            if(!empty($param['optional_memory_id'])){
                foreach($param['optional_memory_id'] as $v){
                    $packageOptionLink[] = [
                        'package_id'        => $package->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::MEMORY,
                    ];
                }
            }
            if(!empty($param['optional_disk_id'])){
                foreach($param['optional_disk_id'] as $v){
                    $packageOptionLink[] = [
                        'package_id'        => $package->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::DISK,
                    ];
                }
            }
            PackageOptionLinkModel::where('package_id', $package->id)->delete();
            if(!empty($packageOptionLink)){
                $PackageOptionLinkModel = new PackageOptionLinkModel();
                $PackageOptionLinkModel->insertAll($packageOptionLink);
            }

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v['id']])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => PriceModel::TYPE_PACKAGE,
                        'rel_id'        => $package->id,
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where('rel_type', PriceModel::TYPE_PACKAGE)->where('rel_id', $package->id)->delete();
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $des = [
            'name'              => lang_plugins('mf_dcim_package_name'),
            'group_id'          => lang_plugins('mf_dcim_group_id'),
            'cpu'               => lang_plugins('mf_dcim_package_cpu'),
            'cpu_num'           => lang_plugins('mf_dcim_package_cpu_num'),
            'memory'            => lang_plugins('mf_dcim_package_memory'),
            'mem_num'           => lang_plugins('mf_dcim_package_mem_num'),
            'disk'              => lang_plugins('mf_dcim_package_disk'),
            'disk_num'          => lang_plugins('mf_dcim_package_disk_num'),
            'bw'                => lang_plugins('mf_dcim_package_bw'),
            'ip_num'            => lang_plugins('mf_dcim_option_value_5'),
            'description'       => lang_plugins('mf_dcim_package_description'),
            'optional_memory'   => lang_plugins('mf_dcim_package_optional_memory'),
            'mem_max'           => lang_plugins('mf_dcim_package_mem_max'),
            'mem_max_num'       => lang_plugins('mf_dcim_package_mem_max_num'),
            'optional_disk'     => lang_plugins('mf_dcim_package_optional_disk'),
            'disk_max_num'      => lang_plugins('mf_dcim_package_disk_max_num'),
            'order'             => lang_plugins('mf_dcim_order'),
        ];

        $old = $package->toArray();
        $old['cpu'] = OptionModel::where('id', $old['cpu_option_id'])->value('value');
        $old['memory'] = OptionModel::where('id', $old['mem_option_id'])->value('value');
        $old['disk'] = OptionModel::where('id', $old['disk_option_id'])->value('value');
        $old['optional_memory'] = !empty($oldOptionalMemory) ? implode(',', $oldOptionalMemory) : lang_plugins('mf_dcim_not_optional');
        $old['optional_disk'] = !empty($oldOptionalDisk) ? implode(',', $oldOptionalDisk) : lang_plugins('mf_dcim_not_optional');

        $new = $param;
        $new['cpu'] = $cpu['value'];
        $new['memory'] = $memory['value'];
        $new['disk'] = $disk['value'];
        $new['optional_memory'] = !empty($optionalMemory) ? implode(',', $optionalMemory) : lang_plugins('mf_dcim_not_optional');
        $new['optional_disk'] = !empty($optionDisk) ? implode(',', $optionDisk) : lang_plugins('mf_dcim_not_optional');

        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $new[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $productName = ProductModel::where('id', $productId)->value('name');

            $description = lang_plugins('mf_dcim_log_update_package_success', [
                '{product}' => 'product#'.$productId.'#'.$productName.'#',
                '{detail}'  => $description,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-11-09
     * @title 删除灵活机型
     * @desc  删除灵活机型
     * @author hh
     * @version v1
     * @param   int id - 灵活机型ID require
     */
    public function packageDelete($param){
        $package = $this->find($param['id']);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
        }
        // 当有正在使用的机器的时候不能删除
        $host = HostLinkModel::alias('hl')
                ->join('host h', 'hl.host_id=h.id')
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->where('hl.package_id', $param['id'])
                ->find();
        if(!empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
        }
        $productId = $package['product_id'];
        $product = ProductModel::find($productId);

        $this->where('id', $param['id'])->delete();
        PackageOptionLinkModel::where('package_id', $param['id'])->delete();

        $description = lang_plugins('mf_dcim_log_delete_package_success', [
            '{product}' => 'product#'.$productId.'#'.($product['name'] ?? '').'#',
            '{name}'    => $package['name'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-11-10
     * @title 前台灵活机型详情
     * @desc  前台灵活机型详情
     * @author hh
     * @version v1
     * @param   int $id - 灵活机型ID require
     * @return  int mem_max - 内存最大容量(0=不限制)
     * @return  int mem_max_num - 内存最大数量(0=不限制)
     * @return  int memory_used - 内存已用容量
     * @return  int memory_slot_used - 内存已用数量
     * @return  int optional_memory[].id - 可选内存配置ID
     * @return  string optional_memory[].value - 可选内存
     * @return  int optional_memory[].other_config.memory_slot - 内存占用插槽
     * @return  int optional_memory[].other_config.memory - 内存大小
     * @return  int disk_max_num - 最大硬盘数量(0=不限制)
     * @return  int disk_used - 已用硬盘数量
     * @return  int optional_disk[].id - 可选硬盘配置ID
     * @return  string optional_disk[].value - 可选硬盘
     */
    public function homePackageIndex($id){
        $package = $this->find($id);
        if(empty($package)){
            return (object)[];
        }
        $memory = OptionModel::find($package['mem_option_id']);
        $memory['other_config'] = json_decode($memory['other_config'], true);

        $data = [];
        $data['bw'] = $package['bw'];
        $data['ip_num'] = $package['ip_num'];
        $data['mem_max'] = $package['mem_max'];
        $data['mem_max_num'] = $package['mem_max_num'];
        $data['memory_used'] = $memory['other_config']['memory'] * $package['mem_num'];
        $data['memory_slot_used'] = $memory['other_config']['memory_slot'] * $package['mem_num'];
        $data['optional_memory'] = [];

        $data['disk_max_num'] = $package['disk_max_num'];
        $data['disk_used'] = $package['disk_num'];
        $data['optional_disk'] = [];

        // 还有可选内存
        if(($data['mem_max'] == 0 || $data['mem_max'] > $data['memory_used']) && ($data['mem_max_num'] == 0 || $data['mem_max_num'] > $data['memory_slot_used'])){
            // 获取可选内存
            $optionalMemoryId = PackageOptionLinkModel::where('package_id', $id)->where('option_rel_type', OptionModel::MEMORY)->column('option_id');
            if(!empty($optionalMemoryId)){
                $data['optional_memory'] = OptionModel::field('id,value,other_config')
                                            ->withAttr('value', function($value){
                                                $multiLanguage = hook_one('multi_language', [
                                                    'replace' => [
                                                        'value' => $value,
                                                    ],
                                                ]);
                                                if(isset($multiLanguage['value'])){
                                                    $value = $multiLanguage['value'];
                                                }
                                                return $value;
                                            })
                                            ->withAttr('other_config', function($value){
                                                return json_decode($value, true) ?? (object)[];
                                            })
                                            ->whereIn('id', $optionalMemoryId)
                                            ->order('order,id', 'asc')
                                            ->select()
                                            ->toArray();
            }
        }
        // 可以加硬盘
        if($data['disk_max_num'] == 0 || $data['disk_max_num'] > $data['disk_used']){
            // 获取可选内存
            $optionalDiskId = PackageOptionLinkModel::where('package_id', $id)->where('option_rel_type', OptionModel::DISK)->column('option_id');
            if(!empty($optionalDiskId)){
                $data['optional_disk'] = OptionModel::field('id,value')
                                            ->withAttr('value', function($value){
                                                $multiLanguage = hook_one('multi_language', [
                                                    'replace' => [
                                                        'value' => $value,
                                                    ],
                                                ]);
                                                if(isset($multiLanguage['value'])){
                                                    $value = $multiLanguage['value'];
                                                }
                                                return $value;
                                            })
                                            ->whereIn('id', $optionalDiskId)
                                            ->order('order,id', 'asc')
                                            ->select()
                                            ->toArray();
            }
        }

        return $data;
    }

    public function updateHidden($param){
        $package = $this->find($param['id']);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
        }
        if($package['hidden'] == $param['hidden']){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $this->update(['hidden'=>$param['hidden']], ['id'=>$package['id']]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }





}