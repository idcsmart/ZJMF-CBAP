<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\model\DataCenterModel;
use server\mf_dcim\model\ConfigLimitModel;

/**
 * @title 下单参数验证
 * @use  server\mf_dcim\validate\CartValidate
 */
class CartValidate extends Validate
{
	protected $rule = [
        'data_center_id'        => 'require|integer',
        'line_id'               => 'require|integer',
        'model_config_id'       => 'require|integer',
        'image_id'              => 'require|integer',
        'duration_id'           => 'require|integer',
        'notes'                 => 'length:0,1000',
        'ip_num'                => 'require',
        'optional_memory'       => 'array|checkOption:thinkphp',
        'optional_disk'         => 'array|checkOption:thinkphp',
        'optional_gpu'          => 'array|checkOption:thinkphp',
        'peak_defence'          => 'integer',
    ];

    protected $message  =   [
    	'data_center_id.require'     	=> 'data_center_id_error',
        'data_center_id.integer'        => 'data_center_id_error',
        'line_id.require'               => 'mf_dcim_please_select_line',
        'line_id.integer'               => 'mf_dcim_please_select_line',
        'model_config_id.require'       => 'please_select_model_config',
        'model_config_id.integer'       => 'please_select_model_config',
        'image_id.require'              => 'mf_dcim_please_select_image',
        'image_id.integer'              => 'mf_dcim_please_select_image',
        'duration_id.require'           => 'mf_dcim_please_select_pay_duration',
        'duration_id.integer'           => 'mf_dcim_please_select_pay_duration',
        'notes.length'                  => 'mf_dcim_notes_length_error',
        'ip_num.require'                => 'mf_dcim_please_select_ip_num',
        'optional_memory.array'         => 'param_error',
        'optional_disk.array'           => 'param_error',
        'optional_gpu.array'            => 'param_error',
        'peak_defence.integer'          => 'param_error',
    ];

    protected $scene = [
        'cal'                   => ['duration_id','data_center_id','line_id','model_config_id','image_id','notes','ip_num','optional_memory','optional_disk','optional_gpu'],
        'calPrice'              => ['data_center_id','image_id','duration_id'],
        'all_duration_price'    => ['model_config_id','optional_memory','optional_disk','optional_gpu','line_id','peak_defence'],
    ];

    public function checkOption($value)
    {
        foreach($value as $v){
            if(!empty($v) && !is_numeric($v)){
                return 'param_error';
            }
        }
        return true;
    }

}