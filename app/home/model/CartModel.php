<?php
namespace app\home\model;

use think\Db;
use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ProductModel;
use app\common\model\OrderModel;
use app\common\model\OrderItemModel;
use app\common\model\HostModel;
use app\common\logic\ModuleLogic;
use app\common\model\ServerModel;

/**
 * @title 购物车模型
 * @desc 购物车模型
 * @use app\home\model\AuthLinkModel
 */
class CartModel extends Model
{
    protected $name = 'cart';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'client_id'     => 'int',
        'data'     		=> 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    private static $cartData = [];

    protected static function init()
    {
        $clientId = get_client_id();
        $cartCookie = cookie("cart_cookie");
        $cartCookieArray = [];
        if(!empty($cartCookie)){
            $cartCookieArray = json_decode($cartCookie, true);
        }

        if(!empty($clientId)) {
            $cart = self::where("client_id", $clientId)->find();
            if(!empty($cart)) {
                $cartData = json_decode($cart['data'], true);
                # cookie中存在产品数据
                if(!empty($cartCookieArray)){
                    # 数据库中存在数据，合并
                    if(!empty($cartData)){
                        foreach($cartCookieArray as $key => $value){
                            $cartData[] = $value; 
                        }
                    }else{
                        # 不存在产品数据，写入
                        $cartData = $cartCookieArray;
                    }
                }

            }else{
                $cartData = [];
                $data = [
                    'client_id' => $clientId,
                    'data' => [],
                    'create_time' => time()
                ];
                # 如果存在cookie数据
                if(!empty($cartCookieArray)) {
                    $cartData = $cartCookieArray;
                }
                $data['data'] = json_encode($cartData);
                self::create($data);
            }
            // 删除cookie
            cookie("cart_cookie", null);
            self::$cartData = $cartData;
            self::saveCart();
        }else{
            # 用户未登录情况
            if(!empty($cartCookieArray)){
                self::$cartData = $cartCookieArray;
            }else{
                self::$cartData = [];
            }
        }
    }

    /**
     * 时间 2022-05-30
     * @title 获取购物车
     * @desc 获取购物车
     * @author theworld
     * @version v1
     * @return  array list - 计算后数据
     * @return  int list[].product_id - 商品ID
     * @return  object list[].config_options - 自定义配置
     * @return  int list[].qty - 数量
     * @return  string list[].name - 商品名称
     * @return  string list[].description - 商品描述
     * @return  int list[].stock_control - 库存控制0:关闭1:启用
     * @return  int list[].stock_qty - 库存数量
     */
    public function indexCart()
    {
        $cartData = [];
        if(!empty(self::$cartData)){
            $cartData = self::$cartData;
            $product = ProductModel::select(array_column($cartData, 'product_id'))->toArray();
            $productName = array_column($product, 'name', 'id');
            $productDesc = array_column($product, 'description', 'id');
            $productStock = array_column($product, 'stock_control', 'id');
            $productQty = array_column($product, 'qty', 'id');
            foreach ($cartData as $key => $value) {
                $cartData[$key]['name'] = $productName[$value['product_id']] ?? '';
                $cartData[$key]['description'] = $productDesc[$value['product_id']] ?? '';
                $cartData[$key]['stock_control'] = $productStock[$value['product_id']] ?? 0;
                $cartData[$key]['stock_qty'] = $productQty[$value['product_id']] ?? 0;
            }
        }
        
        return ['list' => $cartData];
    }

    /**
     * 时间 2022-05-30
     * @title 加入购物车
     * @desc 加入购物车
     * @author theworld
     * @version v1
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置
     * @param  int param.qty - 数量 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createCart($param)
    {
        $product = ProductModel::find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['hidden']==1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['stock_control']==1){
            if($product['qty']<$param['qty']){
                return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
            }
        }

        $param['config_options'] = $param['config_options'] ?? [];
        
        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options'],$param['qty']);

        if($result['status']!=200){
            return $result;
        }

        $data = [
            'product_id' => $param['product_id'],
            'config_options' => $param['config_options'] ?? [],
            'qty' => $param['qty'],
        ];
        //self::$cartData[] = $data;
        array_unshift(self::$cartData, $data);
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('add_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 编辑购物车商品
     * @desc 编辑购物车商品
     * @author theworld
     * @version v1
     * @param  int param.position - 位置 required
     * @param  int param.product_id - 商品ID required
     * @param  object param.config_options - 自定义配置
     * @param  int param.qty - 数量 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateCart($param)
    {
        $product = ProductModel::find($param['product_id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['hidden']==1){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        if($product['stock_control']==1){
            if($product['qty']<$param['qty']){
                return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
            }
        }
        
        $param['config_options'] = $param['config_options'] ?? [];
        
        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->cartCalculatePrice($product, $param['config_options']);

        if($result['status']!=200){
            return $result;
        }

        $position = $param['position'];
        $data = [
            'product_id' => $param['product_id'],
            'config_options' => $param['config_options'] ?? [],
            'qty' => $param['qty'],
        ];
        if(isset(self::$cartData[$position])){
            self::$cartData[$position] = $data;
        }else{
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 修改购物车商品数量
     * @desc 修改购物车商品数量
     * @author theworld
     * @version v1
     * @param  int param.position - 位置 required
     * @param  int param.qty - 数量 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateCartQty($param)
    {
        $position = $param['position'];
        unset($param['position']);
        if(isset(self::$cartData[$position])){
            $product = ProductModel::find(self::$cartData[$position]['product_id']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            if($product['stock_control']==1){
                if($product['qty']<$param['qty']){
                    return ['status'=>400, 'msg'=>lang('product_inventory_shortage')];
                }
            }
            self::$cartData[$position]['qty'] = $param['qty'];
        }else{
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 删除购物车商品
     * @desc 删除购物车商品
     * @author theworld
     * @version v1
     * @param  int position - 位置 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteCart($position)
    {
        if(isset(self::$cartData[$position])){
            unset(self::$cartData[$position]);
        }else{
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 批量删除购物车商品
     * @desc 批量删除购物车商品
     * @author theworld
     * @version v1
     * @param  array positions - 位置 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteCart($positions)
    {
        foreach ($positions as $key => $value) {
            if(isset(self::$cartData[$value])){
                unset(self::$cartData[$value]);
            }else{
                return ['status'=>400, 'msg'=>lang('param_error')];
            }
        }
        
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 清空购物车
     * @desc 清空购物车
     * @author theworld
     * @version v1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function clearCart()
    {
        self::$cartData = [];
        self::saveCart();
        return ['status'=>200, 'msg'=>lang('clear_cart_success')];
    }

    /**
     * 时间 2022-05-31
     * @title 结算购物车
     * @desc 结算购物车
     * @author theworld
     * @version v1
     * @return object data - 数据
     * @return int data.order_id - 订单ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function settle($position,$customfield=[])
    {
        $amount = 0;
        $cartData = [];
        if(empty(self::$cartData)){
            return ['status'=>400, 'msg'=>lang('there_are_no_items_in_the_cart')];
        }
        $ModuleLogic = new ModuleLogic();
        foreach (self::$cartData as $key => $value) {
            if(in_array($key, $position)){
                $product = ProductModel::where('hidden', 0)->find($value['product_id']);
                if(empty($product)){
                    return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
                }
                $value['config_options'] = $value['config_options'] ?? [];
                
                $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options'],$value['qty']);

                if($result['status']!=200){
                    return $result;
                }
                if($product['pay_type']=='free'){
                    $result['data']['price'] = 0;
                }

                $amount += $result['data']['price']*$value['qty'];
                $cartData[$key] = $value;
                $cartData[$key]['price'] = $result['data']['price'];
                $cartData[$key]['renew_price'] = $result['data']['renew_price'] ?? $cartData[$key]['price'];
                $cartData[$key]['billing_cycle'] = $result['data']['billing_cycle'];
                $cartData[$key]['duration'] = $result['data']['duration'];
                $cartData[$key]['description'] = $result['data']['description'];
                unset(self::$cartData[$key]);
            }
        }
        if(empty($cartData)){
            return ['status'=>400, 'msg'=>lang('please_select_products_in_the_cart')];
        }
        $this->startTrans();
        try {
            // 创建订单
            $clientId = get_client_id();
            $time = time();
            $order = OrderModel::create([
                'client_id' => $clientId,
                'type' => 'new',
                'status' => $amount>0 ? 'Unpaid' :'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount,
                'gateway' => '',
                'gateway_name' => '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time
            ]);
            
            // 创建产品
            $orderItem = [];
            $productLog = [];
            foreach ($cartData as $key => $value) {
                $product = ProductModel::find($value['product_id']);
                if($product['stock_control']==1){
                    if($product['qty']<$value['qty']){
                        throw new \Exception(lang('product_inventory_shortage'));
                    }
                    ProductModel::where('id', $value['product_id'])->dec('qty', $value['qty'])->update();
                }
                $productLog[] = 'product#'.$product['id'].'#'.$product['name'].'#';

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
                        'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $value['renew_price'] : 0,
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

            # 记录日志
            active_log(lang('submit_order', ['{client}'=>'client#'.$clientId.'#'.request()->client_name.'#', '{order}'=>$order->id, '{product}'=>implode(',', $productLog)]), 'order', $order->id);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$customfield]);

            self::saveCart();

            $OrderModel = new OrderModel();
            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $OrderModel->where('id',$order->id)->value('amount');

            if($amount<=0){
                $OrderModel->processPaidOrder($order->id);
            }
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['order_id' => $order->id, 'amount' => $amount]];
    }

    # 保存购物车
    private static function saveCart(){
        $clientId = get_client_id();
        self::$cartData = array_values(self::$cartData);
        $cartJson = json_encode(self::$cartData);
        if(!empty($clientId)){
            $data = [
                'data' => $cartJson,
                'update_time' => time(),
            ];
            self::update($data, ['client_id' => $clientId]);
        }else{
            # 未登录保存到本地
            cookie("cart_cookie", $cartJson, 30 * 24 * 3600);
        }
    }


}