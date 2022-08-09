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
     * 时间 2022-07-25
     * @title 提现规则详情
     * @desc 提现规则详情
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw/rule
     * @method  GET
     * @param string source - 提现来源,credit余额或者插件标识 required
     * @return object rule - 提现规则
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

        $param['source'] = $param['source'] ?? '';

        // 获取提现规则
        $rule = $IdcsmartWithdrawRuleModel->idcsmartWithdrawRuleDetail($param['source'], 'home');

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
     * 时间 2022-07-26
     * @title 申请提现
     * @desc 申请提现
     * @author theworld
     * @version v1
     * @url /console/v1/withdraw
     * @method  POST
     * @param string source - 提现来源 required
     * @param string method - 提现方式bank银行卡alipay支付宝 required
     * @param float amount - 提现金额 required
     * @param string card_number - 银行卡号 提现方式为银行卡时必填 
     * @param string name - 提现方式为银行卡时必填
     * @param string account - 支付宝账号 提现方式为支付宝时必填
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