<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

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
        'min'     		    => 'float',
        'max'               => 'float',
        'cycle'             => 'string',
        'cycle_limit'       => 'int',
        'withdraw_fee_type' => 'string',
        'withdraw_fee'      => 'float',
        'percent'           => 'float',
        'percent_min'       => 'float',
        'status'            => 'int',
        'admin_id'          => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',

    ];

    # 提现规则列表
    public function idcsmartWithdrawRuleList($param, $app = '')
    {
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aiwr.'.$param['orderby'] : 'aiwr.id';

        $source = IdcsmartWithdrawSourceModel::select()->toArray();
        $source = array_column($source, 'plugin_title', 'plugin_name');

        $count = $this->alias('aiwr')
            ->field('aiwr.id')
            ->where(function ($query) use($param) {

            })
            ->count();
        $list = $this->alias('aiwr')
            ->field('aiwr.id,aiwr.source,a.name admin,aiwr.create_time,aiwr.status')
            ->leftJoin('admin a', 'a.id=aiwr.admin_id')
            ->where(function ($query) use($param) {

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

    # 提现规则详情
    public function idcsmartWithdrawRuleDetail($param, $app = '')
    {
        if($app=='home'){
            $idcsmartWithdrawRule = $this->field('method,process,min,max,cycle,cycle_limit,withdraw_fee_type,withdraw_fee,percent,percent_min')
                ->where('source', $param)->find();
        }else{
            $idcsmartWithdrawRule = $this->field('id,source,method,process,min,max,cycle,cycle_limit,withdraw_fee_type,withdraw_fee,percent,percent_min')
                ->find($param);
        }
        

        if(empty($idcsmartWithdrawRule)){
            return (object)[];
        }
        $idcsmartWithdrawRule['method'] = array_filter(explode(',', $idcsmartWithdrawRule['method']));

        return $idcsmartWithdrawRule;
    }

    # 新增提现规则
    public function createIdcsmartWithdrawRule($param)
    {
        $source = IdcsmartWithdrawSourceModel::select()->toArray();
        $source = array_column($source, 'plugin_name');

        if(!in_array($param['source'], $source) && $param['source']!='credit'){
            return ['status' => 400, 'msg' => lang_plugins('source_is_not_exist')];
        }

        if($param['withdraw_fee_type']=='fixed'){
            $param['percent'] = 0;
            $param['percent_min'] = 0;
        }else{
            $param['withdraw_fee'] = 0;
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->create([
                'admin_id' => $adminId,
                'source' => $param['source'],
                'method' => implode(',', $param['method']),
                'process' => $param['process'],
                'min' => $param['min'] ?? 0,
                'max' => $param['max'] ?? 0,
                'cycle' => $param['cycle'],
                'cycle_limit' => $param['cycle_limit'] ?? 0,
                'withdraw_fee_type' => $param['withdraw_fee_type'],
                'withdraw_fee' => $param['withdraw_fee'] ?? 0,
                'percent' => $param['percent'] ?? 0,
                'percent_min' => $param['percent_min'] ?? 0,
                'create_time' => time(),
                'update_time' => time()
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 编辑提现规则
    public function updateIdcsmartWithdrawRule($param)
    {
        // 验证提现规则ID
        $idcsmartWithdrawRule = $this->find($param['id']);
        if(empty($idcsmartWithdrawRule)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_rule_is_not_exist')];
        }

        if($param['withdraw_fee_type']=='fixed'){
            $param['percent'] = 0;
            $param['percent_min'] = 0;
        }else{
            $param['withdraw_fee'] = 0;
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'admin_id' => $adminId,
                'method' => implode(',', $param['method']),
                'process' => $param['process'],
                'min' => $param['min'] ?? 0,
                'max' => $param['max'] ?? 0,
                'cycle' => $param['cycle'],
                'cycle_limit' => $param['cycle_limit'] ?? 0,
                'withdraw_fee_type' => $param['withdraw_fee_type'],
                'withdraw_fee' => $param['withdraw_fee'] ?? 0,
                'percent' => $param['percent'] ?? 0,
                'percent_min' => $param['percent_min'] ?? 0,
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除提现规则
    public function deleteIdcsmartWithdrawRule($id)
    {
        // 验证提现规则ID
        $idcsmartWithdrawRule = $this->find($id);
        if(empty($idcsmartWithdrawRule)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_rule_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    # 开启/关闭提现规则
    public function idcsmartWithdrawRuleStatus($param)
    {
        // 验证提现规则ID
        $idcsmartWithdrawRule = $this->find($param['id']);
        if(empty($idcsmartWithdrawRule)){
            return ['status'=>400, 'msg'=>lang_plugins('withdraw_rule_is_not_exist')];
        }

        $status = $param['status'] ?? 0;

        if ($idcsmartWithdrawRule['status'] == $status){
            return ['status' => 400, 'msg' => lang_plugins('cannot_repeat_opreate')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'status' => $param['status'] ?? 0,
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}