<?php
namespace addon\promo_code\model;

use think\Model;

/**
 * @title 优惠码关联商品模型
 * @desc 优惠码关联商品模型
 * @use addon\promo_code\model\PromoCodeProductModel
 */
class PromoCodeProductModel extends Model
{
    protected $name = 'addon_promo_code_product';

    // 设置字段信息
    protected $schema = [
        'id'               		=> 'int',
        'addon_promo_code_id'	=> 'int',
        'product_id'          	=> 'int',
    ];
}
