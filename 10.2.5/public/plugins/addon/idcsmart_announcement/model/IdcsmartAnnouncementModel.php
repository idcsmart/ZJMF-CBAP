<?php
namespace addon\idcsmart_announcement\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UploadLogic;
use addon\idcsmart_announcement\logic\IdcsmartAnnouncementLogic;

/**
 * @title 公告模型
 * @desc 公告模型
 * @use addon\idcsmart_announcement\model\IdcsmartAnnouncementModel
 */
class IdcsmartAnnouncementModel extends Model
{
    protected $name = 'addon_idcsmart_announcement';

    // 设置字段信息
    protected $schema = [
        'id'      		                => 'int',
        'addon_idcsmart_announcement_type_id'   => 'int',
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

    # 公告列表
    public function idcsmartAnnouncementList($param, $app = '')
    {
        $param['addon_idcsmart_announcement_type_id'] = $param['addon_idcsmart_announcement_type_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aih.'.$param['orderby'] : 'aih.id';

    	$count = $this->alias('aih')
            ->field('aih.id')
            ->where(function ($query) use($param, $app) {
                if(!empty($param['keywords'])){
                    $query->where('aih.title', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['addon_idcsmart_announcement_type_id'])){
                    $query->where('aih.addon_idcsmart_announcement_type_id', $param['addon_idcsmart_announcement_type_id']);
                }
                if($app=='home'){
                    $query->where('aih.hidden', 0);
                }
            })
            ->count();

        $IdcsmartAnnouncementLogic = new IdcsmartAnnouncementLogic();
        $config = $IdcsmartAnnouncementLogic->getDefaultConfig();

        $list = $this->alias('aih')
            ->field('aih.id,aih.title,aih.img,aiht.name type,a.name admin,aih.create_time,aih.hidden')
            ->leftJoin('addon_idcsmart_announcement_type aiht', 'aiht.id=aih.addon_idcsmart_announcement_type_id')
            ->leftJoin('admin a', 'a.id=aih.admin_id')
            ->where(function ($query) use($param, $app) {
                if(!empty($param['keywords'])){
                    $query->where('aih.title', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['addon_idcsmart_announcement_type_id'])){
                    $query->where('aih.addon_idcsmart_announcement_type_id', $param['addon_idcsmart_announcement_type_id']);
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

    # 公告详情
    public function idcsmartAnnouncementDetail($id, $app = '')
    {
        $IdcsmartAnnouncementLogic = new IdcsmartAnnouncementLogic();
        $config = $IdcsmartAnnouncementLogic->getDefaultConfig();

        $idcsmartAnnouncement = $this->alias('aih')
            ->field('aih.id,aih.addon_idcsmart_announcement_type_id,aiht.name type,aih.title,aih.content,aih.keywords,aih.attachment,aih.create_time,aih.hidden')
            ->leftJoin('addon_idcsmart_announcement_type aiht', 'aiht.id=aih.addon_idcsmart_announcement_type_id')
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

        if(empty($idcsmartAnnouncement)){
            return (object)[];
        }

        if($app=='home'){
            if($idcsmartAnnouncement['hidden']==1){
                return (object)[];
            }
            $next = $this->field('id,title')->where('hidden', 0)->where('addon_idcsmart_announcement_type_id', $idcsmartAnnouncement['addon_idcsmart_announcement_type_id'])->where('id', '<', $idcsmartAnnouncement['id'])->order('id', 'desc')->find();
            $prev = $this->field('id,title')->where('hidden', 0)->where('addon_idcsmart_announcement_type_id', $idcsmartAnnouncement['addon_idcsmart_announcement_type_id'])->where('id', '>', $idcsmartAnnouncement['id'])->order('id', 'asc')->find();
            $idcsmartAnnouncement['prev'] = !empty($prev) ? ['id'=>$prev['id'], 'title'=>$prev['title']] : (object)[];
            $idcsmartAnnouncement['next'] = !empty($next) ? ['id'=>$next['id'], 'title'=>$next['title']] : (object)[];
            unset($idcsmartAnnouncement['hidden']);
        }else{
            unset($idcsmartAnnouncement['create_time']);
        }

        return $idcsmartAnnouncement;
    }

    # 添加公告
    public function createIdcsmartAnnouncement($param)
    {
        $idcsmartAnnouncementType = IdcsmartAnnouncementTypeModel::find($param['addon_idcsmart_announcement_type_id']);
        if(empty($idcsmartAnnouncementType)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            # 移动附件
            $IdcsmartAnnouncementLogic = new IdcsmartAnnouncementLogic();
            $fileUpload = $IdcsmartAnnouncementLogic->getDefaultConfig('file_upload');
            $UploadLogic = new UploadLogic($fileUpload);
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $idcsmartAnnouncement = $this->create([
                'admin_id' => $adminId,
                'addon_idcsmart_announcement_type_id' => $param['addon_idcsmart_announcement_type_id'],
                'title' => $param['title'],
                'keywords' => $param['keywords'] ?? '',
                'img' => $param['img'] ?? '',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content' => $param['content'],
                'hidden' => $param['hidden'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang_plugins('log_admin_add_announcement', ['{admin}'=>request()->admin_name,'{title}'=>$param['title']]), 'addon_idcsmart_announcement', $idcsmartAnnouncement->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 修改公告
    public function updateIdcsmartAnnouncement($param)
    {
        // 验证公告ID
        $idcsmartAnnouncement = $this->find($param['id']);
        if(empty($idcsmartAnnouncement)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_is_not_exist')];
        }

        $idcsmartAnnouncementType = IdcsmartAnnouncementTypeModel::find($param['addon_idcsmart_announcement_type_id']);
        if(empty($idcsmartAnnouncementType)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            # 移动附件
            $IdcsmartAnnouncementLogic = new IdcsmartAnnouncementLogic();
            $fileUpload = $IdcsmartAnnouncementLogic->getDefaultConfig('file_upload');
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
                'addon_idcsmart_announcement_type_id' => $param['addon_idcsmart_announcement_type_id'],
                'title' => $param['title'],
                'keywords' => $param['keywords'] ?? '',
                'img' => $param['img'] ?? '',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content' => $param['content'],
                'hidden' => $param['hidden'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang_plugins('log_admin_edit_announcement', ['{admin}'=>request()->admin_name,'{title}'=>$param['title']]), 'addon_idcsmart_announcement', $idcsmartAnnouncement->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除公告
    public function deleteIdcsmartAnnouncement($id)
    {
        // 验证公告ID
        $idcsmartAnnouncement = $this->find($id);
        if(empty($idcsmartAnnouncement)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_is_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_announcement', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartAnnouncement['title']]), 'addon_idcsmart_announcement', $idcsmartAnnouncement->id);
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    # 隐藏/显示公告
    public function hiddenIdcsmartAnnouncement($param)
    {
        // 验证公告ID
        $idcsmartAnnouncement = $this->find($param['id']);
        if(empty($idcsmartAnnouncement)){
            return ['status'=>400, 'msg'=>lang_plugins('announcement_is_not_exist')];
        }

        $hidden = $param['hidden'];

        if ($idcsmartAnnouncement['hidden'] == $hidden){
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
                active_log(lang_plugins('log_admin_hide_announcement', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartAnnouncement['title']]), 'addon_idcsmart_announcement', $idcsmartAnnouncement->id);
            }else{
                active_log(lang_plugins('log_admin_show_announcement', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartAnnouncement['title']]), 'addon_idcsmart_announcement', $idcsmartAnnouncement->id);
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