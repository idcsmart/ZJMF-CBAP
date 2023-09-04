<?php
namespace addon\idcsmart_news\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UploadLogic;
use addon\idcsmart_news\logic\IdcsmartNewsLogic;

/**
 * @title 新闻模型
 * @desc 新闻模型
 * @use addon\idcsmart_news\model\IdcsmartNewsModel
 */
class IdcsmartNewsModel extends Model
{
    protected $name = 'addon_idcsmart_news';

    // 设置字段信息
    protected $schema = [
        'id'      		                => 'int',
        'addon_idcsmart_news_type_id'   => 'int',
        'title'     		            => 'string',
        'content'     		            => 'string',
        'keywords'                      => 'string',
        'img'                           => 'string',
        'attachment'                    => 'string',
        'hidden'                        => 'string',
        'read'                          => 'string',
        'admin_id'                      => 'string',
        'create_time'                   => 'int',
        'update_time'                   => 'int',
    ];

    # 新闻列表
    public function idcsmartNewsList($param, $app = '')
    {
        $param['addon_idcsmart_news_type_id'] = $param['addon_idcsmart_news_type_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aih.'.$param['orderby'] : 'aih.id';

    	$count = $this->alias('aih')
            ->field('aih.id')
            ->where(function ($query) use($param, $app) {
                if(!empty($param['keywords'])){
                    $query->where('aih.title', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['addon_idcsmart_news_type_id'])){
                    $query->where('aih.addon_idcsmart_news_type_id', $param['addon_idcsmart_news_type_id']);
                }
                if($app=='home'){
                    $query->where('aih.hidden', 0);
                }
            })
            ->count();

        $IdcsmartNewsLogic = new IdcsmartNewsLogic();
        $config = $IdcsmartNewsLogic->getDefaultConfig();

        $list = $this->alias('aih')
            ->field('aih.id,aih.title,aih.img,aiht.name type,a.name admin,aih.create_time,aih.hidden')
            ->leftJoin('addon_idcsmart_news_type aiht', 'aiht.id=aih.addon_idcsmart_news_type_id')
            ->leftJoin('admin a', 'a.id=aih.admin_id')
            ->where(function ($query) use($param, $app) {
                if(!empty($param['keywords'])){
                    $query->where('aih.title', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['addon_idcsmart_news_type_id'])){
                    $query->where('aih.addon_idcsmart_news_type_id', $param['addon_idcsmart_news_type_id']);
                }
                if($app=='home'){
                    $query->where('aih.hidden', 0);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($app=='home'){
                unset($list[$key]['admin'], $list[$key]['type'], $list[$key]['hidden']);
            }else if($app=='index'){
                unset($list[$key]['admin'], $list[$key]['hidden']);
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    # 新闻详情
    public function idcsmartNewsDetail($id, $app = '')
    {
        $IdcsmartNewsLogic = new IdcsmartNewsLogic();
        $config = $IdcsmartNewsLogic->getDefaultConfig();

        $idcsmartNews = $this->alias('aih')
            ->field('aih.id,aih.addon_idcsmart_news_type_id,aiht.name type,aih.title,aih.content,aih.keywords,aih.attachment,aih.create_time,aih.update_time,aih.hidden')
            ->leftJoin('addon_idcsmart_news_type aiht', 'aiht.id=aih.addon_idcsmart_news_type_id')
            ->withAttr('attachment',function ($value) use ($config){
                $attachments = array_filter(explode(',', $value));
                if (!empty($attachments)){
                    foreach ($attachments as &$attachment){
                        $attachment = $config['get_file_upload'] . $attachment;
                    }
                }
                return $attachments;
            })
            ->find($id);

        if(empty($idcsmartNews)){
            return (object)[];
        }

        if($app=='home'){
            if($idcsmartNews['hidden']==1){
                return (object)[];
            }
            $next = $this->field('id,title')->where('hidden', 0)->where('addon_idcsmart_news_type_id', $idcsmartNews['addon_idcsmart_news_type_id'])->where('id', '<', $idcsmartNews['id'])->order('id', 'desc')->find();
            $prev = $this->field('id,title')->where('hidden', 0)->where('addon_idcsmart_news_type_id', $idcsmartNews['addon_idcsmart_news_type_id'])->where('id', '>', $idcsmartNews['id'])->order('id', 'asc')->find();
            $idcsmartNews['prev'] = !empty($prev) ? ['id'=>$prev['id'], 'title'=>$prev['title']] : (object)[];
            $idcsmartNews['next'] = !empty($next) ? ['id'=>$next['id'], 'title'=>$next['title']] : (object)[];
            unset($idcsmartNews['hidden']);
        }else{
            unset($idcsmartNews['create_time']);
        }

        return $idcsmartNews;
    }

    # 添加新闻
    public function createIdcsmartNews($param)
    {
        $idcsmartNewsType = IdcsmartNewsTypeModel::find($param['addon_idcsmart_news_type_id']);
        if(empty($idcsmartNewsType)){
            return ['status'=>400, 'msg'=>lang_plugins('news_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            # 移动附件
            $IdcsmartNewsLogic = new IdcsmartNewsLogic();
            $fileUpload = $IdcsmartNewsLogic->getDefaultConfig('file_upload');
            $UploadLogic = new UploadLogic($fileUpload);
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $idcsmartNews = $this->create([
                'admin_id' => $adminId,
                'addon_idcsmart_news_type_id' => $param['addon_idcsmart_news_type_id'],
                'title' => $param['title'],
                'keywords' => $param['keywords'] ?? '',
                'img' => $param['img'] ?? '',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content' => $param['content'],
                'hidden' => $param['hidden'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang_plugins('log_admin_add_news', ['{admin}'=>request()->admin_name,'{title}'=>$param['title']]), 'addon_idcsmart_news', $idcsmartNews->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 修改新闻
    public function updateIdcsmartNews($param)
    {
        // 验证新闻ID
        $idcsmartNews = $this->find($param['id']);
        if(empty($idcsmartNews)){
            return ['status'=>400, 'msg'=>lang_plugins('news_is_not_exist')];
        }

        $idcsmartNewsType = IdcsmartNewsTypeModel::find($param['addon_idcsmart_news_type_id']);
        if(empty($idcsmartNewsType)){
            return ['status'=>400, 'msg'=>lang_plugins('news_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            # 移动附件
            $IdcsmartNewsLogic = new IdcsmartNewsLogic();
            $fileUpload = $IdcsmartNewsLogic->getDefaultConfig('file_upload');
            $UploadLogic = new UploadLogic($fileUpload);
            if (isset($param['attachment']) && !empty($param['attachment'])){
                foreach ($param['attachment'] as $key => $value) {
                    if(!file_exists($fileUpload.$value)){
                        $result = $UploadLogic->moveTo($value);
                        if (isset($result['error'])){
                            throw new \Exception($result['error']);
                        }
                    }
                }
            }

            $this->update([
                'admin_id' => $adminId,
                'addon_idcsmart_news_type_id' => $param['addon_idcsmart_news_type_id'],
                'title' => $param['title'],
                'keywords' => $param['keywords'] ?? '',
                'img' => $param['img'] ?? '',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content' => $param['content'],
                'hidden' => $param['hidden'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang_plugins('log_admin_edit_news', ['{admin}'=>request()->admin_name,'{title}'=>$param['title']]), 'addon_idcsmart_news', $idcsmartNews->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除新闻
    public function deleteIdcsmartNews($id)
    {
        // 验证新闻ID
        $idcsmartNews = $this->find($id);
        if(empty($idcsmartNews)){
            return ['status'=>400, 'msg'=>lang_plugins('news_is_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_news', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartNews['title']]), 'addon_idcsmart_news', $idcsmartNews->id);
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    # 隐藏/显示新闻
    public function hiddenIdcsmartNews($param)
    {
        // 验证新闻ID
        $idcsmartNews = $this->find($param['id']);
        if(empty($idcsmartNews)){
            return ['status'=>400, 'msg'=>lang_plugins('news_is_not_exist')];
        }

        $hidden = $param['hidden'];

        if ($idcsmartNews['hidden'] == $hidden){
            return ['status' => 400, 'msg' => lang('cannot_repeat_opreate')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $this->update([
                'hidden' => $param['hidden'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            if($param['hidden']==1){
                active_log(lang_plugins('log_admin_hide_news', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartNews['title']]), 'addon_idcsmart_news', $idcsmartNews->id);
            }else{
                active_log(lang_plugins('log_admin_show_news', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartNews['title']]), 'addon_idcsmart_news', $idcsmartNews->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}