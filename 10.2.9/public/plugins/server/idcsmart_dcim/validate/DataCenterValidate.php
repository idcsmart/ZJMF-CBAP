<?php
namespace server\idcsmart_dcim\validate;

use think\Validate;

/**
 * 数据中心验证
 */
class DataCenterValidate extends Validate{

	protected $rule = [
        'id'            => 'require|integer',
		'country_id' 	=> 'require|integer',
        'product_id'    => 'require|integer',
        'city'          => 'require|length:1,12',
        'order'         => 'number|between:0,999',
    ];

    protected $message = [
    	'id.require'     		=> 'id_error',
    	'id.integer'     		=> 'id_error',
        'country_id.require'    => 'country_select_error',
        'country_id.integer'    => 'country_select_error',
        'product_id.require'    => 'product_id_error',
        'product_id.integer'    => 'product_id_error',
        'city.require'          => 'please_input_city',
        'city.length'           => 'city_format_error',
        'order.require'         => 'order_require',
        'order.number'          => 'order_format_error',
        'order.between'         => 'order_format_error',
    ];

    protected $scene = [
        'create' => ['country_id','product_id','city'],
        'edit'   => ['id','country_id','city'],
    ];

    public function sceneOrder(){
        return $this->only(['id','order'])
                    ->append('order', 'require');
    }

}