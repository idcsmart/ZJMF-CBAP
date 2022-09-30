<?php
namespace addon\idcsmart_renew\model;

use app\common\logic\ModuleLogic;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
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
    private function cyclesFilter(HostModel $host,$cycles)
    {
        foreach ($cycles as $k1=>$item1){
            # 未设置参数,清除此周期
            if (!isset($item1['duration']) || !isset($item1['billing_cycle']) || !isset($item1['price'])){
                unset($cycles[$k1]);
            }
        }

        foreach ($cycles as $k2=>$item2){
            # 产品对应周期(只能用时间比较)
            if ($host->billing_cycle_time == $item2['duration']){
                # 产品续费金额大于模块金额
                if ($host->renew_amount > $item2['price']){
                    $max = $item2['duration'];
                }
                # 产品当前周期的价格以 表里数据为准
                $cycles[$k2]['price'] = bcsub((float)$host->renew_amount,0,2);
            }
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
        $result = $ModuleLogic->durationPrice($host);
        if ($result['status'] != 200){
            return ['status'=>400,'msg'=>$result['msg']?:lang_plugins('get_fail')];
        }

        # 处理可续费周期
        $cycles = $result['data']?:[];
        $cycles = $this->cyclesFilter($host,$cycles);

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
        $result = $ModuleLogic->durationPrice($host);
        $cycles = $result['status'] == 200 ? $result['data'] :[];
        # 可续费周期
        $cycles = $this->cyclesFilter($host,$cycles);

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
                $amount = $value['price'];
                $dueTime = $value['duration'];
                break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
            }
        }

        # 订单子项
        $orderItems = [];

        $this->startTrans();

        try{
            $this->deleteHostUnpaidUpgradeOrder($id);
            # 续费记录
            $renew = $this->create([
                'client_id' => $clientId,
                'host_id' => $id,
                'new_billing_cycle' => $billingCycle,
                'new_billing_cycle_time' => $dueTime,
                'new_billing_cycle_amount' => $amount,
                'status' => 'Pending',
                'create_time' => time()
            ]);

            $orderItems[] = [
                'host_id' => $id,
                'product_id' => $host['product_id'],
                'type' => 'renew',
                'rel_id' => $renew->id,
                'amount' => $amount,
                'description' => lang_plugins('host_renew'),
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

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

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
            # 后台直接标记支付
            if ($this->isAdmin && isset($param['pay']) && intval($param['pay'])){
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
            ->field('h.id,h.product_id,p.name product_name,h.name,h.active_time,h.due_time,h.first_payment_amount,h.billing_cycle,h.status,h.billing_cycle_time,h.renew_amount')
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
            $result = $ModuleLogic->durationPrice($host);

            $cycles = isset($result['status']) && $result['status'] == 200 ? $result['data'] :[];

            # 可续费周期
            $cycles = $this->cyclesFilter($host,$cycles);

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
            $result = $ModuleLogic->durationPrice($host);

            $cycles = $result['status'] == 200 ? $result['data'] :[];

            # 可续费周期
            $cycles = $this->cyclesFilter($host,$cycles);

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
                    $dueTime = $value['duration']??0;
                    break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
                }
            }
            # 获取自定义的金额
            $amount = (isset($amountCustom[$id]) && $this->isAdmin)?(float)$amountCustom[$id]:(float)$amount;

            $total = bcadd($total,$amount,2);

            $renewData = [
                'client_id' => $clientId,
                'host_id' => $id,
                'product_id' => $host['product_id'],
                'new_billing_cycle' => $billingCycle,
                'new_billing_cycle_time' => $dueTime??0,
                'new_billing_cycle_amount' => $amount,
                'status' => 'Pending',
                'create_time' => time()
            ];
            $renewDatas[] = $renewData;

            # 默认取第一个产品的支付方式
            if (!isset($gateway)){
                $gateway = $host['gateway'];
            }

            $productIds[$id] = $host['product_id'];

        }

        $this->startTrans();

        try{
            # 续费记录
            $renewIds = [];
            foreach ($renewDatas as $renewData){

                $this->deleteHostUnpaidUpgradeOrder($renewData['host_id']);

                $productId = $renewData['product_id'];

                unset($renewData['product_id']);

                $renew = $this->create($renewData);

                $orderItemData = [
                    'host_id' => $renewData['host_id'],
                    'product_id' => $productId,
                    'type' => 'renew',
                    'rel_id' => $renew->id,
                    'amount' => $renewData['new_billing_cycle_amount'],
                    'description' => lang_plugins('host_renew'),
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

            hook('after_order_create',['id'=>$orderId,'customfield'=>$param['customfield']??[]]);

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
            if ($host->status == 'Suspended'){
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
            $result = $ModuleLogic->unsuspendAccount($host);
            if ($result['status']==200){
                $host->save([
                    'status' => 'Active'
                ]);
            }
            # 任务队列
            # 发送邮件
        }

        # 记录日志

        # 调模块
        $result = $ModuleLogic->renew($host);

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

}
