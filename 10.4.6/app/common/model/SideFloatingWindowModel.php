<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-侧边浮窗模型
 * @desc 模板控制器-侧边浮窗模型
 * @use app\common\model\SideFloatingWindowModel
 */
class SideFloatingWindowModel extends Model
{
    protected $name = 'side_floating_window';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'name'          => 'string',
        'icon'          => 'string',
        'content'       => 'string',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 侧边浮窗列表
     * @desc 侧边浮窗列表
     * @author theworld
     * @version v1
     * @return array list -  侧边浮窗
     * @return int list[].id - 侧边浮窗ID
     * @return string list[].name - 名称
     * @return string list[].icon - 图标
     * @return string list[].content - 显示内容
     */
    public function sideFloatingWindowList()
    {
        $list = $this->field('id,name,icon,content')
            ->order('order', 'asc')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2024-04-02
     * @title 创建侧边浮窗
     * @desc 创建侧边浮窗
     * @author theworld
     * @version v1
     * @param string param.name - 名称 required
     * @param string param.icon - 图标 required
     * @param string param.content - 显示内容 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createSideFloatingWindow($param)
    {
        $this->startTrans();
        try {
            $order = $this->max('order');

            $sideFloatingWindow = $this->create([
                'name' => $param['name'],
                'icon' => $param['icon'],
                'content' => $param['content'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_side_floating_window', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'side_floating_window', $sideFloatingWindow->id);

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
     * @title 编辑侧边浮窗
     * @desc 编辑侧边浮窗
     * @author theworld
     * @version v1
     * @param int param.id - 侧边浮窗ID required
     * @param string param.name - 名称 required
     * @param string param.icon - 图标 required
     * @param string param.content - 显示内容 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateSideFloatingWindow($param)
    {
        // 验证侧边浮窗ID
        $sideFloatingWindow = $this->find($param['id']);
        if(empty($sideFloatingWindow)){
            return ['status'=>400, 'msg'=>lang('side_floating_window_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'name' => $param['name'],
                'icon' => $param['icon'],
                'content' => $param['content'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'name'      => lang('side_floating_window_name'),
                'icon'      => lang('side_floating_window_icon'),
                'content'   => lang('side_floating_window_content'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $sideFloatingWindow[$k] != $param[$k]){
                    $old = $sideFloatingWindow[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_side_floating_window', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $sideFloatingWindow['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'side_floating_window', $sideFloatingWindow->id);
            }

            # 记录日志
            active_log(lang('log_update_side_floating_window', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $sideFloatingWindow['name']]), 'side_floating_window', $sideFloatingWindow->id);

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
     * @title 删除侧边浮窗
     * @desc 删除侧边浮窗
     * @author theworld
     * @version v1
     * @param int id - 侧边浮窗ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteSideFloatingWindow($id)
    {
        // 验证侧边浮窗ID
        $sideFloatingWindow = $this->find($id);
        if(empty($sideFloatingWindow)){
            return ['status'=>400, 'msg'=>lang('side_floating_window_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_side_floating_window', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $sideFloatingWindow['name']]), 'side_floating_window', $sideFloatingWindow->id);
            
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
     * @title 侧边浮窗排序
     * @desc 侧边浮窗排序
     * @author theworld
     * @version v1
     * @param array id - 侧边浮窗ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function sideFloatingWindowOrder($param)
    {
        $id = $param['id'] ?? [];
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }

        $sideFloatingWindow = $this->column('id');
        if(count($id)!=count($sideFloatingWindow) || count($id)!=count(array_intersect($sideFloatingWindow, $id))){
            return ['status'=>400, 'msg'=>lang('side_floating_window_not_exist')];
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
            active_log(lang('log_order_side_floating_window', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('move_fail')];
        }
        return ['status' => 200, 'msg' => lang('move_success')];

    }
}