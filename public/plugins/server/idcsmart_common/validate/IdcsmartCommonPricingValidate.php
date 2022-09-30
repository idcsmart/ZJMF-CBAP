<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 价格验证
 */
class IdcsmartCommonPricingValidate extends Validate
{
	protected $rule = [
		'onetime' => 'float',
		'monthly' => 'require|float',
		'quarterly' => 'require|float',
		'semaiannually' => 'require|float',
		'annually' => 'require|float',
		'biennially' => 'require|float',
		'triennianlly' => 'require|float',
    ];

    protected $message  =   [
    ];

}