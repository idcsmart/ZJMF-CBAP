<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\admin\model\PluginModel;

/**
 * @title 提现规则模型
 * @desc 提现规则模型
 * @use addon\idcsmart_withdraw\model\IdcsmartWithdrawRuleModel
 */
class IdcsmartWithdrawRuleModel extends Model
{
    protected $name = 'addon_idcsmart_withdraw_rule';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'source'            => 'string',
        'method'            => 'string',
        'process'           => 'string',
        'min'     		    => 'string',
        'max'               => 'string',
        'cycle'             => 'string',
        'cycle_limit'       => 'string',
        'withdraw_fee_type' => 'string',
        'withdraw_fee'      => 'string',
        'percent'           => 'string',
        'percent_min'       => 'string',
        'create_time'       => 'int',
        'update_time'       => 'int',
        'status'            => 'int',
    ];

    private $config = [
        'method',
        'process',
        'min',
        'max',
        'cycle',
        'cycle_limit',
        'withdraw_fee_type',
        'withdraw_fee',
        'percent',
        'percent_min',
    ];

    /**
     * 时间 2022-07-25
     * @title 获取余额提现设置
     * @desc 获取余额提现设置
     * @author theworld
     * @version v1
     * @param string app - 前后台home前台admin后台
     * @return array method - 前台提现方式,后台返回提现方式ID
     * @return int method[].id - 提现方式ID,仅前台返回
     * @return string method[].name - 提现方式名称,仅前台返回
     * @return string process - 提现流程 
     * @return float min - 最小金额限制 
     * @return float max - 最大金额限制
     * @return string cycle - 提现周期
     * @return int cycle_limit - 提现周期次数限制,0不限
     * @return string withdraw_fee_type - 手续费类型fixed固定percent百分比
     * @return float withdraw_fee - 固定手续费金额
     * @return float percent - 手续费百分比
     * @return float percent_min - 最低手续费
     * @return int status - 状态0关闭1开启
     */
    public function idcsmartWithdrawRuleCredit($app = '')
    {
        $idcsmartWithdrawRule = $this->field('method,process,min,max,cycle,cycle_limit,withdraw_fee_type,withdraw_fee,percent,percent_min,status')
                ->where('source', 'credit')->find();
        
        if(empty($idcsmartWithdrawRule)){
            return [
                'method' => [],
                'process' => '',
                'min' => '',
                'max' => '',
                'cycle' => '',
                'cycle_limit' => '',
                'withdraw_fee_type' => '',
                'withdraw_fee' => '',
                'percent' => '',
                'percent_min' => '',
                'status' => 1,
            ];
        }
        $idcsmartWithdrawRule['method'] = array_filter(explode(',', $idcsmartWithdrawRule['method']));
        if($app=='home'){
            $idcsmartWithdrawRule['method'] = IdcsmartWithdrawMethodModel::field('id,name')->whereIn('id', $idcsmartWithdrawRule['method'])->select()->toArray();
        }
        

        return $idcsmartWithdrawRule;
    }

    /**
     * 时间 2022-07-25
     * @title 保存余额提现设置
     * @desc 保存余额提现设置
     * @author theworld
     * @version v1
     * @param string param.source - 提现来源 required
     * @param array param.method - 提现方式ID required
     * @param string param.process - 提现流程artificial人工auto自动 required
     * @param float param.min - 最小金额限制 
     * @param float param.max - 最大金额限制
     * @param string param.cycle - 提现周期day每天week每周month每月 required
     * @param int param.cycle_limit - 提现周期次数限制,0不限
     * @param string param.withdraw_fee_type - 手续费类型fixed固定percent百分比 required
     * @param float param.withdraw_fee - 固定手续费金额
     * @param float param.percent - 手续费百分比
     * @param float param.percent_min - 最低手续费
     * @param int param.status - 状态0关闭1开启 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function saveIdcsmartWithdrawRuleCredit($param)
    {
        $idcsmartWithdrawRule = $this->where('source', 'credit')->find();

        if($param['withdraw_fee_type']=='fixed'){
            $param['percent'] = '';
            $param['percent_min'] = '';
        }else{
            $param['withdraw_fee'] = '';
        }

        foreach ($param['method'] as $key => $value) {
            $method = IdcsmartWithdrawMethodModel::find($value);
            if(empty($method)){
                return ['status' => 400, 'msg' => lang_plugins('param_error')];
            }
        }

        if(!empty($idcsmartWithdrawRule)){
            $oldData = $idcsmartWithdrawRule->toArray();
            if(!empty($oldData)){
                $oldData['method'] = array_filter(explode(',', $oldData['method']));
            }
            
            $description = [];
            foreach($param as $k => $v){
                if(!in_array($k, $this->config)){
                    continue;
                }
                $lang = lang_plugins("idcsmart_withdraw_rule_log_{$k}");
                if($k=='method'){
                    $oldData[$k] = is_array($oldData[$k]) ? $oldData[$k] : [];
                    $v = is_array($v) ? $v : [];
                    if(!array_diff($oldData[$k], $v) || !array_diff($v, $oldData[$k])){
                        if(!empty($oldData[$k])){
                            $oldData[$k] = IdcsmartWithdrawMethodModel::whereIn('id', $oldData[$k])->column('name');
                        }
                        if(!empty($v)){
                            $v = IdcsmartWithdrawMethodModel::whereIn('id', $v)->column('name');
                        }

                        $lang_old = implode(', ', $oldData[$k]);
                        $lang_new = implode(', ', $v);
                        $description[] = lang_plugins('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
                    }
                }else if($k=='process'){
                    $oldData[$k] = $oldData[$k] ?? '';
                    $v = $v ?? '';
                    if($oldData[$k]!=$v){
                        $lang_old = !empty($oldData[$k]) ? lang_plugins("idcsmart_withdraw_rule_log_{$k}_{$oldData[$k]}") : '';
                        $lang_new = !empty($v) ? lang_plugins("idcsmart_withdraw_rule_log_{$k}_{$v}") : '';
                        $description[] = lang_plugins('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
                    }
                }else if($k=='cycle'){
                    $oldData[$k] = $oldData[$k] ?? '';
                    $v = $v ?? '';
                    if($oldData[$k]!=$v){
                        $lang_old = !empty($oldData[$k]) ? lang_plugins("idcsmart_withdraw_rule_log_{$k}_{$oldData[$k]}") : '';
                        $lang_new = !empty($v) ? lang_plugins("idcsmart_withdraw_rule_log_{$k}_{$v}") : '';
                        $description[] = lang_plugins('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
                    }
                }else if($k=='withdraw_fee_type'){
                    $oldData[$k] = $oldData[$k] ?? '';
                    $v = $v ?? '';
                    if($oldData[$k]!=$v){
                        $lang_old = !empty($oldData[$k]) ? lang_plugins("idcsmart_withdraw_rule_log_{$k}_{$oldData[$k]}") : '';
                        $lang_new = !empty($v) ? lang_plugins("idcsmart_withdraw_rule_log_{$k}_{$v}") : '';
                        $description[] = lang_plugins('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
                    }
                }else{
                    $oldData[$k] = $oldData[$k] ?? '';
                    $v = $v ?? '';
                    if($oldData[$k]!=$v){
                        $lang_old = $oldData[$k];
                        $lang_new = $v;
                        $description[] = lang_plugins('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
                    }
                }
            }
            $description = implode(',', $description);
        }

        $this->startTrans();
        try {
            if(!empty($idcsmartWithdrawRule)){
                $this->update([
                    'method' => implode(',', $param['method']),
                    'process' => $param['process'],
                    'min' => $param['min'] ?? '',
                    'max' => $param['max'] ?? '',
                    'cycle' => $param['cycle'],
                    'cycle_limit' => $param['cycle_limit'] ?? '',
                    'withdraw_fee_type' => $param['withdraw_fee_type'],
                    'withdraw_fee' => $param['withdraw_fee'] ?? '',
                    'percent' => $param['percent'] ?? '',
                    'percent_min' => $param['percent_min'] ?? '',
                    'create_time' => time(),
                    'update_time' => time(),
                    'status' => $param['status'] ?? 1,
                ], ['id' => $idcsmartWithdrawRule->id]);
            }else{
                $idcsmartWithdrawRule = $this->create([
                    'source' => 'credit',
                    'method' => implode(',', $param['method']),
                    'process' => $param['process'],
                    'min' => $param['min'] ?? '',
                    'max' => $param['max'] ?? '',
                    'cycle' => $param['cycle'],
                    'cycle_limit' => $param['cycle_limit'] ?? '',
                    'withdraw_fee_type' => $param['withdraw_fee_type'],
                    'withdraw_fee' => $param['withdraw_fee'] ?? '',
                    'percent' => $param['percent'] ?? '',
                    'percent_min' => $param['percent_min'] ?? '',
                    'create_time' => time(),
                    'update_time' => time(),
                    'status' => $param['status'] ?? 1,
                ]);
            }
            
            # 记录日志
            if(isset($description) && !empty($description)){
                active_log(lang_plugins('log_admin_configuration_idcsmart_withdraw_rule', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}