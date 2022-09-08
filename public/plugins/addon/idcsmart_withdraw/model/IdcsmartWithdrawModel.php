<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ClientModel;

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
        'id'      		=> 'int',
        'source'        => 'int',
        'amount'     	=> 'float',
        'method'     	=> 'string',
        'card_number'   => 'string',
        'name'          => 'string',
        'account'       => 'string',
        'client_id'     => 'int',
        'status'        => 'int',
        'reason'        => 'string',
        'admin_id'      => 'int',
        'fee'           => 'float',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    # 提现列表
    public function idcsmartWithdrawList($param)
    {
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aiw.'.$param['orderby'] : 'aiw.id';

        $source = IdcsmartWithdrawSourceModel::select()->toArray();
        $source = array_column($source, 'plugin_title', 'plugin_name');

    	$count = $this->alias('aiw')
            ->field('aiw.id')
            ->where(function ($query) use($param) {
                if(in_array($param['status'], [0,1,2])){
                    $query->where('aiw.status', $param['status']);
                }
            })
            ->count();
        $list = $this->alias('aiw')
            ->field('aiw.id,aiw.amount,aiw.status,aiw.create_time,c.username,aiw.source')
            ->leftJoin('client c', 'c.id=aiw.client_id')
            ->where(function ($query) use($param) {
                if(in_array($param['status'], [0,1,2])){
                    $query->where('aiw.status', $param['status']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($value['source']=='credit'){
                $list[$key]['source'] = lang_plugins('withdraw_source_credit');
            }else{
                $list[$key]['source'] = $source[$value['source']];
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
                        'notes' => "Withdraw",
                        'client_id' => $idcsmartWithdraw['client_id'],
                    ]);
                    if(!$result){
                        throw new \Exception(lang('fail_message'));           
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
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        if($param['status']==1){
            $params = [
                'id' => $idcsmartWithdraw['id'], 
                'source' => $idcsmartWithdraw['source'], 
                'amount' => $idcsmartWithdraw['amount'], 
                'fee' => $idcsmartWithdraw['fee'],
                'method' => $idcsmartWithdraw['method'],
                'card_number' => $idcsmartWithdraw['card_number'],
                'name' => $idcsmartWithdraw['name'],
                'account' => $idcsmartWithdraw['account'],
                'client_id' => $idcsmartWithdraw['client_id'],
                'create_time' => $idcsmartWithdraw['create_time'],
            ];
            hook('after_idcsmart_withdraw_pass', $params);
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

        $source = IdcsmartWithdrawSourceModel::select()->toArray();
        $source = array_column($source, 'plugin_name');

        if(!in_array($param['source'], $source) && $param['source']!='credit'){
            return ['status' => 400, 'msg' => lang_plugins('source_is_not_exist')];
        }

        // 验证提现ID
        $idcsmartWithdrawRule = IdcsmartWithdrawRuleModel::where('source', $param['source'])->find();
        if(empty($idcsmartWithdrawRule)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_rule_is_not_exist')];
        }
        if($idcsmartWithdrawRule['status']!=1){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_rule_is_not_exist')];
        }

        $idcsmartWithdrawRule['method'] = array_filter(explode(',', $idcsmartWithdrawRule['method']));
        if(!in_array($param['method'], $idcsmartWithdrawRule['method'])){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_method_is_not_exist')];
        }

        if($param['amount']<$idcsmartWithdrawRule['min']){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_amount_less_than_min')];
        }

        if(!empty($idcsmartWithdrawRule['max'])){
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
        if($param['source']=='credit' && $param['amount']>$client['credit']){
            return ['status'=>400, 'msg'=>lang_plugins('insufficient_balance')];
        }
        $this->startTrans();
        try {
            if($idcsmartWithdrawRule['withdraw_fee_type']=='fixed'){
                $fee = $idcsmartWithdrawRule['withdraw_fee'];
            }else{
                if(!empty($idcsmartWithdrawRule['percent_min'])){
                    if($param['amount']<$idcsmartWithdrawRule['percent_min']){
                        $fee = bcdiv($idcsmartWithdrawRule['percent_min']*$idcsmartWithdrawRule['percent'], 100, 2);
                    }else{
                        $fee = bcdiv($param['amount']*$idcsmartWithdrawRule['percent'], 100, 2);
                    }
                }else{
                    $fee = bcdiv($param['amount']*$idcsmartWithdrawRule['percent'], 100, 2);
                }
            }

            $idcsmartWithdraw = $this->create([
                'source' => $param['source'],
                'amount' => $param['amount'],
                'method' => $param['method'],
                'card_number' => $param['card_number'] ?? '',
                'name' => $param['name'] ?? '',
                'account' => $param['account'] ?? '',
                'client_id' => $clientId,
                'fee' => $fee,
                'status' => $idcsmartWithdrawRule['process']=='auto' ? 1 : 0,
                'create_time' => time()
            ]);

            # 提现审核通过钩子
            if($idcsmartWithdrawRule['process']=='auto'){
                if($param['source']=='credit'){
                    $result = update_credit([
                        'type' => 'Withdraw',
                        'amount' => -$param['amount'],
                        'notes' => "Withdraw",
                        'client_id' => $clientId,
                    ]);
                    if(!$result){
                        throw new \Exception(lang('fail_message'));           
                    }
                }
            }
            if($param['source']=='credit'){
                $source = lang_plugins('withdraw_source_credit');
            }else{
                $source = IdcsmartWithdrawSourceModel::where('plugin_name', $param['source'])->find();
                $source = $source['plugin_title'] ?? '';
            }

            # 记录日志
            active_log(lang_plugins('log_client_withdraw', ['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{source}'=>$source,'{amount}'=>configuration("currency_prefix").$param['amount'].configuration("currency_suffix")]), 'client', $client->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            //return ['status' => 400, 'msg' => lang_plugins('fail_message')];
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        if($idcsmartWithdrawRule['process']=='auto'){
            $params = [
                'id' => $idcsmartWithdraw->id, 
                'source' => $param['source'], 
                'amount' => $param['amount'], 
                'fee' => $fee,
                'method' => $param['method'],
                'card_number' => $param['card_number'] ?? '',
                'name' => $param['name'] ?? '',
                'account' => $param['account'] ?? '',
                'client_id' => $clientId,
                'create_time' => $idcsmartWithdraw->create_time,
            ];
            hook('after_idcsmart_withdraw_pass', $params);
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
}