<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 设置参数验证
 * @use server\mf_cloud\validate\ConfigValidate
 */
class ConfigValidate extends Validate{

	protected $rule = [
        'product_id'                => 'require|integer',
        'host_prefix'               => 'require|length:1,15|checkHostPrefix:thinkphp',
        'host_length'               => 'require|between:6,25|checkHostLength:thinkphp',
        'node_priority'             => 'require|in:1,2,3',
        'ip_mac_bind'               => 'require|in:0,1',
        'support_ssh_key'           => 'require|in:0,1',
        'rand_ssh_port'             => 'require|in:0,1',
        'support_normal_network'    => 'require|in:0,1',
        'support_vpc_network'       => 'require|in:0,1|checkNetwork:thinkphp',
        'support_public_ip'         => 'requireIf:support_vpc_network,1|in:0,1',
        'backup_enable'             => 'require|in:0,1',
        'snap_enable'               => 'require|in:0,1',
        'disk_limit_enable'         => 'require|in:0,1',
        'status'                    => 'require|in:0,1',
        'reinstall_sms_verify'      => 'require|in:0,1',
        'reset_password_sms_verify' => 'require|in:0,1',
        'niccard'                   => 'require|integer|in:0,1,2,3',
        'cpu_model'                 => 'require|integer|in:0,1,2,3',
        'ipv6_num'                  => 'integer|between:0,1000',
        'nat_acl_limit'             => 'integer|between:0,1000',
        'nat_web_limit'             => 'integer|between:0,1000',
    ];

    protected $message = [
        'product_id.require'                => 'product_id_error',
        'product_id.integer'                => 'product_id_error',
        'host_prefix.require'               => '请输入主机名前缀',
        'host_prefix.length'                => '主机名前缀不能超过15个字',
        'host_prefix.checkHostPrefix'       => '主机名前缀只能由字母数字和“-”,“_”,“.”组成,且必须字母开头',
        'host_length.require'               => '请输入主机名长度',
        'host_length.between'               => '主机名长度只能6-25位',
        'node_priority.require'             => 'please_select_node_priority',
        'node_priority.in'                  => 'please_select_node_priority',
        'ip_mac_bind.require'               => 'ip_mac_bind_param_error',
        'ip_mac_bind.in'                    => 'ip_mac_bind_param_error',
        'support_ssh_key.require'           => 'support_ssh_key_param_error',
        'support_ssh_key.in'                => 'support_ssh_key_param_error',
        'rand_ssh_port.require'             => 'rand_ssh_port_param_error',
        'rand_ssh_port.in'                  => 'rand_ssh_port_param_error',
        'support_normal_network.require'    => 'support_normal_network_param_error',
        'support_normal_network.in'         => 'support_normal_network_param_error',
        'support_vpc_network.require'       => 'support_vpc_network_param_error',
        'support_vpc_network.in'            => 'support_vpc_network_param_error',
        'support_public_ip.require'         => 'support_public_ip_param_error',
        'support_public_ip.in'              => 'support_public_ip_param_error',
        'backup_enable.require'             => 'backup_enable_param_error',
        'backup_enable.in'                  => 'backup_enable_param_error',
        'snap_enable.require'               => 'snap_enable_param_error',
        'snap_enable.in'                    => 'snap_enable_param_error',
        'disk_limit_enable.require'         => 'disk_limit_enable_param_error',
        'disk_limit_enable.in'              => 'disk_limit_enable_param_error',
        'status.require'                    => 'please_put_status_param',
        'status.in'                         => 'please_put_status_param',
        'reinstall_sms_verify.require'      => '重装系统短信验证参数错误',
        'reinstall_sms_verify.in'           => '重装系统短信验证参数错误',
        'reset_password_sms_verify.require' => '重置密码短信验证参数错误',
        'reset_password_sms_verify.in'      => '重置密码短信验证参数错误',
        'niccard.require'                   => '请选择网卡驱动',
        'niccard.integer'                   => '网卡驱动参数错误',
        'niccard.in'                        => '网卡驱动参数错误',
        'cpu_model.require'                 => '请选择CPU模式',
        'cpu_model.integer'                 => 'CPU模式参数错误',
        'cpu_model.in'                      => 'CPU模式参数错误',
        'ipv6_num.integer'                  => 'IPv6数量只能是0-1000的整数',
        'ipv6_num.between'                  => 'IPv6数量只能是0-1000的整数',
        'nat_acl_limit.integer'             => 'NAT转发只能是0-1000的整数',
        'nat_acl_limit.between'             => 'NAT转发只能是0-1000的整数',
        'nat_web_limit.integer'             => 'NAT建站只能是0-1000的整数',
        'nat_web_limit.between'             => 'NAT建站只能是0-1000的整数',
    ];

    protected $scene = [
        'save'          => ['product_id','host_prefix','host_length','node_priority','ip_mac_bind','support_ssh_key','rand_ssh_port','support_normal_network','support_vpc_network','support_public_ip','backup_enable','snap_enable','reinstall_sms_verify','reset_password_sms_verify','niccard','cpu_model','ipv6_num','nat_acl_limit','nat_web_limit'],
        'disk_limit'    => ['disk_limit_enable'],
        'toggle'        => ['product_id','status'],
    ];

    public function checkHostPrefix($value){
        return (bool)preg_match('/^[a-zA-Z][0-9a-zA-Z\-_.]?+$/', $value);
    }

    public function checkHostLength($value, $type, $arr){
        if($value - strlen($arr['host_prefix']) < 6){
            return '主机名长度非前缀部分至少6位';
        }
        return true;
    }

    public function checkNetwork($value, $type, $arr){
        if(empty($arr['support_normal_network']) && empty($arr['support_vpc_network'])){
            return 'at_least_enable_one_network';
        }
        return true;
    }
    
    

}