<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 型号配置验证
 * @use  server\mf_dcim\validate\ModelConfigValidate
 */
class ModelConfigValidate extends Validate
{
	protected $rule = [
        'id'                        => 'require|integer',
        'product_id'                => 'require|integer',
        'name'                      => 'require|length:1,100',
        'group_id'                  => 'require|integer|between:1,99999999',
        'cpu'                       => 'require|length:1,255',
        'cpu_param'                 => 'require|length:1,255',
        'memory'                    => 'require|length:1,255',
        'disk'                      => 'require|length:1,255',
        'price'                     => 'checkPrice:thinkphp',
        'support_optional'          => 'require|in:0,1',
        'optional_only_for_upgrade' => 'requireIf:support_optional,1|in:0,1',
        'optional_memory_id'        => 'array',
        'leave_memory'              => 'integer|between:0,99999999',
        'max_memory_num'            => 'integer|between:0,10000',
        'optional_disk_id'          => 'array',
        'max_disk_num'              => 'integer|between:0,10000',
        'order'                     => 'integer|between:0,999',
        'hidden'                    => 'require|in:0,1',
        'prev_model_config_id'      => 'require|integer',
        'gpu'                       => 'length:0,255',
        'optional_gpu_id'           => 'array',
        'max_gpu_num'               => 'integer|between:0,10000',
    ];

    protected $message = [
        'id.require'                            => 'id_error',
        'id.integer'                            => 'id_error',
        'product_id.require'                    => 'product_id_error',
        'product_id.integer'                    => 'product_id_error',
        'name.require'                          => 'mf_dcim_please_input_model_config_name',
        'name.length'                           => 'mf_dcim_model_config_name_length_error',
        'group_id.require'                      => 'mf_dcim_please_input_model_config_group_id',
        'group_id.integer'                      => 'mf_dcim_model_config_group_id_format_error',
        'group_id.between'                      => 'mf_dcim_model_config_group_id_format_error',
        'cpu.require'                           => 'mf_dcim_please_input_model_config_cpu',
        'cpu.length'                            => 'mf_dcim_model_config_cpu_length_error',
        'cpu_param.require'                     => 'mf_dcim_please_input_model_config_cpu_param',
        'cpu_param.length'                      => 'mf_dcim_model_config_cpu_param_length_error',
        'memory.require'                        => 'mf_dcim_please_input_model_config_memory',
        'memory.length'                         => 'mf_dcim_model_config_memory_length_error',
        'disk.require'                          => 'mf_dcim_please_input_model_config_disk',
        'disk.length'                           => 'mf_dcim_model_config_disk_length_error',
        'price.checkPrice'                      => 'mf_dcim_price_cannot_lt_zero',
        'support_optional.require'              => 'param_error',
        'support_optional.in'                   => 'param_error',
        'optional_only_for_upgrade.requireIf'   => 'param_error',
        'optional_only_for_upgrade.in'          => 'param_error',
        'optional_memory_id.array'              => 'param_error',
        'leave_memory.integer'                  => 'mf_dcim_leave_memory_format_error',
        'leave_memory.between'                  => 'mf_dcim_leave_memory_format_error',
        'max_memory_num.integer'                => 'mf_dcim_max_memory_num_format_error',
        'max_memory_num.between'                => 'mf_dcim_max_memory_num_format_error',
        'optional_disk_id.array'                => 'param_error',
        'max_disk_num.integer'                  => 'mf_dcim_max_disk_num_format_error',
        'max_disk_num.between'                  => 'mf_dcim_max_disk_num_format_error',
        'order.integer'                         => 'mf_dcim_order_format_error',
        'order.between'                         => 'mf_dcim_order_format_error',
        'hidden.require'                        => 'param_error',
        'hidden.in'                             => 'param_error',
        'prev_model_config_id.require'          => 'id_error',
        'prev_model_config_id.integer'          => 'id_error',
        'gpu.length'                            => 'mf_dcim_gpu_format_error',
        'optional_gpu_id.array'                 => 'param_error',
        'max_gpu_num.integer'                   => 'mf_dcim_max_gpu_num_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','group_id','cpu','cpu_param','memory','disk','price','support_optional','optional_only_for_upgrade','optional_memory_id','leave_memory','max_memory_num','optional_disk_id','max_disk_num','gpu','optional_gpu_id','max_gpu_num'],
        'update' => ['id','name','group_id','cpu','cpu_param','memory','disk','price','support_optional','optional_only_for_upgrade','optional_memory_id','leave_memory','max_memory_num','optional_disk_id','max_disk_num','gpu','optional_gpu_id','max_gpu_num'],
        'update_hidden' => ['id','hidden'],
        'drag'  => ['id','prev_model_config_id'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return 'mf_dcim_price_must_between_0_999999';
            }
        }
        return true;
    }


}