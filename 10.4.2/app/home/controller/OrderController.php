<?php
namespace app\home\controller;

use app\common\model\OrderModel;
use app\common\model\SelfDefinedFieldModel;

/**
 * @title 订单管理
 * @desc 订单管理
 * @use app\home\controller\OrderController
 */
class OrderController extends HomeBaseController
{
    /**
     * 时间 2022-05-19
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @url /console/v1/order
     * @method  GET
     * @param string keywords - 关键字,搜索范围:订单ID
     * @param string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string status - 状态Unpaid未付款Paid已付款
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,type,create_time,amount,status
     * @param string sort - 升/降序 asc,desc
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款 
     * @return string list[].gateway - 支付方式 
     * @return float list[].credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return string list[].host_name - 产品标识
     * @return string list[].description - 描述
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return int count - 订单总数
     */
	public function list()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $OrderModel = new OrderModel();

        // 获取订单列表
        $data = $OrderModel->orderList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-19
     * @title 订单详情
     * @desc 订单详情
     * @author theworld
     * @version v1
     * @url /console/v1/order/:id
     * @method  GET
     * @param int id - 订单ID required
     * @return object order - 产品
     * @return int order.id - 订单ID
     * @return string order.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单 
     * @return string order.amount - 金额 
     * @return int order.create_time - 创建时间 
     * @return int order.pay_time - 支付时间 
     * @return string order.status - 状态Unpaid未付款Paid已付款
     * @return string order.gateway - 支付方式 
     * @return string order.credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return string order.notes - 备注 
     * @return string order.refund_amount - 订单已退款金额 
     * @return string order.amount_unpaid - 未支付金额 
     * @return array order.items - 订单子项 
     * @return int order.items[].id - 订单子项ID 
     * @return string order.items[].description - 描述
     * @return string order.items[].amount - 金额 
     * @return int order.items[].host_id - 产品ID 
     * @return string order.items[].product_name - 商品名称 
     * @return string order.items[].host_name - 产品标识 
     * @return string order.items[].billing_cycle - 计费周期 
     * @return string order.items[].host_status - 产品状态Unpaid未付款Pending开通中Active使用中Suspended暂停Deleted删除Failed开通失败
     * @return  int self_defined_field[].id - 自定义字段ID
     * @return  string self_defined_field[].field_name - 字段名称
     * @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,checkbox=勾选框,textarea=文本区)
     * @return  string self_defined_field[].value - 当前值
     */
	public function index()
    {
		// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $OrderModel = new OrderModel();
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

        // 获取订单
        $order = $OrderModel->indexOrder($param['id']);
        $selfDefinedField = $SelfDefinedFieldModel->showOrderDetailField(['order_id'=>$param['id']]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'order'             => $order,
                'self_defined_field'=> $selfDefinedField,
            ]
        ];
        return json($result);
	}

    /**
     * 时间 2022-10-18
     * @title 删除订单
     * @desc 删除订单
     * @author theworld
     * @version v1
     * @url /console/v1/order/:id
     * @method  DELETE
     * @param int id - 订单ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 取消订单
        $result = $OrderModel->cancelOrder($param['id']);

        return json($result);
    }

    /**
     * 时间 2023-06-08
     * @title 订单列表导出EXCEL
     * @desc 订单列表导出EXCEL
     * @author theworld
     * @version v1
     * @url /console/v1/order/export_excel
     * @method  GET
     * @param string keywords - 关键字,搜索范围:订单ID
     * @param string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string status - 状态Unpaid未付款Paid已付款
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,type,create_time,amount,status
     * @param string sort - 升/降序 asc,desc
     */
    public function exportExcel()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $OrderModel = new OrderModel();

        // 订单列表导出EXCEL
        return $OrderModel->exportExcel($param);
    }

}