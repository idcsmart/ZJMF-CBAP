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
        'cron_release'                  => 'cron_release',
        'cron_release_time'             => 'cron_release_time',
        'create_time'                   => 'int',
        'update_time'                   => 'int',
    ];

    /**
     * 时间 2022-06-21
     * @title 新闻列表
     * @desc 新闻列表
     * @author theworld
     * @version v1
     * @param int addon_idcsmart_news_type_id - 分类ID 
     * @param string keywords - 关键字,搜索范围:标题
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @param string app - 前后台home前台admin后台index首页
     * @return array list - 新闻
     * @return int list[].id - 新闻ID
     * @return string list[].title - 标题
     * @return string list[].img - 新闻缩略图
     * @return string list[].type - 类型,前台不返回  
     * @return string list[].admin - 提交人,仅后台返回  
     * @return int list[].create_time - 创建时间 
     * @return int list[].hidden - 0显示1隐藏,仅后台返回  
     * @return int list[].cron_release - 是否定时发布(0=否,1=是),仅后台返回 
     * @return int list[].cron_release_time - 定时发布时间,仅后台返回  
     * @return int count - 新闻总数
     */
    public function idcsmartNewsList($param, $app = '')
    {
        $this->cronRelease();

        $param['addon_idcsmart_news_type_id'] = $param['addon_idcsmart_news_type_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aih.'.$param['orderby'] : 'aih.id';

        $where = function (Query $query) use($param, $app) {
            if(!empty($param['keywords'])){
                $query->where('aih.title', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['addon_idcsmart_news_type_id'])){
                $query->where('aih.addon_idcsmart_news_type_id', $param['addon_idcsmart_news_type_id']);
            }
            if($app=='home' || $app == 'index'){
                $query->where('aih.hidden', 0);
            }
        };
        
    	$count = $this->alias('aih')
            ->field('aih.id')
            ->where($where)
            ->count();

        $IdcsmartNewsLogic = new IdcsmartNewsLogic();
        $config = $IdcsmartNewsLogic->getDefaultConfig();

        $time = time();

        $list = $this->alias('aih')
            ->field('aih.id,aih.title,aih.img,aiht.name type,a.name admin,aih.create_time,aih.hidden,aih.cron_release,aih.cron_release_time')
            ->leftJoin('addon_idcsmart_news_type aiht', 'aiht.id=aih.addon_idcsmart_news_type_id')
            ->leftJoin('admin a', 'a.id=aih.admin_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($app=='home'){
                // 计算发布时间
                if($value['cron_release'] == 1 && $value['cron_release_time'] <= $time){
                    $list[$key]['create_time'] = $value['cron_release_time'];
                }
                unset($list[$key]['admin'], $list[$key]['type'], $list[$key]['hidden'], $list[$key]['cron_release'], $list[$key]['cron_release_time']);
            }else if($app=='index'){
                // 计算发布时间
                if($value['cron_release'] == 1 && $value['cron_release_time'] <= $time){
                    $list[$key]['create_time'] = $value['cron_release_time'];
                }
                unset($list[$key]['admin'], $list[$key]['hidden'], $list[$key]['cron_release'], $list[$key]['cron_release_time']);
            }else{
                if($value['cron_release'] == 1){
                    $list[$key]['create_time'] = $value['cron_release_time'];
                }
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-21
     * @title 新闻详情
     * @desc 新闻详情
     * @author theworld
     * @version v1
     * @param int id - 新闻ID required
     * @param string app - 前后台home前台admin后台
     * @return int id - 新闻ID
     * @return int addon_idcsmart_news_type_id - 分类ID
     * @return string type - 分类名
     * @return string title - 标题 
     * @return string content - 内容 
     * @return string keywords - 关键字 
     * @return array attachment - 附件
     * @return int create_time - 创建时间,仅前台返回  
     * @return int update_time - 更新时间,仅前台返回   
     * @return int hidden - 0:显示1:隐藏,仅后台返回 
     * @return int cron_release - 是否定时发布(0=否,1=是),仅后台返回 
     * @return int cron_release_time - 定时发布时间,仅后台返回 
     * @return object prev - 上一条新闻,仅前台返回 
     * @return string prev.id - 新闻ID,仅前台返回 
     * @return string prev.title - 标题,仅前台返回 
     * @return object next - 下一条新闻,仅前台返回 
     * @return string next.id - 新闻ID,仅前台返回 
     * @return string next.title - 标题,仅前台返回 
     */
    public function idcsmartNewsDetail($id, $app = '')
    {
        $IdcsmartNewsLogic = new IdcsmartNewsLogic();
        $config = $IdcsmartNewsLogic->getDefaultConfig();

        $idcsmartNews = $this->alias('aih')
            ->field('aih.id,aih.addon_idcsmart_news_type_id,aiht.name type,aih.title,aih.content,aih.keywords,aih.attachment,aih.create_time,aih.update_time,aih.hidden,aih.cron_release,aih.cron_release_time')
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

            // 计算发布时间
            if($idcsmartNews['cron_release'] == 1 && $idcsmartNews['cron_release_time'] <= time() && $idcsmartNews['update_time'] < $idcsmartNews['cron_release_time']){
                $idcsmartNews['update_time'] = $idcsmartNews['cron_release_time'];
            }
            unset($idcsmartNews['hidden'], $idcsmartNews['cron_release'], $idcsmartNews['cron_release_time']);
        }else{
            unset($idcsmartNews['create_time'], $idcsmartNews['update_time']);
        }

        return $idcsmartNews;
    }

    /**
     * 时间 2022-06-21
     * @title 添加新闻
     * @desc 添加新闻
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param int param.addon_idcsmart_news_type_id - 分类ID required
     * @param string param.keywords - 关键字 
     * @param string param.img - 新闻缩略图
     * @param array param.attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string param.content - 内容 required
     * @param int param.hidden - 0显示1隐藏 required
     * @param int param.cron_release - 是否定时发布(0=否,1=是) required
     * @param int param.cron_release_time - 定时发布时间 requireIf,cron_release=1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartNews($param)
    {
        $idcsmartNewsType = IdcsmartNewsTypeModel::find($param['addon_idcsmart_news_type_id']);
        if(empty($idcsmartNewsType)){
            return ['status'=>400, 'msg'=>lang_plugins('news_type_is_not_exist')];
        }
        // 默认显示
        $hidden = 0;
        if($param['cron_release'] == 1 && $param['cron_release_time'] > time()){
            $hidden = 1;
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
                'admin_id'                      => $adminId,
                'addon_idcsmart_news_type_id'   => $param['addon_idcsmart_news_type_id'],
                'title'                         => $param['title'],
                'keywords'                      => $param['keywords'] ?? '',
                'img'                           => $param['img'] ?? '',
                'attachment'                    => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content'                       => $param['content'],
                'hidden'                        => $hidden,
                'create_time'                   => time(),
                'cron_release'                  => $param['cron_release'],
                'cron_release_time'             => $param['cron_release_time'] ?? 0,
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

    /**
     * 时间 2022-06-21
     * @title 修改新闻
     * @desc 修改新闻
     * @author theworld
     * @version v1
     * @param int id - 新闻ID required
     * @param string title - 标题 required
     * @param int addon_idcsmart_news_type_id - 分类ID required
     * @param string keywords - 关键字
     * @param string img - 新闻缩略图
     * @param array attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string content - 内容 required
     * @param int hidden - 0显示1隐藏 required
     * @param int cron_release - 是否定时发布(0=否,1=是) required
     * @param int cron_release_time - 定时发布时间 requireIf,cron_release=1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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
                'admin_id'                      => $adminId,
                'addon_idcsmart_news_type_id'   => $param['addon_idcsmart_news_type_id'],
                'title'                         => $param['title'],
                'keywords'                      => $param['keywords'] ?? '',
                'img'                           => $param['img'] ?? '',
                'attachment'                    => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content'                       => $param['content'],
                // 'hidden' => $param['hidden'],
                'update_time'                   => time(),
                'cron_release'                  => $param['cron_release'],
                'cron_release_time'             => $param['cron_release_time'],
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

    /**
     * 时间 2022-06-21
     * @title 删除新闻
     * @desc 删除新闻
     * @author theworld
     * @version v1
     * @param int id - 新闻ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 隐藏/显示新闻
     * @desc 隐藏/显示新闻
     * @author theworld
     * @version v1
     * @param int param.id - 新闻ID required
     * @param int param.hidden - 0显示1隐藏 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2023-09-18
     * @title 定时发布
     * @desc  定时发布
     * @author hh
     * @version v1
     */
    public function cronRelease(){
        $this->where('cron_release', 1)->where('hidden', 1)->where('cron_release_time', '<=', time())->update(['hidden'=>0, 'cron_release'=>0]);
    }
}