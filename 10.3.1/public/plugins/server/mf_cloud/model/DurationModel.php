<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 周期模型
 * @use server\mf_cloud\model\DurationModel
 */
class DurationModel extends Model{

    // 计算价格后保存在上面
    public static $configData = [];

	protected $name = 'module_mf_cloud_duration';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
        'num'           => 'int',
        'unit'          => 'string',
        'price_factor'  => 'float',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2023-01-31
     * @title 周期列表
     * @desc 周期列表
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function durationList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','num'])){
            $param['orderby'] = 'id';
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['product_id'])){
                $query->where('product_id', $param['product_id']);
            }
        };

        $duration = $this
                ->field('id,name,num,unit,price_factor')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        $count = $this
                ->where($where)
                ->count();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => $duration,
                'count' => $count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 添加周期
     * @desc 添加周期
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 周期名称 require
     * @param   int num - 周期时长 require
     * @param   string unit - 单位(hour=小时,day=天,month=月) require
     * @param   float price_factor 1 价格系数
     * @return  int id - 添加成功的周期ID
     */
    public function durationCreate($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $param['create_time'] = time();
        $param['price_factor'] = $param['price_factor'] ?? 1;
        if(!is_numeric($param['price_factor'])){
            $param['price_factor'] = 1;
        }

        $duration = $this->create($param, ['product_id','name','num','unit','price_factor','create_time']);

        $description = lang_plugins('log_add_duration_success', ['{name}'=>$param['name'] ]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$duration->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 修改周期
     * @desc 修改周期
     * @author hh
     * @version v1
     * @param   int id - 周期ID require
     * @param   string name - 周期名称 require
     * @param   int num - 周期时长 require
     * @param   string unit - 单位(hour=小时,day=天,month=月) require
     * @param   float price_factor - 价格系数
     */
    public function durationUpdate($param){
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
        }

        if(isset($param['price_factor']) && !is_numeric($param['price_factor'])){
            $param['price_factor'] = 1;
        }

        $this->update($param, ['id'=>$DurationModel->id], ['name','num','unit','price_factor']);

        if($DurationModel['name'] != $param['name']){
            $description = lang_plugins('log_modify_duration_success', ['{name}'=>$DurationModel['name'], 'new_name'=>$param['name'] ]);
            active_log($description, 'product', $DurationModel['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 删除周期
     * @desc 删除周期
     * @author hh
     * @version v1
     * @param   int id - 周期ID require
     */
    public function durationDelete($param){
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
        }

        $this->startTrans();
        try{
            $this->where('id', $param['id'])->delete();

            PriceModel::where('duration_id', $param['id'])->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('log_delete_duration_success', ['{name}'=>$DurationModel['name'] ]);
        active_log($description, 'product', $DurationModel['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-06
     * @title 获取商品配置所有周期价格
     * @desc 获取商品配置所有周期价格
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @param   int cpu - cpu核心数
     * @param   int memory - 内存
     * @param   int image_id 0 镜像ID
     * @param   int system_disk.size - 系统盘大小
     * @param   string system_disk.disk_type - 系统盘类型
     * @param   int data_disk[].size - 数据盘大小
     * @param   string data_disk[].disk_type - 系统盘类型
     * @param   int backup_num 0 备份数量
     * @param   int snap_num 0 备份数量
     * @param   int line_id - 线路ID
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - 附加IP数量
     */
    public function getAllDurationPrice($param, $validate = false){
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [],
        ];

        $ProductModel = ProductModel::find($param['id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        $productId = $ProductModel->id;

        if($ProductModel['pay_type'] == 'onetime'){
            $duration = [
                [
                    'id'            => 0,
                    'name'          => '一次性',
                    'price_factor'  => 1
                ]
            ];
        }else if($ProductModel['pay_type'] == 'recurring_prepayment' || $ProductModel['pay_type'] == 'recurring_postpaid'){
            $duration = $this->field('id,name,num,unit,price_factor')->where('product_id', $productId)->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->select()->toArray();
        }else if($ProductModel['pay_type'] == 'free'){
            $duration = [
                [
                    'id'            => 0,
                    'name'          => '免费',
                    'price'         => '0.00',
                    'price_factor'  => 1
                ]
            ];
            return $result;
        }else{
            return $result;
        }
        $OptionModel = new OptionModel();

        // 价格组成
        $priceComponent = [];
        $priceDetail = [];

        // 获取cpu周期价格
        if(isset($param['cpu']) && !empty($param['cpu'])){
            $optionId = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::CPU)->where('value', $param['cpu'])->value('id');
            if(!empty($optionId)){
                $price = PriceModel::field('duration_id,price')->where('option_id', $optionId)->select()->toArray();

                $priceDetail['cpu'] = array_column($price, 'price', 'duration_id');
                $priceComponent[] = 'cpu';
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
                }
            }
        }
        // 获取内存周期价格
        if(isset($param['memory']) && !empty($param['memory'])){
            $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::MEMORY, 0, $param['memory']);
            if($optionDurationPrice['match']){
                $priceDetail['memory'] = $optionDurationPrice['price'];
                $priceComponent[] = 'memory';
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                }
            }
        }
        // 获取镜像周期价格
        $imagePrice = 0;
        if(isset($param['image_id']) && !empty($param['image_id']) ){
            $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
            // 验证镜像
            if(!empty($image) && $image['charge'] == 1 && !empty($image['price'])){
                $imagePrice = $image['price'];
            }
        }
        // 获取系统盘周期价格
        if(isset($param['system_disk']['size']) && !empty($param['system_disk']['size'])){
            $whereAppend = ['other_config'=>json_encode(['disk_type'=>$param['system_disk']['disk_type'] ?? '']) ];

            $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::SYSTEM_DISK, 0, $param['system_disk']['size'], $whereAppend);
            if($optionDurationPrice['match']){
                $priceDetail['system_disk'] = $optionDurationPrice['price'];
                $priceComponent[] = 'system_disk';
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('system_disk_config_not_found')];
                }
            }
        }
         // 获取数据盘周期价格
        if(isset($param['data_disk']) && !empty($param['data_disk'])){
            foreach($param['data_disk'] as $k=>$v){
                $whereAppend = ['other_config'=>json_encode(['disk_type'=>$v['disk_type'] ?? '']) ];

                $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $whereAppend);
                if($optionDurationPrice['match']){
                    $priceDetail['data_disk_'.$k] = $optionDurationPrice['price'];
                    $priceComponent[] = 'data_disk_'.$k;
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                    }
                }
            }
        }
        $config = ConfigModel::where('product_id', $productId)->find();
        // 备份快照
        $otherPrice = 0;
        if($config['backup_enable'] == 1){
            if(isset($param['backup_num']) && !empty($param['backup_num'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'backup')->where('num', $param['backup_num'])->find();
                if(!empty($BackupConfigModel)){
                    $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('backup_num_error')];
                    }
                }
            }
        }
        if($config['snap_enable'] == 1){
            if(isset($param['snap_num']) && !empty($param['snap_num'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'snap')->where('num', $param['snap_num'])->find();
                if(!empty($BackupConfigModel)){
                    $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('snap_num_error')];
                    }
                }
            }
        }
        // 有线路才能选择防御和附加IP
        if(isset($param['line_id']) && !empty($param['line_id'])){
            $line = LineModel::find($param['line_id']);
            if(!empty($line)){
                if($line['bill_type'] == 'bw'){
                    // 获取带宽周期价格
                    if(isset($param['bw']) && !empty($param['bw'])){
                        $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw']);
                        if($optionDurationPrice['match']){
                            $priceDetail['bw'] = $optionDurationPrice['price'];
                            $priceComponent[] = 'bw';
                        }else{
                            if($validate){
                                return ['status'=>400, 'msg'=>lang_plugins('line_bw_not_found')];
                            }
                        }
                    }
                }else if($line['bill_type'] == 'flow'){
                    // 获取流量周期价格
                    if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0){
                        $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow']);
                        if($optionDurationPrice['match']){
                            $priceDetail['flow'] = $optionDurationPrice['price'];
                            $priceComponent[] = 'flow';
                        }else{
                            if($validate){
                                return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found')];
                            }
                        }
                    }
                }
                // 防护
                if(isset($param['peak_defence']) && !empty($param['peak_defence'])){
                    $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence']);
                    if($optionDurationPrice['match']){
                        $priceDetail['peak_defence'] = $optionDurationPrice['price'];
                        $priceComponent[] = 'peak_defence';
                    }else{
                        if($validate){
                            return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found')];
                        }
                    }
                }
                // 附加IP
                if(isset($param['ip_num']) && !empty($param['ip_num'])){
                    $optionDurationPrice = $OptionModel->optionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num']);
                    if($optionDurationPrice['match']){
                        $priceDetail['ip_num'] = $optionDurationPrice['price'];
                        $priceComponent[] = 'ip_num';
                    }else{
                        if($validate){
                            return ['status'=>400, 'msg'=>lang_plugins('line_add_ip_not_found')];
                        }
                    }
                }
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
                }
            }
        }
        // 快照备份基准
        $base = [];

        $data = [];
        foreach($duration as $k=>$v){
            if(empty($v['id'])){
                continue;
            }
            // 计算周期间倍率
            if(empty($base) || ($v['unit'] == $base['unit'] && $v['num'] == $base['num'])){
                $multiplier = 1;
            }else{
                // 计算倍率
                if($v['unit'] == $base['unit']){
                    $multiplier = round($v['num']/$base['num'], 2);
                }else{
                    if($v['unit'] == 'day' && $base['unit'] == 'hour'){
                        $multiplier = round($v['num']*24/$base['num'], 2);
                    }else if($v['unit'] == 'month' && $base['unit'] == 'hour'){
                        $multiplier = round($v['num']*30*24/$base['num'], 2);
                    }else if($v['unit'] == 'month' && $base['unit'] == 'day'){
                        $multiplier = round($v['num']*30/$base['num'], 2);
                    }
                }
            }
            $price = 0;
            foreach($priceComponent as $vv){
                $price = bcadd($price, $priceDetail[$vv][$v['id']] ?? 0);
            }
            $price = bcadd($price, $imagePrice);
            if(!empty($otherPrice)){
                $price = bcadd($price, bcmul($multiplier, $otherPrice));
            }
            // if($price == 0){
            //     continue;
            // }
            if(empty($base) && $price>0){
                $base = [
                    'unit'  => $v['unit'],
                    'num'   => $v['num'],
                    'price' => $price
                ];
            }

            $discount = 0;
            if($v['price_factor'] < 1){
                $discount = round($v['price_factor']*10, 1);
            }
            $price = bcmul($price, $v['price_factor']);

            // if(isset($base['price'])){
            //     $discount = round($price / $base['price'] / $multiplier * 10, 1);
            // }else{
            //     $discount = 0;
            // }

            $data[] = [
                'id'            => $v['id'],
                'name'          => $v['name'],
                'price'         => $price,
                'discount'      => $discount < 10 ? $discount : 0,
                'num'           => $v['num'] ?? 0,
                'unit'          => $v['unit'] ?? '',
            ];
        }
        $result['data'] = $data;
        return $result;
    }

    /**
     * 时间 2023-02-06
     * @title 配置计算价格
     * @desc 配置计算价格
     * @author hh
     * @version v1
     * @param   int data_center_id - 数据中心ID require
     * @param   int cpu - cpu核心数
     * @param   int memory - 内存
     * @param   int image_id 0 镜像ID
     * @param   int system_disk.size - 系统盘大小
     * @param   string system_disk.disk_type - 系统盘类型
     * @param   int data_disk[].size - 数据盘大小
     * @param   string data_disk[].disk_type - 系统盘类型
     * @param   int backup_num 0 备份数量
     * @param   int snap_num 0 备份数量
     * @param   int line_id - 线路ID
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - 附加IP数量
     * @param   int duration_id - 周期ID require
     */
    public function cartCalculatePrice($params, $only_cal = true){
        bcscale(2);

        $param = $params['custom'];

        $ProductModel = $params['product'];
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        $productId = $ProductModel['id'];

        $configData = [];
        if($ProductModel['pay_type'] == 'onetime'){
            $duration = [
                'id'    => 0,
                'name'  => '一次性',
            ];
            // TODO 一次性怎么计算?
        }else if($ProductModel['pay_type'] == 'recurring_prepayment' || $ProductModel['pay_type'] == 'recurring_postpaid'){
            $firstDuration = $this->field('id,name,num,unit')->where('product_id', $productId)->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();

            if(empty($firstDuration)){
                return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
            }
            $duration = $this->where('product_id', $productId)->where('id', $param['duration_id'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
            }
            // 计算倍率
            if($duration['unit'] == $firstDuration['unit']){
                $multiplier = round($duration['num']/$firstDuration['num'], 2);
            }else{
                if($duration['unit'] == 'day' && $firstDuration['unit'] == 'hour'){
                    $multiplier = round($duration['num']*24/$firstDuration['num'], 2);
                }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'hour'){
                    $multiplier = round($duration['num']*30*24/$firstDuration['num'], 2);
                }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'day'){
                    $multiplier = round($duration['num']*30/$firstDuration['num'], 2);
                }
            }

            $durationTime = 0;
            if($duration['unit'] == 'month'){
                $durationTime = strtotime('+ '.$duration['num'].' month') - time();
            }else if($duration['unit'] == 'day'){
                $durationTime = $duration['num'] * 3600 * 24;
            }else if($duration['unit'] == 'hour'){
                $durationTime = $duration['num'] * 3600;
            }
        }else if($ProductModel['pay_type'] == 'free'){
            $duration = [
                'id'    => 0,
                'name'  => '免费',
                'price' => '0.00',
            ];
        }else{
            return $result;
        }
        $configData['duration'] = $duration;

        $preview = [];
        $dataCenter = [];
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $dataCenter = DataCenterModel::where('product_id', $productId)->where('id', $param['data_center_id'])->find();
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_error')];
            }
            $preview[] = [
                'name'  =>  lang_plugins('country'),
                'value' =>  $dataCenter->getDataCenterName($dataCenter),
                'price' =>  0,
            ];

            $configData['data_center'] = $dataCenter;
        }
        $OptionModel = new OptionModel();
        $config = ConfigModel::where('product_id', $productId)->find();

        // 价格组成
        $priceComponent = [];
        $priceDetail = [];

        // 获取cpu周期价格
        if(isset($param['cpu']) && !empty($param['cpu'])){
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $param['cpu'], $param['duration_id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
            }
            $preview[] = [
                'name'  =>  'CPU',
                'value' =>  $param['cpu'].'核',
                'price' =>  $optionDurationPrice['price'] ?? 0,
            ];

            $configData['cpu'] = [
                'value' => $param['cpu'],
                'price' => $optionDurationPrice['price'] ?? 0,
                'other_config' => $optionDurationPrice['option']['other_config'],
            ];
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_cpu_config')];
            }
        }
        // 获取内存周期价格
        if(isset($param['memory']) && !empty($param['memory'])){
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $param['memory'], $param['duration_id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
            }
            $preview[] = [
                'name'  =>  '内存',
                'value' =>  $param['memory'].$config['memory_unit'],
                'price' =>  $optionDurationPrice['price'] ?? 0,
            ];

            $configData['memory'] = [
                'value' => $param['memory'],
                'price' => $optionDurationPrice['price'] ?? 0
            ];

            $configData['memory_unit'] = $config['memory_unit'];
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_memory_config')];
            }
        }
        // 获取镜像周期价格
        $imagePrice = 0;
        if(isset($param['image_id']) && !empty($param['image_id']) ){
            $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
            if(empty($image) || $image['product_id'] != $productId){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_os_not_found')];
            }
            // 验证镜像
            if($image['charge'] == 1 && !empty($image['price'])){
                $preview[] = [
                    'name'  =>  lang_plugins('mf_cloud_os'),
                    'value' =>  $image['name'],
                    'price' =>  $image['price'],
                ];

                $imagePrice = $image['price'];
            }else{
                $preview[] = [
                    'name'  =>  lang_plugins('mf_cloud_os'),
                    'value' =>  $image['name'],
                    'price' =>  0,
                ];
            }
            $configData['image'] = $image;
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_os')];
            }
        }
        // 获取系统盘周期价格
        if(isset($param['system_disk']['size']) && !empty($param['system_disk']['size'])){
            $whereAppend = ['other_config'=>json_encode(['disk_type'=>$param['system_disk']['disk_type'] ?? ''])];

            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::SYSTEM_DISK, 0, $param['system_disk']['size'], $param['duration_id'], $whereAppend);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('system_disk_config_not_found')];
            }
            $preview[] = [
                'name'  =>  lang_plugins('system_disk'),
                'value' =>  $param['system_disk']['size'].'GB',
                'price' =>  $optionDurationPrice['price'] ?? 0,
            ];

            $configData['system_disk'] = [
                'value' => $param['system_disk']['size'],
                'price' => $optionDurationPrice['price'] ?? 0,
                'other_config' => $optionDurationPrice['option']['other_config'],
            ];
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_system_disk_config')];
            }
        }
        // 获取数据盘周期价格
        if(isset($param['data_disk']) && !empty($param['data_disk'])){
            $dataDiskPrice = 0;
            $size = 0;
            foreach($param['data_disk'] as $k=>$v){
                $whereAppend = ['other_config'=>json_encode(['disk_type'=>$v['disk_type'] ?? ''])];

                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $param['duration_id'], $whereAppend);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('data_disk_config_not_found')];
                }
                $size += $v['size'];
                $dataDiskPrice = bcadd($dataDiskPrice, $optionDurationPrice['price'] ?? 0);

                $configData['data_disk'][] = [
                    'id'            => $v['id'] ?? 0,
                    'value'         => $v['size'],
                    'price'         => $optionDurationPrice['price'] ?? 0,
                    'other_config'  => $optionDurationPrice['option']['other_config'],
                ];
            }
            $preview[] = [
                'name'  =>  count($param['data_disk']).'个数据盘',
                'value' =>  $size.'GB',
                'price' =>  $dataDiskPrice,
            ];
        }
        // 备份快照
        $otherPrice = 0;
        if($config['backup_enable'] == 1){
            if(isset($param['backup_num']) && !empty($param['backup_num'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'backup')->where('num', $param['backup_num'])->find();
                if(empty($BackupConfigModel)){
                    return ['status'=>400, 'msg'=>lang_plugins('backup_num_select_error')];
                }
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);

                $preview[] = [
                    'name'  => lang_plugins('backup_function'),
                    'value' => $BackupConfigModel['num'].'个',
                    'price' => bcmul($BackupConfigModel['price'], $multiplier),
                ];

                $configData['backup'] = [
                    'num' => $BackupConfigModel['num'],
                    'price' => bcmul($BackupConfigModel['price'], $multiplier),
                ];

            }
        }
        if($config['snap_enable'] == 1){
            if(isset($param['snap_num']) && !empty($param['snap_num'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'snap')->where('num', $param['snap_num'])->find();
                if(empty($BackupConfigModel)){
                    return ['status'=>400, 'msg'=>lang_plugins('snap_num_select_error')];
                }
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);

                $preview[] = [
                    'name'  => lang_plugins('snap_function'),
                    'value' => $BackupConfigModel['num'].'个',
                    'price' => bcmul($BackupConfigModel['price'], $multiplier),
                ];

                $configData['snap'] = [
                    'num' => $BackupConfigModel['num'],
                    'price' => bcmul($BackupConfigModel['price'], $multiplier),
                ];
            }
        }
        // 有线路才能选择防御和附加IP
        if(isset($param['line_id']) && !empty($param['line_id'])){
            $line = LineModel::find($param['line_id']);
            if(!empty($line) && $line['data_center_id'] == $dataCenter['id']){

                $configData['line'] = $line;

                if($line['bill_type'] == 'bw'){
                    // 获取带宽周期价格
                    if(isset($param['bw']) && !empty($param['bw'])){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $param['duration_id']);
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('line_bw_not_found') ];
                        }
                        $preview[] = [
                            'name'  => '带宽',
                            'value' => $param['bw'].'Mbps',
                            'price' => $optionDurationPrice['price'] ?? 0,
                        ];

                        $configData['bw'] = [
                            'value' => $param['bw'],
                            'price' => $optionDurationPrice['price'] ?? 0,
                            'other_config' => $optionDurationPrice['option']['other_config'],
                        ];
                    }else{
                        if(!$only_cal){
                            return ['status'=>400, 'msg'=>lang_plugins('please_input_bw')];
                        }
                    }
                }else if($line['bill_type'] == 'flow'){
                    // 获取流量周期价格
                    if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $param['duration_id']);
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found') ];
                        }
                        $preview[] = [
                            'name'  => '流量',
                            'value' => $param['flow'] == 0 ? '无限流量' : $param['flow'].'G',
                            'price' => $optionDurationPrice['price'] ?? 0,
                        ];

                        $configData['flow'] = [
                            'value' => $param['flow'],
                            'price' => $optionDurationPrice['price'] ?? 0,
                            'other_config' => $optionDurationPrice['option']['other_config'],
                        ];
                    }else{
                        if(!$only_cal){
                            return ['status'=>400, 'msg'=>lang_plugins('please_input_line_flow')];
                        }
                    }
                }
                // 防护
                if($line['defence_enable'] == 1 && isset($param['peak_defence']) && !empty($param['peak_defence'])){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $param['duration_id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                    }
                    $preview[] = [
                        'name'  => '防御峰值',
                        'value' => $param['peak_defence'].'G',
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $configData['defence'] = [
                        'value' => $param['peak_defence'],
                        'price' => $optionDurationPrice['price'] ?? 0
                    ];
                }
                // 附加IP
                if($line['ip_enable'] == 1 && isset($param['ip_num']) && !empty($param['ip_num'])){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $param['duration_id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('line_add_ip_not_found') ];
                    }
                    $preview[] = [
                        'name'  => 'IP数量',
                        'value' => $param['ip_num'].'个',
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $configData['ip'] = [
                        'value' => $param['ip_num'],
                        'price' => $optionDurationPrice['price'] ?? 0
                    ];
                }
            }else{
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found') ];
            }
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_line')];
            }
        }
        // 前台勾选
        if($config['ip_mac_bind'] && isset($param['ip_mac_bind_enable']) && $param['ip_mac_bind_enable'] == 1){
            $configData['ip_mac_bind'] = 1;
        }
        if(is_numeric($config['ipv6_num']) && isset($param['ipv6_num_enable']) && $param['ipv6_num_enable'] == 1){
            $configData['ipv6_num'] = $config['ipv6_num'];
        }
        if(is_numeric($config['nat_acl_limit']) && isset($param['nat_acl_limit_enable']) && $param['nat_acl_limit_enable'] == 1){
            $configData['nat_acl_limit'] = $config['nat_acl_limit'];
        }
        if(is_numeric($config['nat_web_limit']) && isset($param['nat_web_limit_enable']) && $param['nat_web_limit_enable'] == 1){
            $configData['nat_web_limit'] = $config['nat_web_limit'];
        }
        if(isset($params['custom']['resource_package_id']) && !empty($params['custom']['resource_package_id'])){
            $configData['resource_package'] = ResourcePackageModel::where('id', $params['custom']['resource_package_id'])->find();
        }
        
        $price = 0;
        $description = '';
        foreach($preview as $k=>$v){
            // 价格系数
            $v['price'] = bcmul($v['price'], $duration['price_factor']);

            $price = bcadd($price, $v['price']);
            $description .= $v['name'].': '.$v['value'].',价格:'.$v['price']."\r\n";

            $preview[$k]['price'] = amount_format($v['price']);
        }

        // 缓存配置用于结算
        DurationModel::$configData = $configData;

        $imagePrice = bcmul($imagePrice, $duration['price_factor']);
        // 续费金额,减去一次性的
        $renewPrice = bcsub($price, $imagePrice);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price'         => amount_format($price),
                'renew_price'   => amount_format($renewPrice),
                'billing_cycle' => $duration['name'],
                'duration'      => $durationTime,
                'description'   => $description,
                'content'       => $description,
                'preview'       => $preview,
                'base_price'    => 0
            ]
        ];
        return $result;
    }




























    /**
     * 时间 2023-01-31
     * @title 计算当前时间一周期差时
     * @desc 计算当前时间一周期差时
     * @author hh
     * @version v1
     */
    public function diffTime($param){
        $time = 0;
        if($param['unit'] == 'hour'){
            // 小时
            $time = $param['num'] * 3600;
        }else if($param['unit'] == 'day'){
            // 天
            $time = $param['num'] * 3600 *24;
        }else if($param['unit'] == 'month'){
            // 自然月
            $expiry = strtotime('+ '.$param['num'].' month');
            $time = $expiry - time();
        }
        return $time;
    }











}