<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\Exception;
use think\Model;

/**
 * @title 临时订单模型
 * @desc 临时订单模型
 * @use app\common\model\OrderTmpModel
 */
class OrderTmpModel extends Model
{
    protected $name = 'order_tmp';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'order_id'          => 'int',
        'tmp_order_id'      => 'string',
        'tmp_order_id2'     => 'string',
        'tmp_order_id3'     => 'string',
        'create_time'       => 'int',
    ];

    public $isAdmin=false;

    /**
     * 时间 2022-05-24
     * @title 支付
     * @desc 支付
     * @author wyh
     * @version v1
     * @param int id 1 订单ID
     * @param string gateway WxPay 支付方式,支付插件标识
     * @return array
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string code - 当status==200,且code==Paid时,表示支付完成;Unpaid表示部分余额支付,需要将返回的data.html数据渲染出来
     * @return string data.html - 三方接口返回内容
     */
    public function pay($param)
    {
        # 未传值
        if (!isset($param['id']) || empty($param['id'])){
            return ['status'=>400,'msg'=>lang('order_id_is_not_exist')];
        }

        $id = intval($param['id']);
        # 订单不存在
        $OrderModel = new OrderModel();
        $order = $OrderModel->find($id);
        if (empty($order)){
            return ['status'=>400,'msg'=>lang('order_is_not_exist')];
        }
        # 订单归属错误
        if ($order->client_id != get_client_id()){
            return ['status'=>400,'msg'=>lang('order_ownership_error')];
        }
        # 订单已支付
        if ($order->status == 'Paid'){
            return ['status'=>400,'msg'=>lang('order_is_paid')];
        }
        # 验证支付方式
        if (!isset($param['gateway']) || empty($param['gateway'])){
            return ['status'=>400,'msg'=>lang('gateway_is_required')];
        }

        if ($param['gateway']!='credit'){
            if (!check_gateway($param['gateway'])){
                return ['status'=>400,'msg'=>lang('no_support_gateway')];
            }
        }

        # 支付金额为0
        if ($order->amount_unpaid <= 0 || $param['gateway']=='credit'){

            $gateway = $param['gateway'];

            if ($gateway!='credit'){
                $plugin = PluginModel::where('name',$gateway)->where('module','gateway')->find();
                if(!empty($plugin)){
                    $plugin['config'] = json_decode($plugin['config'],true);
                    $plugin['title'] =  (isset($plugin['config']['module_name']) && !empty($plugin['config']['module_name']))?$plugin['config']['module_name']:$plugin['title'];
                }
            }

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($order->client_id);

            # 客户余额足够
            if (($client->credit >= $order->credit && $order->amount_unpaid <= 0) || ($client->credit >= $order->amount && $param['gateway']=='credit')){
                $this->startTrans();

                try{
                    $order->save([
                        'gateway' => $gateway,
                        'gateway_name' => $plugin['title']??'余额支付',
                        'status' => 'Paid',
                        'pay_time' => time()
                    ]);

                    update_credit([
                        'type' => 'Applied',
                        'amount' => -$order->amount,
                        'notes' => "应用余额至订单#{$id}",
                        'client_id' => $order->client_id,
                        'order_id' => $id,
                        'host_id' => 0,
                    ]);

                    # 修改子项支付方式
                    $OrderItemModel = new OrderItemModel();
                    $OrderItemModel->update([
                        'gateway' => $gateway,
                        'gateway_name' => $plugin['title']??'余额支付',
                    ],['order_id'=>$order->id]);

                    $this->commit();
                }catch (\Exception $e){
                    $this->rollback();
                    return ['status'=>400,'msg'=>lang('buy_fail') . ':' . $e->getMessage()];
                }

                $OrderModel->processPaidOrder($id);

                return ['status'=>200,'msg'=>lang('buy_success'),'code'=>'Paid'];
            }else{
                return ['status'=>400,'msg'=>lang('client_credit_is_not_enough')];
            }

        }else{
            $this->startTrans();

            try{
                $orderTmp = $this->create([
                    'order_id' => $id,
                    'tmp_order_id' => idcsmart_tmp_order_id(),
                    'tmp_order_id2' => idcsmart_tmp_order_id(2),
                    'tmp_order_id3' => idcsmart_tmp_order_id(3),
                    'create_time' => time()
                ]);
                # 支付接口修改临时订单ID生成规则
                $class = get_plugin_class($param['gateway'],'gateway');
                $obj = new $class;
                if (isset($obj->orderRule) && $obj->orderRule==2){
                    $tmpId = $orderTmp->tmp_order_id2;
                }elseif (isset($obj->orderRule) && $obj->orderRule==3){
                    $tmpId = $orderTmp->tmp_order_id3;
                }else{
                    $tmpId = $orderTmp->tmp_order_id;
                }

                $this->commit();

                $result = $this->startPay($tmpId,$param['gateway']);

                if (isset($result['status']) && $result['status']==400){
                    throw new Exception($result['msg']?:lang('gateway_error'));
                }

            }catch (\Exception $e){
                $this->rollback();
                return ['status'=>400,'msg'=>lang('gateway_error') . ':' . $e->getMessage()];
            }

            $data = [
                'html' => $result
            ];

            return ['status'=>200,'msg'=>lang('success_message'),'code'=>'Unpaid','data'=>$data];
        }

    }

    # 发起支付
    private function startPay($id,$gateway)
    {
        # 客户基础信息
        $ClientModel = new ClientModel();
        $client = $ClientModel->field('id,username,company,address,country,phone_code,phone,email')->find(get_client_id());
        # 订单产品信息
        if (strlen($id)==18){
            $suffix = 2;
        }elseif (strlen($id) == 10){
            $suffix = 3;
        }else{
            $suffix = '';
        }
        $order = $this->alias('ot')
            ->field('o.id,o.type,o.amount_unpaid as total,ot.tmp_order_id,ot.tmp_order_id2,ot.tmp_order_id3')
            ->leftjoin('order o','ot.order_id=o.id')
            ->where('ot.tmp_order_id'.$suffix,$id)
            ->find();
        $type = $order['type'];
        unset($order['type']);
        # 商品信息
        if ($type == 'recharge'){
            $productName = [
                lang('client_recharge')
            ];
        }else{
            $OrderItemModel = new OrderItemModel();
            $products = $OrderItemModel->alias('oi')
                ->field('p.name')
                ->leftjoin('host h','oi.rel_id=h.id')
                ->leftjoin('product p','h.product_id=p.id')
                ->where('oi.type','host')
                ->where('oi.order_id',$order->id)
                ->select()->toArray();
            $productName = [];
            foreach ($products as $product){
                $productName[] = $product['name'];
            }
        }

        $payData = [
            'out_trade_no' => $id,
            'client' => $client?$client->toArray():[],
            'product' => $productName,
            'global' => configuration(['website_name','website_url']), # 全局信息
            'finance' => $order?$order->toArray():[]
        ];

        return plugin_reflection($gateway,$payData);
    }

    /**
     * 时间 2022-05-24
     * @title 支付状态
     * @desc 支付状态(支付后,轮询调此接口,状态返回400时,停止调用;状态返回200且code==Paid时,停止调用)
     * @author wyh
     * @version v1
     * @param int id 1 订单ID
     * @return array
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string code - Paid表示支付成功,停止调用接口;Upaid表示支付失败,持续调用
     */
    public function status($param)
    {
        # 未传值
        if (!isset($param['id']) || empty($param['id'])){
            return ['status'=>400,'msg'=>lang('order_id_is_not_exist')];
        }

        $id = intval($param['id']);
        # 订单不存在
        $OrderModel = new OrderModel();
        $order = $OrderModel->find($id);
        if (empty($order)){
            return ['status'=>400,'msg'=>lang('order_is_not_exist')];
        }
        # 订单归属错误
        if ($order->client_id != get_client_id()){
            return ['status'=>400,'msg'=>lang('order_ownership_error')];
        }

        if ($order->status == 'Paid'){
            return ['status'=>200,'msg'=>lang('pay_success'),'code'=>'Paid'];
        }else{
            return ['status'=>200,'msg'=>lang('pay_fail'),'code'=>'Unpaid'];
        }
    }

    /**
     * 时间 2022-05-24
     * @title 充值
     * @desc 充值
     * @author wyh
     * @version v1
     * @param int client_id 1 用户ID
     * @param float amount 1.00 金额
     * @param string gateway WxPay 支付方式
     * @return array
     * @return data.id 订单ID,用此ID调支付
     */
    public function recharge($param)
    {
        # 未开启充值功能
        if (!configuration('recharge_open')){
            return ['status'=>400,'msg'=>lang('recharge_is_not_open')];
        }
        # 参数错误
        //if (!isset($param['amount']) || !isset($param['gateway'])){
        if (!isset($param['amount'])){
            return ['status'=>400,'msg'=>lang('param_error')];
        }
        # 金额判断
        $amount = round(floatval($param['amount']),2);
        if ($amount<0.01){
            return ['status'=>400,'msg'=>lang('recharge_amount_is_greater_than_0')];
        }
        // 非后台
        if (!$this->isAdmin){
            $min = floatval(configuration('recharge_min'));
            if ($amount<$min){
                return ['status'=>400,'msg'=>lang('min_recharge_is_error',['{min}'=>amount_format($min)])];
            }

            $max = floatval(configuration('recharge_max'));
            if ($amount>$max){
                return ['status'=>400,'msg'=>lang('max_recharge_is_error',['{max}'=>amount_format($max)])];
            }
        }

        $param['gateway'] = $param['gateway'] ?? '';
        if(empty($param['gateway'])){
            $gateway = gateway_list();
            $param['gateway'] = $gateway['list'][0]['name'] ?? '';
        }
        # 支付接口
        if (!check_gateway($param['gateway'])){
            return ['status'=>400,'msg'=>lang('no_support_gateway')];
        }

        if ($this->isAdmin){
            if (!isset($param['client_id']) || empty($param['client_id'])){
                return ['status'=>400,'msg'=>lang('param_error')];
            }

            $result = hook('get_client_parent_id',['client_id'=>$param['client_id']]);

            foreach ($result as $value){
                if ($value){
                    $param['client_id'] = (int)$value;
                }
            }

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($param['client_id']);
            if (empty($client)){
                return ['status'=>400,'msg'=>lang('client_is_not_exist')];
            }
        }

        $this->startTrans();

        try{
            # 创建订单
            $data = [
                'type' => 'recharge',
                'amount' => $param['amount'],
                'gateway' => $param['gateway'],
                'client_id' => $this->isAdmin?($param['client_id']??0):get_client_id(),
                'items' => [
                    [
                        'amount' => $param['amount'],
                        'description' => lang('client_recharge'),
                        'type' => 'recharge',
                    ]
                ]
            ];
            $OrderModel = new OrderModel();
            $orderId = $OrderModel->createOrderBase($data);

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        # 后台直接标记支付
        if ($this->isAdmin){
            $OrderModel->orderPaid(['id'=>$orderId]);
        }

        return ['status'=>200,'msg'=>lang('recharge_success'),'data'=>['id'=>$orderId]];

    }

    /**
     * 时间 2022-05-28
     * @title 使用(取消)余额
     * @desc 使用(取消)余额
     * @author wyh
     * @version v1
     * @param int id 1 订单ID
     * @param int use 1 1使用余额,0取消使用
     * @return array
     * @return int status - 状态码,200成功,400失败,报错已使用过余额(如果要重新使用,先取消余额,再使用)
     * @return string msg - 提示信息
     * @return int data.id - 订单ID
     */
    public function credit($param)
    {
        # 未传值
        if (!isset($param['id']) || empty($param['id'])){
            return ['status'=>400,'msg'=>lang('order_id_is_not_exist')];
        }
        # 参数错误
        if (!isset($param['use']) || !in_array($param['use'],[0,1])){
            return ['status'=>400,'msg'=>lang('param_error')];
        }
        $id = intval($param['id']);
        # 订单不存在
        $OrderModel = new OrderModel();
        $order = $OrderModel->find($id);
        if (empty($order)){
            return ['status'=>400,'msg'=>lang('order_is_not_exist')];
        }
        # 订单归属错误
        if ($order->client_id != get_client_id()){
            return ['status'=>400,'msg'=>lang('order_ownership_error')];
        }
        # 订单已支付
        if ($order->status == 'Paid'){
            return ['status'=>400,'msg'=>lang('order_is_paid')];
        }
        # 充值订单不可使用余额
        if ($order['type'] == 'recharge'){
            return ['status'=>400,'msg'=>lang('recharge_order_cannot_use_credit')];
        }
        $use = $param['use'];
        # 取消余额
        if (!$use){
            $order->save([
                'credit' => 0,
                'amount_unpaid' => $order->amount
            ]);
            return ['status'=>200,'msg'=>lang('success_message'),'data'=>['id'=>$id]];
        }else{ # 使用余额
            # 客户余额为0
            $ClientModel = new ClientModel();
            $client = $ClientModel->find($order->client_id);
            if ($client->credit <= 0){
                return ['status'=>400,'msg'=>lang('client_credit_is_0')];
            }
            # 已使用过余额(如果要重新使用,先取消余额,再使用)
            if ($order->credit > 0){
                return ['status'=>400,'msg'=>lang('client_credit_is_used')];
            }

            $this->startTrans();

            try{
                if ($client->credit >= $order->amount){
                    $order->save([
                        'credit' => $order->amount,
                        'amount_unpaid' => 0,
                    ]);
                }else{
                    $order->save([
                        'credit' => $client->credit,
                        'amount_unpaid' => bcsub($order->amount,$client->credit,2)
                    ]);
                }

                $this->commit();
            }catch (\Exception $e){
                $this->rollback();
                return ['status'=>400,'msg'=>$e->getMessage()];
            }

            return ['status'=>200,'msg'=>lang('success_message'),'data'=>['id'=>$id]];
        }
    }

    /**
     * 时间 2022-05-24
     * @title 订单支付回调系统处理
     * @desc 订单支付回调系统处理
     * @author wyh
     * @version v1
     * @param string tmp_order_id 1653364762428172693291 临时订单ID
     * @param float amount 1.00 金额
     * @param string trans_id qwery134151786 交易流水ID
     * @param string currency CNY 货币
     * @param string paid_time 2022-05-24 时间
     * @param string gateway AliPay 支付方式
     * @return bool
     */
    public function orderPayHandle($param)
    {
        # 基础参数
        $tmpOrderId = $param['tmp_order_id']??'';
        $amount = $param['amount']??'';
        $transId = $param['trans_id']??'';
        $currency = $param['currency']??'CNY';
        $payTime = isset($param['paid_time'])?strtotime($param['paid_time']):'';
        $gateway = $param['gateway']??'AliPay';

        if (strlen($tmpOrderId)==18){
            $suffix = 2;
        }elseif (strlen($tmpOrderId) == 10){
            $suffix = 3;
        }else{
            $suffix = '';
        }

        $tmpOrder = $this->where('tmp_order_id'.$suffix,$tmpOrderId)->find();
        if (empty($tmpOrder)){
            return false;
        }

        $OrderModel = new OrderModel();
        $order = $OrderModel->find($tmpOrder->order_id);
        if (empty($order)){
            return false;
        }
        # 因为临时订单原因,加此判断会出现扫描多个临时订单二维码时,有的会不入账的问题
        /*if ($order->status == 'Paid'){
            return true;
        }*/
        $status = $order->status;
        # 存在此交易流水
        $TransactionModel = new TransactionModel();
        $transaction = $TransactionModel->where('transaction_number',$transId)->find();
        if (!empty($transaction)){
            return true;
        }
        # 由于payssion只能设置一个后台通知地址，为了区分具体支付方式
        if (strpos($order->gateway,$gateway) !== false){
            $gateway = $order->gateway;
        }
        $plugin = PluginModel::where('name',$gateway)->where('module','gateway')->find();
        if(!empty($plugin)){
            $plugin['config'] = json_decode($plugin['config'],true);
            $plugin['title'] =  (isset($plugin['config']['module_name']) && !empty($plugin['config']['module_name']))?$plugin['config']['module_name']:$plugin['title'];
        }
        # 货币与系统货币不同(不做处理 20220607)
        /*if ($systemCurrency = configuration('currency_code') != $currency){
            $amount = $order->amount_unpaid;
            $currency = $systemCurrency;
        }*/
        $time = time();
        $flag = false;

        # 生成交易流水:防止重复入账(加锁?)
        $transaction = $TransactionModel->create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'amount' => $amount,
            'gateway' => $gateway,
            'gateway_name' => $plugin['title'] ?? '',
            'transaction_number' => $transId,
            'create_time' => $time
        ]);

        if (!empty($transaction)){
            $this->startTrans();
            try{
                $ClientModel = new ClientModel();
                # 加锁,使另外一笔查询等待
                $client = $ClientModel->lock(true)->find($order->client_id);
                # 超付,充值至余额
                if ($amount > $order->amount_unpaid){
                    # 充值进余额
                    update_credit([
                        'type' => 'Overpayment',
                        'amount' => $amount,
                        'notes' => "订单超付,充值至余额#{$order->id}",
                        'client_id' => $order->client_id,
                        'order_id' => $order->id,
                        'host_id' => 0,
                    ]);
                    # 多个临时订单情况
                    if ($status == 'Paid'){
                        $tmpOrder->delete();
                        $this->commit();
                        return true;
                    }
                    # 客户余额足够且订单未支付
                    if (($client->credit >= $order->amount) && $status == 'Unpaid'){
                        # 使用余额
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$order->amount,
                            'notes' => "应用余额至订单#{$order->id}",
                            'client_id' => $order->client_id,
                            'order_id' => $order->id,
                            'host_id' => 0,
                        ]);
                        # 修改订单状态及支付方式(余额支付)
                        $order->save([
                            'credit' => $order->amount,
                            'amount_unpaid' => 0,
                            'status' => 'Paid',
                            'gateway' => $gateway,
                            'gateway_name' => $plugin['title'] ?? '',
                            'pay_time' => $payTime
                        ]);
                        # 修改子项支付方式
                        $OrderItemModel = new OrderItemModel();
                        $OrderItemModel->update([
                            'gateway' => $gateway,
                            'gateway_name' => $plugin['title'] ?? '',
                        ],['order_id'=>$order->id]);

                        $flag = true;
                    }
                }
                elseif($amount < $order->amount_unpaid){ # 少付,充值至余额
                    update_credit([
                        'type' => 'Underpayment',
                        'amount' => $amount,
                        'notes' => "少付,充值至余额#{$order->id}",
                        'client_id' => $order->client_id,
                        'order_id' => $order->id,
                        'host_id' => 0,
                    ]);
                }
                else{ # 正常
                    if ($status == 'Unpaid'){
                        if ($order->credit > 0){ # 部分余额支付
                            if ($client->credit >= $order->credit){ # 客户余额充足
                                # 修改订单
                                $order->save([
                                    'amount_unpaid' => 0,
                                    'status' => 'Paid',
                                    'gateway' => $gateway,
                                    'gateway_name' => $plugin['title'] ?? '',
                                    'pay_time' => $payTime
                                ]);
                                # 修改子项
                                $OrderItemModel = new OrderItemModel();
                                $OrderItemModel->update([
                                    'gateway' => $gateway,
                                    'gateway_name' => $plugin['title'] ?? '',
                                ],['order_id'=>$order->id]);

                                # 记录
                                update_credit([
                                    'type' => 'Applied',
                                    'amount' => -$order->credit,
                                    'notes' => "应用余额至订单#{$order->id}",
                                    'client_id' => $order->client_id,
                                    'order_id' => $order->id,
                                    'host_id' => 0,
                                ]);

                                $flag = true;

                            }else{ # 客户余额不足,充值至余额
                                update_credit([
                                    'type' => 'Underpayment',
                                    'amount' => $amount,
                                    'notes' => "少付,充值至余额#{$order->id}",
                                    'client_id' => $order->client_id,
                                    'order_id' => $order->id,
                                    'host_id' => 0,
                                ]);
                                # 余额不足
                                # active_log(lang('log_client_pay_with_credit_fail',['{client}'=>$client->username]),'client',$client->id);
                            }

                        }else{ # 接口支付
                            # 修改订单状态及支付方式
                            $order->save([
                                'credit' => 0,
                                'amount_unpaid' => 0,
                                'status' => 'Paid',
                                'gateway' => $gateway,
                                'gateway_name' => $plugin['title'] ?? '',
                                'pay_time' => $payTime
                            ]);
                            # 修改子项支付方式
                            $OrderItemModel = new OrderItemModel();
                            $OrderItemModel->update([
                                'gateway' => $gateway,
                                'gateway_name' => $plugin['title'] ?? '',
                            ],['order_id'=>$order->id]);

                            $flag = true;
                        }
                    }
                }

                $this->commit();
            }catch (\Exception $e){
                $transaction->delete();
                $this->rollback();
            }
        }

        # 处理成功支付后逻辑
        if ($flag){
            # 记录日志
            # active_log(lang('log_client_pay_order_success',['{client}'=>$client->username,'{order_id}'=>$order->id,'{amount}'=>amount_format($amount)]),'client',$client->id);
            # 删除订单所有临时数据会存在入账问题,所以这里只删除当前的临时订单
            $tmpOrder->delete();
            # 处理订单类型
            $OrderModel->processPaidOrder($order->id);
        }

        return $flag;
    }

}
