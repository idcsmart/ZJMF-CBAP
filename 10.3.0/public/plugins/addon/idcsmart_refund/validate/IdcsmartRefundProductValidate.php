<?php
namespace addon\idcsmart_refund\validate;

use app\common\model\ProductModel;
use think\Validate;

/**
 * 退款商品验证
 */
class IdcsmartRefundProductValidate extends Validate
{
	protected $rule = [
		'id' 		            => 'require|integer',
		'product_id' 		    => 'require|integer|checkProduct:thinkphp',
		'type' 		            => 'require|in:Artificial,Auto',
		'require' 		        => 'in:First,Same',
        'range_control' 		=> 'in:0,1',
		'range' 		        => 'integer|egt:0',
		'rule' 		            => 'require|in:Day,Month,Ratio|checkRefundRule:thinkphp|checkRatioValue:thinkphp',
		'ratio_value' 		    => 'float|egt:0|elt:100',
    ];

    protected $message  =   [
    	'id.require'     			        => 'id_error',
    	'id.integer'     			        => 'id_error',
    	'product_id.require'     			=> 'refund_product_id_require',
    	'product_id.integer'     			=> 'refund_product_id_integer',
    	'type.require'     			        => 'refund_type_require',
    	'type.in'     			            => 'refund_type_in',
    	'require.in'     			        => 'refund_require_in',
    	#'range.require'     			    => 'refund_range_require',
    	#'range_control.require'     		=> 'refund_range_control_require',
    	'range_control.in'     		        => 'refund_range_control_in',
    	'range.integer'     			    => 'refund_range_integer',
    	'range.egt'     			        => 'refund_range_egt',
    	'rule.require'     			        => 'refund_rule_require',
    	'rule.in'     			            => 'refund_rule_in',
    	'ratio_value.float'     			=> 'refund_ratio_value_float',
    	'ratio_value.egt'     			    => 'refund_ratio_value_egt',
    	'ratio_value.elt'     			    => 'refund_ratio_value_elt',
    ];

    protected $scene = [
        'create' => ['product_id','type','require','range','rule','ratio_value','range_control'],
        'update' => ['id','product_id','type','require','range','rule','ratio_value','range_control'],
    ];

    protected function checkProduct($value,$rule,$data)
    {
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($value);
        if (empty($product)){
            return 'refund_product_is_not_exist';
        }

        # 免费商品不可添加为可退款商品
        if ($product->pay_type == 'free'){
            return 'refund_product_pay_type_free';
        }

        return true;
    }

    protected function checkRefundRule($value,$rule,$data)
    {
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($data['product_id']);
        if ($product['pay_type'] == 'recurring_prepayment' || $product['pay_type'] == 'recurring_postpaid'){
            if (!in_array($value,['Day','Month'])){
                return 'refund_rule_only_day_or_month';
            }
        }else{
            if (!in_array($value,['Ratio'])){
                return 'refund_rule_only_ratio';
            }
        }

        return true;
    }

    protected function checkRatioValue($value,$rule,$data)
    {
        if ($value == 'Ratio' && !isset($data['ratio_value'])){
            return 'refund_ratio_require';
        }

        return true;
    }
}