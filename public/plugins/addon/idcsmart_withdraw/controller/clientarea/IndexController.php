<?php
namespace addon\idcsmart_withdraw\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawModel;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawRuleModel;
use addon\idcsmart_withdraw\validate\IdcsmartWithdrawValidate;

/**
 * @title 提现插件
 * @desc 提现插件
 * @use addon\idcsmart_withdraw\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
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
     * @url /console/v1/withdraw
     * @method  GET
     * @param int start_time - 开始时间，时间戳(s)
     * @param int end_time - 结束时间，时间戳(s)
     * @param string source - 提现来源，默认为余额
     * @param int status - 状态0待审核1待打款2审核驳回3已打款
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序id
     * @param string sort - 升/降序asc,desc
     * @return array list - 提现
     * @return int list[].id - 提现ID
     * @return string list[].amount - 金额
     * @return string list[].fee - 手续费
     * @return string list[].method - 提现方式
     * @return string list[].withdraw_amount - 提现到账金额
     * @return int list[].status - 状态0待审核1待打款2审核驳回3已打款
     * @return string list[].reason - 驳回原因 
     * @return int list[].create_time - 提现时间 
     * @return int count - 提现总数
     */
    public function idcsmartWithdrawList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 获取提现列表
        $data = $IdcsmartWithdrawModel->idcsmartWithdrawList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-25
     * @title 获取余额提现设置
     * @desc 获取余额提现设置
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw/rule/credit
     * @method  GET
     * @return array method - 提现方式
     * @return int method[].id - 提现方式ID
     * @return string method[].name - 提现方式名称
     * @return string process - 提现流程 
     * @return float min - 最小金额限制 
     * @return float max - 最大金额限制
     * @return string cycle - 提现周期
     * @return int cycle_limit - 提现周期次数限制,0不限
     * @return string withdraw_fee_type - 手续费类型fixed固定percent百分比
     * @return float withdraw_fee - 固定手续费金额
     * @return float percent - 手续费百分比
     * @return float percent_min - 最低手续费
     */
    public function idcsmartWithdrawRuleCredit()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartWithdrawRuleModel = new IdcsmartWithdrawRuleModel();

        // 获取提现规则
        $data = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleCredit('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-26
     * @title 申请提现
     * @desc 申请提现
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw
     * @method  POST
     * @param string source - 提现来源 required
     * @param int method_id - 提现方式ID required
     * @param float amount - 提现金额 required
     * @param string card_number - 银行卡号 
     * @param string name - 姓名
     * @param string account - 账号
     * @param string notes - 备注
     * @param float fee - 提现手续费,非余额提现时使用
     */
    public function idcsmartWithdraw()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('withdraw')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        // 申请提现
        $result = $IdcsmartWithdrawModel->idcsmartWithdraw($param);

        return json($result);
    }
}