<?php
namespace server\idcsmart_cloud_disk\validate;

use think\Validate;

/**
 * 下单参数验证
 */
class CartValidate extends Validate
{
    protected $rule = [
        'duration_price_id'  => 'require|integer|gt:0',
        'package_id'         => 'require|integer|gt:0',
        'size'               => 'require|integer|gt:0',
    ];

    protected $message  =   [
        'duration_price_id.require'        => 'param_error',
        'duration_price_id.integer'        => 'param_error',
        'duration_price_id.gt'             => 'param_error',
        'package_id.require'            => 'param_error',
        'package_id.integer'            => 'param_error',
        'package_id.gt'                 => 'param_error',
        'size.require'                  => 'param_error',
        'size.integer'                  => 'param_error',
        'size.gt'                       => 'param_error',
    ];

    protected $scene = [
        'cal' => ['duration_price_id','package_id','size'],
    ];
}