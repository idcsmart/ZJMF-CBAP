<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ClientModel;
use app\admin\model\PluginModel;
use app\common\model\TransactionModel;

/**
 * @title 提现模型
 * @desc 提现模型
 * @use addon\idcsmart_withdraw\model\IdcsmartWithdrawModel
 */
class IdcsmartWithdrawModel extends Model
{
    protected $name = 'addon_idcsmart_withdraw';

    // 设置字段信息
    protected $schema = [
        'id'                                => 'int',
        'source'                            => 'int',
        'amount'                            => 'float',
        'method'                            => 'string',
        'addon_idcsmart_withdraw_method_id' => 'int',
        'card_number'                       => 'string',
        'name'                              => 'string',
        'account'                           => 'string',
        'notes'                             => 'string',
        'client_id'                         => 'int',
        'status'                            => 'int',
        'reason'                            => 'string',
        'admin_id'                          => 'int',
        'fee'                               => 'float',
        'transaction_id'                    => 'int',
        'create_time'                       => 'int',
        'update_time'                       => 'int',
    ];

    # 提现列表
    public function idcsmartWithdrawList($param, $app = 'admin')
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        #$param['source'] = $param['source'] ?? 'credit';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aiw.'.$param['orderby'] : 'aiw.id';
        $param['start_time'] = $param['start_time'] ?? 0;
        $param['end_time'] = $param['end_time'] ?? 0;


        $plugin = PluginModel::select()->toArray();
        $plugin = array_column($plugin, 'title', 'name');

    	$count = $this->alias('aiw')
            ->field('aiw.id')
            ->leftJoin('client c', 'c.id=aiw.client_id')
            ->where(function ($query) use($param, $app) {
                if($app=='home'){
                    $clientId = get_client_id();
                    $query->where('aiw.client_id', $clientId);
                }
                if(in_array($param['status'], ['0','1','2','3'])){
                    $query->where('aiw.status', $param['status']);
                }
                if(isset($param['source']) && !empty($param['source'])){
                    $query->where('aiw.source', $param['source']);
                }
                if(!empty($param['keywords'])){
                    $query->where('c.username', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['start_time']) && !empty($param['end_time'])){
                    $query->where('aiw.create_time', '>=', $param['start_time'])->where('aiw.create_time', '<=', $param['end_time']);
                }
            })
            ->count();
        $list = $this->alias('aiw')
            ->field('aiw.id,aiw.amount,aiw.fee,aiwm.name method,aiw.card_number,aiw.name,aiw.account,aiw.status,aiw.reason,aiw.create_time,c.username,c.company,aiw.source,t.transaction_number')
            ->leftJoin('client c', 'c.id=aiw.client_id')
            ->leftJoin('transaction t', 't.id=aiw.transaction_id')
            ->leftJoin('addon_idcsmart_withdraw_method aiwm', 'aiwm.id=aiw.addon_idcsmart_withdraw_method_id')
            ->where(function ($query) use($param, $app) {
                if($app=='home'){
                    $clientId = get_client_id();
                    $query->where('aiw.client_id', $clientId);
                }
                if(in_array($param['status'], ['0','1','2','3'])){
                    $query->where('aiw.status', $param['status']);
                }
                if(!empty($param['source'])){
                    $query->where('aiw.source', $param['source']);
                }
                if(!empty($param['keywords'])){
                    $query->where('c.username', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['start_time']) && !empty($param['end_time'])){
                    $query->where('aiw.create_time', '>=', $param['start_time'])->where('aiw.create_time', '<=', $param['end_time']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        $credit = lang_plugins('withdraw_source_credit');
        foreach ($list as $key => $value) {
            if($value['source']=='credit'){
                $list[$key]['source'] = $credit;
            }else{
                $list[$key]['source'] = $plugin[$value['source']] ?? $value['source'];
            }
            $list[$key]['withdraw_amount'] = amount_format($value['amount']-$value['fee']);
            $list[$key]['amount'] = amount_format($value['amount']);
            $list[$key]['fee'] = amount_format($value['fee']);
            if($app=='home'){
                unset($list[$key]['card_number'],$list[$key]['name'],$list[$key]['account'],$list[$key]['username'],$list[$key]['company'],$list[$key]['source'],$list[$key]['transaction_number']);
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    # 提现审核
    public function idcsmartWithdrawAudit($param)
    {
        // 验证提现ID
        $idcsmartWithdraw = $this->find($param['id']);
        if(empty($idcsmartWithdraw)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_is_not_exist')];
        }

        if ($idcsmartWithdraw['status'] != 0){
            return ['status' => 400, 'msg' => lang_plugins('cannot_repeat_opreate')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'status' => $param['status'],
                'reason' => $param['reason'] ?? '',
                'admin_id' => $adminId,
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 提现审核通过钩子
            if($param['status']==1){
                if($idcsmartWithdraw['source']=='credit'){
                    $result = update_credit([
                        'type' => 'Withdraw',
                        'amount' => -$idcsmartWithdraw['amount'],
                        'notes' => lang_plugins('credit_withdraw'),
                        'client_id' => $idcsmartWithdraw['client_id'],
                    ]);
                    if(!$result){
                        throw new \Exception(lang_plugins('insufficient_credit_deduction_failed'));           
                    }
                }
            }

            $client = ClientModel::find($idcsmartWithdraw['client_id']);
            if($param['status']==1){
                # 记录日志
                active_log(lang_plugins('admin_pass_client_withdraw', ['{admin}'=>request()->admin_name,'{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{amount}'=>configuration("currency_prefix").$idcsmartWithdraw['amount'].configuration("currency_suffix")]), 'addon_idcsmart_withdraw', $idcsmartWithdraw->id);
            }else{
                # 记录日志
                active_log(lang_plugins('admin_reject_client_withdraw', ['{admin}'=>request()->admin_name,'{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{reason}'=>$param['reason'] ?? '']), 'addon_idcsmart_withdraw', $idcsmartWithdraw->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        if($param['status']==1){
            $params = [
                'id' => $idcsmartWithdraw['id'], 
                'source' => $idcsmartWithdraw['source'], 
                'amount' => $idcsmartWithdraw['amount'], 
                'fee' => $idcsmartWithdraw['fee'],
                'addon_idcsmart_withdraw_method_id' => $idcsmartWithdraw['addon_idcsmart_withdraw_method_id'],
                'card_number' => $idcsmartWithdraw['card_number'],
                'name' => $idcsmartWithdraw['name'],
                'account' => $idcsmartWithdraw['account'],
                'client_id' => $idcsmartWithdraw['client_id'],
                'create_time' => $idcsmartWithdraw['create_time'],
            ];
            hook('after_idcsmart_withdraw_pass', $params);
        }else{
            $params = [
                'id' => $idcsmartWithdraw['id'], 
                'source' => $idcsmartWithdraw['source'], 
                'amount' => $idcsmartWithdraw['amount'], 
                'fee' => $idcsmartWithdraw['fee'],
                'addon_idcsmart_withdraw_method_id' => $idcsmartWithdraw['addon_idcsmart_withdraw_method_id'],
                'card_number' => $idcsmartWithdraw['card_number'],
                'name' => $idcsmartWithdraw['name'],
                'account' => $idcsmartWithdraw['account'],
                'client_id' => $idcsmartWithdraw['client_id'],
                'create_time' => $idcsmartWithdraw['create_time'],
            ];
            hook('after_idcsmart_withdraw_reject', $params);
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }

    # 申请提现
    public function idcsmartWithdraw($param)
    {
        $clientId = get_client_id();

        $client = ClientModel::find($clientId);
        if(empty($client)){
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }

        $plugin = PluginModel::select()->toArray();
        $plugin = array_column($plugin, 'name');

        if(!in_array($param['source'], $plugin) && $param['source']!='credit'){
            return ['status' => 400, 'msg' => lang_plugins('source_is_not_exist')];
        }

        // 验证提现ID
        if($param['source']=='credit'){
            $idcsmartWithdrawRule = IdcsmartWithdrawRuleModel::where('source', $param['source'])->find();
            if(empty($idcsmartWithdrawRule)){
                return ['status'=>400, 'msg'=>lang_plugins('withdraw_rule_is_not_exist')];
            }

            $idcsmartWithdrawRule['method'] = array_filter(explode(',', $idcsmartWithdrawRule['method']));
            if(!in_array($param['method_id'], $idcsmartWithdrawRule['method'])){
                return ['status'=>400, 'msg'=>lang_plugins('withdraw_method_is_not_exist')];
            }
            $idcsmartWithdrawMethod = IdcsmartWithdrawMethodModel::find($param['method_id']);
            if(empty($idcsmartWithdrawMethod)){
                return ['status'=>400, 'msg'=>lang_plugins('withdraw_method_is_not_exist')];
            }


            if($param['amount']<$idcsmartWithdrawRule['min']){
                return ['status'=>400, 'msg'=>lang_plugins('withdraw_amount_less_than_min')];
            }

            if(!empty($idcsmartWithdrawRule['max']) && $idcsmartWithdrawRule['max']>0){
                if($param['amount']>$idcsmartWithdrawRule['max']){
                    return ['status'=>400, 'msg'=>lang_plugins('withdraw_amount_more_than_max')];
                }
            }

            if(!empty($idcsmartWithdrawRule['cycle_limit'])){
                if($idcsmartWithdrawRule['cycle']=='day'){
                    $start = mktime(0,0,0,date("m"),date("d"),date("Y"));
                    $end = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
                }else if($idcsmartWithdrawRule['cycle']=='week'){
                    $day = date('w');
                    if($day==0){
                        $day = 7;
                    }
                    $start = strtotime(date("Y-m-d"))-24*3600*($day-1);
                    $end = strtotime(date("Y-m-d"))+24*3600*(8-$day);
                }else{
                    $allday = date("t");
                    $start = strtotime("Y-m-1");
                    $end = strtotime("Y-m-".$allday)+24*3600;
                }
                $count = $this->where('client_id', $clientId)->where('create_time', '>=', $start)->where('create_time', '<', $end)->count();
                if($count>=$idcsmartWithdrawRule['cycle_limit']){
                    if($idcsmartWithdrawRule['cycle']=='day'){
                        return ['status'=>400, 'msg'=>lang_plugins('this_day_withdrawals_have_been_exhausted')];
                    }else if($idcsmartWithdrawRule['cycle']=='week'){
                        return ['status'=>400, 'msg'=>lang_plugins('this_week_withdrawals_have_been_exhausted')];
                    }else{
                        return ['status'=>400, 'msg'=>lang_plugins('this_month_withdrawals_have_been_exhausted')];
                    }
                    
                }
            }
            if($param['amount']>$client['credit']){
                return ['status'=>400, 'msg'=>lang_plugins('insufficient_balance')];
            }
        }
        
        $this->startTrans();
        try {
            if($param['source']=='credit'){
                if($idcsmartWithdrawRule['withdraw_fee_type']=='fixed'){
                    $fee = !empty($idcsmartWithdrawRule['withdraw_fee']) ? $idcsmartWithdrawRule['withdraw_fee'] : 0;
                }else{
                    if(!empty($idcsmartWithdrawRule['percent'])){
                        if(!empty($idcsmartWithdrawRule['percent_min'])){
                            $fee = bcdiv($param['amount']*$idcsmartWithdrawRule['percent'], 100, 2);

                            if($fee<$idcsmartWithdrawRule['percent_min']){
                                $fee = $idcsmartWithdrawRule['percent_min'];
                            }
                        }else{
                            $fee = bcdiv($param['amount']*$idcsmartWithdrawRule['percent'], 100, 2);
                        }
                    }
                    
                }
                $status = $idcsmartWithdrawRule['process']=='auto' ? 1 : 0;
            }else{
                $fee = $param['fee'] ?? 0;
                $status = 0;
            }

            $idcsmartWithdraw = $this->create([
                'source' => $param['source'],
                'amount' => $param['amount'],
                'addon_idcsmart_withdraw_method_id' => $param['method_id'],
                'card_number' => $param['card_number'] ?? '',
                'name' => $param['name'] ?? '',
                'account' => $param['account'] ?? '',
                'client_id' => $clientId,
                'fee' => $fee ?? 0,
                'status' => $status,
                'create_time' => time()
            ]);

            if($param['source']=='credit'){
                # 提现审核通过钩子
                if($idcsmartWithdrawRule['process']=='auto'){
                    if($param['source']=='credit'){
                        $result = update_credit([
                            'type' => 'Withdraw',
                            'amount' => -$param['amount'],
                            'notes' => lang_plugins('credit_withdraw'),
                            'client_id' => $clientId,
                        ]);
                        if(!$result){
                            throw new \Exception(lang_plugins('insufficient_credit_deduction_failed'));           
                        }
                    }
                }
            }
            if($param['source']=='credit'){
                $source = lang_plugins('withdraw_source_credit');
            }else{
                $plugin = PluginModel::where('name', $param['source'])->find();
                $source = $plugin['title'] ?? '';
            }

            # 记录日志
            active_log(lang_plugins('log_client_withdraw', ['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{source}'=>$source,'{amount}'=>configuration("currency_prefix").$param['amount'].configuration("currency_suffix")]), 'client', $client->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        if($param['source']=='credit'){
            if($idcsmartWithdrawRule['process']=='auto'){
                $params = [
                    'id' => $idcsmartWithdraw->id, 
                    'source' => $param['source'], 
                    'amount' => $param['amount'], 
                    'fee' => $fee,
                    'addon_idcsmart_withdraw_method_id' => $param['method_id'],
                    'card_number' => $param['card_number'] ?? '',
                    'name' => $param['name'] ?? '',
                    'account' => $param['account'] ?? '',
                    'client_id' => $clientId,
                    'create_time' => $idcsmartWithdraw->create_time,
                ];
                hook('after_idcsmart_withdraw_pass', $params);
            }
        }

        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }

    # 用户已提现金额
    public function idcsmartWithdrawClient($id)
    {
        $client = ClientModel::find($id);
        if(empty($client)){
            return '0.00';
        }
        $amount = $this->where('status', 1)->where('client_id', $id)->sum('amount');
        return amount_format($amount);
    }

    # 确认已汇款
    public function idcsmartWithdrawConfirmRemit($param)
    {
        // 验证提现ID
        $idcsmartWithdraw = $this->find($param['id']);
        if(empty($idcsmartWithdraw)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_is_not_exist')];
        }

        if ($idcsmartWithdraw['status'] == 3){
            return ['status' => 400, 'msg' => lang_plugins('cannot_repeat_opreate')];
        }

        if ($idcsmartWithdraw['status'] != 1){
            return ['status' => 400, 'msg' => lang_plugins('withdraw_not_pass_cannot_remit')];
        }

        //验证支付方式
        $gateway = PluginModel::where('module', 'gateway')->where('name', 'UserCustom')->find();
        if (empty($gateway)){
            return ['status'=>400, 'msg'=>lang('gateway_is_not_exist')];
        }


        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $transaction = TransactionModel::create([
                'amount' => $idcsmartWithdraw['amount'],
                'gateway' => 'UserCustom',
                'gateway_name' => $gateway['title'],
                'transaction_number' => $param['transaction_number'] ?? '',
                'client_id' => $idcsmartWithdraw['client_id'],
                'create_time' => time()
            ]);

            $this->update([
                'status' => 3,
                'transaction_id' => $transaction->id,
                'update_time' => time()
            ], ['id' => $param['id']]);

            $client = ClientModel::find($idcsmartWithdraw['client_id']);

            # 记录日志
            active_log(lang_plugins('admin_remit_client_withdraw', ['{admin}'=>request()->admin_name,'{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{amount}'=>configuration("currency_prefix").$idcsmartWithdraw['amount'].configuration("currency_suffix")]), 'addon_idcsmart_withdraw', $idcsmartWithdraw->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }

        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }

    # 修改提现状态
    public function updateIdcsmartWithdrawStatus($param)
    {
        // 验证提现ID
        $idcsmartWithdraw = $this->find($param['id']);
        if(empty($idcsmartWithdraw)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_is_not_exist')];
        }

        if ($idcsmartWithdraw['status'] != 2){
            return ['status' => 400, 'msg' => lang_plugins('withdraw_not_pass_can_update')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'status' => $param['status'],
                'reason' => '',
                'admin_id' => $adminId,
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 提现审核通过钩子
            if($param['status']==1){
                if($idcsmartWithdraw['source']=='credit'){
                    $result = update_credit([
                        'type' => 'Withdraw',
                        'amount' => -$idcsmartWithdraw['amount'],
                        'notes' => lang_plugins('credit_withdraw'),
                        'client_id' => $idcsmartWithdraw['client_id'],
                    ]);
                    if(!$result){
                        throw new \Exception(lang_plugins('insufficient_credit_deduction_failed'));           
                    }
                }
            }

            $client = ClientModel::find($idcsmartWithdraw['client_id']);
            if($param['status']==1){
                # 记录日志
                active_log(lang_plugins('admin_pass_client_withdraw', ['{admin}'=>request()->admin_name,'{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{amount}'=>configuration("currency_prefix").$idcsmartWithdraw['amount'].configuration("currency_suffix")]), 'addon_idcsmart_withdraw', $idcsmartWithdraw->id);
            }else{
                # 记录日志
                active_log(lang_plugins('admin_update_client_withdraw', ['{admin}'=>request()->admin_name,'{client}'=>'client#'.$client['id'].'#'.$client['username'].'#']), 'addon_idcsmart_withdraw', $idcsmartWithdraw->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        if($param['status']==1){
            $params = [
                'id' => $idcsmartWithdraw['id'], 
                'source' => $idcsmartWithdraw['source'], 
                'amount' => $idcsmartWithdraw['amount'], 
                'fee' => $idcsmartWithdraw['fee'],
                'addon_idcsmart_withdraw_method_id' => $idcsmartWithdraw['addon_idcsmart_withdraw_method_id'],
                'card_number' => $idcsmartWithdraw['card_number'],
                'name' => $idcsmartWithdraw['name'],
                'account' => $idcsmartWithdraw['account'],
                'client_id' => $idcsmartWithdraw['client_id'],
                'create_time' => $idcsmartWithdraw['create_time'],
            ];
            hook('after_idcsmart_withdraw_reject_pass', $params);
        }else{
            $params = [
                'id' => $idcsmartWithdraw['id'], 
                'source' => $idcsmartWithdraw['source'], 
                'amount' => $idcsmartWithdraw['amount'], 
                'fee' => $idcsmartWithdraw['fee'],
                'addon_idcsmart_withdraw_method_id' => $idcsmartWithdraw['addon_idcsmart_withdraw_method_id'],
                'card_number' => $idcsmartWithdraw['card_number'],
                'name' => $idcsmartWithdraw['name'],
                'account' => $idcsmartWithdraw['account'],
                'client_id' => $idcsmartWithdraw['client_id'],
                'create_time' => $idcsmartWithdraw['create_time'],
            ];
            hook('after_idcsmart_withdraw_reject_pending', $params);
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }

    # 修改提现交易流水号 
    public function updateIdcsmartWithdrawTransaction($param)
    {
        // 验证提现ID
        $idcsmartWithdraw = $this->find($param['id']);
        if(empty($idcsmartWithdraw)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_is_not_exist')];
        }

        if ($idcsmartWithdraw['status'] != 3){
            return ['status' => 400, 'msg' => lang_plugins('withdraw_not_remit_cannot_update')];
        }

        $transaction = TransactionModel::find($idcsmartWithdraw['transaction_id']);
        if(empty($transaction)){
            return ['status'=>400, 'msg'=>lang_plugins('transaction_is_not_exist')];
        }


        $this->startTrans();
        try {
            TransactionModel::update([
                'transaction_number' => $param['transaction_number'] ?? '',
            ], ['id' => $idcsmartWithdraw['transaction_id']]);

            $client = ClientModel::find($idcsmartWithdraw['client_id']);

            # 记录日志
            active_log(lang_plugins('admin_update_remit_client_withdraw', ['{admin}'=>request()->admin_name,'{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{old}'=>$transaction['transaction_number'], 'new' => $param['transaction_number'] ?? '']), 'addon_idcsmart_withdraw', $idcsmartWithdraw->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }

        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}