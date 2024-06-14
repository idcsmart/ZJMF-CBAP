<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 型号配置模型
 * @use   server\mf_dcim\model\ModelConfigModel
 */
class ModelConfigModel extends Model
{
	protected $name = 'module_mf_dcim_model_config';

    // 设置字段信息
    protected $schema = [
        'id'                        => 'int',
        'product_id'                => 'int',
        'name'                      => 'string',
        'group_id'                  => 'int',
        'cpu'              	        => 'string',
        'cpu_param'                 => 'string',
        'memory'                    => 'string',
        'disk'                      => 'string',
        'order'                     => 'int',
        'hidden'                    => 'int',
        'support_optional'          => 'int',
        'optional_only_for_upgrade' => 'int',
        'leave_memory'              => 'int',
        'max_memory_num'            => 'int',
        'max_disk_num'              => 'int',
        'gpu'                       => 'string',
        'max_gpu_num'               => 'int',
        'create_time'               => 'int',
        'update_time'               => 'int',
    ];

    /**
     * 时间 2023-02-15
     * @title 添加型号配置
     * @desc 添加型号配置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.name - 配置名称 require
     * @param   int param.group_id - 分组ID require
     * @param   string param.cpu - 处理器 require
     * @param   string param.cpu_param - 处理器参数 require
     * @param   string param.memory - 内存 require
     * @param   string param.disk - 硬盘 require
     * @param   string param.gpu - 显卡
     * @param   int param.support_optional - 允许增值选配(0=不允许,1=允许) require
     * @param   int param.optional_only_for_upgrade - 增值仅用于升降级(0=关闭,1=开启) requireIf:support_optional=1
     * @param   array param.optional_memory_id - 可选配内存ID
     * @param   int param.leave_memory - 剩余内存
     * @param   int param.max_memory_num - 可增加内存条数
     * @param   array param.optional_disk_id - 可选配硬盘ID
     * @param   int param.max_disk_num - 可增加硬盘数量
     * @param   array param.optional_gpu_id - 可选配显卡ID
     * @param   int param.max_gpu_num - 可增加显卡数量
     * @param   array param.price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 型号配置ID
     */
    public function modelConfigCreate($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }

        $optionalMemory = [];
        $optionDisk = [];
        $optionGpu = [];
        if($param['support_optional'] == 1){
            if(!empty($param['optional_memory_id'])){
                $optionalMemory = OptionModel::whereIn('id', $param['optional_memory_id'])
                                ->where('rel_type', OptionModel::MEMORY)
                                ->column('value');
                if(count($optionalMemory) != count($param['optional_memory_id'])){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
                }
            }
            if(!empty($param['optional_disk_id'])){
                $optionDisk = OptionModel::whereIn('id', $param['optional_disk_id'])
                                ->where('rel_type', OptionModel::DISK)
                                ->column('value');
                if(count($optionDisk) != count($param['optional_disk_id'])){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
                }
            }
            if(!empty($param['optional_gpu_id'])){
                $optionGpu = OptionModel::whereIn('id', $param['optional_gpu_id'])
                                ->where('rel_type', OptionModel::GPU)
                                ->column('value');
                if(count($optionGpu) != count($param['optional_gpu_id'])){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_gpu_optional_not_found')];
                }
            }
            $param['leave_memory'] = $param['leave_memory'] ?? 0;
            $param['max_memory_num'] = $param['max_memory_num'] ?? 0;
            $param['max_disk_num'] = $param['max_disk_num'] ?? 0;
            $param['max_gpu_num'] = $param['max_gpu_num'] ?? 0;
        }else{
            $param['optional_memory_id'] = [];
            $param['optional_disk_id'] = [];
            $param['optional_gpu_id'] = [];
            $param['leave_memory'] = 0;
            $param['max_memory_num'] = 0;
            $param['max_disk_num'] = 0;
            $param['max_gpu_num'] = 0;
            $param['optional_only_for_upgrade'] = 0;
        }

        $productId = $ProductModel->id;
        $param['create_time'] = time();
       
        // 验证周期价格
        $duration = DurationModel::where('product_id', $productId)->column('id');

        $this->startTrans();
        try{
        	$modelConfig = $this->create($param, ['product_id','name','group_id','cpu','cpu_param','memory','disk','support_optional','optional_only_for_upgrade','leave_memory','max_memory_num','max_disk_num','create_time','gpu','max_gpu_num']);

            $modelConfigOptionLink = [];
            if(!empty($param['optional_memory_id'])){
                foreach($param['optional_memory_id'] as $v){
                    $modelConfigOptionLink[] = [
                        'model_config_id'   => $modelConfig->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::MEMORY,
                    ];
                }
            }
            if(!empty($param['optional_disk_id'])){
                foreach($param['optional_disk_id'] as $v){
                    $modelConfigOptionLink[] = [
                        'model_config_id'   => $modelConfig->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::DISK,
                    ];
                }
            }
            if(!empty($param['optional_gpu_id'])){
                foreach($param['optional_gpu_id'] as $v){
                    $modelConfigOptionLink[] = [
                        'model_config_id'   => $modelConfig->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::GPU,
                    ];
                }
            }
            if(!empty($modelConfigOptionLink)){
                $ModelConfigOptionLinkModel = new ModelConfigOptionLinkModel();
                $ModelConfigOptionLinkModel->insertAll($modelConfigOptionLink);
            }

        	$priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'    	=> 'model_config',
                        'rel_id'     	=> $modelConfig->id,
                        'duration_id'   => $v,
                        'price'         => $param['price'][$v],
                    ];
                }
            }
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }

            // 置顶
            $this->where('product_id', $productId)->where('order', '>=', 0)->inc('order', 1)->update();

            $this->commit();
        }catch(\Exception $e){
        	$this->rollback();
        	return ['status'=>400, 'msg'=>lang_plugins('create_fail')];
        }

        $description = lang_plugins('mf_dcim_log_add_model_config_success', [
            '{name}' => $param['name'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$modelConfig->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-15
     * @title 型号配置列表
     * @desc 型号配置列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   int param.product_id - 商品ID
     * @return  int list[].id - 型号配置ID
     * @return  string list[].name - 配置名称
     * @return  int list[].group_id - 分组ID
     * @return  string list[].cpu - 处理器
     * @return  string list[].cpu_param - 处理器参数
     * @return  string list[].memory - 内存
     * @return  string list[].disk - 硬盘
     * @return  string list[].gpu - 显卡
     * @return  int list[].support_optional - 允许增值选配(0=不允许,1=允许)
     * @return  int list[].hidden - 是否隐藏(0=否,1=是)
     * @return  int count - 总条数
     */
    public function modelConfigList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        
        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }

        $list = $this
                ->field('id,name,group_id,cpu,cpu_param,memory,disk,gpu,support_optional,hidden')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order('order,id', 'asc')
                ->select()
                ->toArray();
    
        $count = $this
                ->where($where)
                ->count();
        
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-15
     * @title 修改型号配置
     * @desc 修改型号配置
     * @author hh
     * @version v1
     * @param   int param.id - 型号配置ID require
     * @param   string param.name - 配置名称 require
     * @param   int param.group_id - 分组ID require
     * @param   string param.cpu - 处理器 require
     * @param   string param.cpu_param - 处理器参数 require
     * @param   string param.memory - 内存 require
     * @param   string param.disk - 硬盘 require
     * @param   string param.gpu - 显卡
     * @param   int param.support_optional - 允许增值选配(0=不允许,1=允许) require
     * @param   int param.optional_only_for_upgrade - 增值仅用于升降级(0=关闭,1=开启) requireIf:support_optional=1
     * @param   array param.optional_memory_id - 可选配内存ID
     * @param   int param.leave_memory - 剩余内存
     * @param   int param.max_memory_num - 可增加内存条数
     * @param   array param.optional_disk_id - 可选配硬盘ID
     * @param   int param.max_disk_num - 可增加硬盘数量
     * @param   array param.optional_gpu_id - 可选配显卡ID
     * @param   int param.max_gpu_num - 可增加显卡数量
     * @param   array param.price - 周期价格(如["5"=>"12"],5是周期ID,12是价格)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function modelConfigUpdate($param)
    {
        $modelConfig = $this->find($param['id']);
        if(empty($modelConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        $productId = $modelConfig['product_id'];

        $optionalMemory = [];
        $optionDisk = [];
        $optionGpu = [];
        
        if(!empty($param['optional_memory_id'])){
            $optionalMemory = OptionModel::whereIn('id', $param['optional_memory_id'])
                            ->where('rel_type', OptionModel::MEMORY)
                            ->column('value');
            if(count($optionalMemory) != count($param['optional_memory_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
            }
        }
        if(!empty($param['optional_disk_id'])){
            $optionDisk = OptionModel::whereIn('id', $param['optional_disk_id'])
                            ->where('rel_type', OptionModel::DISK)
                            ->column('value');
            if(count($optionDisk) != count($param['optional_disk_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
            }
        }
        if(!empty($param['optional_gpu_id'])){
            $optionGpu = OptionModel::whereIn('id', $param['optional_gpu_id'])
                            ->where('rel_type', OptionModel::GPU)
                            ->column('value');
            if(count($optionGpu) != count($param['optional_gpu_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_gpu_optional_not_found')];
            }
        }

        // 验证周期价格
        $duration = DurationModel::field('id,name')->where('product_id', $productId)->select()->toArray();

        $oldPrice = PriceModel::field('duration_id,price')->where('rel_type','model_config')->where('rel_id', $param['id'])->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');

        $oldOptionalMemory = ModelConfigOptionLinkModel::alias('mcol')
                            ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                            ->where('mcol.model_config_id', $modelConfig->id)
                            ->where('mcol.option_rel_type', OptionModel::MEMORY)
                            ->column('o.value');
        
        $oldOptionalDisk = ModelConfigOptionLinkModel::alias('mcol')
                            ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                            ->where('mcol.model_config_id', $modelConfig->id)
                            ->where('mcol.option_rel_type', OptionModel::DISK)
                            ->column('o.value');

        $oldOptionalGpu = ModelConfigOptionLinkModel::alias('mcol')
                            ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                            ->where('mcol.model_config_id', $modelConfig->id)
                            ->where('mcol.option_rel_type', OptionModel::GPU)
                            ->column('o.value');

        $param['update_time'] = time();
        $this->startTrans();
        try{
        	$this->update($param, ['id'=>$modelConfig['id']], ['name','group_id','cpu','cpu_param','memory','disk','support_optional','optional_only_for_upgrade','leave_memory','max_memory_num','max_disk_num','update_time','gpu','max_gpu_num']);

            $modelConfigOptionLink = [];
            if(!empty($param['optional_memory_id'])){
                foreach($param['optional_memory_id'] as $v){
                    $modelConfigOptionLink[] = [
                        'model_config_id'   => $modelConfig->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::MEMORY,
                    ];
                }
            }
            if(!empty($param['optional_disk_id'])){
                foreach($param['optional_disk_id'] as $v){
                    $modelConfigOptionLink[] = [
                        'model_config_id'   => $modelConfig->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::DISK,
                    ];
                }
            }
            if(!empty($param['optional_gpu_id'])){
                foreach($param['optional_gpu_id'] as $v){
                    $modelConfigOptionLink[] = [
                        'model_config_id'   => $modelConfig->id,
                        'option_id'         => $v,
                        'option_rel_type'   => OptionModel::GPU,
                    ];
                }
            }
            ModelConfigOptionLinkModel::where('model_config_id', $modelConfig->id)->delete();
            if(!empty($modelConfigOptionLink)){
                $ModelConfigOptionLinkModel = new ModelConfigOptionLinkModel();
                $ModelConfigOptionLinkModel->insertAll($modelConfigOptionLink);
            }

        	$priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v['id']])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'    	=> 'model_config',
                        'rel_id'     	=> $modelConfig->id,
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where('rel_type', 'model_config')->where('rel_id', $modelConfig->id)->delete();
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }

            $this->commit();
        }catch(\Exception $e){
        	$this->rollback();
        	return ['status'=>400, 'msg'=>lang_plugins('update_fail')];
        }

        $des = [
            'name'                      => lang_plugins('mf_dcim_model_config_name'),
            'group_id'                  => lang_plugins('mf_dcim_model_config_group_id'),
            'cpu'                       => lang_plugins('mf_dcim_model_config_cpu'),
            'cpu_param'                 => lang_plugins('mf_dcim_model_config_cpu_param'),
            'memory'                    => lang_plugins('mf_dcim_model_config_memory'),
            'disk'                      => lang_plugins('mf_dcim_model_config_disk'),
            'support_optional'          => lang_plugins('mf_dcim_support_optional'),
            'optional_only_for_upgrade' => lang_plugins('mf_dcim_optional_only_for_upgrade'),
            'optional_memory'           => lang_plugins('mf_dcim_package_optional_memory'),
            'leave_memory'              => lang_plugins('mf_dcim_leave_memory'),
            'max_memory_num'            => lang_plugins('mf_dcim_max_memory_num'),
            'optional_disk'             => lang_plugins('mf_dcim_package_optional_disk'),
            'max_disk_num'              => lang_plugins('mf_dcim_max_disk_num'),
            'gpu'                       => lang_plugins('mf_dcim_gpu'),
            'max_gpu_num'               => lang_plugins('mf_dcim_max_gpu_num'),
        ];

        $switch = [
            lang_plugins('mf_dcim_switch_off'),
            lang_plugins('mf_dcim_switch_on'),
        ];

        $old = $modelConfig->toArray();
        $old['support_optional'] = $switch[ $old['support_optional'] ];
        $old['optional_only_for_upgrade'] = $switch[ $old['optional_only_for_upgrade'] ];
        $old['optional_memory'] = !empty($oldOptionalMemory) ? implode(',', $oldOptionalMemory) : lang_plugins('mf_dcim_not_optional');
        $old['optional_disk'] = !empty($oldOptionalDisk) ? implode(',', $oldOptionalDisk) : lang_plugins('mf_dcim_not_optional');
        $old['optional_gpu'] = !empty($oldOptionalGpu) ? implode(',', $oldOptionalGpu) : lang_plugins('mf_dcim_not_optional');

        $param['support_optional'] = $switch[ $param['support_optional'] ];
        $param['optional_only_for_upgrade'] = $switch[ $param['optional_only_for_upgrade'] ];
        $param['optional_memory'] = !empty($optionalMemory) ? implode(',', $optionalMemory) : lang_plugins('mf_dcim_not_optional');
        $param['optional_disk'] = !empty($optionDisk) ? implode(',', $optionDisk) : lang_plugins('mf_dcim_not_optional');
        $param['optional_gpu'] = !empty($optionGpu) ? implode(',', $optionGpu) : lang_plugins('mf_dcim_not_optional');

        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('mf_dcim_price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $param[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }
        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_model_config_success', [
                '{id}'      => $param['id'],
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
     * 时间 2023-02-15
     * @title 删除型号配置
     * @desc 删除型号配置
     * @author hh
     * @version v1
     * @param   int id - 型号配置ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function modelConfigDelete($id)
    {
        $modelConfig = $this->find($id);
        if(empty($modelConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        
        $this->startTrans();
        try{
            $this->where('id', $id)->delete();
            PriceModel::where('rel_type', 'model_config')->where('rel_id', $id)->delete();
            ModelConfigOptionLinkModel::where('model_config_id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('mf_dcim_log_delete_model_config_success', [
            '{name}' => $modelConfig['name'],
        ]);
        active_log($description, 'product', $modelConfig['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-20
     * @title 型号配置详情
     * @desc 型号配置详情
     * @author hh
     * @version v1
     * @param   int id - 型号配置ID require
     * @return  int id - 型号配置ID
     * @return  string name - 配置名称
     * @return  int group_id - 销售分组ID
     * @return  string cpu - 处理器
     * @return  string cpu_param - 处理器参数
     * @return  string memory - 内存
     * @return  string disk - 硬盘
     * @return  string gpu - 显卡
     * @return  int support_optional - 允许增值选配(0=不允许,1=允许)
     * @return  int leave_memory - 剩余内存容量
     * @return  int max_memory_num - 可增加内存条数
     * @return  int max_disk_num - 可增加硬盘数量
     * @return  int max_gpu_num - 可增加显卡数量
     * @return  int optional_only_for_upgrade - 增值仅用于升降级(0=关闭,1=开启)
     * @return  array optional_memory_id - 可选配内存配置ID
     * @return  array optional_disk_id - 可选配硬盘配置ID
     * @return  array optional_gpu_id - 可选配显卡配置ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 周期价格
     */
    public function modelConfigIndex($id)
    {
        $modelConfig = $this->field('id,product_id,name,group_id,cpu,cpu_param,memory,disk,gpu,support_optional,leave_memory,max_memory_num,max_disk_num,max_gpu_num,optional_only_for_upgrade')->find($id);
        if(empty($modelConfig)){
            return (object)[];
        }
        $modelConfig = $modelConfig->toArray();
        $modelConfig['optional_memory_id'] = ModelConfigOptionLinkModel::where('model_config_id', $id)->where('option_rel_type', OptionModel::MEMORY)->column('option_id');
        $modelConfig['optional_disk_id'] = ModelConfigOptionLinkModel::where('model_config_id', $id)->where('option_rel_type', OptionModel::DISK)->column('option_id');
        $modelConfig['optional_gpu_id'] = ModelConfigOptionLinkModel::where('model_config_id', $id)->where('option_rel_type', OptionModel::GPU)->column('option_id');

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_dcim_price p', 'p.rel_type="model_config" AND p.rel_id='.$id.' AND d.id=p.duration_id')
                    ->where('d.product_id', $modelConfig['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

        unset($modelConfig['product_id']);

        $modelConfig['duration'] = $duration;
        return $modelConfig;
    }

    /**
     * 时间 2024-02-18
     * @title 切换订购是否显示
     * @desc 切换订购是否显示
     * @author hh
     * @version v1
     * @param   int param.id - 配置型号ID require
     * @param   int param.hidden - 状态(0=显示,1=隐藏) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function updateHidden($param)
    {
        $modelConfig = $this->find($param['id']);
        if(empty($modelConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        if($modelConfig['hidden'] == $param['hidden']){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $this->update(['hidden'=>$param['hidden']], ['id'=>$modelConfig['id']]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-12-20
     * @title 拖动排序
     * @desc 拖动排序
     * @author hh
     * @version v1
     * @param   int param.prev_model_config_id - 前一个机型配置ID(0=表示置顶) require
     * @param   int param.id - 当前机型配置ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function dragToSort($param)
    {
        $modelConfig = $this->find($param['id']);
        if(empty($modelConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        if($param['prev_model_config_id'] == 0){
            $preOrder = -1;
            $order = 0;
        }else{
            $preModelConfig = $this->find($param['prev_model_config_id']);
            if(empty($preModelConfig)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
            }
            $preOrder = $preModelConfig['order'];
            $order = $preModelConfig['order']+1;
        }
        $this->where('product_id', $modelConfig['product_id'])->where('order', '>=', $preOrder)->where('id', '>', $param['prev_model_config_id'])->inc('order', 2)->update();
        $this->where('id', $param['id'])->update(['order'=>$order]);

        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }


}