<?php
namespace addon\idcsmart_help\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 帮助文档分类模型
 * @desc 帮助文档分类模型
 * @use addon\idcsmart_help\model\SecurityGroupRuleModel
 */
class IdcsmartHelpTypeModel extends Model
{
    protected $name = 'addon_idcsmart_help_type';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'name'              => 'string',
        'index_section'     => 'int',
        'index_hot_show'    => 'int',
        'admin_id'     		=> 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',

    ];

    # 获取帮助文档分类
    public function idcsmartHelpTypeList($param = [], $app = '')
    {
        $list = $this->alias('aiht')
            ->field('aiht.id,aiht.name,a.name admin,aiht.update_time')
            ->leftJoin('admin a', 'a.id=aiht.admin_id')
            ->select()
            ->toArray();
        if($app=='home'){
            $helpId = 0;
            $param['keywords'] = $param['keywords'] ?? '';
            if(!empty($param['keywords'])){
                $help = IdcsmartHelpModel::where('title|keywords|content', 'like', "%{$param['keywords']}%")->find();
                if(!empty($help)){
                    $helpId = $help['id'];
                }
            }
            $helps = IdcsmartHelpModel::field('id,title,addon_idcsmart_help_type_id')->where('hidden', 0)->select()->toArray();
            $helpArr = [];
            foreach ($helps as $key => $value) {
                if($value['id']==$helpId){
                    $value['search'] = true;
                }else{
                    $value['search'] = false;
                }
                $addon_idcsmart_help_type_id = $value['addon_idcsmart_help_type_id'];
                unset($value['addon_idcsmart_help_type_id']);
                $helpArr[$addon_idcsmart_help_type_id][] = $value;
            }
            foreach ($list as $key => $value) {
                $list[$key]['helps'] = $helpArr[$value['id']] ?? [];
                unset($list[$key]['admin'], $list[$key]['update_time']);
            }
        }
        return ['list' => $list];
    }

    # 添加帮助文档分类
    public function createIdcsmartHelpType($param)
    {
        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $idcsmartHelpType = $this->create([
                'admin_id' => $adminId,
                'name' => $param['name'],
                'create_time' => time(),
                'update_time' => time()
            ]);

            # 记录日志
            active_log(lang_plugins('log_admin_add_help_type', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_help_type', $idcsmartHelpType->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 修改帮助文档分类
    public function updateIdcsmartHelpType($param)
    {
        // 验证帮助文档分类ID
        $idcsmartHelpType = $this->find($param['id']);
        if(empty($idcsmartHelpType)){
            return ['status'=>400, 'msg'=>lang_plugins('help_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'admin_id' => $adminId,
                'name' => $param['name'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang_plugins('log_admin_edit_help_type', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_help_type', $idcsmartHelpType->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除帮助文档分类
    public function deleteIdcsmartHelpType($id)
    {
        // 验证帮助文档分类ID
        $idcsmartHelpType = $this->find($id);
        if(empty($idcsmartHelpType)){
            return ['status'=>400, 'msg'=>lang_plugins('help_type_is_not_exist')];
        }

        $count = IdcsmartHelpModel::where('addon_idcsmart_help_type_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang_plugins('help_type_is_used')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_help_type', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartHelpType['name']]), 'addon_idcsmart_help_type', $idcsmartHelpType->id);

            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    # 帮助中心首页
    public function indexIdcsmartHelp($app = '')
    {
        $helpType = $this->field('id,name,index_section,index_hot_show')->where('index_section', '>', 0)->select()->toArray();

        $index = [
            '1' => (object)[],
            '2' => (object)[],
            '3' => (object)[],
            '4' => (object)[],
            '5' => (object)[],
            '6' => (object)[]
        ];
        foreach ($helpType as $key => $value) {
            if($app=='home' && $value['index_hot_show']==1){
                $value['helps'] = IdcsmartHelpModel::field('id,title')->where('addon_idcsmart_help_type_id', $value['id'])->where('hidden', 0)->order('read', 'desc')->limit(3)->select()->toArray();
            }else{
                $value['helps'] = IdcsmartHelpModel::field('id,title')->where('addon_idcsmart_help_type_id', $value['id'])->where('hidden', 0)->where('index_hidden', 0)->limit(3)->select()->toArray();
            }
            $index[$value['index_section']] = $value;
            unset($index[$value['index_section']]['index_section']);
            if($app=='home'){
                unset($index[$value['index_section']]['index_hot_show']);
            }
        }

        return ['index' => array_values($index)];
    }

    # 保存帮助中心首页数据
    public function indexIdcsmartHelpSave($param)
    {
        foreach ($param['index'] as $key => $value) {
            if(!empty($value['id'])){
                $idcsmartHelpType = $this->find($value['id']);
                if(empty($idcsmartHelpType)){
                    return ['status'=>400, 'msg'=>lang_plugins('help_type_is_not_exist')];
                }
            }
            
            if(!empty($value['helps'])){
                foreach ($value['helps'] as $v) {
                    $idcsmartHelp = IdcsmartHelpModel::find($v['id']);
                    if(empty($idcsmartHelp)){
                        return ['status'=>400, 'msg'=>lang_plugins('help_is_not_exist')];
                    }
                    if($idcsmartHelp['addon_idcsmart_help_type_id']!=$value['id']){
                        return ['status'=>400, 'msg'=>lang_plugins('help_is_not_exist')];
                    }
                }
            }
        }
        $this->startTrans();
        try {
            $this->where('index_section', '>', 0)->update(['index_section'=>0, 'index_hot_show'=>0]);
            IdcsmartHelpModel::where('index_hidden', 0)->update(['index_hidden' => 1]);
            foreach ($param['index'] as $key => $value) {
                $this->update([
                    'index_section' => $key+1,
                    'index_hot_show' => $value['index_hot_show'],
                ], ['id' => $value['id']]);
                if(!empty($value['helps'])){
                    foreach ($value['helps'] as $v) {
                        IdcsmartHelpModel::update([
                            'index_hidden' => 0,
                        ], ['id' => $v['id']]);
                    }
                }
            }

            # 记录日志
            active_log(lang_plugins('log_admin_edit_help_index', ['{admin}'=>request()->admin_name]), 'addon_idcsmart_help_index');

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}