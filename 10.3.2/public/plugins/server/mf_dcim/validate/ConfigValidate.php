<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 设置参数验证
 * @use server\mf_dcim\validate\ConfigValidate
 */
class ConfigValidate extends Validate{

	protected $rule = [
        'product_id'                => 'require|integer',
        'rand_ssh_port'             => 'require|in:0,1',
        'reinstall_sms_verify'      => 'require|in:0,1',
        'reset_password_sms_verify' => 'require|in:0,1',
    ];

    protected $message = [
        'product_id.require'                => 'product_id_error',
        'product_id.integer'                => 'product_id_error',
        'rand_ssh_port.require'             => 'mf_dcim_rand_ssh_port_param_error',
        'rand_ssh_port.in'                  => 'mf_dcim_rand_ssh_port_param_error',
        'reinstall_sms_verify.require'      => 'mf_dcim_reinstall_sms_verify_param_error',
        'reinstall_sms_verify.in'           => 'mf_dcim_reinstall_sms_verify_param_error',
        'reset_password_sms_verify.require' => 'mf_dcim_reset_password_sms_verify_param_error',
        'reset_password_sms_verify.in'      => 'mf_dcim_reset_password_sms_verify_param_error',
    ];

    protected $scene = [
        'save'  => ['product_id','rand_ssh_port','reinstall_sms_verify','reset_password_sms_verify'],
    ];

}