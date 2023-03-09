<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 磁盘配置验证
 * @use  server\mf_cloud\validate\DiskValidate
 */
class DiskValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'type'              => 'require|in:radio,step,total',
        'value'             => 'require|integer|between:1,1048576',
        'min_value'         => 'require|integer|between:1,1048576',
        'max_value'         => 'require|integer|between:1,1048576|gt:min_value',
        'step'              => 'require|integer|between:1,1048576|checkStep:thinkphp',
        'price'             => 'checkPrice:thinkphp',
        'other_config'      => 'checkOtherConfig:thinkphp',
        'disk_type'         => 'length:0,50',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'type.require'                  => 'please_select_config_type',
        'type.in'                       => 'config_type_error',
        'value.require'                 => 'please_input_disk_size',
        'value.integer'                 => 'disk_size_format_error',
        'value.between'                 => 'disk_size_format_error',
        'min_value.require'             => 'please_input_disk_min_value',
        'min_value.integer'             => 'disk_min_value_format_error',
        'min_value.between'             => 'disk_min_value_format_error',
        'max_value.require'             => 'please_input_disk_max_value',
        'max_value.integer'             => 'disk_max_value_format_error',
        'max_value.between'             => 'disk_max_value_format_error',
        'step.require'                  => 'please_input_disk_step',
        'step.integer'                  => 'disk_step_format_error',
        'step.between'                  => 'disk_step_format_error',
        'price.checkPrice'              => 'price_cannot_lt_zero',
        'disk_type.length'              => 'disk_type_format_error',
    ];

    protected $scene = [
        'create'        => ['product_id','type','price','other_config'],
        'update'        => ['id','price','other_config'],
        'radio'         => ['value'],
        'step'          => ['min_value','max_value','step'],
        'other_config'  => ['disk_type'],
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
        if((!isset($arr['type']) || $arr['type'] != 'radio') && $arr['step'] > $arr['max_value'] - $arr['min_value']){
            return 'step_must_gt_diff_of_max_and_min';
        }
        return true;
    }

    public function checkOtherConfig($value){
        $DiskValidate = new DiskValidate();
        if(!$DiskValidate->scene('other_config')->check($value)){
            return $DiskValidate->getError();
        }
        return true;
    }

}