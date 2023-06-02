<?php 
namespace server\mf_dcim\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 周期模型
 * @use server\mf_dcim\model\DurationModel
 */
class DurationModel extends Model{

    // 计算价格后保存在上面
    public static $configData = [];

	protected $name = 'module_mf_dcim_duration';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
        'num'           => 'int',
        'unit'          => 'string',
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

        $dataCenter = $this
                ->field('id,name,num,unit')
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
                'list'=>$dataCenter,
                'count'=>$count
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
     * @return  int id - 添加成功的周期ID
     */
    public function durationCreate($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $param['create_time'] = time();

        $duration = $this->create($param, ['product_id','name','num','unit','create_time']);

        $description = lang_plugins('mf_dcim_log_add_duration_success', ['{name}'=>$param['name'] ]);
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
     */
    public function durationUpdate($param){
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_duration_not_found')];
        }

        $this->update($param, ['id'=>$DurationModel->id], ['name','num','unit']);

        if($DurationModel['name'] != $param['name']){
            $description = lang_plugins('mf_dcim_log_modify_duration_success', ['{name}'=>$DurationModel['name'], 'new_name'=>$param['name'] ]);
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
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_duration_not_found')];
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

        $description = lang_plugins('mf_dcim_log_delete_duration_success', ['{name}'=>$DurationModel['name'] ]);
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
     * @param   int model_config_id - 型号配置ID
     * @param   int image_id 0 镜像ID
     * @param   int line_id - 线路ID
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - 公网IP数量
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
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        $productId = $ProductModel->id;

        if($ProductModel['pay_type'] == 'onetime'){
            $duration = [
                [
                    'id'    => 0,
                    'name'  => '一次性',
                ]
            ];
        }else if($ProductModel['pay_type'] == 'recurring_prepayment' || $ProductModel['pay_type'] == 'recurring_postpaid'){
            $duration = $this->field('id,name,num,unit')->where('product_id', $productId)->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->select()->toArray();
        }else if($ProductModel['pay_type'] == 'free'){
            $duration = [
                [
                    'id'    => 0,
                    'name'  => '免费',
                    'price' => '0.00',
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

        // 获取型号周期价格
        if(isset($param['model_config_id']) && !empty($param['model_config_id'])){
            $modelConfig = ModelConfigModel::find($param['model_config_id']);
            if(!empty($modelConfig) && $modelConfig['product_id'] == $productId){
                $price = PriceModel::field('duration_id,price')->where('rel_type', 'model_config')->where('rel_id', $modelConfig['id'])->select()->toArray();

                $priceDetail['model_config_id'] = array_column($price, 'price', 'duration_id');
                $priceComponent[] = 'model_config_id';
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
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
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_bw_not_found')];
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
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_flow_not_found')];
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
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_defence_not_found')];
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
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_add_ip_not_found')];
                        }
                    }
                }
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
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
            if(isset($base['price'])){
                $discount = round($price / $base['price'] / $multiplier * 10, 1);
            }else{
                $discount = 0;
            }

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
     * @param   int duration_id - 周期ID require
     */
    public function cartCalculatePrice($params, $only_cal = true){
        bcscale(2);

        $param = $params['custom'];

        $ProductModel = $params['product'];
        $productId = $ProductModel['id'];

        $configData = [];
        if($ProductModel['pay_type'] == 'onetime'){
            $duration = [
                'id'    => 0,
                'name'  => '一次性',
            ];
            // TODO 一次性怎么计算?
        }else if($ProductModel['pay_type'] == 'recurring_prepayment' || $ProductModel['pay_type'] == 'recurring_postpaid'){
            $duration = $this->where('product_id', $productId)->where('id', $param['duration_id'])->find();
            if(empty($duration)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_duration_not_found')];
            }
            // 计算倍率
            // if($duration['unit'] == $firstDuration['unit']){
            //     $multiplier = round($duration['num']/$firstDuration['num'], 2);
            // }else{
            //     if($duration['unit'] == 'day' && $firstDuration['unit'] == 'hour'){
            //         $multiplier = round($duration['num']*24/$firstDuration['num'], 2);
            //     }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'hour'){
            //         $multiplier = round($duration['num']*30*24/$firstDuration['num'], 2);
            //     }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'day'){
            //         $multiplier = round($duration['num']*30/$firstDuration['num'], 2);
            //     }
            // }

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
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_data_center')];
            }
        }
        $OptionModel = new OptionModel();

        // 价格组成
        $priceComponent = [];
        $priceDetail = [];

        // 获取型号周期价格
        if(isset($param['model_config_id']) && !empty($param['model_config_id'])){
            $modelConfig = ModelConfigModel::find($param['model_config_id']);
            if(!empty($modelConfig) && $modelConfig['product_id'] == $productId){
                $optionDurationPrice = PriceModel::field('duration_id,price')->where('rel_type', 'model_config')->where('rel_id', $modelConfig['id'])->where('duration_id', $param['duration_id'])->find();

                $preview[] = [
                    'name'  =>  lang_plugins('mf_dcim_model_config'),
                    'value' =>  $modelConfig['name'],
                    'price' =>  $optionDurationPrice['price'] ?? 0,
                ];

                $modelConfig = $modelConfig->toArray();
                $modelConfig['price'] = $optionDurationPrice['price'] ?? 0;

                $configData['model_config'] = $modelConfig;
            }else{
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
            }
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('please_select_model_config')];
            }
        }
        // 获取镜像周期价格
        $imagePrice = 0;
        if(isset($param['image_id']) && !empty($param['image_id']) ){
            $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
            if(empty($image)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
            }
            // 验证镜像
            if($image['charge'] == 1 && !empty($image['price'])){
                $preview[] = [
                    'name'  =>  lang_plugins('mf_dcim_image'),
                    'value' =>  $image['name'],
                    'price' =>  $image['price'],
                ];

                $imagePrice = $image['price'];
            }else{
                $preview[] = [
                    'name'  =>  lang_plugins('mf_dcim_image'),
                    'value' =>  $image['name'],
                    'price' =>  0,
                ];
            }
            $configData['image'] = $image;
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_image')];
            }
        }
        // 有线路才能选择防御和公网IP
        if(isset($param['line_id']) && !empty($param['line_id'])){
            $line = LineModel::find($param['line_id']);
            if(!empty($line) && $line['data_center_id'] == $dataCenter['id']){

                $configData['line'] = $line;

                if($line['bill_type'] == 'bw'){
                    // 获取带宽周期价格
                    if(isset($param['bw']) && !empty($param['bw'])){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $param['duration_id']);
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_bw_not_found') ];
                        }
                        $preview[] = [
                            'name'  => lang_plugins('mf_dcim_bw'),
                            'value' => $param['bw'] == 'NC' ? '真实带宽' : $param['bw'].'Mbps',
                            'price' => $optionDurationPrice['price'] ?? 0,
                        ];

                        $configData['bw'] = [
                            'value' => $param['bw'],
                            'price' => $optionDurationPrice['price'] ?? 0,
                            'other_config' => $optionDurationPrice['option']['other_config'],
                        ];
                    }else{
                        if(!$only_cal){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_input_bw')];
                        }
                    }
                }else if($line['bill_type'] == 'flow'){
                    // 获取流量周期价格
                    if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0){
                        $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $param['duration_id']);
                        if(!$optionDurationPrice['match']){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_flow_not_found') ];
                        }
                        $preview[] = [
                            'name'  => lang_plugins('mf_dcim_flow'),
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
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_input_line_flow')];
                        }
                    }
                }
                // 防护
                if($line['defence_enable'] == 1 && isset($param['peak_defence']) && !empty($param['peak_defence'])){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $param['duration_id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('mf_dcim_peak_defence'),
                        'value' => $param['peak_defence'].'G',
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $configData['defence'] = [
                        'value' => $param['peak_defence'],
                        'price' => $optionDurationPrice['price'] ?? 0
                    ];
                }
                // 附加IP
                if(isset($param['ip_num']) && !empty($param['ip_num'])){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $param['duration_id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_ip_num_error') ];
                    }

                    if(strpos($param['ip_num'], '_') !== false){
                        $ip_num = explode(',', $param['ip_num']);

                        $num = 0;
                        foreach($ip_num as $vv){
                            $vv = explode('_', $vv);
                            $num += $vv[0];
                        }
                        $num = $num.'个';
                    }else if($param['ip_num'] == 'NC'){
                        $num = '真实IP';
                    }else{
                        $num = $param['ip_num'].'个';
                    }

                    $preview[] = [
                        'name'  => lang_plugins('mf_dcim_ip_num'),
                        'value' => $num,
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $configData['ip'] = [
                        'value' => $param['ip_num'],
                        'price' => $optionDurationPrice['price'] ?? 0
                    ];
                }else{
                    if(!$only_cal){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_ip_num')];
                    }
                }
            }else{
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found') ];
            }
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_line')];
            }
        }
       
        $price = 0;
        $description = '';
        foreach($preview as $k=>$v){
            $price = bcadd($price, $v['price']);
            $description .= $v['name'].': '.$v['value'].',价格:'.$v['price']."\r\n";

            $preview[$k]['price'] = amount_format($v['price']);
        }

        // 缓存配置用于结算
        DurationModel::$configData = $configData;

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