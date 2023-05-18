<?php 
namespace server\idcsmart_dcim\model;

use think\Model;
use think\db\Query;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\idcsmart_dcim\logic\ToolLogic;

class PackageModel extends Model{

	protected $name = 'module_idcsmart_dcim_package';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'name'                  => 'string',
        'description'           => 'string',
        'data_center_id'        => 'int',
        'dcim_server_group_id'  => 'int',
        'in_bw'                 => 'int',
        'out_bw'                => 'int',
        'ip_num'                => 'int',
        'ip_group'              => 'int',
        'custom_param'          => 'string',
        'traffic_enable'        => 'int',
        'flow'                  => 'int',
        'traffic_bill_type'     => 'string',
        'onetime_fee'           => 'string',
        'month_fee'             => 'string',
        'quarter_fee'           => 'string',
        'half_year_fee'         => 'string',
        'year_fee'              => 'string',
        'two_year'              => 'string',
        'three_year'            => 'string',
        'create_time'           => 'int',
        'product_id'            => 'int',
        'order'                 => 'int',
    ];

    /**
     * 时间 2022-06-16
     * @title 套餐列表
     * @desc 套餐列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   int param.product_id - 商品ID
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 套餐ID
     * @return  string data.list[].name - 套餐名称
     * @return  string data.list[].description - 描述
     * @return  int data.list[].data_center_id - 数据中心ID
     * @return  int data.list[].dcim_server_group_id - 销售分组ID
     * @return  int data.list[].in_bw - 进带宽
     * @return  int data.list[].out_bw - 出带宽
     * @return  int data.list[].ip_num - IP数量
     * @return  int data.list[].ip_group - IP分组
     * @return  string data.list[].custom_param - 自定义参数
     * @return  int data.list[].traffic_enable - 是否启用流量计费(0=关闭,1=开启)
     * @return  int data.list[].flow - 可用流量
     * @return  string data.list[].traffic_bill_type - 流量计费周期(month=自然月,last_30days=周期)
     * @return  string data.list[].onetime_fee - 一次性
     * @return  string data.list[].month_fee - 月
     * @return  string data.list[].quarter_fee - 季度
     * @return  string data.list[].half_year_fee - 半年
     * @return  string data.list[].year_fee - 一年
     * @return  string data.list[].two_year - 两年
     * @return  string data.list[].three_year - 三年
     * @return  int data.list[].order - 排序
     * @return  int data.list[].create_time - 创建时间
     * @return  int data.list[].product_id - 商品ID
     * @return  string data.list[].city - 城市
     * @return  string data.list[].country_name - 国家
     * @return  int data.count - 总条数
     */
    public function packageList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','order'])){
            $param['orderby'] = 'id';
        }

        $package = [];
        $count = 0;

        if(!empty($param['product_id'])){
            // 先获取当前月付比例
            $package = $this
                    ->alias('p')
                    ->field('p.*,dc.city,c.name_zh country_name')
                    ->leftJoin('module_idcsmart_dcim_data_center dc', 'p.data_center_id=dc.id')
                    ->leftJoin('country c', 'dc.country_id=c.id')
                    ->where('p.product_id', $param['product_id'])
                    ->limit($param['limit'])
                    ->page($param['page'])
                    ->order($param['orderby'], $param['sort'])
                    ->select()
                    ->toArray();

            $count = $this->where('product_id', $param['product_id'])->count();
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'=>$package,
                'count'=>$count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-06-17
     * @title 创建套餐
     * @desc 创建套餐
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.name - 套餐名称 require
     * @param   string param.description - 描述 require
     * @param   array param.data_center_id - 数据中心ID
     * @param   int param.dcim_server_group_id - 销售分组ID require
     * @param   int param.in_bw - 进带宽 require
     * @param   int param.out_bw - 出带宽 require
     * @param   int param.ip_num - IP数量 require
     * @param   int param.ip_group - IP分组ID
     * @param   string param.custom_param - 自定义参数
     * @param   int param.traffic_enable - 是否启用流量计费(0=关闭,1=开启) require
     * @param   int param.flow - 可用流量 开启require
     * @param   string param.traffic_bill_type 计费周期 month=自然月,last_30days=购买日一月
     * @param   string param.onetime_fee - 一次性价格
     * @param   string param.month_fee - 月价格
     * @param   string param.quarter_fee - 季度
     * @param   string param.half_year_fee - 半年
     * @param   string param.year_fee - 一年
     * @param   string param.two_year - 两年
     * @param   string param.three_year - 三年
     * @param   int param.order 0 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.id - 创建的套餐ID
     */
    public function createPackage($param)
    {
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'idcsmart_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        if(isset($param['data_center_id']) && is_array($param['data_center_id']) && !empty($param['data_center_id'])){
            $dateCenter = DataCenterModel::whereIn('id', $param['data_center_id'])
                        ->where('product_id', $param['product_id'])
                        ->select()
                        ->toArray();
            if(count($dateCenter) != count($param['data_center_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
            }
        }else{
            $param['data_center_id'] = 0;
        }

        $param['custom_param'] = $param['custom_param'] ?? '';
        $param['create_time'] = time();
        $param['traffic_bill_type'] = isset($param['traffic_bill_type']) && !empty($param['traffic_bill_type']) ? $param['traffic_bill_type'] : 'month';

        $schema = array_keys($this->schema);
        unset($schema['id']);

        $id = [];
        $this->startTrans();
        try{
            if(isset($dateCenter)){
                foreach($dateCenter as $v){
                    $param['data_center_id'] = $v['id'];

                    // 名称是否重复
                    // $name_exist = $this->where('name', $param['name'])->where('product_id', $param['product_id'])->find();
                    // if(!empty($name_exist)){
                    //     throw new \Exception(lang_plugins('package_name_is_using'));
                    // }

                    $package = $this->create($param, $schema);

                    $id[] = (int)$package->id;
                }
            }else{
                $param['data_center_id'] = 0;

                // 名称是否重复
                // $name_exist = $this->where('name', $param['name'])->where('product_id', $param['product_id'])->find();
                // if(!empty($name_exist)){
                //     throw new \Exception(lang_plugins('package_name_is_using'));
                // }

                $package = $this->create($param, $schema);

                $id[] = (int)$package->id;
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
        $this->saveMinPrice($ProductModel['id']);

        $description = lang_plugins('log_create_package_success', [
            '{name}'=>$param['name'],
        ]);
        active_log($description, 'product', $ProductModel['id']);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => $id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 修改套餐
     * @desc 修改套餐
     * @author hh
     * @version v1
     * @param   int param.id - 套餐ID require
     * @param   string param.name - 套餐名称 require
     * @param   string param.description - 描述 require
     * @param   array param.data_center_id - 数据中心ID
     * @param   int param.dcim_server_group_id - 销售分组ID require
     * @param   int param.in_bw - 进带宽 require
     * @param   int param.out_bw - 出带宽 require
     * @param   int param.ip_num - IP数量 require
     * @param   int param.ip_group - IP分组ID
     * @param   string param.custom_param - 自定义参数
     * @param   int param.traffic_enable - 是否启用流量计费(0=关闭,1=开启) require
     * @param   int param.flow - 可用流量 开启require
     * @param   string param.traffic_bill_type 计费周期 month=自然月,last_30days=购买日一月
     * @param   string param.onetime_fee - 一次性价格
     * @param   string param.month_fee - 月价格
     * @param   string param.quarter_fee - 季度
     * @param   string param.half_year_fee - 半年
     * @param   string param.year_fee - 一年
     * @param   string param.two_year - 两年
     * @param   string param.three_year - 三年
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updatePackage($param)
    {
        $package = $this->find($param['id']);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }

        if(isset($param['data_center_id'])){
            if(!empty($param['data_center_id'])){
                $dateCenter = DataCenterModel::find($param['data_center_id']);
                if(empty($dateCenter)){
                    return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
                }
            }else{
                $param['data_center_id'] = 0;
            }
        }

        $oldDataCenter = DataCenterModel::find($package['data_center_id']);

        $schema = array_keys($this->schema);
        unset($schema['id'], $schema['create_time'], $schema['product_id']);

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], $schema);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
        $this->saveMinPrice($package['product_id']);

        $desc = [
            'name'=>lang_plugins('name'),
            'description'=>lang_plugins('description'),
            'data_center_id'=>lang_plugins('data_center'),
            'dcim_server_group_id'=>lang_plugins('dcim_server_group_id'),
            'in_bw'=>lang_plugins('in_bw'),
            'out_bw'=>lang_plugins('out_bw'),
            'ip_num'=>lang_plugins('ip_num'),
            'ip_group'=>lang_plugins('ip_group'),
            'custom_param'=>lang_plugins('custom_param'),
            'traffic_enable'=>lang_plugins('traffic_enable'),
            'flow'=>lang_plugins('flow'),
            'traffic_bill_type'=>lang_plugins('traffic_bill_type'),
            'onetime_fee'=>lang_plugins('onetime_fee'),
            'month_fee'=>lang_plugins('month_fee'),
            'quarter_fee'=>lang_plugins('quarter_fee'),
            'half_year_fee'=>lang_plugins('half_year_fee'),
            'year_fee'=>lang_plugins('year_fee'),
            'two_year'=>lang_plugins('two_year'),
            'three_year'=>lang_plugins('three_year'),
            'order'=>lang_plugins('order'),
        ];

        $old = $package;
        
        $new = $param;
        if(isset($param['data_center_id'])){
            $old['data_center_id'] = isset($oldDataCenter) ? $oldDataCenter->getDataCenterName() : '';
            $new['data_center_id'] = isset($dateCenter) ? $dateCenter->getDataCenterName() : '';
        }
        
        $description = ToolLogic::createEditLog($old, $new, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_package_success', [
                '{name}'=>$package['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $package['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 删除套餐
     * @desc 删除套餐
     * @author hh
     * @version v1
     * @param   int id - 套餐ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function deletePackage($id)
    {
        $package = $this->find($id);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $use = HostLinkModel::where('package_id', $id)->find();
        if(!empty($use)){
            return ['status'=>400, 'msg'=>lang_plugins('package_is_using')];
        }

        $this->startTrans();
        try{
            $package->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
        $this->saveMinPrice($package['product_id']);

        $description = lang_plugins('log_delete_package_success', [
            '{name}'=>$package['name'],
        ]);
        active_log($description, 'product', $package['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-17
     * @title 修改排序
     * @desc 修改排序
     * @author hh
     * @version v1
     * @param   int param.id - 套餐ID
     * @param   int param.order - 排序
     */
    public function updateOrder($param){
        $package = $this->find((int)$param['id']);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }

        //$param['update_time'] = time();
        $this->update($param, ['id'=>$package['id']], ['order']);

        $desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($package, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_package_success', [
                '{name}'=>$package['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $package['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-22
     * @title 获取订购页实例配置
     * @desc 获取订购页实例配置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @return  array data.package - 套餐数据
     * @return  int data.package[].id - 套餐ID
     * @return  string data.package[].name - 套餐名称
     * @return  string data.package[].description - 套餐描述
     * @return  string data.package[].onetime_fee - 一次性费用(空不支持,0=免费)
     * @return  string data.package[].month_fee - 月(空不支持,0=免费)
     * @return  string data.package[].quarter_fee - 季度(空不支持,0=免费)
     * @return  string data.package[].year_fee - 年(空不支持,0=免费)
     * @return  string data.package[].two_year - 年(空不支持,0=免费)
     * @return  string data.package[].three_year - 年(空不支持,0=免费)
     * @return  string data.product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     */
    public function orderConfigShow($param){

        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');

        $product = ProductModel::field('pay_type')->find($param['product_id']) ?? (object)[];

        $where = [];
        $where[] = ['product_id', '=', $param['product_id'] ?? 0 ];
        
        if($product['pay_type'] == 'free'){
            // $where[] = ['onetime_fee|month_fee|quarter_fee|year_fee|two_year|three_year', '<>', ''];
        }else if($product['pay_type'] == 'onetime'){
            $where[] = ['onetime_fee', '<>', ''];
        }else{
            $where[] = ['month_fee|quarter_fee|year_fee|two_year|three_year', '<>', ''];
        }
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }

        $package = $this
                    ->field('id,name,description,onetime_fee,month_fee,quarter_fee,year_fee,two_year,three_year')
                    ->order('ip_num', 'asc')
                    ->order('in_bw', 'asc')
                    ->order('out_bw', 'asc')
                    ->where($where)
                    ->withAttr('month_fee', function($val) use ($product) {
                        return $product['pay_type'] == 'free' ? '0.00' : $val;
                    })
                    ->withAttr('onetime_fee', function($val) use ($product) {
                        return $product['pay_type'] == 'free' ? '0.00' : $val;
                    })
                    ->page($param['page'])
                    ->limit($param['limit'])
                    ->select()
                    ->toArray();
        
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'package'=>$package,
                'product'=>$product,
            ]
        ];
        return $result;
    }


    /**
     * 时间 2022-06-22
     * @title 配置计算价格
     * @desc 配置计算价格
     * @author hh
     * @version v1
     * @param   int params.custom.product_id - 商品ID require
     * @param   int params.custom.package_id - 套餐ID require
     * @param   int params.custom.image_id - 镜像ID require
     * @param   string params.custom.duration - 所选周期(onetime_fee=一次性就是)
     */
    public function cartCalculatePrice($params){
        // 自定义参数
        $param = $params['custom'];

        $duration = [
            'free'=>[
                'name'=>lang_plugins('免费'),
                'time'=>0,
            ],
            'onetime_fee'=>[
                'name'=>lang_plugins('onetime_fee'),
                'time'=>0,
            ],
            'month_fee'=>[
                'name'=>lang_plugins('month_fee'),
                'time'=>30*24*3600,
            ],
            'quarter_fee'=>[
                'name'=>lang_plugins('quarter_fee'),
                'time'=>90*24*3600,
            ],
            'half_year_fee'=>[
                'name'=>lang_plugins('half_year_fee'),
                'time'=>180*24*3600,
            ], 
            'year_fee'=>[
                'name'=>lang_plugins('year_fee'),
                'time'=>365*24*3600,
            ],
            'two_year'=>[
                'name'=>lang_plugins('two_year'),
                'time'=>2*365*24*3600,
            ],
            'three_year'=>[
                'name'=>lang_plugins('three_year'),
                'time'=>3*365*24*3600,
            ],
        ];

        $preview = [];
        $dataCenter = [];
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $dataCenter = DataCenterModel::where('product_id', $param['product_id'])->where('id', $param['data_center_id'])->find();
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_error')];
            }
            $preview[] = [
                'name'=>lang_plugins('country'),
                'value'=>$dataCenter->getDataCenterName($dataCenter),
                'price'=>0,
            ];
        }
        $PackageModel = PackageModel::where('product_id', $param['product_id'])->where('id', $param['package_id'])->find();
        if(empty($PackageModel)){
            if($params['scene'] != 'cal_price'){
                return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
            }
        }else{
            $preview[] = [
                'name'=>lang_plugins('package'),
                'value'=>$PackageModel['name'],
                'price'=>$PackageModel[ $param['duration'] ] ?? 0,
            ];
        }

        if(!empty($PackageModel) && !empty($PackageModel['data_center_id']) && !empty($dataCenter) && $dataCenter['id'] != $PackageModel['data_center_id']){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }

        $productId = $param['product_id'];
        if($PackageModel[ $param['duration'] ] === ''){
            return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
        }

        $isFree = false;
        // 看是否是商品设置的周期
        if($params['product']['pay_type'] == 'onetime'){
            if($param['duration'] != 'onetime_fee'){
                return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
            }
        }else if($params['product']['pay_type'] == 'recurring_prepayment' || $params['product']['pay_type'] == 'recurring_postpaid'){
            if($param['duration'] == 'onetime_fee'){
                return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
            }
        }else if($params['product']['pay_type'] == 'free'){
            $isFree = true;
        }else{
            return ['status'=>400, 'msg'=>lang_plugins('not_support_this_product')];
        }

        $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
        // 验证镜像
        if(empty($image)){
            if($params['scene'] != 'cal_price'){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }
        }else{
            $preview[] = [
                'name'=>lang_plugins('system'),
                'value'=>$image['name'],
                'price'=>$image['charge'] == 1 && !empty($image['price']) ? $image['price'] : 0,
            ];
        }

        // 套餐的价格+镜像
        $price = $PackageModel[ $param['duration'] ];
        $renew_price = $price;
        // 镜像
        if(!empty($image) && $image['charge'] == 1 && !empty($image['price'])){
            $price = bcadd($price, $image['price']);
        }
        $price = amount_format($price);

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'price'=>$isFree ? 0 : $price,
                'renew_price'=>$isFree ? 0 : $renew_price,
                'billing_cycle'=>$duration[ $param['duration'] ]['name'],
                'duration'=>$duration[ $param['duration'] ]['time'],
                'description'=>$PackageModel['description'],
                'content'=>$PackageModel['description'],  // TODO
                'preview'=>$preview,
                'base_price' => 0
            ]
        ];
        return $result;
    }

     /**
     * 时间 2022-07-29
     * @title 获取升降级套餐价格
     * @desc 获取升降级套餐价格
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.package_id - 产品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     */
    public function calUpgradePackagePrice($param){
        // 验证产品和用户
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
        // 前台判断
        $app = app('http')->getName();
        if($app == 'home'){
            if($host['client_id'] != get_client_id()){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
        }    
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }

        $oldPackage = PackageModel::find($hostLink['package_id']);

        // 验证套餐
        $package = $this->find($param['package_id']);
        if(empty($package) || $package['product_id'] != $host['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        if($param['package_id'] == $hostLink['package_id']){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_change')];
        }
        // 验证数据中心
        if($oldPackage['data_center_id']>0){
            if($oldPackage['data_center_id'] != $package['data_center_id']){
                return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
            }
        }


        // 获取当前周期
        if($host['billing_cycle'] == 'onetime'){
            $oldPrice = $oldPackage['onetime_fee'];
            $newPrice = $package['onetime_fee'];

            if(!is_numeric($newPrice)){
                return ['status'=>400, 'msg'=>lang_plugins('package_not_support_this_duration')];
            }
        }else{
            // 计算周期
            $days = $host['billing_cycle_time']/24/3600;

            $duration = [
                '30'=>'month_fee',
                '90'=>'quarter_fee',
                '180'=>'half_year_fee',
                '365'=>'year_fee',
                '730'=>'two_year',
                '1095'=>'three_year',
            ];

            $oldPrice = $oldPackage[ $duration[$days] ] ?? 0;
            $newPrice = $package[ $duration[$days] ] ?? 0;

            if(!isset($package[ $duration[$days] ]) || !is_numeric($package[ $duration[$days] ])){
                return ['status'=>400, 'msg'=>lang_plugins('package_not_support_this_duration')];
            }
        }

        $diffTime = $host['due_time'] - time();

        $priceDifference = $newPrice - $oldPrice;

        if($host['billing_cycle'] == 'onetime' || $diffTime <= 0){
            $diffPrice = $priceDifference;
        }else{
            $diffPrice = $priceDifference*$diffTime/$host['billing_cycle_time'];
        }
        $diffPrice = amount_format($diffPrice);
        $description = lang_plugins('package_change_description', ['{old_package}'=>$oldPackage['name'], '{new_package}'=>$package['name']]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $diffPrice,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成升降级套餐订单
     * @desc 生成升降级套餐订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.package_id - 产品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createUpgradePackageOrder($param){
        $res = $this->calUpgradePackagePrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'config_options' => [
                'type'       => 'change_package',
                'package_id' => $param['package_id'],
            ]
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2022-06-22
     * @title 根据配置获取周期价格
     * @desc 根据配置获取周期价格
     * @author hh
     * @version v1
     * @param   int param.hostId - 产品ID
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 周期价格ID
     * @return  string data.list[].display_name - 周期名称
     * @return  string data.list[].price - 周期价格
     */
    public function currentDurationPrice($param){
        $hostId = $param['host']['id'];
        $hostLink = HostLinkModel::where('host_id', $hostId)->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_found')];
        }
        $PackageModel = PackageModel::find($hostLink['package_id'] ?? 0);
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $duration = [
            'month_fee'=>[
                'num'=>1,
                'duration'=>30*24*3600,
                'desc'=>lang_plugins('month_fee'),
            ],
            'quarter_fee'=>[
                'num'=>3,
                'duration'=>90*24*3600,
                'desc'=>lang_plugins('quarter_fee'),
            ],
            'half_year_fee'=>[
                'num'=>6,
                'duration'=>180*24*3600,
                'desc'=>lang_plugins('half_year_fee'),
            ],
            'year_fee'=>[
                'num'=>12,
                'duration'=>365*24*3600,
                'desc'=>lang_plugins('year_fee'),
            ],
            'two_year'=>[
                'num'=>24,
                'duration'=>730*24*3600,
                'desc'=>lang_plugins('two_year'),
            ],
            'three_year'=>[
                'num'=>36,
                'duration'=>1095*24*3600,
                'desc'=>lang_plugins('three_year'),
            ],
        ];

        $durationPrice = [];
        foreach($duration as $k=>$v){
            if(is_numeric($PackageModel[ $k ] )){
                $durationPrice[] = [
                    'id'=>$k,
                    'duration'=>$v['duration'],
                    'billing_cycle'=>$v['desc'],
                    'price'=>amount_format($PackageModel[ $k ]),
                ];
            }
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$durationPrice,
        ];
        return $result;
    }

    /**
     * 时间 2022-10-11
     * @title 获取最低价格
     * @desc 获取最低价格
     * @author hh
     * @version v1
     * @param   int productId - 商品ID
     */
    public function saveMinPrice($productId){
        $ProductModel = ProductModel::find($productId);
        if(empty($ProductModel)){
            return false;
        }

        $cycle = null;
        if($ProductModel['pay_type'] == 'free'){
            $price = 0;
        }else if($ProductModel['pay_type'] == 'onetime'){
            $price = $this
                    ->where('product_id', $productId)
                    ->where('onetime_fee', '<>', '')
                    ->min('onetime_fee');
        }else{
            $duration = [
                'month_fee',
                'quarter_fee',
                'half_year_fee',
                'year_fee',
                'two_year',
                'three_year',
            ];

            $min = null;
            foreach($duration as $v){
                $price = $this
                    ->field("id,min(`{$v}`) price")
                    ->where('product_id', $productId)
                    ->where($v, '<>', '')
                    ->find();
                if(empty($price)){
                    continue;
                }
                if(isset($min)){
                    if($price['price'] < $min){
                        $min = $price['price'];
                        $cycle = $v;
                    }
                }else{
                    $min = $price['price'];
                    $cycle = $v;
                }
            }
            $price = $min;
            $cycle = lang_plugins($cycle);
        }
        $ProductModel->setPriceCycle('', $price, $cycle);
        return $price;
    }


    /**
     * 时间 2022-10-12
     * @title 获取所有周期价格
     * @desc 获取所有周期价格
     * @author hh
     * @version v1
     * @param   int package_id - 套餐ID require
     * @param   int image_id 0 镜像ID
     */
    public function allDuration($param){
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [],
        ];

        $durationArr = [
            'onetime_fee'=>[
                'name'=>lang_plugins('onetime_fee'),
                'num'=>1,
                'time'=>0,
            ],
            'month_fee'=>[
                'name'=>lang_plugins('month_fee'),
                'num'=>1,
                'time'=>30*24*3600,
            ],
            'quarter_fee'=>[
                'name'=>lang_plugins('quarter_fee'),
                'num'=>3,
                'time'=>90*24*3600,
            ],
            'half_year_fee'=>[
                'name'=>lang_plugins('half_year_fee'),
                'num'=>6,
                'time'=>180*24*3600,
            ], 
            'year_fee'=>[
                'name'=>lang_plugins('year_fee'),
                'num'=>12,
                'time'=>365*24*3600,
            ],
            'two_year'=>[
                'name'=>lang_plugins('two_year'),
                'num'=>24,
                'time'=>2*365*24*3600,
            ],
            'three_year'=>[
                'name'=>lang_plugins('three_year'),
                'num'=>36,
                'time'=>3*365*24*3600,
            ],
        ];

        $PackageModel = PackageModel::where('product_id', $param['id'])->where('id', $param['package_id'] ?? 0)->find();
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $ProductModel = ProductModel::find($PackageModel['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }

        if($ProductModel['pay_type'] == 'onetime'){
            // 强制一次性
            $durationArr = [ 
                'onetime_fee'=>$durationArr['onetime_fee']
            ];
        }else if($ProductModel['pay_type'] == 'recurring_prepayment' || $ProductModel['pay_type'] == 'recurring_postpaid'){
            // 取默认的周期
            unset($durationArr['onetime']);
        }else if($ProductModel['pay_type'] == 'free'){
            
            $result['data'][] = [
                'name'=>lang_plugins('free'),
                'duration'=>'free',
                'price'=>[
                    'total'     => '0.00',
                    'package'   => '0.00',
                    'image'     => '0.00',
                ],
            ];

            return $result;
        }else{
            return $result;
        }

        if(isset($param['image_id']) && !empty($param['image_id']) ){
            $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
            // 验证镜像
            if(empty($image)){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }
        }
        
        // 镜像价格
        if(isset($image) && $image['charge'] == 1 && !empty($image['price'])){
            $imagePrice = $image['price'];
        }else{
            $imagePrice = 0;
        }

        $data = [];
        foreach($durationArr as $k=>$v){
            if(!is_numeric($PackageModel[$k])){
                continue;
            }
            $data[] = [
                'name'=>$v['name'],
                'duration'=>$k,
                'price'=>[
                    'total'     => amount_format(bcadd($PackageModel[$k], $imagePrice)),
                    'package'   => amount_format($PackageModel[$k]),
                    'image'     => amount_format($imagePrice),
                ],
            ];
        }
        $result['data'] = $data;
        return $result;
    }



}