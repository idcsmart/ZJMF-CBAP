<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\Exception;
use think\Model;
use think\Db;
use think\db\Query;

/**
 * @title 退款记录模型
 * @desc 退款记录模型
 * @use app\common\model\RefundRecordModel
 */
class RefundRecordModel extends Model
{
	protected $name = 'refund_record';

	// 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'order_id'              => 'int',
        'client_id'             => 'int',
        'admin_id'              => 'int',
        'type'      	        => 'string',
        'transaction_id'        => 'int',
        'amount'                => 'float',
        'create_time'           => 'int',
        'status'                => 'string',
        'reason'                => 'string',
        'refund_time'           => 'int',
        'gateway'               => 'string',
    ];

    /**
     * 时间 2023-01-29
     * @title 退款记录列表
     * @desc 退款记录列表
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
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
    public function refundRecordList($param)
    {
        $param['id'] = intval($param['id'] ?? 0);
        if(empty($param['id'])){
            return ['list' => [], 'count' => 0];
        }
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $where = function (Query $query) use($param) {
            if(!empty($param['id'])){
                $query->where('a.order_id', $param['id']);
            }
        };

        $count = $this->alias('a')
            ->field('a.id')
            ->leftJoin('admin b', 'a.admin_id=b.id')
            ->where($where)
            ->count();
        $list = $this->alias('a')
            ->field('a.id,a.create_time,a.amount,a.admin_id,b.name admin_name,a.type,a.status,a.reason,a.refund_time,a.gateway')
            ->leftJoin('admin b', 'a.admin_id=b.id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['amount'] = amount_format($value['amount']); // 处理金额格式
        }    

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2023-01-29
     * @title 删除退款记录
     * @desc 删除退款记录
     * @author theworld
     * @version v1
     * @param int id - 退款记录ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteRefundRecord($id)
    {
        // 验证ID
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        $this->startTrans();
        try {

            # 记录日志
            active_log(lang('admin_delete_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$record->id]), 'refund_record', $record->id);

            $this->destroy($id);

            $refundAmount = $this->where('order_id', $record['order_id'])
                ->where('status','Refunded')
                ->sum('amount');

            OrderModel::update([
                'refund_amount' => $refundAmount,
                'update_time' => time(),
            ], ['id' => $record['order_id']]);

            // 只有已退款状态才处理
            if ($record['status']=='Refunded'){
                if($record['type']=='transaction'){
                    // 删除交易流水
                    TransactionModel::destroy($record['transaction_id']);
                }elseif ($record['type']=='original'){
                    throw new \Exception(lang('fail_message'));
                } else{
                    $client = ClientModel::find($record['client_id']);
                    if ($client['credit']>=$record['amount']){
                        update_credit([
                            'type' => 'Artificial',
                            'amount' => -$record['amount'],
                            'notes' => lang('order_cancel_refund', ['{id}' => $record['order_id']]),
                            'client_id' => $record['client_id'],
                            'order_id' => $record['order_id'],
                            'host_id' => 0
                        ]);
                    }else{
                        throw new \Exception(lang('client_credit_is_not_enough'));
                    }

                }
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2024-05-10
     * @title 退款通过
     * @desc 退款通过
     * @author wyh
     * @version v1
     * @param int id - 退款记录ID required
     */
    public function pendingRefundRecord($id)
    {
        // 验证ID
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        if ($record['status']!='Pending'){
            return ['status'=>400,'msg'=>lang('refund_record_pending')];
        }

        $this->startTrans();
        try {
            $OrderModel = new OrderModel();
            $order = $OrderModel->find($record['order_id']);

            $amount = TransactionModel::where('order_id', $record['order_id'])->sum('amount');
            $refundAmount = RefundRecordModel::where('order_id', $record['order_id'])
                ->whereIn('status',['Refunded'])
                ->where('type', 'credit')
                ->sum('amount');

            $amount = $amount-$refundAmount;
            if($record['amount']>$amount){
                throw new \Exception(lang('refund_amount_not_enough'));
            }
            if($record['amount']>($order['amount']-$order['refund_amount'])){
                throw new \Exception(lang('refund_amount_not_enough'));
            }
            // 自动退款
            if ($record['type']=='original'){
                $tran = TransactionModel::where('order_id', $record['order_id'])->find();
                $refundData = [
                    'transaction_number' => $tran['transaction_number'],
                    'amount' => $record['amount'],
                    'out_request_no' => time() . rand_str(8,'NUMBER'), // 退款请求号：标识一次退款请求，需要保证在交易号下唯一，如需部分退款，则此参数必传
                    'total_fee' => $tran['amount'], // 总金额
                ];
                $refundResult = plugin_reflection($order['gateway'],$refundData,'gateway','handle_refund');
                if (isset($refundResult['status']) && $refundResult['status']==200){
                    $refundTransactionNumber = $refundResult['data']['trade_no']??"";
                    if (!empty($refundTransactionNumber)){
                        $transaction = TransactionModel::create([
                            'order_id' => $record['order_id'],
                            'client_id' => $order['client_id'],
                            'amount' => -$record['amount'],
                            'gateway' => $record['gateway'],
                            'gateway_name' => $gateway['title'] ?? '',
                            'transaction_number' => $refundTransactionNumber,
                            'create_time' => time()
                        ]);
                        $record->save([
                            'status' => 'Refunded',
                            'transaction_id' => $transaction['id'],
                            'refund_time' => time()
                        ]);
                        $refundAmount = $this->where('order_id', $record['order_id'])
                            ->where('status','Refunded')
                            ->sum('amount');
                        $order->save([
                            'refund_amount' => $refundAmount,
                            'status' => 'Refunded',
                            'update_time' => time(),
                        ]);

                        hook('after_order_refund',['id'=>$record['order_id']]);

                        active_log(lang("order_orginal_refund_success",['{transaction_number}'=>$refundTransactionNumber]), 'order', $order->id);
                    }else{
                        throw new \Exception(lang("gateway_return_error_other"));
                    }
                }else{
                    throw new \Exception((lang('gateway_return_error').$refundResult['msg'])??lang('gateway_not_exist'));
                }
            }elseif ($record['type']=='transaction'){
                $record->save([
                    'status' => 'Refunding',
                ]);
            }else {
                // 退余额不需要交易流水
                update_credit([
                    'type' => 'Refund',
                    'amount' => $record['amount'],
                    'notes' => lang('order_refund', ['{id}' => $record['order_id']]),
                    'client_id' => $record['client_id'],
                    'order_id' => $record['order_id'],
                    'host_id' => 0
                ]);
                $record->save([
                    'status' => 'Refunded',
                    'transaction_id' => 0,
                    'refund_time' => time()
                ]);
                $refundAmount = $this->where('order_id', $record['order_id'])
                    ->where('status','Refunded')
                    ->sum('amount');
                $order->save([
                    'refund_amount' => $refundAmount,
                    'status' => 'Refunded',
                    'update_time' => time(),
                ]);

                hook('after_order_refund',['id'=>$record['order_id']]);

                $client = ClientModel::find($order['client_id']);
                if(empty($client)){
                    $clientName = '#'.$order['client_id'];
                }else{
                    $clientName = 'client#'.$client->id.'#'.$client->username.'#';
                }

                active_log(lang('admin_refund_user_order_credit', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$record['amount']]), 'order', $order->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            active_log(lang('order_orginal_refund_fail').$e->getMessage(), 'order', $order->id);
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-05-10
     * @title 退款拒绝
     * @desc 退款拒绝
     * @author wyh
     * @version v1
     * @param int id - 退款记录ID required
     * @param string reason - 拒绝原因 required
     */
    public function rejectRefundRecord($param)
    {
        // 验证ID
        $record = $this->find($param['id']);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        if ($record['status']!='Pending'){
            return ['status'=>400,'msg'=>lang('refund_record_pending_reject')];
        }

        $this->startTrans();
        try {

            # 记录日志
            active_log(lang('admin_reject_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$record->id]), 'refund_record', $record->id);

            $record->save([
                'status' => 'Reject',
                'reason' => $param['reason']
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2024-05-10
     * @title 已退款
     * @desc 已退款
     * @author wyh
     * @version v1
     * @param int id - 退款记录ID required
     * @param string transaction_number - 交易流水ID required
     */
    public function redundedRefundRecord($param)
    {
        // 验证ID
        $id = $param['id']??0;
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        if ($record['status']!='Refunding'){
            return ['status'=>400,'msg'=>lang('refund_record_pending_refunding')];
        }

        $this->startTrans();
        try {

            # 记录日志
            active_log(lang('admin_refunded_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$record->id]), 'refund_record', $record->id);

            if ($record['type']=='transaction'){
                $OrderModel = new OrderModel();
                $order = $OrderModel->find($record['order_id']);

                $amount = TransactionModel::where('order_id', $record['order_id'])->sum('amount');
                $refundAmount = RefundRecordModel::where('order_id', $record['order_id'])
                    ->whereIn('status',['Refunded'])
                    ->where('type', 'credit')
                    ->sum('amount');

                $amount = $amount-$refundAmount;
                if($record['amount']>$amount){
                    throw new \Exception(lang('refund_amount_not_enough'));
                }
                if($record['amount']>($order['amount']-$order['refund_amount'])){
                    throw new \Exception(lang('refund_amount_not_enough'));
                }
                if (!isset($param['transaction_number']) || empty($param['transaction_number'])){
                    throw new \Exception(lang('param_error'));
                }
                // 获取支付接口名称
                $gateway = PluginModel::where('module', 'gateway')->where('name', $record['gateway'])->find();
                if(empty($gateway)){
                    throw new \Exception(lang('gateway_is_not_exist'));
                }
                $gateway['config'] = json_decode($gateway['config'],true);
                $gateway['title'] =  (isset($gateway['config']['module_name']) && !empty($gateway['config']['module_name']))?$gateway['config']['module_name']:$gateway['title'];

                $transaction = TransactionModel::create([
                    'order_id' => $record['order_id'],
                    'client_id' => $record['client_id'],
                    'amount' => -$record['amount'],
                    'gateway' => $record['gateway'],
                    'gateway_name' => $gateway['title'] ?? '',
                    'transaction_number' => $param['transaction_number'],
                    'create_time' => time()
                ]);
                $record->save([
                    'status' => 'Refunded',
                    'transaction_id' => $transaction->id,
                    'refund_time' => time()
                ]);
                $refundAmount = $this->where('order_id', $record['order_id'])
                    ->where('status','Refunded')
                    ->sum('amount');
                $order->save([
                    'refund_amount' => $refundAmount,
                    'status' => 'Refunded',
                    'update_time' => time(),
                ]);

                hook('after_order_refund',['id'=>$record['order_id']]);

                $client = ClientModel::find($order['client_id']);
                if(empty($client)){
                    $clientName = '#'.$order['client_id'];
                }else{
                    $clientName = 'client#'.$client->id.'#'.$client->username.'#';
                }

                active_log(lang('admin_refund_user_order_transaction', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{order}'=>'#'.$order->id, '{amount}'=>$record['amount'], '{transaction}'=>$param['transaction_number']]), 'order', $order->id);

            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }
}
