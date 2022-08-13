<?php
namespace addon\idcsmart_withdraw\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawRuleModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawSourceModel;
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
     * @param int status - 状态0待审核1审核通过2审核驳回 
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 提现
     * @return int list[].id - 提现ID
     * @return string list[].amount - 金额
     * @return int list[].status - 状态0待审核1审核通过2审核驳回 
     * @return int list[].create_time - 申请时间 
     * @return string list[].username - 申请人 
     * @return string list[].source - 来源
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
     * @title 提现规则列表
     * @desc 提现规则列表
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 提现规则
     * @return int list[].id - 提现规则ID
     * @return string list[].source - 来源
     * @return string list[].admin - 提交人 
     * @return int list[].create_time - 提交时间 
     * @return int list[].status - 状态0关闭1开启 
     * @return int count - 提现规则总数
     */
    public function idcsmartWithdrawRuleList()
    {  
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 获取提现规则列表
        $data = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 提现规则详情
     * @desc 提现规则详情
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule/:id
     * @method  GET
     * @param int id - 提现规则ID required
     * @return object rule - 提现规则
     * @return int rule.id - 提现规则ID
     * @return string rule.source - 提现来源
     * @return array rule.method - 提现方式bank银行卡alipay支付宝 
     * @return string rule.process - 提现流程 
     * @return string rule.min - 最小金额限制 
     * @return string rule.max - 最大金额限制
     * @return string rule.cycle - 提现周期
     * @return int rule.cycle_limit - 提现周期次数限制,0不限
     * @return int rule.withdraw_fee_type - 手续费类型fixed固定percent百分比
     * @return int rule.withdraw_fee - 固定手续费金额
     * @return int rule.percent - 手续费百分比
     * @return int rule.percent_min - 百分比最低计算金额,不足时以该金额计算
     */
    public function idcsmartWithdrawRuleDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 获取提现规则
        $rule = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleDetail($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'rule' => $rule
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 新增提现规则
     * @desc 新增提现规则
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule
     * @method  POST
     * @param string source - 提现来源 required
     * @param array method - 提现方式bank银行卡alipay支付宝 required
     * @param string process - 提现流程artificial人工auto自动 required
     * @param float min - 最小金额限制 
     * @param float max - 最大金额限制
     * @param string cycle - 提现周期day每天week每周month每月 required
     * @param int cycle_limit - 提现周期次数限制,0不限
     * @param string withdraw_fee_type - 手续费类型fixed固定percent百分比 required
     * @param float withdraw_fee - 固定手续费金额
     * @param float percent - 手续费百分比
     * @param float percent_min - 百分比最低计算金额,不足时以该金额计算
     */
    public function createIdcsmartWithdrawRule()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 创建帮助文档分类
        $result = $IdcsmartWithdrawRuleModel->createIdcsmartWithdrawRule($param);

        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 编辑提现规则
     * @desc 编辑提现规则
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule/:id
     * @method  PUT
     * @param int id - 提现规则ID required
     * @param array method - 提现方式bank银行卡alipay支付宝 required
     * @param string process - 提现流程artificial人工auto自动 required
     * @param float min - 最小金额限制 
     * @param float max - 最大金额限制
     * @param string cycle - 提现周期day每天week每周month每月 required
     * @param int cycle_limit - 提现周期次数限制,0不限
     * @param string withdraw_fee_type - 手续费类型fixed固定percent百分比 required
     * @param float withdraw_fee - 固定手续费金额
     * @param float percent - 手续费百分比
     * @param float percent_min - 百分比最低计算金额,不足时以该金额计算
     */
    public function updateIdcsmartWithdrawRule()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 编辑提现规则
        $result = $IdcsmartWithdrawRuleModel->updateIdcsmartWithdrawRule($param);

        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 删除提现规则
     * @desc 删除提现规则
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule/:id
     * @method  DELETE
     * @param int id - 提现规则ID required
     */
    public function deleteIdcsmartWithdrawRule()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 删除提现规则
        $result = $IdcsmartWithdrawRuleModel->deleteIdcsmartWithdrawRule($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 开启/关闭提现规则
     * @desc 开启/关闭提现规则
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/rule/:id/status
     * @method  PUT
     * @param int id - 提现规则ID required
     * @param int status - 状态0关闭1开启 required
     */
    public function idcsmartWithdrawRuleStatus()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 开启/关闭提现规则
        $result = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleStatus($param);

        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 获取提现来源
     * @desc 获取提现来源
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/source
     * @method  GET
     * @return array source - 提现来源
     * @return string source[].name - 标识
     * @return string source[].title - 名称
     */
    public function idcsmartWithdrawSource()
    {
        // 实例化模型类
        $IdcsmartWithdrawSourceModel = new IdcsmartWithdrawSourceModel();

        // 获取提现来源
        $data = $IdcsmartWithdrawSourceModel->idcsmartWithdrawSource();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 保存提现来源
     * @desc 保存提现来源
     * @author theworld
     * @version v1
     * @url /admin/v1/withdraw/source
     * @method  PUT
     * @param array source - 提现来源,插件标识组成的数组 required
     */
    public function idcsmartWithdrawSourceSave()
    {
    	// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawSourceModel = new IdcsmartWithdrawSourceModel();

        // 保存提现来源
        $result = $IdcsmartWithdrawSourceModel->idcsmartWithdrawSourceSave($param);

        return json($result);
    }

}