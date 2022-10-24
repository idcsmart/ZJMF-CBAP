<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 带宽类型验证
 */
class BwTypeValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'product_id'    => 'require|integer',
        'name'          => 'require|length:1,100',
        'order'         => 'number|between:0,999',
        'description'   => 'length:0,1000',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'product_id.require'        => 'product_id_error',
    	'product_id.integer'     	=> 'product_id_error',
        'name.require'              => 'bw_type_name_require',
        'name.length'     			=> 'bw_type_name_length_format_error',
        'order.require'             => 'bw_type_order_require',
        'order.number'     			=> 'order_format_error',
        'order.between'             => 'order_format_error',
        'description.length'        => 'description_format_error',
    ];

    protected $scene = [
        'create' => ['name','order','description','product_id'],
        'edit' => ['id','name','order','description'],
    ];

    public function sceneOrder(){
        return $this->only(['id', 'order'])
                    ->append('order', 'require');
    }

}