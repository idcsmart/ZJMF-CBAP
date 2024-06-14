<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 配置限制验证(废弃)
 * @use  server\mf_cloud\validate\ConfigLimitValidate
 */
class ConfigLimitValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'type'              => 'require|in:cpu,data_center,image',
        'data_center_id'    => 'requireIf:type,data_center|integer',
        'cpu'               => 'require|array',
        'memory'            => 'require|array',
        'min_memory'        => 'require|integer|between:1,524288',
        'max_memory'        => 'require|integer|between:1,524288|gt:min_memory',
        'image_id'          => 'requireIf:type,image|integer',
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
        'cpu.require'                   => 'please_select_cpu_config',
        'cpu.array'                     => 'please_select_cpu_config',
        'memory.require'                => 'please_select_memory_config',
        'memory.array'                  => 'please_select_memory_config',
        'min_memory.require'            => 'please_input_memory_min_value',
        'min_memory.integer'            => 'please_input_memory_min_value',
        'min_memory.between'            => 'mf_cloud_min_memory_format_error',
        'max_memory.require'            => 'please_input_memory_max_value',
        'max_memory.integer'            => 'please_input_memory_max_value',
        'max_memory.between'            => 'mf_cloud_max_memory_format_error',
        'max_memory.gt'                 => 'memory_max_value_must_gt_memory_min_value',
        'image_id.requireIf'            => 'please_select_os',
        'image_id.integer'              => 'please_select_os',
    ];

    protected $scene = [
        'create'        => ['product_id','type','data_center_id','cpu','image_id'],
        'update'        => ['id','data_center_id','cpu','image_id'],
        'memory'        => ['memory'],
        'memory_range'  => ['min_memory','max_memory'],
    ];

    



}