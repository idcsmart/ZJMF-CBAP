<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 商品自定义字段验证
 */
class IdcsmartCommonProductCustomFieldValidate extends Validate
{
    protected $rule = [
        'allow_qty' => 'require|in:0,1',
        'auto_support' => 'require|in:0,1',
        'pricing' => 'require|checkPricing:thinkphp',
        'configoption' => 'array|checkConfigoption:thinkphp',
        'cycle' => 'require|checkCycle:thinkphp',
        'product_id' => 'integer',
        'qty' => 'integer|checkQty:thinkphp'
    ];

    protected $message  =   [
    ];

    protected $scene = [
        'create' => ['allow_qty','auto_support','pricing'],
    ];
}