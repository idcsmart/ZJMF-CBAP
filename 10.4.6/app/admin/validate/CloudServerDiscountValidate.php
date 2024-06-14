<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 云服务器优惠验证
 */
class CloudServerDiscountValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'title'         => 'require|max:100',
        'description'   => 'require|max:1000',
        'url'           => 'require|max:255|url',
    ];

    protected $message = [
        'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'title.require'         => 'cloud_server_discount_title_require',
        'title.max'             => 'cloud_server_discount_title_error',
        'description.require'   => 'cloud_server_discount_description_require',
        'description.max'       => 'cloud_server_discount_description_error',
        'url.require'           => 'cloud_server_discount_url_require',
        'url.max'               => 'cloud_server_discount_url_error',
        'url.url'               => 'cloud_server_discount_url_error',
    ];

    protected $scene = [
        'create' => ['title', 'description', 'url'],
        'update' => ['id', 'title', 'description', 'url'],
    ];
}