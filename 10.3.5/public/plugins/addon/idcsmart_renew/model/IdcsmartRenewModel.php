<?php
namespace addon\idcsmart_renew\model;

use addon\promo_code\model\PromoCodeModel;
use app\admin\model\PluginModel;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
use app\common\model\ClientModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-02
 */
class IdcsmartRenewModel extends Model
{
    protected $name = 'addon_idcsmart_renew';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'client_id'                        => 'int',
        'host_id'                          => 'int',
        'new_billing_cycle'                => 'string',
        'new_billing_cycle_time'           => 'int',
        'new_billing_cycle_amount'         => 'float',
        'status'                           => 'string',
        'create_time'                      => 'int',
    ];

    public $isAdmin = false;

    # 处理可续费周期(过滤规则)
    public function cyclesFilter(HostModel $host,$cycles,$promoCode='')
    {
        foreach ($cycles as $k1=>$item1){
            # 未设置参数,清除此周期
            if (!isset($item1['duration']) || !isset($item1['billing_cycle']) || !isset($item1['price'])){
                unset($cycles[$k1]);
            }
        }

        foreach ($cycles as $k2=>$item2){

            $renewAmount = $host->renew_amount;

            if (isset($item2['son_host_id']) && $item2['son_host_id']){
                $sonHost = (new HostModel())->find($item2['son_host_id']);
                $renewAmount += $sonHost['renew_amount'];
                $item2['price'] += $item2['son_price'];
                $cycles[$k2]['price'] = $item2['price'];
            }


            # 基础价格
            $cycles[$k2]['base_price'] = bcsub($item2['price'],0,2);
            # 产品对应周期(只能用时间比较)
            $flag = $host->billing_cycle_time == $item2['duration'] || $host->billing_cycle_name==$item2['billing_cycle'];
            if ($flag){ # 自然月导致前一个判断可能不生效,后一个判断在周期名称相同下也不生效
                # 产品续费金额大于模块金额(过滤掉小于此续费周期的 周期)
                if ($renewAmount > $item2['price']){
                    $max = $item2['duration'];
                }

            }else{ # 其他,也减除优惠码价格
                if(empty($promoCode)){ // 无优惠码
                    // 产品是否存在循环优惠(以原价优惠)
                    $hookResults = hook('apply_promo_code',['host_id'=>$host->id,'price'=>$item2['price'],'scene'=>'renew','duration'=>$item2['duration']]);
                    foreach ($hookResults as $hookResult){
                        if ($hookResult['status']==200){
                            if (isset($hookResult['data']['loop']) && $hookResult['data']['loop']){
                                $cycles[$k2]['price'] = bcsub($cycles[$k2]['price'],$hookResult['data']['discount']??0,2);
                            }
                        }
                    }

                    // 子产品优惠金额：子产品使用父产品优惠码
                    if (isset($item2['son_host_id']) && $item2['son_host_id']){
                        $hookResults = hook('apply_promo_code',['host_id'=>$host->id,'price'=>$item2['son_price'],'scene'=>'renew','duration'=>$item2['duration']]);
                        foreach ($hookResults as $hookResult){
                            if ($hookResult['status']==200){
                                if (isset($hookResult['data']['loop']) && $hookResult['data']['loop']){
                                    $sonPromoCodeDiscount = $hookResult['data']['discount']??0;
                                    // $cycles[$k2]['price'] = bcsub($cycles[$k2]['price'],$sonDis1,2);
                                }
                            }
                        }
                    }

                }
            }
            // 客户等级优惠
            // 实例化模型类
            $PluginModel = new PluginModel();
            $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
            if (!empty($plugin)){
                $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                // 获取商品折扣金额
                $discount = $IdcsmartClientLevelModel->productDiscount([
                    'id' => $host['product_id'],
                    'amount' => $cycles[$k2]['base_price']
                ]);
                $cycles[$k2]['price'] = bcsub($cycles[$k2]['price'],$discount??0,2);

                // 子产品客户等级折扣金额
                if (isset($item2['son_host_id']) && $item2['son_host_id']){
                    $sonClientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                        'id' => $host['product_id'],
                        'amount' => $item2['son_price']
                    ]);
                }
            }

            // 保存的金额
            $cycles[$k2]['price_save'] = $cycles[$k2]['price'];

            // 续费的时候手动输入优惠码
            if (!empty($promoCode)){
                $PromoCodeModel = new PromoCodeModel();
                $res = $PromoCodeModel->apply([
                    'promo_code' => $promoCode,
                    'scene' => 'renew',
                    'host_id' => $host['id'],
                    'product_id' => $host['product_id'],
                    'amount' => $cycles[$k2]['base_price'],
                    'billing_cycle_time' => $host['billing_cycle_time']
                ]);
                $discount2 = $res['data']['discount']??0;

                $cycles[$k2]['price'] = bcsub($cycles[$k2]['price'],$discount2??0,2);
                // 是否是循环优惠
                $loop = $res['data']['loop']??0;

                if ($loop){ // 保存的值 = 实际支付
                    $cycles[$k2]['price_save'] = $cycles[$k2]['price'];
                }

                // 只要续费手动输入了 可用优惠码，就会覆盖原来的产品优惠码以及续费或者升降级使用的！，下次续费 方便其他周期计算
                if ($res['status']==200){
                    $OrderItemModel = new OrderItemModel();
                    // 也就是说，这里可能会更新多条数据！最终产品的所有使用优惠码的子项都会更新为当前优惠码！使用其中一个继续计算就行
                    $OrderItemModel->where('host_id',$host['id'])
                        ->where('type','addon_promo_code')
                        ->update([
                            'rel_id' => $res['data']['id']??0
                        ]);
                    $flag = false; // 使用优惠后金额
                }

                if (isset($item2['son_host_id']) && $item2['son_host_id']){
                    $sonRes = $PromoCodeModel->apply([
                        'promo_code' => $promoCode,
                        'scene' => 'renew',
                        'host_id' => $host['id'],
                        'product_id' => $host['product_id'],
                        'amount' => $item2['son_price'],
                        'billing_cycle_time' => $host['billing_cycle_time']
                    ]);
                    if ($loop){
                        $sonPromoCodeDiscountNew = $sonRes['data']['discount']??0;
                        $cycles[$k2]['price_save'] +=  $sonPromoCodeDiscountNew;
                    }
                }
            }
            // 等级折扣+优惠折扣后，取最大金额
            $cycles[$k2]['max_renew'] = $flag;

            if (isset($item2['son_host_id']) && $item2['son_host_id']){
                $renewAmount = bcsub($renewAmount,0,2);
                $priceSave = bcsub($renewAmount,$sonHost['renew_amount']??0,2);
                $cycles[$k2]['price_save'] = $cycles[$k2]['price_save']- $item2['son_price']+($sonPromoCodeDiscount??0)+($sonClientLevelDiscount??0);
            }else{
                $priceSave = bcsub($renewAmount,0,2);
                $renewAmount = bcsub($renewAmount,0,2);
            }

            // 实际支付的金额
            $cycles[$k2]['price'] = $flag?$renewAmount:$cycles[$k2]['price'];
            // 保存至数据库的金额
            $cycles[$k2]['price_save'] = $flag?$priceSave:$cycles[$k2]['price_save'];

            $cycles[$k2]['renew_amount'] = $flag?$renewAmount:$cycles[$k2]['base_price'];
        }

        if (isset($max)){
            foreach ($cycles as $k3=>$item3){
                if ($item3['duration'] < $max){
                    unset($cycles[$k3]);
                }
            }
        }

        $cycles = array_values($cycles);

        return $cycles?:[];
    }

    # 续费页面
    public function renewPage($param)
    {
        $id = $param['id'];
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $clientId = $this->isAdmin?$host->client_id:get_client_id();
        if ($host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        # 产品已开通/已到期才可续费
        if (!in_array($host['status'],['Active','Suspended'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 产品周期一次性不可续费
        if ($host->billing_cycle == 'onetime'){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        $ModuleLogic = new ModuleLogic();
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->durationPrice($host);
        }else{
            $result = $ModuleLogic->durationPrice($host);
        }
        if ($result['status'] != 200){
            return ['status'=>400,'msg'=>$result['msg']?:lang_plugins('get_fail')];
        }

        # 处理可续费周期
        $cycles = $result['data']?:[];

        $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['host'=>$cycles]];
    }

    # 续费
    public function renew($param)
    {
        $id = $param['id'];
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $clientId = $this->isAdmin?$host->client_id:get_client_id();
        if ($host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        # 产品已开通/已到期才可续费
        if (!in_array($host['status'],['Active','Suspended'])){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 产品周期一次性不可续费
        if ($host->billing_cycle == 'onetime'){
            return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
        }

        # 判断周期
        if (!isset($param['billing_cycle']) || empty($param['billing_cycle'])){
            return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
        }
        $billingCycle = $param['billing_cycle'];

        $ModuleLogic = new ModuleLogic();
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->durationPrice($host);
        }else{
            $result = $ModuleLogic->durationPrice($host);
        }
        $cycles = $result['status'] == 200 ? $result['data'] :[];
        # 可续费周期
        $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'');

        $billingCycleAllow = array_column($cycles,'billing_cycle');

        if (empty($billingCycleAllow)){
            return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
        }
        if (!in_array($billingCycle,$billingCycleAllow)){
            return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
        }

        # 获取金额
        foreach ($cycles as $value){
            if ($billingCycle == $value['billing_cycle']){
                $amount = $value['price']; // 实际支付
                $amountSave = $value['price_save']; // 保存至host表的amount字段
                $renewAmount = $value['renew_amount']; // 基础价格 = 实际支付 + 优惠
                $maxRenew = $value['max_renew'];
                $profit = $value['profit'] ?? 0;
                $dueTime = $value['duration'];
                break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
            }
        }

        // 自定义续费金额
        if ($this->isAdmin && isset($param['custom_amount']) && $param['custom_amount']>=0){
            $amountSave = $amount = $param['custom_amount'];
            $renewAmount = $param['custom_amount'];
        }

        # 订单子项
        $orderItems = [];

        $this->startTrans();

        try{
            $this->deleteHostUnpaidUpgradeOrder($id);
            $this->deleteUnpaidRenewOrder($id);
            # 续费记录
            $renew = $this->create([
                'client_id' => $clientId,
                'host_id' => $id,
                'new_billing_cycle' => $billingCycle,
                'new_billing_cycle_time' => $dueTime,
                'new_billing_cycle_amount' => $amountSave,// $amount,
                'status' => 'Pending',
                'create_time' => time()
            ]);

            # 到期时间描述,应该和实际的有差异
            if ($host->status == 'Suspended' || time() >= $host->due_time){
                $upData['due_time'] = time()+$dueTime;
            }else{
                $upData['due_time'] = $host->due_time+$dueTime;
            }

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($host['product_id']);

            $orderItems[] = [
                'host_id' => $id,
                'product_id' => $host['product_id'],
                'type' => 'renew',
                'rel_id' => $renew->id,
                'amount' => $renewAmount,
                //'amount' => $amount,
                'description' => lang_plugins('host_renew_description',['{product_name}'=>$product['name'],'{name}'=>$host['name'],'{billing_cycle_name}'=>$billingCycle,'{time}'=>date('Y/m/d',$host->due_time) . '-' . date('Y/m/d',$upData['due_time'])]),
            ];

            # 创建订单
            $data = [
                'type' => 'renew',
                'amount' => $amount,
                'gateway' => $host['gateway'],
                'client_id' => $clientId,
                'items' => $orderItems
            ];
            $OrderModel = new OrderModel();
            $orderId = $OrderModel->createOrderBase($data);
            if($upstreamProduct){
                UpstreamOrderModel::create([
                    'supplier_id' => $upstreamProduct['supplier_id'],
                    'order_id' => $orderId,
                    'host_id' => $host->id,
                    'amount' => $amount,
                    'profit' => $profit,
                    'create_time' => time()
                ]);
            }

            // 20230509 wyh
            if (!$maxRenew){
                // 若不换周期，使用新的优惠码(已经变成更新后的优惠码了)
                // 若换周期，使用旧的循环优惠码或者新的优惠码
                $OrderItemModel = new OrderItemModel();
                $orderItem = $OrderItemModel->where('order_id',$host['order_id'])
                    ->where('host_id',$host['id'])
                    ->where('type','addon_promo_code')
                    ->find();
                $PromoCodeModel = new PromoCodeModel();
                if (!empty($orderItem)){
                    $promoCode = $PromoCodeModel->find($orderItem['rel_id']);
                }
                $param['customfield']['promo_code'] = (isset($param['customfield']['promo_code']) && !empty($param['customfield']['promo_code']))?$param['customfield']['promo_code']:($promoCode['code']??'');

            }

            $param['customfield']['max_renew'] = $maxRenew??false;

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($orderId);

            // 自动续费
            if(isset($param['auto_renew'])){
                # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
                $amount = $OrderModel->where('id',$orderId)->value('amount');

                if($amount>0){
                    $client = ClientModel::find($clientId);
                    if($client['credit']>$amount){
                        $res = update_credit([
                            'type' => 'Applied',
                            'amount' => -$amount,
                            'notes' => lang('order_apply_credit')."#{$orderId}",
                            'client_id' => $clientId,
                            'order_id' => $orderId,
                            'host_id' => 0,
                        ]);
                        if($res){
                            $OrderModel->update([
                                'status' => 'Paid', 
                                'credit' => $amount, 
                                'amount_unpaid'=>0, 
                                'pay_time' => time(), 
                                'update_time' => time()
                            ], ['id' => $orderId]);
                            $autoRenew = true;
                        }
                    }
                }
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
        $amount = $OrderModel->where('id',$orderId)->value('amount');

        # 记录日志
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($host['product_id']);
        if ($this->isAdmin){
            active_log(lang_plugins('renew_admin_renew', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name . '#', '{host}'=>'host#'.$id.'#'.$product['name'].'#', '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }else{
            active_log(lang_plugins('renew_client_renew', ['{client}'=>'user#'.get_client_id().'#'.request()->client_name . '#' , '{host}'=>'host#'.$id.'#'.$product['name'].'#', '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }

        if ($amount>0){
            if(isset($autoRenew)){ # 自动续费
                $this->renewHandle($renew->id);
                return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
            }else if ($this->isAdmin && isset($param['pay']) && intval($param['pay'])){ # 后台直接标记支付
                $result = $OrderModel->orderPaid(['id'=>$orderId]);
                if ($result['status'] == 200){
                    return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
                }else{
                    return ['status'=>400,'msg'=>lang_plugins('renew_fail')];
                }
            }
        }else{
            $this->renewHandle($renew->id);

            return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
        }

        return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Unpaid','data'=>['id'=>$orderId]];
    }

    # 批量续费页面
    public function renewBatchPage($param)
    {
        if (!isset($param['ids']) || !is_array($param['ids']) || empty($param['ids'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        $HostModel = new HostModel();

        $hosts = $HostModel->alias('h')
            ->field('h.*,p.name product_name') //,h.id,h.product_id,p.name product_name,h.name,h.active_time,h.due_time,h.first_payment_amount,h.billing_cycle,h.status,h.billing_cycle_time,h.renew_amount
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where(function (Query $query) use($param) {

                $clientId = $this->isAdmin?intval($param['client_id']):get_client_id();

                $query->where('h.client_id', $clientId);

                $query->whereIn('h.id',$param['ids']);
            })
            ->select();

        $ModuleLogic = new ModuleLogic();
        # 过滤不可续费产品
        $hostsFilter = [];
        foreach ($hosts as $host) {
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->durationPrice($host);
            }else{
                $result = $ModuleLogic->durationPrice($host);
            }

            $cycles = isset($result['status']) && $result['status'] == 200 ? $result['data'] :[];

            # 可续费周期
            $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'');

            $host['billing_cycles'] = $cycles;

            # 处理金额格式
            $host['first_payment_amount'] = amount_format($host['first_payment_amount']);
            # 产品已开通/已到期且非一次性才可续费
            if (in_array($host['status'],['Active','Suspended']) && $host->billing_cycle != 'onetime'){
                $hostsFilter[] = $host->toArray();
            }
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$hostsFilter]];
    }

    # 批量续费
    public function renewBatch($param)
    {
        if (!isset($param['ids']) || !is_array($param['ids']) || empty($param['ids'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        if (!isset($param['billing_cycles']) || !is_array($param['billing_cycles']) || empty($param['billing_cycles'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }

        if ($this->isAdmin){
            if (!isset($param['amount_custom']) || !is_array($param['amount_custom']) || empty($param['amount_custom'])){
                return ['status'=>400,'msg'=>lang_plugins('param_error')];
            }
        }

        $ids = $param['ids'];

        $billingCycles = $param['billing_cycles'];

        $amountCustom = $param['amount_custom']??[];

        $HostModel = new HostModel();

        $ModuleLogic = new ModuleLogic();

        $clientId = $this->isAdmin?intval($param['client_id']):get_client_id();

        $renewDatas = [];

        $orderItems = [];

        $total = 0;

        $productIds = [];

        $upstreamOrders = [];

        foreach ($ids as $id){
            $host = $HostModel->find($id);
            if (empty($host)){
                return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
            }

            if ($host->client_id != $clientId){
                return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
            }
            # 产品已开通/已到期才可续费
            if (!in_array($host['status'],['Active','Suspended'])){
                return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
            }

            # 产品周期一次性不可续费
            if ($host->billing_cycle == 'onetime'){
                return ['status'=>400,'msg'=>lang_plugins('host_cannot_renew')];
            }

            if (!isset($billingCycles[$id]) || ($this->isAdmin && !isset($amountCustom[$id]))){
                return ['status'=>400,'msg'=>lang_plugins('param_error')];
            }

            # 判断周期
            $billingCycle = $billingCycles[$id];
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->durationPrice($host);
            }else{
                $result = $ModuleLogic->durationPrice($host);
            }

            $cycles = $result['status'] == 200 ? $result['data'] :[];

            # 可续费周期
            $cycles = $this->cyclesFilter($host,$cycles,$param['customfield']['promo_code']??'');

            $billingCycleAllow = array_column($cycles,'billing_cycle');

            if (empty($billingCycleAllow)){
                return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
            }
            if (!in_array($billingCycle,$billingCycleAllow)){
                return ['status'=>400,'msg'=>lang_plugins('host_billing_cycle_is_invalid')];
            }

            # 获取周期时间
            foreach ($cycles as $value){
                if ($billingCycle == $value['billing_cycle']){
                    $amount = $value['price']??0;
                    $renewAmount = $value['renew_amount'];
                    $maxRenew = $value['max_renew'];
                    $profit = $value['profit']??0;
                    $dueTime = $value['duration']??0;
                    break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
                }
            }
            # 获取自定义的金额
            $amount = (isset($amountCustom[$id]) && $this->isAdmin)?(float)$amountCustom[$id]:(float)$amount;

            $renewAmount = (isset($amountCustom[$id]) && $this->isAdmin)?(float)$amountCustom[$id]:(float)$renewAmount;

            $total = bcadd($total,$amount,2);

            $renewData = [
                'client_id' => $clientId,
                'host_id' => $id,
                'product_id' => $host['product_id'],
                'new_billing_cycle' => $billingCycle,
                'new_billing_cycle_time' => $dueTime??0,
                'new_billing_cycle_amount' => $renewAmount,//$amount,
                'status' => 'Pending',
                'create_time' => time(),
                'host_name' => $host['name'],
            ];
            $renewDatas[] = $renewData;

            # 默认取第一个产品的支付方式
            if (!isset($gateway)){
                $gateway = $host['gateway'];
            }

            $productIds[$id] = $host['product_id'];

            if($upstreamProduct){
                $upstreamOrders[] = [
                    'supplier_id' => $upstreamProduct['supplier_id'],
                    'order_id' => 0,
                    'host_id' => $id,
                    'amount' => $amount,
                    'profit' => $profit,
                    'create_time' => time()
                ];
            }

        }

        $this->startTrans();

        try{
            # 续费记录
            $renewIds = [];

            $ProductModel = new ProductModel();

            foreach ($renewDatas as $renewData){

                $this->deleteHostUnpaidUpgradeOrder($renewData['host_id']);

                $productId = $renewData['product_id'];

                $hostName = $renewData['host_name'];

                unset($renewData['product_id'],$renewData['host_name']);

                $renew = $this->create($renewData);

                # 到期时间描述,应该和实际的有差异
                if ($host->status == 'Suspended' || time() >= $host->due_time){
                    $upData['due_time'] = time()+$dueTime;
                }else{
                    $upData['due_time'] = $host->due_time+$dueTime;
                }

                $product = $ProductModel->find($productId);

                $orderItemData = [
                    'host_id' => $renewData['host_id'],
                    'product_id' => $productId,
                    'type' => 'renew',
                    'rel_id' => $renew->id,
                    'amount' => $renewData['new_billing_cycle_amount'],
                    'description' => lang_plugins('host_renew_description',['{product_name}'=>$product['name'],'{name}'=>$hostName,'{billing_cycle_name}'=>$billingCycle,'{time}'=>date('Y/m/d H',$host->due_time) . '-' . date('Y/m/d H',$upData['due_time'])]),
                ];
                $orderItems[] = $orderItemData;

                $renewIds[] = $renew->id;
            }

            # 创建订单
            $data = [
                'type' => 'renew',
                'amount' => $total,
                'gateway' => $gateway,
                'client_id' => $clientId,
                'items' => $orderItems
            ];
            $OrderModel = new OrderModel();
            $orderId = $OrderModel->createOrderBase($data);

            if(!empty($upstreamOrders)){
                foreach ($upstreamOrders as $key => $value) {
                    $upstreamOrders[$key]['order_id'] = $orderId;
                }
                $UpstreamOrderModel = new UpstreamOrderModel();
                $UpstreamOrderModel->saveAll($upstreamOrders);
            }

            // 20230509 wyh
            if (!$maxRenew){
                $OrderItemModel = new OrderItemModel();
                $orderItem = $OrderItemModel->where('order_id',$host['order_id'])
                    ->where('host_id',$host['id'])
                    ->where('type','addon_promo_code')
                    ->find();
                $PromoCodeModel = new PromoCodeModel();
                $promoCode = $PromoCodeModel->find($orderItem['rel_id']);
                $param['customfield']['promo_code'] = (isset($param['customfield']['promo_code']) && !empty($param['customfield']['promo_code']))?$param['customfield']['promo_code']:($promoCode['code']??'');

            }

            $param['customfield']['max_renew'] = $maxRenew??false;

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

            update_upstream_order_profit($orderId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        # 金额从数据库重新获取,hook里可能会修改金额,wyh改 20220804
        $amount = $OrderModel->where('id',$orderId)->value('amount');

        # 记录日志
        $ProductModel = new ProductModel();
        $productDes = '';
        foreach ($productIds as $hid=>$pid){
            $product = $ProductModel->find($pid);
            $productDes .= "host#{$hid}#{$product['name']}#,";
        }
        if ($this->isAdmin){
            active_log(lang_plugins('renew_admin_renew', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{host}'=>rtrim($productDes,','), '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }else{
            active_log(lang_plugins('renew_client_renew', ['{client}'=>'user#'.get_client_id().'#'.request()->client_name.'#', '{host}'=>rtrim($productDes,','), '{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount, '{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_renew', $renew->id);
        }

        if ($amount>0){
            # 后台直接标记支付
            if ($this->isAdmin && isset($param['pay']) && intval($param['pay'])){
                $OrderModel->orderPaid(['id'=>$orderId]);
                return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
            }
        }else{

            foreach ($renewIds as $renewId){
                $this->renewHandle($renewId);
            }

            return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Paid'];
        }

        return ['status'=>200,'msg'=>lang_plugins('renew_success'),'code'=>'Unpaid','data'=>['id'=>$orderId]];
    }

    # 支付后续费处理
    public function renewHandle($id)
    {
        $renew = $this->find($id);

        if (empty($renew)){
            return false;
        }

        if ($renew->status == 'Completed'){
            return false;
        }

        $amount = $renew->new_billing_cycle_amount;

        $dueTime = $renew->new_billing_cycle_time;

        $billingCycle = $renew->new_billing_cycle;

        $HostModel = new HostModel();
        $host = $HostModel->find($renew->host_id);

        $this->startTrans();

        try{

            $upData = [
                'renew_amount' => $amount,
                'billing_cycle_name' => $billingCycle,
                'billing_cycle_time' => $dueTime,
                'update_time' =>time()
            ];
            # 更改到期时间
            if ($host->status == 'Suspended' || time() >= $host->due_time){
                $upData['due_time'] = time()+$dueTime;
            }else{
                $upData['due_time'] = $host->due_time+$dueTime;
            }
            $host->save($upData);

            $renew->save([
                'status' => 'Completed'
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return false;
        }

        $ModuleLogic = new ModuleLogic();

        # 解除产品暂停
        if ($host->status == 'Suspended'){
            $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $result = $ResModuleLogic->suspendAccount($host);
            }else{
                $result = $ModuleLogic->unsuspendAccount($host);
            }

            if ($result['status']==200){
                $host->save([
                    'status' => 'Active'
                ]);
            }
            //产品续费短信添加到任务队列
            add_task([
                'type' => 'sms',
                'description' => lang_plugins('host_renew_send_sms'),
                'task_data' => [
                    'name'=>'host_renew',//发送动作名称
                    'host_id'=>$renew->host_id,//产品ID
                    'template_param'=>[
                        'id' => $renew->host_id,//产品ID
                    ],
                ],
            ]);
            //产品续费邮件添加到任务队列
            add_task([
                'type' => 'email',
                'description' => lang_plugins('host_renew_send_mail'),
                'task_data' => [
                    'name'=>'host_renew',//发送动作名称
                    'host_id'=>$renew->host_id,//产品ID
                    'template_param'=>[
                        'id' => $renew->host_id,//产品ID
                    ],
                ],
            ]);     
        }

        # 记录日志

        # 调模块
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        if ($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->renew($host);
        }else{
            $result = $ModuleLogic->renew($host);
        }

        upstream_sync_host($host['id'], 'host_renew');


        # 任务队列

        return true;
    }

    # 实现产品列表后按钮模板钩子
    public function templateClientAfterHostListButton($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host)){
            return '';
        }
        $clientId = get_client_id();
        if ($host->client_id != $clientId){
            return '';
        }

        # 产品已开通/已到期才可续费
        if (!in_array($host['status'],['Active','Suspended'])){
            return '';
        }

        # 产品周期一次性不可续费
        if ($host->billing_cycle == 'onetime'){
            return '';
        }

        $url = "console/v1/{$id}/renew";

        $button = lang_plugins('renew');
        # 续费按钮
        return "<a href=\"{$url}\" class=\"btn btn-primary h-100 custom-button text-white\">{$button}</a>";
    }

    # 删除产品未付款升降级订单
    private function deleteHostUnpaidUpgradeOrder($id)
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

    public function beforeHostRenewalFirst($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host)){
            return false;
        }

        $renewAuto = IdcsmartRenewAutoModel::where('host_id', $id)->find();
        if(empty($renewAuto)){
            return false;
        }
        if($renewAuto['status']!=1){
            return false;
        }

        $param = [
            'id' => $id,
            'billing_cycle' => $host['billing_cycle_name'],
            'auto_renew' => 1
        ];

        $res = $this->renew($param);
        if($res['status']==200){
            return ['status' => 200, 'msg' => lang_plugins('success_message'), 'data' => ['action' => 'auto_renew']];
        }else{
            return false;
        }
        
    }

    # 删除产品未支付的续费订单
    private function deleteUnpaidRenewOrder($id){
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

        return true;
    }

}
