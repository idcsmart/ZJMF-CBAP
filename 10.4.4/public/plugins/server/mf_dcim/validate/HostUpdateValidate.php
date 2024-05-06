<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 保存参数验证
 * @use  server\mf_dcim\validate\HostUpdateValidate
 */
class HostUpdateValidate extends Validate
{
	protected $rule = [
        'model_config_name'         => 'length:0,255',
        'model_config_cpu'          => 'length:0,255',
        'model_config_cpu_param'    => 'length:0,255',
        'model_config_memory'       => 'length:0,2000',
        'model_config_disk'         => 'length:0,2000',
        'model_config_gpu'          => 'length:0,2000',
        'image'                     => 'integer',
        'bw'                        => 'checkBw:thinkphp',
        'in_bw'                     => 'integer|between:0,30000',
        'flow'                      => 'integer|between:0,999999',
        'defence'                   => 'integer|between:0,999999',
        'ip_num'                    => 'checkIpNum:thinkphp',
        'ip'                        => 'length:0,100',
        'additional_ip'             => 'length:0,65000',
        'zjmf_dcim_id'              => 'integer',
    ];

    protected $message = [
        'model_config_name.length'      => 'mf_dcim_model_config_name_length_error2',
        'model_config_cpu.length'       => 'mf_dcim_model_config_cpu_length_error',
        'model_config_cpu_param.length' => 'mf_dcim_model_config_cpu_param_length_error',
        'model_config_memory.length'    => 'mf_dcim_model_config_memory_length_error2',
        'model_config_disk.length'      => 'mf_dcim_model_config_disk_length_error2',
        'model_config_gpu.length'       => 'mf_dcim_model_config_gpu_length_error2',
        'image.integer'                 => 'mf_dcim_image_param_error',
        'in_bw.integer'                 => 'mf_dcim_in_bw_format_error_for_update',
        'in_bw.between'                 => 'mf_dcim_in_bw_format_error_for_update',
        'flow.integer'                  => 'mf_dcim_line_flow_format_error',
        'flow.between'                  => 'mf_dcim_line_flow_format_error',
        'defence.integer'               => 'mf_dcim_defence_format_error_for_update',
        'defence.between'               => 'mf_dcim_defence_format_error_for_update',
        'ip.length'                     => 'mf_dcim_ip_length_error',
        'additional_ip.length'          => 'mf_dcim_additional_ip_length_error',
        'zjmf_dcim_id.integer'          => 'mf_dcim_zjmf_dcim_id_param_error',
    ];

    protected $scene = [
        'update' => ['model_config_name','model_config_cpu','model_config_cpu_param','model_config_memory','model_config_disk','model_config_gpu','image','bw','in_bw','flow','defence','ip_num','ip','additional_ip','zjmf_dcim_id'],
    ];

    /**
     * 时间 2023-05-15
     * @title 验证带宽格式
     * @desc  验证带宽格式
     * @author hh
     * @version v1
     * @param   int|string $value - 带宽 require
     */
    public function checkBw($value)
    {
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || $value<1 || $value > 30000){
                return 'mf_dcim_line_bw_format_error';
            }
        }else if($value == 'NC'){

        }else{
            return 'mf_dcim_line_bw_format_error';
        }
        return true;
    }

    /**
     * 时间 2023-05-15
     * @title 验证IP数量格式
     * @desc  验证IP数量格式
     * @author hh
     * @version v1
     * @param   int|string $value - IP数量 require
     */
    public function checkIpNum($value)
    {
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || $value<1 || $value > 10000){
                return 'mf_dcim_line_ip_num_format_error';
            }
        }else if($value == 'NC'){

        }else{
            $value = ToolLogic::formatDcimIpNum($value);
            if($value === false){
                return 'mf_dcim_custom_ip_num_format_error';
            }
        }
        return true;
    }

}