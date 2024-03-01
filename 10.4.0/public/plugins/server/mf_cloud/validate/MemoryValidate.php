<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\ConfigModel;

/**
 * @title 内存配置验证
 * @use  server\mf_cloud\validate\MemoryValidate
 */
class MemoryValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|integer|checkValue:thinkphp',
        'min_value'         => 'require|integer|checkMinValue:thinkphp',
        'max_value'         => 'require|integer|checkMaxValue:thinkphp|egt:min_value',
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
        'value.integer'                 => 'mf_cloud_memory_value_must_be_int',
        'min_value.require'             => 'please_input_memory_min_value',
        'min_value.integer'             => 'mf_cloud_memory_min_value_must_be_int',
        'max_value.require'             => 'please_input_memory_max_value',
        'max_value.integer'             => 'mf_cloud_memory_max_value_must_be_int',
        'max_value.egt'                 => 'memory_max_value_must_gt_memory_min_value',
        'price.checkPrice'              => 'price_cannot_lt_zero',
        'memory_unit.in'                => 'mf_cloud_memory_unit_error',
    ];

    protected $scene = [
        'create' => ['product_id','type','price','memory_unit'],
        'update' => ['id','price'],
        'radio'  => ['value'],
        'step'   => ['min_value','max_value'],
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
                return 'mf_cloud_memory_mb_value_format_error';
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
                return 'mf_cloud_memory_mb_min_value_format_error';
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
                return 'mf_cloud_memory_mb_max_value_format_error';
            }
        }else{
            if($value < 1 || $value > 512){
                return 'memory_max_value_format_error';
            }
        }
        return true;
    }

}