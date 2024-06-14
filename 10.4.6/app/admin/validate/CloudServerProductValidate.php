<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 云服务器商品验证
 */
class CloudServerProductValidate extends Validate
{
    protected $rule = [
        'id'                    => 'require|integer|gt:0',
        'area_id'               => 'require|integer|gt:0',
        'title'                 => 'require|max:15',
        'description'           => 'require|max:30',
        'cpu'                   => 'require',
        'memory'                => 'require',
        'system_disk'           => 'require',
        'bandwidth'             => 'require',
        'duration'              => 'require',
        'tag'                   => 'require',
        'original_price'        => 'require|float|egt:0',
        'original_price_unit'   => 'require|in:month,year',
        'selling_price'         => 'require|float|egt:0',
        'selling_price_unit'    => 'require|in:month,year',
        'product_id'            => 'require|integer|gt:0',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'id.gt'                         => 'id_error',
        'area_id.require'               => 'id_error',
        'area_id.integer'               => 'id_error',
        'area_id.gt'                    => 'id_error',
        'title.require'                 => 'cloud_server_product_title_require',
        'title.max'                     => 'cloud_server_product_title_error',
        'description.require'           => 'cloud_server_product_description_require',
        'description.max'               => 'cloud_server_product_description_error',
        'cpu.require'                   => 'cloud_server_product_cpu_require',
        'memory.require'                => 'cloud_server_product_memory_require',
        'system_disk.require'           => 'cloud_server_product_system_disk_require',
        'bandwidth.require'             => 'cloud_server_product_bandwidth_require',
        'duration.require'              => 'cloud_server_product_duration_require',
        'tag.require'                   => 'cloud_server_product_tag_require',
        'original_price.require'        => 'cloud_server_product_original_price_require',
        'original_price.float'          => 'cloud_server_product_original_price_error',
        'original_price.egt'            => 'cloud_server_product_original_price_error',
        'original_price_unit.require'   => 'param_error',
        'original_price_unit.in'        => 'param_error',
        'selling_price.require'         => 'cloud_server_product_selling_price_require',
        'selling_price.float'           => 'cloud_server_product_selling_price_error',
        'selling_price.egt'             => 'cloud_server_product_selling_price_error',
        'selling_price_unit.require'    => 'param_error',
        'selling_price_unit.in'         => 'param_error',
        'product_id.require'            => 'id_error',
        'product_id.integer'            => 'id_error',
        'product_id.gt'                 => 'id_error',
    ];

    protected $scene = [
        'create' => ['area_id', 'title', 'description', 'cpu', 'memory', 'system_disk', 'bandwidth', 'duration', 'tag', 'original_price', 'original_price_unit', 'selling_price', 'selling_price_unit', 'product_id'],
        'update' => ['id', 'area_id', 'title', 'description', 'cpu', 'memory', 'system_disk', 'bandwidth', 'duration', 'tag', 'original_price', 'original_price_unit', 'selling_price', 'selling_price_unit', 'product_id'],
    ];
}