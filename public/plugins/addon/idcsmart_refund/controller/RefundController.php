<?php
namespace addon\idcsmart_refund\controller;

use addon\idcsmart_refund\model\IdcsmartRefundModel;
use addon\idcsmart_refund\validate\IdcsmartRefundValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 退款停用管理(后台)
 * @desc 退款停用管理(后台)
 * @use addon\idcsmart_refund\controller\RefundController
 */
class RefundController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartRefundValidate();
    }

    /**
     * 时间 2022-07-07
     * @title 停用列表
     * @desc 停用列表
     * @author wyh
     * @version v1
     * @url /admin/v1/refund
     * @method  GET
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name
     * @param string param.sort - 升/降序 asc,desc
     * @param string param.keywords - 关键字搜索:停用原因,申请人
     * @return array list - 停用列表
     * @return int list[].id - ID
     * @return int list[].client_name - 申请人
     * @return int list[].product_name - 申请商品
     * @return float host.amount - 退款金额(amount==-1表示不需要退款)
     * @return int list[].type - 类型:一共四种类型，可退款的有：人工退款Artificial、自动退款Auto，不可退款的有：Expire到期停用、Immediate立即停用
     * @return int list[].admin_name - 审核人
     * @return int list[].create_time - 申请时间
     * @return int list[].due_time - 到期时间
     * @return int list[].status - 状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消
     * @return int count - 停用总数
     */
    public function refundList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->refundList($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 通过
     * @desc 通过
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/:id/pending
     * @method put
     * @param int id - 停用申请ID required
     */
    public function pending()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->pending($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 驳回
     * @desc 驳回
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/:id/reject
     * @method put
     * @param int id - 停用申请ID required
     * @param string reject_reason - 驳回原因 required
     */
    public function reject()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('reject')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->reject($param);

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 取消
     * @desc 取消
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/:id/cancel
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

    /**
     * 时间 2022-08-23
     * @title 获取客户退款金额
     * @desc 获取客户退款金额
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/client/:id/amount
     * @method put
     * @param int id - 客户ID required
     */
    public function clientRefundAmount()
    {
        $param = $this->request->param();

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $result = $IdcsmartRefundModel->clientRefundAmount($param);

        return json($result);
    }
}