<?php
namespace addon\idcsmart_announcement\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 公告分类模型
 * @desc 公告分类模型
 * @use addon\idcsmart_announcement\model\IdcsmartAnnouncementTypeModel
 */
class IdcsmartAnnouncementTypeModel extends Model
{
    protected $name = 'addon_idcsmart_announcement_type';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'name'              => 'string',
        'admin_id'     		=> 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',

    ];

    # 获取公告分类
    public function idcsmartAnnouncementTypeList($app = '')
    {
        $list = $this->alias('aiht')
            ->field('aiht.id,aiht.name,a.name admin,aiht.update_time')
            ->leftJoin('admin a', 'a.id=aiht.admin_id')
            ->select()
            ->toArray();

        # 获取公告数量
        $announcement = IdcsmartAnnouncementModel::field('addon_idcsmart_announcement_type_id,COUNT(id) announcement_num')
            ->where(function ($query) use($app) {
                if($app=='home'){
                    $query->where('hidden', 0);
                }
            })
            ->group('addon_idcsmart_announcement_type_id')
            ->select()
            ->toArray();
        $announcement = array_column($announcement, 'announcement_num', 'addon_idcsmart_announcement_type_id');
        foreach ($list as $key => $value) {
            $list[$key]['announcement_num'] = $announcement[$value['id']] ?? 0;
            if($app=='home'){
                unset($list[$key]['admin'], $list[$key]['update_time']);
            }
        }
        $count = array_sum(array_column($list, 'announcement_num'));

        return ['list' => $list, 'count' => $count];
    }

    # 添加公告分类
    public function createIdcsmartAnnouncementType($param)
    {
        $this->startTrans();
        try {
            $adminId = get_admin_id();

            foreach ($param['list'] as $key => $value) {
                $idcsmartAnnouncementType = $this->create([
                    'admin_id' => $adminId,
                    'name' => $value['name'],
                    'create_time' => time(),
                    'update_time' => time()
                ]);

                # 记录日志
                active_log(lang_plugins('log_admin_add_announcement_type', ['{admin}'=>request()->admin_name,'{name}'=>$value['name']]), 'addon_idcsmart_announcement_type', $idcsmartAnnouncementType->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 修改公告分类
    public function updateIdcsmartAnnouncementType($param)
    {
        // 验证公告分类ID
        $idcsmartAnnouncementType = $this->find($param['id']);
        if(empty($idcsmartAnnouncementType)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'admin_id' => $adminId,
                'name' => $param['name'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang_plugins('log_admin_edit_announcement_type', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_announcement_type', $idcsmartAnnouncementType->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除公告分类
    public function deleteIdcsmartAnnouncementType($id)
    {
        // 验证公告分类ID
        $idcsmartAnnouncementType = $this->find($id);
        if(empty($idcsmartAnnouncementType)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_type_is_not_exist')];
        }

        $count = IdcsmartAnnouncementModel::where('addon_idcsmart_announcement_type_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_type_is_used')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_announcement_type', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartAnnouncementType['name']]), 'addon_idcsmart_announcement_type', $idcsmartAnnouncementType->id);

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