<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 带宽验证
 */
class BwValidate extends Validate
{
	protected $rule = [
        'id'                               => 'require|integer',
		'product_id' 		               => 'require|integer',
        'module_idcsmart_cloud_bw_type_id' => 'require|integer',
        'data_center_id'                   => 'require|array',
        'bw'                               => 'require|number|between:0,30000',
        'flow'                             => 'require|number|between:0,99999999',
        'price'                            => 'require|between:0,99999999',
        'description'                      => 'length:0,1000',
        'flow_type'                        => 'require|in:in,out,all',
        'in_bw_enable'                     => 'require|in:0,1',
        'in_bw'                            => 'requireIf:in_bw_enable,1|number|between:0,30000',
    ];

    protected $message  =   [
    	'id.require'     			               => 'id_error',
        'id.integer'                               => 'id_error',
        'product_id.require'                       => 'product_id_error',
    	'product_id.integer'     			       => 'product_id_error',
        'module_idcsmart_cloud_bw_type_id.require' => 'please_select_bw_type',
        'module_idcsmart_cloud_bw_type_id.integer' => 'please_select_bw_type',
        'data_center_id.require'                   => 'please_select_data_center',
        'data_center_id.array'                     => 'data_center_param_error',
        'bw.require'                               => 'please_input_bw',
        'bw.number'                                => 'bw_format_error',
        'bw.between'                               => 'bw_format_error',
        'flow.require'                             => 'please_input_flow',
        'flow.number'                              => 'flow_format_error',
        'flow.between'                             => 'flow_format_error',
        'price.require'                            => 'please_input_price',
        'price.between'                            => 'price_cannot_lt_zero',
        'description.length'                       => 'description_format_error',
        'flow_type.require'                        => 'flow_type_param_error',
        'flow_type.in'                             => 'flow_type_param_error',
        'in_bw_enable.require'                     => 'in_bw_param_error',
        'in_bw_enable.in'                          => 'in_bw_param_error',
        'in_bw.requireIf'                          => 'please_input_in_bw',
        'in_bw.number'                             => 'in_bw_format_error',
        'in_bw.between'                            => 'in_bw_format_error',
    ];

    protected $scene = [
        'create' => ['module_idcsmart_cloud_bw_type_id','data_center_id','bw','flow','price','description','product_id','flow_type','in_bw_enable','in_bw'],
        'edit' => ['id','module_idcsmart_cloud_bw_type_id','data_center_id','bw','flow','price','description','flow_type','in_bw_enable','in_bw'],
    ];

}