<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 管理员分组验证
 */
class AdminRoleValidate extends Validate
{
	protected $rule = [
		'id' 		        => 'require|integer',
        'name' 		        => 'require|min:1|max:50',
        'description' 		=> 'min:1|max:1000',
        'auth'              => 'array',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'name.require'     			=> 'admin_role_name_cannot_empty',
        'name.min'     			    => 'admin_role_name_at_least_1_chars',
        'name.max'     			    => 'admin_role_name_cannot_exceed_50_chars',
        'description.min' 		    => 'admin_role_description_cannot_exceed_1_chars',
        'description.max' 		    => 'admin_role_description_cannot_exceed_1000_chars',
        'auth.array'                => 'auth_error',
    ];

    protected $scene = [
        'create' => ['name', 'description', 'auth'],
        'update' => ['id', 'name', 'description', 'auth'],
    ];
}