<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 商品验证
 */
class ProductValidate extends Validate
{
	protected $rule = [
		'id' 		                => 'require|integer',
        'name' 		                => 'require|max:100',
        'hidden' 		            => 'in:0,1',
        'stock_control' 		    => 'in:0,1',
        'qty' 		                => 'number',
        'description' 		        => 'max:10000',
        'creating_notice_sms' 		=> 'in:0,1',
        'created_notice_sms' 		=> 'in:0,1',
        'creating_notice_mail' 		=> 'in:0,1',
        'created_notice_mail' 		=> 'in:0,1',
        'pre_product_id' 		    => 'require|integer',
        'product_group_id' 		    => 'require|integer',
        'pay_type' 		            => 'require|in:free,onetime,recurring_prepayment,recurring_postpaid',
        'auto_setup' 		        => 'require|in:0,1',
        'type' 		                => 'require|in:server,server_group',
        'rel_id'                    => 'require|integer',
        'product_id'                => 'integer|egt:0',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'name.require'     			=> 'please_enter_product_name',
        'name.max'     			    => 'product_name_cannot_exceed_100_chars',
        'hidden.in'     			=> 'product_hidden',
        'stock_control.in'     		=> 'product_stock_control',
        'qty.number'     		    => 'product_qty_num',
        'description.max'     		=> 'product_description_max',
        'creating_notice_sms.in'    => 'product_creating_notice_sms',
        'created_notice_sms.in'     => 'product_created_notice_sms',
        'creating_notice_mail.in'   => 'product_creating_notice_mail',
        'created_notice_mail.in'    => 'product_created_notice_mail',
        'pre_product_id.require'    => 'pre_product_id_require',
        'pre_product_id.integer'    => 'pre_product_id_integer',
        'product_group_id.require'  => 'product_group_id_require',
        'product_group_id.integer'  => 'product_group_id_integer',
        'pay_type.require'          => 'product_pay_type_require',
        'pay_type.in'               => 'product_pay_type_in',
        'auto_setup.require'        => 'product_auto_setup_require',
        'auto_setup.in'             => 'product_auto_setup_in',
        'type.require'              => 'product_type_require',
        'type.in'                   => 'product_type_in',
        'rel_id.require'            => 'product_rel_id_require',
        'rel_id.integer'            => 'product_rel_id_integer',
        'product_id.integer'        => 'parent_product_id_integer',
        'product_id.egt'            => 'parent_product_id_integer',
    ];

    protected $scene = [
        'create' => ['name'],
        'edit' => ['id','name','hidden','stock_control','qty','creating_notice_sms','created_notice_sms','creating_notice_mail','description','pay_type','product_id'],
        'edit_server' => ['id','auto_setup','type','rel_id'],
        'order' => ['pre_product_id','product_group_id'],
        'module_server_config_option' => ['id', 'type', 'rel_id'],
    ];

}