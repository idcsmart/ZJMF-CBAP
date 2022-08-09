<?php
namespace addon\idcsmart_refund\controller\clientarea;

use addon\idcsmart_refund\model\IdcsmartRefundModel;
use addon\idcsmart_refund\model\IdcsmartRefundReasonModel;
use addon\idcsmart_refund\validate\IdcsmartRefundValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 退款(会员中心)
 * @desc 退款(会员中心)
 * @use addon\idcsmart_refund\controller\clientarea\RefundController
 */
class RefundController extends PluginBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartRefundValidate();
    }

    /**
     * 时间 2022-07-08
     * @title 停用页面
     * @desc 停用页面
     * @author wyh
     * @version v1
     * @url /console/v1/refund
     * @method  GET
     * @param int host_id - 产品ID required
     * @return int allow_refund - 是否允许退款:0否,1是
     * @return int reason_custom - 是否允许自定义原因:0否,1是
     * @return array reasons - 停用原因
     * @return int reasons[].id - 原因id
     * @return string reasons[].content - 内容
     * @return object host - 产品
     * @return int host.create_time - 订购时间
     * @return float host.first_payment_amount - 订购金额
     * @return float host.amount - 退款金额(amount==-1表示不需要退款)
     * @return array config_option - 产品配置
     */
    public function refundPage()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->refundPage($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 停用
     * @desc 停用
     * @author wyh
     * @version v1
     * @url /console/v1/refund
     * @method  POST
     * @param int host_id - 产品ID required
     * @param mixed suspend_reason - 停用原因,产品可以自定义原因时,输入框,传字符串;产品不可自定义原因时,传停用原因ID数组
     * @param string type - 停用时间:Expire到期,Immediate立即
     */
    public function refund()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->refund($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 取消
     * @desc 取消
     * @author wyh
     * @version v1
     * @url /console/v1/refund/:id/cancel
     * @method put
     * @param int id - 停用申请ID required
     */
    public function cancel()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->cancel($param);

        return json($result);
    }
    
}