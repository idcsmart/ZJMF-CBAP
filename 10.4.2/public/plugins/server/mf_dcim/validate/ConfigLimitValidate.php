<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 配置限制验证
 * @use  server\mf_dcim\validate\ConfigLimitValidate
 */
class ConfigLimitValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'data_center_id'    => 'require|integer',
        'model_config_id'   => 'require|array',
        'line_id'           => 'integer',
        'min_bw'            => 'requireWith:max_bw|integer|between:0,99999999',
        'max_bw'            => 'requireWith:min_bw|integer|between:0,99999999|gt:min_bw',
        'min_flow'          => 'requireWith:max_flow|integer|between:0,99999999',
        'max_flow'          => 'requireWith:min_flow|integer|between:0,99999999|gt:min_flow',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'data_center_id.require'        => 'mf_dcim_please_select_data_center',
        'data_center_id.integer'        => 'mf_dcim_please_select_data_center',
        'model_config_id.require'       => 'please_select_model_config',
        'model_config_id.array'         => 'please_select_model_config',
        'line_id.integer'               => 'mf_dcim_please_select_line',
        'min_bw.requireWith'            => 'mf_dcim_please_input_bw_min_value',
        'min_bw.integer'                => 'mf_dcim_bw_min_value_format_error',
        'min_bw.between'                => 'mf_dcim_bw_min_value_format_error',
        'max_bw.requireWith'            => 'mf_dcim_please_input_bw_max_value',
        'max_bw.integer'                => 'mf_dcim_bw_max_value_format_error',
        'max_bw.between'                => 'mf_dcim_bw_max_value_format_error',
        'max_bw.gt'                     => 'mf_dcim_bw_max_value_must_gt_min_value',
        'min_flow.requireWith'          => 'mf_dcim_please_input_flow_min_value',
        'min_flow.integer'              => 'mf_dcim_flow_min_value_format_error',
        'min_flow.between'              => 'mf_dcim_flow_min_value_format_error',
        'max_flow.requireWith'          => 'mf_dcim_please_input_flow_max_value',
        'max_flow.integer'              => 'mf_dcim_flow_max_value_format_error',
        'max_flow.between'              => 'mf_dcim_flow_max_value_format_error',
        'max_flow.gt'                   => 'mf_dcim_flow_max_value_must_gt_min_value',
    ];

    protected $scene = [
        'create' => ['product_id','data_center_id','line_id','min_bw','max_bw','min_flow','max_flow','model_config_id'],
        'update' => ['id','data_center_id','line_id','min_bw','max_bw','min_flow','max_flow','model_config_id'],
    ];

    



}