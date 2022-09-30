<?php
namespace server\idcsmart_common\validate;

use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;
use think\Validate;

/**
 * 子项验证
 */
class IdcsmartCommonProductConfigoptionSubValidate extends Validate
{
	protected $rule = [
	    'id' => 'integer',
	    'configoption_id' => 'integer|checkOptionName:thinkphp|checkYesNo:thinkphp',
		'option_name' => 'max:255',
		'option_param' => 'max:255',
		'qty_min' => 'integer|egt:0',
		'qty_max' => 'integer|egt:qty_min',
		'hidden' => 'in:0,1',
		'country' => 'max:255',
		'custom_cycle' => 'array',
    ];

    protected $message  =   [
    ];

    protected $scene = [
        'create' => ['configoption_id','option_name','option_param','qty_min','qty_max','hidden','country','custom_cycle'],
        'update' => ['id','configoption_id','option_name','option_param','qty_min','qty_max','hidden','country','custom_cycle'],
    ];

    protected function checkOptionName($value,$rule,$data)
    {
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $configoption = $IdcsmartCommonProductConfigoptionModel->find($value);

        if ($configoption['option_type']=='area' && !isset($data['country'])){
            return lang_plugins('idcsmart_common_configoption_country');
        } elseif (in_array($configoption['option_type'],['quantity','quantity_range']) && (!isset($data['qty_min']) || !isset($data['qty_max'])))
        {
            return lang_plugins('idcsmart_common_configoption_qty_min_max');
        }else{

            if (!in_array($configoption['option_type'],['quantity','quantity_range']) && (!isset($data['option_name']) || empty($data['option_name']))){
                return lang_plugins('idcsmart_common_configoption_option_name');
            }
        }

        return true;
    }

    # 是否类型不可超过
    protected function checkYesNo($value,$rule,$data)
    {
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $configoption = $IdcsmartCommonProductConfigoptionModel->find($value);
        if ($configoption['option_type']=='yes_no'){
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            if (isset($data['id'])){
                $count = $IdcsmartCommonProductConfigoptionSubModel->where('id','<>',$data['id'])->where('product_configoption_id',$value)->count();
            }else{
                $count = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$value)->count();
            }

            if ($count>=2){
                return lang_plugins('idcsmart_common_configoption_yes_no_cannnot_greater_two');
            }
        }

        return true;
    }

}