<?php
namespace server\idcsmart_cloud_disk\validate;

use think\Validate;

/**
 * 套餐验证
 */
class PackageValidate extends Validate
{
	protected $rule = [
        'id'                                    => 'require|integer|gt:0',
		'product_id' 	                        => 'require|integer|gt:0',
        'name'                                  => 'require|max:100',
        'description'                           => 'require|max:1000',
        'module_idcsmart_cloud_data_center_id'  => 'require|integer|gt:0',
        'size_min'                              => 'require|integer|egt:0',
        'size_max'                              => 'require|integer|egt:size_min',
        'precision'                             => 'require|integer|gt:0',
        'price'                                 => 'require|float|egt:0',
        'order'                                 => 'require|integer|egt:0',
    ];

    protected $message  =   [
    	'id.require'     	                            => 'id_error',
        'id.integer'                                    => 'id_error',
        'id.gt'                                         => 'id_error',
        'product_id.require'                            => 'package_product_id_error',
    	'product_id.integer'                            => 'package_product_id_error',
        'product_id.gt'                                 => 'package_product_id_error',
        'name.require'                                  => 'please_enter_package_name',
        'name.max'                                      => 'package_name_cannot_exceed_100_chars',
        'description.require'                           => 'please_enter_package_description',
        'description.max'                               => 'package_description_cannot_exceed_1000_chars',
        'module_idcsmart_cloud_data_center_id.require'  => 'package_data_center_id_error',
        'module_idcsmart_cloud_data_center_id.integer'  => 'package_data_center_id_error',
        'module_idcsmart_cloud_data_center_id.gt'       => 'package_data_center_id_error',
        'size_min.require'                              => 'package_size_min_format_error',
        'size_min.integer'                              => 'package_size_min_format_error',
        'size_min.egt'                                  => 'package_size_min_format_error',
        'size_max.require'                              => 'package_size_max_format_error',
        'size_max.integer'                              => 'package_size_max_format_error',
        'size_max.egt'                                  => 'package_size_max_format_error',
        'precision.require'                             => 'package_precision_format_error',
        'precision.integer'                             => 'package_precision_format_error',
        'precision.gt'                                  => 'package_precision_format_error',
        'price.require'                                 => 'package_price_format_error',
        'price.integer'                                 => 'package_price_format_error',
        'price.egt'                                     => 'package_price_format_error',
        'order.require'                                 => 'package_order_format_error',
        'order.integer'                                 => 'package_order_format_error',
        'order.egt'                                     => 'package_order_format_error',
    ];

    protected $scene = [
        'create' => ['product_id', 'name', 'description', 'module_idcsmart_cloud_data_center_id', 'size_min', 'size_max', 'precision', 'price', 'order'],
        'update' => ['id', 'product_id', 'name', 'description', 'module_idcsmart_cloud_data_center_id', 'size_min', 'size_max', 'precision', 'price', 'order'],
    ];

}