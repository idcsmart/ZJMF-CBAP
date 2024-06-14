<?php
namespace server\idcsmart_common\validate;

use server\idcsmart_common\logic\IdcsmartCommonLogic;
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
	    'configoption_id' => 'integer|checkOptionName:thinkphp|checkYesNo:thinkphp|checkQtyMin:thinkphp',
		'option_name' => 'max:255',
		'option_param' => 'max:255',
		'qty_min' => 'integer|egt:0',
		'qty_max' => 'integer|egt:qty_min',
		'hidden' => 'in:0,1',
		'country' => 'max:255',
		'custom_cycle' => 'array',
		'qty_change' => 'integer',
    ];

    protected $message  =   [
        'qty_max.egt' => 'idcsmart_common_configoption_sub_qty_max_egt'
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

            if (!in_array($configoption['option_type'],['quantity','quantity_range']) && (!isset($data['option_name']) || strlen($data['option_name']===0))){
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
            # 不可更改配置子项名称
            $oldOptionName = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$value)->where('id',$data['id'])->value('option_name');
            if ($data['option_name']!=$oldOptionName){
                return lang_plugins('idcsmart_common_configoption_yes_no_cannnot_update_option_name');
            }
        }

        return true;
    }

    # 数量类型 范围值限制
    protected function checkQtyMin($value,$rule,$data)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $configoption = $IdcsmartCommonProductConfigoptionModel->find($value);

        $max = $configoption['qty_max'];

        $min = $configoption['qty_min'];

        # 编辑子项时
        $data['qty_min'] = $data['qty_min']??0;
        $data['qty_max'] = $data['qty_max']??0;
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        if (isset($data['id'])){
            $max = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$value)
                ->where('id','<>',$data['id'])
                ->max('qty_max');
            $min = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$value)
                ->where('id','<>',$data['id'])
                ->min('qty_min');
            if ($data['qty_min']>$min && $data['qty_max']<$max){
                return true;
            }
        }

        if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
            if (($max>0 && $data['qty_min'] <= $max) && ($min>0 && $data['qty_max']>=$min)){
                return lang_plugins('idcsmart_common_configoption_sub_qty_min_gt_value',['{max}'=>$max,'{min}'=>$min]);
            }
        }

        return true;
    }

}