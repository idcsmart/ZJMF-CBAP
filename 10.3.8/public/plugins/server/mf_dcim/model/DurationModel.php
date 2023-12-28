<?php 
namespace server\mf_dcim\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_dcim\logic\ToolLogic;
use addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel;

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
        'price_factor'  => 'float',
        'price'         => 'float',
        'create_time'   => 'int',
    ];

    protected $clientLevel = [];

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
            $param['orderby'] = 'd.id';
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['product_id'])){
                $query->where('d.product_id', $param['product_id']);
            }
        };

        $duration = $this
                ->alias('d')
                ->field('d.id,d.name,d.num,d.unit,d.price_factor,d.price,pdr.ratio')
                ->leftJoin('product_duration_ratio pdr', 'd.product_id=pdr.product_id AND d.id=pdr.duration_id')
                ->withAttr('ratio', function($val){
                    return $val ?? '';
                })
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->group('d.id')
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('d')
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
     * @param   float price 0 价格
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
        $param['price_factor'] = $param['price_factor'] ?? 1;
        $param['price'] = $param['price'] ?? 0;
        if(!is_numeric($param['price_factor'])){
            $param['price_factor'] = 1;
        }
        if(!is_numeric($param['price'])){
            $param['price'] = 0;
        }

        $duration = $this->create($param, ['product_id','name','num','unit','price_factor','price','create_time']);

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
     * @param   float price_factor - 价格系数
     * @param   float price - 基础价格
     */
    public function durationUpdate($param){
        $DurationModel = $this->find($param['id']);
        if(empty($DurationModel)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_duration_not_found')];
        }

        if(isset($param['price_factor']) && !is_numeric($param['price_factor'])){
            $param['price_factor'] = 1;
        }
        if(isset($param['price']) && !is_numeric($param['price'])){
            $param['price'] = 0;
        }

        $this->update($param, ['id'=>$DurationModel->id], ['name','num','unit','price_factor','price']);

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
            DurationRatioModel::where('product_id', $DurationModel['product_id'])->where('duration_id', $param['id'])->delete();

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
    public function getAllDurationPrice($param, $validate = false, $clientId = 0){
        bcscale(2);
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
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;

        if($ProductModel['pay_type'] == 'onetime'){
            $duration = [
                [
                    'id'    => 0,
                    'name'  => lang_plugins('mf_dcim_onetime'),
                    'price_factor'  => 1,
                    'price'         => 0.00,
                ]
            ];
        }else if($ProductModel['pay_type'] == 'recurring_prepayment' || $ProductModel['pay_type'] == 'recurring_postpaid'){
            $duration = $this
                        ->field('id,name,num,unit,price_factor,price')
                        ->where('product_id', $productId)
                        ->orderRaw('field(unit, "hour","day","month")')
                        ->order('num', 'asc')
                        ->select()
                        ->toArray();
        }else if($ProductModel['pay_type'] == 'free'){
            $duration = [
                [
                    'id'            => 0,
                    'name'          => lang_plugins('mf_dcim_free'),
                    'price'         => '0.00',
                    'price_factor'  => 1,
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

        // 优先灵活机型
        // if(isset($param['package_id']) && !empty($param['package_id'])){
        //     $package = PackageModel::find($param['package_id']);
        //     if(!empty($package) && $package['product_id'] == $productId){
        //         // 当套餐IP数量/带宽和当前不一致时
        //         if((isset($param['bw']) && $param['bw'] != $package['bw']) || (isset($param['ip_num']) && $param['ip_num'] != $package['ip_num'])){
        //             if($validate){
        //                 return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        //             }
        //         }
        //         $price = PriceModel::field('duration_id,price')->where('rel_type', PriceModel::TYPE_PACKAGE)->where('rel_id', $package['id'])->select()->toArray();

        //         $priceDetail['package'] = array_column($price, 'price', 'duration_id');
        //         $priceComponent[] = 'package';

        //         $optional = [];
        //         // 是否选配了内存
        //         if(isset($param['optional_memory']) && !empty($param['optional_memory'])){
        //             $optionalMemoryId = array_keys($param['optional_memory']);

        //             $optionalMemory = PackageOptionLinkModel::alias('pol')
        //                             ->field('o.id,o.value,o.other_config')
        //                             ->join('module_mf_dcim_option o', 'pol.option_id=o.id')
        //                             ->where('pol.package_id', $package['id'])
        //                             ->whereIn('pol.option_id', $optionalMemoryId)
        //                             ->where('pol.option_rel_type', OptionModel::MEMORY)
        //                             ->order('o.order,o.id', 'asc')
        //                             ->select()
        //                             ->toArray();
        //             if(count($optionalMemoryId) != count($optionalMemory)){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
        //             }

        //             $memoryPrice = [];
        //             foreach($optionalMemory as $v){
        //                 $num = (int)$param['optional_memory'][ $v['id'] ];
        //                 if($num <= 0){
        //                     continue;
        //                 }
        //                 $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->select()->toArray();

        //                 foreach($price as $vv){
        //                     if(!isset($memoryPrice[ $vv['duration_id'] ])){
        //                         $memoryPrice[ $vv['duration_id'] ] = bcmul($vv['price'], $num);
        //                     }else{
        //                         $memoryPrice[ $vv['duration_id'] ] = bcadd($memoryPrice[ $vv['duration_id'] ], bcmul($vv['price'], $num));
        //                     }
        //                 }
        //             }
        //             $priceDetail['optional_memory'] = $memoryPrice;
        //             $priceComponent[] = 'optional_memory';
        //         }
        //         // 是否选配了硬盘
        //         if(isset($param['optional_disk']) && !empty($param['optional_disk'])){
        //             $optionalDiskId = array_keys($param['optional_disk']);

        //             $optionalDisk = PackageOptionLinkModel::alias('pol')
        //                             ->field('o.id,o.value,o.other_config')
        //                             ->join('module_mf_dcim_option o', 'pol.option_id=o.id')
        //                             ->where('pol.package_id', $package['id'])
        //                             ->whereIn('pol.option_id', $optionalDiskId)
        //                             ->where('pol.option_rel_type', OptionModel::DISK)
        //                             ->order('o.order,o.id', 'asc')
        //                             ->select()
        //                             ->toArray();
        //             if(count($optionalDiskId) != count($optionalDisk)){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
        //             }
        //             $diskPrice = [];
        //             foreach($optionalDisk as $v){
        //                 $num = (int)$param['optional_disk'][ $v['id'] ];
        //                 if($num <= 0){
        //                     continue;
        //                 }
        //                 $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->select()->toArray();

        //                 foreach($price as $vv){
        //                     if(!isset($diskPrice[ $vv['duration_id'] ])){
        //                         $diskPrice[ $vv['duration_id'] ] = bcmul($vv['price'], $num);
        //                     }else{
        //                         $diskPrice[ $vv['duration_id'] ] = bcadd($diskPrice[ $vv['duration_id'] ], bcmul($vv['price'], $num));
        //                     }
        //                 }
        //             }
        //             $priceDetail['optional_disk'] = $diskPrice;
        //             $priceComponent[] = 'optional_disk';
        //         }
        //     }else{
        //         if($validate){
        //             return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
        //         }
        //     }
        // }else{
            // 获取型号周期价格
            if(isset($param['model_config_id']) && !empty($param['model_config_id'])){
                $modelConfig = ModelConfigModel::find($param['model_config_id']);
                if(!empty($modelConfig) && $modelConfig['product_id'] == $productId){
                    $price = PriceModel::field('duration_id,price')->where('rel_type', 'model_config')->where('rel_id', $modelConfig['id'])->select()->toArray();

                    $priceDetail['model_config_id'] = array_column($price, 'price', 'duration_id');
                    $priceComponent[] = 'model_config_id';

                    $optional = [];
                    // 是否选配了内存
                    if(isset($param['optional_memory']) && !empty($param['optional_memory'])){
                        $optionalMemoryId = array_keys($param['optional_memory']);

                        $optionalMemory = ModelConfigOptionLinkModel::alias('mcol')
                                        ->field('o.id,o.value,o.other_config')
                                        ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                        ->where('mcol.model_config_id', $modelConfig['id'])
                                        ->whereIn('mcol.option_id', $optionalMemoryId)
                                        ->where('mcol.option_rel_type', OptionModel::MEMORY)
                                        ->order('o.order,o.id', 'asc')
                                        ->select()
                                        ->toArray();
                        if(count($optionalMemoryId) != count($optionalMemory)){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
                        }

                        $memoryPrice = [];
                        foreach($optionalMemory as $v){
                            $num = (int)$param['optional_memory'][ $v['id'] ];
                            if($num <= 0){
                                continue;
                            }
                            $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->select()->toArray();

                            foreach($price as $vv){
                                if(!isset($memoryPrice[ $vv['duration_id'] ])){
                                    $memoryPrice[ $vv['duration_id'] ] = bcmul($vv['price'], $num);
                                }else{
                                    $memoryPrice[ $vv['duration_id'] ] = bcadd($memoryPrice[ $vv['duration_id'] ], bcmul($vv['price'], $num));
                                }
                            }
                        }
                        $priceDetail['optional_memory'] = $memoryPrice;
                        $priceComponent[] = 'optional_memory';
                    }
                    // 是否选配了硬盘
                    if(isset($param['optional_disk']) && !empty($param['optional_disk'])){
                        $optionalDiskId = array_keys($param['optional_disk']);

                        $optionalDisk = ModelConfigOptionLinkModel::alias('mcol')
                                        ->field('o.id,o.value,o.other_config')
                                        ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                        ->where('mcol.model_config_id', $modelConfig['id'])
                                        ->whereIn('mcol.option_id', $optionalDiskId)
                                        ->where('mcol.option_rel_type', OptionModel::DISK)
                                        ->order('o.order,o.id', 'asc')
                                        ->select()
                                        ->toArray();
                        if(count($optionalDiskId) != count($optionalDisk)){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
                        }
                        $diskPrice = [];
                        foreach($optionalDisk as $v){
                            $num = (int)$param['optional_disk'][ $v['id'] ];
                            if($num <= 0){
                                continue;
                            }
                            $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->select()->toArray();

                            foreach($price as $vv){
                                if(!isset($diskPrice[ $vv['duration_id'] ])){
                                    $diskPrice[ $vv['duration_id'] ] = bcmul($vv['price'], $num);
                                }else{
                                    $diskPrice[ $vv['duration_id'] ] = bcadd($diskPrice[ $vv['duration_id'] ], bcmul($vv['price'], $num));
                                }
                            }
                        }
                        $priceDetail['optional_disk'] = $diskPrice;
                        $priceComponent[] = 'optional_disk';
                    }
                    // 是否选配了显卡
                    if(isset($param['optional_gpu']) && !empty($param['optional_gpu'])){
                        $optionalGpuId = array_keys($param['optional_gpu']);

                        $optionalGpu = ModelConfigOptionLinkModel::alias('mcol')
                                        ->field('o.id,o.value,o.other_config')
                                        ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                        ->where('mcol.model_config_id', $modelConfig['id'])
                                        ->whereIn('mcol.option_id', $optionalGpuId)
                                        ->where('mcol.option_rel_type', OptionModel::GPU)
                                        ->order('o.order,o.id', 'asc')
                                        ->select()
                                        ->toArray();
                        if(count($optionalGpuId) != count($optionalGpu)){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_gpu_optional_not_found')];
                        }
                        $gpuPrice = [];
                        foreach($optionalGpu as $v){
                            $num = (int)$param['optional_gpu'][ $v['id'] ];
                            if($num <= 0){
                                continue;
                            }
                            $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->select()->toArray();

                            foreach($price as $vv){
                                if(!isset($gpuPrice[ $vv['duration_id'] ])){
                                    $gpuPrice[ $vv['duration_id'] ] = bcmul($vv['price'], $num);
                                }else{
                                    $gpuPrice[ $vv['duration_id'] ] = bcadd($gpuPrice[ $vv['duration_id'] ], bcmul($vv['price'], $num));
                                }
                            }
                        }
                        $priceDetail['optional_gpu'] = $gpuPrice;
                        $priceComponent[] = 'optional_gpu';
                    }
                }else{
                    if($validate){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
                    }
                }
            }
        // }
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
                // if(isset($param['package_id']) && !empty($param['package_id'])){

                // }else{
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
                // }
                // 防护
                if(isset($param['peak_defence']) && is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0){
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
            }else{
                if($validate){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
                }
            }
        }
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'] ?? [];

        $clientLevel = $this->getClientLevel([
            'product_id'    => $productId,
            'client_id'     => $clientId ?: get_client_id(),
        ]);
        // 快照备份基准
        $base = [];

        $data = [];
        foreach($duration as $k=>$v){
            if(empty($v['id'])){
                continue;
            }
            // 计算周期间倍率
            // if(empty($base) || ($v['unit'] == $base['unit'] && $v['num'] == $base['num'])){
            //     $multiplier = 1;
            // }else{
            //     // 计算倍率
            //     if($v['unit'] == $base['unit']){
            //         $multiplier = round($v['num']/$base['num'], 2);
            //     }else{
            //         if($v['unit'] == 'day' && $base['unit'] == 'hour'){
            //             $multiplier = round($v['num']*24/$base['num'], 2);
            //         }else if($v['unit'] == 'month' && $base['unit'] == 'hour'){
            //             $multiplier = round($v['num']*30*24/$base['num'], 2);
            //         }else if($v['unit'] == 'month' && $base['unit'] == 'day'){
            //             $multiplier = round($v['num']*30/$base['num'], 2);
            //         }
            //     }
            // }
            $price = 0;
            $discountPrice = 0; // 可以计算等级折扣的金额

            foreach($priceComponent as $vv){
                if($vv == 'optional_memory' && $config['level_discount_memory_order'] == 0){

                }else if($vv == 'optional_disk' && $config['level_discount_disk_order'] == 0){

                }else if($vv == 'optional_gpu' && $config['level_discount_gpu_order'] == 0){

                }else{
                    $discountPrice = bcadd($discountPrice, $priceDetail[$vv][$v['id']] ?? 0);
                }
                $price = bcadd($price, $priceDetail[$vv][$v['id']] ?? 0);
            }
            $price = bcadd($price, $imagePrice);
            // 加上周期价格
            $price = bcadd($price, $v['price']);

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
            
            $durationName = $v['name'];
            if(app('http')->getName() == 'home'){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'name' => $v['name'],
                    ],
                ]);
                if(isset($multiLanguage['name'])){
                    $durationName = $multiLanguage['name'];
                }
            }
            $clientLevelDiscount = 0;
            if(!empty($clientLevel)){
                $clientLevelDiscount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
            }
            if($isDownstream){
                $price = bcsub($price, $clientLevelDiscount, 2);
                $clientLevel = 0;
            }

            $data[] = [
                'id'                    => $v['id'],
                'name'                  => $v['name'],
                'name_show'             => $durationName,
                'price'                 => $price,
                'discount'              => $discount < 10 ? $discount : 0,
                'num'                   => $v['num'] ?? 0,
                'unit'                  => $v['unit'] ?? '',
                'client_level_discount' => $clientLevelDiscount,
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
                'name'  => lang_plugins('mf_dcim_onetime'),
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
                'name'  => lang_plugins('mf_dcim_free'),
                'price' => '0.00',
            ];
        }else{
            return $result;
        }
        $configData['duration'] = $duration;

        $durationName = $duration['name'];
        $multiLanguage = hook_one('multi_language', [
            'replace' => [
                'name' => $duration['name'],
            ],
        ]);
        if(isset($multiLanguage['name'])){
            $durationName = $multiLanguage['name'];
        }

        $preview = [];
        $orderItem = []; // 追加的item

        // 记录后台产品详情信息
        $adminField = [
            'flow'      => '',
            'defence'   => '',
        ];

        // 周期基础价格
        $preview[] = [
            'name'  => lang_plugins('mf_dcim_time_duration'),
            'value' => $durationName,
            'price' => $duration['price'],
        ];
        $dataCenter = [];
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $dataCenter = DataCenterModel::where('product_id', $productId)->where('id', $param['data_center_id'])->find();
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_error')];
            }
            $preview[] = [
                'name'  =>  lang_plugins('country'),
                'value' =>  $dataCenter->getCountryName($dataCenter),
                'price' =>  0,
            ];

            $configData['data_center'] = $dataCenter;
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_data_center')];
            }
        }
        $OptionModel = new OptionModel();
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'] ?? [];

        // 灵活机型去掉 20231220
        // if(isset($param['package_id']) && !empty($param['package_id'])){
        //     $package = PackageModel::find($param['package_id']);
        //     if(!empty($package) && $package['hidden'] == 0 && $package['product_id'] == $productId){
        //         $optionDurationPrice = PriceModel::field('duration_id,price')
        //                             ->where('rel_type', PriceModel::TYPE_PACKAGE)
        //                             ->where('rel_id', $package['id'])
        //                             ->where('duration_id', $param['duration_id'])
        //                             ->find();

        //         $package = $package->toArray();
        //         $package['price'] = $optionDurationPrice['price'] ?? 0;
        //         $configData['package'] = $package;

        //         $multiLanguage = hook_one('multi_language', [
        //             'replace' => [
        //                 'name' => $package['name'],
        //             ],
        //         ]);
        //         if(isset($multiLanguage['name'])){
        //             $package['name'] = $multiLanguage['name'];
        //         }
        //         $preview[] = [
        //             'name'  =>  lang_plugins('mf_dcim_model_config'),
        //             'value' =>  $package['name'],
        //             'price' =>  $optionDurationPrice['price'] ?? 0,
        //         ];
        //         // 同时显示带宽/IP数量
        //         $preview[] = [
        //             'name'  =>  lang_plugins('mf_dcim_bw'),
        //             'value' =>  $package['bw'].'Mbps',
        //             'price' =>  0,
        //         ];
        //         $preview[] = [
        //             'name'  =>  lang_plugins('mf_dcim_option_value_5'),
        //             'value' =>  $package['ip_num'].lang_plugins('mf_dcim_indivual'),
        //             'price' =>  0,
        //         ];

        //         $cpu = OptionModel::find($package['cpu_option_id']);
        //         $memory = OptionModel::find($package['mem_option_id']);
        //         $disk = OptionModel::find($package['disk_option_id']);
        //         $memory['other_config'] = json_decode($memory['other_config'], true);

        //         $memoryDesc = [];
        //         $diskDesc = [];
        //         $adminFieldMemory = [];
        //         $adminFieldDisk = [];
        //         $mergeMemory = false;
        //         $mergeDisk = false;

        //         $memoryUsed = $memory['other_config']['memory'] * $package['mem_num'];
        //         $memorySlotUsed = $memory['other_config']['memory_slot'] * $package['mem_num'];
        //         $diskUsed = $package['disk_num'];

        //         $optional = [];
        //         // 是否选配了内存
        //         if(isset($param['optional_memory']) && !empty($param['optional_memory'])){
        //             $optionalMemoryId = array_keys($param['optional_memory']);

        //             $optionalMemory = PackageOptionLinkModel::alias('pol')
        //                             ->field('o.id,o.value,o.other_config')
        //                             ->join('module_mf_dcim_option o', 'pol.option_id=o.id')
        //                             ->where('pol.package_id', $package['id'])
        //                             ->whereIn('pol.option_id', $optionalMemoryId)
        //                             ->where('pol.option_rel_type', OptionModel::MEMORY)
        //                             ->order('o.order,o.id', 'asc')
        //                             ->select()
        //                             ->toArray();
        //             if(count($optionalMemoryId) != count($optionalMemory)){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
        //             }

        //             $memoryPrice = 0;
        //             foreach($optionalMemory as $v){
        //                 $v['other_config'] = json_decode($v['other_config'], true);
        //                 $num = (int)$param['optional_memory'][ $v['id'] ];
        //                 if($num <= 0){
        //                     continue;
        //                 }
        //                 $optional[] = [
        //                     'id'    => $v['id'],
        //                     'num'   => $num,
        //                 ];

        //                 $memoryUsed += $v['other_config']['memory'] * $num;
        //                 $memorySlotUsed += $v['other_config']['memory_slot'] * $num;

        //                 $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
        //                 $memoryPrice = bcadd($memoryPrice, bcmul($price, $num));

        //                 $multiLanguage = hook_one('multi_language', [
        //                     'replace' => [
        //                         'value' => $v['value'],
        //                     ],
        //                 ]);
        //                 $langValue = $multiLanguage['value'] ?? $v['value'];
                        
        //                 $memoryDesc[] = sprintf('%s_%d', $langValue, $num);

        //                 if($memory['id'] == $v['id']){
        //                     $adminFieldMemory[] = sprintf('%s_%d', $v['value'], $num + $package['mem_num']);
        //                     $mergeMemory = true;
        //                 }else{
        //                     $adminFieldMemory[] = sprintf('%s_%d', $v['value'], $num);
        //                 }
        //             }
        //             if($package['mem_max'] > 0 && $memoryUsed > $package['mem_max']){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_mem_max')];
        //             }
        //             if($package['mem_max_num'] > 0 && $memorySlotUsed > $package['mem_max_num']){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_mem_num_max')];
        //             }
        //             if(!empty($memoryDesc)){
        //                 $preview[] = [
        //                     'name'  =>  lang_plugins('mf_dcim_addition_memory'),
        //                     'value' =>  implode(';', $memoryDesc),
        //                     'price' =>  $memoryPrice,
        //                     'key'   => 'optional_memory',
        //                 ];
        //             }
        //         }
        //         // 是否选配了硬盘
        //         if(isset($param['optional_disk']) && !empty($param['optional_disk'])){
        //             $optionalDiskId = array_keys($param['optional_disk']);

        //             $optionalDisk = PackageOptionLinkModel::alias('pol')
        //                             ->field('o.id,o.value,o.other_config')
        //                             ->join('module_mf_dcim_option o', 'pol.option_id=o.id')
        //                             ->where('pol.package_id', $package['id'])
        //                             ->whereIn('pol.option_id', $optionalDiskId)
        //                             ->where('pol.option_rel_type', OptionModel::DISK)
        //                             ->order('o.order,o.id', 'asc')
        //                             ->select()
        //                             ->toArray();
        //             if(count($optionalDiskId) != count($optionalDisk)){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
        //             }
        //             $diskPrice = 0;
        //             foreach($optionalDisk as $v){
        //                 $v['other_config'] = json_decode($v['other_config'], true);
        //                 $num = (int)$param['optional_disk'][ $v['id'] ];
        //                 if($num <= 0){
        //                     continue;
        //                 }
        //                 $optional[] = [
        //                     'id'    => $v['id'],
        //                     'num'   => $num,
        //                 ];

        //                 $diskUsed += $num;

        //                 $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
        //                 $diskPrice = bcadd($diskPrice, bcmul($price, $num));

        //                 $multiLanguage = hook_one('multi_language', [
        //                     'replace' => [
        //                         'value' => $v['value'],
        //                     ],
        //                 ]);
        //                 $langValue = $multiLanguage['value'] ?? $v['value'];
                        
        //                 $diskDesc[] = sprintf('%s_%d', $langValue, $num);

        //                 if($disk['id'] == $v['id']){
        //                     $adminFieldDisk[] = sprintf('%s_%d', $v['value'], $num + $package['disk_num']);
        //                     $mergeDisk = true;
        //                 }else{
        //                     $adminFieldDisk[] = sprintf('%s_%d', $v['value'], $num);
        //                 }
        //             }
        //             if($package['disk_max_num'] > 0 && $diskUsed > $package['disk_max_num']){
        //                 return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_disk_num_max')];
        //             }

        //             if(!empty($diskDesc)){
        //                 $preview[] = [
        //                     'name'  =>  lang_plugins('mf_dcim_addition_disk'),
        //                     'value' =>  implode(';', $diskDesc),
        //                     'price' =>  $diskPrice,
        //                     'key'   => 'optional_disk',
        //                 ];
        //             }
        //         }
        //         if(!$mergeMemory) array_unshift($adminFieldMemory, sprintf('%s_%d', $memory['value'], $package['mem_num']));
        //         if(!$mergeDisk) array_unshift($adminFieldDisk, sprintf('%s_%d', $disk['value'], $package['disk_num']));

        //         $adminField['model_name'] = $package['name'];
        //         $adminField['cpu'] = sprintf('%s_%d', $cpu['value'], $package['cpu_num']);
        //         $adminField['cpu_param'] = '';
        //         $adminField['memory'] = implode(';', $adminFieldMemory);
        //         $adminField['disk'] = implode(';', $adminFieldDisk);
        //         $adminField['memory_used'] = $memoryUsed;
        //         $adminField['memory_num_used'] = $memorySlotUsed;
        //         $adminField['disk_num_used'] = $diskUsed;
        //         $adminField['bw'] = $package['bw'];
        //         $adminField['in_bw'] = '';
        //         $adminField['ip_num'] = $package['ip_num'];
        //         $adminField['flow'] = 0;

        //         $configData['optional'] = $optional;
        //     }else{
        //         return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
        //     }
        // }else{
            // 获取型号周期价格
            if(isset($param['model_config_id']) && !empty($param['model_config_id'])){
                $modelConfig = ModelConfigModel::find($param['model_config_id']);
                if(!empty($modelConfig) && $modelConfig['product_id'] == $productId){
                    $optionDurationPrice = PriceModel::field('duration_id,price')->where('rel_type', 'model_config')->where('rel_id', $modelConfig['id'])->where('duration_id', $param['duration_id'])->find();

                    $modelConfig = $modelConfig->toArray();
                    $modelConfig['price'] = $optionDurationPrice['price'] ?? 0;
                    $configData['model_config'] = $modelConfig;

                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'name' => $modelConfig['name'],
                        ],
                    ]);
                    if(isset($multiLanguage['name'])){
                        $modelConfig['name'] = $multiLanguage['name'];
                    }

                    $preview[] = [
                        'name'  =>  lang_plugins('mf_dcim_model_config'),
                        'value' =>  $modelConfig['name'],
                        'price' =>  $optionDurationPrice['price'] ?? 0,
                    ];

                    $adminFieldMemory = [];
                    $adminFieldDisk = [];
                    $adminFieldGpu = [];
                    $optional = [];
                    $memoryUsed = 0;
                    $memorySlotUsed = 0;
                    $diskUsed = 0;
                    $gpuUsed = 0;

                    $adminFieldMemory[] = $modelConfig['memory'];
                    $adminFieldDisk[] = $modelConfig['disk'];

                    if(!empty($modelConfig['gpu'])){
                        $adminFieldGpu[] = $modelConfig['gpu'];
                    }
                    if($modelConfig['support_optional'] == 1){
                        $memoryDesc = [];
                        $diskDesc = [];
                        $gpuDesc = [];
                        // 是否选配了内存
                        if(isset($param['optional_memory']) && !empty($param['optional_memory'])){
                            $optionalMemoryId = array_keys($param['optional_memory']);

                            $optionalMemory = ModelConfigOptionLinkModel::alias('mcol')
                                            ->field('o.id,o.value,o.other_config')
                                            ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                            ->where('mcol.model_config_id', $modelConfig['id'])
                                            ->whereIn('mcol.option_id', $optionalMemoryId)
                                            ->where('mcol.option_rel_type', OptionModel::MEMORY)
                                            ->order('o.order,o.id', 'asc')
                                            ->select()
                                            ->toArray();
                            if(count($optionalMemoryId) != count($optionalMemory)){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_memory_optional_not_found')];
                            }

                            $memoryPrice = 0;
                            foreach($optionalMemory as $v){
                                $v['other_config'] = json_decode($v['other_config'], true);
                                $num = (int)$param['optional_memory'][ $v['id'] ];
                                if($num <= 0){
                                    continue;
                                }
                                $optional[] = [
                                    'id'    => $v['id'],
                                    'num'   => $num,
                                ];

                                $memoryUsed += $v['other_config']['memory'] * $num;
                                $memorySlotUsed += $v['other_config']['memory_slot'] * $num;

                                $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                                $memoryPrice = bcadd($memoryPrice, bcmul($price, $num));

                                $multiLanguage = hook_one('multi_language', [
                                    'replace' => [
                                        'value' => $v['value'],
                                    ],
                                ]);
                                $langValue = $multiLanguage['value'] ?? $v['value'];
                                
                                $memoryDesc[] = sprintf('%s_%d', $langValue, $num);
                                $adminFieldMemory[] = sprintf('%s_%d', $v['value'], $num);
                            }
                            if($memoryUsed > $modelConfig['leave_memory']){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_mem_max')];
                            }
                            if($memorySlotUsed > $modelConfig['max_memory_num']){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_mem_num_max')];
                            }
                            if(!empty($memoryDesc)){
                                $preview[] = [
                                    'name'  =>  lang_plugins('mf_dcim_addition_memory'),
                                    'value' =>  implode(';', $memoryDesc),
                                    'price' =>  $memoryPrice,
                                    'key'   => 'optional_memory',
                                ];
                            }
                        }
                        // 是否选配了硬盘
                        if(isset($param['optional_disk']) && !empty($param['optional_disk'])){
                            $optionalDiskId = array_keys($param['optional_disk']);

                            $optionalDisk = ModelConfigOptionLinkModel::alias('mcol')
                                            ->field('o.id,o.value,o.other_config')
                                            ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                            ->where('mcol.model_config_id', $modelConfig['id'])
                                            ->whereIn('mcol.option_id', $optionalDiskId)
                                            ->where('mcol.option_rel_type', OptionModel::DISK)
                                            ->order('o.order,o.id', 'asc')
                                            ->select()
                                            ->toArray();
                            if(count($optionalDiskId) != count($optionalDisk)){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_disk_optional_not_found')];
                            }
                            $diskPrice = 0;
                            foreach($optionalDisk as $v){
                                $v['other_config'] = json_decode($v['other_config'], true);
                                $num = (int)$param['optional_disk'][ $v['id'] ];
                                if($num <= 0){
                                    continue;
                                }
                                $optional[] = [
                                    'id'    => $v['id'],
                                    'num'   => $num,
                                ];

                                $diskUsed += $num;

                                $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                                $diskPrice = bcadd($diskPrice, bcmul($price, $num));

                                $multiLanguage = hook_one('multi_language', [
                                    'replace' => [
                                        'value' => $v['value'],
                                    ],
                                ]);
                                $langValue = $multiLanguage['value'] ?? $v['value'];
                                
                                $diskDesc[] = sprintf('%s_%d', $langValue, $num);
                                $adminFieldDisk[] = sprintf('%s_%d', $v['value'], $num);
                            }
                            if($diskUsed > $modelConfig['max_disk_num']){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_disk_num_max')];
                            }
                            if(!empty($diskDesc)){
                                $preview[] = [
                                    'name'  =>  lang_plugins('mf_dcim_addition_disk'),
                                    'value' =>  implode(';', $diskDesc),
                                    'price' =>  $diskPrice,
                                    'key'   => 'optional_disk',
                                ];
                            }
                        }
                        // 是否选配了显卡
                        if(isset($param['optional_gpu']) && !empty($param['optional_gpu'])){
                            $optionalGpuId = array_keys($param['optional_gpu']);

                            $optionalGpu = ModelConfigOptionLinkModel::alias('mcol')
                                            ->field('o.id,o.value,o.other_config')
                                            ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                                            ->where('mcol.model_config_id', $modelConfig['id'])
                                            ->whereIn('mcol.option_id', $optionalGpuId)
                                            ->where('mcol.option_rel_type', OptionModel::GPU)
                                            ->order('o.order,o.id', 'asc')
                                            ->select()
                                            ->toArray();
                            if(count($optionalGpuId) != count($optionalGpu)){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_gpu_optional_not_found')];
                            }
                            $gpuPrice = 0;
                            foreach($optionalGpu as $v){
                                $v['other_config'] = json_decode($v['other_config'], true);
                                $num = (int)$param['optional_gpu'][ $v['id'] ];
                                if($num <= 0){
                                    continue;
                                }
                                $optional[] = [
                                    'id'    => $v['id'],
                                    'num'   => $num,
                                ];

                                $gpuUsed += $num;

                                $price = PriceModel::where('rel_type', PriceModel::TYPE_OPTION)->where('rel_id', $v['id'])->where('duration_id', $duration['id'])->value('price') ?? 0;
                                $gpuPrice = bcadd($gpuPrice, bcmul($price, $num));

                                $multiLanguage = hook_one('multi_language', [
                                    'replace' => [
                                        'value' => $v['value'],
                                    ],
                                ]);
                                $langValue = $multiLanguage['value'] ?? $v['value'];
                                
                                $gpuDesc[] = sprintf('%s_%d', $langValue, $num);
                                $adminFieldGpu[] = sprintf('%s_%d', $v['value'], $num);
                            }
                            if($gpuUsed > $modelConfig['max_gpu_num']){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_over_package_gpu_num_max')];
                            }
                            if(!empty($gpuDesc)){
                                $preview[] = [
                                    'name'  =>  lang_plugins('mf_dcim_addition_gpu'),
                                    'value' =>  implode(';', $gpuDesc),
                                    'price' =>  $gpuPrice,
                                    'key'   => 'optional_gpu',
                                ];
                            }
                        }
                    }
                    
                    $adminField['model_name'] = $modelConfig['name'];
                    $adminField['cpu'] = $modelConfig['cpu'];
                    $adminField['cpu_param'] = $modelConfig['cpu_param'];
                    $adminField['memory'] = implode(';', $adminFieldMemory);
                    $adminField['disk'] = implode(';', $adminFieldDisk);
                    $adminField['gpu'] = implode(';', $adminFieldGpu);
                    $adminField['memory_used'] = $memoryUsed;
                    $adminField['memory_num_used'] = $memorySlotUsed;
                    $adminField['disk_num_used'] = $diskUsed;
                    $adminField['gpu_num_used'] = $gpuUsed;

                    $configData['optional'] = $optional;
                }else{
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
                }
            }else{
                if(!$only_cal){
                    return ['status'=>400, 'msg'=>lang_plugins('please_select_model_config')];
                }
            }
        // }
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
                    'key'   => 'image',
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

                $adminField['line'] = [
                    'id'    => $line['id'],
                    'name'  => $line['name'],
                ];

                // if(!(isset($param['package_id']) && !empty($param['package_id']))){
                    if($line['bill_type'] == 'bw'){
                        // 获取带宽周期价格
                        if(isset($param['bw']) && !empty($param['bw'])){
                            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $param['duration_id']);
                            if(!$optionDurationPrice['match']){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_bw_not_found') ];
                            }
                            $preview[] = [
                                'name'  => lang_plugins('mf_dcim_bw'),
                                'value' => $param['bw'] == 'NC' ? lang_plugins('mf_dcim_real_bw') : $param['bw'].'Mbps',
                                'price' => $optionDurationPrice['price'] ?? 0,
                            ];

                            $configData['bw'] = [
                                'value' => $param['bw'],
                                'price' => $optionDurationPrice['price'] ?? 0,
                                'other_config' => $optionDurationPrice['option']['other_config'],
                            ];

                            $adminField['bw'] = $param['bw'];
                            $adminField['in_bw'] = $optionDurationPrice['option']['other_config']['in_bw'];
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
                                'value' => $param['flow'] == 0 ? lang_plugins('mf_dcim_unlimited_flow') : $param['flow'].'G',
                                'price' => $optionDurationPrice['price'] ?? 0,
                            ];

                            $configData['flow'] = [
                                'value' => $param['flow'],
                                'price' => $optionDurationPrice['price'] ?? 0,
                                'other_config' => $optionDurationPrice['option']['other_config'],
                            ];

                            $adminField['flow'] = $param['flow'];
                            $adminField['bw'] = $optionDurationPrice['option']['other_config']['out_bw'];
                            $adminField['in_bw'] = $optionDurationPrice['option']['other_config']['in_bw'];
                        }else{
                            if(!$only_cal){
                                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_input_line_flow')];
                            }
                        }
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
                            $num = $num . lang_plugins('mf_dcim_indivual');
                        }else if($param['ip_num'] == 'NC'){
                            $num = lang_plugins('mf_dcim_real_ip');
                        }else{
                            $num = $param['ip_num'] . lang_plugins('mf_dcim_indivual');
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

                        $adminField['ip_num'] = $param['ip_num'];
                    }else{
                        if(!$only_cal){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_ip_num')];
                        }
                    }
                // }else{
                //     if(!$only_cal && $line['bill_type'] != 'bw'){
                //         return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
                //     }
                // }
                // 防护
                if($line['defence_enable'] == 1 && isset($param['peak_defence']) && is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $param['duration_id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('mf_dcim_peak_defence'),
                        'value' => $param['peak_defence'] == 0 ? lang_plugins('mf_dcim_no_defence') : $param['peak_defence'].'G',
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $configData['defence'] = [
                        'value' => $param['peak_defence'],
                        'price' => $optionDurationPrice['price'] ?? 0
                    ];

                    $adminField['defence'] = $param['peak_defence'];
                }
            }else{
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found') ];
            }
        }else{
            if(!$only_cal){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_please_select_line')];
            }
        }
       
        $isDownstream = input('get.is_downstream', 0) == 1;
        $clientLevel = $this->getClientLevel([
            'product_id'    => $productId,
            'client_id'     => get_client_id(),
        ]);

        $price = 0;
        $discountPrice = 0; // 可以优惠的总金额
        $discount = 0;
        $description = '';
        $basePrice = $price;
        $renewPrice = 0;
        foreach($preview as $k=>$v){
            // 价格系数
            $v['price'] = bcmul($v['price'], $duration['price_factor']);

            $price = bcadd($price, $v['price']);
            // 镜像不算续费
            if(isset($v['key']) && $v['key'] == 'image'){

            }else{
                $basePrice = bcadd($basePrice,$v['price']);
                $renewPrice = bcadd($renewPrice, $v['price']);
            }
            if(isset($v['key']) && $v['key'] == 'optional_memory' && $config['level_discount_memory_order'] == 0){

            }else if(isset($v['key']) && $v['key'] == 'optional_disk' && $config['level_discount_disk_order'] == 0){

            }else if(isset($v['key']) && $v['key'] == 'optional_gpu' && $config['level_discount_gpu_order'] == 0){

            }else{
                $discountPrice = bcadd($discountPrice, $v['price']);
                if($isDownstream){
                    $clientLevelDiscount = bcdiv($v['price']*$clientLevel['discount_percent'], 100, 2);
                    if($clientLevelDiscount > 0){
                        $v['price'] = bcsub($v['price'], $clientLevelDiscount, 2);
                    }
                }
            }
            
            $description .= $v['name'].': '.$v['value'].','.lang_plugins('mf_dcim_price').':'.$v['price']."\r\n";

            $preview[$k]['price'] = amount_format($v['price']);
        }

        if(!empty($clientLevel)){
            $discount = bcdiv($discountPrice*$clientLevel['discount_percent'], 100, 2);
            $renewDiscount = bcdiv($renewPrice*$clientLevel['discount_percent'], 100, 2);
            
            $orderItem[] = [
                'type'          => 'addon_idcsmart_client_level',
                'rel_id'        => $clientLevel['id'],
                'amount'        => -$discount,
                'description'   => lang_plugins('mf_dcim_client_level', [
                    '{name}'    => $clientLevel['name'],
                    '{value}'   => $clientLevel['discount_percent'],
                ]),
            ];
        }

        // 缓存配置用于结算
        $configData['admin_field'] = $adminField;
        DurationModel::$configData = $configData;

        // $imagePrice = bcmul($imagePrice, $duration['price_factor']);
        // 续费金额,减去一次性的
        // $renewPrice = bcsub($price, $imagePrice);

        if($discount != 0){
            $price = bcsub($price, $discount);
            $renewPrice = bcsub($renewPrice, $renewDiscount);
        }

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
                'base_price'    => $basePrice,
                // 'admin_field'   => $adminField,
                'order_item'    => $orderItem,
                'discount'      => amount_format($discount),
                // 'client_level'  => $clientLevel,
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

    public function getClientLevel($param){
        $discount = [];
        if(class_exists('addon\idcsmart_client_level\model\IdcsmartClientLevelClientLinkModel')){
            try{
                $discount = IdcsmartClientLevelClientLinkModel::alias('aiclcl')
                    ->field('aicl.id,aicl.name,aiclpl.product_id,aiclpl.discount_percent')
                    ->leftJoin('addon_idcsmart_client_level aicl', 'aiclcl.addon_idcsmart_client_level_id=aicl.id')
                    ->leftJoin('addon_idcsmart_client_level_product_link aiclpl', 'aiclpl.addon_idcsmart_client_level_id=aicl.id')
                    ->where('aiclcl.client_id', $param['client_id'])
                    ->where('aiclpl.product_id', $param['product_id'])
                    ->where('aicl.discount_status', 1)
                    ->find();
            }catch(\Exception $e){
                
            }
        }
        return $discount;
    }

    public function downstreamSubClientLevelPrice($param){
        if(!isset($this->clientLevel[ $param['client_id'] ][ $param['product_id'] ])){
            $clientLevel = $this->getClientLevel([
                'product_id'    => $param['product_id'],
                'client_id'     => $param['client_id'],
            ]);
            $this->clientLevel[ $param['client_id'] ][ $param['product_id'] ] = $clientLevel;
        }else{
            $clientLevel = $this->clientLevel[ $param['client_id'] ][ $param['product_id'] ];
        }
        if($param['price'] > 0 && !empty($clientLevel)){
            $clientLevelDiscount = bcdiv($param['price'] * $clientLevel['discount_percent'], 100, 2);
            if($clientLevelDiscount > 0){
                $param['price'] = bcsub($param['price'], $clientLevelDiscount, 2);
            }
        }
        return $param['price'];
    }

}