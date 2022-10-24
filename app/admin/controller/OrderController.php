<?php
namespace app\admin\controller;

use app\common\model\OrderModel;
use app\admin\validate\OrderValidate;

/**
 * @title 订单管理
 * @desc 订单管理
 * @use app\admin\controller\OrderController
 */
class OrderController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new OrderValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @url /admin/v1/order
     * @method  GET
     * @param string keywords - 关键字,搜索范围:订单ID,用户名称,邮箱,手机号
     * @param int client_id - 用户ID
     * @param string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string status - 状态Unpaid未付款Paid已付款
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款Cancelled已取消  
     * @return string list[].gateway - 支付方式 
     * @return float list[].credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int list[].client_id - 用户ID
     * @return string list[].client_name - 用户名称
     * @return string list[].client_credit - 用户余额
     * @return string list[].email - 邮箱 
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return string list[].company - 公司
     * @return string list[].host_name - 产品标识
     * @return string list[].billing_cycle - 计费周期
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return int count - 订单总数
     */
	public function orderList()
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
     * 时间 2022-05-17
     * @title 订单详情
     * @desc 订单详情
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id
     * @method  GET
     * @param int id - 订单ID required
     * @return object order - 产品
     * @return int order.id - 订单ID
     * @return string order.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单 
     * @return string order.amount - 金额 
     * @return int order.create_time - 创建时间 
     * @return string order.status - 状态Unpaid未付款Paid已付款Cancelled已取消 
     * @return string order.gateway - 支付方式 
     * @return float order.credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return array order.items - 订单子项 
     * @return int order.items[].id - 订单子项ID 
     * @return string order.items[].description - 描述
     * @return string order.items[].amount - 金额 
     * @return int order.items[].host_id - 产品ID 
     * @return string order.items[].product_name - 商品名称 
     * @return string order.items[].host_name - 产品标识 
     * @return string order.items[].billing_cycle - 计费周期 
     * @return string order.items[].host_status - 产品状态Unpaid未付款Pending开通中Active使用中Suspended暂停Deleted删除Failed开通失败
     * @return int order.items[].edit - 是否可编辑1是0否
     */
	public function index()
    {
		// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $OrderModel = new OrderModel();

        // 获取订单
        $order = $OrderModel->indexOrder($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'order' => $order
            ]
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 新建订单
     * @desc 新建订单
     * @author theworld
     * @version v1
     * @url /admin/v1/order
     * @method  POST
     * @param string type - 类型new新订单upgrade升降级订单artificial人工订单 required
     * @param array products - 商品 类型为新订单时需要
     * @param int products[].product_id - 商品ID
     * @param object products[].config_options - 自定义配置
     * @param int products[].qty - 数量
     * @param float products[].price - 商品价格
     * @param int host_id - 产品ID 类型为升降级订单时需要
     * @param object product - 升降级商品 类型为升降级订单时需要
     * @param int product.product_id - 商品ID
     * @param object product.config_options - 自定义配置
     * @param float product.price - 商品价格
     * @param float amount - 金额 类型为人工订单时需要
     * @param string description - 描述 类型为人工订单时需要
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
        $OrderModel = new OrderModel();
        
        // 新建订单
        $result = $OrderModel->createOrder($param);

        return json($result);
	}

    /**
     * 时间 2022-07-01
     * @title 获取升降级订单金额
     * @desc 获取升降级订单金额
     * @author theworld
     * @version v1
     * @url /admin/v1/order/upgrade/amount
     * @method  POST
     * @param int host_id - 产品ID required
     * @param object product - 升降级商品 required
     * @param int product.product_id - 商品ID
     * @param object product.config_options - 自定义配置
     * @param float product.price - 商品价格
     * @param int client_id - 用户ID required
     * @return string refund - 原产品应退款金额
     * @return string pay - 新产品应付金额
     * @return string amount - 升降级订单金额,前两者之差
     */
    public function getUpgradeAmount()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('upgrade')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 获取升降级订单金额
        $result = $OrderModel->getUpgradeAmount($param);

        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 调整订单金额
     * @desc 调整订单金额
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/amount
     * @method  PUT
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     * @param string description - 描述 required
     */
	public function updateAmount()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('amount')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->updateAmount($param);

        return json($result);
	}


    /**
     * 时间 2022-05-17
     * @title 编辑人工调整的订单子项
     * @desc 编辑人工调整的订单子项
     * @author theworld
     * @version v1
     * @url /admin/v1/order/item/:id
     * @method  PUT
     * @param int id - 订单子项ID required
     * @param float amount - 金额 required
     * @param string description - 描述 required
     */
    public function updateOrderItem()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('amount')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->updateOrderItem($param);

        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 标记支付
     * @desc 标记支付
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/status/paid
     * @method  PUT
     * @param int id - 订单ID required
     * @param int use_credit - 是否使用余额0否1是 required
     */
	public function paid()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('paid')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单标记支付
        $result = $OrderModel->orderPaid($param);

        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 删除订单
     * @desc 删除订单
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id
     * @method  DELETE
     * @param int id - 订单ID required
     * @param int delete_host 1 是否删除产品:0否1是 required
     */
	public function delete()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('delete')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 删除订单
        $result = $OrderModel->deleteOrder($param);

        return json($result);
	}
}