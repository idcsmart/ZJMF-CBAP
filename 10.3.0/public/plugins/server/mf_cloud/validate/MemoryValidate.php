<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\ConfigModel;

/**
 * @title 内存配置验证
 * @use  server\mf_cloud\validate\MemoryValidate
 */
class MemoryValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|integer|checkValue:thinkphp',
        'min_value'         => 'require|integer|checkMinValue:thinkphp',
        'max_value'         => 'require|integer|checkMaxValue:thinkphp|gt:min_value',
        // 'step'              => 'require|integer|between:1,512|checkStep:thinkphp',
        'price'             => 'checkPrice:thinkphp',
        'memory_unit'       => 'in:GB,MB',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'type.require'                  => 'please_select_config_type',
        'type.in'                       => 'config_type_error',
        'value.require'                 => 'please_input_memory_value',
        'value.integer'                 => '内存容量只能是整数',
        // 'value.between'                 => 'memory_value_format_error',
        'min_value.require'             => 'please_input_memory_min_value',
        'min_value.integer'             => '内存最小值只能是整数',
        // 'min_value.between'             => 'memory_min_value_format_error',
        'max_value.require'             => 'please_input_memory_max_value',
        'max_value.integer'             => '内存最大值只能是整数',
        // 'max_value.between'             => 'memory_max_value_format_error',
        'max_value.gt'                  => 'memory_max_value_must_gt_memory_min_value',
        'step.require'                  => 'please_input_memory_step',
        'step.integer'                  => 'memory_step_format_error',
        'step.between'                  => 'memory_step_format_error',
        'price.checkPrice'              => 'price_cannot_lt_zero',
        'memory_unit.in'                => '内存单位错误',
    ];

    protected $scene = [
        'create' => ['product_id','type','price','memory_unit'],
        'update' => ['id','price'],
        'radio'  => ['value'],
        'step'   => ['min_value','max_value','step'],
    ];

    protected $memoryUnit = null;

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

    public function checkValue($value, $type, $param){
        $memoryUnit = '';
        $id = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::MEMORY)->value('id');
        if(empty($id)){
            if(isset($param['memory_unit']) && !empty($param['memory_unit'])){
                $memoryUnit = $param['memory_unit'];
            }
        }
        if(empty($memoryUnit)){
            $memoryUnit = ConfigModel::where('product_id', $param['product_id'])->value('memory_unit') ?? 'GB';
        }
        $this->memoryUnit = $memoryUnit;

        if($memoryUnit == 'MB'){
            if($value < 128 || $value > 524288){
                return '内存容量只能是128-524288的整数';
            }
        }else{
            if($value < 1 || $value > 512){
                return 'memory_value_format_error';
            }
        }
        return true;
    }

    public function checkMinValue($value, $type, $param){
        $memoryUnit = '';
        $id = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::MEMORY)->value('id');
        if(empty($id)){
            if(isset($param['memory_unit']) && !empty($param['memory_unit'])){
                $memoryUnit = $param['memory_unit'];
            }
        }
        if(empty($memoryUnit)){
            $memoryUnit = ConfigModel::where('product_id', $param['product_id'])->value('memory_unit') ?? 'GB';
        }
        $this->memoryUnit = $memoryUnit;

        if($memoryUnit == 'MB'){
            if($value < 128 || $value > 524288){
                return '内存最小值只能是128-524288的整数';
            }
        }else{
            if($value < 1 || $value > 512){
                return 'memory_min_value_format_error';
            }
        }
        return true;
    }

    public function checkMaxValue($value, $type, $param){
        if(!is_null($this->memoryUnit)){
            $memoryUnit = $this->memoryUnit;
        }else{
            $memoryUnit = '';
            $id = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::MEMORY)->value('id');
            if(empty($id)){
                if(isset($param['memory_unit']) && !empty($param['memory_unit'])){
                    $memoryUnit = $param['memory_unit'];
                }
            }
            if(empty($memoryUnit)){
                $memoryUnit = ConfigModel::where('product_id', $param['product_id'])->value('memory_unit') ?? 'GB';
            }
        }
        if($memoryUnit == 'MB'){
            if($value < 128 || $value > 524288){
                return '内存最大值只能是128-524288的整数';
            }
        }else{
            if($value < 1 || $value > 512){
                return 'memory_max_value_format_error';
            }
        }
        return true;
    }

}