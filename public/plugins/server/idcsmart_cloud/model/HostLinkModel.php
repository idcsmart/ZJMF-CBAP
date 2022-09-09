<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use server\idcsmart_cloud\logic\ToolLogic;
use addon\idcsmart_cloud\model\IdcsmartVpcModel;
use addon\idcsmart_cloud\model\IdcsmartVpcLinkModel;
use addon\idcsmart_cloud\model\IdcsmartVpcHostLinkModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;

class HostLinkModel extends Model{

	protected $name = 'module_idcsmart_cloud_host_link';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'                                    => 'int',
        'host_id'                               => 'int',
        'rel_id'                                => 'int',
        'ip'                                    => 'string',
        'module_idcsmart_cloud_data_center_id'  => 'int',
        'module_idcsmart_cloud_image_id'        => 'int',
        'password'                              => 'string',
        'backup_enable'                         => 'int',
        'snap_enable'                           => 'int',
        'panel_enable'                          => 'int',
        'module_idcsmart_cloud_package_id'      => 'int',
        'power_status'                          => 'string',
        'vpc_ip'                                => 'string',
    ];


    /**
     * 时间 2022-06-24
     * @title 魔方云产品列表页
     * @desc 魔方云产品列表页
     * @author hh
     * @version v1
     * @param   int param.page 1 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序(id,due_time,status)
     * @param   string param.sort - 升/降序
     * @param   string param.keywords - 关键字搜索
     * @param   int param.data_center_id - 数据中心搜索
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 列表数据
     * @return  string data.list[].name - 产品标识
     * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除,Failed=开通失败)
     * @return  int data.list[].due_time - 到期时间
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  string data.list[].package_name - 套餐名称
     * @return  string data.list[].ip - IP
     * @return  string data.list[].image_name - 镜像名称
     * @return  string data.list[].icon - 镜像图标
     * @return  string data.list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     */
    public function idcsmartCloudList($param){
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => [],
                'count' => [],
            ]
        ];

        $clientId = get_client_id();

        if(empty($clientId)){
            return $result;
        }
        
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','due_time','status']) ? $param['orderby'] : 'id';
        $param['orderby'] = 'h.'.$param['orderby'];  

        $where = [];
        $where[] = ['h.client_id', '=', $clientId];
        if(isset($param['keywords']) && trim($param['keywords']) !== ''){
            $where[] = ['p.name|h.name|hl.ip', 'LIKE', '%'.$param['keywords'].'%'];
        }
        if(!empty($param['data_center_id'])){
            $where[] = ['hl.module_idcsmart_cloud_data_center_id', '=', $param['data_center_id']];
        }

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('module_idcsmart_cloud_data_center dc', 'hl.module_idcsmart_cloud_data_center_id=dc.id')
            ->leftJoin('module_idcsmart_cloud_package p', 'hl.module_idcsmart_cloud_package_id=p.id')
            ->leftJoin('module_idcsmart_cloud_image i', 'hl.module_idcsmart_cloud_image_id=i.id')
            ->where($where)
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,p.name package_name,hl.ip,hl.power_status,i.name image_name,i.image_type,i.icon,ig.name image_group_name')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('module_idcsmart_cloud_data_center dc', 'hl.module_idcsmart_cloud_data_center_id=dc.id')
            ->leftJoin('module_idcsmart_cloud_package p', 'hl.module_idcsmart_cloud_package_id=p.id')
            ->leftJoin('module_idcsmart_cloud_image i', 'hl.module_idcsmart_cloud_image_id=i.id')
            ->leftJoin('module_idcsmart_cloud_image_group ig', 'i.module_idcsmart_cloud_image_group_id=ig.id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();

        foreach($host as $k=>$v){
            if($v['image_type'] == 'system'){
                $host[$k]['icon'] = 'plugins/server/idcsmart_cloud/view/img/'.$v['image_group_name'].'.png';
            }else if(!empty($v['icon'])){
                $host[$k]['icon'] = 'plugins/server/idcsmart_cloud/view/img/'.$v['icon'];
            }
            unset($host[$k]['image_type'], $host[$k]['image_group_name']);
        }
        
        $result['data']['list']  = $host;
        $result['data']['count'] = $count;
        return $result;
    }

    /**
     * 时间 2022-06-30
     * @title 详情
     * @desc 详情
     * @author hh
     * @version v1
     * @param   int $hostId - 产品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.rel_id - 魔方云ID
     * @return  string data.ip - IP地址
     * @return  int data.backup_enable - 是否启用自动备份(0=未启用,1=启用)
     * @return  int data.panel_enable - 是否启用独立面板控制(0=未启用,1=启用)
     * @return  string data.power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.cal.id - 计算型号ID
     * @return  string data.cal.name - 计算型号名称
     * @return  int data.bw.id - 带宽ID
     * @return  int data.bw.bw - 带宽
     * @return  string data.bw.bw_type_name - 带宽类型名称
     * @return  int data.data_center.id - 数据中心ID
     * @return  string data.data_center.country - 国家
     * @return  string data.data_center.country_code - 国家代码
     * @return  string data.data_center.city - 城市
     * @return  string data.data_center.area - 区域
     * @return  int data.image.id - 镜像ID
     * @return  string data.image.name - 镜像名称
     * @return  string data.image.icon - 镜像图标
     * @return  int data.package.id - 套餐ID
     * @return  string data.package.name - 套餐名称
     */
    public function detail($hostId){
        $res = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[]
        ];
        $host = HostModel::find($hostId);
        if(empty($host)){
            return $res;
        }
        if(app('http')->getName() == 'home' && $host['client_id'] != get_client_id()){
            return $res;
        }
        $hostLink = $this->where('host_id', $hostId)->find();
        $res = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[]
        ];
        if(!empty($hostLink)){
            $data = [];
            $data['rel_id'] = $hostLink['rel_id'];
            $data['ip'] = $hostLink['ip'];
            $data['backup_enable'] = $hostLink['backup_enable'];
            $data['panel_enable'] = $hostLink['panel_enable'];
            $data['power_status'] = $hostLink['power_status'];

            $package = PackageModel::find($hostLink['module_idcsmart_cloud_package_id']);
            $cal = CalModel::field('id,name,cpu,memory,disk_size')->find($package['module_idcsmart_cloud_cal_id'] ?? 0);
            $bw = BwModel::alias('b')
                ->field('b.id,b.bw,b.flow,bt.name bw_type_name')
                ->leftJoin('module_idcsmart_cloud_bw_type bt', 'b.module_idcsmart_cloud_bw_type_id=bt.id')
                ->where('b.id', $package['module_idcsmart_cloud_bw_id'] ?? 0)
                ->find();
            $dataCenter = DataCenterModel::field('id,country,country_code,city,area')->find($hostLink['module_idcsmart_cloud_data_center_id']);
            $image = ImageModel::field('id,name,icon')->find($hostLink['module_idcsmart_cloud_image_id']);

            $data['cal'] = $cal ?? (object)[];

            $data['bw'] = $bw ?? (object)[];
            
            $data['data_center'] = $dataCenter ?? (object)[];
            
            $data['image'] = $image ?? (object)[];

            $data['package'] = [
                'id'=>$package['id'] ?? 0,
                'name'=>$package['name'] ?? ''
            ];

            $res['data'] = $data;
        }
        return $res;
    }

    /**
     * 时间 2022-06-30
     * @title 获取VPC网络
     * @desc 获取VPC网络
     * @author hh
     * @version v1
     * @param   int id - 产品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.vpc_id - VPC网络ID
     * @return  string data.vpc_name - VPC网络名称
     * @return  string data.ip - IP地址
     * @return  string data.gateway - 网关
     * @return  string data.mask - 掩码
     */
    public function getVpcNetwork($id){
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[],
        ];
        $hostLink = $this->where('host_id', $id)->find();

        $vpcHostLink = IdcsmartVpcHostLinkModel::where('host_id', $id)->find();
        if(empty($vpcHostLink) || empty($vpcHostLink['addon_idcsmart_vpc_id'])){
            return $result;
        }
        $vpc = IdcsmartVpcModel::find($vpcHostLink['addon_idcsmart_vpc_id']);
        if(empty($vpc)){
            return $result;
        }

        $ipArr = explode('/', $vpc['ip']);

        $data = [
            'vpc_id'    => $vpc['id'],
            'vpc_name'  => $vpc['name'],
            'ip'        => $hostLink['vpc_ip'],
            'gateway'   => long2ip(ip2long($ipArr[0]) + 1),
            'mask'      => ToolLogic::numToSub($ipArr[1])
        ];
        $result['data'] = $data;
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
        $hostLink = $this->where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('产品未开通')];
        }
        // 验证套餐
        $package = PackageModel::find($param['package_id']);
        if(empty($package) || $package['product_id'] != $host['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('套餐不存在')];
        }
        if($param['package_id'] == $hostLink['module_idcsmart_cloud_package_id']){
            return ['status'=>400, 'msg'=>lang_plugins('套餐未变动')];
        }

        $packageDataCenterLink = PackageDataCenterLinkModel::where('module_idcsmart_cloud_package_id', $param['package_id'])->where('module_idcsmart_cloud_data_center_id', $hostLink['module_idcsmart_cloud_data_center_id'])->find();
        if(empty($packageDataCenterLink)){
            return ['status'=>400, 'msg'=>lang_plugins('当前区域不能使用该套餐')];
        }

        $cal = CalModel::find($package['module_idcsmart_cloud_cal_id']);
        $bw  = BwModel::find($package['module_idcsmart_cloud_bw_id']);
        $image = ImageModel::find($hostLink['module_idcsmart_cloud_image_id']);

        // 获取当前周期
        $durationPrice = DurationPriceModel::where('duration', $host['billing_cycle_time']/24/3600)
                            ->where('product_id', $host['product_id'])
                            ->find();
        if(empty($durationPrice)){
            return ['status'=>400, 'msg'=>lang_plugins('周期错误')];
        }

        // $config = ConfigModel::where('product_id', $host['product_id'])->find();

        $newConfig = [
            'cal_ratio'=>$durationPrice['cal_ratio'],
            'bw_ratio'=>$durationPrice['bw_ratio'],
            'cal_price'=>$cal['price'],
            'bw_price'=>$bw['price'],
            // 'backup_enable'=>$hostLink['backup_enable'],
            // 'panel_enable'=>$hostLink['panel_enable'],
            // 'backup_price'=>$config['backup_price'],
            // 'panel_price'=>$config['panel_price'],
            // 'image_price'=>$image['charge'] == 1 ? $image['price'] : 0,
        ];

        $DurationPriceModel = new DurationPriceModel();
        $newPrice = $DurationPriceModel->calFormula($newConfig);
        
        // 获取原配置价格
        $oldPackage = PackageModel::find($hostLink['module_idcsmart_cloud_package_id']);
    
        $oldCal = CalModel::find($oldPackage['module_idcsmart_cloud_cal_id']);
        $oldBw  = BwModel::find($oldPackage['module_idcsmart_cloud_bw_id']);

        // 
        $oldConfig = [
            'cal_ratio'=>$durationPrice['cal_ratio'],
            'bw_ratio'=>$durationPrice['bw_ratio'],
            'cal_price'=>$oldCal['price'],
            'bw_price'=>$oldBw['price'],
            // 'backup_enable'=>$hostLink['backup_enable'],
            // 'panel_enable'=>$hostLink['panel_enable'],
            // 'backup_price'=>$config['backup_price'],
            // 'panel_price'=>$config['panel_price'],
            // 'image_price'=>$image['charge'] == 1 ? $image['price'] : 0,
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
            ]
        ];
        return $OrderModel->createOrder($data);
    }

    # 获取所有实例
    public function getAllIdcsmartCloud()
    {
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => []
            ]
        ];

        $clientId = get_client_id();

        if(empty($clientId)){
            return $result;
        }
        

        $where = [];
        $where[] = ['h.client_id', '=', $clientId];

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name')
            ->join('host h', 'hl.host_id=h.id')
            ->where($where)
            ->group('h.id')
            ->select()
            ->toArray();
        
        $result['data']['list']  = $host;
        return $result;
    }

}