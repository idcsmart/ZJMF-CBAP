<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title CPU配置验证
 * @use  server\mf_cloud\validate\CpuValidate
 */
class CpuValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'value'             => 'require|integer|between:1,240',
        'price'             => 'checkPrice:thinkphp',
        'other_config'      => 'array|checkOtherConfig:thinkphp',
        'advanced_cpu'      => 'integer',
        'cpu_limit'         => 'integer|between:0,100',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'please_input_cpu_core',
        'value.integer'                 => 'cpu_core_format_error',
        'value.between'                 => 'cpu_core_format_error',
        'price.checkPrice'              => 'price_must_between_0_999999',
        'other_config.array'            => 'other_config_param_error',
        'advanced_cpu.integer'          => 'advanced_cpu_rule_error',
        'cpu_limit.integer'             => 'cpu_limit_format_error',
        'cpu_limit.between'             => 'cpu_limit_format_error',
    ];

    protected $scene = [
        'create'        => ['product_id','value','price','other_config'],
        'update'        => ['id','value','price','other_config'],
        'other_config'  => ['advanced_cpu','cpu_limit'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return false;
            }
        }
        return true;
    }

    public function checkOtherConfig($value){
        $CpuValidate = new CpuValidate();
        if(!$CpuValidate->scene('other_config')->check($value)){
            return $CpuValidate->getError();
        }
        return true;
    }

}