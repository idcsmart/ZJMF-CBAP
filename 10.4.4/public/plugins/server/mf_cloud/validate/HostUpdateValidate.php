<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\ConfigModel;

/**
 * @title 保存参数验证
 * @use  server\mf_cloud\validate\HostUpdateValidate
 */
class HostUpdateValidate extends Validate
{
	protected $rule = [
        'cpu'                   => 'integer|between:1,240',
        'memory'                => 'integer|checkMemory:thinkphp',
        'bw'                    => 'integer|between:0,30000',
        'in_bw'                 => 'integer|between:0,30000',
        'out_bw'                => 'integer|between:0,30000',
        'flow'                  => 'integer|between:0,999999',
        'defence'               => 'integer|between:0,999999',
        'ip_num'                => 'integer|between:0,10000',
        'ip'                    => 'length:0,255',
    ];

    protected $message = [
        'cpu.integer'           => 'mf_cloud_update_cpu_format_error',
        'cpu.between'           => 'mf_cloud_update_cpu_format_error',
        'memory.integer'        => 'mf_cloud_update_memory_must_be_int',
        'bw.integer'            => 'mf_cloud_update_bw_format_error',
        'bw.between'            => 'mf_cloud_update_bw_format_error',
        'in_bw.integer'         => 'mf_cloud_update_in_bw_format_error',
        'in_bw.between'         => 'mf_cloud_update_in_bw_format_error',
        'out_bw.integer'        => 'mf_cloud_update_out_bw_format_error',
        'out_bw.between'        => 'mf_cloud_update_out_bw_format_error',
        'flow.integer'          => 'line_flow_format_error',
        'flow.between'          => 'line_flow_format_error',
        'defence.integer'       => 'mf_cloud_update_defence_format_error',
        'defence.between'       => 'mf_cloud_update_defence_format_error',
        'ip_num.integer'        => 'mf_cloud_update_ip_num_format_error',
        'ip_num.between'        => 'mf_cloud_update_ip_num_format_error',
        'ip.length'             => 'mf_cloud_update_ip_length_error',
    ];

    protected $scene = [
        'update' => ['cpu','memory','bw','in_bw','out_bw','flow','defence','ip_num','ip'],
    ];

     public function checkMemory($value, $type, $param){
        $memoryUnit = ConfigModel::where('product_id', $param['product_id'])->value('memory_unit') ?? 'GB';
        if($memoryUnit == 'MB'){
            if($value < 128 || $value > 524288){
                return 'mf_cloud_update_memory_format_error';
            }
        }else{
            if($value < 1 || $value > 512){
                return 'memory_value_format_error';
            }
        }
        return true;
    }

}