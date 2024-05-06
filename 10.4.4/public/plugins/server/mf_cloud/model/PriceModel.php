<?php 
namespace server\mf_cloud\model;

use think\Model;

/**
 * @title 配置价格模型
 * @use server\mf_cloud\model\PriceModel
 */
class PriceModel extends Model
{
	protected $name = 'module_mf_cloud_price';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rel_type'     	=> 'int',
        'rel_id'     	=> 'int',
        'duration_id'   => 'int',
        'price'         => 'float',
    ];

    // rel_type常量
    const REL_TYPE_OPTION = 0;           // 关联option
    const REL_TYPE_RECOMMEND_CONFIG = 1; // 关联套餐
    


}