<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 短信服务商品验证
 */
class SmsServiceProductValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'title'         => 'require|max:15',
        'description'   => 'require',
        'price'         => 'require|float|egt:0',
        'price_unit'    => 'require|in:month,year',
        'product_id'    => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'title.require'         => 'sms_service_product_title_require',
        'title.max'             => 'sms_service_product_title_error',
        'description.require'   => 'sms_service_product_description_require',
        'price.require'         => 'sms_service_product_price_require',
        'price.float'           => 'sms_service_product_price_error',
        'price.egt'             => 'sms_service_product_price_error',
        'price_unit.require'    => 'param_error',
        'price_unit.in'         => 'param_error',
        'product_id.require'    => 'id_error',
        'product_id.integer'    => 'id_error',
        'product_id.gt'         => 'id_error',
    ];

    protected $scene = [
        'create' => ['title', 'description', 'price', 'price_unit', 'product_id'],
        'update' => ['id', 'title', 'description', 'price', 'price_unit', 'product_id'],
    ];
}