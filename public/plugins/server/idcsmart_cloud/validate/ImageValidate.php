<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 镜像验证
 */
class ImageValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'name'          => 'require|length:1,100',
        'enable'        => 'require|number|in:0,1',
        'order'         => 'number|between:0,999',
        'charge'        => 'number|in:0,1',
        'price'         => 'float|egt:0',
        'icon'          => 'length:0,255',
        'product_id'    => 'require',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'name.require'              => 'please_input_image_name',
        'name.length'     	        => 'image_name_format_error',
        'enable.require'            => 'enable_param_require',
        'enable.number'             => 'enable_param_error',
        'enable.in'                 => 'enable_param_error',
        'order.number'     			=> 'order_format_error',
        'order.between'             => 'order_format_error',
        'charge.number'             => 'charge_param_error',
        'charge.in'                 => 'charge_param_error',
        'price.float'               => 'price_format_error',
        'price.egt'                 => 'price_cannot_lt_zero',
        'icon.length'               => 'icon_format_error',
    ];

    protected $scene = [
        'enable'  => ['id','enable'],
        'sync'    => ['product_id']
    ];

    public function sceneEdit(){
        return $this->only(['id','name','charge','price','icon'])
                    ->remove('name', 'require');
    }

}