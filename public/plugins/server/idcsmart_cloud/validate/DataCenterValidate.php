<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 数据中心验证
 */
class DataCenterValidate extends Validate{

	protected $rule = [
		'id' 		    => 'require|integer',
        'country'       => 'require|length:1,100',
        'country_code'  => 'require|length:1,100',
        'city'          => 'require|length:1,255',
        'area'          => 'require|number|between:1,9',
        'server'        => 'require|array',
        'order'			=> 'number|between:0,999',
        'product_id'    => 'require|integer',
    ];

    protected $message = [
    	'id.require'     		=> 'id_error',
    	'id.integer'     		=> 'id_error',
    	'country.require'  		=> 'please_input_country',
    	'country.length'  		=> 'country_length_format_error',
        'country_code.require'  => 'please_input_country_code',
    	'country_code.length'   => 'country_code_length_format_error',
    	'city.require'  		=> 'please_input_city',
    	'city.length'  			=> 'city_format_error',
    	'area.require'  		=> 'please_input_area',
        'area.number'           => 'area_format_error',
    	'area.between'  	    => 'area_format_error',
    	'server.require'  		=> 'please_select_server',
    	'server.array'  		=> 'please_select_server',
        'order.require'         => 'order_require',
    	'order.number'          => 'order_format_error',
        'order.between'         => 'order_format_error',
        'product_id.require'    => 'product_id_error',
        'product_id.integer'    => 'product_id_error',
    ];

    protected $scene = [
        'create' => ['country','country_code','city','area','server','product_id','order'],
        'edit'   => ['id','country','country_code','city','area','server','order'],
    ];

    public function sceneOrder(){
        return $this->only(['id','order'])
                    ->append('order', 'require');
    }



}