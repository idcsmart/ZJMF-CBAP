<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\admin\model\PluginModel;

/**
 * @title 提现方式模型
 * @desc 提现方式模型
 * @use addon\idcsmart_withdraw\model\IdcsmartWithdrawMethodModel
 */
class IdcsmartWithdrawMethodModel extends Model
{
    protected $name = 'addon_idcsmart_withdraw_method';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'name'              => 'string',
        'admin_id'          => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    # 提现方式列表
    public function idcsmartWithdrawMethodList()
    {
        $count = $this->alias('aiwrr')
            ->field('aiwrr.id')
            ->count();
        $list = $this->alias('aiwrr')
            ->field('aiwrr.id,aiwrr.name,aiwrr.admin_id,a.name admin,aiwrr.create_time')
            ->leftJoin('admin a', 'a.id=aiwrr.admin_id')
            ->select()
            ->toArray();

        return ['list' => $list, 'count' => $count];
    }

    # 创建提现方式
    public function createIdcsmartWithdrawMethod($param)
    {
        $adminId = get_admin_id();
        $param['name'] = $param['name'] ?? '';
        if(empty($param['name']) || !is_string($param['name'])){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_method_name_require')];
        }
        if(strlen($param['name'])>20){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_method_name_max')];
        }

        $this->startTrans();
        try {
            $method = $this->create([
                'name' => $param['name'] ?? '',
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

    # 编辑提现方式
    public function updateIdcsmartWithdrawMethod($param)
    {
        // 验证提现方式ID
        $method = $this->find($param['id']);
        if(empty($method)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_withdraw_method_is_not_exist')];
        }

        $param['name'] = $param['name'] ?? '';
        if(empty($param['name']) || !is_string($param['name'])){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_method_name_require')];
        }
        if(strlen($param['name'])>20){
            return ['status' => 400, 'msg' => lang_plugins('addon_idcsmart_withdraw_method_name_max')];
        }

        $this->startTrans();
        try {
            $this->update([
                'name' => $param['name'] ?? '',
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

    # 删除提现方式
    public function deleteIdcsmartWithdrawMethod($id)
    {
        // 验证提现方式ID
        $method = $this->find($id);
        if(empty($method)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_withdraw_method_is_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            
            $this->destroy($id);
            $rule = IdcsmartWithdrawRuleModel::where('source', 'credit')->find();
            if(!empty($rule)){
                $rule['method'] = array_filter(explode(',', $rule['method']));
                if(in_array($id, $rule['method'])){
                    unset($rule['method'][array_search($id, $rule['method'])]);
                }
                $rule['method'] = array_values($rule['method']);
                IdcsmartWithdrawRuleModel::update([
                    'method' => implode(',', $rule['method']),
                ],['id' => $rule['id']]);
            }


            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }
}