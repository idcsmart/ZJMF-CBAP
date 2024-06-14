<?php
namespace app\admin\controller;

use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\RefundRecordModel;
use app\admin\validate\OrderValidate;
use app\admin\validate\ConfigurationValidate;
use app\home\validate\ProductValidate;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\ConfigurationModel;

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
     * @param string keywords - 关键字,搜索范围:订单ID,商品名称,用户名称,邮箱,手机号
     * @param int client_id - 用户ID
     * @param string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string status - 状态Unpaid未付款Paid已付款
     * @param string amount - 金额
     * @param string gateway - 支付方式
     * @param int start_time - 开始时间
     * @param int end_time - 结束时间
     * @param int order_id - 订单ID
     * @param int product_id - 商品ID
     * @param string username - 用户名称
     * @param string email - 邮箱
     * @param string phone - 手机号
     * @param int pay_time - 支付时间
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby id 排序(id,amount,client_id,reg_time)
     * @param string sort - 升/降序 asc,desc
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款  
     * @return string list[].gateway - 支付方式 
     * @return float list[].credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int list[].client_id - 用户ID
     * @return string list[].client_name - 用户名称
     * @return string list[].client_credit - 用户余额
     * @return string list[].email - 邮箱 
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return string list[].company - 公司
     * @return int list[].client_status - 用户是否启用0:禁用,1:正常
     * @return int list[].reg_time - 用户注册时间
     * @return string list[].country - 国家
     * @return string list[].address - 地址
     * @return string list[].language - 语言
     * @return string list[].notes - 备注
     * @return string list[].refund_amount - 订单已退款金额
     * @return string list[].host_name - 产品标识
     * @return string list[].description - 描述
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id - 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return bool list[].certification - 是否实名认证true是false否(显示字段有certification返回)
     * @return string list[].certification_type - 实名类型person个人company企业(显示字段有certification返回)
     * @return string list[].client_level - 用户等级(显示字段有client_level返回)
     * @return string list[].client_level_color - 用户等级颜色(显示字段有client_level返回)
     * @return string list[].addon_client_custom_field_[id] - 用户自定义字段(显示字段有addon_client_custom_field_[id]返回,[id]为用户自定义字段ID)
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
     * @return object order - 订单
     * @return int order.id - 订单ID
     * @return string order.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单 
     * @return string order.amount - 金额 
     * @return int order.create_time - 创建时间 
     * @return string order.status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款 
     * @return string order.gateway - 支付方式 
     * @return string order.credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int order.client_id - 用户ID
     * @return string order.client_name - 用户名称
     * @return string order.notes - 备注
     * @return string order.refund_amount - 订单已退款金额
     * @return string order.amount_unpaid - 未支付金额 
     * @return string order.refundable_amount - 订单可退款金额
     * @return string order.apply_credit_amount - 订单可应用余额金额
     * @return int order.admin_id - 管理员ID
     * @return string order.admin_name - 管理员名称
     * @return int order.is_recycle - 是否在回收站(0=否,1=是)
     * @return int order.refund_orginal - 订单支付方式退款时是否支持原路返回：1是，0否(退款至下拉就不显示'原支付路径')
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
     * @return string order.items[].profit - 利润
     * @return int order.items[].agent - 代理订单1是0否
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
     * 时间 2022-05-17
     * @title 新建订单
     * @desc 新建订单
     * @author theworld
     * @version v1
     * @url /admin/v1/order
     * @method  POST
     * @param string type - 类型new新订单renew续费订单artificial人工订单 required
     * @param array products - 商品 类型为新订单时需要
     * @param int products[].product_id - 商品ID
     * @param object products[].config_options - 自定义配置
     * @param int products[].qty - 数量
     * @param float products[].price - 商品价格
     * @param object products[].customfield - 自定义字段
     * @param int id - 产品ID 类型为续费订单时需要
     * @param float amount - 金额 类型为人工订单时需要
     * @param string description - 描述 类型为人工订单时需要
     * @param int client_id - 用户ID required
     * @param object customfield - 自定义字段
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
     * 时间 2023-01-30
     * @title 删除人工调整的订单子项
     * @desc 删除人工调整的订单子项
     * @author theworld
     * @version v1
     * @url /admin/v1/order/item/:id
     * @method  DELETE
     * @param int id - 订单子项ID required
     */
    public function deleteOrderItem()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->deleteOrderItem($param['id']);

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
     * @param string transaction_number - 交易流水号
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

    /**
     * 时间 2022-05-17
     * @title 批量删除订单
     * @desc 批量删除订单
     * @author theworld
     * @version v1
     * @url /admin/v1/order
     * @method  DELETE
     * @param array id - 订单ID required
     * @param int delete_host 1 是否删除产品:0否1是 required
     */
    public function batchDelete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 删除订单
        $result = $OrderModel->batchDeleteOrder($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单退款
     * @desc 订单退款
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/refund
     * @method  POST
     * @param int id - 订单ID required
     * @param string type - 退款类型credit退款到余额transaction退款到流水original原支付路径 required
     * @param float amount - 退款金额 required
     * @param string gateway - 支付方式 退款到流水时需传
     * @param string transaction_number - 流水号 退款到流水时需传
     */
    public function orderRefund()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('refund')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单退款
        $result = $OrderModel->orderRefund($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单应用余额
     * @desc 订单应用余额
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/apply_credit
     * @method  POST
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     */
    public function orderApplyCredit()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('apply')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单应用余额
        $result = $OrderModel->orderApplyCredit($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单扣除余额
     * @desc 订单扣除余额
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/remove_credit
     * @method  POST
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     */
    public function orderRemoveCredit()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('remove')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 订单扣除余额
        $result = $OrderModel->orderRemoveCredit($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 订单退款记录列表
     * @desc 订单退款记录列表
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/refund_record
     * @method  GET
     * @param int id - 订单ID required
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 退款记录
     * @return int list[].id - 退款记录ID 
     * @return int list[].create_time - 退款时间 
     * @return string list[].amount - 金额 
     * @return int list[].admin_id - 操作人ID 
     * @return string list[].admin_name - 操作人名称
     * @return string list[].type - 退款类型:credit退款到余额,transaction退款到流水,original原支付路径
     * @return string list[].status - 退款状态：Pending待审核，Reject已拒绝，Refunding退款中，Refunded已退款
     * @return string list[].reason - 拒绝原因
     * @return int list[].refund_time - 退款时间
     * @return int list[].gateway - 支付方式
     * @return int count - 退款记录总数
     */
    public function refundRecordList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();
        
        // 订单退款记录列表
        $data = $RefundRecordModel->refundRecordList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 删除退款记录
     * @desc 删除退款记录
     * @author theworld
     * @version v1
     * @url /admin/v1/refund_record/:id
     * @method  DELETE
     * @param int id - 退款记录ID required
     */
    public function deleteRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();
        
        // 删除退款记录
        $result = $RefundRecordModel->deleteRefundRecord($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-05-10
     * @title 退款通过
     * @desc 退款通过
     * @author wyh
     * @version v1
     * @url /admin/v1/refund_record/:id/pending
     * @method  PUT
     * @param int id - 退款记录ID required
     */
    public function pendingRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->pendingRefundRecord($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-05-10
     * @title 退款拒绝
     * @desc 退款拒绝
     * @author wyh
     * @version v1
     * @url /admin/v1/refund_record/:id/reject
     * @method  PUT
     * @param int id - 退款记录ID required
     * @param string reason - 拒绝原因 required
     */
    public function rejectRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->rejectRefundRecord($param);

        return json($result);
    }

    /**
     * 时间 2024-05-10
     * @title 已退款
     * @desc 已退款
     * @author wyh
     * @version v1
     * @url /admin/v1/refund_record/:id/refunded
     * @method  PUT
     * @param int id - 退款记录ID required
     * @param string transaction_number - 交易流水ID required
     */
    public function redundedRefundRecord()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $RefundRecordModel = new RefundRecordModel();

        // 删除退款记录
        $result = $RefundRecordModel->redundedRefundRecord($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单支付方式
     * @desc 修改订单支付方式
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/gateway
     * @method  PUT
     * @param int id - 订单ID required
     * @param string gateway - 支付方式 required
     */
    public function updateGateway()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->updateGateway($param);

        return json($result);
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单备注
     * @desc 修改订单备注
     * @author theworld
     * @version v1
     * @url /admin/v1/order/:id/notes
     * @method  PUT
     * @param int id - 订单ID required
     * @param string notes - 备注
     */
    public function updateNotes()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $OrderModel = new OrderModel();
        
        // 修改订单金额
        $result = $OrderModel->updateNotes($param);

        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 结算商品
     * @desc 结算商品
     * @author wyh
     * @version v1
     * @url /admin/v1/product/settle
     * @method  POST
     * @param  int client_id - 客户ID required
     * @param  float custom_order_amount - 自定义订单金额 required
     * @param  float custom_renew_amount - 自定义续费金额 required
     * @param  int product_id - 商品ID required
     * @param  object config_options - 自定义配置 required
     * @param  object customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @param  int qty - 数量 required
     * @return int order_id - 订单ID
     */
    public function settle()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        $ProductValidate = new ProductValidate();
        if (!$ProductValidate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ProductModel = new ProductModel();

        // 结算商品
        $result = $ProductModel->settle($param,true);

        // 修改订单金额！！
        if ($result['status']==200){
            $orderId = $result['data']['order_id']??0;
            $OrderModel = new OrderModel();
            $order = $OrderModel->find($orderId);
            $amount = $order['amount'];
            if (isset($param['custom_order_amount']) && $param['custom_order_amount']>=0){
                $OrderModel->update([
                    'amount' => $param['custom_order_amount'],
                    'amount_unpaid' => $param['custom_order_amount']
                ],['id'=>$orderId]);
                if(($param['custom_order_amount']-$order['amount'])!=0){
                    OrderItemModel::create([
                        'type' => 'manual',
                        'order_id' => $orderId,
                        'client_id' => $order['client_id'],
                        'description' => lang('update_amount'),
                        'amount' => $param['custom_order_amount']-$amount,
                        'create_time' => time()
                    ]);
                }
            }
            $OrderItemModel = new OrderItemModel();
            $orderItems = $OrderItemModel->where('order_id',$orderId)->select();
            $hostCount = $OrderItemModel->where('order_id',$orderId)->where('type','host')->count();
            $HostModel = new HostModel();
            foreach ($orderItems as $orderItem){
                /*if (isset($param['custom_order_amount']) && !in_array($orderItem['type'],['manual','host'])){
                    $orderItem->save(['amount'=>0]);
                }*/
                $hostUpdate = [];
                if (isset($param['custom_order_amount']) && $param['custom_order_amount']>=0 && $hostCount>0){
                    $hostUpdate['first_payment_amount'] = bcdiv($param['custom_order_amount'],$hostCount,2);
                }
                if (isset($param['custom_renew_amount']) && $param['custom_renew_amount']>=0){
                    $hostUpdate['renew_amount'] = $param['custom_renew_amount'];
                }
                // 自定义续费金额
                if (!empty($hostUpdate)){
                    $HostModel->update($hostUpdate,['id'=>$orderItem['host_id']]);
                }
            }
        }

        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 商品配置页面
     * @desc 商品配置页面
     * @url /admin/v1/product/:id/config_option
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 商品ID required
     * @param   string tag - 商品价格显示标识
     * @return  string data.content - 模块输出内容
     */
    public function moduleClientConfigOption()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();

        $ProductModel->isAdmin = true;

        $result = $ProductModel->moduleClientConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 获取订单回收站设置
     * @desc  获取订单回收站设置
     * @url /admin/v1/order/recycle_bin/config
     * @method  GET
     * @author hh
     * @version v1
     * @return  string order_recycle_bin - 订单回收站(0=关闭,1=开启)
     * @return  string order_recycle_bin_save_days - 保留天数(0=永不删除)
     */
    public function getOrderRecycleBinConfig()
    {
        $ConfigurationModel = new ConfigurationModel();

        $data = $ConfigurationModel->getOrderRecycleBinConfig();

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => (object)$data,
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 开启订单回收站
     * @desc  开启订单回收站
     * @url /admin/v1/order/recycle_bin/enable
     * @method  POST
     * @author hh
     * @version v1
     */
    public function enableOrderRecycleBin()
    {
        $ConfigurationModel = new ConfigurationModel();
        
        $result = $ConfigurationModel->orderRecycleBinConfigUpdate(['order_recycle_bin'=>1]);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 修改订单回收站设置
     * @desc  修改订单回收站设置
     * @url /admin/v1/order/recycle_bin/config
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int order_recycle_bin - 订单回收站(0=关闭,1=开启) require
     * @param   int order_recycle_bin_save_days - 保留天数(0=永不删除)
     */
    public function orderRecycleBinConfigUpdate()
    {
        $param = $this->request->param();

        $ConfigurationValidate = new ConfigurationValidate();
        if (!$ConfigurationValidate->scene('order_recycle_bin')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ConfigurationValidate->getError())]);
        }

        $ConfigurationModel = new ConfigurationModel();
        
        $result = $ConfigurationModel->orderRecycleBinConfigUpdate($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 订单回收站列表
     * @desc  订单回收站列表
     * @author hh
     * @version v1
     * @url /admin/v1/order/recycle_bin
     * @method  GET
     * @param string keywords - 关键字,搜索范围:订单ID,商品名称,用户名称,邮箱,手机号
     * @param int client_id - 用户ID
     * @param string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string status - 状态Unpaid未付款Paid已付款
     * @param string amount - 金额
     * @param string gateway - 支付方式
     * @param int start_time - 开始时间
     * @param int end_time - 结束时间
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @param  int start_recycle_time - 回收开始时间
     * @param  int end_recycle_time - 回收结束时间
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款  
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
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return int list[].is_lock - 是否锁定(0=否,1=是)
     * @return int list[].recycle_time - 放入回收站时间
     * @return int list[].will_delete_time - 彻底删除时间
     * @return int count - 订单总数
     */
    public function recycleBinOrderList()
    {
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        $OrderModel = new OrderModel();

        $data = $OrderModel->orderList($param, 'recycle_bin');

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 恢复订单
     * @desc  恢复订单
     * @url /admin/v1/order/recycle_bin/recover
     * @method  POST
     * @author hh
     * @version v1
     * @param   array id - 订单ID require
     */
    public function recoverOrder()
    {
        $param = $this->request->param();

        $OrderModel = new OrderModel();
        
        $result = $OrderModel->recoverOrder($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 从回收站删除订单
     * @desc  从回收站删除订单
     * @url /admin/v1/order/recycle_bin
     * @method  DELETE
     * @author hh
     * @version v1
     * @param   array id - 订单ID require
     */
    public function deleteOrderFromRecycleBin()
    {
        $param = $this->request->param();
        $param['delete_host'] = 1;

        $OrderModel = new OrderModel();
        
        $result = $OrderModel->batchDeleteOrder($param, 'recycle_bin');
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 清空回收站
     * @desc  清空回收站
     * @url /admin/v1/order/recycle_bin/clear
     * @method  POST
     * @author hh
     * @version v1
     */
    public function clearRecycleBin()
    {
        $OrderModel = new OrderModel();
        
        $result = $OrderModel->batchDeleteOrder([], 'clear_recycle_bin');
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 锁定订单
     * @desc  锁定订单
     * @url /admin/v1/order/lock
     * @method  POST
     * @author hh
     * @version v1
     * @param   array id - 订单ID require
     */
    public function lockOrder()
    {
        $param = $this->request->param();

        $OrderModel = new OrderModel();
        
        $result = $OrderModel->lockOrder($param);
        return json($result);
    }

    /**
     * 时间 2024-03-18
     * @title 取消锁定订单
     * @desc  取消锁定订单
     * @url /admin/v1/order/unlock
     * @method  POST
     * @author hh
     * @version v1
     * @param   array id - 订单ID require
     */
    public function unlockOrder()
    {
        $param = $this->request->param();

        $OrderModel = new OrderModel();
        
        $result = $OrderModel->unlockOrder($param);
        return json($result);
    }


}