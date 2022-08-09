<?php
namespace app\home\validate;

use think\Validate;

/**
 * 商品验证
 */
class ProductValidate extends Validate
{
	protected $rule = [
        'product_id'        => 'require|integer|gt:0',
        'config_options'    => 'array',
        'qty'               => 'require|integer|gt:0',
    ];

    protected $message  =   [
        'product_id.require'        => 'please_select_product',
        'product_id.integer'        => 'product_id_error',
        'product_id.gt'             => 'product_id_error', 
        'config_options.array'      => 'config_options_error',
        'qty.require'               => 'please_enter_qty',
        'qty.integer'               => 'qty_error', 
        'qty.gt'                    => 'qty_error',
    ];

    protected $scene = [
        'settle' => ['product_id', 'config_options', 'qty'],
    ];
}