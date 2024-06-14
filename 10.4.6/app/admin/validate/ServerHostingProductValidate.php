<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 服务器托管商品验证
 */
class ServerHostingProductValidate extends Validate
{
    protected $rule = [
        'id'                    => 'require|integer|gt:0',
        'area_id'               => 'require|integer|gt:0',
        'title'                 => 'require|max:15',
        'region'                => 'require|max:30',
        'ip_num'                => 'require',
        'bandwidth'             => 'require',
        'defense'               => 'require',
        'bandwidth_price'       => 'require|float|egt:0',
        'bandwidth_price_unit'  => 'require|in:month,year',
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
        'title.require'                 => 'server_hosting_product_title_require',
        'title.max'                     => 'server_hosting_product_title_error',
        'region.require'                => 'server_hosting_product_region_require',
        'region.max'                    => 'server_hosting_product_region_error',
        'ip_num.require'                => 'server_hosting_product_ip_num_require',
        'bandwidth.require'             => 'server_hosting_product_bandwidth_require',
        'defense.require'               => 'server_hosting_product_defense_require',
        'bandwidth_price.require'       => 'server_hosting_product_bandwidth_price_require',
        'bandwidth_price.float'         => 'server_hosting_product_bandwidth_price_error',
        'bandwidth_price.egt'           => 'server_hosting_product_bandwidth_price_error',
        'bandwidth_price_unit.require'  => 'param_error',
        'bandwidth_price_unit.in'       => 'param_error',
        'selling_price.require'         => 'server_hosting_product_selling_price_require',
        'selling_price.float'           => 'server_hosting_product_selling_price_error',
        'selling_price.egt'             => 'server_hosting_product_selling_price_error',
        'selling_price_unit.require'    => 'param_error',
        'selling_price_unit.in'         => 'param_error',
        'product_id.require'            => 'id_error',
        'product_id.integer'            => 'id_error',
        'product_id.gt'                 => 'id_error',
    ];

    protected $scene = [
        'create' => ['area_id', 'title', 'region', 'ip_num', 'bandwidth', 'defense', 'bandwidth_price', 'bandwidth_price_unit', 'selling_price', 'selling_price_unit', 'product_id'],
        'update' => ['id', 'area_id', 'title', 'region', 'ip_num', 'bandwidth', 'defense', 'bandwidth_price', 'bandwidth_price_unit', 'selling_price', 'selling_price_unit', 'product_id'],
    ];
}