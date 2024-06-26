<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 商标延伸服务商品验证
 */
class TrademarkServiceProductValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'title'         => 'require|max:15',
        'description'   => 'require',
        'price'         => 'require|float|egt:0',
        'product_id'    => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'title.require'         => 'trademark_service_product_title_require',
        'title.max'             => 'trademark_service_product_title_error',
        'description.require'   => 'trademark_service_product_description_require',
        'price.require'         => 'trademark_service_product_price_require',
        'price.float'           => 'trademark_service_product_price_error',
        'price.egt'             => 'trademark_service_product_price_error',
        'product_id.require'    => 'id_error',
        'product_id.integer'    => 'id_error',
        'product_id.gt'         => 'id_error',
    ];

    protected $scene = [
        'create' => ['title', 'description', 'price', 'product_id'],
        'update' => ['id', 'title', 'description', 'price', 'product_id'],
    ];
}