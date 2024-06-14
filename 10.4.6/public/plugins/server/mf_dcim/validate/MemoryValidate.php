<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 内存配置验证
 * @use  server\mf_dcim\validate\MemoryValidate
 */
class MemoryValidate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'value'             => 'require|length:1,255',
        'order'             => 'require|integer|between:0,999',
        'price'             => 'checkPrice:thinkphp',
        'other_config'      => 'require|checkOtherConfig:thinkphp',
        'memory_slot'       => 'require|integer|between:1,1000',
        'memory'            => 'require|integer|between:1,100000',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'value.require'                 => 'mf_dcim_memory_value_require',
        'value.length'                  => 'mf_dcim_memory_value_length_error',
        'order.require'                 => 'mf_dcim_order_require',
        'order.integer'                 => 'mf_dcim_order_format_error',
        'order.between'                 => 'mf_dcim_order_format_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
        'other_config.require'          => 'param_error',
        'memory_slot.require'           => 'mf_dcim_memory_slot_require',
        'memory_slot.integer'           => 'mf_dcim_memory_slot_format_error',
        'memory_slot.between'           => 'mf_dcim_memory_slot_format_error',
        'memory.require'                => 'mf_dcim_memory_capacity_require',
        'memory.integer'                => 'mf_dcim_memory_capacity_format_error',
        'memory.between'                => 'mf_dcim_memory_capacity_format_error',
    ];

    protected $scene = [
        'create'        => ['product_id','value','order','price','other_config'],
        'update'        => ['id','value','order','price','other_config'],
        'other_config'  => ['memory_slot','memory'],
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

    public function checkOtherConfig($value){
        $MemoryValidate = new MemoryValidate();
        if(!$MemoryValidate->scene('other_config')->check($value)){
            return $MemoryValidate->getError();
        }
        return true;
    }

}