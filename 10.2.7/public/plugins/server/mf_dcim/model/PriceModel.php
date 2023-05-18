<?php 
namespace server\mf_dcim\model;

use think\Model;

/**
 * @title 配置价格模型
 * @use server\mf_dcim\model\PriceModel
 */
class PriceModel extends Model{

	protected $name = 'module_mf_dcim_price';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rel_type'     	=> 'string',
        'rel_id'     	=> 'int',
        'duration_id'   => 'int',
        'price'         => 'float',
    ];

    
    
    




}