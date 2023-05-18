<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 型号配置验证
 * @use  server\mf_dcim\validate\ModelConfigValidate
 */
class ModelConfigValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'name'              => 'require|length:1,100',
        'group_id'          => 'require|integer|between:1,99999999',
        'cpu'               => 'require|length:1,255',
        'cpu_param'         => 'require|length:1,255',
        'memory'            => 'require|length:1,255',
        'disk'              => 'require|length:1,255',
        'price'             => 'checkPrice:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'name.require'                  => 'mf_dcim_please_input_model_config_name',
        'name.length'                   => 'mf_dcim_model_config_name_length_error',
        'group_id.require'              => 'mf_dcim_please_input_model_config_group_id',
        'group_id.integer'              => 'mf_dcim_model_config_group_id_format_error',
        'group_id.between'              => 'mf_dcim_model_config_group_id_format_error',
        'cpu.require'                   => 'mf_dcim_please_input_model_config_cpu',
        'cpu.length'                    => 'mf_dcim_model_config_cpu_length_error',
        'cpu_param.require'             => 'mf_dcim_please_input_model_config_cpu_param',
        'cpu_param.length'              => 'mf_dcim_model_config_cpu_param_length_error',
        'memory.require'                => 'mf_dcim_please_input_model_config_memory',
        'memory.length'                 => 'mf_dcim_model_config_memory_length_error',
        'disk.require'                  => 'mf_dcim_please_input_model_config_disk',
        'disk.length'                   => 'mf_dcim_model_config_disk_length_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
    ];

    protected $scene = [
        'create' => ['product_id','name','group_id','cpu','cpu_param','memory','disk','price'],
        'update' => ['id','name','group_id','cpu','cpu_param','memory','disk','price'],
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