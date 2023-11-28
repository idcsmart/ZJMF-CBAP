<?php
namespace server\idcsmart_common\validate;

use app\common\model\ProductModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use think\Validate;

/**
 * 商品验证
 */
class IdcsmartCommonProductConfigoptionValidate extends Validate
{
	protected $rule = [
		'option_name' => 'require|max:255',
		'option_type' => 'require|in:select,multi_select,radio,quantity,quantity_range,yes_no,area,os|checkOptionType:thinkphp',
		'option_param' => 'max:255',
		'unit' => 'max:255',
		'allow_repeat' => 'in:0,1',
		'fee_type' => 'in:stage,qty',
		'max_repeat' => 'integer|egt:0',
		'qty_min' => 'integer|egt:0',
		'qty_max' => 'integer|egt:qty_min',
		'hidden' => 'in:0,1',
		'configoption_id' => 'checkConfigoptionId:thinkphp',
        'set_son_product' => 'in:0,1|checkSetSonProduct:thinkphp',
        'pay_type' => 'in:free,onetime,recurring_prepayment,recurring_postpaid',
        'free' => 'in:0,1'
    ];

    protected $message  =   [
    ];

    protected $scene = [
        'create' => ['option_name','option_type','option_param','unit','allow_repeat','fee_type','max_repeat','qty_min','qty_max','hidden','configoption_id','set_son_product','pay_type','free'],
        'update' => ['option_name','option_type','option_param','unit','allow_repeat','fee_type','max_repeat','qty_min','qty_max','hidden','configoption_id','set_son_product','pay_type','free'],
    ];

    protected function checkSetSonProduct($value,$rule,$data)
    {
        $productId = $data['product_id']??0;

        $ProductModel = new ProductModel();

        $product = $ProductModel->find($productId);
        if (!empty($product)){
            $parentProductId = $product['product_id'];
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            # 当前商品为某个父级商品的子商品时
            $exist = $IdcsmartCommonProductConfigoptionModel->where('product_id',$parentProductId)
                ->where('son_product_id',$productId)
                ->find();
            if (!empty($exist)){
                return 'idcsmart_common_product_is_son';
            }
        }

        if ($value && empty($data['pay_type'])){
            return 'idcsmart_common_son_product_pay_type_require';
        }

        return true;
    }

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

    protected function checkConfigoptionId($value,$rule,$data)
    {
        if (!empty($value)){
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

            $configoption = $IdcsmartCommonProductConfigoptionModel->where('id',$value)
                ->find();

            if (!in_array($configoption['option_type'],['quantity_range','quantity'])){
                return 'idcsmart_common_configoption_id_quantity';
            }

            if (empty($configoption)){
                return 'idcsmart_common_configoption_id_error';
            }

            if ($configoption['configoption_id']>0){  # 且关联配置项不关联其他
                return 'idcsmart_common_configoption_id_other';
            }

            if (isset($data['id']) && $data['id']==$value){
                return 'idcsmart_common_configoption_id_self';
            }
        }

        return true;
    }

}