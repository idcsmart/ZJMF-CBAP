<?php
namespace server\common_cloud\validate;

use think\Validate;

/**
 * 镜像验证
 */
class ImageValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'enable'        => 'require|number|in:0,1',
        'charge'        => 'number|in:0,1',
        'price'         => 'requireIf:charge,1|float|between:0,999999',
        'product_id'    => 'require|number',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'enable.require'            => 'enable_param_require',
        'enable.number'             => 'enable_param_error',
        'enable.in'                 => 'enable_param_error',
        'charge.number'             => 'charge_param_error',
        'charge.in'                 => 'charge_param_error',
        'price.requireIf'           => 'price_format_error',
        'price.float'               => 'price_format_error',
        'price.between'             => 'price_must_between_0_999999',
        'product_id.require'        => 'product_id_error',
        'product_id.number'         => 'product_id_error',
    ];

    protected $scene = [
        'edit'    => ['id','enable','price','charge'],
        'enable'  => ['id','enable'],
        'sync'    => ['product_id']
    ];

}