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
        'type'                      => 'require|in:host,lightHost,hyperv',
        'node_priority'             => 'require|in:1,2,3',
        'ip_mac_bind'               => 'in:0,1',
        'support_ssh_key'           => 'in:0,1',
        'rand_ssh_port'             => 'require|in:0,1',
        'support_normal_network'    => 'requireIf:type,host|in:0,1',
        'support_vpc_network'       => 'requireIf:type,host|in:0,1|checkNetwork:thinkphp',
        'support_public_ip'         => 'requireIf:support_vpc_network,1|in:0,1',
        'backup_enable'             => 'require|in:0,1',
        'snap_enable'               => 'in:0,1',
        'disk_limit_enable'         => 'require|in:0,1',
        'status'                    => 'require|in:0,1',
        'reinstall_sms_verify'      => 'require|in:0,1',
        'reset_password_sms_verify' => 'require|in:0,1',
        'niccard'                   => 'integer|in:0,1,2,3',
        'cpu_model'                 => 'integer|in:0,1,2,3',
        'ipv6_num'                  => 'integer|between:0,1000',
        'nat_acl_limit'             => 'integer|between:0,1000',
        'nat_web_limit'             => 'integer|between:0,1000',
    ];

    protected $message = [
        'product_id.require'                => 'product_id_error',
        'product_id.integer'                => 'product_id_error',
        'type.require'                      => 'mf_cloud_type_error',
        'type.in'                           => 'mf_cloud_type_error',
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
        'reinstall_sms_verify.require'      => 'mf_cloud_reinstall_sms_verify_param_error',
        'reinstall_sms_verify.in'           => 'mf_cloud_reinstall_sms_verify_param_error',
        'reset_password_sms_verify.require' => 'mf_cloud_reset_password_sms_verify_param_error',
        'reset_password_sms_verify.in'      => 'mf_cloud_reset_password_sms_verify_param_error',
        'niccard.require'                   => 'mf_cloud_niccard_require',
        'niccard.integer'                   => 'mf_cloud_niccard_param_error',
        'niccard.in'                        => 'mf_cloud_niccard_param_error',
        'cpu_model.require'                 => 'mf_cloud_cpu_model_require',
        'cpu_model.integer'                 => 'mf_cloud_cpu_model_param_error',
        'cpu_model.in'                      => 'mf_cloud_cpu_model_param_error',
        'ipv6_num.integer'                  => 'mf_cloud_ipv6_num_format_error',
        'ipv6_num.between'                  => 'mf_cloud_ipv6_num_format_error',
        'nat_acl_limit.integer'             => 'mf_cloud_nat_acl_limit_format_error',
        'nat_acl_limit.between'             => 'mf_cloud_nat_acl_limit_format_error',
        'nat_web_limit.integer'             => 'mf_cloud_nat_web_limit_format_error',
        'nat_web_limit.between'             => 'mf_cloud_nat_web_limit_format_error',
    ];

    protected $scene = [
        'save'          => ['product_id','node_priority','ip_mac_bind','support_ssh_key','rand_ssh_port','support_normal_network','support_vpc_network','support_public_ip','backup_enable','snap_enable','reinstall_sms_verify','reset_password_sms_verify','niccard','cpu_model','ipv6_num','nat_acl_limit','nat_web_limit'],
        'disk_limit'    => ['disk_limit_enable'],
        'toggle'        => ['product_id','status'],
        'check_clear'   => ['product_id','type'],
    ];

    public function checkNetwork($value, $type, $arr){
        if($arr['type'] == 'host' && empty($arr['support_normal_network']) && empty($arr['support_vpc_network'])){
            return 'at_least_enable_one_network';
        }
        return true;
    }

}