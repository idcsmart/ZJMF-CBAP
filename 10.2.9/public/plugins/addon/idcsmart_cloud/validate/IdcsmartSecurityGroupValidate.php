<?php
namespace addon\idcsmart_cloud\validate;

use think\Validate;
use addon\idcsmart_cloud\IdcsmartCloud;

/**
 * 安全组管理验证
 */
class IdcsmartSecurityGroupValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'name'          => 'require|max:100',
        'type'          => 'in:host,lightHost,hyperv',
        'description'   => 'max:1000',
        'direction'     => 'require|in:in,out',
        'protocol'      => 'require|in:all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre',
        'port'          => 'require|checkPort:thinkphp',
        'ip'            => 'require|checkSecurityIp:thinkphp',
        'lock'          => 'in:0,1',
        // 轻量版参数
        'start_ip'      => 'require|ip',
        'end_ip'        => 'require|ip',
        'start_port'    => 'number|between:0,65535',
        'end_port'      => 'number|between:0,65535',
        'priority'      => 'number|between:0,9999',
        'action'        => 'require|in:accept,drop',
        'host_id'       => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'name.require'          => 'security_name_require',
        'name.length'           => 'security_name_length',
        'description.length'    => 'security_description_length',
        'type.in'               => 'security_type_in',
        'direction.require'     => 'security_rule_direction_require',
        'direction.in'          => 'security_rule_direction_in',
        'protocol.require'      => 'security_rule_protocol_require',
        'protocol.in'           => 'security_rule_protocol_in',
        'port.require'          => 'security_rule_port_require',
        'port.checkPort'        => 'port_error',
        'ip.require'            => 'security_rule_ip_require',
        'ip.checkSecurityIp'    => 'security_rule_ip_format',
        'lock.in'               => 'security_rule_lock_in',
        'start_ip.require'      => 'security_rule_start_ip_require',
        'start_ip.ip'           => 'security_rule_start_ip_ip',
        'end_ip.require'        => 'security_rule_end_ip_require',
        'end_ip.ip'             => 'security_rule_end_ip_ip',
        'start_port.number'     => 'security_rule_start_port_format',
        'start_port.between'    => 'security_rule_start_port_format',
        'end_port.number'       => 'security_rule_end_port_format',
        'end_port.between'      => 'security_rule_end_port_format',
        'priority.number'       => 'security_rule_priority_format',
        'priority.between'      => 'security_rule_priority_format',
        'action.require'        => 'security_rule_action_require',
        'action.in'             => 'security_rule_action_in',
        'host_id.require'       => 'id_error',
        'host_id.integer'       => 'id_error',
        'host_id.gt'            => 'id_error',
    ];

    protected $scene = [
        'create' => ['name', 'description'],
        'update' => ['id', 'name', 'description'],
        'create_rule' => ['description', 'direction', 'protocol', 'port', 'ip', 'lock'],
        'update_rule' => ['id', 'description', 'direction', 'protocol', 'port', 'ip', 'lock'],
        'light_create_rule' => ['description','direction','protocol','lock','start_ip','end_ip','start_port','end_port','priority','action'],
        'light_update_rule' => ['id', 'description','direction','protocol','lock','start_ip','end_ip','start_port','end_port','priority','action'],
        'link' => ['id', 'host_id'],
        'unlink' => ['id', 'host_id'],
    ];

    // Hyper-V创建规则
    public function sceneHypervCreateRule(){
        return $this->only(['description','direction','protocol','lock','ip','start_port','end_port','action'])
                    ->remove('ip', 'checkSecurityIp')
                    ->append('ip', 'checkHypervIp:thinkphp');
    }

    // Hyper-V修改规则
    public function sceneHypervUpdateRule(){
        return $this->only(['id', 'description','direction','protocol','lock','ip','start_port','end_port','action'])
                    ->remove('ip', 'checkSecurityIp')
                    ->append('ip', 'checkHypervIp:thinkphp');
    }


    public function checkPort($value, $rule, $data=[]){
        return check_security_port($value);
    }

    public function checkSecurityIp($value, $rule, $data=[]){
        return check_security_ip($value);
    }

    public function checkHypervIp($value, $rule, $data=[]){
        return check_ipsegment($value);
    }
}