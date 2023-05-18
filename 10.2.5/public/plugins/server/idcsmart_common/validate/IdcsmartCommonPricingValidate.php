<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 价格验证
 */
class IdcsmartCommonPricingValidate extends Validate
{
	protected $rule = [
		'onetime' => 'float|egt:0',
    ];

    protected $message  =   [
    ];

}