<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 商品分组验证
 */
class ProductGroupValidate extends Validate
{
	protected $rule = [
		'id' 		                => 'require|integer',
        'name' 		                => 'require|min:1|max:100',
        'pre_product_group_id' 		=> 'require|integer',
        'pre_first_product_group_id'=> 'require|integer',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'name.require'     			=> 'please_enter_product_group_name',
        'name.min'     			    => 'product_group_name_cannot_exceed_1_chars',
        'name.max'     			    => 'product_group_name_cannot_exceed_100_chars',
        'pre_product_group_id.require'=> 'pre_product_group_id_require',
        'pre_product_group_id.integer'=> 'pre_product_group_id_integer',
        'pre_first_product_group_id.require'=> 'pre_first_product_group_id_require',
        'pre_first_product_group_id.integer'=> 'pre_first_product_group_id_integer',
    ];

    protected $scene = [
        'create' => ['id','name'],
        'edit' => ['id','name'],
        'order'=> ['pre_product_group_id','pre_first_product_group_id'],
        'order_first'=> ['pre_first_product_group_id'],
    ];

}