<?php 
namespace server\common_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
// use server\idcsmart_cloud\logic\ToolLogic;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use server\common_cloud\logic\CloudLogic;

class HostLinkModel extends Model{

	protected $name = 'module_common_cloud_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'host_id'           => 'int',
        'rel_id'            => 'int',
        'data_center_id'    => 'int',
        'image_id'          => 'int',
        'backup_num'        => 'int',
        'snap_num'          => 'int',
        'package_id'        => 'int',
        'package_data'      => 'string', // 暂时没用
        'create_time'       => 'int',
        'password'          => 'string',
        'ip'                => 'string',
        'power_status'      => 'string',
        'free_disk_id'      => 'int',
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
     * @param   string param.status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除,Failed=开通失败)
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 列表数据
     * @return  string data.list[].name - 产品标识
     * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除,Failed=开通失败)
     * @return  int data.list[].due_time - 到期时间
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].package_name - 套餐名称
     * @return  string data.list[].ip - IP
     * @return  string data.list[].image_name - 镜像名称
     * @return  string data.list[].image_group_name - 镜像分组名称
     * @return  string data.list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.list[].active_time - 开通时间
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
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $where[] = ['hl.data_center_id', '=', $param['data_center_id']];
        }
        if(isset($param['status']) && !empty($param['status'])){
            $where[] = ['h.status', '=', $param['status']];
        }

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('module_common_cloud_data_center dc', 'hl.data_center_id=dc.id')
            ->leftJoin('module_common_cloud_package p', 'hl.package_id=p.id')
            ->leftJoin('module_common_cloud_image i', 'hl.image_id=i.id')
            ->where($where)
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,h.active_time,h.due_time,c.name_zh country,c.iso country_code,dc.city,p.name package_name,hl.ip,hl.power_status,i.name image_name,ig.name image_group_name')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('module_common_cloud_data_center dc', 'hl.data_center_id=dc.id')
            ->leftJoin('country c', 'dc.country_id=c.id')
            ->leftJoin('module_common_cloud_package p', 'hl.package_id=p.id')
            ->leftJoin('module_common_cloud_image i', 'hl.image_id=i.id')
            ->leftJoin('module_common_cloud_image_group ig', 'i.image_group_id=ig.id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();

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
     * @return  int data.rel_id - 魔方云ID
     * @return  string data.ip - IP地址
     * @return  int data.backup_num - 允许备份数量
     * @return  int data.snap_num - 允许快照数量
     * @return  string data.power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.data_center.id - 数据中心ID
     * @return  string data.data_center.city - 城市
     * @return  string data.data_center.country_name - 国家
     * @return  string data.data_center.iso - 图标
     * @return  int data.image.id - 镜像ID
     * @return  string data.image.name - 镜像名称
     * @return  string data.image.image_group_name - 镜像分组
     * @return  int data.package.id - 套餐ID
     * @return  string data.package.name - 套餐名称
     * @return  string data.package.description - 套餐描述
     * @return  string data.package.cpu - cpu
     * @return  string data.package.memory - 内存(MB)
     * @return  string data.package.in_bw - 进带宽
     * @return  string data.package.out_bw - 出带宽
     * @return  string data.package.system_disk_size - 系统盘(GB)
     * @return  int data.security_group.id - 关联的安全组ID(0=没关联)
     * @return  string data.security_group.name - 关联的安全组名称
     * @return  string data.duration - 周期
     * @return  string data.first_payment_amount - 首付金额
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
        
        if(!empty($hostLink)){
            $data = [];
            $data['rel_id'] = $hostLink['rel_id'];
            $data['ip'] = $hostLink['ip'];
            $data['backup_num'] = $hostLink['backup_num'];
            $data['snap_num'] = $hostLink['snap_num'];
            $data['power_status'] = $hostLink['power_status'];
            $data['duration'] = $this->getDuration($host)['duration'];
            $data['first_payment_amount'] = $host['first_payment_amount'];

            $package = PackageModel::field('id,name,description,cpu,memory,in_bw,out_bw,system_disk_size')->where('id', $hostLink['package_id'])->find();
            
            $image = ImageModel::alias('i')
                    ->field('i.id,i.name,ig.name image_group_name')
                    ->leftJoin('module_common_cloud_image_group ig', 'i.image_group_id=ig.id')
                    ->where('i.id', $hostLink['image_id'])
                    ->find();

            if($image['image_group_name'] == 'Windows'){
                $data['username'] = 'administrator';
            }else{
                $data['username'] = 'root';
            }
            $data['password'] = aes_password_decode($hostLink['password']);

            try{
                $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();
                $securityGroupId = IdcsmartSecurityGroupHostLinkModel::where('host_id', $hostId)->value('addon_idcsmart_security_group_id');
                if(!empty($securityGroupId)){
                    $IdcsmartSecurityGroupModel = IdcsmartSecurityGroupModel::find($securityGroupId);
                }
            }catch(\Exception $e){
                $securityGroupId = 0;
            }

            $dataCenter = DataCenterModel::alias('dc')
                        ->field('dc.id,dc.city,c.name_zh country_name,c.iso')
                        ->leftJoin('country c', 'dc.country_id=c.id')
                        ->where('dc.id', $hostLink['data_center_id'])
                        ->find();

            $data['data_center'] = $dataCenter ?? (object)[];
            
            $data['image'] = $image ?? (object)[];

            $data['package'] = $package;

            if(!empty($securityGroupId)){
                $data['security_group'] = [
                    'id'=>$securityGroupId,
                    'name'=>$IdcsmartSecurityGroupModel['name'] ?? '',
                ];
            }else{
                $data['security_group'] = [
                    'id'=>0,
                    'name'=>'',
                ];
            }
            $res['data'] = $data;
        }
        return $res;
    }

    /**
     * 时间 2022-09-27
     * @title 获取当前周期标识
     * @desc
     * @url
     * @method  POST
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public static function getDuration($HostModel){
        $now = '';
        // 获取当前周期
        if($HostModel['billing_cycle'] == 'onetime'){
            $now = 'onetime';
        }else{
            // 计算周期
            $days = $HostModel['billing_cycle_time']/24/3600;

            $duration = [
                '0'=>'onetime_fee',
                '30'=>'month_fee',
                '90'=>'quarter_fee',
                '180'=>'half_year_fee',
                '365'=>'year_fee',
                '720'=>'two_year',
                '1085'=>'three_year',
            ];

            $now = $duration[$days] ?? '';
        }
        $desc = [
            'onetime_fee'=>'一次性',
            'month_fee'=>'月',
            'quarter_fee'=>'季度',
            'half_year_fee'=>'半年',
            'year_fee'=>'年',
            'two_year'=>'两年',
            'three_year'=>'三年',
        ];
        $num = [
            'onetime_fee'=>1,
            'month_fee'=>1,
            'quarter_fee'=>3,
            'half_year_fee'=>6,
            'year_fee'=>12,
            'two_year'=>24,
            'three_year'=>36,
        ];
        return ['duration'=>$now, 'desc'=>$desc[$now] ?? '', 'num'=>$num[ $now ]];
    }


}