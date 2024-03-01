<?php
namespace addon\idcsmart_refund\model;

use addon\idcsmart_refund\IdcsmartRefund;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpstreamProductModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-07-06
 */
class IdcsmartRefundModel extends Model
{
    protected $name = 'addon_idcsmart_refund';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'client_id'                        => 'int',
        'host_id'                          => 'int',
        'amount'                           => 'float',
        'suspend_reason'                   => 'string',
        'type'                             => 'string',
        'admin_id'                         => 'int',
        'create_time'                      => 'int',
        'status'                           => 'string',
        'reject_reason'                    => 'string',
        'update_time'                      => 'int',
    ];

    /**
     * 时间 2022-07-07
     * @title 停用列表
     * @desc 停用列表
     * @author wyh
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name
     * @param string param.sort - 升/降序 asc,desc
     * @param string param.keywords - 关键字搜索:停用原因,申请人
     * @return array list - 停用列表
     * @return int list[].id - ID
     * @return int list[].client_name - 申请人
     * @return int list[].product_name - 申请商品
     * @return float host.amount - 退款金额(amount==-1表示不需要退款)
     * @return int list[].type - 类型:一共四种类型，可退款的有：人工退款Artificial、自动退款Auto，不可退款的有：Expire到期停用、Immediate立即停用
     * @return int list[].admin_name - 审核人
     * @return int list[].create_time - 申请时间
     * @return int list[].due_time - 到期时间
     * @return int list[].status - 状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消
     * @return int count - 停用总数
     */
    public function refundList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'r.id';
        }
        $where = function (Query $query) use ($param){
            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('r.suspend_reason|c.username','like',"%{$param['keywords']}%");
            }
        };

        $refunds = $this->alias('r')
            ->field('r.client_id,r.host_id,r.id,r.suspend_reason,c.username as client_name,p.name as product_name,r.amount,r.type,a.name as admin_name,r.create_time,h.due_time,r.status,rp.type as refund_product_type')
            ->leftJoin('client c','c.id=r.client_id')
            ->leftJoin('host h','h.id=r.host_id')
            ->leftJoin('product p','p.id=h.product_id')
            ->leftJoin('admin a','a.id=r.admin_id')
            ->leftJoin('addon_idcsmart_refund_product rp','rp.product_id=p.id')
            ->withAttr('type',function ($value,$data){
                if ($data['amount']>=0){ # 可退款
                    return $data['refund_product_type'];
                }else{ # 不可退款
                    return $value;
                }
            })
            ->withAttr('admin_name',function ($value){
                if (is_null($value)){
                    return '';
                }
                return $value;
            })
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        foreach ($refunds as &$refund){
            unset($refund['refund_product_type']);
        }

        $count = $this->alias('r')
            ->leftJoin('client c','c.id=r.client_id')
            ->leftJoin('host h','h.id=r.host_id')
            ->leftJoin('product p','p.id=h.product_id')
            ->leftJoin('admin a','a.id=r.admin_id')
            ->leftJoin('addon_idcsmart_refund_product rp','rp.product_id=p.id')
            ->where($where)
            ->count();

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$refunds,'count'=>$count]];
    }

    /**
     * 时间 2022-07-08
     * @title 通过
     * @desc 通过
     * @author wyh
     * @version v1
     * @param int id - 停用申请ID required
     */
    public function pending($param)
    {
        $this->startTrans();

        try{
            $id = $param['id'];

            $refund = $this->find($id);

            if (empty($refund)){
                throw new \Exception(lang_plugins('refund_refund_is_not_exist'));
            }

            if ($refund->status != 'Pending'){
                throw new \Exception(lang_plugins('refund_refund_only_pending'));
            }

            $refund->save([
                'status' => 'Suspending',
                'admin_id' => get_admin_id(),
                'update_time' => time()
            ]);

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($refund['client_id']);
            active_log(lang_plugins('refund_pending_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$refund['amount'],'{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_refund', $id);
			
			$host = (new HostModel())->find($refund['host_id']);
			$product = (new ProductModel())->find($host['product_id']);
			//产品退款成功短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => lang_plugins('client_refund_success_send_sms'),
				'task_data' => [
					'name'=>'client_refund_success',//发送动作名称
					'client_id'=>$client['id'],//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
			//产品退款成功邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => lang_plugins('client_refund_success_send_mail'),
				'task_data' => [
					'name'=>'client_refund_success',//发送动作名称
					'client_id'=>$client['id'],//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-08
     * @title 驳回
     * @desc 驳回
     * @author wyh
     * @version v1
     * @param int id - 停用申请ID required
     * @param string reject_reason - 驳回原因 required
     */
    public function reject($param)
    {
        $this->startTrans();

        try{
            $id = $param['id'];

            $refund = $this->find($id);

            if (empty($refund)){
                throw new \Exception(lang_plugins('refund_refund_is_not_exist'));
            }

            if ($refund->status != 'Pending'){
                throw new \Exception(lang_plugins('refund_refund_only_pending'));
            }

            $refund->save([
                'status' => 'Reject',
                'reject_reason' => $param['reject_reason']??'',
                'admin_id' => get_admin_id(),
                'update_time' => time()
            ]);

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($refund['client_id']);
            active_log(lang_plugins('refund_reject_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{reason}'=>$refund['suspend_reason']]), 'addon_idcsmart_refund', $id);
			
			$host = (new HostModel())->find($refund['host_id']);
			$product = (new ProductModel())->find($host['product_id']);
			//产品退款驳回短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => lang_plugins('admin_refund_reject_send_sms'),
				'task_data' => [
					'name'=>'admin_refund_reject',//发送动作名称
					'client_id'=>$client['id'],//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
			//产品退款驳回邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => lang_plugins('admin_refund_reject_send_mail'),
				'task_data' => [
					'name'=>'admin_refund_reject',//发送动作名称
					'client_id'=>$client['id'],//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-08
     * @title 取消
     * @desc 取消
     * @author wyh
     * @version v1
     * @param int id - 停用申请ID required
     */
    public function cancel($param)
    {
        $this->startTrans();

        try{
            $id = $param['id'];

            $refund = $this->find($id);

            if (empty($refund)){
                throw new \Exception(lang_plugins('refund_refund_is_not_exist'));
            }

            if (!in_array($refund->status,['Pending','Suspending','Suspend'])){
                throw new \Exception(lang_plugins('refund_refund_only_pending_or_suspending'));
            }

            $refund->save([
                'status' => 'Cancelled',
                'admin_id' => get_admin_id()?:0,
                'update_time' => time()
            ]);

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($refund['client_id']);
            active_log(lang_plugins('refund_cancel_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client['id'].'#'.$client['username'].'#']), 'addon_idcsmart_refund', $id);
			
			
			$host = (new HostModel())->find($refund['host_id']);
			$product = (new ProductModel())->find($host['product_id']);
			//产品取消请求短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => lang_plugins('client_refund_cancel_send_sms'),
				'task_data' => [
					'name'=>'client_refund_cancel',//发送动作名称
					'client_id'=>$client['id'],//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
			//产品取消请求邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => lang_plugins('client_refund_cancel_send_mail'),
				'task_data' => [
					'name'=>'client_refund_cancel',//发送动作名称
					'client_id'=>$client['id'],//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-08
     * @title 停用页面
     * @desc 停用页面
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID required
     * @return int allow_refund - 是否允许退款:0否,1是
     * @return int reason_custom - 是否允许自定义原因:0否,1是
     * @return array reasons - 停用原因
     * @return int reasons[].id - 原因id
     * @return string reasons[].content - 内容
     * @return object host - 产品
     * @return int host.create_time - 订购时间
     * @return float host.first_payment_amount - 订购金额
     * @return float host.amount - 退款金额(amount==-1表示不需要退款)
     * @return array config_option - 产品配置
     */
    public function refundPage($param)
    {
        $hostId = intval($param['host_id']);

        $HostModel = new HostModel();
        $host = $HostModel->where('id',$hostId)
            ->where('client_id',get_client_id())
            ->find();
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('refund_host_is_not_exist')];
        }

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();
        $reasons = $IdcsmartRefundReasonModel->field('id,content')
            ->select()
            ->toArray();
        # 是否可自定义原因
        $IdcsmartRefund = new IdcsmartRefund();
        $config = $IdcsmartRefund->getConfig();

        $productId = $host->product_id;
        # 获取配置项,调模块
        $product = (new ProductModel())->find($productId);
        $upstreamProduct = UpstreamProductModel::where('product_id', $productId)->find();
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $configOption = $ResModuleLogic->allConfigOption($product);
        }else{
            $configOption = (new ModuleLogic())->allConfigOption($product);
        }


        if ($this->allowRefund($hostId)){ # 可退款
            $data = [
                'allow_refund' => 1,
                'reason_custom' => $config['reason_custom']??0,
                'config_option' => $configOption,
                'reasons' => $reasons,
                'host' => [
                    'create_time' => $host->create_time,
                    'first_payment_amount' => $host->first_payment_amount,
                    'amount' => $this->refundAmount($hostId) # 退款金额
                ],
            ];
        }else{
            $data = [
                'allow_refund' => 0,
                'reason_custom' => $config['reason_custom']??0,
                'config_option' => $configOption,
                'reasons' => $reasons,
                'host' => [
                    'create_time' => $host->create_time,
                ],
            ];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-07-08
     * @title 停用
     * @desc 停用
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID required
     * @param mixed suspend_reason - 停用原因,产品可以自定义原因时,输入框,传字符串;产品不可自定义原因时,传停用原因ID数组
     * @param string type - 停用时间:Expire到期,Immediate立即
     */
    public function refund($param)
    {
        $this->startTrans();

        try{
            $IdcsmartRefund = new IdcsmartRefund();
            $config = $IdcsmartRefund->getConfig();

            # 退款金额
            if ($param['type'] == 'Expire'){
                $amount = 0;
            }else{
                $amount = $this->allowRefund($param['host_id'])?$this->refundAmount($param['host_id']):-1;# -1表示不需要退款
            }

            # 产品是否人工审核
            $HostModel = new HostModel();
            $host = $HostModel->find($param['host_id']);
            $productId = $host->product_id;

            $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
            $refundProduct = $IdcsmartRefundProductModel->where('product_id',$productId)->find();

            if (isset($refundProduct['type']) && $refundProduct['type'] == 'Artificial'){
                $status = 'Pending';
            }else{
                $status = 'Suspending';
            }

            // wyh 20230511 退款商品且产品使用信用额未还款时
            $hookRes = hook_one('before_host_refund',['host_id'=>$param['host_id']]);
            if (!empty($refundProduct) && isset($hookRes['status']) && $hookRes['status']==400){
                throw new \Exception($hookRes['msg']);
            }

            $IdcsmartRefundModel = new IdcsmartRefundModel();
            $refunded = $IdcsmartRefundModel->where('host_id',$param['host_id'])
                ->whereNotIn('status',['Reject','Cancelled'])
                ->find();
            if (!empty($refunded)){
                throw new \Exception(lang_plugins('refund_product_refunded'));
            }

            # 停用原因
            if (isset($config['reason_custom']) && $config['reason_custom']==1){
                $suspendReason = $param['suspend_reason'];
            }else{
                $suspendReason = '';
                $suspendReasons = $param['suspend_reason'];
                $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();
                foreach ($suspendReasons as $item){
                    $refundReason = $IdcsmartRefundReasonModel->find($item);
                    $suspendReason .= $refundReason['content'] . "\n";
                }
                $suspendReason = rtrim($suspendReason,"\n");
            }

            $refund = $this->create([
                'client_id' => get_client_id(),
                'host_id' => $param['host_id'],
                'amount' => $amount,
                'suspend_reason' => $suspendReason?:'',
                'type' => $param['type'],
                'create_time' => time(),
                'status' => $status
            ]);

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($productId);
            active_log(lang_plugins('refund_refund_host', ['{client}'=>'client#'.get_client_id().'#'. request()->client_name .'#','{host}'=>'host#'.$param['host_id'].'#'.$product['name'].'#','{currency_prefix}'=>configuration('currency_prefix'),'{amount}'=>$amount,'{currency_suffix}'=>configuration('currency_suffix')]), 'addon_idcsmart_refund', $refund->id);
			//产品退款申请短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => lang_plugins('client_create_refund_send_sms'),
				'task_data' => [
					'name'=>'client_create_refund',//发送动作名称
					'client_id'=>get_client_id(),//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
			//产品退款申请邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => lang_plugins('client_create_refund_send_mail'),
				'task_data' => [
					'name'=>'client_create_refund',//发送动作名称
					'client_id'=>get_client_id(),//客户ID
					'template_param'=>[
						'product_name' => $product['name'].'-'.$host['name'],//产品名称
					],
				],		
			]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        //TODO 测试使用,后面删除
        $this->dailyCron();

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    # 判断产品是否可退款
    private function allowRefund($hostId)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        $productId = $host->product_id;

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
        $refundProduct = $IdcsmartRefundProductModel->where('product_id',$productId)->find();
        $allowRefund = false;

        if (!empty($refundProduct)){

            $OrderModel = new OrderModel();

            $condition1 = $condition2 = true;

            if ($refundProduct->require == 'First'){
                # 非首次订购
                $otherOrder = $OrderModel->where('client_id',get_client_id())->where('id','<>',$host->order_id)->find();
                if (!empty($otherOrder)){
                    $condition1 = false;
                }
            }elseif ($refundProduct->require == 'Same'){
                # 同商品非首次订购
                $otherOrder = $OrderModel->alias('o')
                    ->leftJoin('order_item oi','oi.order_id=o.id')
                    ->leftJoin('host h','h.id=oi.host_id')
                    ->where('oi.type','host')
                    ->where('h.product_id',$productId)
                    ->where('o.id','<>',$host->order_id)
                    ->find();
                if (!empty($otherOrder)){
                    $condition1 = false;
                }
            }

            # 购买后X天内
            if ($refundProduct->range_control && time()>($host->create_time+24*3600*$refundProduct->range)){
                $condition2 = false;
            }

            if ($condition1 && $condition2){
                $allowRefund = true;
            }
        }

        return $allowRefund;
    }

    # 计算产品退款金额
    private function refundAmount($hostId)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        $productId = $host->product_id;

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();
        $refundProduct = $IdcsmartRefundProductModel->where('product_id',$productId)->find();

        $dueTime = $host->due_time;
        $diffTime = $dueTime-time()>0?$dueTime-time():0;
        # 总周期时间
        $billingCycleTime = $dueTime - $host->active_time;

        # 计算基础金额
        $OrderItemModel = new OrderItemModel();
        $orderItem =  $OrderItemModel->where('host_id',$hostId)->where('type','host')->find();
        $baseAmount = $orderItem['amount'];

        # 插件修改金额
        $hookResults = hook('after_refund',['host_id'=>$hostId]);
        foreach ($hookResults as $hookResult){
            $baseAmount = bcadd($baseAmount,(float)$hookResult,2);
        }
        # 升降级金额(操作产品)
        $upgrades = $OrderItemModel->alias('oi')
            ->field('oi.order_id,oi.amount')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('oi.host_id',$hostId)
            ->where('oi.type','upgrade')
            ->where('o.status','Paid')
            ->select()
            ->toArray();
        $manualUpgradeOrderIds = [];
        foreach ($upgrades as $upgrade){
            $manualUpgradeOrderIds[] = $upgrade['order_id'];
            $baseAmount = bcadd($baseAmount,$upgrade['amount'],2);
        }

        # 升降级手动金额(是操作订单金额)
        $manualAmount = $OrderItemModel->whereIn('order_id',$manualUpgradeOrderIds)
            ->where('type','manual')
            ->sum('amount');
        $baseAmount = bcadd($baseAmount,$manualAmount,2);

        # 原订单手动金额(当原订单超过1个产品时,手动金额>0的不管)
        $count = $OrderItemModel->where('order_id',$orderItem['order_id'])->where('type','host')->count();
        if ($count>1){
            $manualAmount2 = $OrderItemModel->where('order_id',$orderItem['order_id'])
                ->where('type','manual')
                ->where('amount','<',0)
                ->sum('amount');
        }else{
            $manualAmount2 = $OrderItemModel->where('order_id',$orderItem['order_id'])
                ->where('type','manual')
                ->sum('amount');
        }
        $baseAmount = bcadd($baseAmount,$manualAmount2,2);

        if ($refundProduct->rule == 'Day'){ # 按天退款
            $day = floor($diffTime/(24*3600));

            $totalDay = $billingCycleTime / (24*3600);
            if ($totalDay>0){
                $amount = bcmul($baseAmount,bcdiv($day,$totalDay,20),2);
            }else{
                $amount = $baseAmount;
            }

        }elseif ($refundProduct->rule == 'Month'){ # 按月(30天)退款
            $month = floor($diffTime/(24*3600*30));

            $totalMonth = $billingCycleTime / (24*3600*30);

            if ($totalMonth>0){
                $amount = bcmul($baseAmount,bcdiv($month,$totalMonth,20),2);
            }else{
                $amount = $baseAmount;
            }

        }else{ # 按比例退款
            $amount = bcmul($baseAmount,$refundProduct->ratio_value/100,2);
        }

        return $amount>0?$amount:0;
    }

    # 退款停用按钮模板钩子
    public function templateAfterServicedetailSuspended($param)
    {
        $hostId = intval($param['host_id']??0);
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);

        if (empty($host)){
            return '';
        }

        $IdcsmartRefundModel = new IdcsmartRefundModel();
        $refund = $IdcsmartRefundModel->where('host_id',$hostId)
            ->order('id','desc')
            ->find();
        if (!empty($refund)){
            if ($refund->status == 'Reject'){ # 驳回显示:停用+驳回原因
                return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_suspend') ."</a>" . lang_plugins('refund_reject_reason') . ":{$refund->reject_reason})";
            }elseif ($refund->status == 'Cancelled'){ # 取消显示:停用按钮
                # 开通中/已开通 才显示“停用”按钮
                if (in_array($host->status,['Pending','Active'])){
                    return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_suspend') ."</a>";
                }
            }else{
                if ($refund->status == 'Pending'){
                    $status = lang_plugins('refund_pending');
                }elseif ($refund->status == 'Suspending'){
                    $status = lang_plugins('refund_suspending');
                }elseif ($refund->status == 'Suspend'){
                    $status = lang_plugins('refund_suspend_1');
                }elseif ($refund->status == 'Suspended'){
                    $status = lang_plugins('refund_suspended');
                }elseif ($refund->status == 'Refund'){ # 已退款
                    $status = lang_plugins('refund_refund');
                }else{
                    $status = '';
                }
                $html = "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". $status ."</a>";

                if ($refund->status == 'Suspending'){ # 待停用状态 + 取消停用按钮
                    $html .= "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_cancelled_button') ."</a>";
                }
                return $html;

            }
        }else{
            # 开通中/已开通 才显示“停用”按钮
            if (in_array($host->status,['Pending','Active'])){
                return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" >". lang_plugins('refund_suspend') ."</a>";
            }
        }

        return '';
    }

    # 实现每日一次定时任务钩子
    public function dailyCron()
    {
        $where = function (Query $query){
            $query->where('r.status','Suspending')
                ->where('r.type','Immediate');
        };

        $whereOr = function (Query $query){
            $query->where('r.status','Suspending')
                ->where('r.type','Expire')
                ->where('h.due_time','<=',time());
        };

        $refunds = $this->alias('r')
            ->field('r.id,r.host_id,r.client_id,r.amount,h.order_id')
            ->leftJoin('host h','h.id=r.host_id')
            #->where('h.id','>',0)
            ->where($where)
            ->whereOr($whereOr)
            ->select()
            ->toArray();
        $ModuleLogic = new ModuleLogic();
        $HostModel = new HostModel();
        foreach ($refunds as $refund){
            $host = $HostModel->find($refund['host_id']);
            if (empty($host)){ # 考虑产品被删除的情况
                $status = 'Suspended';
            }else{
                $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
                if($upstreamProduct){
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $result = $ResModuleLogic->terminateAccount($host);
                }else{
                    $result = $ModuleLogic->suspendAccount($host);
                }

                if ($result['status'] == 200){
                    if ($refund['amount'] == -1){ # 不需要退款
                        $status = 'Suspended'; # 已停用
                    }elseif ($refund['amount'] == 0){
                        $status = 'Refund'; # 已退款
                    }else{
                        # 退款金额大于0时,退款至用户余额
                        update_credit([
                            'type' => 'Refund',
                            'amount' => $refund['amount'],
                            'notes' => lang_plugins('refund_to_client_credit').'#'.$refund['order_id'],
                            'client_id' => $refund['client_id'],
                            'order_id' => $refund['order_id'],
                            'host_id' => $refund['host_id'],
                        ]);
                        $status = 'Refund'; # 已退款
                    }

                    $host->save([
                        'status' => 'Suspended',
                        'update_time' => time()
                    ]);

                }else{ # 模块删除未成功
                    $status = 'Suspend'; # 停用中
                }
            }
            # 更新停用申请
            $this->update([
                'status' => $status,
                'update_time' => time()
            ],['id'=>$refund['id']]);

            upstream_sync_host($refund['host_id'],'refund');
        }

        return true;
    }

    # 实现产品退款判断钩子
    public function hostRefund($param)
    {
        $id = $param['id']??0;

        $IdcsmartRefundModel = new IdcsmartRefundModel();

        $refund = $IdcsmartRefundModel->where('host_id',$id)->where('amount','>',0)->find();

        # 订单有退款
        if (!empty($refund)){
            $amount = $refund['amount'];
            return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['amount'=>$amount]];
        }else{ # 订单无退款
            return ['status'=>400,'msg'=>lang_plugins('fail_message')];
        }
    }

    /**
     * 时间 2022-08-11
     * @title 获取待审核金额
     * @desc 获取待审核金额
     * @author wyh
     * @version v1
     * @url /console/v1/refund/pending/amount
     * @method get
     * @return float amount - 退款待审核金额
     */
    public function pendingAmount()
    {
        $amount = $this->where('client_id',get_client_id())
            ->where('status','Pending')
            ->where('amount','>',0)
            ->sum('amount');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['amount'=>bcsub($amount,0,2)]];
    }

    /**
     * 时间 2022-08-11
     * @title 获取产品停用信息
     * @desc 获取产品停用信息
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     * @return object refund - 退款信息
     * @return int refund.id - 退款ID
     * @return float refund.amount - 退款金额:-1表示不需要退款
     * @return string refund.suspend_reason - 停用原因
     * @return string refund.type - 类型:Expire到期退款,Immediate立即退款
     * @return string refund.status - 状态:Pending待审核,Suspending待停用,Suspend停用中,Suspended已停用,Refund已退款,Reject审核驳回,Cancelled已取消
     * @return string refund.reject_reason - 驳回原因
     * @return int refund.create_time - 申请时间
     */
    public function hostRefundInfo($param)
    {
        $id = $param['id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->where('id',$id)->where('client_id',get_client_id())->find();
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('refund_host_is_not_exist')];
        }

        $refund = $this->field('id,amount,suspend_reason,type,status,reject_reason,create_time')
            ->where('host_id',$id)
            ->order('id','desc')
            ->find();

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['refund'=>$refund]];
    }

    /**
     * 时间 2022-08-23
     * @title 获取客户退款金额
     * @desc 获取客户退款金额
     * @author wyh
     * @version v1
     * @param int id - 客户ID required
     * @return float amount - 退款金额
     */
    public function clientRefundAmount($param)
    {
        $clientId = $param['id']??0;

        $ClientModel = new ClientModel();
        $client = $ClientModel->find($clientId);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang_plugins('client_is_not_exist')];
        }

        $amount = $this->where('client_id',$clientId)
            ->where('status','Refund')
            ->where('amount','>',0)
            ->sum('amount');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['amount'=>bcsub($amount,0,2)]];

    }
}
