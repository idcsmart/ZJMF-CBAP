<?php 
namespace server\idcsmart_dcim\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use app\common\model\MenuModel;
// use server\idcsmart_cloud\logic\ToolLogic;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;

class HostLinkModel extends Model{

	protected $name = 'module_idcsmart_dcim_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'host_id'           => 'int',
        'rel_id'            => 'int',
        'image_id'          => 'int',
        'package_id'        => 'int',
        'package_data'      => 'string',
        'create_time'       => 'int',
        'port'              => 'string',
        'password'          => 'string',
        'ip'                => 'string',
        'power_status'      => 'string',
        'traffic_bill_type' => 'string',
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
     * @param   string param.status - 产品状态搜索(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除,Failed=开通失败)
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
     * @return  string data.list[].product_name - 商品名称
     */
    public function dcimList($param){
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
        $where[] = ['h.status', '<>', 'Cancelled'];
        if(isset($param['keywords']) && trim($param['keywords']) !== ''){
            $where[] = ['p.name|h.name|hl.ip', 'LIKE', '%'.$param['keywords'].'%'];
        }
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $where[] = ['p.data_center_id', '=', $param['data_center_id']];
        }
        if(isset($param['status']) && !empty($param['status'])){
            if($param['status'] == 'Pending'){
                $where[] = ['h.status', 'IN', ['Pending','Failed']];
            }else if(in_array($param['status'], ['Unpaid','Active','Suspended','Deleted'])){
                $where[] = ['h.status', '=', $param['status']];
            }
        }
        if(isset($param['m']) && !empty($param['m'])){
            $MenuModel = MenuModel::where('module', 'idcsmart_dcim')
                                ->where('id', $param['m'])
                                ->find();
            if(!empty($MenuModel) && !empty($MenuModel['product_id'])){
                $MenuModel['product_id'] = json_decode($MenuModel['product_id'], true);
                if(!empty($MenuModel['product_id'])){
                    $where[] = ['h.product_id', 'IN', $MenuModel['product_id'] ];
                }
            }
        }

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'IN', $hostId];
        }

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('module_idcsmart_dcim_package p', 'hl.package_id=p.id')
            ->leftJoin('module_idcsmart_dcim_data_center dc', 'p.data_center_id=dc.id')
            ->leftJoin('module_idcsmart_dcim_image i', 'hl.image_id=i.id')
            ->where($where)
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,h.due_time,h.active_time,pro.name product_name,c.name_zh country,c.iso country_code,dc.city,p.name package_name,hl.ip,hl.power_status,i.name image_name,ig.name image_group_name')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('product pro', 'h.product_id=pro.id')
            ->leftJoin('module_idcsmart_dcim_package p', 'hl.package_id=p.id')
            ->leftJoin('module_idcsmart_dcim_data_center dc', 'p.data_center_id=dc.id')
            ->leftJoin('country c', 'dc.country_id=c.id')
            ->leftJoin('module_idcsmart_dcim_image i', 'hl.image_id=i.id')
            ->leftJoin('module_idcsmart_dcim_image_group ig', 'i.image_group_id=ig.id')
            ->where($where)
            ->withAttr('status', function($val){
                return $val == 'Failed' ? 'Pending' : $val;
            })
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
     * @return  int data.port - 端口
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
     * @return  string data.package.ip_num - IP数量
     * @return  string data.package.in_bw - 进带宽
     * @return  string data.package.out_bw - 出带宽
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
            $data['port'] = $hostLink['port'];
            $data['power_status'] = $hostLink['power_status'];

            $package = PackageModel::field('id,name,description,data_center_id,ip_num,in_bw,out_bw')->where('id', $hostLink['package_id'])->find();
            
            $image = ImageModel::alias('i')
                    ->field('i.id,i.name,ig.name image_group_name')
                    ->leftJoin('module_idcsmart_dcim_image_group ig', 'i.image_group_id=ig.id')
                    ->where('i.id', $hostLink['image_id'])
                    ->find();

            $dataCenter = DataCenterModel::alias('dc')
                        ->field('dc.id,dc.city,c.name_zh country_name,c.iso')
                        ->leftJoin('country c', 'dc.country_id=c.id')
                        ->where('dc.id', $package['data_center_id'])
                        ->find();

            $data['data_center'] = $dataCenter ?? (object)[];
            
            $data['image'] = $image ?? (object)[];

            $data['package'] = $package;

            $res['data'] = $data;
        }
        return $res;
    }

}