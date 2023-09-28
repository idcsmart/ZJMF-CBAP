<?php 
namespace server\idcsmart_common\model;

use server\idcsmart_common\logic\ProvisionLogic;
use think\db\Query;
use think\Model;

class IdcsmartCommonServerModel extends Model
{
    protected $name = 'module_idcsmart_common_server';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'gid'             => 'int',
        'name'                   => 'string',
        'ip_address'             => 'string',
        'assigned_ips'             => 'string',
        'hostname'            => 'string',
        'monthly_cost'            => 'float',
        'noc'            => 'string',
        'status_address'            => 'string',
        'name_server1'            => 'string',
        'name_server1_ip'            => 'string',
        'name_server2'            => 'string',
        'name_server2_ip'            => 'string',
        'name_server3'            => 'string',
        'name_server3_ip'            => 'string',
        'name_server4'            => 'string',
        'name_server4_ip'            => 'string',
        'name_server5'            => 'string',
        'name_server5_ip'            => 'string',
        'max_accounts'            => 'int',
        'username'            => 'string',
        'password'            => 'string',
        'accesshash'            => 'string',
        'secure'            => 'int',
        'port'            => 'string',
        'active'            => 'int',
        'disabled'            => 'int',
        'server_type'            => 'string',
        'link_status'            => 'int',
        'type'            => 'string',
    ];

    public function serverList($param){
        $where = function (Query $query) use ($param){
            $query->where('server_type','normal');

            if (isset($param['gid'])){
                $query->where('gid',$param['gid']);
            }

            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('name|hostname','like',"%{$param['keywords']}%");
            }
            if (isset($param['status']) && $param['status']!="" && in_array($param['status'],[0,1])){
                $query->where('disabled',$param['status']);
            }
        };

        $ProvisionLogic = new ProvisionLogic();

        $modules = $ProvisionLogic->getModules();
        if (!empty($modules)){
            $modules = array_column($modules,"name","value");
        }

        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();
        $serverGroups = $IdcsmartCommonServerGroupModel->where('system_type','normal')->select()->toArray();
        if (!empty($serverGroups)){
            $serverGroups = array_column($serverGroups,"name","id");
        }

        $count = $this->where($where)->count();

        $servers = $this->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            //->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        $list = array_map(function ($v) use ($serverGroups, $modules) {
            $v['group_name'] = $serverGroups[$v['gid']] ?? '';
            $v['type'] = $modules[$v['type']] ?? '';
            $v['used'] = $this->serverCountUsedAtHost($v['id']);
            $v['password'] = !empty($v['password'])?aes_password_decode($v['password']):"";
            return $v;
        }, $servers);

        return [
            'status' => 200,
            'msg' => lang_plugins("success_message"),
            'data' => [
                'list' => $list,
                'count' => $count
            ]
        ];
    }

    public function createServer($param){
        if (!empty($param['password'])){
            $param['password'] = aes_password_encode($param['password']);
        }else{
            $param['password'] = '';
        }

        $this->startTrans();
        try{
            $this->insert($param);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }
        return ['status'=>200,'msg'=>lang_plugins("success_message")];
    }

    public function indexServer($param){
        $server = $this->find($param['id']);
        if (!empty($server['password'])){
            $server['password'] = aes_password_decode($server['password']);
        }else{
            $server['password'] = "";
        }


        return [
            'status' => 200,
            'msg' => lang_plugins("success_message"),
            'data' => [
                'server' => $server,
            ]
        ];
    }

    public function updateServer($param){
        $server = $this->find($param['id']);
        if (empty($server)){
            return ['status'=>400,'msg'=>"接口不存在"];
        }
        $param['password'] = aes_password_encode($param['password']);
        $this->startTrans();
        try{
            $server->save($param);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins("error_message")];
        }
        return ['status'=>200,'msg'=>lang_plugins("success_message")];
    }

    public function deleteServer($param){
        $server = $this->find($param['id']);
        if (empty($server)){
            return ['status'=>400,'msg'=>"接口不存在"];
        }
        $count = $this->serverCountUsedAtHost($param['id']);
        if ($count>0){
            return ['status'=>400,'msg'=>"此接口已被使用，不能删除"];
        }
        $this->startTrans();
        try{
            $server->delete();
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins("error_message")];
        }
        return ['status'=>200,'msg'=>lang_plugins("success_message")];
    }

    public function testLinkServer($param){
        $id = $param['id'];
        $data = $this->alias('a')
            ->field('a.*,b.name group_name,b.type module_type,b.system_type system_module_type,a.type server_module_type,a.server_type servers_module_type')
            ->leftJoin('module_idcsmart_common_server_group b', 'a.gid=b.id')
            ->where('a.id', $id)
            ->find();
        if(!empty($data['server_module_type'])){
            $data['server_ip'] = $data['ip_address'];
            $data['server_host'] = $data['hostname'];
            $data['server_password'] = $data['password'] = aes_password_decode($data['password']);
            $data['server_username'] = $data['username'];

            $module = $data['server_module_type'];
            unset($data['module_type'], $data['system_module_type'], $data['server_module_type'], $data['servers_module_type']);
            if($data['secure'] == 1){
                $data['server_http_prefix'] = 'https';
            }else{
                $data['server_http_prefix'] = 'http';
            }
            $provision = new ProvisionLogic();
            $result = $provision->testLink($module, $data);
            if($result['status'] == 200){
                // 更新服务器状态
                if($result['data']['server_status'] == 1){
                    $this->where('id', $id)->update(['link_status'=>1]);
                }else{
                    $this->where('id', $id)->update(['link_status'=>0]);
                }
            }
        }else{
            $result['status'] = 200;
            $result['data']['server_status'] = 0;
            $result['data']['msg'] = '接口没有模块';
        }
        $result['msg'] = lang_plugins("success_message");
        return $result;
    }

    public function getModules($param){
        return [
            'status' => 200,
            'msg' => lang_plugins("success_message"),
            'data' => [
                'modules' => (new ProvisionLogic())->getModules()
            ]
        ];
    }

    private function serverCountUsedAtHost($id){
        $IdcsmartCommonServerHostLinkModel = new IdcsmartCommonServerHostLinkModel();
        $count = $IdcsmartCommonServerHostLinkModel->where('host_id',$id)->count();
        return $count;
    }
}