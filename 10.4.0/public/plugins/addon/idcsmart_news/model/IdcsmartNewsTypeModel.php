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

    /**
     * 时间 2022-06-21
     * @title 获取新闻分类
     * @desc 获取新闻分类
     * @author theworld
     * @version v1
     * @param string app - 前后台home前台admin后台
     * @return array list - 新闻分类
     * @return int list[].id - 新闻分类ID
     * @return string list[].name - 名称
     * @return string list[].admin - 修改人,仅后台返回 
     * @return int list[].update_time - 修改时间,仅后台返回  
     * @return int list[].news_num - 新闻数量 
     * @return int count - 全部新闻数量 
     */
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
        $count = array_sum(array_column($list, 'news_num'));

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-21
     * @title 添加新闻分类
     * @desc 添加新闻分类
     * @author theworld
     * @version v1
     * @param array param.list - 分类数组 required
     * @param string param.list[].name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartNewsType($param)
    {
        $this->startTrans();
        try {
            $adminId = get_admin_id();

            foreach ($param['list'] as $key => $value) {
                $idcsmartNewsType = $this->create([
                    'admin_id' => $adminId,
                    'name' => $value['name'],
                    'create_time' => time(),
                    'update_time' => time()
                ]);

                # 记录日志
                active_log(lang_plugins('log_admin_add_news_type', ['{admin}'=>request()->admin_name,'{name}'=>$value['name']]), 'addon_idcsmart_news_type', $idcsmartNewsType->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    /**
     * 时间 2022-06-21
     * @title 修改新闻分类
     * @desc 修改新闻分类
     * @author theworld
     * @version v1
     * @param int param.id - 新闻分类ID required
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 删除新闻分类
     * @desc 删除新闻分类
     * @author theworld
     * @version v1
     * @param int id - 新闻分类ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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