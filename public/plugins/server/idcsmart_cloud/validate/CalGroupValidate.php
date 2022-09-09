<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 计算型号分组验证
 */
class CalGroupValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'product_id'    => 'require|number',
        'name'          => 'require|length:1,100',
        'order'         => 'number|between:0,999',
        'description'   => 'length:0,1000',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'product_id.require'        => 'product_id_error',
    	'product_id.number'     	=> 'product_id_error',
        'name.require'              => 'please_input_cal_group_name',
        'name.length'     			=> 'cal_group_name_length_format_error',
        'order.require'             => 'order_require',
        'order.number'     			=> 'order_format_error',
        'order.between'             => 'order_format_error',
        'description.length'        => 'description_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','order','description'],
        'edit' => ['id','name','order','description'],
    ];


    public function sceneOrder(){
        return $this->only(['id', 'order'])
                    ->append('order', 'require');
    }


}