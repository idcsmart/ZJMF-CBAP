<?php
namespace app\common\model;

use think\db\Query;
use think\db\Where;
use think\Model;
use app\admin\model\PluginModel;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\admin\model\AdminModel;

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
        'refund_amount'     => 'float',
        'admin_id'          => 'int',
        'base_price'        => 'float',
        'is_lock'           => 'int',
        'recycle_time'      => 'int',
        'will_delete_time'  => 'int',
        'is_recycle'        => 'int',
    ];

	/**
     * 时间 2022-05-17
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:订单ID,商品名称,用户名称,邮箱,手机号
     * @param int param.client_id - 用户ID
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string param.status - 状态Unpaid未付款Paid已付款
     * @param string param.amount - 金额
     * @param string param.gateway - 支付方式
     * @param int param.start_time - 开始时间
     * @param int param.end_time - 结束时间
     * @param int param.order_id - 订单ID
     * @param string param.product_id - 商品ID
     * @param string param.username - 用户名称
     * @param string param.email - 邮箱
     * @param string param.phone - 手机号
     * @param int param.pay_time - 支付时间
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,type,create_time,amount,status
     * @param string param.sort - 升/降序 asc,desc
     * @param  int param.start_recycle_time - 回收开始时间(scene=recycle有效)
     * @param  int param.end_recycle_time - 回收结束时间(scene=recycle有效)
     * @param  string scene - 场景(recycle_bin=回收站)
     * @return array list - 订单
     * @return int list[].id - 订单ID 
     * @return string list[].type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @return int list[].create_time - 创建时间 
     * @return string list[].amount - 金额 
     * @return string list[].status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款 
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
     * @return string list[].description - 描述
     * @return string list[].billing_cycle - 计费周期
     * @return array list[].product_names - 订单下所有产品的商品名称
     * @return int list[].host_id 产品ID
     * @return int list[].order_item_count - 订单子项数量
     * @return int list[].is_lock - 是否锁定(0=否,1=是),scene=recycle_bin返回
     * @return int list[].recycle_time - 放入回收站时间,scene=recycle_bin返回
     * @return int list[].will_delete_time - 彻底删除时间,scene=recycle_bin返回
     * @return int count - 订单总数
     */
    public function orderList($param, $scene = '')
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
        $param['amount'] = $param['amount'] ?? '';
        $param['gateway'] = $param['gateway'] ?? '';
        $param['start_time'] = intval($param['start_time'] ?? 0);
        $param['end_time'] = intval($param['end_time'] ?? 0);
        $param['order_id'] = intval($param['order_id'] ?? 0);
        $param['product_id'] = intval($param['product_id'] ?? 0);
        $param['username'] = $param['username'] ?? '';
        $param['pay_time'] = intval($param['pay_time'] ?? 0);
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'type', 'create_time', 'amount', 'status']) ? 'o.'.$param['orderby'] : 'o.id';

        $where = function (Query $query) use ($param, $app, $scene){
            if($app=='home'){
                $query->where('o.status', '<>', 'Cancelled');
                // 修改为后台显示
                $query->where('o.type', '<>', 'recharge');
                $query->where('o.type', '<>', 'combine');
                $query->where('o.type', '<>', 'credit_limit');
            }
            if(!empty($param['client_id'])){
                $query->where('o.client_id', $param['client_id']);
            }
            if(!empty($param['keywords'])){
                $query->where('o.id|p.name|c.username|c.email|c.phone', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['type'])){
                $query->where('o.type', $param['type']);
            }
            if(!empty($param['status'])){
                $query->where('o.status', $param['status']);
            }
            if(!empty($param['amount'])){
                if(strpos($param['amount'],'.')!==false){
                    $query->where('o.amount', $param['amount']);
                }else{
                    $query->where('o.amount', 'like', "{$param['amount']}.%");
                }
            }
            if(!empty($param['gateway'])){
                if(ucfirst($param['gateway'])=='Credit'){
                    $query->whereRaw("o.credit>0 OR o.gateway='credit'");
                }else{
                    $query->whereRaw('o.amount>o.credit')->where('o.gateway', $param['gateway']);
                }
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('o.create_time', '>=', strtotime(date('Y-m-d', $param['start_time'])))->where('o.create_time', '<=', strtotime(date('Y-m-d 23:59:59', $param['end_time'])));
            }
            if (!empty($param['host_id'])){
                $query->where('oi.host_id',$param['host_id']);
            }
            if($scene == 'recycle_bin'){
                $query->where('o.is_recycle', 1);
                if(isset($param['start_recycle_time']) && $param['start_recycle_time'] !== ''){
                    $query->where('o.recycle_time', '>=', $param['start_recycle_time']);
                }
                if(isset($param['end_recycle_time']) && $param['end_recycle_time'] !== ''){
                    $query->where('o.recycle_time', '<=', $param['end_recycle_time']);
                }
            }else{
                $query->where('o.is_recycle', 0);
            }
            // 右下角搜索
            if(!empty($param['order_id'])){
                $query->where('o.id', $param['order_id']);
            }
            if(!empty($param['product_id'])){
                $query->where('p.id', $param['product_id']);
            }
            if(!empty($param['username'])){
                $query->where('c.username', 'like', "%{$param['username']}%");
            }
            if(!empty($param['email'])){
                $query->where('c.email', 'like', "%{$param['email']}%");
            }
            if(!empty($param['phone'])){
                $query->where('c.phone', 'like', "%{$param['phone']}%");
            }
            if(!empty($param['pay_time'])){
                $query->where('o.pay_time', '>=', strtotime(date('Y-m-d', $param['pay_time'])))->where('o.pay_time', '<=', strtotime(date('Y-m-d 23:59:59', $param['pay_time'])));
            }
            hook('order_list_where_query_append', ['param'=>$param, 'app'=>$app, 'query'=>$query]);
        };
        // wyh 20230510 增加 关联订单 子商品订单或父商品订单
        $whereOr = function (Query $query)use($param){
            if (!empty($param['host_id'])){
                if (class_exists('server\idcsmart_common_dcim\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_dcim\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $dcimOrderIds = array_column($links,'order_id');
                    $dcimHostIds = array_column($links,'host_id');
                    $dcimSonHostIds = array_column($links,'son_host_id');
                }
                if (class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_finance\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $financeOrderIds = array_column($links,'order_id');
                    $financeHostIds = array_column($links,'host_id');
                    $financeSonHostIds = array_column($links,'son_host_id');
                }
                if (class_exists('server\idcsmart_common_cloud\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_cloud\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $cloudOrderIds = array_column($links,'order_id');
                    $cloudHostIds = array_column($links,'host_id');
                    $cloudSonHostIds = array_column($links,'son_host_id');
                }
                if (class_exists('server\idcsmart_common_business\model\IdcsmartCommonSonHost')){
                    $IdcsmartCommonSonHost = new \server\idcsmart_common_business\model\IdcsmartCommonSonHost();
                    $links = $IdcsmartCommonSonHost->where('host_id',$param['host_id'])
                        ->whereOr('son_host_id',$param['host_id'])
                        ->select()->toArray();
                    $businessOrderIds = array_column($links,'order_id');
                    $businessHostIds = array_column($links,'host_id');
                    $businessSonHostIds = array_column($links,'son_host_id');
                }
                // 续费 和 升降级订单
                $hostIds = array_merge($dcimHostIds??[],$dcimSonHostIds??[],$financeHostIds??[],$financeSonHostIds??[],$cloudHostIds??[],$cloudSonHostIds??[],
                    $businessHostIds??[],$businessSonHostIds??[]);
                $otherOrderIds = $this->alias('o')
                    ->leftJoin('order_item oi','o.id=oi.order_id')
                    ->whereIn('oi.host_id',$hostIds)
                    ->whereIn('oi.type',['host','renew','upgrade'])
                    ->column('o.id');
                $orderIds = array_merge($dcimOrderIds??[],$financeOrderIds??[],$cloudOrderIds??[],$businessOrderIds??[],$otherOrderIds??[]);

                if (!empty($orderIds)){
                    $query->whereIn('o.id',$orderIds);
                }
            }
        };

        $count = $this->alias('o')
            ->field('o.id')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->leftjoin('order_item oi',"oi.order_id=o.id")
            ->leftjoin('product p',"p.id=oi.product_id")
            ->where($where)
            ->whereOr($whereOr)
            ->group('o.id')
            ->count();
        $orders = $this->alias('o')
            ->field('o.id,o.type,o.create_time,o.amount,o.status,o.gateway_name gateway,o.credit,o.client_id,c.username client_name,c.credit client_credit,c.email,c.phone_code,c.phone,c.company,o.is_lock,o.recycle_time,o.will_delete_time')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->leftjoin('order_item oi',"oi.order_id=o.id")
            ->leftjoin('product p',"p.id=oi.product_id")
            ->where($where)
            ->whereOr($whereOr)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('o.id')
            ->select()
            ->toArray();

        $orderId = array_column($orders, 'id');

        $orderItems = OrderItemModel::alias('oi')
        	->field('oi.order_id,oi.type,h.id,h.name,h.billing_cycle,h.billing_cycle_name,p.name product_name,oi.description')
        	->leftjoin('host h',"h.id=oi.host_id AND h.is_delete=0")
        	->leftjoin('product p',"p.id=oi.product_id")
            ->withAttr('product_name', function($val) use ($app) {
                if($app == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
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

            // wyh 20230130 有问题就注释
            $description = explode("\n",$orderItem['description']);
            if (!empty($description)){
                $newDes = '';
                foreach ($description as $item1){
                    if (count(explode('=>',$item1))==4){
                        $arr = explode('=>',$item1);
                        $itemDes = $arr[0] . ':' . $arr[1] . $arr[2] . ' '.lang('price').' ' . $arr[3];
                        $newDes = $newDes.$itemDes . "\n";
                    }else{
                        $newDes = $newDes . ' ' . $item1 . "\n";
                    }
                }
                $orderItem['description'] = trim($newDes,"\n");
            }

            $orderItemCount[$orderItem['order_id']] = $orderItemCount[$orderItem['order_id']] ?? 0;
            $orderItemCount[$orderItem['order_id']]++;
            // 获取产品ID
            if(!empty($orderItem['id'])){
                $hostIds[$orderItem['order_id']][] = $orderItem['id'];
            }
            // 获取产品名称
            $names[$orderItem['order_id']][] = $orderItem['name'];
            // 获取产品计费周期
            $billingCycles[$orderItem['order_id']][] = $orderItem['billing_cycle_name'];
            // 获取商品名称
            if(in_array($orderItem['type'], ['addon_promo_code', 'addon_idcsmart_promo_code', 'addon_idcsmart_client_level', 'addon_event_promotion'])){
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
            $orders[$key]['description'] = $descriptions[$order['id']] ?? [];
            if(!empty($orders[$key]['description']) && count($orders[$key]['description'])==1){
                $orders[$key]['description'] = $orders[$key]['description'][0] ?? '';
            }else{
                $orders[$key]['description'] = '';
            }
        	

            // 获取计费周期,计费周期不一致是返回空字符串
            /*$billingCycle = isset($billingCycles[$order['id']]) ? array_values(array_unique($billingCycles[$order['id']])) : [];
            if(!empty($billingCycle) && count($billingCycle)==1){
                $orders[$key]['billing_cycle'] = $billingCycle[0] ?? '';
            }else{
                $orders[$key]['billing_cycle'] = '';
            }*/

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
            if($scene != 'recycle_bin'){
                unset($orders[$key]['is_lock'],$orders[$key]['recycle_time'],$orders[$key]['will_delete_time']);
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
     * @return int pay_time - 支付时间 
     * @return string status - 状态Unpaid未付款Paid已付款Cancelled已取消Refunded已退款
     * @return string gateway - 支付方式 
     * @return string credit - 使用余额,大于0代表订单使用了余额,和金额相同代表订单支付方式为余额 
     * @return int client_id - 用户ID
     * @return string client_name - 用户名称
     * @return string notes - 备注
     * @return string refund_amount - 订单已退款金额
     * @return string amount_unpaid - 未支付金额 
     * @return string refundable_amount - 订单可退款金额
     * @return string apply_credit_amount - 订单可应用余额金额 
     * @return int admin_id - 管理员ID
     * @return string admin_name - 管理员名称
     * @return int is_recycle - 是否在回收站(0=否,1=是)
     * @return array items - 订单子项 
     * @return int items[].id - 订单子项ID 
     * @return string items[].description - 描述
     * @return string items[].amount - 金额 
     * @return int items[].host_id - 产品ID 
     * @return string items[].product_name - 商品名称 
     * @return string items[].host_name - 产品标识 
     * @return string items[].billing_cycle - 计费周期 
     * @return string items[].host_status - 产品状态Unpaid未付款Pending开通中Active使用中Suspended暂停Deleted删除Failed开通失败
     * @return int items[].edit - 是否可编辑1是0否
     * @return string items[].profit - 利润
     * @return int items[].agent - 代理订单1是0否
     */
    public function indexOrder($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $order = $this->field('id,type,amount,create_time,pay_time,status,gateway_name gateway,credit,client_id,notes,refund_amount,amount_unpaid,admin_id,is_recycle')->find($id);
        if (empty($order)){
            return (object)[]; // 转换为对象
        }

        $client = ClientModel::find($order['client_id']);
        $order['client_name'] = $client['username'] ?? '';

        $admin = AdminModel::find($order['admin_id']);
        $order['admin_name'] = $admin['name'] ?? '';

        // 订单的用户ID和前台用户不一致时返回空对象
        if($app=='home'){
            $client_id = get_client_id();
            if($order['client_id']!=$client_id || $order['status']=='Cancelled' || $order['is_recycle'] == 1){
                return (object)[]; // 转换为对象
            }
            unset($order['client_id'], $order['admin_id'], $order['client_name'], $order['admin_name'],$order['is_recycle']);
        }else{
            $amount = TransactionModel::where('order_id', $id)->sum('amount'); // 订单流水金额
            $refundAmount = RefundRecordModel::where('order_id', $id)->where('type', 'credit')->sum('amount'); // 订单已退款金额
            $order['refund_amount'] = amount_format($order['refund_amount']);
            $order['refundable_amount'] = amount_format($amount-$refundAmount); // 订单可退款金额
            $order['refundable_amount'] = $order['refundable_amount']>($order['amount']-$order['refund_amount']) ? amount_format($order['amount']-$order['refund_amount']) : $order['refundable_amount'];
            $order['apply_credit_amount'] = $order['amount']-$order['credit']-$order['refundable_amount']; // 订单可应用余额金额
        }

        $order['amount'] = amount_format($order['amount']); // 处理金额格式
        $order['credit'] = amount_format($order['credit']); // 处理金额格式
        //unset($order['client_id']);

        $orderItems = OrderItemModel::alias('oi')
            ->field('oi.id,oi.type,oi.description,oi.amount,h.id host_id,p.name product_name,h.name host_name,h.billing_cycle,h.billing_cycle_name,h.status host_status,uo.id upstream_order_id,uo.profit')
            ->leftjoin('host h',"h.id=oi.host_id AND h.is_delete=0")
            ->leftjoin('product p',"p.id=oi.product_id")
            ->leftjoin('upstream_order uo',"uo.host_id=oi.host_id AND uo.order_id=oi.order_id")
            ->withAttr('product_name', function($val) use ($app) {
                if($app == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
            ->where('oi.order_id', $id)
            ->select()
            ->toArray();
        foreach ($orderItems as $key => $orderItem) {
            $orderItems[$key]['amount'] = amount_format($orderItem['amount']); // 处理金额格式
            $orderItems[$key]['host_id'] = $orderItem['host_id'] ?? 0; // 处理空数据
            $orderItems[$key]['product_name'] = $orderItem['product_name'] ?? ''; // 处理空数据
            $orderItems[$key]['product_name'] = !empty($orderItems[$key]['product_name']) ? $orderItems[$key]['product_name'] : $orderItem['description'];
            $orderItems[$key]['host_name'] = $orderItem['host_name'] ?? ''; // 处理空数据
            $orderItems[$key]['billing_cycle'] = $orderItem['billing_cycle_name']; // 处理空数据
            $orderItems[$key]['host_status'] = $orderItem['host_status'] ?? ''; // 处理空数据
            $orderItems[$key]['profit'] = amount_format($orderItem['profit']); // 处理金额格式
            $orderItems[$key]['agent'] = !empty($orderItem['upstream_order_id']) ? 1 : 0;

            if(in_array($orderItem['type'], ['addon_promo_code', 'addon_idcsmart_promo_code', 'addon_idcsmart_client_level', 'addon_event_promotion'])){
                $orderItems[$key]['product_name'] = $orderItem['description'];
                $orderItems[$key]['host_name'] = '';
            }

            $description = explode("\n",$orderItem['description']);
            if (!empty($description)){
                $newDes = '';
                foreach ($description as $item1){
                    if (count(explode('=>',$item1))==4){
                        $arr = explode('=>',$item1);
                        $itemDes = $arr[0] . ':' . $arr[1] . $arr[2] .' '.lang('price').' ' . $arr[3];
                        $newDes = $newDes.$itemDes . "\n";
                    }else{
                        $newDes = $newDes . $item1 . "\n";
                    }
                }
                $orderItems[$key]['description'] = trim($newDes,"\n");
            }

            if($app!='home'){
                //$orderItems[$key]['edit'] = $order['status']=='Unpaid' ? ($orderItem['type']=='manual' ? 1 : 0) : 0;
                // wyh 20230412修改 都可更改
                $orderItems[$key]['edit'] = $order['status']=='Unpaid' ? 1 : 0;
            }else{
                unset($orderItems[$key]['profit'], $orderItems[$key]['agent']);
            }
            unset($orderItems[$key]['billing_cycle_name'], $orderItems[$key]['type'], $orderItems[$key]['upstream_order_id']);
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
     * @param string type - 类型new新订单upgrade升降级商品订单upgrade_config升降级配置订单renew续费订单artificial人工订单 required
     * @param array products - 商品 类型为新订单时需要
     * @param int products[].product_id - 商品ID
     * @param object products[].config_options - 自定义配置
     * @param int products[].qty - 数量
     * @param float products[].price - 商品价格
     * @param object products[].customfield - 自定义字段
     * @param int host_id - 产品ID 类型为升降级商品订单时需要
     * @param object product - 升降级商品 类型为升降级商品订单时需要
     * @param int product.product_id - 商品ID
     * @param object product.config_options - 自定义配置
     * @param float product.price - 商品价格
     * @param int upgrade_refund - 是否退款0否1是 类型为升降级商品订单和升降级配置订单时需要
     * @param float price_difference - 产品价格差价 类型为升降级配置订单时需要
     * @param float renew_price_difference - 产品续费价格差价 类型为升降级配置订单时需要
     * @param float base_price - 产品新原价 类型为升降级配置订单时需要
     * @param object config_options - 自定义配置 类型为升降级配置订单时需要
     * @param int id - 产品ID 类型为续费订单时需要
     * @param float amount - 金额 类型为升降级配置订单和人工订单时需要
     * @param string description - 描述 类型为升降级配置订单和人工订单时需要
     * @param int client_id - 用户ID required
     * @param object customfield - 自定义字段
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createOrder($param)
    {
        $result = hook('get_client_parent_id',['client_id'=>$param['client_id']]);

        foreach ($result as $value){
            if ($value){
                $param['client_id'] = (int)$value;
            }
        }

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
            if (class_exists("addon\\idcsmart_renew\\model\\IdcsmartRenewModel")){
                $data = [
                    'id' => $param['id'],
                    'billing_cycle' => $param['customfield']['billing_cycle']??'',
                    'promo_code' => $param['customfield']['promo_code']??[],
                    //'pay' => 1, // 标记支付
                ];
                if (isset($param['customfield']['custom_amount']) && $param['customfield']['custom_amount']>=0){
                    $data['custom_amount'] = $param['customfield']['custom_amount'];
                }
                $IdcsmartRenewModel = new \addon\idcsmart_renew\model\IdcsmartRenewModel();
                $IdcsmartRenewModel->isAdmin = true;
                $result = $IdcsmartRenewModel->renew($data);
            }
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
                return ['status' => 400, 'msg' => $e->getMessage()];
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
        $appendOrderItem = [];
        $ModuleLogic = new ModuleLogic();
        foreach ($products as $key => $value) {
            $product = ProductModel::find($value['product_id']);
            if(empty($product)){
                return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
            }
            $value['config_options'] = $value['config_options'] ?? [];
            
            $upstreamProduct = UpstreamProductModel::where('product_id', $value['product_id'])->find();

            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->cartCalculatePrice($product, $value['config_options']);
            }else{
                $result = $ModuleLogic->cartCalculatePrice($product, $value['config_options']);
            }
            if($result['status']!=200){
                return $result;
            }
            if($product['pay_type']=='free'){
                $result['data']['price'] = 0;
            }
            $appendOrderItem = $result['data']['order_item'] ?? [];
            $result['data']['price'] = isset($value['price']) ? $value['price'] : $result['data']['price'];
            $amount +=  $result['data']['price'] *  $value['qty'];
            $products[$key] = $value;
            $products[$key]['price'] = $result['data']['price'];
            $products[$key]['discount'] = $result['data']['discount'] ?? 0;
            $products[$key]['renew_price'] = $result['data']['renew_price'] ?? $products[$key]['price'];
            $products[$key]['billing_cycle'] = $result['data']['billing_cycle'];
            $products[$key]['duration'] = $result['data']['duration'];
            $products[$key]['description'] = $result['data']['description'];
        }
        $this->startTrans();
        try {
            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

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
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time ,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
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
                        'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $value['renew_price'] : 0,
                        'billing_cycle' => $product['pay_type'],
                        'billing_cycle_name' => $value['billing_cycle'],
                        'billing_cycle_time' => $value['duration'],
                        'active_time' => $time,
                        'due_time' => $product['pay_type']!='onetime' ? $time : 0,
                        'create_time' => $time
                    ]);

                    // 产品和对应自定义字段
                    $param['customfield']['host_customfield'][] = ['id'=>$host->id, 'customfield' => $value['customfield'] ?? []];

                    $upstreamProduct = UpstreamProductModel::where('product_id', $value['product_id'])->find();

                    if($upstreamProduct){
                        $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                        $ResModuleLogic->afterSettle($product, $host->id, $value['config_options']);
                    }else{
                        $ModuleLogic->afterSettle($product, $host->id, $value['config_options']);
                    }
                    $orderItem[] = [
                        'order_id' => $order->id,
                        'client_id' => $clientId,
                        'host_id' => $host->id,
                        'product_id' => $value['product_id'],
                        'type' => 'host',
                        'rel_id' => $host->id,
                        'amount' => bcadd($value['price'], $value['discount']),
                        'description' => $value['description'],
                        'create_time' => $time,
                    ];

                    foreach($appendOrderItem as $v){
                        $v['order_id'] = $order->id;
                        $v['client_id'] = $clientId;
                        $v['host_id'] = $host->id;
                        $v['product_id'] = $value['product_id'];
                        $v['create_time'] = $time;
                        $orderItem[] = $v;
                    }
                }
            }

            // 创建订单子项
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->saveAll($orderItem);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            if($amount<=0){
                $this->processPaidOrder($order->id);
            }

            $client = ClientModel::find($clientId);
            # 记录日志
            active_log(lang('admin_create_new_purchase_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);
			add_task([
				'type' => 'email',
				'description' => lang('order_create_send_mail'),
				'task_data' => [
					'name'=>'order_create',//发送动作名称
					'order_id'=>$order->id,//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => lang('order_create_send_sms'),
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

    /**
     * 时间 2022-07-01
     * @title 获取升降级订单金额
     * @desc 获取升降级订单金额
     * @author theworld
     * @version v1
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
    public function getUpgradeAmount($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
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
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }
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

    # 删除产品未付款升降级订单
    public function deleteHostUnpaidUpgradeOrder($id)
    {
        $OrderModel = new OrderModel();

        $orderIds = $OrderModel->alias('o')
            ->leftJoin('order_item oi','oi.order_id=o.id')
            ->where('oi.host_id',$id)
            ->where('o.type','upgrade')
            ->where('o.status','Unpaid')
            ->group('o.id')
            ->column('o.id');
        if (!empty($orderIds)){
            // 外层必须捕获异常,这个hook会抛出异常
            hook('before_delete_host_unpaid_upgrade_order', ['id'=>$orderIds]);

            $OrderModel->whereIn('id',$orderIds)->delete();
            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->whereIn('order_id',$orderIds)->delete();
            # 删除升降级数据
            $UpgradeModel = new UpgradeModel();
            foreach ($orderIds as $orderId){
                $UpgradeModel->where('order_id',$orderId)
                    ->where('host_id',$id)
                    ->delete();
            }

        }

        return true;
    }

    /**
     * 时间 2024-01-22
     * @title 删除未支付的续费类型订单
     * @desc  删除未支付的续费类型订单
     * @author hh
     * @version v1
     * @throws \Exception 可能抛出异常,需要catch
     * @param   int $id - 产品ID require
     */
    public function deleteUnpaidRenewOrder($id)
    {
        $OrderModel = new OrderModel();

        $unpaidRenewOrders = $OrderModel->alias('o')
            ->field('oi.order_id')
            ->leftJoin('order_item oi','oi.order_id=o.id')
            ->where('oi.type','renew')
            ->where('oi.host_id',$id)
            ->where('o.status','Unpaid')
            ->select()->toArray();
        if (!empty($unpaidRenewOrders)){
            $orderIds = array_column($unpaidRenewOrders,'order_id');

            // 这个hook会抛出异常
            hook('before_delete_unpaid_renew_order', ['id'=>$orderIds]);

            $OrderItemModel = new OrderItemModel();
            $renewIds = $OrderItemModel->whereIn('order_id',$orderIds)
                ->where('type','renew')
                ->column('rel_id');
            $OrderItemModel->whereIn('order_id',$orderIds)->delete();
            $OrderModel->whereIn('id',$orderIds)->delete();
            $this->whereIn('id',$renewIds)->delete();
            $UpstreamOrderModel = new UpstreamOrderModel();
            $UpstreamOrderModel->whereIn('order_id',$orderIds)->delete();
        }
    }


    # 升降级订单
    public function createUpgradeOrder($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
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
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $product['id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->cartCalculatePrice($product, $param['product']['config_options']);
        }

        if($result['status']!=200){
            return $result;
        }
        if($product['pay_type']=='free'){
            $result['data']['price'] = 0;
        }

        $result['data']['price'] = isset($param['product']['price']) ? $param['product']['price'] : $result['data']['price'];
        $result['data']['renew_price'] = isset($param['product']['price']) ? $param['product']['price'] : ($result['data']['renew_price'] ?? $result['data']['price']);
        $time = time(); // 获取当前时间
        
        $this->startTrans();
        try {

            // wyh 20230508 删除未支付升降级订单
            $this->deleteHostUnpaidUpgradeOrder($hostId);

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
                        //$refund = bcdiv($host['first_payment_amount']/$host['billing_cycle_time']*($host['due_time']-$time), 1, 2);
                        $hookResult = hook_one('renew_host_refund_amount',['id'=>$hostId]);
                        $renewRefundTotal = $hookResult[0]??0; // 总续费退款
                        $renewCycleTotal = $hookResult[1]??0; // 总续费周期
                        if (isset($hookResult[2]) && $hookResult[2]){
                            $refund = $renewRefundTotal;
                        }else{
                            $hostBillingCycleTime = $host['due_time']-$renewCycleTotal-$host['active_time']; // 产品购买周期=(总到期时间-续费周期-开通时间)
                            $refund = bcdiv(bcdiv($host['first_payment_amount'],$hostBillingCycleTime,20)*($host['due_time']-$renewCycleTotal-$time), 1, 2);
                            $refund = bcadd($refund,$renewRefundTotal,2);
                        }
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
                        $pay = $result['data']['price'];//bcdiv($result['data']['price']/$result['data']['duration']*($host['due_time']-$time), 1, 2);
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

            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type' => 'upgrade',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
            ]);
            // 20231225 新增
            if (isset($result['data']['base_price_son'])){
                $pay = $result['data']['base_price'];
                $param['product']['config_options']['base_price_son'] = $result['data']['base_price_son'];
            }
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
                'renew_price' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $result['data']['renew_price'] : 0,
                'billing_cycle_name' => $result['data']['billing_cycle'],
                'billing_cycle_time' => $result['data']['duration'],
                'status' => $amount>0 ? 'Unpaid' : 'Pending',
                'description' => $result['data']['description'],
                'create_time' => $time,
                'base_price' => $pay
            ]);

            if (isset($result['data']['description']) && is_array($result['data']['description'])){
                $result['data']['description'] = implode("\n",$result['data']['description']);
            }
            // 创建订单子项
            $orderItem = OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $param['product']['product_id'],
                'type' => 'upgrade',
                'rel_id' => $upgrade->id,
                'description' => $result['data']['description'],
                'amount' => $amount,
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'notes' => '',
                'create_time' => $time,
            ]);

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            // wyh 20230531 升降级统一处理续费金额：续费差价 = 升降级金额 - (优惠码*升降级金额) - （客户等级*升降级金额）
            // 续费差价<0
            //$renewPriceDifference = bcsub($param['amount'],)
            $baseRenewPrice = $result['data']['renew_price'];
            $hookDiscountResults = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$param['product']['product_id'],'amount'=>$baseRenewPrice]);
            foreach ($hookDiscountResults as $hookDiscountResult){
                if ($hookDiscountResult['status']==200){
                    $renewPrice = $baseRenewPrice-($hookDiscountResult['data']['discount']??0);
                }
            }
            $hookPromoCodeResults = hook('apply_promo_code',['host_id'=>$host->id,'product_id'=>$param['product']['product_id'],'price'=>$baseRenewPrice,'scene'=>'upgrade','duration'=>$host['billing_cycle_time']]);
            foreach ($hookPromoCodeResults as $hookPromoCodeResult){
                if ($hookPromoCodeResult['status']==200){
                    $renewPrice = ($renewPrice??0)-($hookPromoCodeResult['data']['discount']??0);
                }
            }
            $renewPrice = (isset($renewPrice) && $renewPrice>0)?$renewPrice:0;
            $upgrade->save([
                'renew_price' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment')?$renewPrice:0
            ]);

            $product = (new ProductModel())->find($host['product_id']);
            if (in_array($host['billing_cycle'],['onetime','free'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$host['due_time']);
            }

            //$des = $product['name'] . '(' .$host['name']. '),'.lang('purchase_duration').':'.$host['billing_cycle_name'] .'(' . date('Y/m/d',$host['active_time']) . '-'. $desDueTime .')';
            //$des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
            $des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',time()) . '-' . $desDueTime]);
            $newOrderItem = OrderItemModel::find($orderItem['id']);
            $newOrderItem->save([
                'description' => $newOrderItem['description'] . "\n" . $des
            ]);
            
            if($amount<=0){
                // 获取接口
                /*if($product['type']=='server_group'){
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
                    'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $result['data']['renew_price'] : 0,
                    'billing_cycle' => $product['pay_type'],
                    'billing_cycle_name' => $result['data']['billing_cycle'],
                    'billing_cycle_time' => $result['data']['duration'],
                ],['id' => $host['id']]);
                $ModuleLogic = new ModuleLogic();
                $host = HostModel::find($host['id']);
                $ModuleLogic->changeProduct($host, $param['product']['config_options']);*/

                // 退款到余额
                if($amount<0 && $param['upgrade_refund']==1){
                    $result = update_credit([
                        'type' => 'Refund',
                        'amount' => -$amount,
                        'notes' => lang('upgrade_refund'),
                        'client_id' => $host['client_id'],
                        'order_id' => $order->id,
                        'host_id' => $host['id']
                    ]);
                    if(!$result){
                        throw new \Exception(lang('fail_message'));           
                    }
                }else if($amount<0 && $param['upgrade_refund']!=1){
                    OrderItemModel::create([
                        'type' => 'manual',
                        'order_id' => $order->id,
                        'client_id' => $host['client_id'],
                        'description' => lang('update_amount'),
                        'amount' => -$amount,
                        'create_time' => $time
                    ]);
                    $this->update([
                        'amount' => 0,
                    ], ['id' => $order->id]);

                }

                $this->processPaidOrder($order->id);
            }

            $client = ClientModel::find($host['client_id']);
            # 记录日志
            active_log(lang('admin_create_upgrade_order', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$client->username.'#', '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage() ];
        }

        return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['id' => $order->id]];


    }

    # 升降级配置订单
    public function createUpgradeConfigOrder($param)
    {
        $hostId = $param['host_id'] ?? 0;
        $host = HostModel::find($hostId);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['client_id']!=$param['client_id']){
            return ['status'=>400, 'msg'=>lang('client_host_error')];
        }
        if($host['status']!='Active'){
            return ['status'=>400, 'msg'=>lang('active_host_can_be_upgraded')];
        }

        $param['config_options'] = $param['config_options'] ?? [];
        $param['renew_price_difference'] = $param['renew_price_difference'] ?? $param['price_difference'];

        $time = time(); // 获取当前时间
        $appendOrderItem = $param['order_item'] ?? [];

        $this->startTrans();
        try {
            // wyh 20230508 删除未支付升降级订单
            $this->deleteHostUnpaidUpgradeOrder($hostId);

            // 金额
            $amount = $param['amount'];
            $discount = $param['discount'] ?? 0; // 用户等级折扣 
            
            $param['upgrade_refund'] = $param['upgrade_refund'] ?? 1; // 是否退款,默认退款

            /*$gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];*/

            // 创建订单
            $order = $this->create([
                'client_id' => $host['client_id'],
                'type' => 'upgrade',
                'status' => $amount>0 ? 'Unpaid' : 'Paid',
                'amount' => $amount,
                'credit' => 0,
                'amount_unpaid' => $amount>0 ? $amount : 0,
                'upgrade_refund' => $param['upgrade_refund'],
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'pay_time' => $amount>0 ? 0 : $time,
                'create_time' => $time,
                'admin_id' => get_admin_id(),
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
                'price' => ($host['first_payment_amount']+$param['price_difference'])>0 ? ($host['first_payment_amount']+$param['price_difference']) : 0,
                // 'renew_price' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? (($host['renew_amount']+$param['renew_price_difference'])>0 ? ($host['renew_amount']+$param['renew_price_difference']) : 0) : 0,
                'renew_price' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? (bcadd($host['renew_amount'], $param['renew_price_difference'], 2)>0 ? bcadd($host['renew_amount'], $param['renew_price_difference'], 2) : 0) : 0,
                'billing_cycle_name' => $host['billing_cycle_name'],
                'billing_cycle_time' => $host['billing_cycle_time'],
                'status' => $amount>0 ? 'Unpaid' : 'Pending',
                'description' => $param['description'] ?? '',
                'create_time' => $time,
                'base_price' => $param['base_price']??0
            ]);

            // 创建订单子项
            if (isset($param['description']) && is_array($param['description'])){
                $param['description'] = implode("\n",$param['description']);
            }
            $orderItem = OrderItemModel::create([
                'order_id' => $order->id,
                'client_id' => $host['client_id'],
                'host_id' => $host['id'],
                'product_id' => $host['product_id'],
                'type' => 'upgrade',
                'rel_id' => $upgrade->id,
                'description' => ($param['description'] ?? ''),
                'amount' => bcadd($amount, $discount, 2),
                //'gateway' => $gateway['name'] ?? '',
                //'gateway_name' => $gateway['title'] ?? '',
                'notes' => '',
                'create_time' => $time,
            ]);

            if(!empty($appendOrderItem)){
                foreach($appendOrderItem as $k=>$v){
                    $appendOrderItem[$k]['order_id'] = $order->id;
                    $appendOrderItem[$k]['client_id'] = $host['client_id'];
                    $appendOrderItem[$k]['host_id'] = $host['id'];
                    $appendOrderItem[$k]['product_id'] = $host['product_id'];
                    $appendOrderItem[$k]['create_time'] = $time;
                }
                $OrderItemModel = new OrderItemModel();
                $OrderItemModel->saveAll($appendOrderItem);
            }

            hook('after_order_create',['id'=>$order->id,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($order->id);

            # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
            $amount = $this->where('id',$order->id)->value('amount');

            $discountPromo = 0;
            $hookPromoCodeResults = hook('apply_promo_code',['host_id'=>$host->id,'price'=>$param['renew_price_difference'],'scene'=>'upgrade','duration'=>$host['billing_cycle_time']]);
            foreach ($hookPromoCodeResults as $hookPromoCodeResult){
                if ($hookPromoCodeResult['status']==200){
                    if (isset($hookPromoCodeResult['data']['loop']) && $hookPromoCodeResult['data']['loop']){
                        $discountPromo = $hookPromoCodeResult['data']['discount']??0;
                    }
                }
            }
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            // 降级
            if ($param['renew_price_difference']<0){ // $discountPromo应该是负数
                // wyh 20231025 获取产品周期原价
                $amountBase = $amountBase1= 0;
                $ModuleLogic = new ModuleLogic();
                if (!empty($upstreamProduct)){
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $durationResult = $ResModuleLogic->durationPrice($host);
                }else{
                    $durationResult = $ModuleLogic->durationPrice($host);
                }

                $cycles = $durationResult['data']?:[];
                foreach ($cycles as $item2){
                    $flag = $host->billing_cycle_time == $item2['duration'] || $host->billing_cycle_name==$item2['billing_cycle'];
                    if ($flag){
                        $amountBase = $amountBase1 = $item2['price'];
                        break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
                    }
                }

                $hookPromoCodeResultsOrgins = hook('apply_promo_code',['host_id'=>$host->id,'price'=>$amountBase1,'scene'=>'upgrade','duration'=>$host['billing_cycle_time']]);
                foreach ($hookPromoCodeResultsOrgins as $hookPromoCodeResultsOrgin){
                    if ($hookPromoCodeResultsOrgin['status']==200){
                        if (isset($hookPromoCodeResultsOrgin['data']['loop']) && $hookPromoCodeResultsOrgin['data']['loop']){
                            $amountBase = $amountBase - ($hookPromoCodeResultsOrgin['data']['discount']??0);
                        }
                    }
                }

                $hookDiscountResultsOrgins = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$host['product_id'],'amount'=>$amountBase1,'id'=>$order->id]);
                foreach ($hookDiscountResultsOrgins as $hookDiscountResultsOrgin){
                    if ($hookDiscountResultsOrgin['status']==200){
                        $amountBase = $amountBase - ($hookDiscountResultsOrgin['data']['discount']??0);
                    }
                }
                $renewPrice = $amountBase + $param['renew_price_difference']-$discountPromo;
                // 若有优惠码，折扣金额这样计算！
            }else{
                // 升级
                $renewPrice = $host['renew_amount'] + $param['renew_price_difference'] - $discountPromo;
            }

            if(empty($appendOrderItem)){
                $hookDiscountResults = hook("client_discount_by_amount",['client_id'=>$host['client_id'],'product_id'=>$host['product_id'],'amount'=>$param['renew_price_difference'], 'id'=>$order->id ]);
                foreach ($hookDiscountResults as $hookDiscountResult){
                    if ($hookDiscountResult['status']==200){
                        $discountClient = $hookDiscountResult['data']['discount']??0;
                        $renewPrice = $renewPrice-$discountClient;
                    }
                }
            }

            $updateData = [];

            // 一次性时，实际首付金额
            if ($host['billing_cycle']=='onetime'){
                $updateData['price'] = $host['first_payment_amount']+$renewPrice;
            }

            $renewPrice = $renewPrice>0?$renewPrice:0;

            $updateData['renew_price'] = ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment')?$renewPrice:0;

            $upgrade->save($updateData);

            $product = (new ProductModel())->find($host['product_id']);
            if (in_array($host['billing_cycle'],['onetime','free'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$host['due_time']);
            }

            //$des = $product['name'] . '(' .$host['name']. '),'.lang('purchase_duration').':'.$host['billing_cycle_name'] .'(' . date('Y/m/d',$host['active_time']) . '-'. $desDueTime .')';
            //$des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',$host['active_time']) . '-' . $desDueTime]);
            $des = lang('order_description_append',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'],'{time}'=>date('Y/m/d',time()) . '-' . $desDueTime]);
            $newOrderItem = OrderItemModel::find($orderItem['id']);
            $newOrderItem->save([
                'description' => $newOrderItem['description'] . "\n" . $des
            ]);

            if($amount<=0){
                $this->processPaidOrder($order->id);
                
                /*HostModel::update([
                    'first_payment_amount' => $upgrade['price'],
                    'renew_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? (($host['renew_amount']+$param['renew_price_difference'])>0 ? ($host['renew_amount']+$param['renew_price_difference']) : 0) : 0,
                ],['id' => $host['id']]);

                $ModuleLogic = new ModuleLogic();
                $host = HostModel::find($host['id']);
                $ModuleLogic->changePackage($host, $param['config_options']);*/

                // 退款到余额
                if($amount<0 && $param['upgrade_refund']==1){
                    $result = update_credit([
                        'type' => 'Refund',
                        'amount' => -$amount,
                        'notes' => lang('upgrade_refund'),
                        'client_id' => $host['client_id'],
                        'order_id' => $order->id,
                        'host_id' => $host['id']
                    ]);
                    if(!$result){
                        throw new \Exception(lang('fail_message'));           
                    }
                }else if($amount<0 && $param['upgrade_refund']!=1){
                    OrderItemModel::create([
                        'type' => 'manual',
                        'order_id' => $order->id,
                        'client_id' => $host['client_id'],
                        'description' => lang('update_amount'),
                        'amount' => -$amount,
                        'create_time' => $time
                    ]);
                    $this->update([
                        'amount' => 0,
                    ], ['id' => $order->id]);
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
        $param['status'] = $param['amount']>0 ? ($param['status'] ?? 'Unpaid') : 'Paid';
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
        if(!empty($gateway)){
            $gateway['config'] = json_decode($gateway['config'],true);
            $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];
        }/*else{
            $gateway = gateway_list();
            $gateway = $gateway['list'][0]??[];
        }*/


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
            'create_time' => $time,
            'admin_id' => get_admin_id(),
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
        if (empty($order) || $order['is_recycle'] == 1){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        //if(in_array($order['status'], ['Paid', 'Refunded']) && $order['type']!='artificial'){
        if(in_array($order['status'], ['Paid', 'Refunded'])){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] +=  $param['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $hookRes = hook('before_update_amount',['id'=>$param['id']]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            OrderItemModel::create([
                'type' => 'manual',
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
				'description' => lang('admin_order_amount_send_mail'),
				'task_data' => [
					'name'=>'admin_order_amount',//发送动作名称
					'order_id'=>$param['id'],//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => lang('admin_order_amount_send_sms'),
				'task_data' => [
					'name'=>'admin_order_amount',//发送动作名称
					'order_id'=>$param['id'],//订单ID
				],		
			]);

            update_upstream_order_profit($order->id);
			
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        hook('after_update_order_amount',['id'=>$param['id'],'amount'=>$param['amount'],'description'=>$param['description']]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-10-11
     * @title 编辑人工调整的订单子项
     * @desc 编辑人工调整的订单子项
     * @author theworld
     * @version v1
     * @param int param.id - 订单子项ID required
     * @param float param.amount - 金额 required
     * @param string param.description - 描述 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateOrderItem($param)
    {
        // 验证订单ID
        $orderItem = OrderItemModel::find($param['id']);
        if (empty($orderItem)){
            return ['status'=>400, 'msg'=>lang('order_item_is_not_exist')];
        }

        // wyh 20230412 注释 所有类型都可以修改
        /*if ($orderItem['type']!='manual'){
            return ['status'=>400, 'msg'=>lang('order_item_cannot_update')];
        }*/

        // 验证订单ID
        $order = $this->find($orderItem['order_id']);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if (in_array($order['status'], ['Paid', 'Refunded'])){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] = $order['amount_unpaid'] - $orderItem['amount'] + $param['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $this->startTrans();
        try {
            OrderItemModel::update([
                //'type' => 'manual',
                'description' => $param['description'],
                'amount' => $param['amount'],
                'create_time' => time()
            ], ['id' => $param['id']]);
            // 修改订单金额
            $this->update(['amount' => $order['amount_unpaid'] + $order['credit'], 'amount_unpaid' => $order['amount_unpaid'], 'update_time' => time()], ['id' => $order['id']]);

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
                'description' => lang('admin_order_amount_send_mail'),
                'task_data' => [
                    'name'=>'admin_order_amount',//发送动作名称
                    'order_id'=>$order['id'],//订单ID
                ],      
            ]);
            add_task([
                'type' => 'sms',
                'description' => lang('admin_order_amount_send_sms'),
                'task_data' => [
                    'name'=>'admin_order_amount',//发送动作名称
                    'order_id'=>$order['id'],//订单ID
                ],      
            ]);

            update_upstream_order_profit($order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2023-01-30
     * @title 删除人工调整的订单子项
     * @desc 删除人工调整的订单子项
     * @author theworld
     * @version v1
     * @param int id - 订单子项ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteOrderItem($id)
    {
        // 验证订单ID
        $orderItem = OrderItemModel::find($id);
        if (empty($orderItem)){
            return ['status'=>400, 'msg'=>lang('order_item_is_not_exist')];
        }

        if ($orderItem['type']!='manual'){
            return ['status'=>400, 'msg'=>lang('order_item_cannot_delete')];
        }

        // 验证订单ID
        $order = $this->find($orderItem['order_id']);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if (in_array($order['status'], ['Paid', 'Refunded'])){
            return ['status'=>400, 'msg'=>lang('order_already_paid_cannot_adjustment_amount')];
        }

        // 调整后的订单金额不能小于0
        $order['amount_unpaid'] = $order['amount_unpaid'] - $orderItem['amount'];
        if($order['amount_unpaid']<0){
            return ['status'=>400, 'msg'=>lang('order_amount_adjustment_failed')];
        }

        $this->startTrans();
        try {
            OrderItemModel::destroy($id);
            // 修改订单金额
            $this->update(['amount' => $order['amount_unpaid'] + $order['credit'], 'amount_unpaid' => $order['amount_unpaid'], 'update_time' => time()], ['id' => $order['id']]);

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
                'description' => lang('admin_order_amount_send_mail'),
                'task_data' => [
                    'name'=>'admin_order_amount',//发送动作名称
                    'order_id'=>$order['id'],//订单ID
                ],      
            ]);
            add_task([
                'type' => 'sms',
                'description' => lang('admin_order_amount_send_sms'),
                'task_data' => [
                    'name'=>'admin_order_amount',//发送动作名称
                    'order_id'=>$order['id'],//订单ID
                ],      
            ]);

            update_upstream_order_profit($order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 标记支付
     * @desc 标记支付
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param string transaction_number - 交易流水号
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderPaid($param)
    {
        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($param['id']);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }

            // 已付款的订单不能标记支付
            if(in_array($order['status'], ['Paid', 'Refunded'])){
                throw new \Exception(lang('order_already_paid'));
            }

            /*if(isset($param['use_credit']) && $param['use_credit']==1){
                $client = ClientModel::find($order['client_id']);

                if($client['credit']>0){
                    if($client['credit']>$order['amount_unpaid']){
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$order['amount_unpaid'],
                            'notes' => lang('order_apply_credit')."#{$param['id']}",
                            'client_id' => $order->client_id,
                            'order_id' => $param['id'],
                            'host_id' => 0,
                        ]);
                        $order['amount_unpaid'] = 0;
                        $order['credit'] = $order['credit']+$order['amount_unpaid'];
                    }else{
                        $order['amount_unpaid'] = $order['amount_unpaid']-$client['credit'];
                        update_credit([
                            'type' => 'Applied',
                            'amount' => -$client['credit'],
                            'notes' => lang('order_apply_credit')."#{$param['id']}",
                            'client_id' => $order->client_id,
                            'order_id' => $param['id'],
                            'host_id' => 0,
                        ]);
                        $order['credit'] = $order['credit']+$client['credit'];
                    }
                }
            }*/
            if($order['amount_unpaid']>0){
                // 创建交易流水
                TransactionModel::create([
                    'order_id' => $order['id'],
                    'amount' => $order['amount_unpaid'],
                    'gateway' => $order['gateway'],
                    'gateway_name' => $order['gateway_name'],
                    'transaction_number' => $param['transaction_number'] ?? '',
                    'client_id' => $order['client_id'],
                    'create_time' => time()
                ]);
            }
            if($order['credit']>0){
                $res = update_credit([
                    'type' => 'Applied',
                    'amount' => -$order['credit'],
                    'notes' => lang('order_apply_credit')."#{$param['id']}",
                    'client_id' => $order->client_id,
                    'order_id' => $param['id'],
                    'host_id' => 0,
                ]);
                if(!$res){
                    throw new \Exception(lang('insufficient_credit_deduction_failed'));
                }
            }

            $this->update(['status' => 'Paid', 'credit' => $order['credit'], 'amount_unpaid'=>0, 'pay_time' => time(), 'update_time' => time()], ['id' => $param['id']]);

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
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $hosts = HostModel::where('status', 'Pending')->where('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('hosts_under_activation_in_the_order')];
        }

        // 订单回收站
        $recycleBin = configuration('order_recycle_bin');
        if(!empty($recycleBin)){
            return $this->recycleOrder([
                'id'            => [$id],
                'delete_host'   => $delete_host,
            ]);
        }

        $hookRes = hook('before_order_delete',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
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
            // 删除上游订单
            UpstreamOrderModel::destroy(function($query) use($id){
                $query->where('order_id', $id);
            });
            if($delete_host==1){
                // 删除订单产品
                HostModel::destroy(function($query) use($id){
                    $query->where('status', '<>', 'Active')->where('order_id', $id);
                }); 
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        hook('after_order_delete',['id'=>$id, 'order'=>$order]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 批量删除订单
     * @desc 批量删除订单
     * @author theworld
     * @version v1
     * @param array id - 订单ID required
     * @param int delete_host 1 是否删除产品:0否1是 required
     * @param  string type - 类型(recycle_bin=从回收站删除,clear_recycle_bin=清空回收站)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteOrder($param, $type = '')
    {
        $id = $param['id']??[];

        $delete_host = $param['delete_host']??1;

        // 验证订单ID
        if($type == 'clear_recycle_bin'){
            $order = $this->where('is_recycle', 1)->where('is_lock', 0)->select()->toArray();
            $id = array_column($order, 'id');
            if(empty($id)){
                return ['status'=>200, 'msg'=>lang('delete_success') ];
            }
        }else if($type == 'recycle_bin'){
            $order = $this->whereIn('id', $id)->where('is_recycle', 1)->where('is_lock', 0)->select()->toArray();
            $id = array_column($order, 'id');
            if(empty($id)){
                return ['status'=>400, 'msg'=>lang('order_locked_or_not_found')];
            }
        }else{
            $order = $this->whereIn('id', $id)->select()->toArray();
        }
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        if(count($order)!=count($id)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $hosts = HostModel::where('status', 'Pending')->whereIn('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('hosts_under_activation_in_the_order')];
        }

        if($type != 'clear_recycle_bin' && $type != 'recycle_bin'){
            // 订单回收站
            $recycleBin = configuration('order_recycle_bin');
            if(!empty($recycleBin)){
                return $this->recycleOrder($param);
            }
        }

        foreach ($id as $value) {
            $hookRes = hook('before_order_delete',['id'=>$value]);
            foreach($hookRes as $v){
                if(isset($v['status']) && $v['status'] == 400){
                    return $v;
                }
            }
        }
        
        $this->startTrans();
        try {
            foreach ($order as $key => $value) {
                $orderId = $value['id'];
                $client = ClientModel::find($value['client_id']);
                if(empty($client)){
                    $clientName = '#'.$value['client_id'];
                }else{
                    $clientName = 'client#'.$client['id'].'#'.$client['username'].'#';
                }
                # 记录日志
                if($type == 'clear_recycle_bin' || $type == 'recycle_bin'){
                    active_log(lang('log_order_delete_from_recycle_bin_success', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$orderId]), 'order', $orderId);
                }else{
                    active_log(lang('admin_delete_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$orderId]), 'order', $orderId);
                }

                $this->destroy($orderId);
                // 删除订单子项
                OrderItemModel::destroy(function($query) use($orderId){
                    $query->where('order_id', $orderId);
                });
                // 删除上游订单
                UpstreamOrderModel::destroy(function($query) use($orderId){
                    $query->where('order_id', $orderId);
                });
                if($delete_host==1){
                    // 从回收站删除
                    if($type == 'clear_recycle_bin' || $type == 'recycle_bin'){
                        // 删除已经逻辑删除的产品
                        HostModel::where('order_id', $orderId)->where('is_delete', 1)->delete();
                    }else{
                        // 删除订单产品
                        HostModel::destroy(function($query) use($orderId){
                            $query->where('status', '<>', 'Active')->where('order_id', $orderId);
                        });
                    }
                }
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        foreach ($id as $value) {
            hook('after_order_delete', ['id'=>$value]);
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-10-18
     * @title 取消订单
     * @desc 取消订单
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function cancelOrder($id)
    {
        // 验证订单ID
        $order = $this->find($id);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }
        $clientId = get_client_id();
        if($clientId!=$order['client_id']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        if($order['status']!='Unpaid'){
            return ['status'=>400, 'msg'=>lang('order_cannot_cancel')];
        }

        $hosts = HostModel::where('status', '<>', 'Unpaid')->where('order_id', $id)->where('is_delete', 0)->select()->toArray();
        if (!empty($hosts)){
            return ['status'=>400, 'msg'=>lang('order_host_not_unpaid')];
        }

        $hookRes = hook('before_order_cancel',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            $client = ClientModel::find($clientId);
            if(empty($client)){
                $clientName = '#'.$clientId;
            }else{
                $clientName = 'client#'.$clientId.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('log_client_cancel_order', ['{client}'=>$clientName, '{order}'=>'#'.$order->id]), 'order', $order->id);

            $this->update([
                'status' => 'Cancelled',
                'update_time' => time()
            ], ['id' => $id]);
            
            HostModel::update([
                'status' => 'Cancelled',
                'update_time' => time()
            ], ['order_id' => $id]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        hook('after_order_cancel',['id'=>$id]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2023-01-29
     * @title 订单退款
     * @desc 订单退款
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param string type - 退款类型credit退款到余额transaction退款到流水 required
     * @param float amount - 退款金额 required
     * @param string gateway - 支付方式 退款到流水时需传
     * @param string transaction_number - 流水号 退款到流水时需传
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderRefund($param)
    {
        $id = $param['id']??0;
        $adminId = get_admin_id();
        $param['type'] = $param['type'] ?? '';
        if(!in_array($param['type'], ['credit', 'transaction'])){
            return ['status' => 400, 'msg' => lang('param_error')];
        }

        if($param['type']=='transaction'){
            $param['gateway'] = $param['gateway'] ?? '';
            $param['transaction_number'] = $param['transaction_number'] ?? '';

            if(empty($param['gateway'])){
                // 获取支付接口名称
                $gateway = PluginModel::where('module', 'gateway')->find();
                if(empty($gateway)){
                    return ['status' => 400, 'msg' => lang('gateway_is_not_exist')];
                }
                $gateway['config'] = json_decode($gateway['config'],true);
                $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];
                $param['gateway'] = $gateway['name'] ?? '';
            }else{
                // 获取支付接口名称
                $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();
                if(empty($gateway)){
                    return ['status' => 400, 'msg' => lang('gateway_is_not_exist')];
                }
                $gateway['config'] = json_decode($gateway['config'],true);
                $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];
            }
        }

        $hookRes = hook('before_order_refund',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($id);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }

            if(!in_array($order['status'], ['Paid', 'Refunded'])){
                throw new \Exception(lang('order_not_support_refund'));
            }

            $amount = TransactionModel::where('order_id', $id)->sum('amount');
            $refundAmount = RefundRecordModel::where('order_id', $id)->where('type', 'credit')->sum('amount');

            $amount = $amount-$refundAmount;
            if($param['amount']>$amount){
                throw new \Exception(lang('refund_amount_not_enough'));
            }
            if($param['amount']>($order['amount']-$order['refund_amount'])){
                throw new \Exception(lang('refund_amount_not_enough'));
            }

            if($param['type']=='transaction'){
                $transaction = TransactionModel::create([
                    'order_id' => $id,
                    'client_id' => $order['client_id'],
                    'amount' => -$param['amount'],
                    'gateway' => $param['gateway'],
                    'gateway_name' => $gateway['title'] ?? '',
                    'transaction_number' => $param['transaction_number'],
                    'create_time' => time()
                ]);
            }else{
                update_credit([
                    'type' => 'Refund',
                    'amount' => $param['amount'],
                    'notes' => lang('order_refund', ['{id}' => $id]),
                    'client_id' => $order['client_id'],
                    'order_id' => $id,
                    'host_id' => 0
                ]);
            }

            RefundRecordModel::create([
                'order_id'              => $id,
                'client_id'             => $order['client_id'],
                'admin_id'              => $adminId,
                'type'                  => $param['type'],
                'transaction_id'        => $param['type']=='transaction' ? $transaction->id : 0,
                'amount'                => $param['amount'],
                'create_time'           => time(),
            ]);

            $refundAmount = RefundRecordModel::where('order_id', $id)->sum('amount');

            $this->update([
                'refund_amount' => $refundAmount,
                'status' => 'Refunded',
                'update_time' => time(),
            ], ['id' => $id]);

            $client = ClientModel::find($order['client_id']);
            if(empty($client)){
                $clientName = '#'.$order['client_id'];
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            if($param['type']=='transaction'){
                active_log(lang('admin_refund_user_order_transaction', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$param['amount'], '{transaction}'=>$param['transaction_number']]), 'order', $order->id);
            }else{
                active_log(lang('admin_refund_user_order_credit', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$param['amount']]), 'order', $order->id);
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        hook('after_order_refund',['id'=>$id]);

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2023-01-29
     * @title 订单应用余额
     * @desc 订单应用余额
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderApplyCredit($param)
    {
        $id = $param['id']??0;
        $adminId = get_admin_id();

        $hookRes = hook('before_order_apply_credit',['id'=>$id]);
        foreach($hookRes as $v){
            if(isset($v['status']) && $v['status'] == 400){
                return $v;
            }
        }

        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($id);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }

            $amount = TransactionModel::where('order_id', $id)->sum('amount');
            $refundAmount = RefundRecordModel::where('order_id', $id)->where('type', 'credit')->sum('amount');
            $amount = $amount-$refundAmount;
            if($amount<0){
                throw new \Exception(lang('order_not_support_apply_credit'));
            }

            $amount = $order['amount']-$order['credit']-$amount;

            $amount = amount_format($amount);
            if($param['amount']>$amount){
                throw new \Exception(lang('apply_credit_not_enough'));
            }


            $apply = false; // 应用余额

            if(in_array($order['status'], ['Paid', 'Refunded'])){
                $this->update([
                    'credit' => $order['credit']+$param['amount'],
                ], ['id' => $id]);
                $apply = true;
            }else if($order['status']=='Unpaid'){
                $this->update([
                    'credit' => $order['credit']+$param['amount'],
                    'status' => $param['amount']==$amount ? 'Paid' : $order['status'],
                    'amount_unpaid' => $order['amount']-$order['credit']-$param['amount'],
                    'gateway' => ($order['credit']+$param['amount'])==$order['amount'] ? 'credit' : $order['gateway'],
                    'gateway_name' => ($order['credit']+$param['amount'])==$order['amount'] ? lang('credit_payment') : $order['gateway_name'],
                    'pay_time' => $param['amount']==$amount ? time() :  $order['pay_time'],
                ], ['id' => $id]);
                if($param['amount']==$amount){
                    $res = update_credit([
                        'type' => 'Applied',
                        'amount' => -($order['credit']+$param['amount']),
                        'notes' => lang('order_apply_credit')."#{$id}",
                        'client_id' => $order['client_id'],
                        'order_id' => $id,
                        'host_id' => 0,
                    ]);

                    if(!$res){
                        throw new \Exception(lang('insufficient_credit_deduction_failed'));
                    }
                    $this->processPaidOrder($param['id']);
                }
            }else{
                $this->update([
                    'credit' => $order['credit']+$param['amount'],
                    'amount_unpaid' => $order['amount']-$order['credit']-$param['amount'],
                ], ['id' => $id]);
            }

            if($apply){
                $res = update_credit([
                    'type' => 'Applied',
                    'amount' => -$param['amount'],
                    'notes' => lang('order_apply_credit')."#{$id}",
                    'client_id' => $order['client_id'],
                    'order_id' => $id,
                    'host_id' => 0,
                ]);

                if(!$res){
                    throw new \Exception(lang('insufficient_credit_deduction_failed'));
                }
            }

            $client = ClientModel::find($order['client_id']);
            if(empty($client)){
                $clientName = '#'.$order['client_id'];
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_apply_credit_to_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$param['amount']]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2023-01-29
     * @title 订单扣除余额
     * @desc 订单扣除余额
     * @author theworld
     * @version v1
     * @param int id - 订单ID required
     * @param float amount - 金额 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function orderRemoveCredit($param)
    {
        $id = $param['id']??0;
        $adminId = get_admin_id();

        $this->startTrans();
        try {
            // 验证订单ID
            $order = $this->lock(true)->find($id);
            if (empty($order) || $order['is_recycle']){
                throw new \Exception(lang('order_is_not_exist'));
            }

            if($param['amount']>$order['credit']){
                throw new \Exception(lang('remove_credit_not_enough'));
            }

            if(in_array($order['status'], ['Paid', 'Refunded'])){
                $this->update([
                    'credit' => $order['credit']-$param['amount'],
                    'status' => 'Refunded',
                ], ['id' => $id]);

                update_credit([
                    'type' => 'Refund',
                    'amount' => $param['amount'],
                    'notes' => lang('order_remove_credit', ['{id}' => $id]),
                    'client_id' => $order['client_id'],
                    'order_id' => $id,
                    'host_id' => 0,
                ]);
            }else{
                $this->update([
                    'credit' => $order['credit']-$param['amount'],
                    'amount_unpaid' => $order['amount']-$order['credit']+$param['amount'],
                ], ['id' => $id]);
            }

            $client = ClientModel::find($order['client_id']);
            if(empty($client)){
                $clientName = '#'.$order['client_id'];
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_remove_credit_from_user_order', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$param['amount']]), 'order', $order->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单支付方式
     * @desc 修改订单支付方式
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param string param.gateway - 支付方式 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateGateway($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order) || $order['is_recycle']){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $param['gateway'] = $param['gateway'] ?? '';

        // 获取支付接口名称
        $gateway = PluginModel::where('module', 'gateway')->where('name', $param['gateway'])->find();
        if(empty($gateway)){
            return ['status' => 400, 'msg' => lang('gateway_is_not_exist')];
        }
        $gateway['config'] = json_decode($gateway['config'],true);
        $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];

        $this->startTrans();
        try {
            // 修改订单支付方式
            $this->update([
                'gateway' => $param['gateway'], 
                'gateway_name' => $gateway['title'], 
                'update_time' => time()
            ], ['id' => $param['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_gateway', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order['gateway_name'], '{new}'=>$gateway['title']]), 'order', $order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2023-01-29
     * @title 修改订单备注
     * @desc 修改订单备注
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param string param.notes - 备注
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateNotes($param)
    {
        // 验证订单ID
        $order = $this->find($param['id']);
        if (empty($order)){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $param['notes'] = $param['notes'] ?? '';

        $this->startTrans();
        try {
            // 修改订单备注
            $this->update([
                'notes' => $param['notes'], 
                'update_time' => time()
            ], ['id' => $param['id']]);

            $client = ClientModel::find($order->client_id);
            if(empty($client)){
                $clientName = '#'.$order->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_adjust_user_order_notes', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{old}'=>$order['notes'], '{new}'=>$param['notes']]), 'order', $order->id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    # 更新上游订单利润
    public function updateUpstreamOrderProfit($id){
        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel->where('order_id', $id)->select()->toArray();
        $upstreamOrder = UpstreamOrderModel::where('order_id', $id)->select()->toArray();
        if(!empty($upstreamOrder)){
            $hostAmountArr = [];
            $hostReduceArr = [];
            $amount = 0;
            $reduce = 0;
            foreach ($orderItems as $key => $value) {
                if($value['amount']>=0){
                    $amount += $value['amount'];
                    if(!empty($value['host_id'])){
                        $hostAmountArr[$value['host_id']] = ($hostAmountArr[$value['host_id']] ?? 0) + $value['amount']; 
                    }
                }else{
                    if(!empty($value['host_id'])){
                        $hostReduceArr[$value['host_id']] = ($hostReduceArr[$value['host_id']] ?? 0) + $value['amount']; 
                    }else{
                        $reduce += $value['amount'];
                    }
                }
                
            }

            foreach ($hostAmountArr as $key => $value) {
                if($amount>0){
                    $value = bcadd($value + ($hostReduceArr[$key] ?? 0), ($value/$amount*$reduce), 2);
                }else{
                    $value = 0;
                }
                $hostAmountArr[$key] = $value;
            }
            
            foreach ($upstreamOrder as $key => $value) {
                $value['original_price'] = $value['amount'] - $value['profit'];
                $amount = $hostAmountArr[$value['host_id']] ?? 0;
                UpstreamOrderModel::update([
                    'amount' => $amount, 
                    'profit' => bcsub($amount, $value['original_price'], 2)
                ], ['id' => $value['id']]);
            }
        }
        return true;
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
		if(isset($orderItems[0]['type']) && $orderItems[0]['type'] == 'recharge'){
			add_task([
				'type' => 'email',
				'description' => lang('order_recharge_send_mail'),
				'task_data' => [
					'name'=>'order_recharge',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => lang('order_recharge_send_sms'),
				'task_data' => [
					'name'=>'order_recharge',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);
		}else{
			add_task([
				'type' => 'email',
				'description' => lang('order_pay_send_mail'),
				'task_data' => [
					'name'=>'order_pay',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => lang('order_pay_send_sms'),
				'task_data' => [
					'name'=>'order_pay',//发送动作名称
					'order_id'=>$id,//订单ID
				],		
			]);			
		}
        update_upstream_order_profit($id);
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
                        'notes' => lang('recharge')."#{$transactionNumber}",
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
                default:
                    break;
            }
        }
        # 引入订单支付后钩子
        hook('order_paid',['id'=>$id]);

        return true;
    }

    # 产品订单处理
    public function hostOrderHandle($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,h.order_id,h.client_id,h.status,h.billing_cycle,h.billing_cycle_name,h.name,h.billing_cycle_time,p.auto_setup,p.creating_notice_sms,p.creating_notice_mail,h.order_id,p.name as product_name,h.active_time')
            ->leftjoin('product p','h.product_id=p.id')
            ->where('h.id',$id)
            ->where('h.is_delete', 0)
            ->find();
        if (empty($host)){
            return false;
        }
        if(in_array($host->billing_cycle,['onetime'])){
            $dueTime = 0;
        }else if(in_array($host->billing_cycle,['free']) && $host->billing_cycle_time==0){
            $dueTime = 0;
        }else{
            $dueTime = time() + intval($host->billing_cycle_time);
        }
        # 修改产品
        $HostModel->update([
            'status' => 'Pending',
            'due_time' => $dueTime,
        ],['id'=>$id]);

        # 更改新购订单子项描述
        $OrderItemModel = new OrderItemModel();
        $orderItem = $OrderItemModel->where('order_id',$host['order_id'])
            ->where('host_id',$id)
            ->where('type','host')
            ->find();
        if (!empty($orderItem)){
            if (in_array($host['billing_cycle'],['onetime','free'])){
                $desDueTime = '∞';
            }else{
                $desDueTime = date('Y/m/d',$dueTime);
            }
            $des = $host['product_name'] . '(' .$host['name']. '),'.lang('purchase_duration').':'.$host['billing_cycle_name'] .'(' . date('Y/m/d',$host['active_time']) . '-'. $desDueTime .')';
            //$des = lang('order_description_append',['{product_name}'=>$host['product_name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$host['billing_cycle_name'] ,'{time}'=>date('Y/m/d H',$host['active_time']) . '-' . date('Y/m/d H',date('Y/m/d H',$dueTime))]);
            $orderItemDes = explode("\n",$orderItem['description'])??[];
            if (count($orderItemDes)>=2){
                array_pop($orderItemDes);
                array_push($orderItemDes,$des);
            }
            $orderItem->save([
                'description' => implode("\n",$orderItemDes)
            ]);
        }

        # 暂停时,付款后解除
        if ($host->status == 'Suspended'){
			add_task([
				'type' => 'host_unsuspend',
				'description' => '#'.$id.lang('host_unsuspend'),
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
                    'description' => lang('host_creating_send_sms'),
                    'task_data' => [
                        'name'=>'host_pending',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]);
            }
            if($host_pending['email_enable']==1){
                add_task([
                    'type' => 'email',
                    'description' => lang('host_creating_send_mail'),
                    'task_data' => [
                        'name'=>'host_pending',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]);
            }
			
			add_task([
				'type' => 'host_create',
				'description' => lang('client_host_create', ['{client_id}' => $host['client_id'], '{host_id}' => $host['id']]),
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
    public function upgradeOrderHandle($id)
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

        # 升降级
        if($upgrade['type']=='product'){
            // 获取接口
            $product = ProductModel::find($upgrade['rel_id']);
            if($product['type']=='server_group'){
                $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                $serverId = $server['id'] ?? 0;
            }else{
                $serverId = $product['rel_id'];
            }
            $HostModel = new HostModel();
            $host = $HostModel->find($upgrade['host_id']);
            // wyh 20210109 改 一次性/免费可升级后
            if($host['billing_cycle']=='onetime'){
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];
                }
            }else if($host['billing_cycle']=='free' && $host['billing_cycle_time']==0){
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];
                }
            }else{
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{ # 周期到周期,不变更
                    $hostDueTime = $host['due_time'];
                }
            }

            HostModel::update([
                'product_id' => $upgrade['rel_id'],
                'server_id' => $serverId,
                // TODO 更改为实际支付
                'first_payment_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['renew_price'] : ($product['pay_type']=='onetime'?$upgrade['price']:0),//$upgrade['price'],
                'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['renew_price'] : 0,
                'billing_cycle' => $product['pay_type'],
                'billing_cycle_name' => $upgrade['billing_cycle_name'],
                'billing_cycle_time' => $upgrade['billing_cycle_time'],
                'due_time' => $hostDueTime,
                'base_price' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['base_price'] : 0,
            ],['id' => $upgrade['host_id']]);
        }else if($upgrade['type']=='config_option'){
            $host = HostModel::find($upgrade['host_id']);
            HostModel::update([
                'first_payment_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['renew_price'] : ($host['billing_cycle']=='onetime'?$upgrade['price']:0),//$upgrade['price'],
                'renew_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['renew_price'] : 0,
                'base_price' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['base_price'] : ($host['billing_cycle']=='onetime'?$upgrade['base_price']:0)
            ],['id' => $upgrade['host_id']]);
        }
        
		# 添加到定时任务
		add_task([
			'type' => 'host_upgrade',
			'description' => lang('client_host_upgrade', ['{client_id}' => $upgrade['client_id'], '{host_id}' => $upgrade['host_id']]),
			'task_data' => [
				'upgrade_id'=>$id,//upgrade ID
			],		
		]);
        

        return true;
    }

    /**
     * 时间 2023-06-08
     * @title 订单列表导出EXCEL
     * @desc 订单列表导出EXCEL
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:订单ID
     * @param string param.type - 类型new新订单renew续费订单upgrade升降级订单artificial人工订单
     * @param string param.status - 状态Unpaid未付款Paid已付款
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,type,create_time,amount,status
     * @param string param.sort - 升/降序 asc,desc
     */
    public function exportExcel($param){
        $data = $this->orderList($param);
        $data = $data['list'];
        foreach ($data as $key => $value) {
            $data[$key] = [
                'id' => $value['id'],
                'product_name' => !empty($value['product_names']) ? implode('', $value['product_names']) : '',
                'amount' => configuration('currency_prefix').$value['amount'],
                'gateway' => $value['credit']>0 ? ($value['amount']==$value['credit'] ? lang('order_credit') : (lang('order_credit').'+'.$value['gateway'])) : $value['gateway'],
                'create_time' => date("Y-m-d H:i", $value['create_time']),
                'status' => lang('order_status_'.$value['status']),
            ];
        }
        $field = [
            'id' => 'ID',
            'product_name' => lang('order_product_name'),
            'amount' => lang('order_amount'),
            'gateway' => lang('order_gateway'),
            'create_time' => lang('order_create_time'),
            'status' => lang('order_status'),
        ];

        return export_excel('order'.time(), $field, $data);
    }

    /**
     * 时间 2024-03-18
     * @title 锁定订单
     * @desc  锁定订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function lockOrder($param)
    {
        if(!isset($param['id']) || !is_array($param['id']) || empty($param['id'])){
            return ['status'=>400, 'msg'=>lang('id_error')];
        }
        $orderId = $this->whereIn('id', $param['id'])->column('id');
        if(count($orderId) != count($param['id'])){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $this->whereIn('id', $orderId)->update([
            'is_lock'       => 1,
            'update_time'   => time(),
        ]);

        $description = lang('log_order_lock_success', [
            '{id}'  => implode(',', $orderId),
        ]);
        active_log($description, 'order');

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-18
     * @title 取消锁定订单
     * @desc  取消锁定订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function unlockOrder($param)
    {
        if(!isset($param['id']) || !is_array($param['id']) || empty($param['id'])){
            return ['status'=>400, 'msg'=>lang('id_error')];
        }
        $orderId = $this->whereIn('id', $param['id'])->column('id');
        if(count($orderId) != count($param['id'])){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $this->whereIn('id', $orderId)->update([
            'is_lock'       => 0,
            'update_time'   => time(),
        ]);

        $description = lang('log_order_unlock_success', [
            '{id}'  => implode(',', $orderId),
        ]);
        active_log($description, 'order');

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-18
     * @title 回收订单
     * @desc  回收订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @param   int param.delete_host 1 是否删除产品:0否1是
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function recycleOrder($param)
    {
        $deleteHost = $param['delete_host'] ?? 1;
        $saveDays = configuration('order_recycle_bin_save_days');
        $time = time();
        $willDeleteTime = $saveDays === '0' ? 0 : $time + ($saveDays ?: 30)*24*3600;

        $orders = $this
                ->field('id')
                ->whereIn('id', $param['id'])
                ->select();

        $hostIds = [];
        $this->startTrans();
        try{
            foreach($orders as $order){
                $orderId = $order['id'];
                $change = $this->where('id', $orderId)->where('is_recycle', 0)->update([
                    'is_recycle'        => 1,
                    'recycle_time'      => $time,
                    'will_delete_time'  => $willDeleteTime,
                ]);
                if(!$change){
                    continue;
                }

                if($deleteHost == 1){
                    $hostIds = HostModel::where('order_id', $orderId)->where('status', '<>', 'Active')->where('is_delete', 0)->column('id');
                    if(!empty($hostIds)){
                        HostModel::where('order_id', $orderId)->where('status', '<>', 'Active')->update([
                            'is_delete'     => 1,
                            'delete_time'   => $time,
                        ]);
                    }
                }
                // 日志
                active_log(lang('log_order_recycle_success', ['{admin}'=>request()->admin_name, '{order}'=>'#'.$orderId]), 'order', $orderId);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        foreach($hostIds as $hostId){
            hook('after_host_soft_delete', ['id'=>$hostId]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-18
     * @title 恢复订单
     * @desc  恢复订单
     * @author hh
     * @version v1
     * @param   array param.id - 订单ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function recoverOrder($param)
    {
        if(!isset($param['id']) || !is_array($param['id']) || empty($param['id'])){
            return ['status'=>400, 'msg'=>lang('id_error')];
        }
        $orders = $this
                ->field('id')
                ->whereIn('id', $param['id'])
                ->where('is_recycle', 1)
                ->select()
                ->toArray();
        if(count($orders) != count($param['id'])){
            return ['status'=>400, 'msg'=>lang('order_is_not_exist')];
        }

        $this->startTrans();
        try{
            foreach($orders as $order){
                $orderId = $order['id'];
                $change = $this->where('id', $orderId)->where('is_recycle', 1)->update([
                    'is_recycle'        => 0,
                    'recycle_time'      => 0,
                    'will_delete_time'  => 0,
                ]);
                if(!$change){
                    continue;
                }
                // 还原产品
                HostModel::where('order_id', $orderId)->where('is_delete', 1)->update([
                    'is_delete'     => 0,
                    'delete_time'   => 0,
                    'update_time'   => time(),
                ]);

                // 日志
                active_log(lang('log_order_recover_from_recycle_bin_success', ['{admin}'=>request()->admin_name, '{order}'=>'#'.$orderId]), 'order', $orderId);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }


}
