<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路带宽验证
 * @use  server\mf_cloud\validate\LineBwValidate
 */
class LineBwValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|integer|between:1,30000',
        'min_value'         => 'require|integer|between:1,30000',
        'max_value'         => 'require|integer|between:1,30000|egt:min_value',
        // 'step'              => 'require|integer|between:1,30000|checkStep:thinkphp',
        'price'             => 'checkPrice:thinkphp',
        'other_config'      => 'checkOtherConfig:thinkphp',
        'in_bw'             => 'integer|between:1,30000',
        'advanced_bw'       => 'integer',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'type.require'                  => 'please_select_line_bw_type',
        'type.in'                       => 'line_bw_type_error',
        'value.require'                 => 'please_input_bw',
        'value.integer'                 => 'line_bw_format_error',
        'value.between'                 => 'line_bw_format_error',
        'min_value.require'             => 'please_input_line_bw_min_value',
        'min_value.integer'             => 'line_bw_min_value_format_error',
        'min_value.between'             => 'line_bw_min_value_format_error',
        'max_value.require'             => 'please_input_line_bw_max_value',
        'max_value.integer'             => 'line_bw_max_value_format_error',
        'max_value.between'             => 'line_bw_max_value_format_error',
        'max_value.egt'                 => 'line_bw_max_value_must_gt_min_value',
        // 'step.require'                  => 'please_input_line_bw_step',
        // 'step.integer'                  => 'line_bw_step_format_error',
        // 'step.between'                  => 'line_bw_step_format_error',
        'price.checkPrice'              => 'price_cannot_lt_zero',
        'in_bw.integer'                 => 'mf_cloud_in_bw_format_error',
        'in_bw.between'                 => 'mf_cloud_in_bw_format_error',
        'advanced_bw.integer'           => 'advanced_bw_format_error',
    ];

    protected $scene = [
        'create'        => ['id','type','price','other_config'],
        'update'        => ['id','price','other_config'],
        'radio'         => ['value'],
        'step'          => ['min_value','max_value'],
        'other_config'  => ['in_bw','advanced_bw'],
        'line_create'   => ['type','price','other_config'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return 'price_must_between_0_999999';
            }
        }
        return true;
    }

    public function checkStep($value, $type, $arr){
        if($arr['type'] != 'radio' && $arr['step'] > $arr['max_value'] - $arr['min_value']){
            return 'step_must_gt_diff_of_max_and_min';
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