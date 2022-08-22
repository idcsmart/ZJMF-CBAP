<?php
namespace app\common\model;

use think\db\Where;
use think\Model;
use app\admin\model\PluginModel;
use app\common\logic\ModuleLogic;
use app\common\model\NoticeSettingModel;

/**
 * @title 订单模型
 * @desc 订单模型
 * @use app\common\model\OrderModel
 */
class OrderModel extends Model
{
	protected $name = 'order';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'client_id'         => 'int',
        'type'              => 'string',
        'status'            => 'string',
        'amount'            => 'float',
        'credit'            => 'float',
        'amount_unpaid'     => 'float',
        'upgrade_refund'    => 'int',
        'gateway'           => 'string',
        'gateway_name'      => 'string',
        'notes'             => 'string',
        'pay_time'          => 'int',
        'due_time'          => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

	/**
     * 时间 2022-05-17
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:订单ID,用户名称,邮箱,手机号
     * @param int param.client_id - 用户ID
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string param.status - 状态Unpaid未付款Paid已付款
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,type,create_time,amount,status
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款 
     * @return string list[].gateway - 支付方式 
     * @return float list[].credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int list[].client_id - 用户ID,前台接口调用时不返回
     * @return string list[].client_name - 用户名称,前台接口调用时不返回
     * @return string list[].client_credit - 用户余额,前台接口调用时不返回
     * @return string list[].email - 邮箱,前台接口调用时不返回
     * @return string list[].phone_code - 国际电话区号,前台接口调用时不返回 
     * @return string list[].phone - 手机号,前台接口调用时不返回 
     * @return string list[].company - 公司,前台接口调用时不返回 
     * @return string list[].host_name - 产品标识
     * @return string list[].billing_cycle - 计费周期
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return int count - 订单总数
     */
    public function orderList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['client_id'] = get_client_id();
            if(empty($param['client_id'])){
                return ['list' => [], 'count' => 0];
            }
        }else{
            $param['client_id'] = isset($param['client_id']) ? intval($param['client_id']) : 0;
        }

        $param['keywords'] = $param['keywords'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'type', 'create_time', 'amount', 'status']) ? 'o.'.$param['orderby'] : 'o.id';

        $count = $this->alias('o')
            ->field('o.id')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->where(function ($query) use($param) {
                $query->where('o.type', '<>', 'recharge');
                if(!empty($param['client_id'])){
                    $query->where('o.client_id', $param['client_id']);
                }
                if(!empty($param['keywords'])){
                    $query->where('o.id|c.username|c.email|c.phone', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['type'])){
                    $query->where('o.type', $param['type']);
                }
                if(!empty($param['status'])){
                    $query->where('o.status', $param['status']);
                }
            })
            ->count();
        $orders = $this->alias('o')
            ->field('o.id,o.type,o.create_time,o.amount,o.status,o.gateway_name gateway,o.credit,o.client_id,c.username client_name,c.credit client_credit,c.email,c.phone_code,c.phone,c.company')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->where(function ($query) use($param) {
                $query->where('o.type', '<>', 'recharge');
                if(!empty($param['client_id'])){
                    $query->where('o.client_id', $param['client_id']);
                }
                if(!empty($param['keywords'])){
                    $query->where('o.id|c.username|c.email|c.phone', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['type'])){
                    $query->where('o.type', $param['type']);
                }
                if(!empty($param['status'])){
                    $query->where('o.status', $param['status']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        $orderId = array_column($orders, 'id');

        $orderItems = OrderItemModel::alias('oi')
        	->field('oi.order_id,oi.type,h.id,h.name,h.billing_cycle,h.billing_cycle_name,p.name product_name,oi.description')
        	->leftjoin('host h',"h.id=oi.host_id")
        	->leftjoin('product p',"p.id=oi.product_id")
        	->whereIn('oi.order_id', $orderId)
        	->select()
            ->toArray();
        $orderItemCount = [];
        $names = [];
        $billingCycles = [];
        $productNames = [];
        $descriptions = [];
        $hostIds = [];
        foreach ($orderItems as $key => $orderItem) {
            $orderItemCount[$orderItem['order_id']] = $orderItemCount[$orderItem['order_id']] ?? 0;
            $orderItemCount[$orderItem['order_id']]++;
            // 获取产品ID
            if(!empty($orderItem['id'])){
                $hostIds[$orderItem['order_id']][] = $orderItem['id'];
            }
            // 获取产品名称
            $names[$orderItem['order_id']][] = $orderItem['name'];
            // 获取产品计费周期
            $billingCycles[$orderItem['order_id']][] = $orderItem['billing_cycle']!='onetime' ? $orderItem['billing_cycle_name'] : '';
            // 获取商品名称
            if(in_array($orderItem['type'], ['addon_idcsmart_promo_code'])){
                $productNames[$orderItem['order_id']][] = $orderItem['description'];
            }else if(!empty($orderItem['product_name'])){
                $productNames[$orderItem['order_id']][] = $orderItem['product_name'];
            }else{
                $productNames[$orderItem['order_id']][] = $orderItem['description'];
            }
            // 获取商品名称
            if(!empty($orderItem['description'])){
                $descriptions[$orderItem['order_id']][] = $orderItem['description'];
            }
        }

        foreach ($orders as $key => $order) {
            $orders[$key]['amount'] = amount_format($order['amount']); // 处理金额格式
            $orders[$key]['client_credit'] = amount_format($order['client_credit']); // 处理金额格式

            // 获取产品标识,产品标识不一致是返回空字符串
            if($order['type']=='artificial'){
                $orders[$key]['host_name'] = $descriptions[$order['id']] ?? [];
            }else{
                $orders[$key]['host_name'] = $names[$order['id']] ?? [];
            }
            if(!empty($orders[$key]['host_name']) && count($orders[$key]['host_name'])==1){
                $orders[$key]['host_name'] = $orders[$key]['host_name'][0] ?? '';
            }else{
                $orders[$key]['host_name'] = '';
            } 
        	

            // 获取计费周期,计费周期不一致是返回空字符串
            $billingCycle = isset($billingCycles[$order['id']]) ? array_values(array_unique($billingCycles[$order['id']])) : [];
            if(!empty($billingCycle) && count($billingCycle)==1){
                $orders[$key]['billing_cycle'] = $billingCycle[0] ?? '';
            }else{
                $orders[$key]['billing_cycle'] = '';
            }

            // 获取商品名称
            $orders[$key]['product_names'] = $productNames[$order['id']] ?? [];

            if(count($orders[$key]['product_names'])==1){
                $orders[$key]['host_id'] = $hostIds[$order['id']][0] ?? 0;
            }else{
                $orders[$key]['host_id'] = 0;
            }

            $orders[$key]['order_item_count'] = $orderItemCount[$order['id']] ?? 0;

            // 前台接口去除字段
            if($app=='home'){
                unset($orders[$key]['client_id'], $orders[$key]['client_name'], $orders[$key]['client_credit'], $orders[$key]['email'], $orders[$key]['phone_code'], $orders[$key]['phone'], $orders[$key]['company']);
            }
        }


        return ['list' => $orders, 'count' => $count];
    }

    /**
     * 时间 2022-05-17
     * @title 订单详情
     * @desc 订单详情
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @return int id - 订单ID 
     * @return string type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return string amount - 金额 
     * @return int create_time - 创建时间 
     * @return string status - 状态Unpaid未付款Paid已付款
     * @return string gateway - 支付方式 
     * @return float credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return array items - 订单子项 
     * @return int items[].id - 订单子项ID 
     * @return string items[].description - 描述
     * @return string items[].amount - 金额 
     * @return int items[].host_id - 产品ID 
     * @return string items[].product_name - 商品名称 
     * @return string items[].host_name - 产品标识 
     * @return string items[].billing_cycle - 计费周期 
     * @return string items[].host_status - 产品状态Unpaid未付款Pending开通中Active使用中Suspended暂停Deleted删除Failed开通失败
     */
    public function indexOrder($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $order = $this->field('id,type,amount,create_time,status,gateway_name gateway,credit,client_id')->find($id);
        if (empty($order)){
            return (object)[]; // 转换为对象
        }

        // 订单的用户ID和前台用户不一致时返回空对象
        if($app=='home'){
            $client_id = get_client_id();
            if($order['client_id']!=$client_id){
                return (object)[]; // 转换为对象
            }
        }

        $order['amount'] = amount_format($order['amount']); // 处理金额格式
        unset($order['client_id']);

        $orderItems = OrderItemModel::alias('oi')
            ->field('oi.id,oi.type,oi.description,oi.amount,h.id host_id,p.name product_name,h.name host_name,h.billing_cycle,h.billing_cycle_name,h.status host_status')
            ->leftjoin('host h',"h.id=oi.host_id")
            ->leftjoin('product p',"p.id=oi.product_id")
            ->where('oi.order_id', $id)
            ->select()
            ->toArray();
        foreach ($orderItems as $key => $orderItem) {
            $orderItems[$key]['amount'] = amount_format($orderItem['amount']); // 处理金额格式
            $orderItems[$key]['host_id'] = $orderItem['host_id'] ?? 0; // 处理空数据
            $orderItems[$key]['product_name'] = $orderItem['product_name'] ?? ''; // 处理空数据
            $orderItems[$key]['product_name'] = !empty($orderItems[$key]['product_name']) ? $orderItems[$key]['product_name'] : $orderItem['description'];
            $orderItems[$key]['host_name'] = $orderItem['host_name'] ?? ''; // 处理空数据
            $orderItems[$key]['billing_cycle'] = $orderItem['billing_cycle']!='onetime' ? $orderItem['billing_cycle_name'] : ''; // 处理空数据
            $orderItems[$key]['host_status'] = $orderItem['host_status'] ?? ''; // 处理空数据

            if($orderItem['type']=='addon_idcsmart_promo_code'){
                $orderItems[$key]['product_name'] = $orderItem['description'];
                $orderItems[$key]['host_name'] = '';
            }
            unset($orderItems[$key]['billing_cycle_name'], $orderItems[$key]['type']);
        }

        $order['items'] = $orderItems;

        return $order;
    }

    /**
     * 时间 2022-05-17
     * @title 新建订单
     * @desc 新建订单
     * @author theworld
     * @version v1
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单 required
     * @param array param.products - 商品 类型为新订单时需要
     * @param int param.products[].product_id - 商品ID
     * @param object param.products[].config_options - 自定义配置
     * @param int param.products[].qty - 数量
     * @param int param.host_id - 产品ID 类型为升降级订单时需要
     * @param object param.product - 升降级商品 类型为升降级订单时需要
     * @param int param.product.product_id - 商品ID
     * @param object param.product.config_options - 自定义配置
     * @param float param.amount - 金额 类型为人工订单时需要
     * @param string param.description - 描述 类型为人工订单时需要
     * @param int param.client_id - 用户ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createOrder($param)
    {
        // 验证用户ID
        $client = ClientModel::find($param['client_id']);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }
        // 验证支付方式
        /*if (!check_gateway($gateway)){
            return ['status'=>400, 'msg'=>lang('gateway_is_not_exist')];
        }*/
        if($param['type']=='new'){
            $result = $this->createNewOrder($param);
        }else if($param['type']=='renew'){
            
        }else if($param['type']=='upgrade'){
            $result = $this->createUpgradeOrder($param);
        }else if($param['type']=='upgrade_config'){
            $result = $this->createUpgradeConfigOrder($param);
        }else{
           $this->startTrans();
            try {
                $param['items'][] = [
                    'description' => $param['description'],
                    'amount' => $param['amount'],
                ];
                $id = $this->createOrderBase($param);

                hook('after_order_create',['id'=>$id,'customfield'=>$param['customfield']??[]]);
                # 记录日志
                active_log(lang('admin_create_artificial_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$id]), 'order', $id);
                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('create_fail')];
            }
            $result = ['status' => 200, 'msg' => lang('create_success'), 'data' => ['id' => $id]];
        }
    
        return $result;
    }

    # 新订单
    private function createNewOrder($param)
    {
        $amount = 0;
        $products = $param['products'] ?? [];
        if(empty($products)){
            return ['status'=>400, 'msg'=>lang('please_select_product')];
        }
        $ModuleLogic = new ModuleLogic();
        foreach ($products as $key => $value) {
            $product = ProductModel::find($value['product_id']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            $value['config_options'] = $value['config_options'] ?? [];
            
            $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options']);

            if($result['status']!=200){
                return $result;
            }
            if($product['pay_type']=='free'){
                $result['data']['price'] = 0;
            }
            $result['data']['price'] = isset($value['price']) ? $value['price'] : $result['data']['price'];
            $amount += $result['data']['price']*$value['qty'];
            $products[$key] = $value;
            $products[$key]['price'] = $result['data']['price'];
            $products[$key]['billing_cycle'] = $result['data']['billing_cycle'];
            $products[$key]['duration'] = $result['data']['duration'];
            $products[$key]['description'] = $result['data']['description'];
        }
        $this->startTrans();
        try {
            // 创建订单
            $clientId = $param['client_id'];
            $time = time();
            $order = $this->create([
                'client_id' => $clientId,
                'type' => 'new',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount,
                'gateway' => '',
                'gateway_name' => '',
                'pay_time' => $amount>0 ? 0 : $time ,
                'create_time' => $time
            ]);
            
            // 创建产品
            $ModuleLogic = new ModuleLogic();
            $orderItem = [];
            foreach ($products as $key => $value) {
                $product = ProductModel::find($value['product_id']);
                if($product['stock_control']==1){
                    if($product['qty']<$value['qty']){
                        throw new \Exception(lang('product_inventory_shortage'));
                    }
                    ProductModel::where('id', $value['product_id'])->dec('qty', $value['qty'])->update();
                }
                if($product['type']=='server_group'){
                    $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                    $serverId = $server['id'] ?? 0;
                }else{
                    $serverId = $product['rel_id'];
                }
                for ($i=1; $i<=$value['qty']; $i++) {
                    $host = HostModel::create([
                        'client_id' => $clientId,
                        'order_id' => $order->id,
                        'product_id' => $value['product_id'],
                        'server_id' => $serverId,
                        'name' => generate_host_name(),
                        'status' => 'Unpaid',
                        'first_payment_amount' => $value['price'],
                        'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $value['price'] : 0,
                        'billing_cycle' => $product['pay_type'],
                        'billing_cycle_name' => $value['billing_cycle'],
                        'billing_cycle_time' => $value['duration'],
                        'active_time' => $time,
                        'due_time' => $product['pay_type']!='onetime' ? $time : 0,
                        'create_time' => $time
                    ]);
                    $ModuleLogic->afterSettle($product, $host->id, $value['config_options']);
                    $orderItem[] = [
                        'order_id' => $order->id,
                        'client_id' => $clientId,
                        'host_id' => $host->id,
                        'product_id' => $value['product_id'],
                        'type' => 'host',
                        'rel_id' => $host->id,
                        'amount' => $value['price'],
                        'description' => $value['description'],
                        'create_time' => $time,
                    ];
                }
            }

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            if($amount<=0){
                $this->processPaidOrder($order->id);
            }

            $client = ClientModel::find($clientId);
            # 记录日志
            active_log(lang('admin_create_new_purchase_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);
			add_task([
				'type' => 'email',
				'description' => '订单创建,发送邮件',
				'task_data' => [
					'name'=>'order_create',//发送动作名称
					'order_id'=>$order->id,//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => '订单创建,发送短信',
				'task_data' => [
					'name'=>'order_create',//发送动作名称
					'order_id'=>$order->id,//订单ID
				],		
			]);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id]];
    }

    public function getUpgradeAmount($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }
        $oldProduct = ProductModel::find($host['product_id']);
        $upgradeProductId = ProductUpgradeProductModel::where('product_id', $host['product_id'])->column('upgrade_product_id');
        if(!in_array($param['product']['product_id'], $upgradeProductId)){
            return ['status'=>400, 'msg'=>lang('host_cannot_be_upgraded_to_the_product')];
        }
        $param['product']['product_id'] = $param['product']['product_id'] ?? 0;
        $param['product']['config_options'] = $param['product']['config_options'] ?? [];

        $product = ProductModel::find($param['product']['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        
        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }

        $result['data']['price'] = isset($param['product']['price']) ? $param['product']['price'] : $result['data']['price'];
        $time = time(); // 获取当前时间

        // 计算退款金额
        if($oldProduct['pay_type']=='onetime'){
            $refund = $host['first_payment_amount'];
        }else if($oldProduct['pay_type']=='free'){
            $refund = 0;
        }else{
            if($host['billing_cycle_time']>0){
                if(($host['due_time']-$time)>0){
                    $refund = bcdiv($host['first_payment_amount']/$host['billing_cycle_time']*($host['due_time']-$time), 1, 2);
                }else{
                    $refund = $host['first_payment_amount'];
                }
            }else{
                $refund = $host['first_payment_amount'];
            }
        }

        if($product['pay_type']=='onetime'){
            $pay = $result['data']['price'];
        }else if($product['pay_type']=='free'){
            $pay = 0;
        }else{
            if($result['data']['duration']>0){
                if(($host['due_time']-$time)>0){
                    $pay = bcdiv($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), 1, 2);
                }else{
                    $pay = $result['data']['price'];
                }
            }else{
                $pay = $result['data']['price'];
            }
        }
        $amount = bcsub($pay, $refund, 2);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'refund' => amount_format($refund), 
                'pay' => amount_format($pay), 
                'amount' => amount_format($amount)
            ]
        ];
        return $result;
    }

    # 升降级订单
    public function createUpgradeOrder($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }
        $oldProduct = ProductModel::find($host['product_id']);
        $upgradeProductId = ProductUpgradeProductModel::where('product_id', $host['product_id'])->column('upgrade_product_id');
        if(!in_array($param['product']['product_id'], $upgradeProductId)){
            return ['status'=>400, 'msg'=>lang('host_cannot_be_upgraded_to_the_product')];
        }
        $param['product']['product_id'] = $param['product']['product_id'] ?? 0;
        $param['product']['config_options'] = $param['product']['config_options'] ?? [];

        $product = ProductModel::find($param['product']['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        
        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }

        $result['data']['price'] = isset($param['product']['price']) ? $param['product']['price'] : $result['data']['price'];
        $time = time(); // 获取当前时间
        
        $this->startTrans();
        try {
            $product = ProductModel::find($param['product']['product_id']);
            if($product['stock_control']==1){
                if($product['qty']<1){
                    throw new \Exception(lang('product_inventory_shortage'));
                }
                ProductModel::where('id', $param['product']['product_id'])->dec('qty', 1)->update();
            }

            // 计算退款金额
            if($oldProduct['pay_type']=='onetime'){
                $refund = $host['first_payment_amount'];
            }else if($oldProduct['pay_type']=='free'){
                $refund = 0;
            }else{
                if($host['billing_cycle_time']>0){
                    if(($host['due_time']-$time)>0){
                        $refund = bcdiv($host['first_payment_amount']/$host['billing_cycle_time']*($host['due_time']-$time), 1, 2);
                    }else{
                        $refund = $host['first_payment_amount'];
                    }
                }else{
                    $refund = $host['first_payment_amount'];
                }
                
            }

            /*if($product['pay_type']=='onetime'){
                $amount = bcsub($result['data']['price'], $refund, 2);
            }else if($product['pay_type']=='free'){
                $amount = bcsub(0, $refund, 2);
            }else{
                if($result['data']['duration']>0){
                    if(($host['due_time']-$time)>0){
                        $amount = bcsub($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), $refund, 2);
                    }else{
                        $amount = bcsub($result['data']['price'], $refund, 2);
                    }
                }else{
                    $amount = bcsub($result['data']['price'], $refund, 2);
                }
            }*/
            //计算应付金额
            if($product['pay_type']=='onetime'){
                $pay = $result['data']['price'];
            }else if($product['pay_type']=='free'){
                $pay = 0;
            }else{
                if($result['data']['duration']>0){
                    if(($host['due_time']-$time)>0){
                        $pay = bcdiv($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), 1, 2);
                    }else{
                        $pay = $result['data']['price'];
                    }
                }else{
                    $pay = $result['data']['price'];
                }
            }

            // 计算差价
            $amount = bcsub($pay, $refund, 2);
            
            $param['upgrade_refund'] = $param['upgrade_refund'] ?? 1; // 是否退款,默认退款
            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type' => 'upgrade',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                'gateway' => '',
                'gateway_name' => '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time
            ]);
            // 创建升降级
            $upgrade = UpgradeModel::create([
                'client_id' => $host['client_id'],
                'order_id' => $order->id,
                'host_id' => $host['id'],
                'type' => 'product',
                'rel_id' => $param['product']['product_id'],
                'data' => json_encode($param['product']['config_options']),
                'amount' => $amount,
                'price' => $result['data']['price'],
                'billing_cycle_name' => $result['data']['billing_cycle'],
                'billing_cycle_time' => $result['data']['duration'],
                'status' => $amount>0 ? 'Unpaid' : 'Pending',
                'description' => $result['data']['description'],
                'create_time' => $time,
            ]);
            // 创建订单子项
            OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $param['product']['product_id'],
                'type' => 'upgrade',
                'rel_id' => $upgrade->id,
                'description' => $result['data']['description'],
                'amount' => $amount,
                'gateway' => '',
                'gateway_name' => '',
                'notes' => '',
                'create_time' => $time,
            ]);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            if($amount<=0){
                // 获取接口
                if($product['type']=='server_group'){
                    $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                    $serverId = $server['id'] ?? 0;
                }else{
                    $serverId = $product['rel_id'];
                }

                if($oldProduct['stock_control']==1){
                    ProductModel::where('id', $host['product_id'])->inc('qty', 1)->update();
                }
                HostModel::update([
                    'product_id' => $param['product']['product_id'],
                    'server_id' => $serverId,
                    'first_payment_amount' => $result['data']['price'],
                    'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $result['data']['price'] : 0,
                    'billing_cycle' => $product['pay_type'],
                    'billing_cycle_name' => $result['data']['billing_cycle'],
                    'billing_cycle_time' => $result['data']['duration'],
                ],['id' => $host['id']]);
                $ModuleLogic = new ModuleLogic();
                $host = HostModel::find($host['id']);
                $ModuleLogic->changeProduct($host, $param['product']['config_options']);

                // 退款到余额
                if($amount<0 && $param['upgrade_refund']==1){
                    $result = update_credit([
                        'type' => 'Refund',
                        'amount' => -$amount,
                        'notes' => "Upgrade Refund",
                        'client_id' => $host['client_id'],
                        'order_id' => $order->id,
                        'host_id' => $host['id']
                    ]);
                    if(!$result){
                        throw new Exception(lang('fail_message'));           
                    }
                }
            }

            $client = ClientModel::find($host['client_id']);
            # 记录日志
            active_log(lang('admin_create_upgrade_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id]];


    }

    # 升降级配置订单
    public function createUpgradeConfigOrder($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }

        $param['config_options'] = $param['config_options'] ?? [];

        $time = time(); // 获取当前时间
        
        $this->startTrans();
        try {
            // 金额
            $amount = $param['amount'];
            
            $param['upgrade_refund'] = $param['upgrade_refund'] ?? 1; // 是否退款,默认退款
            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type' => 'upgrade',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                'gateway' => '',
                'gateway_name' => '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time
            ]);
            // 创建升降级
            $upgrade = UpgradeModel::create([
                'client_id' => $host['client_id'],
                'order_id' => $order->id,
                'host_id' => $host['id'],
                'type' => 'config_option',
                'rel_id' => 0,
                'data' => json_encode($param['config_options']),
                'amount' => $amount,
                'price' => $host['first_payment_amount'],
                'billing_cycle_name' => $host['billing_cycle_name'],
                'billing_cycle_time' => $host['billing_cycle_time'],
                'status' => $amount>0 ? 'Unpaid' : 'Pending',
                'description' => $param['description'] ?? '',
                'create_time' => $time,
            ]);
            // 创建订单子项
            OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $host['product_id'],
                'type' => 'upgrade',
                'rel_id' => $upgrade->id,
                'description' => $param['description'] ?? '',
                'amount' => $amount,
                'gateway' => '',
                'gateway_name' => '',
                'notes' => '',
                'create_time' => $time,
            ]);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            if($amount<=0){
                
                $ModuleLogic = new ModuleLogic();
                $host = HostModel::find($host['id']);
                $ModuleLogic->changePackage($host, $param['config_options']);

                // 退款到余额
                if($amount<0 && $param['upgrade_refund']==1){
                    $result = update_credit([
                        'type' => 'Refund',
                        'amount' => -$amount,
                        'notes' => "Upgrade Refund",
                        'client_id' => $host['client_id'],
                        'order_id' => $order->id,
                        'host_id' => $host['id']
                    ]);
                    if(!$result){
                        throw new Exception(lang('fail_message'));           
                    }
                }
            }

            $client = ClientModel::find($host['client_id']);
            # 记录日志
            active_log(lang('admin_create_upgrade_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id]];


    }

    /**
     * 时间 2022-05-24
     * @title 新建订单基础方法
     * @desc 新建订单基础方法,供系统内所有订单创建使用,未使用事务,只有基础的创建
     * @author theworld
     * @version v1
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单recharge充值 required
     * @param float param.amount - 金额 required
     * @param float param.credit - 余额 required
     * @param int param.upgrade_refund - 升降级是否退款0否1是
     * @param string param.status - 状态Unpaid未付款Paid已付款 required
     * @param string param.gateway - 支付方式 required
     * @param int param.client_id - 用户ID required
     * @param array param.items - 订单子项 required
     * @param int param.items[].host_id - 关联产品ID
     * @param string param.items[].type - 关联类型
     * @param int param.items[].rel_id - 关联ID
     * @param string param.items[].description - 描述 有关联的子项不需要描述
     * @param float param.items[].amount - 金额 required
     * @return int
     */
    public function createOrderBase($param)
    {
        // 处理传入数据
        $param['type'] = $param['type'] ?? 'new';
        $param['amount'] = $param['amount'] ?? 0;
        $param['credit'] = $param['credit'] ?? 0;
        $param['status'] = $param['status'] ?? 'Unpaid';
        $param['gateway'] = $param['gateway'] ?? '';
        $param['client_id'] = $param['client_id'] ?? 0;
        $param['items'] = $param['items'] ?? [];
        if($param['status']=='Unpaid'){
            $param['amount_unpaid'] = $param['amount'] - $param['credit'];
        }else{
            $param['amount_unpaid'] = 0;
        }
        $time = time();

        // 获取支付接口名称
        $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();

        // 新建订单
        $order = $this->create([
            'type' => $param['type'],
            'amount' => $param['amount'],
            'credit' => $param['credit'],
            'amount_unpaid' => $param['amount_unpaid'],
            'upgrade_refund' => $param['upgrade_refund'] ?? 1,
            'status' => $param['status'],
            'gateway' => $param['gateway'],
            'gateway_name' => $gateway['title'] ?? '',
            'client_id' => $param['client_id'],
            'pay_time' => $param['status']=='Paid' ? $time : 0,
            'create_time' => $time
        ]);

        // 新建订单子项
        $list = [];
        foreach ($param['items'] as $key => $value) {
            $list[] = [
                'order_id' => $order->id,
                'client_id' => $param['client_id'],
                'host_id' => $value['host_id'] ?? 0,
                'product_id' => $value['product_id'] ?? 0,
                'type' => $value['type'] ?? '',
                'rel_id' => $value['rel_id'] ?? 0,
                'description' => $value['description'] ?? '',
                'amount' => $value['amount'] ?? 0,
                'gateway' => $param['gateway'],
                'gateway_name' => $gateway['title'] ?? '',
                'create_time' => $time
            ];
        }
        // 创建订单子项
        $OrderItemModel = new OrderItemModel();
        $OrderItemModel->saveAll($list);

        // 返回订单ID
        return $order->id;
    }

    /**
     * 时间 2022-05-17
     * @title 调整订单金额
     * @desc 调整订单金额
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param float param.amount - 金额 required
     * @param string param.description - 描述 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAmount($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if($order['status']=='Paid' && $order['type']!='artificial'){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] +=  $param['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $this->startTrans();
        try {
            OrderItemModel::create([
                'order_id' => $param['id'],
                'client_id' => $order['client_id'],
                'description' => $param['description'],
                'amount' => $param['amount'],
                'create_time' => time()
            ]);
            // 修改订单金额
            $this->update(['amount' => $order['amount_unpaid'] + $order['credit'], 'amount_unpaid' => $order['amount_unpaid'], 'update_time' => time()], ['id' => $param['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_price', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order->amount, '{new}'=>($order['amount_unpaid'] + $order['credit'])]), 'order', $order->id);
			add_task([
				'type' => 'email',
				'description' => '后台管理员调整订单价格,发送邮件',
				'task_data' => [
					'name'=>'admin_order_amount',//发送动作名称
					'order_id'=>$param['id'],//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => '后台管理员调整订单价格,发送短信',
				'task_data' => [
					'name'=>'admin_order_amount',//发送动作名称
					'order_id'=>$param['id'],//订单ID
				],		
			]);
			
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 标记支付
     * @desc 标记支付
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param int param.use_credit - 是否使用余额0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderPaid($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        // 已付款的订单不能标记支付
        if($order['status']=='Paid'){
            return ['status'=>400, 'msg'=>lang('order_already_paid')];
        }

        $this->startTrans();
        try {
            if(isset($param['use_credit']) && $param['use_credit']==1){
                $client = ClientModel::find($order['client_id']);

                if($client['credit']>0){
                    if($client['credit']>$order['amount_unpaid']){
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$order['amount_unpaid'],
                            'notes' => "Applied Creidt to Order #{$param['id']}",
                            'client_id' => $order->client_id,
                            'order_id' => $param['id'],
                            'host_id' => 0,
                        ]);
                        $order['amount_unpaid'] = 0;
                    }else{
                        $order['amount_unpaid'] = $order['amount_unpaid']-$client['credit'];
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$client['credit'],
                            'notes' => "Applied Creidt to Order #{$param['id']}",
                            'client_id' => $order->client_id,
                            'order_id' => $param['id'],
                            'host_id' => 0,
                        ]);
                    }
                }
            }
            if($order['amount_unpaid']>0){
                // 创建交易流水
                TransactionModel::create([
                    'order_id' => $order['id'],
                    'amount' => $order['amount_unpaid'],
                    'gateway' => $order['gateway'],
                    'gateway_name' => $order['gateway_name'],
                    'transaction_number' => '',
                    'client_id' => $order['client_id'],
                    'create_time' => time()
                ]);
            }

            $this->update(['status' => 'Paid', 'amount_unpaid'=>0, 'pay_time' => time(), 'update_time' => time()], ['id' => $param['id']]);

            // 处理已支付订单
            $this->processPaidOrder($param['id']);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_mark_user_order_payment_status', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail') . ':' . $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 删除订单
     * @desc 删除订单
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param int delete_host 1 是否删除产品:0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteOrder($param)
    {
        $id = $param['id']??0;

        $delete_host = $param['delete_host']??1;

        // 验证订单ID
        $order = $this->find($id);
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $hosts = HostModel::where('status', 'Pending')->where('order_id', $id)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('hosts_under_activation_in_the_order')];
        }

        $this->startTrans();
        try {
            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_delete_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->destroy($id);
            // 删除订单子项
            OrderItemModel::destroy(function($query) use($id){
                $query->where('order_id', $id);
            });
            if($delete_host==1){
                // 删除订单产品
                HostModel::destroy(function($query) use($id){
                    $query->where('order_id', $id);
                }); 
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        hook('after_order_delete',['id'=>$id]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    # 处理已支付订单
    public function processPaidOrder($id)
    {
        $order = $this->find($id);
        if ($order->status != 'Paid'){
            return false;
        }

        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel->where('order_id',$id)->select();
		if($orderItems[0]['type'] == 'recharge'){
			add_task([
				'type' => 'email',
				'description' => '客户充值成功,发送邮件',
				'task_data' => [
					'name'=>'order_recharge',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => '客户充值成功,发送短信',
				'task_data' => [
					'name'=>'order_recharge',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);
		}else{
			add_task([
				'type' => 'email',
				'description' => '客户支付成功,发送邮件',
				'task_data' => [
					'name'=>'order_pay',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => '客户支付成功,发送短信',
				'task_data' => [
					'name'=>'order_pay',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);			
		}
        foreach($orderItems as $orderItem){
            $type = $orderItem['type'];
			
            switch ($type){
                case 'host':
					
                    $this->hostOrderHandle($orderItem->rel_id);
                    break;
                case 'recharge':
                    $TransactionModel = new TransactionModel();
                    $transaction = $TransactionModel->where('order_id',$id)->find();
                    $transactionNumber = $transaction['transaction_number']??'';
                    update_credit([
                        'type' => 'Recharge',
                        'amount' => $orderItem->amount,
                        'notes' => "Transaction Number #{$transactionNumber} Recharge",
                        'client_id' => $orderItem->client_id,
                        'order_id' => $id,
                        'host_id' => 0
                    ]);

                    $ClientModel = new ClientModel();
                    $client = $ClientModel->find($order['client_id']);
                    active_log(lang('log_client_recharge',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{transaction}'=>$transactionNumber,'{amount}'=>$orderItem->amount]),'client',$client['id']);

                    break;
                case 'upgrade':
                    $this->upgradeOrderHandle($orderItem->rel_id);
                    break;
                # 续费处理逻辑放在续费插件钩子order_paid里,放在这里不伦不类
                /*case 'renew':
                    $this->renewOrderHandle($orderItem->rel_id);
                    break;*/
                default:
                    break;
            }
        }
        # 引入订单支付后钩子
        hook('order_paid',['id'=>$id]);

        return true;
    }

    # 产品订单处理
    private function hostOrderHandle($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.status,h.billing_cycle,h.billing_cycle_time,p.auto_setup,p.creating_notice_sms,p.creating_notice_mail')
            ->leftjoin('product p','h.product_id=p.id')
            ->where('h.id',$id)
            ->find();
        if (empty($host)){
            return false;
        }
        # 修改产品
        $HostModel->update([
            'status' => 'Pending',
            'due_time' => !in_array($host->billing_cycle,['onetime']) ? (time() + intval($host->billing_cycle_time)) : 0,
        ],['id'=>$id]);

        # 暂停时,付款后解除
        if ($host->status == 'Suspended'){
			add_task([
				'type' => 'host_unsuspend',
				'description' => '解除暂停',
				'task_data' => [
					'host_id'=>$id,//主机ID
				],		
			]);
            /*$unsuspend = $HostModel->unsuspendAccount($id);
            if ($unsuspend['status'] == 200){
                # 记录日志
				
                # 加任务队列

            }else{

            }*/
        }

        # 开通
        if($host->auto_setup==1){
            $host_pending = (new NoticeSettingModel())->indexSetting('host_pending');
            if($host_pending['sms_enable']==1){
                add_task([
                    'type' => 'sms',
                    'description' => '产品开通中,发送短信',
                    'task_data' => [
                        'name'=>'host_pending',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]);
            }
            if($host_pending['email_enable']==1){
                add_task([
                    'type' => 'email',
                    'description' => '产品开通中,发送邮件',
                    'task_data' => [
                        'name'=>'host_pending',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]);
            }
			
			add_task([
				'type' => 'host_create',
				'description' => '主机创建',
				'task_data' => [
					'host_id'=>$id,//主机ID
				],		
			]);
            /*$create = $HostModel->createAccount($id);
            if ($create['status'] == 200){
				
            }else{

            }*/
        }
        
        # 发送邮件短信

        return true;
    }

    # 升降级订单处理
    private function upgradeOrderHandle($id)
    {
		
        $upgrade = UpgradeModel::find($id);
        if (empty($upgrade)){
            return false;
        }
        # 修改状态
        UpgradeModel::update([
            'status' => 'Pending',
            'update_time' => time()
        ], ['id' => $id]);

        
		# 添加到定时任务
		add_task([
			'type' => 'host_upgrade',
			'description' => '升降级',
			'task_data' => [
				'upgrade_id'=>$id,//upgrade ID
			],		
		]);
        

        return true;
    }

}
