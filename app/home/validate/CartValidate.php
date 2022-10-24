<?php
namespace app\home\validate;

use think\Validate;

/**
 * 购物车验证
 */
class CartValidate extends Validate
{
	protected $rule = [
        'position'          => 'require|integer|egt:0',
        'product_id'        => 'require|integer|gt:0',
        'config_options'    => 'array',
        'qty'               => 'require|integer|gt:0',
        'positions'         => 'require|array',
    ];

    protected $message  =   [
        'position.require'          => 'position_error',
        'position.integer'          => 'position_error',
        'position.egt'              => 'position_error',
        'product_id.require'        => 'please_select_product',
        'product_id.integer'        => 'product_id_error',
        'product_id.gt'             => 'product_id_error', 
        'config_options.array'      => 'config_options_error',
        'qty.require'               => 'please_enter_qty',
        'qty.integer'               => 'qty_error', 
        'qty.gt'                    => 'qty_error',
        'positions.require'         => 'please_select_products_in_the_cart',
        'positions.array'           => 'please_select_products_in_the_cart', 
    ];

    protected $scene = [
        'create' => ['product_id', 'config_options', 'qty'],
        'update' => ['position', 'product_id', 'config_options', 'qty'],
        'update_qty' => ['position', 'qty'],
        'delete' => ['position'],
        'batch_delete' => ['positions'],
        'settle' => ['positions'],
    ];
}