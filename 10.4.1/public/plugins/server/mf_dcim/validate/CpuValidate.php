<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 处理器验证
 * @use  server\mf_dcim\validate\CpuValidate
 */
class CpuValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'value'             => 'require|length:1,255',
        'price'             => 'checkPrice:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'mf_dcim_cpu_value_require',
        'value.length'                  => 'mf_dcim_cpu_value_length_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
    ];

    protected $scene = [
        'create'        => ['product_id','value','price'],
        'update'        => ['id','value','price'],
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