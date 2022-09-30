<?php
namespace server\idcsmart_common\validate;

use think\Validate;

/**
 * 商品验证
 */
class IdcsmartCommonProductConfigoptionValidate extends Validate
{
	protected $rule = [
		'option_name' => 'require|max:255',
		'option_type' => 'require|in:select,multi_select,radio,quantity,quantity_range,yes_no,area|checkOptionType:thinkphp',
		'option_param' => 'max:255',
		'unit' => 'max:255',
		'allow_repeat' => 'in:0,1',
		'fee_type' => 'in:stage,qty',
		'max_repeat' => 'integer|egt:0',
		'qty_min' => 'integer|egt:0',
		'qty_max' => 'integer|egt:qty_min',
		'hidden' => 'in:0,1',
    ];

    protected $message  =   [
    ];

    protected $scene = [
        'create' => ['option_name','option_type','option_param','unit','allow_repeat','fee_type','max_repeat','qty_min','qty_max','hidden'],
        'update' => ['option_name','option_type','option_param','unit','allow_repeat','fee_type','max_repeat','qty_min','qty_max','hidden'],
    ];

    protected function checkOptionType($value,$rule,$data)
    {
        if (in_array($value,['quantity','quantity_range'])){
            if (!isset($data['fee_type'])){
                return lang_plugins('idcsmart_common_configoption_fee_type');
            }
            if (!isset($data['allow_repeat'])){
                return lang_plugins('idcsmart_common_configoption_allow_repeat');
            }
            if (!isset($data['max_repeat'])){
                return lang_plugins('idcsmart_common_configoption_max_repeat');
            }
        }

        return true;
    }

}