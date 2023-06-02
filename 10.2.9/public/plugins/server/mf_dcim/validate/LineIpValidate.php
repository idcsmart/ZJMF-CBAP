<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 线路IP验证
 * @use  server\mf_dcim\validate\LineIpValidate
 */
class LineIpValidate extends Validate{

	protected $rule = [
        'value'             => 'require|length:1,255|checkIpNum:thinkphp',
        'price'             => 'checkPrice:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'mf_dcim_please_input_line_ip_num',
        'value.length'                  => 'IP数量不能超过255个字',
        // 'value.integer'                 => 'mf_dcim_line_ip_num_format_error',
        // 'value.between'                 => 'mf_dcim_line_ip_num_format_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
    ];

    protected $scene = [
        'create'        => ['id','value','price'],
        'update'        => ['id','value','price'],
        'line_create'   => ['value','price'],
        'option_create' => ['value'],
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

    /**
     * 时间 2023-05-15
     * @title 验证IP数量格式
     * @desc  验证IP数量格式
     * @author hh
     * @version v1
     * @param   int|string $value - IP数量 require
     */
    public function checkIpNum($value){
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || $value<1 || $value > 10000){
                return 'mf_dcim_line_ip_num_format_error';
            }
        }else if($value == 'NC'){

        }else{
            $value = ToolLogic::formatDcimIpNum($value);
            if($value === false){
                return 'IP数量格式错误';
            }
        }
        return true;
    }



}