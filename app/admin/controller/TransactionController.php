<?php
namespace app\admin\controller;

use app\common\model\TransactionModel;
use app\admin\validate\TransactionValidate;

/**
 * @title 交易流水管理
 * @desc 交易流水管理
 * @use app\admin\controller\TransactionController
 */
class TransactionController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new TransactionValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 交易流水列表
     * @desc 交易流水列表
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction
     * @method  GET
     * @param string keywords - 关键字,搜索范围:交易流水号,订单ID,用户名称,邮箱,手机号
     * @param int client_id - 用户ID
     * @param string gateway - 支付方式
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 交易流水
     * @return int list[].id - 交易流水ID 
     * @return float list[].amount - 金额
     * @return string list[].gateway - 支付方式
     * @return string list[].transaction_number - 交易流水号
     * @return int list[].client_id - 用户ID 
     * @return string list[].client_name - 用户名称 
     * @return string list[].email - 邮箱 
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return string list[].company - 公司 
     * @return int list[].order_id - 订单ID 
     * @return int list[].create_time - 创建时间
     * @return string list[].type - 订单类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return array list[].hosts - 产品
     * @return int list[].hosts[].id - 产品ID
     * @return string list[].hosts[].name - 商品名称
     * @return array list[].descriptions - 描述
     * @return int count - 交易流水总数
     */
	public function transactionList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $TransactionModel = new TransactionModel();

        // 获取交易流水列表
        $data = $TransactionModel->transactionList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 新增交易流水
     * @desc 新增交易流水
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction
     * @method  POST
     * @param float amount - 金额 required
     * @param string gateway - 支付方式 required
     * @param string transaction_number - 交易流水号
     * @param int client_id - 用户ID required
     */
	public function create()
    {
		// 接收参数
		$param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

		// 实例化模型类
        $TransactionModel = new TransactionModel();
        
        // 新建流水
        $result = $TransactionModel->createTransaction($param);

        return json($result);
	}

    /**
     * 时间 2022-10-12
     * @title 编辑交易流水
     * @desc 编辑交易流水
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction/:id
     * @method  PUT
     * @param int id - 交易流水ID required
     * @param float amount - 金额 required
     * @param string gateway - 支付方式 required
     * @param string transaction_number - 交易流水号
     * @param int client_id - 用户ID required
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
        $TransactionModel = new TransactionModel();
        
        // 编辑交易流水
        $result = $TransactionModel->updateTransaction($param);

        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 删除交易流水
     * @desc 删除交易流水
     * @author theworld
     * @version v1
     * @url /admin/v1/transaction/:id
     * @method  DELETE
     * @param int id - 交易流水ID required
     */
	public function delete()
    {
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $TransactionModel = new TransactionModel();
        
        // 删除流水
        $result = $TransactionModel->deleteTransaction($param['id']);

        return json($result);
	}
}