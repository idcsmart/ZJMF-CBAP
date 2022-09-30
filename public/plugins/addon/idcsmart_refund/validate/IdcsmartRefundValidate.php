<?php
namespace addon\idcsmart_refund\validate;

use addon\idcsmart_refund\IdcsmartRefund;
use addon\idcsmart_refund\model\IdcsmartRefundReasonModel;
use app\common\model\HostModel;
use think\Validate;

/**
 * 停用退款验证
 */
class IdcsmartRefundValidate extends Validate
{
	protected $rule = [
		'host_id' 		        => 'require|checkHost:thinkphp',
		'suspend_reason' 		=> 'checkSuspendReason:thinkphp',
		'type' 		            => 'require|in:Expire,Immediate',
		'reject_reason' 		=> 'require|max:2000',
    ];

    protected $message  =   [
    	'host_id.require'     			    => 'refund_host_id_require',
    	'type.require'     			        => 'refund_type_require',
    	'type.in'     			            => 'refund_refund_type_in',
    	'reject_reason.require'     		=> 'refund_reject_reason_require',
    	'reject_reason.max'     		    => 'refund_reject_reason_max',
    ];

    protected $scene = [
        'create' => ['host_id','suspend_reason','type'],
        'reject' => ['reject_reason'],
    ];

    protected function checkHost($value,$rule,$data)
    {
        $HostModel = new HostModel();

        $host = $HostModel->where('client_id',get_client_id())
            ->where('id',$value)
            ->find();

        if (empty($host)){
            return 'refund_host_is_not_exist';
        }

        if (!in_array($host->status,['Pending','Active'])){
            return 'refund_host_cannot_suspend';
        }

        return true;
    }

    protected function checkSuspendReason($value,$rule,$data)
    {
        $IdcsmartRefund = new IdcsmartRefund();
        $config = $IdcsmartRefund->getConfig();
        # 自定义原因
        if (isset($config['reason_custom']) && $config['reason_custom']==1){

            if (!is_string($value)){
                return 'param_error';
            }

            if (mb_strlen($value)>500){
                return 'refund_suspend_reason_max';
            }
        }else{
            if (!is_array($value)){
                return 'refund_suspend_reason_array';
            }

            if (empty($value)){
                return 'refund_suspend_reason_require';
            }

            $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();
            foreach ($value as $item){
                $reason = $IdcsmartRefundReasonModel->find($item);
                if (empty($reason)){
                    return 'refund_suspend_reason_is_not_exist';
                }
            }
        }

        return true;
    }

}