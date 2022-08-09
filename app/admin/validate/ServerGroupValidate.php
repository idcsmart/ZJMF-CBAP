<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 接口分组验证
 */
class ServerGroupValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'name' 		    => 'require|min:1|max:50',
        'server_id'     => 'require|array'
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'name.require'     			=> 'please_enter_server_group_name',
        'name.min'     			    => 'server_group_name_at_least_1_chars',
        'name.max'                  => 'server_group_name_cannot_exceed_50_chars',
        'server_id.require'         => 'please_select_server',
        'server_id.array'           => 'server_must_be_array',
    ];

    protected $scene = [
        'create' => ['name', 'server_id'],
        'update' => ['id', 'name', 'server_id'],
    ];

}