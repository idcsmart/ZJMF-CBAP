<?php
namespace app\common\model;

use think\Model;

/**
 * @title 商品升级商品模型
 * @desc 商品升级商品模型
 * @use app\common\model\ProductUpgradeProductModel
 */
class ProductUpgradeProductModel extends Model
{
    protected $name = 'product_upgrade_product';

    // 设置字段信息
    protected $schema = [
        'product_id'        => 'int',
        'upgrade_product_id'=> 'int',
    ];

}
