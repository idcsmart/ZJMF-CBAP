<?php
namespace reserver\mf_cloud\validate;

use think\Validate;
use reserver\mf_cloud\logic\ToolLogic;

/**
 * @title 下单参数验证
 * @use  reserver\mf_cloud\validate\CartValidate
 */
class CartValidate extends Validate
{
	protected $rule = [
        'cpu'                => 'require|integer',
        'memory'             => 'require|integer',
        'bw'                 => 'integer',
        'flow'               => 'integer',
        'peak_defence'       => 'integer',
    ];

    protected $message  =   [
        'cpu.require'                   => 'res_mf_cloud_cpu_require',
        'cpu.integer'                   => 'res_mf_cloud_cpu_require',
        'memory.require'                => 'res_mf_cloud_memory_require',
        'memory.integer'                => 'res_mf_cloud_memory_require',
        'bw.integer'                    => 'res_mf_cloud_bw_param_error',
        'flow.integer'                  => 'res_mf_cloud_flow_param_error',
        'peak_defence.integer'          => 'res_mf_cloud_peak_defence_param_error',
    ];

    protected $scene = [
        'upgrade_config' => ['cpu','memory','bw','flow','peak_defence'],
    ];

}