<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 订单管理验证
 */
class OrderValidate extends Validate
{
	protected $rule = [
		'id' 		    				=> 'require|integer|gt:0',
		'type' 							=> 'require|in:new,renew,upgrade,artificial',
		'amount' 						=> 'requireIf:type,artificial|float|gt:0',
        'client_id'     				=> 'require|integer|gt:0',
        'description'                   => 'requireIf:type,artificial|max:1000',
        'delete_host'                   => 'require|in:0,1',
        'products'                      => 'requireIf:type,new|checkProducts:thinkphp',
        'host_id'                       => 'requireIf:type,upgrade|integer|gt:0',
        'product'                       => 'requireIf:type,upgrade|checkProduct:thinkphp',
        'use_credit'                    => 'require|in:0,1',
    ];

    protected $message  =   [
    	'id.require'     				=> 'id_error',
    	'id.integer'     				=> 'id_error',
        'id.gt'                         => 'id_error',
    	'type.require'        			=> 'please_select_order_type',
    	'type.in'        				=> 'order_type_error',
        'amount.requireIf'              => 'please_enter_amount',
    	'amount.require'    			=> 'please_enter_amount',
        'amount.float'    				=> 'amount_formatted_incorrectly',
        'amount.gt' 					=> 'amount_formatted_incorrectly',
        'client_id.require'     		=> 'please_select_client',
    	'client_id.integer'     		=> 'client_id_error',
        'client_id.gt'                  => 'client_id_error',
        'description.requireIf'         => 'please_enter_description',
        'description.require'           => 'please_enter_description',
        'description.max'               => 'description_cannot_exceed_1000_chars',
        'delete_host.require'           => 'please_select_order_delete_host',
        'delete_host.in'                => 'param_error',
        'products.requireIf'            => 'please_select_product',
        'products.checkProducts'        => 'param_error',
        'host_id.require'               => 'please_select_host',
        'host_id.requireIf'             => 'please_select_host',
        'host_id.integer'               => 'host_id_error',
        'host_id.gt'                    => 'host_id_error',
        'product.require'               => 'please_select_product',
        'product.requireIf'             => 'please_select_product',
        'product.checkProduct'          => 'param_error',
        'use_credit.require'            => 'param_error',
        'use_credit.in'                 => 'param_error',
    ];

    protected $scene = [
        'create' => ['type', 'amount', 'client_id', 'description', 'products', 'host_id', 'product'],
        'delete' => ['id', 'delete_host'],
        'paid' => ['id', 'use_credit'],
    ];

    # 修改金额验证
    public function sceneAmount()
    {
        return $this->only(['id', 'amount', 'description'])
            ->remove('amount', 'gt|requireIf')
            ->append('amount', 'require')
            ->remove('description', 'requireIf')
            ->append('description', 'require');
    }

    # 获取升降级订单金额
    public function sceneUpgrade()
    {
        return $this->only(['client_id', 'host_id', 'product'])
            ->remove('host_id', 'requireIf')
            ->append('host_id', 'require')
            ->remove('product', 'requireIf')
            ->append('product', 'require');
    }

    public function checkProducts($products)
    {
        if(is_array($products)){
            foreach ($products as $key => $value) {
                if(!isset($value['product_id']) || !is_integer($value['product_id']) || $value['product_id']<=0){
                    return false;
                }
                if(isset($value['config_options']) && !is_array($value['config_options'])){
                    return false;
                }
                if(!isset($value['qty']) || !is_integer($value['qty']) || $value['qty']<=0){
                    return false;
                }
                if(isset($value['price']) && (!is_float($value['price']) || $value['price']<0)){
                    return false;
                }
            }
        }else{
            return false;
        }
        return true;
    }

    public function checkProduct($product)
    {
        if(is_array($product)){
            if(!isset($product['product_id']) || !is_integer($product['product_id']) || $product['product_id']<=0){
                return false;
            }
            if(isset($product['config_options']) && !is_array($product['config_options'])){
                return false;
            }
            if(isset($product['price']) && (!is_float($product['price']) || $product['price']<0)){
                return false;
            }
        }else{
            return false;
        }
        return true;
    }
}