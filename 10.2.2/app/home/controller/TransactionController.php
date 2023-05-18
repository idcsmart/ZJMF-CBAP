<?php
namespace app\home\controller;

use app\common\model\TransactionModel;

/**
 * @title 消费管理
 * @desc 消费管理
 * @use app\home\controller\TransactionController
 */
class TransactionController extends HomeBaseController
{
    /**
     * 时间 2022-05-19
     * @title 交易记录
     * @desc 交易记录
     * @author theworld
     * @version v1
     * @url /console/v1/transaction
     * @method  GET
     * @param string keywords - 关键字,搜索范围:交易流水号,订单ID
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
     * @return int list[].order_id - 订单ID 
     * @return int list[].create_time - 创建时间
     * @return string list[].type - 订单类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return array list[].hosts - 产品
     * @return int list[].hosts[].id - 产品ID
     * @return string list[].hosts[].name - 商品名称
     * @return array list[].descriptions - 描述
     * @return int count - 交易流水总数
     */
	public function list()
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
}