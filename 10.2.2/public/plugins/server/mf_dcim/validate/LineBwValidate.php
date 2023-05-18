<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 线路带宽验证
 * @use  server\mf_dcim\validate\LineBwValidate
 */
class LineBwValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|integer|between:1,30000',
        'min_value'         => 'require|integer|between:1,30000',
        'max_value'         => 'require|integer|between:1,30000|gt:min_value',
        'step'              => 'require|integer|between:1,30000|checkStep:thinkphp',
        'price'             => 'checkPrice:thinkphp',
        'other_config'      => 'checkOtherConfig:thinkphp',
        'in_bw'             => 'integer|between:1,30000',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'type.require'                  => 'mf_dcim_please_select_line_bw_type',
        'type.in'                       => 'mf_dcim_line_bw_type_error',
        'value.require'                 => 'mf_dcim_please_input_bw',
        'value.integer'                 => 'mf_dcim_line_bw_format_error',
        'value.between'                 => 'mf_dcim_line_bw_format_error',
        'min_value.require'             => 'mf_dcim_please_input_min_value',
        'min_value.integer'             => 'mf_dcim_line_bw_min_value_format_error',
        'min_value.between'             => 'mf_dcim_line_bw_min_value_format_error',
        'max_value.require'             => 'mf_dcim_please_input_max_value',
        'max_value.integer'             => 'mf_dcim_line_bw_max_value_format_error',
        'max_value.between'             => 'mf_dcim_line_bw_max_value_format_error',
        'max_value.gt'                  => 'mf_dcim_max_value_must_gt_min_value',
        'step.require'                  => 'mf_dcim_please_input_step',
        'step.integer'                  => 'mf_dcim_line_bw_step_format_error',
        'step.between'                  => 'mf_dcim_line_bw_step_format_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
        'in_bw.integer'                 => 'mf_dcim_in_bw_format_error',
        'in_bw.between'                 => 'mf_dcim_in_bw_format_error',
    ];

    protected $scene = [
        'create'        => ['id','type','price','other_config'],
        'update'        => ['id','price','other_config'],
        'radio'         => ['value'],
        'step'          => ['min_value','max_value','step'],
        'other_config'  => ['in_bw'],
        'line_create'   => ['type','price','other_config'],
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

    public function checkStep($value, $type, $arr){
        if($arr['type'] != 'radio' && $arr['step'] > $arr['max_value'] - $arr['min_value']){
            return 'mf_dcim_step_must_gt_diff_of_max_and_min';
        }
        return true;
    }

    public function checkOtherConfig($value){
        $LineBwValidate = new LineBwValidate();
        if(!$LineBwValidate->scene('other_config')->check($value)){
            return $LineBwValidate->getError();
        }
        return true;
    }


}