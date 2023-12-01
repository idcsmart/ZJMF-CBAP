<?php
namespace addon\idcsmart_withdraw\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawRuleModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawMethodModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawRejectReasonModel;
use addon\idcsmart_withdraw\validate\IdcsmartWithdrawValidate;

/**
 * @title 提现插件
 * @desc 提现插件
 * @use addon\idcsmart_withdraw\controller\AdminIndexController
 */
class AdminIndexController extends PluginAdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartWithdrawValidate();
    }

    /**
     * 时间 2022-07-22
     * @title 提现列表
     * @desc 提现列表
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw
     * @method  GET
     * @param string keywords - 关键字:申请人
     * @param int client_id - 用户ID
     * @param string source - 提现来源
     * @param int status - 状态0待审核1审核通过2审核驳回3确认已汇款
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序id
     * @param string sort - 升/降序asc,desc
     * @return array list - 提现
     * @return int list[].id - 提现ID
     * @return string list[].amount - 金额
     * @return string list[].fee - 手续费
     * @return string list[].withdraw_amount - 提现到账金额
     * @return string list[].method - 提现方式
     * @return string list[].card_number - 银行卡号
     * @return string list[].name - 姓名
     * @return string list[].account - 支付宝账号
     * @return int list[].status - 状态0待审核1审核通过2审核驳回3确认已汇款 
     * @return string list[].reason - 驳回原因 
     * @return int list[].create_time - 申请时间 
     * @return int list[].client_id - 用户ID
     * @return string list[].username - 申请人 
     * @return string list[].company - 公司 
     * @return string list[].source - 来源
     * @return string list[].transaction_number - 交易流水号
     * @return int count - 提现总数
     */
    public function idcsmartWithdrawList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 获取提现列表
        $data = $IdcsmartWithdrawModel->idcsmartWithdrawList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 提现审核
     * @desc 提现审核
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/:id/audit
     * @method  PUT
     * @param int id - 提现ID required
     * @param int status - 状态1审核通过2审核驳回 required
     * @param string reason - 驳回原因 审核驳回时需要
     */
    public function idcsmartWithdrawAudit()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('audit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 提现审核
        $result = $IdcsmartWithdrawModel->idcsmartWithdrawAudit($param);

        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 获取余额提现设置
     * @desc 获取余额提现设置
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule/credit
     * @method  GET
     * @return array method - 提现方式ID
     * @return string process - 提现流程 
     * @return float min - 最小金额限制 
     * @return float max - 最大金额限制
     * @return string cycle - 提现周期
     * @return int cycle_limit - 提现周期次数限制,0不限
     * @return string withdraw_fee_type - 手续费类型fixed固定percent百分比
     * @return float withdraw_fee - 固定手续费金额
     * @return float percent - 手续费百分比
     * @return float percent_min - 最低手续费
     * @return int status - 状态0关闭1开启
     */
    public function idcsmartWithdrawRuleCredit()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 获取余额提现设置
        $data = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleCredit();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 保存余额提现设置
     * @desc 保存余额提现设置
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule/credit
     * @method  PUT
     * @param string source - 提现来源 required
     * @param array method - 提现方式ID required
     * @param string process - 提现流程artificial人工auto自动 required
     * @param float min - 最小金额限制 
     * @param float max - 最大金额限制
     * @param string cycle - 提现周期day每天week每周month每月 required
     * @param int cycle_limit - 提现周期次数限制,0不限
     * @param string withdraw_fee_type - 手续费类型fixed固定percent百分比 required
     * @param float withdraw_fee - 固定手续费金额
     * @param float percent - 手续费百分比
     * @param float percent_min - 最低手续费
     * @param int status - 状态0关闭1开启 required
     */
    public function saveIdcsmartWithdrawRuleCredit()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 保存余额提现设置
        $result = $IdcsmartWithdrawRuleModel->saveIdcsmartWithdrawRuleCredit($param);

        return json($result);
    }

    /**
     * 时间 2022-08-22
     * @title 用户已提现金额
     * @desc 用户已提现金额
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/client/:id
     * @method  GET
     * @param int id - 用户ID
     * @return string amount - 提现金额
     */
    public function idcsmartWithdrawClient()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 保存提现来源
        $amount = $IdcsmartWithdrawModel->idcsmartWithdrawClient($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'amount' => $amount
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-09-21
     * @title 确认已汇款
     * @desc 确认已汇款
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/:id/confirm_remit
     * @method  PUT
     * @param int id - 提现ID required
     * @param string transaction_number - 交易流水号 required
     */
    public function confirmRemit()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 参数验证
        if (!$this->validate->scene('confirm_remit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 保存提现来源
        $result = $IdcsmartWithdrawModel->idcsmartWithdrawConfirmRemit($param);

        return json($result);
    }

    /**
     * 时间 2022-09-21
     * @title 修改提现状态
     * @desc 修改提现状态
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/:id/status
     * @method  PUT
     * @param int id - 提现ID required
     * @param int status - 状态0待审核1审核通过 required
     */
    public function updateIdcsmartWithdrawStatus()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 修改提现状态
        $result = $IdcsmartWithdrawModel->updateIdcsmartWithdrawStatus($param);

        return json($result);
    }

    /**
     * 时间 2022-09-21
     * @title 修改提现交易流水号
     * @desc 修改提现交易流水号
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/:id/transaction_number
     * @method  PUT
     * @param int id - 提现ID required
     * @param string transaction_number - 交易流水号 required
     */
    public function updateIdcsmartWithdrawTransaction()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_transaction')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 修改提现交易流水号
        $result = $IdcsmartWithdrawModel->updateIdcsmartWithdrawTransaction($param);

        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 提现方式列表
     * @desc 提现方式列表
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/method
     * @method  GET
     * @return array list - 提现方式
     * @return int list[].id - 提现方式ID
     * @return string list[].name - 名称
     * @return string list[].admin_id - 管理员ID
     * @return string list[].admin - 管理员
     * @return string list[].create_time - 添加时间
     * @return int count - 提现方式总数
     */
    public function idcsmartWithdrawMethodList()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawMethodModel = new IdcsmartWithdrawMethodModel();

        // 获取提现方式列表
        $data = $IdcsmartWithdrawMethodModel->idcsmartWithdrawMethodList();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 添加提现方式
     * @desc 添加提现方式
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/method
     * @method  POST
     * @param string name - 名称 required
     */
    public function createIdcsmartWithdrawMethod()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawMethodModel = new IdcsmartWithdrawMethodModel();

        // 添加提现方式
        $result = $IdcsmartWithdrawMethodModel->createIdcsmartWithdrawMethod($param);

        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 修改提现方式
     * @desc 修改提现方式
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/method/:id
     * @method  PUT
     * @param int id - 提现方式ID required
     * @param string name - 名称 required
     */
    public function updateIdcsmartWithdrawMethod()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawMethodModel = new IdcsmartWithdrawMethodModel();

        // 修改提现方式
        $result = $IdcsmartWithdrawMethodModel->updateIdcsmartWithdrawMethod($param);

        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 删除提现方式
     * @desc 删除提现方式
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/method/:id
     * @method  DELETE
     * @param int id - 提现方式ID required
     */
    public function deleteIdcsmartWithdrawMethod()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawMethodModel = new IdcsmartWithdrawMethodModel();

        // 删除提现方式
        $result = $IdcsmartWithdrawMethodModel->deleteIdcsmartWithdrawMethod($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 驳回原因列表
     * @desc 驳回原因列表
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/reject_reason
     * @method  GET
     * @return array list - 驳回原因
     * @return int list[].id - 驳回原因ID
     * @return string list[].reason - 驳回原因内容
     * @return string list[].admin_id - 管理员ID
     * @return string list[].admin - 管理员
     * @return string list[].create_time - 添加时间
     * @return int count - 驳回原因总数
     */
    public function idcsmartWithdrawRejectReasonList()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRejectReasonModel = new IdcsmartWithdrawRejectReasonModel();

        // 获取驳回原因列表
        $data = $IdcsmartWithdrawRejectReasonModel->idcsmartWithdrawRejectReasonList();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 添加驳回原因
     * @desc 添加驳回原因
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/reject_reason
     * @method  POST
     * @param string reason - 驳回原因 required
     */
    public function createIdcsmartWithdrawRejectReason()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRejectReasonModel = new IdcsmartWithdrawRejectReasonModel();

        // 添加驳回原因
        $result = $IdcsmartWithdrawRejectReasonModel->createIdcsmartWithdrawRejectReason($param);

        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 修改驳回原因
     * @desc 修改驳回原因
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/reject_reason/:id
     * @method  PUT
     * @param int id - 驳回原因ID required
     * @param string reason - 驳回原因 required
     */
    public function updateIdcsmartWithdrawRejectReason()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRejectReasonModel = new IdcsmartWithdrawRejectReasonModel();

        // 修改驳回原因
        $result = $IdcsmartWithdrawRejectReasonModel->updateIdcsmartWithdrawRejectReason($param);

        return json($result);
    }

    /**
     * 时间 2022-10-25
     * @title 删除驳回原因
     * @desc 删除驳回原因
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/reject_reason/:id
     * @method  DELETE
     * @param int id - 驳回原因ID required
     */
    public function deleteIdcsmartWithdrawRejectReason()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRejectReasonModel = new IdcsmartWithdrawRejectReasonModel();

        // 删除驳回原因
        $result = $IdcsmartWithdrawRejectReasonModel->deleteIdcsmartWithdrawRejectReason($param['id']);

        return json($result);
    }
}