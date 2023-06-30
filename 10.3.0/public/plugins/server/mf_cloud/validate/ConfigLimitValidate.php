<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 配置限制验证
 * @use  server\mf_cloud\validate\ConfigLimitValidate
 */
class ConfigLimitValidate extends Validate{

    protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'type'              => 'require|in:cpu,data_center,line',
        'data_center_id'    => 'requireIf:type,data_center|integer',
        'line_id'           => 'requireIf:type,line|integer',
        'min_bw'            => 'requireIf:type,line|integer|between:0,99999999',
        'max_bw'            => 'requireIf:type,line|integer|between:0,99999999|gt:min_bw',
        'cpu'               => 'require|array',
        'memory'            => 'require|array',
        'min_memory'        => 'require|integer|between:1,524288',
        'max_memory'        => 'require|integer|between:1,524288|gt:min_memory',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'type.require'                  => 'config_limit_type_param_error',
        'type.in'                       => 'config_limit_type_param_error',
        'data_center_id.requireIf'      => 'please_select_data_center',
        'data_center_id.integer'        => 'please_select_data_center',
        'line_id.requireIf'             => 'please_select_bw_line',
        'line_id.integer'               => 'please_select_bw_line',
        'min_bw.integer'                => 'please_input_bw_min_value',
        'min_bw.integer'                => 'bw_min_value_format_error',
        'min_bw.between'                => 'bw_min_value_format_error',
        'max_bw.between'                => 'please_input_bw_max_value',
        'max_bw.integer'                => 'bw_max_value_format_error',
        'max_bw.between'                => 'bw_max_value_format_error',
        'max_bw.gt'                     => 'bw_max_value_must_gt_bw_min_value',
        'cpu.require'                   => 'please_select_cpu_config',
        'cpu.array'                     => 'please_select_cpu_config',
        'memory.require'                => 'please_select_memory_config',
        'memory.array'                  => 'please_select_memory_config',
        'min_memory.require'            => 'please_input_memory_min_value',
        'min_memory.integer'            => 'please_input_memory_min_value',
        'min_memory.between'            => '最小值只能是1-524288的整数',
        'max_memory.require'            => 'please_input_memory_max_value',
        'max_memory.integer'            => 'please_input_memory_max_value',
        'max_memory.between'            => '最大值只能是1-524288的整数',
        'max_memory.gt'                 => 'memory_max_value_must_gt_memory_min_value',
    ];

    protected $scene = [
        'create'        => ['product_id','type','data_center_id','line_id','cpu','min_bw','max_bw'],
        'update'        => ['id','data_center_id','line_id','cpu','min_bw','max_bw'],
        'memory'        => ['memory'],
        'memory_range'  => ['min_memory','max_memory'],
    ];

    



}