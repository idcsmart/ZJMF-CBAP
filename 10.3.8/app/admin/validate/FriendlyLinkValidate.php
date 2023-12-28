<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 友情链接验证
 */
class FriendlyLinkValidate extends Validate
{
	protected $rule = [
		'id'    => 'require|integer|gt:0',
        'name'  => 'require|max:100',
        'url'   => 'require|max:255|url'
    ];

    protected $message = [
    	'id.require'    => 'id_error',
        'id.integer'    => 'id_error',
        'id.gt'         => 'id_error',
        'name.require'  => 'please_enter_friendly_link_name',
        'name.max'      => 'friendly_link_name_cannot_exceed_100_chars',
        'url.require'   => 'please_enter_friendly_link_url',
        'url.max'       => 'friendly_link_url_cannot_exceed_255_chars',
        'url.url'       => 'friendly_link_url_error',
    ];

    protected $scene = [
        'create' => ['name', 'url'],
        'update' => ['id', 'name', 'url'],
    ];

}