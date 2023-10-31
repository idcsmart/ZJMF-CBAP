<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 线路防护验证
 * @use  server\mf_dcim\validate\LineDefenceValidate
 */
class LineDefenceValidate extends Validate{

	protected $rule = [
        'value'             => 'require|integer|between:1,999999',
        'price'             => 'checkPrice:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'mf_dcim_please_input_peak_defence',
        'value.integer'                 => 'mf_dcim_peak_defence_format_error',
        'value.between'                 => 'mf_dcim_peak_defence_format_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
    ];

    protected $scene = [
        'create'        => ['id','value','price'],
        'update'        => ['id','value','price'],
        'line_create'   => ['value','price'],
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