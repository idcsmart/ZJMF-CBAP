<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 云服务器区域验证
 */
class CloudServerAreaValidate extends Validate
{
	protected $rule = [
		'id'            => 'require|integer|gt:0',
        'first_area'    => 'require|max:50',
        'second_area'   => 'require|max:50'
    ];

    protected $message = [
    	'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'first_area.require'    => 'cloud_server_area_first_area_require',
        'first_area.max'        => 'cloud_server_area_first_area_error',
        'second_area.require'   => 'cloud_server_area_second_area_require',
        'second_area.max'       => 'cloud_server_area_second_area_error',
    ];

    protected $scene = [
        'create' => ['first_area', 'second_area'],
        'update' => ['id', 'first_area', 'second_area'],
    ];

}