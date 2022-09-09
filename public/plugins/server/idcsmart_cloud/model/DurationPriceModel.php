<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;
use addon\idcsmart_cloud\model\IdcsmartVpcModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;

class DurationPriceModel extends Model{

	protected $name = 'module_idcsmart_cloud_duration_price';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'duration'      => 'int',
        'duration_name' => 'string',
        'display_name'  => 'string',
        'cal_ratio'     => 'float',
        'bw_ratio'      => 'float',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2022-06-17
     * @title 添加默认周期价格
     * @desc 添加默认周期价格
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  bool
     */
    public function defaultAdd($product_id){
        $add = $this->where('product_id', $product_id)->find();
        if(!empty($add)){
            return true;
        }
        $time = time();
        $data = [
            [
                'product_id'    => $product_id,
                'duration'      => 30,
                'duration_name' => 'month',
                'display_name'  => '月',
                'cal_ratio'     => 1,
                'bw_ratio'      => 1,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $product_id,
                'duration'      => 90,
                'duration_name' => 'quarterly',
                'display_name'  => '季度',
                'cal_ratio'     => 3,
                'bw_ratio'      => 3,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $product_id,
                'duration'      => 180,
                'duration_name' => 'half_year',
                'display_name'  => '半年',
                'cal_ratio'     => 6,
                'bw_ratio'      => 6,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $product_id,
                'duration'      => 365,
                'duration_name' => 'year',
                'display_name'  => '年',
                'cal_ratio'     => 10,
                'bw_ratio'      => 12,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $product_id,
                'duration'      => 3*365,
                'duration_name' => 'three_year',
                'display_name'  => '3年',
                'cal_ratio'     => 30,
                'bw_ratio'      => 36,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $product_id,
                'duration'      => 5*365,
                'duration_name' => 'five_year',
                'display_name'  => '5年',
                'cal_ratio'     => 50,
                'bw_ratio'      => 60,
                'create_time'   => $time,
            ]
        ];
        $this->saveAll($data);
        return true;
    }

    /**
     * 时间 2022-06-17
     * @title 获取周期价格
     * @desc 获取周期价格
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 周期价格ID
     * @return  int data.list[].duration - 时长(天)
     * @return  string data.list[].display_name - 时长显示名称
     * @return  float data.list[].cal_ratio - 计算型号比例
     * @return  float data.list[].bw_ratio - 带宽比例
     */
    public function durationPriceList($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        // 在这里设置还是其他时候
        if($ProductModel->getModule() == 'idcsmart_cloud'){
            $this->defaultAdd($param['product_id']);
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $durationPrice = $this
                        ->field('id,duration,display_name,cal_ratio,bw_ratio')
                        ->where($where)
                        ->order('duration', 'asc')
                        ->select()
                        ->toArray();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $durationPrice
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 保存所有周期价格
     * @desc 保存所有周期价格
     * @author hh
     * @version v1
     * @param array param.data - 所有周期价格数据 require
     * @param int   param.data[].id - 周期价格ID require
     * @param string  param.data[].display_name - 显示值
     * @param float   param.data[].cal_ratio - 计算型号比例
     * @param float   param.data[].bw_ratio - 带宽比例
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function saveDurationPrice($param){
        $id = array_column($param['data'], 'id');

        $durationPrice = $this
                        ->whereIn('id', $id)
                        ->select()
                        ->toArray();
        if(count($durationPrice) != count($id)){
            return ['status'=>400, 'msg'=>lang_plugins('id_error')];
        }
        // 暂时不能修改时长
        foreach($param['data'] as $v){
            $v['update_time'] = time();
            if(isset($v['display_name']) && trim($v['display_name']) === ''){
                unset($v['display_name']);
            }
            if(isset($v['cal_ratio']) && empty($v['cal_ratio'])){
                unset($v['cal_ratio']);
            }
            if(isset($v['bw_ratio']) && empty($v['bw_ratio'])){
                unset($v['bw_ratio']);
            }
            $this->update($v, ['id'=>$v['id']], ['display_name','cal_ratio','bw_ratio','update_time']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2022-06-22
     * @title 根据配置获取周期价格
     * @desc 根据配置获取周期价格
     * @author hh
     * @version v1
     * @param   int param.package_id - 套餐ID require
     * @param   int param.image_id 0 镜像ID
     * @param   int param.backup_enable 0 启用自动备份(0=不启用,1=启用)
     * @param   int param.panel_enable 0 启用独立控制面板(0=不启用,1=启用)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 周期价格ID
     * @return  string data.list[].display_name - 周期名称
     * @return  string data.list[].price - 周期价格
     */
    public function configDurationPrice($param){
        $PackageModel = PackageModel::find($param['package_id'] ?? 0);
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $productId = $PackageModel['product_id'];

        // 获取所有周期价格,暂时只有前付
        $durationPrice = $this
                        ->field('id,display_name,cal_ratio,bw_ratio')
                        ->where('product_id', $productId)
                        ->where('pay_type', 'recurring_prepayment')
                        ->select()
                        ->toArray();

        $cal = CalModel::find($PackageModel['module_idcsmart_cloud_cal_id']);
        $bw = BwModel::find($PackageModel['module_idcsmart_cloud_bw_id']);
        if(empty($cal) || empty($bw)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cal_or_bw_error')];
        }
        $config = ConfigModel::where('product_id', $productId)->find();
        if($config['backup_enable'] == 0 && isset($param['backup_enable']) && $param['backup_enable'] == 1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_enable_auto_backup')];
        }
        if($config['panel_enable'] == 0 && isset($param['panel_enable']) && $param['panel_enable'] == 1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_enable_panel')];
        }

        // 用镜像时
        if(isset($param['image_id']) && !empty($param['image_id'])){
            $image = ImageModel::find($param['image_id']);
            // 验证镜像
            if(empty($image) || $image['enable'] == 0){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }
        }else{
            $image['charge'] = 0;
        }
        foreach($durationPrice as $k=>$v){
            $price = bcadd(bcmul($v['cal_ratio'], $cal['price']), bcmul($v['bw_ratio'], $bw['price']));
            if(isset($param['backup_enable']) && $param['backup_enable'] == 1){
                $price = bcadd($price, bcmul($v['cal_ratio'], $config['backup_price']));
            }
            if(isset($param['panel_enable']) && $param['panel_enable'] == 1){
                $price = bcadd($price, bcmul($v['cal_ratio'], $config['panel_price']));
            }
            if($image['charge'] == 1 && !empty($image['price'])){
                $price = bcadd($price, $image['price']);
            }
            $durationPrice[$k]['price'] = amount_format($price);
            unset($durationPrice[$k]['cal_ratio'], $durationPrice[$k]['bw_ratio']);
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'list'=>$durationPrice
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
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function cartCalculatePrice($param){
        $PackageModel = PackageModel::find($param['package_id'] ?? 0);
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $productId = $PackageModel['product_id'];

        // 获取所有周期价格,暂时只有前付
        $durationPrice = $this
                        ->where('product_id', $productId)
                        ->where('pay_type', 'recurring_prepayment')
                        ->where('id', $param['duration_price_id'] ?? 0)
                        ->find();
        if(empty($durationPrice)){
            return ['status'=>400, 'msg'=>lang_plugins('duration_error')];
        }
        $image = ImageModel::find($param['image_id'] ?? 0);
        // 验证镜像
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
        }
        // 镜像是否禁用
        $ImageDataCenterLinkModel = ImageDataCenterLinkModel::where('module_idcsmart_cloud_image_id', $image['id'])
                                ->where('module_idcsmart_cloud_data_center_id', $param['data_center_id'])
                                ->find();
        if(empty($ImageDataCenterLinkModel) || $ImageDataCenterLinkModel['enable'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
        }
        // 验证vpc
        if(isset($param['vpc_id']) && !empty($param['vpc_id'])){
            $vpc = IdcsmartVpcModel::where('id', $param['vpc_id'])
                    ->where('client_id', get_client_id() )
                    ->where('module_idcsmart_cloud_data_center_id', $param['data_center_id'])
                    ->find();
            if(empty($vpc)){
                return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
            }
        }
        // 验证
        if(isset($param['security_group_id']) && !empty($param['security_group_id'])){
            $securityGroup = IdcsmartSecurityGroupModel::where('id', $param['security_group_id'])
                            ->where('client_id', get_client_id() )
                            ->find();
            if(empty($securityGroup)){
                return ['status'=>400, 'msg'=>lang_plugins('security_group_not_found')];
            }
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
        if($config['backup_enable'] == 0 && isset($param['backup_enable']) && $param['backup_enable'] == 1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_enable_auto_backup')];
        }
        if($config['panel_enable'] == 0 && isset($param['panel_enable']) && $param['panel_enable'] == 1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_enable_panel')];
        }

        $cal = CalModel::find($PackageModel['module_idcsmart_cloud_cal_id']);
        $bw = BwModel::find($PackageModel['module_idcsmart_cloud_bw_id']);

        // 镜像和其他配置价格
        $price = bcadd(bcmul($durationPrice['cal_ratio'], $cal['price']), bcmul($durationPrice['bw_ratio'], $bw['price']));
        if(isset($param['backup_enable']) && $param['backup_enable'] == 1){
            $price = bcadd($price, bcmul($durationPrice['cal_ratio'], $config['backup_price']));
        }
        if(isset($param['panel_enable']) && $param['panel_enable'] == 1){
            $price = bcadd($price, bcmul($durationPrice['cal_ratio'], $config['panel_price']));
        }
        if($image['charge'] == 1 && !empty($image['price'])){
            $price = bcadd($price, $image['price']);
        }
        $price = amount_format($price);
        
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'price'=>$price,
                'billing_cycle'=>$durationPrice['display_name'],
                'duration'=>$durationPrice['duration']*24*3600,
                'description'=>$cal['description'].$bw['description'],
                'content'=>$cal['description'].$bw['description'],  // TODO 
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 价格计算公式
     * @desc 价格计算公式
     * @author hh
     * @version v1
     * @param   float param.cal_ratio - 计算比例 require
     * @param   float param.bw_ratio - 带宽比例 require
     * @param   float param.cal_price - 计算单价 require
     * @param   float param.bw_price - 带宽单价 require
     * @param   int param.backup_enable - 是否启用备份
     * @param   float param.backup_price - 备份价格
     * @param   int param.panel_enable - 是否启用面板
     * @param   float param.panel_price - 面板价格
     * @param   float param.image_price - 镜像价格
     */
    public function calFormula($param){
        $price = bcadd(bcmul($param['cal_ratio'], $param['cal_price']), bcmul($param['bw_ratio'], $param['bw_price']));
        if(isset($param['backup_enable']) && $param['backup_enable'] == 1){
            $price = bcadd($price, bcmul($param['cal_ratio'], $param['backup_price']));
        }
        if(isset($param['panel_enable']) && $param['panel_enable'] == 1){
            $price = bcadd($price, bcmul($param['cal_ratio'], $param['panel_price']));
        }
        if(isset($param['image_price']) && !empty($param['image_price'])){
            $price = bcadd($price, $param['image_price']);
        }
        return $price;
    }

    /**
     * 时间 2022-06-22
     * @title 根据配置获取周期价格
     * @desc 根据配置获取周期价格
     * @author hh
     * @version v1
     * @param   int param.package_id - 套餐ID require
     * @param   int param.image_id 0 镜像ID
     * @param   int param.backup_enable 0 启用自动备份(0=不启用,1=启用)
     * @param   int param.panel_enable 0 启用独立控制面板(0=不启用,1=启用)
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 周期价格ID
     * @return  string data.list[].display_name - 周期名称
     * @return  string data.list[].price - 周期价格
     */
    public function currentDurationPrice($hostId){
        $hostLink = HostLinkModel::where('host_id', $hostId)->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_found')];
        }

        $param = [
            'package_id'=>$hostLink['module_idcsmart_cloud_package_id'],
            'backup_enable'=>$hostLink['backup_enable'],
            'panel_enable'=>$hostLink['panel_enable'],
            'image_id'=>$hostLink['module_idcsmart_cloud_image_id'],
        ];

        $PackageModel = PackageModel::find($param['package_id'] ?? 0);
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
        }
        $productId = $PackageModel['product_id'];

        // 获取所有周期价格,暂时只有前付
        $durationPrice = $this
                        ->field('duration,display_name billing_cycle,cal_ratio,bw_ratio')
                        ->where('product_id', $productId)
                        ->where('pay_type', 'recurring_prepayment')
                        ->select()
                        ->toArray();

        $cal = CalModel::find($PackageModel['module_idcsmart_cloud_cal_id']);
        $bw = BwModel::find($PackageModel['module_idcsmart_cloud_bw_id']);
        if(empty($cal) || empty($bw)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cal_or_bw_error')];
        }
        $config = ConfigModel::where('product_id', $productId)->find();
        if($config['backup_enable'] == 0 && isset($param['backup_enable']) && $param['backup_enable'] == 1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_enable_auto_backup')];
        }
        if($config['panel_enable'] == 0 && isset($param['panel_enable']) && $param['panel_enable'] == 1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_enable_panel')];
        }

        // 用镜像时
        if(isset($param['image_id']) && !empty($param['image_id'])){
            $image = ImageModel::find($param['image_id']);
            // 验证镜像
            if(empty($image) || $image['enable'] == 0){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }
        }else{
            $image['charge'] = 0;
        }
        foreach($durationPrice as $k=>$v){
            $price = bcadd(bcmul($v['cal_ratio'], $cal['price']), bcmul($v['bw_ratio'], $bw['price']));
            if(isset($param['backup_enable']) && $param['backup_enable'] == 1){
                $price = bcadd($price, bcmul($v['cal_ratio'], $config['backup_price']));
            }
            if(isset($param['panel_enable']) && $param['panel_enable'] == 1){
                $price = bcadd($price, bcmul($v['cal_ratio'], $config['panel_price']));
            }
            if($image['charge'] == 1 && !empty($image['price'])){
                $price = bcadd($price, $image['price']);
            }
            $durationPrice[$k]['duration'] = $v['duration']*24*3600;
            $durationPrice[$k]['price'] = amount_format($price);
            unset($durationPrice[$k]['cal_ratio'], $durationPrice[$k]['bw_ratio']);
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$durationPrice,
        ];
        return $result;
    }


}