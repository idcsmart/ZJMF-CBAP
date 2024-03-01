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

    /**
     * 时间 2023-6-8
     * @title 服务器列表
     * @desc 服务器列表
     * @author wyh
     * @version v1
     * @return array list - 服务器列表
     * @return string list[].id - 服务器ID
     * @return string list[].name - 服务器名称
     * @return string list[].ip_address - ip地址
     * @return string list[].assigned_ips - 其他IP地址
     * @return string list[].hostname - 主机名
     * @return string list[].noc -
     * @return string list[].status_address - 服务器状态地址
     * @return string list[].username - 用户名
     * @return string list[].password - 密码
     * @return string list[].accesshash - 访问散列值
     * @return int list[].secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @return string list[].port - 访问端口(默认80)
     * @return string list[].disabled - 1勾选禁用，0使用(默认)(单选框)
     * @return string list[].type - 接口类型
     * @return int list[].max_accounts - 最大账号数量（默认为200）
     * @return int list[].gid - 服务器组ID（下拉框）单选
     * @return string list[].group_name - 分组名称
     * @return int list[].used - 已使用数量
     * @return int count - 数量
     */
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
            $v['accesshash'] = !empty($v['accesshash'])?htmlspecialchars_decode($v['accesshash']):"";
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

    /**
     * 时间 2023-6-8
     * @title 添加服务器
     * @desc 添加服务器
     * @author wyh
     * @version v1
     * @param string name - 服务器名称 required
     * @param string ip_address - ip地址
     * @param string assigned_ips - 其他IP地址
     * @param string hostname - 主机名
     * @param string noc -
     * @param string status_address - 服务器状态地址
     * @param string username - 用户名
     * @param string password - 密码
     * @param string accesshash - 访问散列值
     * @param int secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @param string port - 访问端口(默认80)
     * @param string disabled - 1勾选禁用，0使用(默认)(单选框)
     * @param string type - 接口类型
     * @param int max_accounts - 最大账号数量（默认为200）
     * @param int gid - 服务器组ID（下拉框）单选(调服务器分组列表接口，传modules，值为接口类型)
     */
    public function createServer($param){
        if (!empty($param['password'])){
            $param['password'] = aes_password_encode($param['password']);
            $param['accesshash'] = htmlspecialchars($param['accesshash']);
        }else{
            $param['password'] = '';
            $param['accesshash'] = '';
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

    /**
     * 时间 2023-6-8
     * @title 服务器详情
     * @desc 服务器详情
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     * @return string name - 服务器名称 required
     * @return string ip_address - ip地址
     * @return string assigned_ips - 其他IP地址
     * @return string hostname - 主机名
     * @return string noc -
     * @return string status_address - 服务器状态地址
     * @return string username - 用户名
     * @return string password - 密码
     * @return string accesshash - 访问散列值
     * @return int secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @return string port - 访问端口(默认80)
     * @return string disabled - 1勾选禁用，0使用(默认)(单选框)
     * @return string type - 接口类型
     * @return int max_accounts - 最大账号数量（默认为200）
     * @return int gid - 服务器组ID（下拉框）单选
     */
    public function indexServer($param){
        $server = $this->find($param['id']);
        if (!empty($server['password'])){
            $server['password'] = aes_password_decode($server['password']);
            $server['accesshash'] = htmlspecialchars_decode($server['accesshash']);
        }else{
            $server['password'] = "";
            $server['accesshash'] = "";
        }


        return [
            'status' => 200,
            'msg' => lang_plugins("success_message"),
            'data' => [
                'server' => $server,
            ]
        ];
    }

    /**
     * 时间 2023-6-8
     * @title 更新服务器
     * @desc 更新服务器
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     * @param string name - 服务器名称 required
     * @param string ip_address - ip地址
     * @param string assigned_ips - 其他IP地址
     * @param string hostname - 主机名
     * @param string noc -
     * @param string status_address - 服务器状态地址
     * @param string username - 用户名
     * @param string password - 密码
     * @param string accesshash - 访问散列值
     * @param int secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @param string port - 访问端口(默认80)
     * @param int disabled - 1勾选禁用，0使用(默认)(单选框)
     * @param string type - 接口类型
     * @param int max_accounts - 最大账号数量（默认为200）
     * @param int gid - 服务器组ID（下拉框）单选
     */
    public function updateServer($param){
        $server = $this->find($param['id']);
        if (empty($server)){
            return ['status'=>400,'msg'=>lang_plugins("idcsmart_common_server_not_exist")];
        }
        $param['password'] = aes_password_encode($param['password']);
        $param['accesshash'] = htmlspecialchars($param['accesshash']);
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

    /**
     * 时间 2023-6-8
     * @title 删除服务器
     * @desc 删除服务器
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     */
    public function deleteServer($param){
        $server = $this->find($param['id']);
        if (empty($server)){
            return ['status'=>400,'msg'=>lang_plugins("idcsmart_common_server_not_exist")];
        }
        $count = $this->serverCountUsedAtHost($param['id']);
        if ($count>0){
            return ['status'=>400,'msg'=>lang_plugins("idcsmart_common_server_is_used")];
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

    /**
     * 时间 2023-6-8
     * @title 测试服务器链接
     * @desc 测试服务器链接
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     */
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
            $result['data']['msg'] = lang_plugins("idcsmart_common_server_no_module");
        }
        $result['msg'] = lang_plugins("success_message");
        return $result;
    }

    /**
     * 时间 2023-6-8
     * @title 模块列表
     * @desc 模块列表
     * @author wyh
     * @version v1
     */
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