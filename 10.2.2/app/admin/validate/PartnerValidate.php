<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 合作伙伴验证
 */
class PartnerValidate extends Validate
{
	protected $rule = [
		'id'            => 'require|integer|gt:0',
        'name'          => 'require|max:100',
        'img'           => 'require',
        'description'   => 'require',
    ];

    protected $message = [
    	'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'name.require'          => 'please_enter_partner_name',
        'name.max'              => 'partner_name_cannot_exceed_100_chars',
        'img.require'           => 'please_select_partner_image',
        'description.require'   => 'please_enter_partner_description',
    ];

    protected $scene = [
        'create' => ['name', 'img', 'description'],
        'update' => ['id', 'name', 'img', 'description'],
    ];

}