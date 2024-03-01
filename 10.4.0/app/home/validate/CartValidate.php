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
        'product_id'        => 'requireWithout:products|integer|gt:0',
        'config_options'    => 'array',
        'qty'               => 'requireWithout:products|integer|gt:0',
        'positions'         => 'require|array',
        'products'          => 'requireWithout:product_id|checkProducts:thinkphp',
    ];

    protected $message  =   [
        'position.require'          => 'position_error',
        'position.integer'          => 'position_error',
        'position.egt'              => 'position_error',
        'product_id.requireWithout' => 'please_select_product',
        'product_id.require'        => 'please_select_product',
        'product_id.integer'        => 'product_id_error',
        'product_id.gt'             => 'product_id_error', 
        'config_options.array'      => 'config_options_error',
        'qty.requireWithout'        => 'please_enter_qty',
        'qty.require'               => 'please_enter_qty',
        'qty.integer'               => 'qty_error', 
        'qty.gt'                    => 'qty_error',
        'positions.require'         => 'please_select_products_in_the_cart',
        'positions.array'           => 'please_select_products_in_the_cart', 
        'products.requireWithout'   => 'param_error',
        'products.checkProducts'    => 'param_error',
    ];

    protected $scene = [
        'create' => ['product_id', 'config_options', 'qty', 'products'],
        'update_qty' => ['position', 'qty'],
        'delete' => ['position'],
        'batch_delete' => ['positions'],
        'settle' => ['positions'],
    ];

    # 登录验证
    public function sceneUpdate()
    {
        return $this->only(['position', 'product_id', 'config_options', 'qty'])
            ->remove('product_id', 'requireWithout')
            ->append('product_id', 'require')
            ->remove('qty', 'requireWithout')
            ->append('qty', 'require');
    }

    public function checkProducts($value)
    {
        if(!is_array($value)){
            return false;
        }
        foreach ($value as $k => $v) {
            if(!isset($v['product_id'])){
                return false;
            }
            if(!is_integer($v['product_id'])){
                return false;
            }
            if($v['product_id']<=0){
                return false;
            }
            if(!is_array($v['config_options'])){
                return false;
            }
            if(!isset($v['qty'])){
                return false;
            }
            if(!is_integer($v['qty'])){
                return false;
            }
            if($v['qty']<=0){
                return false;
            }
        }
        return true;
    }
}