<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\HostModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title VPC网络模型
 * @use server\mf_cloud\model\VpcNetworkModel
 */
class VpcNetworkModel extends Model{

	protected $name = 'module_mf_cloud_vpc_network';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'product_id'            => 'int',
        'data_center_id'        => 'int',
        'name'                  => 'string',
        'client_id'             => 'int',
        'ips'                   => 'string',
        'rel_id'                => 'int',
        'vpc_name'              => 'string',
        'create_time'           => 'int',
        'downstream_client_id'  => 'int',
    ];

    /**
     * 时间 2023-02-13
     * @title 搜索VPC网络
     * @desc 搜索VPC网络
     * @author hh
     * @version v1
     */
    public function vpcNetworkSearch($param){
        $clientId = get_client_id() ?? 0;

        $where = [];
        $where[] = ['product_id', '=', $param['id']];
        $where[] = ['client_id', '=', $clientId];
        if(isset($param['data_center_id'])){
            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }
        if(request()->is_api && isset($param['downstream_client_id'])){
            $where[] = ['downstream_client_id', '=', $param['downstream_client_id']];
        }

        $list = $this
                ->field('id,name')
                ->where($where)
                ->select()
                ->toArray();

        return ['list'=>$list];
    }

    /**
     * 时间 2023-02-13
     * @title 创建VPC网络
     * @desc 创建VPC网络
     * @author hh
     * @version v1
     * @param   string x      -             x
     * @param   [type] $param [description]
     * @return  [type]        [description]
     */
    public function vpcNetworkCreate($param){
        $param['ips'] = isset($param['ips']) && !empty($param['ips']) ? $param['ips'] : '10.0.0.0/16';
        $param['create_time'] = time();
        $param['vpc_name'] = 'VPC-'.rand_str(8);
        if(request()->is_api && isset($param['downstream_client_id']) && $param['downstream_client_id']>0){
            $param['downstream_client_id'] = $param['downstream_client_id'];
        }else{
            $param['downstream_client_id'] = 0;
        }

        $vpc = $this->create($param, ['product_id','data_center_id','name','vpc_name','client_id','ips','create_time','downstream_client_id']);

        $description = lang_plugins('log_mf_cloud_add_vpc_network_success', [
            '{name}' => $param['name'],
            '{ips}' => $param['ips'],
        ]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$vpc->id
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-02-13
     * @title VPC网络列表
     * @desc VPC网络列表
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @return  int list[].id - VPC网络ID
     * @return  string list[].name - VPC网络名称
     * @return  string list[].ips - VPC网络网段
     * @return  int list[].host[].id - 主机产品ID
     * @return  string list[].host[].name - 主机标识
     */
    public function vpcNetworkList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','num'])){
            $param['orderby'] = 'id';
        }
        $clientId = get_client_id();

        $list = [];
        $count = 0;

        $host = HostModel::find($param['id']);
        if(empty($host) || $host['client_id'] != $clientId){
            return ['list'=>$list, 'count'=>$count];
        }
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['list'=>$list, 'count'=>$count];
        }

        $where = [];
        $where[] = ['product_id', '=', $host['product_id']];
        $where[] = ['data_center_id', '=', $hostLink['data_center_id']];
        $where[] = ['client_id', '=', $clientId];
        if(request()->is_api && isset($param['downstream_client_id'])){
            $where[] = ['downstream_client_id', '=', $param['downstream_client_id'] ];
        }

        $list = $this
                ->field('id,name,ips')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        if(!empty($list)){
            $vpcNetworkId = array_column($list, 'id');

            $host = HostLinkModel::alias('hl')
                    ->field('h.id,h.name,hl.vpc_network_id')
                    ->join('host h', 'hl.host_id=h.id')
                    ->whereIn('hl.vpc_network_id', $vpcNetworkId)
                    ->select()
                    ->toArray();

            $hostArr = [];
            foreach($host as $v){
                $hostArr[ $v['vpc_network_id'] ][] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                ];
            }

            foreach($list as $k=>$v){
                $list[$k]['host'] = $hostArr[$v['id']] ?? [];
            }
        }

        $count = $this
                ->where($where)
                ->count();

        $result = ['list'=>$list, 'count'=>$count];
        if(request()->is_api){
            $result['host'] = !empty($list) ? array_column($host, 'id') : [];
        }
        return $result;
    }

    /**
     * 时间 2023-02-13
     * @title 修改VPC网络
     * @desc 修改VPC网络
     * @author hh
     * @version v1
     * @param   int id - VPC网络ID require
     * @param   string name - 网络名称 require
     */
    public function vpcNetworkUpdate($param){
        $clientId = get_client_id();
        $vpcNetwork = $this->find($param['vpc_network_id']);
        if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $clientId){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        if(request()->is_api && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        $this->update($param, ['id'=>$param['vpc_network_id']], ['name']);

        if($param['name'] != $vpcNetwork['name']){
            $description = lang_plugins('log_mf_cloud_modify_vpc_network_success', [
                '{name}' => $vpcNetwork['name'],
                '{new_name}' => $param['name'],
            ]);
            active_log($description, 'product', $vpcNetwork['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
            'data'   => [
                'name' => $vpcNetwork['name'],
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-02-13
     * @title 删除VPC网络
     * @desc 删除VPC网络
     * @author hh
     * @version v1
     * @param   int id - VPC网络ID require
     */
    public function vpcNetworkDelete($param){
        $clientId = get_client_id();
        $vpcNetwork = $this->find($param['vpc_network_id']);
        if(empty($vpcNetwork) || $vpcNetwork['client_id'] != $clientId){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        if(request()->is_api && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }
        // 是否还有主机正在使用
        $use = HostLinkModel::where('vpc_network_id', $param['vpc_network_id'])->find();
        if(!empty($use)){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_used_cannot_delete')];
        }
        $this->where('id', $param['vpc_network_id'])->delete();

        $description = lang_plugins('log_mf_cloud_delete_vpc_network_success', [
            '{name}'=>$vpcNetwork['name'],
            '{ips}' => $vpcNetwork['ips'],
        ]);
        active_log($description, 'product', $vpcNetwork['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
            'data'   => [
                'name' => $vpcNetwork['name'],
                'ips' => $vpcNetwork['ips'],
            ]
        ];
        return $result;
    }

    
    
    




}