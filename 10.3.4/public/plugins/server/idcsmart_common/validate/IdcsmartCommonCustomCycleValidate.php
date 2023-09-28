<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 自定义周期验证
 */
class IdcsmartCommonCustomCycleValidate extends Validate
{
	protected $rule = [
		'name' => 'require|max:255',
		'cycle_time' => 'require|integer',
        'cycle_unit' => 'require|in:hour,day,month,infinite',
        'amount' => 'require|float|egt:0',
    ];

    protected $message  =   [
    ];

}