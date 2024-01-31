<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 安全组模型
 * @desc 安全组模型
 * @use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel
 */
class IdcsmartSecurityGroupModel extends Model
{
    protected $name = 'addon_idcsmart_security_group';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'client_id'     => 'int',
        'type'     		=> 'string',
        'name'     		=> 'string',
        'description'   => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2022-06-08
     * @title 安全组列表
     * @desc 安全组列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,username,phone,email
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 安全组
     * @return int list[].id - 安全组ID
     * @return string list[].name - 名称 
     * @return string list[].description - 描述 
     * @return int list[].create_time - 创建时间 
     * @return int list[].host_num - 产品数量 
     * @return int list[].rule_num - 规则数量
     * @return int count - 安全组总数
     */
    public function idcsmartSecurityGroupList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aisg.'.$param['orderby'] : 'aisg.id';

        $clientId = get_client_id();
        if(empty($clientId)){
            return ['list' => [], 'count' => 0];
        }

    	$count = $this->alias('aisg')
            ->field('aisg.id')
            ->group('aisg.id')
            ->where(function ($query) use($param, $clientId) {
                if(!empty($clientId)){
                    $query->where('aisg.client_id', $clientId);
                }
                if(!empty($param['keywords'])){
                    $query->where('aisg.name|aisg.description', 'like', "%{$param['keywords']}%");
                }
                if(isset($param['type']) && !empty($param['type'])){
                    $query->where('aisg.type', $param['type']);
                }
            })
            ->count();
        $list = $this->alias('aisg')
            ->field('aisg.id,aisg.name,aisg.description,aisg.create_time,count(DISTINCT h.id) host_num,count(DISTINCT aisgr.id) rule_num')
            ->leftJoin('addon_idcsmart_security_group_host_link aisghl', 'aisghl.addon_idcsmart_security_group_id=aisg.id')
            ->leftJoin('host h', 'h.id=aisghl.host_id')
            ->leftJoin('client c', 'c.id=aisg.client_id')
            ->leftJoin('addon_idcsmart_security_group_rule aisgr', 'aisgr.addon_idcsmart_security_group_id=aisg.id')
            ->group('aisg.id')
            ->where(function ($query) use($param, $clientId) {
                if(!empty($clientId)){
                    $query->where('aisg.client_id', $clientId);
                }
                if(!empty($param['keywords'])){
                    $query->where('aisg.name|aisg.description', 'like', "%{$param['keywords']}%");
                }
                if(isset($param['type']) && !empty($param['type'])){
                    $query->where('aisg.type', $param['type']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-08
     * @title 安全组详情
     * @desc 安全组详情
     * @author theworld
     * @version v1
     * @param int id - 安全组ID required
     * @return int id - 安全组ID
     * @return string name - 名称 
     * @return string description - 描述 
     * @return int create_time - 创建时间 
     */
    public function indexIdcsmartSecurityGroup($id)
    {
        $clientId = get_client_id();
        if(empty($clientId)){
            return (object)[];
        }

        $idcsmartSecurityGroup = $this->field('id,client_id,name,description,create_time')->find($id);

        if(empty($idcsmartSecurityGroup)){
            return (object)[];
        }

        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return (object)[];
        }
        unset($idcsmartSecurityGroup['client_id']);

        return $idcsmartSecurityGroup;
    }

    /**
     * 时间 2022-06-08
     * @title 添加安全组
     * @desc 添加安全组
     * @author theworld
     * @version v1
     * @param string param.name - 名称 required
     * @param string param.description - 描述
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartSecurityGroup($param)
    {
        $this->startTrans();
        try {
            $clientId = get_client_id();

            $idcsmartSecurityGroup = $this->create([
                'client_id' => $clientId,
                'type' => 'host',
                'name' => $param['name'] ?? '',
                'description' => $param['description'] ?? '',
                'create_time' => time()
            ]);

            $this->createDefaulRules($idcsmartSecurityGroup->id, 'host');

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    /**
     * 时间 2022-06-08
     * @title 修改安全组
     * @desc 修改安全组
     * @author theworld
     * @version v1
     * @param int param.id - 安全组ID required
     * @param string param.name - 名称 required
     * @param string param.description - 描述
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateIdcsmartSecurityGroup($param)
    {
        $idcsmartSecurityGroup = $this->find($param['id']);
        if(empty($idcsmartSecurityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $this->startTrans();
        try {
            $clientId = get_client_id();

            $this->update([
                'name' => $param['name'] ?? '',
                'description' => $param['description'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    /**
     * 时间 2022-06-08
     * @title 删除安全组
     * @desc 删除安全组
     * @author theworld
     * @version v1
     * @param int id - 安全组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteIdcsmartSecurityGroup($id)
    {
        // 验证安全组ID
        $idcsmartSecurityGroup = $this->find($id);
        if(empty($idcsmartSecurityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        $hookRes = hook('before_addon_idcsmart_cloud_security_group_delete', ['security_group'=>$idcsmartSecurityGroup->toArray() ]);
        if(!empty($hookRes)){
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] != 200){
                    return $v;
                }
            }
        }

        $this->startTrans();
        try {
            $idcsmartSecurityGroup->destroy($id);

            // 删除安全组规则
            $ruleIds = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $id)->column('id');

            if(!empty($ruleIds)){
                IdcsmartSecurityGroupRuleLinkModel::whereIn('addon_idcsmart_security_group_rule_id', $ruleIds)->delete();
            }
            IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $id)->delete();
            IdcsmartSecurityGroupHostLinkModel::where('addon_idcsmart_security_group_id', $id)->delete();

            add_task([
                'type' => 'addon_idcsmart_security_group',
                'rel_id' => $id,
                'description' => lang_plugins('addon_idcsmart_security_group_delete'),
                'task_data' => [
                    'type' => 'delete',
                ]
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail').$e->getMessage().$this->getLastSql() ];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    // 创建默认安全组的规则
    private function createDefaulRules($id, $type = 'host'){
        if(empty($id)){
            return false;
        }
        $time = time();
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();
        if($type == 'lightHost'){
            $rules = [
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'ICMP',
                    'direction' => 'in',
                    'protocol' => 'icmp',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 1,
                    'end_port' => 65535,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'HTTP',
                    'direction' => 'in',
                    'protocol' => 'http',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 80,
                    'end_port' => 80,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'HTTPS',
                    'direction' => 'in',
                    'protocol' => 'https',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 443,
                    'end_port' => 443,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'SSH',
                    'direction' => 'in',
                    'protocol' => 'ssh',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 22,
                    'end_port' => 22,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'RDP',
                    'direction' => 'in',
                    'protocol' => 'rdp',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 3389,
                    'end_port' => 3389,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'ALLOW ALL OUT',
                    'direction' => 'out',
                    'protocol' => 'all',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 1,
                    'end_port' => 65535,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'UDP3389',
                    'direction' => 'in',
                    'protocol' => 'udp',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 3389,
                    'end_port' => 3389,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'UDP22',
                    'direction' => 'in',
                    'protocol' => 'udp',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 22,
                    'end_port' => 22,
                    'priority' => 1,
                    'action' => 'accept',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => '',
                    'direction' => 'in',
                    'protocol' => 'all',
                    'create_time' => $time,
                    'start_ip' => '0.0.0.0',
                    'end_ip' => '0.0.0.0',
                    'start_port' => 1,
                    'end_port' => 65535,
                    'priority' => 1000,
                    'action' => 'drop',
                ],
            ];
            $IdcsmartSecurityGroupRuleModel->saveAll($rules);
        }else if($type == 'hyperv'){
            $rules = [
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => '',
                    'direction' => 'in',
                    'protocol' => 'all',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 1,
                    'end_port' => 65535,
                    // 'priority' => 1000,
                    'action' => 'drop',
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'ICMP',
                    'direction' => 'in',
                    'protocol' => 'icmp',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 1,
                    'end_port' => 65535,
                    'action' => 'accept',
                    // 'priority' => 1,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'HTTP',
                    'direction' => 'in',
                    'protocol' => 'http',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 80,
                    'end_port' => 80,
                    'action' => 'accept',
                    // 'priority' => 2,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'HTTPS',
                    'direction' => 'in',
                    'protocol' => 'https',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 443,
                    'end_port' => 443,
                    'action' => 'accept',
                    // 'priority' => 3,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'SSH',
                    'direction' => 'in',
                    'protocol' => 'ssh',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 22,
                    'end_port' => 22,
                    'action' => 'accept',
                    // 'priority' => 4,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'RDP',
                    'direction' => 'in',
                    'protocol' => 'rdp',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 3389,
                    'end_port' => 3389,
                    'action' => 'accept',
                    // 'priority' => 5,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'ALLOW ALL OUT',
                    'direction' => 'out',
                    'protocol' => 'tcp',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 1,
                    'end_port' => 65535,
                    'action' => 'accept',
                    // 'priority' => 6,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'UDP3389',
                    'direction' => 'in',
                    'protocol' => 'udp',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 3389,
                    'end_port' => 3389,
                    'action' => 'accept',
                    // 'priority' => 7,
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'UDP22',
                    'direction' => 'in',
                    'protocol' => 'udp',
                    'create_time' => $time,
                    'ip' => '0.0.0.0/0',
                    'start_port' => 22,
                    'end_port' => 22,
                    'action' => 'accept',
                    // 'priority' => 8,
                ],
            ];
            $IdcsmartSecurityGroupRuleModel->saveAll($rules);
        }else if($type == 'host'){
            $rules = [
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'ICMP',
                    'direction' => 'in',
                    'protocol' => 'icmp',
                    'port' => '1-65535',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'HTTP',
                    'direction' => 'in',
                    'protocol' => 'http',
                    'port' => '80',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'HTTPS',
                    'direction' => 'in',
                    'protocol' => 'https',
                    'port' => '443',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'SSH',
                    'direction' => 'in',
                    'protocol' => 'ssh',
                    'port' => '22',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'RDP',
                    'direction' => 'in',
                    'protocol' => 'rdp',
                    'port' => '3389',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'ALLOW ALL OUT',
                    'direction' => 'out',
                    'protocol' => 'all',
                    'port' => '1-65535',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'UDP3389',
                    'direction' => 'in',
                    'protocol' => 'udp',
                    'port' => '3389',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
                [
                    'addon_idcsmart_security_group_id' => $id,
                    'description' => 'UDP22',
                    'direction' => 'in',
                    'protocol' => 'udp',
                    'port' => '22',
                    'ip' => '0.0.0.0/0',
                    'create_time' => $time
                ],
            ];
            $IdcsmartSecurityGroupRuleModel->saveAll($rules);
        }
        return true;
    }
}