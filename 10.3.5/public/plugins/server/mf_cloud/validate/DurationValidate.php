<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 周期参数验证
 * @use  server\mf_cloud\validate\DurationValidate
 */
class DurationValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'name'              => 'require|length:1,10',
        'num'               => 'require|integer|between:1,999',
        'unit'              => 'require|in:hour,day,month',
        'price_factor'      => 'float|between:0,9999',
        'price'             => 'float|between:0,99999999',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'name.require'                  => 'please_input_duration_name',
        'name.length'                   => 'duration_name_format_error',
        'num.require'                   => 'please_input_duration_num',
        'num.integer'                   => 'duration_num_format_error',
        'num.between'                   => 'duration_num_format_error',
        'unit.require'                  => 'duration_unit_param_error',
        'unit.in'                       => 'duration_unit_param_error',
        'price_factor.float'            => 'mf_cloud_price_factor_format_error',
        'price_factor.between'          => 'mf_cloud_price_factor_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','num','unit','price_factor','price'],
        'update' => ['id','name','num','unit','price_factor','price'],
        'delete' => ['id'],
    ];


}