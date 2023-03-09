<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 周期参数验证
 * @use  server\mf_dcim\validate\DurationValidate
 */
class DurationValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'name'              => 'require|length:1,10',
        'num'               => 'require|integer|between:1,999',
        'unit'              => 'require|in:hour,day,month',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'name.require'                  => 'mf_dcim_please_input_duration_name',
        'name.length'                   => 'mf_dcim_duration_name_length_error',
        'num.require'                   => 'mf_dcim_please_input_duration_num',
        'num.integer'                   => 'mf_dcim_duration_num_format_error',
        'num.between'                   => 'mf_dcim_duration_num_format_error',
        'unit.require'                  => 'mf_dcim_duration_unit_param_error',
        'unit.in'                       => 'mf_dcim_duration_unit_param_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','num','unit'],
        'update' => ['id','name','num','unit'],
        'delete' => ['id'],
    ];


}