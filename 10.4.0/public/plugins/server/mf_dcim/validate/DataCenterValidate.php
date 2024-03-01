<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 数据中心验证
 * @use server\mf_dcim\validate
 */
class DataCenterValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
		'country_id' 		=> 'require|integer',
        'product_id'        => 'require|integer',
        'city'              => 'require|length:1,255',
        'area'              => 'require|length:1,255',
        'order'             => 'require|number|between:0,999',
    ];

    protected $message = [
    	'id.require'     		    => 'id_error',
    	'id.integer'     		    => 'id_error',
        'country_id.require'        => 'mf_dcim_country_select_error',
        'country_id.integer'        => 'mf_dcim_country_select_error',
        'product_id.require'        => 'product_id_error',
        'product_id.integer'        => 'product_id_error',
        'city.require'              => 'mf_dcim_please_input_city',
        'city.length'               => 'mf_dcim_city_format_error',
        'area.require'              => 'mf_dcim_please_input_area',
        'area.length'               => 'mf_dcim_area_format_error',
        'order.require'             => 'mf_dcim_order_require',
        'order.number'              => 'mf_dcim_order_format_error',
        'order.between'             => 'mf_dcim_order_format_error',
    ];

    protected $scene = [
        'create' => ['country_id','product_id','city','area','order'],
        'update' => ['id','country_id','city','area','order'],
    ];

}