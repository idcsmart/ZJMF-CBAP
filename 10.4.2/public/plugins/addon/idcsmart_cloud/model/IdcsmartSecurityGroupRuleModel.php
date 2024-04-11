<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 安全组规则模型
 * @desc 安全组规则模型
 * @use addon\idcsmart_cloud\model\SecurityGroupRuleModel
 */
class IdcsmartSecurityGroupRuleModel extends Model
{
    protected $name = 'addon_idcsmart_security_group_rule';

    // 设置字段信息
    protected $schema = [
        'id'      		                        => 'int',
        'addon_idcsmart_security_group_id'      => 'int',
        'description'     		                => 'string',
        'direction'     		                => 'string',
        'protocol'                              => 'string',
        'port'                                  => 'string',
        'ip'                                    => 'string',
        'lock'                                  => 'int',
        'start_ip'                              => 'string',
        'end_ip'                                => 'string',
        'start_port'                            => 'int',
        'end_port'                              => 'int',
        'priority'                              => 'int',
        'action'                                => 'string',
        'create_time'                           => 'int',
        'update_time'                           => 'int',
    ];

    /**
     * 时间 2022-06-09
     * @title 安全组规则列表
     * @desc 安全组规则列表
     * @author theworld
     * @version v1
     * @param int param.id - 安全组ID required
     * @param string param.keywords - 关键字
     * @param string param.direction - 规则方向in,out
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 安全组规则
     * @return int list[].id - 安全组规则ID
     * @return string list[].description - 描述 
     * @return string list[].direction - 规则方向in,out
     * @return string list[].protocol - 协议 
     * @return string list[].port - 端口范围 
     * @return string list[].ip - 授权IP 
     * @return int list[].create_time - 创建时间 
     * @return int count - 安全组规则总数
     */
    public function idcsmartSecurityGroupRuleList($param)
    {
        $param['id'] = $param['id'] ?? 0;
        $idcsmartSecurityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($idcsmartSecurityGroup)){
            return ['list' => [], 'count' => 0];
        }
        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['list' => [], 'count' => 0];
        }
        $param['keywords'] = $param['keywords'] ?? '';
        $param['direction'] = $param['direction'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? $param['orderby'] : 'id';

        $where = function (Query $query) use($param) {
            if(!empty($param['id'])){
                $query->where('addon_idcsmart_security_group_id', $param['id']);
            }
            if(!empty($param['keywords'])){
                $query->where('description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['direction'])){
                $query->where('direction', $param['direction']);
            }
        };
        
    	$count = $this->field('id')
            ->where($where)
            ->count();
        $list = $this->field('id,description,direction,protocol,port,ip,create_time')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则详情
     * @desc 安全组规则详情
     * @author theworld
     * @version v1
     * @param int id - 安全组规则ID required
     * @return int id - 安全组规则ID
     * @return string description - 描述 
     * @return string direction - 规则方向in,out
     * @return string protocol - 协议 
     * @return string port - 端口范围 
     * @return string ip - 授权IP 
     * @return int create_time - 创建时间 
     */
    public function indexIdcsmartSecurityGroupRule($id)
    {
        $idcsmartSecurityGroupRule = $this->field('id,addon_idcsmart_security_group_id,description,direction,protocol,port,ip,create_time')->find($id);
        if(empty($idcsmartSecurityGroupRule)){
            return (object)[];
        }

        $idcsmartSecurityGroup = IdcsmartSecurityGroupModel::find($idcsmartSecurityGroupRule['addon_idcsmart_security_group_id']);
        if(empty($idcsmartSecurityGroup)){
            return (object)[];
        }
        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return (object)[];
        }
        unset($idcsmartSecurityGroupRule['addon_idcsmart_security_group_id']);

        return $idcsmartSecurityGroupRule;
    }

    /**
     * 时间 2022-06-09
     * @title 添加安全组规则
     * @desc 添加安全组规则
     * @author theworld
     * @version v1
     * @param int param.id - 安全组ID required
     * @param string param.description - 描述
     * @param string param.direction - 规则方向in,out required
     * @param string param.protocol - 协议 required
     * @param string param.port - 端口范围 required
     * @param string param.ip - 授权IP required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartSecurityGroupRule($param)
    {
        $idcsmartSecurityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($idcsmartSecurityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $exist = $this->where('addon_idcsmart_security_group_id', $param['id'])
            ->where('direction', $param['direction'] ?? 'in')
            ->where('protocol', $param['protocol'] ?? 'all')
            ->where('port', $param['port'] ?? '')
            ->where('ip', $param['ip'] ?? '')
            ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_rule_exist')];
        }

        // 是否超出限制
        $num = $this->where('addon_idcsmart_security_group_id', $param['id'])
                ->count();
        if($num + 1 >= 100){
            return ['status'=>400, 'msg'=>lang_plugins('security_max_rule_num')];
        }
        $protocol = get_security_group_protocol();
        if(isset($protocol[ $param['protocol'] ]['port'])){
            $param['port'] = $protocol[ $param['protocol'] ]['port'];
        }

        $this->startTrans();
        try {
            $idcsmartSecurityGroupRule = $this->create([
                'addon_idcsmart_security_group_id' => $param['id'],
                'description' => $param['description'] ?? '',
                'direction' => $param['direction'] ?? 'in',
                'protocol' => $param['protocol'] ?? 'all',
                'port' => $param['port'] ?? '',
                'ip' => $param['ip'] ?? '',
                'create_time' => time()
            ]);

            add_task([
                'type' => 'addon_idcsmart_security_group_rule',
                'rel_id' => $idcsmartSecurityGroupRule->id,
                'description' => lang_plugins('addon_idcsmart_security_group_rule_create'),
                'task_data' => [
                    'type' => 'create',
                    'id' => $param['id']
                ]
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    /**
     * 时间 2022-06-09
     * @title 修改安全组规则
     * @desc 修改安全组规则
     * @author theworld
     * @version v1
     * @param int param.id - 安全组规则ID required
     * @param string param.description - 描述
     * @param string param.direction - 规则方向in,out required
     * @param string param.protocol - 协议 required
     * @param string param.port - 端口范围 required
     * @param string param.ip - 授权IP required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateIdcsmartSecurityGroupRule($param)
    {
        $idcsmartSecurityGroupRule = $this->find($param['id']);
        if(empty($idcsmartSecurityGroupRule)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_rule_is_not_exist')];
        }

        $idcsmartSecurityGroup = IdcsmartSecurityGroupModel::find($idcsmartSecurityGroupRule['addon_idcsmart_security_group_id']);
        if(empty($idcsmartSecurityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_rule_is_not_exist')];
        }

        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_rule_is_not_exist')];
        }
        $protocol = get_security_group_protocol();
        if(isset($protocol[ $param['protocol'] ]['port'])){
            $param['port'] = $protocol[ $param['protocol'] ]['port'];
        }

        $exist = $this->where('id', '<>', $param['id'])
            ->where('addon_idcsmart_security_group_id', $idcsmartSecurityGroup['id'])
            ->where('direction', $param['direction'] ?? 'in')
            ->where('protocol', $param['protocol'] ?? 'all')
            ->where('port', $param['port'] ?? '')
            ->where('ip', $param['ip'] ?? '')
            ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_rule_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'description' => $param['description'] ?? '',
                'direction' => $param['direction'] ?? 'in',
                'protocol' => $param['protocol'] ?? 'all',
                'port' => $param['port'] ?? '',
                'ip' => $param['ip'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            add_task([
                'type' => 'addon_idcsmart_security_group_rule',
                'rel_id' => $param['id'],
                'description' => lang_plugins('addon_idcsmart_security_group_rule_update'),
                'task_data' => [
                    'type' => 'update',
                ]
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    /**
     * 时间 2022-06-09
     * @title 删除安全组规则
     * @desc 删除安全组规则
     * @author theworld
     * @version v1
     * @param int id - 安全组规则ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteIdcsmartSecurityGroupRule($id)
    {
        $idcsmartSecurityGroupRule = $this->find($id);
        if(empty($idcsmartSecurityGroupRule)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_rule_is_not_exist')];
        }

        $idcsmartSecurityGroup = IdcsmartSecurityGroupModel::find($idcsmartSecurityGroupRule['addon_idcsmart_security_group_id']);
        if(empty($idcsmartSecurityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_rule_is_not_exist')];
        }

        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_rule_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->destroy($id);
            /*IdcsmartSecurityGroupRuleLinkModel::destroy(function($query) use($id){
                $query->where('addon_idcsmart_security_group_rule_id', $id);
            });*/
            add_task([
                'type' => 'addon_idcsmart_security_group_rule',
                'rel_id' => $id,
                'description' => lang_plugins('addon_idcsmart_security_group_rule_delete'),
                'task_data' => [
                    'type' => 'delete',
                ]
            ]);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    /**
     * 时间 2022-08-26
     * @title 批量添加安全组规则
     * @desc 批量添加安全组规则
     * @author theworld
     * @version v1
     * @param  array rule - 规则数组
     * @param  string rule[].description - 描述
     * @param  string rule[].direction - 规则方向in,out require
     * @param  string rule[].protocol - 协议all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre require
     * @param  string rule[].port - 端口范围 require
     * @param  string rule[].ip - 授权IP require
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return int data.success_num - 添加成功的规则数量
     */
    public function batchCreate($param){
        $idcsmartSecurityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($idcsmartSecurityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $clientId = get_client_id();
        if($idcsmartSecurityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $rule = $param['rule'];
        if(empty($rule) || !is_array($rule)){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }
        $validate = new \addon\idcsmart_cloud\validate\IdcsmartSecurityGroupValidate();
        $config = get_security_group_protocol();

        foreach($rule as $k=>$v){
            if(!$validate->scene('create_rule')->check($v)){
                return ['status' => 400 , 'msg' => lang_plugins($validate->getError())];
            }
            if(isset($config[$v['protocol']]['port']) && !empty($config[$v['protocol']]['port'])){
                $rule[$k]['port'] = $config[$v['protocol']]['port'];
            }else{
                if(empty($v['port'])){
                    return ['status'=>400, 'msg'=>lang_plugins('please_input_port_range')];
                }
                if(!check_security_port($v['port'])){
                    return ['status'=>400, 'msg'=>lang_plugins('port_range_format_error')];
                }
            }
        }

        // 是否超出限制
        $num = $this->where('addon_idcsmart_security_group_id', $param['id'])
                ->count();
        if($num + count($rule) >= 100){
            return ['status'=>400, 'msg'=>lang_plugins('security_max_rule_num')];
        }

        $this->startTrans();
        try {
            // 批量添加,返回添加成功的数量
            $successNum = 0;

            foreach($rule as $v){
                $exist = $this->where('addon_idcsmart_security_group_id', $param['id'])
                    ->where('direction', $v['direction'])
                    ->where('protocol', $v['protocol'])
                    ->where('port', $v['port'])
                    ->where('ip', $v['ip'])
                    ->find();
                if(!empty($exist)){
                    continue;
                }
                $idcsmartSecurityGroupRule = $this->create([
                    'addon_idcsmart_security_group_id' => $param['id'],
                    'description' => $v['description'] ?? '',
                    'direction' => $v['direction'],
                    'protocol' => $v['protocol'],
                    'port' => $v['port'],
                    'ip' => $v['ip'],
                    'create_time' => time(),
                ]);
                if($idcsmartSecurityGroupRule){
                    $successNum++;
                    add_task([
                        'type' => 'addon_idcsmart_security_group_rule',
                        'rel_id' => $idcsmartSecurityGroupRule->id,
                        'description' => lang_plugins('addon_idcsmart_security_group_rule_create'),
                        'task_data' => [
                            'type' => 'create',
                            'id' => $param['id']
                        ]
                    ]);
                }
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success'), 'data' => ['success_num' => $successNum]];
    }

    /**
     * 时间 2023-08-23
     * @title 转换填充规则项
     * @desc  转换填充规则项
     * @author hh
     * @version v1
     * @param   array $rule - 规则数据
     */
    public static function transRule($rule){
        // 端口范围
        if(strpos($rule['port'], '-') !== false){
            $portRange = explode('-', $rule['port']);
            $rule['start_port'] = $portRange[0];
            $rule['end_port'] = $portRange[1];
        }else{
            $rule['start_port'] = $rule['port'];
            $rule['end_port'] = $rule['port'];
        }
        // ip范围
        if(strpos($rule['ip'], '/') !== false){
            $cidrArr = explode('/', $rule['ip']);

            if($cidrArr > 0 && $cidrArr <= 32){
                $rule['start_ip'] = $cidrArr[0];
                $rule['end_ip'] = long2ip(ip2long($cidrArr[0]) + pow(2, 32 - $cidrArr[1]) - 1);
            }else{
                $rule['start_ip'] = $cidrArr[0];
                $rule['end_ip'] = $cidrArr[0];
            }
        }else{
            $rule['start_ip'] = $rule['ip'];
            $rule['end_ip'] = $rule['ip'];
        }
        // 优先级固定高
        $rule['priority'] = 90;
        // 全是允许
        $rule['action'] = 'accept';
        return $rule;
    }



}