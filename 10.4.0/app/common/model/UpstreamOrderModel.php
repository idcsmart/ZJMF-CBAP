<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 上游订单模型
 * @desc 上游订单模型
 * @use app\common\model\UpstreamOrderModel
 */
class UpstreamOrderModel extends Model
{
	protected $name = 'upstream_order';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'supplier_id'   => 'int',
        'order_id'      => 'int',
        'host_id'       => 'int',
        'amount'        => 'float',
        'profit'        => 'float',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2023-02-13
     * @title 订单列表
     * @desc 订单列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:ID,用户名称,邮箱,手机号,商品名称,产品标识
     * @param int param.supplier_id - 供应商ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
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
    public function orderList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['supplier_id'] = intval($param['supplier_id'] ?? 0);
        /*$param['type'] = $param['type'] ?? '';
        $param['status'] = $param['status'] ?? '';*/
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'type', 'create_time', 'amount', 'status']) ? 'o.'.$param['orderby'] : 'o.id';

        $where = function (Query $query) use ($param){
            if (!empty($param['keywords'])){
                $query->where('o.id|c.username|c.email|c.phone|p.name','like',"%{$param['keywords']}%");
            }
            if(!empty($param['supplier_id'])){
                $query->where('a.supplier_id', $param['supplier_id']);
            }
        };

        $count = $this->alias('a')
            ->field('a.id')
            ->leftjoin('order o', 'o.id=a.order_id')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->leftjoin('host h', 'h.id=a.host_id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where($where)
            ->group('o.id')
            ->count();
        $orders = $this->alias('a')
            ->field('o.id,o.type,o.create_time,o.amount,sum(a.profit) profit,o.status,o.gateway_name gateway,o.credit,o.client_id,c.username client_name,c.email,c.phone_code,c.phone,c.company,p.name product_name')
            ->leftjoin('order o', 'o.id=a.order_id')
            ->leftjoin('client c', 'c.id=o.client_id')
            ->leftjoin('host h', 'h.id=a.host_id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('o.id')
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

            // wyh 20230130 有问题就注释
            $description = explode("\n",$orderItem['description']);
            if (!empty($description)){
                $newDes = '';
                foreach ($description as $item1){
                    if (count(explode('=>',$item1))==4){
                        $arr = explode('=>',$item1);
                        $itemDes = $arr[0] . ':' . $arr[1] . $arr[2] . $arr[3];
                        $newDes = $newDes.$itemDes . "\n";
                    }else{
                        $newDes = $newDes . $item1 . "\n";
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
            if(in_array($orderItem['type'], ['addon_promo_code', 'addon_idcsmart_promo_code', 'addon_idcsmart_client_level'])){
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
            $orders[$key]['profit'] = amount_format($order['profit']); // 处理金额格式

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
        }


        return ['list' => $orders, 'count' => $count];
        
    }

    /**
     * 时间 2023-02-13
     * @title 销售信息
     * @desc 销售信息
     * @author theworld
     * @version v1
     * @param int param.supplier_id - 供应商ID
     * @return string total - 总销售额 
     * @return string profit - 总利润 
     * @return int product_count - 商品总数
     * @return int host_count - 产品总数
     */
    public function sellInfo($param)
    {
        $where = function (Query $query) use ($param){
            if (!empty($param['supplier_id'])){
                $query->where('a.supplier_id', $param['supplier_id']);
            }
        };
        $total = $this->alias('a')->leftjoin('order o', 'o.id=a.order_id')->where('o.status', 'Paid')->where($where)->sum('a.amount');
        $profit = $this->alias('a')->leftjoin('order o', 'o.id=a.order_id')->where('o.status', 'Paid')->where($where)->sum('a.profit');
        $productCount = UpstreamProductModel::alias('a')->leftjoin('product p', 'p.id=a.product_id')->where('p.id', '>', 0)->where($where)->count();
        $hostCount = UpstreamHostModel::alias('a')->leftjoin('host h', 'h.id=a.host_id')->where('h.id', '>', 0)->where($where)->count();

        return [
            'total' => amount_format($total),
            'profit' => amount_format($profit),
            'product_count' => $productCount,
            'host_count' => $hostCount,
        ];
    }
}