<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 物理服务器商品验证
 */
class PhysicalServerProductValidate extends Validate
{
    protected $rule = [
        'id'                    => 'require|integer|gt:0',
        'area_id'               => 'require|integer|gt:0',
        'title'                 => 'require|max:15',
        'description'           => 'require|max:30',
        'cpu'                   => 'require',
        'memory'                => 'require',
        'disk'                  => 'require',
        'ip_num'                => 'require',
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
        'title.require'                 => 'physical_server_product_title_require',
        'title.max'                     => 'physical_server_product_title_error',
        'description.require'           => 'physical_server_product_description_require',
        'description.max'               => 'physical_server_product_description_error',
        'cpu.require'                   => 'physical_server_product_cpu_require',
        'memory.require'                => 'physical_server_product_memory_require',
        'disk.require'                  => 'physical_server_product_disk_require',
        'ip_num.require'                => 'physical_server_product_ip_num_require',
        'bandwidth.require'             => 'physical_server_product_bandwidth_require',
        'duration.require'              => 'physical_server_product_duration_require',
        'tag.require'                   => 'physical_server_product_tag_require',
        'original_price.require'        => 'physical_server_product_original_price_require',
        'original_price.float'          => 'physical_server_product_original_price_error',
        'original_price.egt'            => 'physical_server_product_original_price_error',
        'original_price_unit.require'   => 'param_error',
        'original_price_unit.in'        => 'param_error',
        'selling_price.require'         => 'physical_server_product_selling_price_require',
        'selling_price.float'           => 'physical_server_product_selling_price_error',
        'selling_price.egt'             => 'physical_server_product_selling_price_error',
        'selling_price_unit.require'    => 'param_error',
        'selling_price_unit.in'         => 'param_error',
        'product_id.require'            => 'id_error',
        'product_id.integer'            => 'id_error',
        'product_id.gt'                 => 'id_error',
    ];

    protected $scene = [
        'create' => ['area_id', 'title', 'description', 'cpu', 'memory', 'disk', 'ip_num', 'bandwidth', 'duration', 'tag', 'original_price', 'original_price_unit', 'selling_price', 'selling_price_unit', 'product_id'],
        'update' => ['id', 'area_id', 'title', 'description', 'cpu', 'memory', 'disk', 'ip_num', 'bandwidth', 'duration', 'tag', 'original_price', 'original_price_unit', 'selling_price', 'selling_price_unit', 'product_id'],
    ];
}