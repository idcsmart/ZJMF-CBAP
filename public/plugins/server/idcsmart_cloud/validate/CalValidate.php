<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 计算型号验证
 */
class CalValidate extends Validate
{
	protected $rule = [
		'id' 		                         => 'require|integer',
        'name'                               => 'require|length:1,100',
        'module_idcsmart_cloud_cal_group_id' => 'require|number',
        'cpu'                                => 'require|number|between:1,240',
        'memory'                             => 'require|number|between:128,524288',
        'disk_size'                          => 'require|number|between:1,1048576',
        'price'                              => 'require|float|between:0,99999999',
        'order'                              => 'number|between:0,999',
    ];

    protected $message  =   [
    	'id.require'     			                   => 'id_error',
    	'id.integer'     			                   => 'id_error',
        'name.require'     			                   => 'please_input_cal_name',
        'name.length'     			                   => 'cal_name_length_format_error',
        'module_idcsmart_cloud_cal_group_id.require'   => 'please_select_group',
        'module_idcsmart_cloud_cal_group_id.number'    => 'please_select_group',
        'cpu.require'                                  => 'please_input_cpu',
        'cpu.number'                                   => 'cpu_format_error',
        'cpu.between'                                  => 'cpu_format_error',
        'memory.require'                               => 'please_input_memory',
        'memory.number'                                => 'memory_format_error',
        'memory.between'                               => 'memory_format_error',
        'disk_size.require'                            => 'disk_format_error',
        'disk_size.number'                             => 'disk_format_error',
        'disk_size.between'                            => 'disk_format_error',
        'price.require'                                => 'please_input_price',
        'price.float'                                  => 'price_must_be_number',
        'price.between'                                => 'price_cannot_lt_zero',
        'order.require'                                => 'order_require',
        'order.number'                                 => 'order_format_error',
        'order.between'                                => 'order_format_error',
    ];

    protected $scene = [
        'create' => ['name','module_idcsmart_cloud_cal_group_id','cpu','memory','disk_size','price','order'],
        'edit' => ['id', 'name','module_idcsmart_cloud_cal_group_id','cpu','memory','disk_size','price','order'],
    ];

    public function sceneOrder(){
        return $this->only(['id','order'])
                    ->append('order', 'require');
    }

}