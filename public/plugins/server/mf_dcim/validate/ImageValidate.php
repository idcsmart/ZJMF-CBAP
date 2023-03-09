<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 操作系统验证
 * @use   server\mf_dcim\validate\ImageValidate
 */
class ImageValidate extends Validate
{
	protected $rule = [
		'id' 		        => 'require|integer',
        'product_id'        => 'require|integer',
        'image_group_id'    => 'require|integer',
        'name'              => 'require|length:1,255',
        'charge'            => 'require|integer|in:0,1',
        'price'             => 'requireIf:charge,1|float|between:0,999999',
        'enable'            => 'require|integer|in:0,1',
        'rel_image_id'      => 'require|integer',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'product_id.require'        => 'product_id_error',
        'product_id.integer'        => 'product_id_error',
        'image_group_id.require'    => 'mf_dcim_please_select_image_group',
        'image_group_id.integer'    => 'mf_dcim_please_select_image_group',
        'name.require'              => 'mf_dcim_please_input_image_name',
        'name.length'               => 'mf_dcim_image_name_length_error',
        'charge.require'            => 'mf_dcim_charge_param_error',
        'charge.integer'            => 'mf_dcim_charge_param_error',
        'charge.in'                 => 'mf_dcim_charge_param_error',
        'price.requireIf'           => 'mf_dcim_price_format_error',
        'price.float'               => 'mf_dcim_price_format_error',
        'price.between'             => 'mf_dcim_price_must_between_0_999999',
        'enable.require'            => 'mf_dcim_enable_param_require',
        'enable.integer'            => 'mf_dcim_enable_param_error',
        'enable.in'                 => 'mf_dcim_enable_param_error',
        'rel_image_id.require'      => 'mf_dcim_please_input_rel_image_id',
        'rel_image_id.integer'      => 'mf_dcim_please_input_rel_image_id',
    ];

    protected $scene = [
        'create'  => ['image_group_id','name','charge','price','enable','rel_image_id'],
        'update'  => ['id','image_group_id','name','charge','price','enable','rel_image_id'],
        'enable'  => ['id','enable'],
        'sync'    => ['product_id']
    ];

}