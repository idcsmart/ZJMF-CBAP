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

    /**
     * 时间 2022-06-21
     * @title 公告列表
     * @desc 公告列表
     * @author theworld
     * @version v1
     * @param int param.addon_idcsmart_announcement_type_id - 分类ID 
     * @param string param.keywords - 关键字,搜索范围:标题
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @param string app - 前后台home前台admin后台index会员中心首页
     * @return array list - 公告
     * @return int list[].id - 公告ID
     * @return string list[].title - 标题
     * @return string list[].img - 公告缩略图
     * @return string list[].type - 类型,前台不返回 
     * @return string list[].admin - 提交人,仅后台返回 
     * @return int list[].create_time - 创建时间 
     * @return int list[].hidden - 0显示1隐藏,仅后台返回 
     * @return int count - 公告总数
     */
    public function idcsmartAnnouncementList($param, $app = '')
    {
        $param['addon_idcsmart_announcement_type_id'] = $param['addon_idcsmart_announcement_type_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aih.'.$param['orderby'] : 'aih.id';

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('aih.title', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['addon_idcsmart_announcement_type_id'])){
                $query->where('aih.addon_idcsmart_announcement_type_id', $param['addon_idcsmart_announcement_type_id']);
            }
            if($app=='home'){
                $query->where('aih.hidden', 0);
            }
        };
        
    	$count = $this->alias('aih')
            ->field('aih.id')
            ->where($where)
            ->count();

        $IdcsmartAnnouncementLogic = new IdcsmartAnnouncementLogic();
        $config = $IdcsmartAnnouncementLogic->getDefaultConfig();

        $list = $this->alias('aih')
            ->field('aih.id,aih.title,aih.img,aiht.name type,a.name admin,aih.create_time,aih.hidden')
            ->leftJoin('addon_idcsmart_announcement_type aiht', 'aiht.id=aih.addon_idcsmart_announcement_type_id')
            ->leftJoin('admin a', 'a.id=aih.admin_id')
            ->where($where)
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

    /**
     * 时间 2022-06-21
     * @title 公告详情
     * @desc 公告详情
     * @author theworld
     * @version v1
     * @url /admin/v1/announcement/:id
     * @method  GET
     * @param int id - 公告ID required
     * @param string app - 前后台home前台admin后台
     * @return int id - 公告ID
     * @return int addon_idcsmart_announcement_type_id - 分类ID
     * @return string type - 分类名
     * @return string title - 标题 
     * @return string content - 内容 
     * @return string keywords - 关键字 
     * @return array attachment - 附件
     * @return int create_time - 创建时间,仅前台返回 
     * @return int update_time - 更新时间,仅前台返回 
     * @return int hidden - 0:显示1:隐藏,仅后台返回
     * @return object prev - 上一条公告,仅前台返回
     * @return string prev.id - 公告ID,仅前台返回
     * @return string prev.title - 标题,仅前台返回
     * @return object next - 下一条公告,仅前台返回
     * @return string next.id - 公告ID,仅前台返回
     * @return string next.title - 标题,仅前台返回
     */
    public function idcsmartAnnouncementDetail($id, $app = '')
    {
        $IdcsmartAnnouncementLogic = new IdcsmartAnnouncementLogic();
        $config = $IdcsmartAnnouncementLogic->getDefaultConfig();

        $idcsmartAnnouncement = $this->alias('aih')
            ->field('aih.id,aih.addon_idcsmart_announcement_type_id,aiht.name type,aih.title,aih.content,aih.keywords,aih.attachment,aih.create_time,aih.update_time,aih.hidden')
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
            unset($idcsmartAnnouncement['create_time'], $idcsmartAnnouncement['update_time']);
        }

        return $idcsmartAnnouncement;
    }

    /**
     * 时间 2022-06-21
     * @title 添加公告
     * @desc 添加公告
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param int param.addon_idcsmart_announcement_type_id - 分类ID required
     * @param string param.keywords - 关键字 
     * @param string param.img - 公告缩略图
     * @param array param.attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string param.content - 内容 required
     * @param int param.hidden - 0显示1隐藏 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 修改公告
     * @desc 修改公告
     * @author theworld
     * @version v1
     * @param int param.id - 公告ID required
     * @param string param.title - 标题 required
     * @param int param.addon_idcsmart_announcement_type_id - 分类ID required
     * @param string param.keywords - 关键字
     * @param string param.img - 公告缩略图
     * @param array param.attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string param.content - 内容 required
     * @param int param.hidden - 0显示1隐藏 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 删除公告
     * @desc 删除公告
     * @author theworld
     * @version v1
     * @param int id - 公告ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 隐藏/显示公告
     * @desc 隐藏/显示公告
     * @author theworld
     * @version v1
     * @param int param.id - 公告ID required
     * @param int param.hidden - 0显示1隐藏 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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