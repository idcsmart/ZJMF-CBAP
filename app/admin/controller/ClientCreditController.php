<?php
namespace app\admin\controller;

use app\common\model\ClientCreditModel;
use app\admin\validate\ClientCreditValidate;
use app\common\model\OrderTmpModel;

/**
 * @title 用户余额管理
 * @desc 用户余额管理
 * @use app\admin\controller\ClientCreditController
 */
class ClientCreditController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ClientCreditValidate();
    }

    /**
     * 时间 2022-05-11
     * @title 用户余额变更记录列表
     * @desc 用户余额变更记录列表
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/credit
     * @method  GET
     * @param int id - 用户ID required
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 记录
     * @return int list[].id - 记录ID
     * @return string list[].type - 类型:人工Artificial,充值Recharge,应用至订单Applied,超付Overpayment,少付Underpayment,退款Refund,提现Withdraw
     * @return string list[].amount - 金额 
     * @return string list[].notes - 备注 
     * @return int list[].create_time - 变更时间 
     * @return int list[].admin_id - 管理员ID 
     * @return string list[].admin_name - 管理员名称 
     * @return int count - 记录总数
     */
    public function clientCreditList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $ClientCreditModel = new ClientCreditModel();

        // 获取记录
        $data = $ClientCreditModel->clientCreditList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-11
     * @title 更改用户余额
     * @desc 更改用户余额
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/credit
     * @method  PUT
     * @param int id - 用户ID required
     * @param string type - 类型recharge充值deduction扣费 required
     * @param float amount - 金额 required
     * @param string notes - 备注 required
     */
	public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientCreditModel = new ClientCreditModel();

        // 计算当前余额,小于0则报错
        if($param['type']=='deduction' && $param['amount']>0){
            $param['amount'] = -$param['amount'];
        }
        $param['type'] = 'Artificial';

        // 修改余额
        $result = $ClientCreditModel->updateClientCredit($param);

        return json($result);
	}

    /**
     * 时间 2022-05-24
     * @title 充值
     * @desc 充值
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:id/recharge
     * @method  post
     * @param int client_id 1 用户ID
     * @param float amount 1.00 金额
     * @param string gateway WxPay 支付方式
     */
    public function recharge()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $OrderTmpModel->isAdmin = true;

        $result = $OrderTmpModel->recharge($param);

        return json($result);
    }
}	