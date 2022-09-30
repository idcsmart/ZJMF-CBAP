<?php 
namespace server\common_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\common_cloud\logic\ToolLogic;
use server\common_cloud\idcsmart_cloud\IdcsmartCloud;

class PackageModel extends Model{

	protected $name = 'module_common_cloud_package';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'name'                  => 'string',
        'description'           => 'string',
        'data_center_id'        => 'int',
        'cpu'                   => 'int',
        'memory'                => 'int',
        'system_disk_size'      => 'int',
        'system_disk_store'     => 'int',
        'free_data_disk_size'   => 'int',
        'data_disk_store'       => 'int',
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
     * @return  int data.list[].cpu - CPU
     * @return  int data.list[].memory - 内存
     * @return  int data.list[].system_disk_size - 系统盘大小
     * @return  int data.list[].system_disk_store - 系统盘存储
     * @return  int data.list[].free_data_disk_size - 赠送数据盘大小
     * @return  int data.list[].data_disk_store - 数据盘存储
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

        $package = [];
        $count = 0;

        if(!empty($param['product_id'])){
            // 先获取当前月付比例
            $package = $this
                    ->alias('p')
                    ->field('p.*,dc.city,c.name_zh country_name')
                    ->leftJoin('module_common_cloud_data_center dc', 'p.data_center_id=dc.id')
                    ->leftJoin('country c', 'dc.country_id=c.id')
                    ->where('p.product_id', $param['product_id'])
                    ->limit($param['limit'])
                    ->page($param['page'])
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
     * @param   array param.data_center_id - 数据中心ID require
     * @param   int param.cpu - CPU require
     * @param   int param.memory - 内存 require
     * @param   int param.system_disk_size - 系统盘 require
     * @param   int param.system_disk_store - 存储ID
     * @param   int param.free_data_disk_size - 赠送数据盘
     * @param   int param.data_disk_store - 数据盘存储ID
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
        if($ProductModel->getModule() != 'common_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $dateCenter = DataCenterModel::whereIn('id', $param['data_center_id'])
                        ->where('product_id', $param['product_id'])
                        ->select()
                        ->toArray();
        if(count($dateCenter) != count($param['data_center_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }

        $param['custom_param'] = $param['custom_param'] ?? '';
        $param['create_time'] = time();
        $param['traffic_bill_type'] = isset($param['traffic_bill_type']) && !empty($param['traffic_bill_type']) ? $param['traffic_bill_type'] : 'month';

        $schema = array_keys($this->schema);
        unset($schema['id']);

        $id = [];
        $name = [];
        $this->startTrans();
        try{
            $tmp_name = $param['name'];

            $i = 1;
            foreach($dateCenter as $v){
                $param['name'] = count($dateCenter) == 1 ? $tmp_name : $tmp_name.'-'.$i;
                $param['data_center_id'] = $v['id'];

                // 名称是否重复
                $name_exist = $this->where('name', $param['name'])->where('product_id', $param['product_id'])->find();
                if(!empty($name_exist)){
                    throw new \Exception(lang_plugins('package_name_is_using'));
                }

                $package = $this->create($param, $schema);

                $i++;
                $id[] = (int)$package->id;
                $name[] = $param['name'];
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
        $description = lang_plugins('log_create_package_success', [
            '{name}'=>implode(',', $name),
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
     * @param   array param.data_center_id - 数据中心ID require
     * @param   int param.cpu - CPU require
     * @param   int param.memory - 内存 require
     * @param   int param.system_disk_size - 系统盘 require
     * @param   int param.system_disk_store - 存储ID
     * @param   int param.free_data_disk_size - 赠送数据盘
     * @param   int param.data_disk_store - 数据盘存储ID
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
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updatePackage($param)
    {
        $package = $this->find($param['id']);
        if(empty($package)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $dateCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dateCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $nameExist = $this
                    ->where('name', $param['name'])
                    ->where('product_id', $package['product_id'])
                    ->where('id', '<>', $param['id'])
                    ->find();
        if(!empty($nameExist)){
            return ['status'=>400, 'msg'=>lang_plugins('package_name_is_using')];
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

        $desc = [
            'name'=>lang_plugins('name'),
            'description'=>lang_plugins('description'),
            'data_center_id'=>lang_plugins('data_center'),
            'cpu'=>'CPU',
            'memory'=>lang_plugins('memory'),
            'system_disk_size'=>lang_plugins('system_disk_size'),
            'system_disk_store'=>lang_plugins('system_disk_store'),
            'free_data_disk_size'=>lang_plugins('free_disk'),
            'data_disk_store'=>lang_plugins('free_disk_store'),
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
        ];

        $old = $package;
        $old['data_center_id'] = $oldDataCenter->getDataCenterName();
        
        $new = $param;
        $new['data_center_id'] = $dateCenter->getDataCenterName();
        
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
     * 时间 2022-06-22
     * @title 获取订购页实例配置
     * @desc 获取订购页实例配置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.data_center_id - 数据中心ID
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
     * @return  int data.count - 总条数
     * @return  string data.product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     */
    public function orderConfigShow($param){
        
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');

        $product = ProductModel::field('pay_type')->find($param['product_id']) ?? (object)[];

        $where = [];
        $where[] = ['product_id', '=', $param['product_id'] ?? 0 ];
        $where[] = ['onetime_fee|month_fee|quarter_fee|year_fee|two_year|three_year', '<>', ''];
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }

        $count = $this->where($where)->count();

        $package = $this
                    ->field('id,name,description,onetime_fee,month_fee,quarter_fee,year_fee,two_year,three_year')
                    ->order('cpu', 'asc')
                    ->order('memory', 'asc')
                    ->where($where)
                    ->page($param['page'])
                    ->limit($param['limit'])
                    ->select()
                    ->toArray();
        
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'package'=>$package,
                'count'=>$count,
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
     * @param   int data_center_id - 数据中心
     * @param   int package_id - 套餐ID require
     * @param   int image_id - 镜像ID require
     * @param   int backup_num_id - 选择的备份数量ID(0=关闭)
     * @param   int snap_num_id - 选择的快照数量ID(0=关闭)
     * @param   array data_disk - 额外数据盘大小
     * @param   string duration - 所选周期(onetime_fee=一次性就是)
     */
    public function cartCalculatePrice($params){
        $param = $params['custom'];

        $dataCenter = [];
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $dataCenter = DataCenterModel::where('product_id', $param['product_id'])->where('id', $param['data_center_id'])->find();
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>'数据中心错误'];
            }
        }
        $PackageModel = PackageModel::where('product_id', $param['product_id'])->where('id', $param['package_id'])->find();
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        if(!empty($PackageModel['data_center_id']) && !empty($dataCenter) && $dataCenter['id'] != $PackageModel['data_center_id']){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }

        $productId = $PackageModel['product_id'];
        if($PackageModel[ $param['duration'] ] === ''){
            return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
        }
        // 看是否是商品设置的周期
        if($params['product']['pay_type'] == 'onetime'){
            if($param['duration'] != 'onetime_fee'){
                return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
            }
        }else if($params['product']['pay_type'] == 'recurring_prepayment' || $params['product']['pay_type'] == 'recurring_postpaid'){
            if($param['duration'] == 'onetime_fee'){
                return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
            }
        }

        $image = ImageModel::where('id', $param['image_id'])->where('enable', 1)->find();
        // 验证镜像
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
        }
        // 有密钥密钥优先
        if(isset($param['ssh_key_id']) && !empty($param['ssh_key_id'])){
            $sshKey = IdcsmartSshKeyModel::where('client_id', get_client_id())->where('id', $param['ssh_key_id'])->find();
            if(empty($sshKey)){
                return ['status'=>400, 'msg'=>lang_plugins('ssh_key_error')];
            }
        }else{
            $param['ssh_key_id'] = 0;
        }
        $config = ConfigModel::where('product_id', $productId)->find();

        // 套餐的价格+额外数据盘+备份+快照+镜像
        $price = $PackageModel[ $param['duration'] ];
        // 镜像
        if($image['charge'] == 1 && !empty($image['price'])){
            $price = bcadd($price, $image['price']);
        }
        // 数据盘+备份快照
        $otherPrice = 0;
        if($config['backup_enable'] == 1){
            if(isset($param['backup_num_id']) && !empty($param['backup_num_id'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'backup')->where('id', $param['backup_num_id'])->find();
                if(empty($BackupConfigModel)){
                    return ['status'=>400, 'msg'=>lang_plugins('备份功能选择错误')];
                }
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
            }else if(isset($param['backup_num']) && !empty($param['backup_num'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'backup')->where('id', $param['backup_num'])->find();
                if(empty($BackupConfigModel)){
                    return ['status'=>400, 'msg'=>lang_plugins('备份功能选择错误')];
                }
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
            }
        }
        if($config['snap_enable'] == 1){
            if(isset($param['snap_num_id']) && !empty($param['snap_num_id'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'snap')->where('id', $param['snap_num_id'])->find();
                if(empty($BackupConfigModel)){
                    return ['status'=>400, 'msg'=>lang_plugins('快照功能选择错误')];
                }
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
            }else if(isset($param['snap_num']) && !empty($param['snap_num'])){
                $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('type', 'snap')->where('num', $param['snap_num'])->find();
                if(empty($BackupConfigModel)){
                    return ['status'=>400, 'msg'=>lang_plugins('快照功能选择错误')];
                }
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
            }
        }
        if($config['buy_data_disk'] == 1){
            if(isset($param['data_disk']) && is_array($param['data_disk'])){
                if(count($param['data_disk']) > $config['disk_max_num']){
                    return ['status'=>400, 'msg'=>'最多只可购买'.$config['disk_max_num'].'个额外磁盘'];
                }
                $size = 0;
                foreach($param['data_disk'] as $v){
                    if(!is_int($v)){
                        return ['status'=>400, 'msg'=>'额外数据盘大小只能是数字'];
                    }
                    if(!empty($config['disk_min_size']) && $v < $config['disk_min_size']){
                        return ['status'=>400, 'msg'=>'额外数据盘大小错误'];
                    }
                    if(!empty($config['disk_max_size']) && $v > $config['disk_max_size']){
                        return ['status'=>400, 'msg'=>'额外数据盘大小错误'];
                    }
                    if($v%10 != 0){
                        return ['status'=>400, 'msg'=>'磁盘大小只能是10的倍数'];
                    }
                    $size += $v;
                }
                if($size > 0){
                    $otherPrice = bcadd($otherPrice, $size/10 * $config['price']);
                }
            }
        }
        
        $duration = [
            'onetime_fee'=>[
                'name'=>'一次性',
                'num'=>1,
                'time'=>0,
            ],
            'month_fee'=>[
                'name'=>'月',
                'num'=>1,
                'time'=>30*24*3600,
            ],
            'quarter_fee'=>[
                'name'=>'季度',
                'num'=>3,
                'time'=>90*24*3600,
            ],
            'half_year_fee'=>[
                'name'=>'半年',
                'num'=>6,
                'time'=>180*24*3600,
            ], 
            'year_fee'=>[
                'name'=>'年',
                'num'=>12,
                'time'=>365*24*3600,
            ],
            'two_year'=>[
                'name'=>'两年',
                'num'=>24,
                'time'=>2*365*24*3600,
            ],
            'three_year'=>[
                'name'=>'三年',
                'num'=>36,
                'time'=>3*365*24*3600,
            ],
        ];
        $price = bcadd($price, bcmul($otherPrice, $duration[ $param['duration'] ]['num']));

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'price'=>$price,
                'billing_cycle'=>$duration[ $param['duration'] ]['name'],
                'duration'=>$duration[ $param['duration'] ]['time'],
                'description'=>$PackageModel['description'],
                'content'=>$PackageModel['description'],
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
            return ['status'=>400, 'msg'=>lang_plugins('产品未开通')];
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
            return ['status'=>400, 'msg'=>lang_plugins('产品未开通')];
        }
        // 验证套餐
        $package = $this->find($param['package_id']);
        if(empty($package) || $package['product_id'] != $host['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('套餐不存在')];
        }
        if($param['package_id'] == $hostLink['package_id']){
            return ['status'=>400, 'msg'=>lang_plugins('套餐未变动')];
        }
        // TODO 是否需要验证数据中心

        $oldPackage = PackageModel::find($hostLink['package_id']);
        
        // 获取当前周期
        if($host['billing_cycle'] == 'onetime'){
            $oldPrice = $oldPackage['onetime_fee'];
            $newPrice = $package['onetime_fee'];
        }else{
            // 计算周期
            $days = $host['billing_cycle_time']/24/3600;

            $duration = [
                '0'=>'onetime_fee',
                '30'=>'month_fee',
                '90'=>'quarter_fee',
                '180'=>'half_year_fee',
                '365'=>'year_fee',
                '720'=>'two_year_fee',
                '1085'=>'three_year_fee',
            ];

            $oldPrice = $oldPackage[ $duration[$days] ] ?? 0;
            $newPrice = $package[ $duration[$days] ] ?? 0;

            if(!isset($package[ $duration[$days] ]) || !is_numeric($package[ $duration[$days] ])){
                return ['status'=>400, 'msg'=>'该套餐不支持当前周期'];
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
     * @title 获取当前配置所有周期价格
     * @desc 获取当前配置所有周期价格
     * @author hh
     * @version v1
     * @param   int param.hostId - 产品ID
     * @return  array data.list - 列表数据
     * @return  string data.list[].id - 周期价格标识
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
        $productId = $PackageModel['product_id'];

        $ConfigModel = ConfigModel::where('product_id', $productId)->find();

        // 先计算其他价格,备份快照+磁盘
        $otherPrice = 0;
        if($hostLink['backup_num']>0){
            $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('num', $hostLink['backup_num'])->where('type', 'backup')->find();
            if(!empty($BackupConfigModel)){
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
            }
        }
        if($hostLink['snap_num']>0){
            $BackupConfigModel = BackupConfigModel::where('product_id', $productId)->where('num', $hostLink['snap_num'])->where('type', 'snap')->find();
            if(!empty($BackupConfigModel)){
                $otherPrice = bcadd($otherPrice, $BackupConfigModel['price']);
            }
        }
        // 计算磁盘价格
        $IdcsmartCloud = new IdcsmartCloud($param['server']);
        $detail = $IdcsmartCloud->cloudDetail($hostLink['rel_id']);
        if($detail['status'] != 200){
            return ['status'=>400, 'msg'=>'实例状态异常,请稍后重试'];
        }
        $size = 0;
        foreach($detail['data']['disk'] as $v){
            if($v['type'] == 'data' && $v['id'] != $hostLink['free_disk_id']){
                $size += $v['size'];
            }
        }
        if($size>0){
            $otherPrice = bcadd($otherPrice, $size/10*$ConfigModel['price']);
        }

        $duration = [
            'month_fee'=>[
                'num'=>1,
                'duration'=>30*24*3600,
                'desc'=>'月',
            ],
            'quarter_fee'=>[
                'num'=>3,
                'duration'=>90*24*3600,
                'desc'=>'季度',
            ],
            'half_year_fee'=>[
                'num'=>6,
                'duration'=>180*24*3600,
                'desc'=>'半年',
            ],
            'year_fee'=>[
                'num'=>12,
                'duration'=>365*24*3600,
                'desc'=>'年',
            ],
            'two_year'=>[
                'num'=>24,
                'duration'=>730*24*3600,
                'desc'=>'两年',
            ],
            'three_year'=>[
                'num'=>36,
                'duration'=>1095*24*3600,
                'desc'=>'三年',
            ],
        ];

        $durationPrice = [];
        foreach($duration as $k=>$v){
            if(is_numeric($PackageModel[ $k ] )){
                $price = bcadd($PackageModel[ $k ], $otherPrice * $v['num']);

                $durationPrice[] = [
                    'id'=>$k,
                    'duration'=>$v['duration'],
                    'billing_cycle'=>$v['desc'],
                    'price'=>amount_format($price),
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





}