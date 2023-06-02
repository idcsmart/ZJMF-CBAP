<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 操作系统分类验证
 * @use  server\mf_cloud\validate\ImageGroupValidate
 */
class ImageGroupValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'name'        		=> 'require|length:1,50',
        'icon'              => 'require|length:1,255',
        'image_group_order' => 'require|array',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'name.require'            		=> 'mf_cloud_please_input_image_group_name',
        'name.length'            		=> 'mf_cloud_image_group_name_format_error',
        'icon.require'            		=> 'mf_cloud_please_select_image_group_icon',
        'icon.length'                   => 'mf_cloud_please_select_image_group_icon',
        'image_group_order.require'     => 'mf_cloud_image_group_require',
        'image_group_order.array'       => 'mf_cloud_image_group_require',
    ];

    protected $scene = [
        'create' => ['product_id','name','icon'],
        'update' => ['id','name','icon'],
        'order'  => ['image_group_order'],
    ];


}