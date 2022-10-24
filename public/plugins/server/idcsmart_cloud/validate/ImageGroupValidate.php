<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 镜像分组验证
 */
class ImageGroupValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'product_id'    => 'require|number',
        'name'          => 'require|length:1,100',
        'enable'        => 'require|number|in:0,1',
        'order'         => 'number|between:0,999',
        'description'   => 'length:0,1000',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'product_id.require'        => 'product_id_error',
    	'product_id.number'     	=> 'product_id_error',
        'name.require'              => 'please_input_image_group_name',
        'name.length'     			=> 'image_group_name_format_error',
        'order.require'             => 'order_require',
        'enable.require'            => 'enable_param_require',
        'enable.number'             => 'enable_param_error',
        'enable.in'                 => 'enable_param_error',
        'order.number'     			=> 'order_format_error',
        'order.between'             => 'order_format_error',
        'description.length'        => 'description_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','order','description','enable'],
        'edit' => ['id','name','order','description','enable'],
        'enable' => ['id','enable'],
    ];

    public function sceneOrder(){
        return $this->only(['id', 'order'])
                    ->append('order', 'require');
    }


}