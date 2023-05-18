<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 内存配置验证
 * @use  server\mf_cloud\validate\MemoryValidate
 */
class MemoryValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|integer|between:1,512',
        'min_value'         => 'require|integer|between:1,512',
        'max_value'         => 'require|integer|between:1,512|gt:min_value',
        'step'              => 'require|integer|between:1,512|checkStep:thinkphp',
        'price'             => 'checkPrice:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'type.require'                  => 'please_select_config_type',
        'type.in'                       => 'config_type_error',
        'value.require'                 => 'please_input_memory_value',
        'value.integer'                 => 'memory_value_format_error',
        'value.between'                 => 'memory_value_format_error',
        'min_value.require'             => 'please_input_memory_min_value',
        'min_value.integer'             => 'memory_min_value_format_error',
        'min_value.between'             => 'memory_min_value_format_error',
        'max_value.require'             => 'please_input_memory_max_value',
        'max_value.integer'             => 'memory_max_value_format_error',
        'max_value.between'             => 'memory_max_value_format_error',
        'max_value.gt'                  => 'memory_max_value_must_gt_memory_min_value',
        'step.require'                  => 'please_input_memory_step',
        'step.integer'                  => 'memory_step_format_error',
        'step.between'                  => 'memory_step_format_error',
        'price.checkPrice'              => 'price_cannot_lt_zero',
    ];

    protected $scene = [
        'create' => ['product_id','type','price'],
        'update' => ['id','price'],
        'radio'  => ['memory'],
        'step'   => ['min_value','max_value','step'],
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
        if($arr['step'] > $arr['max_value'] - $arr['min_value']){
            return 'step_must_gt_diff_of_max_and_min';
        }
        return true;
    }



}