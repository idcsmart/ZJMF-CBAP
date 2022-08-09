<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 接口验证
 */
class ServerValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'name' 		    => 'require|min:1|max:50',
        'module'        => 'require|min:1|max:100',
        'url'           => 'require|url',
        'username'      => 'max:100',
        'password'      => 'max:100',
        'status'        => 'in:0,1',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'name.require'     			=> 'please_enter_server_name',
        'name.min'     			    => 'server_name_at_least_1_chars',
        'name.max'                  => 'server_name_cannot_exceed_50_chars',
        'module.require'            => 'please_select_module',
        'module.min'                => 'module_at_least_1_chars',
        'module.max'                => 'module_cannot_exceed_100_chars',
        'url.require'               => 'please_enter_url',
        'url.url'                   => 'please_enter_an_right_url',
        'username.max'              => 'server_username_cannot_exceed_100_chars',
        'password.max'              => 'server_password_cannot_exceed_100_chars',
        'status.in'     	        => 'server_status_only_zero_or_one',
    ];

    protected $scene = [
        'create' => ['name', 'module', 'url', 'username', 'password', 'status'],
        'update' => ['id', 'name', 'module', 'url', 'username', 'password', 'status'],
    ];

}