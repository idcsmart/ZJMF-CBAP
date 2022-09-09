<?php 
namespace server\idcsmart_cloud_ip\model;

use think\Model;
use server\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use app\common\model\HostModel;
use server\idcsmart_cloud\model\HostLinkModel AS HLM;

class HostLinkModel extends Model{

	protected $name = 'module_idcsmart_cloud_ip_host_link';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'                                    => 'int',
        'module_idcsmart_cloud_ip_package_id'   => 'int',
        'host_id'                               => 'int',
        'rel_id'                                => 'int',
        'rel_bw_id'                             => 'int',
        'rel_host_id'                           => 'int',
        'bw_type'                               => 'string',
        'bw_size'                               => 'int',
        'ip'                                    => 'string',
        'status'                                => 'int',
        'create_time'                           => 'int',
        'update_time'                           => 'int',
    ];


    # 魔方云磁盘产品列表
    public function idcsmartCloudIpList($param){
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

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('host ch', 'hl.rel_host_id=ch.id')
            ->where($where)
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,hl.ip,hl.bw_size,hl.rel_host_id host_id,ch.name cloud_name,h.first_payment_amount,h.billing_cycle_name')
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

    # 挂载IP
    public function mountIdcsmartCloudIp($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('id_error')];
        }
        $params = $host->getModuleParams();

        $hostLink = HLM::where('host_id', $param['host_id'])->find();
        $params['rel_id'] = $hostLink['rel_id'] ?? 0;
        $params['product_type'] = 'host';
        if(empty($params['rel_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_id_is_empty')];
        }

        $ipHostLink = $this->where('host_id', $param['id'])->find();
        $ipId = $ipHostLink['rel_id'] ?? 0;
        if(empty($ipId)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_ip_id_is_empty')];
        }

        $IC = new IC($params['server']);
        $res = $IC->elasticIpAttach($ipId, $params);
        if($res['status']==200){
            $this->update([
                'status'=>1, 
                'rel_host_id'=>$param['host_id']
            ], ['host_id'=>$param['id']]);
        }
        return $res;
    }

    # 卸载IP
    public function umountIdcsmartCloudIp($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('id_error')];
        }
        $params = $host->getModuleParams();

        $ipHostLink = $this->where('host_id', $param['id'])->find();
        $ipId = $ipHostLink['rel_id'] ?? 0;
        if(empty($ipId)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_ip_id_is_empty')];
        }
        if(empty($ipHostLink['rel_host_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('package_not_be_related_host')];
        }

        $IC = new IC($params['server']);
        $res = $IC->elasticIpDetach($ipId, $params);
        if($res['status']==200){
            $this->update([
                'status'=>0, 
                'rel_host_id'=>0
            ], ['host_id'=>$param['id']]);
        }

        return $res;
    }

    # 删除IP
    /*public function deleteIdcsmartCloudIp($param)
    {
        $host = HostModel::find($param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('id_error')];
        }
        $params = $host->getModuleParams();

        $ipHostLink = $this->where('host_id', $param['id'])->find();
        $id = $ipHostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('package_cloud_ip_id_is_empty')];
        }

        $IC = new IC($params['server']);
        $res = $IC->elasticIpDelete($id);
        return $res;
    }
*/
}