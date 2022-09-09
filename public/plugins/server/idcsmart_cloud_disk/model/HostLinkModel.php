<?php 
namespace server\idcsmart_cloud_disk\model;

use think\Model;
use server\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use app\common\model\HostModel;
use server\idcsmart_cloud\model\HostLinkModel AS HLM;
use app\common\model\OrderModel;

class HostLinkModel extends Model{

	protected $name = 'module_idcsmart_cloud_disk_host_link';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'                                    => 'int',
        'module_idcsmart_cloud_disk_package_id' => 'int',
        'host_id'                               => 'int',
        'rel_id'                                => 'int',
        'rel_host_id'                           => 'int',
        'size'                                  => 'int',
        'mount_enable'                          => 'int',
        'file_system'                           => 'string',
        'mount_path'                            => 'string',
        'status'                                => 'int',
        'create_time'                           => 'int',
        'update_time'                           => 'int',
    ];


    # 魔方云磁盘产品列表
    public function idcsmartCloudDiskList($param){
        $clientId = get_client_id();

        if(empty($clientId)){
            return ['list'  => [], 'count' => []];
        }
        
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','due_time','status']) ? $param['orderby'] : 'id';
        $param['orderby'] = 'h.'.$param['orderby'];  

        $where = [];
        $where[] = ['h.client_id', '=', $clientId];
        if(!empty($param['keywords'])){
            $where[] = ['ch.name|h.name', 'LIKE', '%'.$param['keywords'].'%'];
        }
        if(!empty($param['host_id'])){
            $where[] = ['ch.id', '=', $param['host_id']];
        }

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('host ch', 'hl.rel_host_id=ch.id')
            ->where($where)
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,hl.size,ch.name cloud_name,h.first_payment_amount,h.billing_cycle_name,hl.create_time')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('host ch', 'hl.rel_host_id=ch.id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();
        
        return ['list'  => $host, 'count' => $count];
    }

    # 挂载磁盘
    public function mountIdcsmartCloudDisk($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>'host'];
        }
        $params = $host->getModuleParams();

        $hostLink = HLM::where('host_id', $param['host_id'])->find();
        $params['cloud'] = $hostLink['rel_id'] ?? 0;
        if(empty($params['cloud'])){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_id_is_empty')];
        }

        $diskHostLink = $this->where('host_id', $param['id'])->find();
        $id = $diskHostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_disk_id_is_empty')];
        }

        $IC = new IC($params['server']);
        $res = $IC->diskMount($id, $params);
        if($res['status']==200){
            $this->update([
                'status'=>1, 
                'rel_host_id'=>$param['host_id']
            ], ['host_id'=>$param['id']]);
        }
        return $res;
    }

    # 卸载磁盘
    public function umountIdcsmartCloudDisk($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>'host'];
        }
        $params = $host->getModuleParams();

        $diskHostLink = $this->where('host_id', $param['id'])->find();
        $diskId = $diskHostLink['rel_id'] ?? 0;
        if(empty($diskId)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_disk_id_is_empty')];
        }
        if(empty($diskHostLink['rel_host_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_be_related_host')];
        }
        
        
        $hostLink = HLM::where('host_id', $diskHostLink['rel_host_id'])->find();
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_id_is_empty')];
        }

        $IC = new IC($params['server']);
        $res = $IC->cloudUmountDisk($id, $diskId);
        if($res['status']==200){
            $this->update([
                'status'=>0, 
                'rel_host_id'=>0
            ], ['host_id'=>$param['id']]);
        }
        return $res;
    }

    # 删除磁盘
    /*public function deleteIdcsmartCloudDisk($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>'host'];
        }
        $params = $host->getModuleParams();

        $diskHostLink = $this->where('host_id', $param['id'])->find();
        $id = $diskHostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_disk_id_is_empty')];
        }

        $IC = new IC($params['server']);
        $res = $IC->diskDelete($id);
        return $res;
    }*/

    # 扩容磁盘
    public function expansionIdcsmartCloudDisk($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>'host'];
        }
        $params['type'] = 'upgrade_config';
        $params['host_id'] = $param['id'];
        $params['config_options'] = ['size' => $param['size']];
        $params['amount'] = $amount ?? 0;
        $params['description'] = $description ?? '';
        $params['client_id'] = get_client_id();

        $OrderModel = new OrderModel();
        $res = $OrderModel->createOrder($params);
        return $res;
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
        $hostLink = $this->where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('产品未开通')];
        }
        // 验证套餐
        $package = PackageModel::find($param['package_id']);
        if(empty($package) || $package['product_id'] != $host['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('套餐不存在')];
        }
        /*if($param['package_id'] == $hostLink['module_idcsmart_cloud_package_id']){
            return ['status'=>400, 'msg'=>lang_plugins('套餐未变动')];
        }*/

        // 获取当前周期
        $durationPrice = DurationPriceModel::where('duration', $host['billing_cycle_time']/24/3600)
                            ->where('product_id', $host['product_id'])
                            ->find();
        if(empty($durationPrice)){
            return ['status'=>400, 'msg'=>lang_plugins('周期错误')];
        }

        // $config = ConfigModel::where('product_id', $host['product_id'])->find();

        $newConfig = [
            'disk_ratio'=>$durationPrice['disk_ratio'],
            'price'=>$package['price'],
            'size'=>$param['size'],
        ];

        $DurationPriceModel = new DurationPriceModel();
        $newPrice = $DurationPriceModel->calFormula($newConfig);
        
        // 获取原配置价格
        $oldPackage = PackageModel::find($hostLink['module_idcsmart_cloud_package_id']);

        // 
        $oldConfig = [
            'disk_ratio'=>$durationPrice['disk_ratio'],
            'price'=>$oldPackage['price'],
            'size'=>$hostLink['size'],
        ];

        $oldPrice = $DurationPriceModel->calFormula($oldConfig);

        $diffTime = $host['due_time'] - time();

        $diffPrice = $newPrice - $oldPrice*(($diffTime < 0 ? 0 : $diffTime)/$durationPrice['duration']/24/3600);
        $diffPrice = amount_format($diffPrice);
        $description = lang_plugins('package_change_description', ['{old_package}'=>$oldPackage['name'], '{new_package}'=>$package['name']]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $diffPrice,
                'description' => $description,
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
            'config_options' => [
                'type'       => 'change_package',
                'package_id' => $param['package_id'],
                'size' => $param['size'],
            ]
        ];
        return $OrderModel->createOrder($data);
    }

}