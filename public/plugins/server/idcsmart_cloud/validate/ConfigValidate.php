<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 设置验证
 */
class ConfigValidate extends Validate
{
	protected $rule = [
		'backup_enable' 	=> 'require|integer|in:0,1',
        'backup_price'      => 'require|float|between:0,99999999',
        'backup_param'      => 'length:0,255',
        'panel_enable'      => 'require|integer|in:0,1',
        'panel_price'       => 'require|float|between:0,99999999',
        'panel_param'       => 'length:0,255',
        'snap_enable'       => 'require|integer|in:0,1',
        'snap_free_num'     => 'require|number',
        'snap_price'        => 'require|float|between:0,99999999',
        'hostname_rule'     => 'require|number|in:1,2,3',
        'product_id'        => 'require|number',
    ];

    protected $message  =   [
        'backup_enable.require' => 'auto_backup_param_error',
        'backup_enable.integer' => 'auto_backup_param_error',
        'backup_enable.in'      => 'auto_backup_param_error',
        'backup_price.require'  => 'please_input_backup_price',
        'backup_price.float'    => 'backup_price_format_error',
        'backup_price.between'  => 'backup_price_format_error',
        'backup_param.length'   => 'backup_param_length_error',
        'panel_enable.require'  => 'panel_enable_param_error',
        'panel_enable.integer'  => 'panel_enable_param_error',
        'panel_enable.in'       => 'panel_enable_param_error',
        'panel_price.require' => 'please_input_panel_price',
        'panel_price.float' => 'panel_price_format_error',
        'panel_price.between' => 'panel_price_format_error',
        'panel_param.length' => 'panel_param_length_error',
        'snap_enable.require' => 'enable_snapshot_param_error',
        'snap_enable.integer' => 'enable_snapshot_param_error',
        'snap_enable.in' => 'enable_snapshot_param_error',
        'snap_free_num.require' => 'please_input_snapshot_free_num',
        'snap_free_num.number' => 'snapshot_free_num_is_number',
        'snap_price.require' => 'please_input_snapshot_price',
        'snap_price.float' => 'snapshot_price_format_error',
        'snap_price.between' => 'snapshot_price_format_error',
        'hostname_rule.require' => 'please_select_hostname_rule',
        'hostname_rule.number' => 'hostname_rule_param_error',
        'hostname_rule.in' => 'hostname_rule_param_error',
        'product_id.require' => 'product_id_error',
        'product_id.number' => 'product_id_error',
    ];

    protected $scene = [
        'save' => ['backup_enable','backup_price','backup_param','panel_enable','panel_price','panel_param','snap_enable','snap_free_num','snap_price','hostname_rule','product_id'],
    ];


}