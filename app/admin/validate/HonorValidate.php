<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 荣誉资质验证
 */
class HonorValidate extends Validate
{
	protected $rule = [
		'id'    => 'require|integer|gt:0',
        'name'  => 'require|max:100',
        'img'   => 'require'
    ];

    protected $message = [
    	'id.require'    => 'id_error',
        'id.integer'    => 'id_error',
        'id.gt'         => 'id_error',
        'name.require'  => 'please_enter_honor_name',
        'name.max'      => 'honor_name_cannot_exceed_100_chars',
        'img.require'   => 'please_select_honor_image',
    ];

    protected $scene = [
        'create' => ['name', 'img'],
        'update' => ['id', 'name', 'img'],
    ];

}