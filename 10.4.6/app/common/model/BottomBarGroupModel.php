<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-底部栏分组模型
 * @desc 模板控制器-底部栏分组模型
 * @use app\common\model\BottomBarGroupModel
 */
class BottomBarGroupModel extends Model
{
    protected $name = 'bottom_bar_group';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'theme'         => 'string',
        'name'          => 'string',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 底部栏分组列表
     * @desc 底部栏分组列表
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list -  分组
     * @return int list[].id - 分组ID
     * @return string list[].name - 名称
     */
    public function groupList($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');

        $list = $this->field('id,name')
            ->where('theme', $theme)
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2024-04-02
     * @title 创建底部栏分组
     * @desc 创建底部栏分组
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createGroup($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');

        $this->startTrans();
        try {
            $order = $this->max('order');

            $group = $this->create([
                'theme' => $theme,
                'name' => $param['name'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_bottom_bar_group', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'bottom_bar_group', $group->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 编辑底部栏分组
     * @desc 编辑底部栏分组
     * @author theworld
     * @version v1
     * @param int param.id - 分组ID required
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateGroup($param)
    {
        // 验证分组ID
        $group = $this->find($param['id']);
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'name' => $param['name'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'name'  => lang('bottom_bar_group_name'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $group[$k] != $param[$k]){
                    $old = $group[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_bottom_bar_group', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $group['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'bottom_bar_group', $group->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 删除底部栏分组
     * @desc 删除底部栏分组
     * @author theworld
     * @version v1
     * @param int id - 分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteGroup($id)
    {
        // 验证分组ID
        $group = $this->find($id);
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_not_exist')];
        }

        $BottomBarNavModel = new BottomBarNavModel();
        $count = $BottomBarNavModel->where('group_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_used_cannot_delete')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_bottom_bar_group', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $group['name']]), 'bottom_bar_group', $group->id);
            
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 底部栏分组排序
     * @desc 底部栏分组排序
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @param array param.id - 分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function groupOrder($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');
        
        $id = $param['id'] ?? [];
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }

        $group = $this->where('theme', $theme)->column('id');
        if(count($id)!=count($group) || count($id)!=count(array_intersect($group, $id))){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_not_exist')];
        }

        $this->startTrans();
        try {
            foreach ($id as $key => $value) {
                $this->update([
                    'order' => $key,
                    'update_time' => time()
                ], ['id' => $value]);
            }

            # 记录日志
            active_log(lang('log_order_bottom_bar_group', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('move_fail')];
        }
        return ['status' => 200, 'msg' => lang('move_success')];

    }
}