<?php
namespace addon\idcsmart_withdraw\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\admin\model\PluginModel;

/**
 * @title 提现来源模型
 * @desc 提现来源模型
 * @use addon\idcsmart_withdraw\model\IdcsmartWithdrawSourceModel
 */
class IdcsmartWithdrawSourceModel extends Model
{
    protected $name = 'addon_idcsmart_withdraw_source';

    // 设置字段信息
    protected $schema = [
        'plugin_name'   => 'string',
        'plugin_title'  => 'string',
    ];

    # 获取提现来源
    public function idcsmartWithdrawSource()
    {
        $source = $this->field('plugin_name name,plugin_title title')->select()->toArray();

        array_unshift($source, ['name' => 'credit', 'title' => lang_plugins('withdraw_source_credit')]);

        return ['source' => $source];
    }

    # 保存提现来源
    public function idcsmartWithdrawSourceSave($param)
    {
        $addons = PluginModel::field('name,title')->where('module', 'addon')->select()->toArray();
        $addons = array_column($addons, 'title', 'name');
        if(!is_array($param['source']) || empty($param['source'])){
            return ['status' => 400, 'msg' => lang_plugins('param_error')];
        }

        foreach ($param['source'] as $key => $value) {
            if(!isset($addons[$value])){
                return ['status' => 400, 'msg' => lang_plugins('addon_is_not_exist')];
            }
        }
        $this->startTrans();
        try {
            $this->where('plugin_name', '<>', '')->delete();
            $arr = [];
            foreach ($param['source'] as $key => $value) {
                $arr[] = ['plugin_name' => $value, 'plugin_title' => $addons[$value]];
            }
            $this->saveAll($arr);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}