<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 套餐验证
 */
class PackageValidate extends Validate
{
	protected $rule = [
        'id'                            => 'require|integer',
		'product_id' 	                => 'require|integer',
        'name'                          => 'require|length:1,100',
        'cal_id'                        => 'require|array',
        'data_center_id'                => 'require|array',
        'bw_id'                         => 'require|array',
        'module_idcsmart_cloud_cal_id'  => 'require|integer',
        'module_idcsmart_cloud_bw_id'   => 'require|integer',
    ];

    protected $message  =   [
    	'id.require'     	                    => 'id_error',
        'id.integer'                            => 'id_error',
        'product_id.require'                    => 'product_id_error',
    	'product_id.integer'                    => 'product_id_error',
        'name.require'                          => 'please_input_package_name',
        'name.length'                           => 'package_name_length_foramt_error',
        'cal_id.require'                        => 'please_select_cal',
        'cal_id.array'                          => 'please_select_cal',
        'data_center_id.require'                => 'please_select_data_center',
        'data_center_id.array'                  => 'please_select_data_center',
        'bw_id.require'                         => 'please_select_bw',
        'bw_id.array'                           => 'please_select_bw',
        'module_idcsmart_cloud_cal_id.require'  => 'please_select_cal',
        'module_idcsmart_cloud_cal_id.integer'  => 'please_select_cal',
        'module_idcsmart_cloud_bw_id.require'   => 'please_select_bw',
        'module_idcsmart_cloud_bw_id.integer'   => 'please_select_bw',
    ];

    protected $scene = [
        'create' => ['name','cal_id','data_center_id','bw_id'],
        'edit' => ['id','name','module_idcsmart_cloud_cal_id','data_center_id','module_idcsmart_cloud_bw_id'],
    ];

}