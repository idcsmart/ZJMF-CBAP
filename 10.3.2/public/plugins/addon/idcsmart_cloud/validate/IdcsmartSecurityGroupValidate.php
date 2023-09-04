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
        'description'   => 'max:1000',
        'direction'     => 'require|in:in,out',
        'protocol'      => 'require|in:all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis',
        'port'          => 'require|checkPort:thinkphp',
        'ip'            => 'require|checkSecurityIp:thinkphp',
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
        'host_id.require'       => 'id_error',
        'host_id.integer'       => 'id_error',
        'host_id.gt'            => 'id_error',
    ];

    protected $scene = [
        'create'        => ['name', 'description'],
        'update'        => ['id', 'name', 'description'],
        'create_rule'   => ['description', 'direction', 'protocol', 'port', 'ip'],
        'update_rule'   => ['id', 'description', 'direction', 'protocol', 'port', 'ip'],
        'link'          => ['id', 'host_id'],
        'unlink'        => ['id', 'host_id'],
    ];


    public function checkPort($value, $rule, $data=[]){
        return check_security_port($value);
    }

    public function checkSecurityIp($value, $rule, $data=[]){
        return check_security_ip($value);
    }
}