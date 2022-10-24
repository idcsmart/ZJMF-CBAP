<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;
use server\idcsmart_cloud\logic\ToolLogic;

/**
 * 下单参数验证
 */
class CartValidate extends Validate
{
	protected $rule = [
        'data_center_id'     => 'require|integer',
        'package_id'         => 'require|integer',
        'image_id'           => 'require|integer',   // 镜像ID,暂时必须
        'hostname'           => 'require|checkHostname:thinkphp',
        'password'           => 'requireWithout:ssh_key_id|checkPassword:thinkphp',
        'ssh_key_id'         => 'requireWithout:password|number',
        'backup_enable'      => 'integer|in:0,1',
        'panel_enable'       => 'integer|in:0,1',
        'vpc_id'             => 'integer',
        'security_group_id'  => 'integer',
		'duration_price_id'  => 'require|integer',
    ];

    protected $message  =   [
    	'data_center_id.require'     	=> 'data_center_id_error',
    	'data_center_id.integer'     	=> 'data_center_id_error',
        'package_id.require'            => 'please_select_package',
        'package_id.integer'            => 'please_select_package',
        'image_id.require'              => 'please_select_image',
        'image_id.integer'              => 'please_select_image',
        'hostname.require'              => 'please_input_hostname',
        'hostname.checkHostname'        => 'hostname_foramt_error',
        'password.requireWithout'       => 'please_input_password',
        'password.checkPassword'        => 'password_format_error',
        'ssh_key_id.requireWithout'     => 'password_and_ssh_key_must_have_one',
        'ssh_key_id.number'             => 'ssh_key_format_error',
        'backup_enable.integer'         => 'auto_backup_param_error',
        'backup_enable.in'              => 'auto_backup_param_error',
        'panel_enable.integer'          => 'panel_enable_param_error',
        'panel_enable.in'               => 'panel_enable_param_error',
        'vpc_id.integer'                => 'vpc_network_param_error',
        'security_group_id.integer'     => 'vpc_network_param_error',
        'duration_price_id.require'     => 'duration_price_error',
        'duration_price_id.integer'     => 'duration_price_error',
    ];

    protected $scene = [
        'cal' => ['data_center_id','package_id','image_id','hostname','password','backup_enable','panel_enable','vpc_id','security_group_id','duration_price_id','ssh_key_id'],
    ];

    // 验证主机名
    public function checkHostname($value){
        return preg_match('/^[a-zA-Z][0-9a-zA-Z_\-.]{5,15}$/', $value) ? true : false;
    }

    // 验证密码
    public function checkPassword($value){
        if(is_null($value)){
            return true;
        }
        return ToolLogic::checkPassword($value);
    }

}