<?php
namespace server\idcsmart_common\validate;

use app\common\model\ProductModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\model\IdcsmartCommonCustomCycleModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonProductModel;
use server\idcsmart_common\model\IdcsmartCommonServerModel;
use think\Validate;

/**
 * 商品验证
 */
class IdcsmartCommonProductValidate extends Validate
{
	protected $rule = [
		'allow_qty' => 'require|in:0,1',
		'auto_support' => 'require|in:0,1',
        'pricing' => 'require|checkPricing:thinkphp',
        'configoption' => 'array|checkConfigoption:thinkphp',
        'cycle' => 'require|checkCycle:thinkphp',
        'product_id' => 'integer',
        'server_id' => 'integer|checkServer:thinkphp',
        'qty' => 'integer|checkQty:thinkphp',
    ];

    protected $message  =   [
    ];

    protected $scene = [
        'create' => ['allow_qty','auto_support','pricing','server_id'],
        'cart_calculate' => ['configoption','cycle','product_id','qty'],
    ];

    protected function checkServer($value,$rule,$data){
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
        $exist = $IdcsmartCommonServerModel->find($value);
        if (empty($exist)){
            return "子接口不存在";
        }

        return true;
    }

    protected function checkPricing($value,$rule,$data)
    {
        $productId = $data['product_id'];
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($productId);
        if ($product['pay_type']=='free'){
            return true;
        }

        $IdcsmartCommonPricingValidate = new IdcsmartCommonPricingValidate();

        if (!$IdcsmartCommonPricingValidate->check($value)){
            return $IdcsmartCommonPricingValidate->getError();
        }

        return true;
    }

    # 检查周期
    protected function checkCycle($value,$rule,$data)
    {
        $productId = $data['product_id'];
        if ($value == 'free'){
            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            if ($product['pay_type']!='free'){
                return lang_plugins('cycle_error');
            }
            return true;
        }

        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0)
            ->where('cc.id',intval($value))
            ->find();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        if (!in_array($value,array_keys($IdcsmartCommonLogic->systemCycles)) && empty($customCycle)){

            return lang_plugins('cycle_error');
        }

        return true;
    }

    # 检查配置项
    protected function checkConfigoption($value)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        foreach ($value as $key=>$item){
            $configoption = $IdcsmartCommonProductConfigoptionModel->where('id',$key)->find();
            if (empty($configoption)){
                return lang_plugins('idcsmart_common_configoption_not_exist');
            }

            $optionType = $configoption['option_type'];
            if ($IdcsmartCommonLogic->checkQuantity($optionType)){
                if (!is_array($item)){
                    return lang_plugins('param_error');
                }

                if (!$configoption['allow_repeat'] && count($item)>1){
                    return lang_plugins('param_error');
                }
                if ($configoption['allow_repeat'] && count($item)>$configoption['max_repeat']){
                    return lang_plugins('idcsmart_common_configoption_option_type_quantity_max_repeat_error');
                }

                foreach ($item as $v){
                    if ($v>$configoption['qty_max'] || $v<$configoption['qty_min']){
                        return lang_plugins('qty_error');
                    }
                }

            }elseif ($IdcsmartCommonLogic->checkMultiSelect($optionType)){
                foreach ($item as $v){
                    $tmp = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$v)
                        ->find();
                    if (empty($tmp)){
                        return lang_plugins('param_error');
                    }
                }
            }else{
                $tmp = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                    ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                    ->where('pc.hidden',0)
                    ->where('pcs.hidden',0)
                    ->where('pc.id',$key)
                    ->where('pcs.id',$item)
                    ->find();
                if (empty($tmp)){
                    return lang_plugins('param_error');
                }
            }
        }

        return true;
    }

    protected function checkQty($value,$rule,$data)
    {

        $productId = $data['product_id'];

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $allowQty = $IdcsmartCommonProductModel->where('product_id',$productId)->value('allow_qty');

        if (!$allowQty && $value>1){
            return lang_plugins('cannot_qty');
        }

        return true;
    }
}