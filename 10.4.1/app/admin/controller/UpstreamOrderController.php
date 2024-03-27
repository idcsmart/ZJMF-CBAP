<?php
namespace app\admin\controller;

use app\common\model\UpstreamOrderModel;

/**
 * @title 上下游(后台)
 * @desc 上下游(后台)
 * @use app\admin\controller\UpstreamOrderController
 */
class UpstreamOrderController extends AdminBaseController
{
    /**
     * 时间 2023-02-13
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/order
     * @method  GET
     * @param string keywords - 关键字,搜索范围:ID,用户名称,邮箱,手机号,商品名称,产品标识
     * @param int supplier_id - 供应商ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].profit - 利润 
     * @return string list[].status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款  
     * @return string list[].gateway - 支付方式 
     * @return string list[].credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return string list[].description - 描述 
     * @return int list[].client_id - 用户ID
     * @return string list[].client_name - 用户名称
     * @return string list[].email - 邮箱 
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return string list[].company - 公司
     * @return string list[].product_name - 商品名称
     * @return string list[].host_name - 产品标识
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
        $UpstreamOrderModel = new UpstreamOrderModel();

        // 获取上游订单列表
        $data = $UpstreamOrderModel->orderList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 销售信息
     * @desc 销售信息
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/sell_info
     * @method  GET
     * @param int supplier_id - 供应商ID
     * @return string total - 总销售额 
     * @return string profit - 总利润 
     * @return int product_count - 商品总数
     * @return int host_count - 产品总数
     */
    public function sellInfo()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $UpstreamOrderModel = new UpstreamOrderModel();

        // 获取销售信息
        $data = $UpstreamOrderModel->sellInfo($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}