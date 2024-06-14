<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-底部栏导航模型
 * @desc 模板控制器-底部栏导航模型
 * @use app\common\model\BottomBarNavModel
 */
class BottomBarNavModel extends Model
{
    protected $name = 'bottom_bar_nav';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'theme'         => 'string',
        'group_id'      => 'int',
        'name'     	    => 'string',
        'url'           => 'string',
        'show'          => 'int',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 底部栏导航列表
     * @desc 底部栏导航列表
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list -  分组
     * @return int list[].id - 分组ID
     * @return string list[].name - 名称
     * @return array list[].children - 导航
     * @return int list[].children[].id - 导航ID
     * @return int list[].children[].group_id - 分组ID
     * @return string list[].children[].name - 名称
     * @return string list[].children[].url - 跳转地址
     * @return int list[].children[].show - 是否展示
     */
    public function navList($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');

        $list = $this->field('id,group_id,name,url,show')
            ->where('theme', $theme)
            ->order('order', 'asc')
            ->select()
            ->toArray();

        $groupArr = [];
        foreach ($list as $key => $value) {
            if(!isset($groupArr[$value['group_id']])){
                $groupArr[$value['group_id']] = [];
            }
            $groupArr[$value['group_id']][] = $value;
        }

        $group = BottomBarGroupModel::field('id,name')
            ->where('theme', $theme)
            ->order('order', 'asc')
            ->select()
            ->toArray();

        foreach ($group as $key => $value) {
            $group[$key]['children'] = $groupArr[$value['id']] ?? [];
        }

        return ['list' => $group];
    }

    /**
     * 时间 2024-04-02
     * @title 创建底部栏导航
     * @desc 创建底部栏导航
     * @author theworld
     * @version v1
     * @param int param.group_id - 分组ID required
     * @param string param.name - 名称 required
     * @param string param.url - 跳转地址 required
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createNav($param)
    {
        $group = BottomBarGroupModel::where('id', $param['group_id'])->find();
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_not_exist')];
        }

        $this->startTrans();
        try {
            $order = $this->where('group_id', $param['group_id'])->max('order');
 
            $nav = $this->create([
                'theme' => $group['theme'],
                'group_id' => $param['group_id'],
                'name' => $param['name'],
                'url' => $param['url'],
                'show' => $param['show'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_bottom_bar_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'bottom_bar_nav', $nav->id);

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
     * @title 编辑底部栏导航
     * @desc 编辑底部栏导航
     * @author theworld
     * @version v1
     * @param int param.id - 导航ID required
     * @param int param.group_id - 分组ID required
     * @param string param.name - 名称 required
     * @param string param.url - 跳转地址 required
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateNav($param)
    {
        // 验证导航ID
        $nav = $this->find($param['id']);
        if(empty($nav)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_nav_not_exist')];
        }

        $BottomBarGroupModel = new BottomBarGroupModel();
        $group = $BottomBarGroupModel->where('id', $param['group_id'])->find();
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'group_id' => $param['group_id'],
                'name' => $param['name'],
                'url' => $param['url'],
                'show' => $param['show'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'group_id'     => lang('bottom_bar_nav_group_id'),
                'name'          => lang('bottom_bar_nav_name'),
                'url'          => lang('bottom_bar_nav_url'),
                'show'          => lang('bottom_bar_nav_show'),
            ];


            $param['group_id'] = $group['name'];
 
            $old = $BottomBarGroupModel->where('id', $nav['group_id'])->find();
            $nav['group_id'] = $old['name'];


            $param['show'] = lang('whether_'.$param['show']);
            $nav['show'] = lang('whether_'.$nav['show']);

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $nav[$k] != $param[$k]){
                    $old = $nav[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_bottom_bar_nav', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $nav['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'bottom_bar_nav', $nav->id);
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
     * @title 删除底部栏导航
     * @desc 删除底部栏导航
     * @author theworld
     * @version v1
     * @param int id - 导航ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteNav($id)
    {
        // 验证导航ID
        $nav = $this->find($id);
        if(empty($nav)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_nav_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_bottom_bar_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $nav['name']]), 'bottom_bar_nav', $nav->id);
            
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
     * @title 底部栏导航显示
     * @desc 底部栏导航显示
     * @author theworld
     * @version v1
     * @param int param.id - 导航ID required
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function navShow($param)
    {
        // 验证导航ID
        $nav = $this->find($param['id']);
        if(empty($nav)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_nav_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'show' => $param['show'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            if($param['show']==1){
                active_log(lang('log_show_bottom_bar_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $nav['name']]), 'bottom_bar_nav', $nav->id);
            }else{
                active_log(lang('log_hide_bottom_bar_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $nav['name']]), 'bottom_bar_nav', $nav->id);
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
     * @title 底部栏导航排序
     * @desc 底部栏导航排序
     * @author theworld
     * @version v1
     * @param int param.group_id - 分组ID required
     * @param array param.id - 导航ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function navOrder($param)
    {
        $BottomBarGroupModel = new BottomBarGroupModel();
        $group = $BottomBarGroupModel->where('id', $param['group_id'])->find();
        if(empty($group)){
            return ['status'=>400, 'msg'=>lang('bottom_bar_group_not_exist')];
        }

        $id = $param['id'] ?? [];
        $nav = $this->where('group_id', $param['group_id'])->column('id');
        if(count($id)!=count($nav) || count($id)!=count(array_intersect($nav, $id))){
            return ['status'=>400, 'msg'=>lang('bottom_bar_nav_not_exist')];
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
            active_log(lang('log_bottom_bar_order_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('move_fail')];
        }
        return ['status' => 200, 'msg' => lang('move_success')];

    }

    /**
     * 时间 2024-04-02
     * @title 网站底部栏数据
     * @desc 网站底部栏数据
     * @author theworld
     * @version v1
     * @return array list -  分组
     * @return int list[].id - 分组ID
     * @return string list[].name - 名称
     * @return array list[].children - 导航
     * @return int list[].children[].id - 导航ID
     * @return int list[].children[].group_id - 分组ID
     * @return string list[].children[].name - 名称
     * @return string list[].children[].url - 跳转地址
     */
    public function webFooterNav()
    {
        $theme = configuration('web_theme');

        $list = $this->field('id,group_id,name,url')
            ->where('show', 1)
            ->where('theme', $theme)
            ->order('order', 'asc')
            ->select()
            ->toArray();

        $groupArr = [];
        foreach ($list as $key => $value) {
            if(!isset($groupArr[$value['group_id']])){
                $groupArr[$value['group_id']] = [];
            }
            $groupArr[$value['group_id']][] = $value;
        }

        $group = BottomBarGroupModel::field('id,name')
            ->where('theme', $theme)
            ->order('order', 'asc')
            ->select()
            ->toArray();

        foreach ($group as $key => $value) {
            $group[$key]['children'] = $groupArr[$value['id']] ?? [];
            if(empty($group[$key]['children'])){
                unset($group[$key]);
            }
        }

        return ['list' => array_values($group)];
    }
}