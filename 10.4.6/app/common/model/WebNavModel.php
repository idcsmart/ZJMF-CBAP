<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-导航模型
 * @desc 模板控制器-导航模型
 * @use app\common\model\WebNavModel
 */
class WebNavModel extends Model
{
    protected $name = 'web_nav';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'theme'         => 'string',
        'parent_id'     => 'int',
        'name'     	    => 'string',
        'description'   => 'string',
        'file_address'  => 'string',
        'icon'          => 'string',
        'show'          => 'int',
        'order'         => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 导航列表
     * @desc 导航列表
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list -  一级导航
     * @return int list[].id - 一级导航ID
     * @return string list[].name - 名称
     * @return string list[].file_address - 文件地址
     * @return int list[].show - 是否展示
     * @return array list[].children - 二级导航
     * @return int list[].children[].id - 二级导航ID
     * @return int list[].children[].parent_id - 父导航ID
     * @return string list[].children[].name - 名称
     * @return string list[].children[].description - 描述
     * @return string list[].children[].file_address - 文件地址
     * @return string list[].children[].icon - 图标
     * @return int list[].children[].show - 是否展示
     */
    public function navList($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');

        $list = $this->field('id,parent_id,name,description,file_address,icon,show')
            ->where('theme', $theme)
            ->order('order', 'asc')
            ->select()
            ->toArray();

        $first = [];
        foreach ($list as $key => $value) {
            if(empty($value['parent_id'])){
                $first[$value['id']] = ['id' => $value['id'], 'name' => $value['name'], 'file_address' => $value['file_address'], 'show' => $value['show'], 'children' => []];
            }
        }
        foreach ($list as $key => $value) {
            if(!empty($value['parent_id']) && isset($first[$value['parent_id']])){
                $first[$value['parent_id']]['children'][] = $value;
            }
        }

        return ['list' => array_values($first)];
    }

    /**
     * 时间 2024-04-02
     * @title 创建导航
     * @desc 创建导航
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @param int param.parent_id - 父导航ID
     * @param string param.name - 名称 required
     * @param string param.description - 描述
     * @param string param.file_address - 文件地址
     * @param string param.icon - 导航图标
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createNav($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');

        $param['parent_id'] = $param['parent_id'] ?? 0;
        if(!empty($param['parent_id'])){
            $parent = $this->where('id', $param['parent_id'])->find();
            if(empty($parent) || $parent['parent_id']>0){
                return ['status'=>400, 'msg'=>lang('first_web_nav_not_exist')];
            }
        }else{
            $param['description'] = '';
            $param['icon'] = '';
        }
        

        $this->startTrans();
        try {
            if(isset($param['parent_id']) && $param['parent_id']>0){
                $order = $this->where('parent_id', $param['parent_id'])->max('order');
            }else{
                $order = $this->where('parent_id', 0)->max('order');
            }
            
            $nav = $this->create([
                'theme' => $theme,
                'parent_id' => $param['parent_id'],
                'name' => $param['name'],
                'description' => $param['description'] ?? '',
                'file_address' => $param['file_address'] ?? '',
                'icon' => $param['icon'] ?? '',
                'show' => $param['show'],
                'order' => $order+1,
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_web_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['name']]), 'web_nav', $nav->id);

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
     * @title 编辑导航
     * @desc 编辑导航
     * @author theworld
     * @version v1
     * @param int param.id - 导航ID required
     * @param int param.parent_id - 父导航ID
     * @param string param.name - 名称 required
     * @param string param.description - 描述
     * @param string param.file_address - 文件地址
     * @param string param.icon - 导航图标
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateNav($param)
    {
        // 验证导航ID
        $nav = $this->find($param['id']);
        if(empty($nav)){
            return ['status'=>400, 'msg'=>lang('web_nav_not_exist')];
        }

        $param['parent_id'] = $param['parent_id'] ?? 0;
        if(!empty($param['parent_id'])){
            $parent = $this->where('id', $param['parent_id'])->find();
            if(empty($parent) || $parent['parent_id']>0){
                return ['status'=>400, 'msg'=>lang('first_web_nav_not_exist')];
            }
        }else{
            $param['description'] = '';
            $param['icon'] = '';
        }

        if(in_array($param['id'], [1])){
            $param['parent_id'] = 0;
            $param['name'] = $nav['name'];
            $param['description'] = '';
            $param['icon'] = '';
            $param['show'] = 1;
        }
        if(in_array($param['id'], [2])){
            $param['parent_id'] = 0;
            $param['description'] = '';
            $param['icon'] = '';
        }

        $this->startTrans();
        try {
            $this->update([
                'parent_id' => $param['parent_id'],
                'name' => $param['name'],
                'description' => $param['description'] ?? '',
                'file_address' => $param['file_address'] ?? '',
                'icon' => $param['icon'] ?? '',
                'show' => $param['show'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'parent_id'     => lang('web_nav_parent_id'),
                'name'          => lang('web_nav_name'),
                'description'   => lang('web_nav_description'),
                'file_address'  => lang('web_nav_file_address'),
                'icon'          => lang('web_nav_icon'),
                'show'          => lang('web_nav_show'),
            ];

            if(!empty($param['parent_id'])){
                $param['parent_id'] = $parent['name'];
            }else{
                $param['parent_id'] = lang('none');
            }
            if(!empty($nav['parent_id'])){
                $old = $this->where('id', $nav['parent_id'])->find();
                $nav['parent_id'] = $old['name'];
            }else{
                $nav['parent_id'] = lang('none');
            }

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
                $description = lang('log_update_web_nav', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $nav['name'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'web_nav', $nav->id);
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
     * @title 删除导航
     * @desc 删除导航
     * @author theworld
     * @version v1
     * @param int id - 导航ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteNav($id)
    {
        if(in_array($id, [1,2,3,4,5,6,7,8,9,10])){
            return ['status'=>400, 'msg'=>lang('default_web_nav_cannot_delete')];
        }

        // 验证导航ID
        $nav = $this->find($id);
        if(empty($nav)){
            return ['status'=>400, 'msg'=>lang('web_nav_not_exist')];
        }

        if($nav['parent_id']==0){
            $count = $this->where('parent_id', $id)->count();
            if($count>0){
                return ['status'=>400, 'msg'=>lang('second_web_nav_in_nav')];
            }
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_web_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $nav['name']]), 'web_nav', $nav->id);
            
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
     * @title 导航显示
     * @desc 导航显示
     * @author theworld
     * @version v1
     * @param int param.id - 导航ID required
     * @param int param.show - 是否展示0否1是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function navShow($param)
    {
        if(in_array($param['id'], [1])){
            return ['status'=>400, 'msg'=>lang('index_web_nav_cannot_hide')];
        }

        // 验证导航ID
        $nav = $this->find($param['id']);
        if(empty($nav)){
            return ['status'=>400, 'msg'=>lang('web_nav_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'show' => $param['show'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            if($param['show']==1){
                active_log(lang('log_show_web_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $nav['name']]), 'web_nav', $nav->id);
            }else{
                active_log(lang('log_hide_web_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $nav['name']]), 'web_nav', $nav->id);
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
     * @title 一级导航排序
     * @desc 一级导航排序
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @param array param.id - 一级导航ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function firstNavOrder($param)
    {
        $theme = $param['theme'] ?? configuration('web_theme');

        $id = $param['id'] ?? [];
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }

        $nav = $this->where('parent_id', 0)->where('theme', $theme)->column('id');
        if(count($id)!=count($nav) || count($id)!=count(array_intersect($nav, $id))){
            return ['status'=>400, 'msg'=>lang('first_web_nav_not_exist')];
        }

        if($theme=='default'){
            if($id[0]!=1){
                return ['status'=>400, 'msg'=>lang('index_web_nav_cannot_order')];
            }
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
            active_log(lang('log_order_web_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

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
     * @title 二级导航排序
     * @desc 二级导航排序
     * @author theworld
     * @version v1
     * @param int param.parent_id - 父导航ID required
     * @param array param.id - 二级导航ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function secondNavOrder($param)
    {
        $parent = $this->where('id', $param['parent_id'])->find();
        if(empty($parent) || $parent['parent_id']>0){
            return ['status'=>400, 'msg'=>lang('first_web_nav_not_exist')];
        }

        $id = $param['id'] ?? [];
        $nav = $this->where('parent_id', $param['parent_id'])->column('id');
        if(count($id)!=count($nav) || count($id)!=count(array_intersect($nav, $id))){
            return ['status'=>400, 'msg'=>lang('second_web_nav_not_exist')];
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
            active_log(lang('log_order_web_nav', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#']));

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
     * @title 网站头部导航数据
     * @desc 网站头部导航数据
     * @author theworld
     * @version v1
     * @return array list -  一级导航
     * @return int list[].id - 一级导航ID
     * @return string list[].name - 名称
     * @return string list[].file_address - 文件地址
     * @return array list[].children - 二级导航
     * @return int list[].children[].id - 二级导航ID
     * @return int list[].children[].parent_id - 父导航ID
     * @return string list[].children[].name - 名称
     * @return string list[].children[].description - 描述
     * @return string list[].children[].file_address - 文件地址
     * @return string list[].children[].icon - 图标
     */
    public function webHeaderNav()
    {
        $theme = configuration('web_theme');

        $list = $this->field('id,parent_id,name,description,file_address,icon')
            ->where('show', 1)
            ->where('theme', $theme)
            ->order('order', 'asc')
            ->select()
            ->toArray();

        $first = [];
        foreach ($list as $key => $value) {
            $value['file_address'] = '/'. ltrim($value['file_address'], '/');
            $list[$key]['file_address'] = $value['file_address'];
            if(empty($value['parent_id'])){
                $first[$value['id']] = ['id' => $value['id'], 'name' => $value['name'], 'file_address' => $value['file_address'], 'children' => []];
            }
        }
        foreach ($list as $key => $value) {
            if(!empty($value['parent_id']) && isset($first[$value['parent_id']])){
                $first[$value['parent_id']]['children'][] = $value;
            }
        }

        $defaultPage = [];
        foreach ($list as $key => $value) {
            if(in_array($value['id'], [1,3,4,5,6,7,8,9,10])){
                $defaultPage[] = ['id' => $value['id'], 'file_address' => $value['file_address']];
            }
        }


        return ['list' => array_values($first), 'default_page' => $defaultPage];
    }
}