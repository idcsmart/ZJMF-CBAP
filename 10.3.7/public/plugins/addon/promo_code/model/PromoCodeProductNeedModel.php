<?php
namespace addon\promo_code\model;

use think\Model;

/**
 * @title 优惠码关联需求商品模型
 * @desc 优惠码关联需求商品模型
 * @use addon\promo_code\model\PromoCodeProductNeedModel
 */
class PromoCodeProductNeedModel extends Model
{
    protected $name = 'addon_promo_code_product_need';

    // 设置字段信息
    protected $schema = [
        'id'               		=> 'int',
        'addon_promo_code_id'	=> 'int',
        'product_id'          	=> 'int',
    ];
}
