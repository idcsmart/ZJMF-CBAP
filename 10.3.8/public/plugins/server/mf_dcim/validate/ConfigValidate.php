<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 设置参数验证
 * @use server\mf_dcim\validate\ConfigValidate
 */
class ConfigValidate extends Validate{

	protected $rule = [
        'product_id'                    => 'require|integer',
        'rand_ssh_port'                 => 'require|in:0,1',
        'reinstall_sms_verify'          => 'require|in:0,1',
        'reset_password_sms_verify'     => 'require|in:0,1',
        'manual_resource'               => 'require|in:0,1',
        'level_discount_memory_order'   => 'require|in:0,1',
        'level_discount_memory_upgrade' => 'require|in:0,1',
        'level_discount_disk_order'     => 'require|in:0,1',
        'level_discount_disk_upgrade'   => 'require|in:0,1',
        'level_discount_bw_upgrade'     => 'require|in:0,1',
        'level_discount_ip_num_upgrade' => 'require|in:0,1',
        'optional_host_auto_create'     => 'require|in:0,1',
        'level_discount_gpu_order'      => 'require|in:0,1',
        'level_discount_gpu_upgrade'    => 'require|in:0,1',
    ];

    protected $message = [
        'product_id.require'                    => 'product_id_error',
        'product_id.integer'                    => 'product_id_error',
        'rand_ssh_port.require'                 => 'mf_dcim_rand_ssh_port_param_error',
        'rand_ssh_port.in'                      => 'mf_dcim_rand_ssh_port_param_error',
        'reinstall_sms_verify.require'          => 'mf_dcim_reinstall_sms_verify_param_error',
        'reinstall_sms_verify.in'               => 'mf_dcim_reinstall_sms_verify_param_error',
        'reset_password_sms_verify.require'     => 'mf_dcim_reset_password_sms_verify_param_error',
        'reset_password_sms_verify.in'          => 'mf_dcim_reset_password_sms_verify_param_error',
        'manual_resource.require'               => 'param_error',
        'manual_resource.in'                    => 'param_error',
        'level_discount_memory_order.require'   => 'param_error',
        'level_discount_memory_order.in'        => 'param_error',
        'level_discount_memory_upgrade.require' => 'param_error',
        'level_discount_memory_upgrade.in'      => 'param_error',
        'level_discount_disk_order.require'     => 'param_error',
        'level_discount_disk_order.in'          => 'param_error',
        'level_discount_disk_upgrade.require'   => 'param_error',
        'level_discount_disk_upgrade.in'        => 'param_error',
        'level_discount_bw_upgrade.require'     => 'param_error',
        'level_discount_bw_upgrade.in'          => 'param_error',
        'level_discount_ip_num_upgrade.require' => 'param_error',
        'level_discount_ip_num_upgrade.in'      => 'param_error',
        'optional_host_auto_create.require'     => 'param_error',
        'optional_host_auto_create.in'          => 'param_error',
        'level_discount_gpu_order.require'      => 'param_error',
        'level_discount_gpu_order.in'           => 'param_error',
        'level_discount_gpu_upgrade.require'    => 'param_error',
        'level_discount_gpu_upgrade.in'         => 'param_error',
    ];

    protected $scene = [
        'save'  => ['product_id','rand_ssh_port','reinstall_sms_verify','reset_password_sms_verify','manual_resource','level_discount_memory_order','level_discount_memory_upgrade','level_discount_disk_order','level_discount_disk_upgrade','level_discount_bw_upgrade','level_discount_ip_num_upgrade','optional_host_auto_create','level_discount_gpu_order','level_discount_gpu_upgrade'],
    ];

}