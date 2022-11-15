<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\admin\model\PluginModel;

/**
 * @title 提现驳回原因模型
 * @desc 提现驳回原因模型
 * @use addon\idcsmart_withdraw\model\IdcsmartWithdrawRejectReasonModel
 */
class IdcsmartWithdrawRejectReasonModel extends Model
{
    protected $name = 'addon_idcsmart_withdraw_reject_reason';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'reason'            => 'string',
        'admin_id'       	=> 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    # 驳回原因列表
    public function idcsmartWithdrawRejectReasonList()
    {
    	$count = $this->alias('aiwrr')
            ->field('aiwrr.id')
            ->count();
        $list = $this->alias('aiwrr')
            ->field('aiwrr.id,aiwrr.reason,aiwrr.admin_id,a.name admin,aiwrr.create_time')
            ->leftJoin('admin a', 'a.id=aiwrr.admin_id')
            ->select()
            ->toArray();

        return ['list' => $list, 'count' => $count];
    }

    # 创建驳回原因
    public function createIdcsmartWithdrawRejectReason($param)
    {
        $adminId = get_admin_id();

        $param['reason'] = $param['reason'] ?? '';
        if(empty($param['reason']) || !is_string($param['reason'])){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_reject_reason_require')];
        }
        if(strlen($param['reason'])>1000){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_reject_reason_max')];
        }

        $this->startTrans();
        try {
            $reason = $this->create([
                'reason' => $param['reason'] ?? '',
                'admin_id' => $adminId,
                'create_time' => time()
            ]);

            # 记录日志
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 编辑驳回原因
    public function updateIdcsmartWithdrawRejectReason($param)
    {
        // 验证驳回原因ID
        $reason = $this->find($param['id']);
        if(empty($reason)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_withdraw_reject_reason_is_not_exist')];
        }

        $param['reason'] = $param['reason'] ?? '';
        if(empty($param['reason']) || !is_string($param['reason'])){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_reject_reason_require')];
        }
        if(strlen($param['reason'])>1000){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_reject_reason_max')];
        }

        $this->startTrans();
        try {
            $this->update([
                'reason' => $param['reason'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除驳回原因
    public function deleteIdcsmartWithdrawRejectReason($id)
    {
    	// 验证驳回原因ID
        $reason = $this->find($id);
        if(empty($reason)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_withdraw_reject_reason_is_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }
    
}