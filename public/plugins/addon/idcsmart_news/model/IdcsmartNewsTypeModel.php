<?php
namespace addon\idcsmart_news\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 新闻分类模型
 * @desc 新闻分类模型
 * @use addon\idcsmart_news\model\IdcsmartNewsTypeModel
 */
class IdcsmartNewsTypeModel extends Model
{
    protected $name = 'addon_idcsmart_news_type';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'name'              => 'string',
        'admin_id'     		=> 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',

    ];

    # 获取新闻分类
    public function idcsmartNewsTypeList($app = '')
    {
        $list = $this->alias('aiht')
            ->field('aiht.id,aiht.name,a.name admin,aiht.update_time')
            ->leftJoin('admin a', 'a.id=aiht.admin_id')
            ->select()
            ->toArray();

        # 获取新闻数量
        $news = IdcsmartNewsModel::field('addon_idcsmart_news_type_id,COUNT(id) news_num')
            ->where(function ($query) use($app) {
                if($app=='home'){
                    $query->where('hidden', 0);
                }
            })
            ->group('addon_idcsmart_news_type_id')
            ->select()
            ->toArray();
        $news = array_column($news, 'news_num', 'addon_idcsmart_news_type_id');
        foreach ($list as $key => $value) {
            $list[$key]['news_num'] = $news[$value['id']] ?? 0;
            if($app=='home'){
                unset($list[$key]['admin'], $list[$key]['update_time']);
            }
        }

        return ['list' => $list];
    }

    # 添加新闻分类
    public function createIdcsmartNewsType($param)
    {
        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $idcsmartNewsType = $this->create([
                'admin_id' => $adminId,
                'name' => $param['name'] ?? '',
                'create_time' => time(),
                'update_time' => time()
            ]);

            # 记录日志
            active_log(lang_plugins('log_admin_add_news_type', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_news_type', $idcsmartNewsType->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 修改新闻分类
    public function updateIdcsmartNewsType($param)
    {
        // 验证新闻分类ID
        $idcsmartNewsType = $this->find($param['id']);
        if(empty($idcsmartNewsType)){
            return ['status'=>400, 'msg'=>lang_plugins('news_type_is_not_exist')];
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
            active_log(lang_plugins('log_admin_edit_news_type', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_news_type', $idcsmartNewsType->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除新闻分类
    public function deleteIdcsmartNewsType($id)
    {
        // 验证新闻分类ID
        $idcsmartNewsType = $this->find($id);
        if(empty($idcsmartNewsType)){
            return ['status'=>400, 'msg'=>lang_plugins('news_type_is_not_exist')];
        }

        $count = IdcsmartNewsModel::where('addon_idcsmart_news_type_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang_plugins('news_type_is_used')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_news_type', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartNewsType['name']]), 'addon_idcsmart_news_type', $idcsmartNewsType->id);

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