<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 设置参数验证
 * @use server\mf_cloud\validate\ConfigValidate
 */
class ConfigValidate extends Validate
{
	protected $rule = [
        'product_id'                => 'require|integer',
        'type'                      => 'require|in:host,lightHost,hyperv',
        'node_priority'             => 'require|in:1,2,3,4',
        'ip_mac_bind'               => 'in:0,1',
        'support_ssh_key'           => 'in:0,1',
        'rand_ssh_port'             => 'require|in:0,1,2',
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
        'disk_limit_switch'         => 'require|in:0,1',
        'disk_limit_num'            => 'require|integer|between:1,16',
        'free_disk_switch'          => 'require|in:0,1',
        'free_disk_size'            => 'requireIf:free_disk_switch,1|integer|between:1,1048576',
        'default_nat_acl'           => 'integer|in:0,1',
        'default_nat_web'           => 'integer|in:0,1',
        'rand_ssh_port_start'       => 'requireIf:rand_ssh_port,1|integer|between:100,65535',
        'rand_ssh_port_end'         => 'requireIf:rand_ssh_port,1|integer|between:100,65535|gt:rand_ssh_port_start',
        'rand_ssh_port_windows'     => 'requireIf:rand_ssh_port,2|integer|between:100,65535',
        'rand_ssh_port_linux'       => 'requireIf:rand_ssh_port,2|integer|checkPort:thinkphp',
        'default_one_ipv4'          => 'require|in:0,1',
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
        'disk_limit_switch.require'         => 'mf_cloud_disk_limit_switch_param_error',
        'disk_limit_switch.in'              => 'mf_cloud_disk_limit_switch_param_error',
        'disk_limit_num.require'            => 'mf_cloud_disk_limit_num_format_error',
        'disk_limit_num.integer'            => 'mf_cloud_disk_limit_num_format_error',
        'disk_limit_num.between'            => 'mf_cloud_disk_limit_num_format_error',
        'free_disk_switch.require'          => 'mf_cloud_free_disk_switch_param_error',
        'free_disk_switch.in'               => 'mf_cloud_free_disk_switch_param_error',
        'free_disk_size.requireIf'          => 'mf_cloud_free_disk_size_format_error',
        'free_disk_size.integer'            => 'mf_cloud_free_disk_size_format_error',
        'free_disk_size.between'            => 'mf_cloud_free_disk_size_format_error',
        'default_nat_acl.integer'           => 'param_error',
        'default_nat_acl.in'                => 'param_error',
        'default_nat_web.integer'           => 'param_error',
        'default_nat_web.in'                => 'param_error',
        'rand_ssh_port_start.requireIf'     => 'mf_cloud_rand_ssh_port_start_require',
        'rand_ssh_port_start.integer'       => 'mf_cloud_rand_ssh_port_start_format_error',
        'rand_ssh_port_start.between'       => 'mf_cloud_rand_ssh_port_start_format_error',
        'rand_ssh_port_end.requireIf'       => 'mf_cloud_rand_ssh_port_end_require',
        'rand_ssh_port_end.integer'         => 'mf_cloud_rand_ssh_port_end_format_error',
        'rand_ssh_port_end.between'         => 'mf_cloud_rand_ssh_port_end_format_error',
        'rand_ssh_port_end.gt'              => 'mf_cloud_rand_ssh_port_end_must_gt_start',
        'rand_ssh_port_windows.requireIf'   => 'mf_cloud_rand_ssh_port_windows_require',
        'rand_ssh_port_windows.integer'     => 'mf_cloud_rand_ssh_port_windows_format_error',
        'rand_ssh_port_windows.between'     => 'mf_cloud_rand_ssh_port_windows_format_error',
        'rand_ssh_port_linux.requireIf'     => 'mf_cloud_rand_ssh_port_linux_require',
        'rand_ssh_port_linux.integer'       => 'mf_cloud_rand_ssh_port_linux_format_error',
        'rand_ssh_port_linux.checkPort'     => 'mf_cloud_rand_ssh_port_linux_format_error',
        'default_one_ipv4.require'          => 'mf_cloud_default_one_ipv4_format_error',
        'default_one_ipv4.in'               => 'mf_cloud_default_one_ipv4_format_error',
    ];

    protected $scene = [
        'save'          => ['product_id','node_priority','ip_mac_bind','support_ssh_key','rand_ssh_port','support_normal_network','support_vpc_network','support_public_ip','backup_enable','snap_enable','reinstall_sms_verify','reset_password_sms_verify','niccard','cpu_model','ipv6_num','nat_acl_limit','nat_web_limit','default_nat_acl','default_nat_web','rand_ssh_port_start','rand_ssh_port_end','rand_ssh_port_windows','rand_ssh_port_linux','default_one_ipv4'],
        'toggle'        => ['product_id','status'],
        'check_clear'   => ['product_id','type'],
        'disk_num_limit'=> ['product_id','disk_limit_switch','disk_limit_num'],
        'free_disk'     => ['product_id','free_disk_switch','free_disk_size'],
    ];

    public function checkNetwork($value, $type, $arr){
        if($arr['type'] == 'host' && empty($arr['support_normal_network']) && empty($arr['support_vpc_network'])){
            return 'at_least_enable_one_network';
        }
        return true;
    }

    public function checkPort($value){
        return $value==22 || ($value>=100 && $value<=65535);
    }

}