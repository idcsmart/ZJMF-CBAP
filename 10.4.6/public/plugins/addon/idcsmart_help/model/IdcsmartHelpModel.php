<?php
namespace addon\idcsmart_help\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UploadLogic;
use addon\idcsmart_help\logic\IdcsmartHelpLogic;

/**
 * @title 帮助文档模型
 * @desc 帮助文档模型
 * @use addon\idcsmart_help\model\IdcsmartHelpModel
 */
class IdcsmartHelpModel extends Model
{
    protected $name = 'addon_idcsmart_help';

    // 设置字段信息
    protected $schema = [
        'id'      		                => 'int',
        'addon_idcsmart_help_type_id'   => 'int',
        'title'     		            => 'string',
        'content'     		            => 'string',
        'keywords'                      => 'string',
        'attachment'                    => 'string',
        'hidden'                        => 'string',
        'index_hidden'                  => 'string',
        'read'                          => 'string',
        'admin_id'                      => 'string',
        'cron_release'                  => 'int',
        'cron_release_time'             => 'int',
        'create_time'                   => 'int',
        'update_time'                   => 'int',
    ];

    /**
     * 时间 2022-06-20
     * @title 帮助文档列表
     * @desc 帮助文档列表
     * @author theworld
     * @version v1
     * @param int param.addon_idcsmart_help_type_id - 分类ID
     * @param string param.keywords - 关键字,搜索范围:标题
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 帮助文档
     * @return int list[].id - 帮助文档ID
     * @return string list[].title - 标题
     * @return string list[].type - 类型 
     * @return string list[].admin - 提交人 
     * @return int list[].create_time - 创建时间 
     * @return int list[].hidden - 0显示1隐藏 
     * @return int list[].cron_release - 是否定时发布(0=否,1=是) 
     * @return int list[].cron_release_time - 定时发布时间 
     * @return int count - 帮助文档总数
     */
    public function idcsmartHelpList($param)
    {
        $this->cronRelease();

        $param['addon_idcsmart_help_type_id'] = $param['addon_idcsmart_help_type_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aih.'.$param['orderby'] : 'aih.id';

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('aih.title', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['addon_idcsmart_help_type_id'])){
                $query->where('aih.addon_idcsmart_help_type_id', $param['addon_idcsmart_help_type_id']);
            }
        };
        
    	$count = $this->alias('aih')
            ->field('aih.id')
            ->where($where)
            ->count();
        $list = $this->alias('aih')
            ->field('aih.id,aih.title,aiht.name type,a.name admin,aih.create_time,aih.hidden,aih.cron_release,aih.cron_release_time')
            ->leftJoin('addon_idcsmart_help_type aiht', 'aiht.id=aih.addon_idcsmart_help_type_id')
            ->leftJoin('admin a', 'a.id=aih.admin_id')
            ->where($where)
            ->withAttr('create_time', function($value, $row){
                $time = $row['cron_release'] == 1 ? $row['cron_release_time'] : $value;
                // unset($row['cron_release'], $row['cron_release_time']);
                return $time;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档详情
     * @desc 帮助文档详情
     * @author theworld
     * @version v1
     * @param int id - 帮助文档ID required
     * @param string app - 前后台home前台admin后台
     * @return int id - 帮助文档ID
     * @return int addon_idcsmart_help_type_id - 分类ID,仅后台返回
     * @return string title - 标题 
     * @return string content - 内容 
     * @return string keywords - 关键字 
     * @return array attachment - 附件
     * @return int hidden - 0:显示1:隐藏,仅后台返回
     * @return int create_time - 创建时间,仅前台返回
     * @return int update_time - 更新时间
     * @return int cron_release - 是否定时发布(0=否,1=是),仅后台返回
     * @return int cron_release_time - 定时发布时间,仅后台返回
     * @return object prev - 上一篇文档,仅前台返回
     * @return string prev.id - 文档ID,仅前台返回
     * @return string prev.title - 标题,仅前台返回
     * @return object next - 下一篇文档,仅前台返回
     * @return string next.id - 文档ID,仅前台返回
     * @return string next.title - 标题,仅前台返回
     */
    public function idcsmartHelpDetail($id, $app = '')
    {
        $IdcsmartHelpLogic = new IdcsmartHelpLogic();
        $config = $IdcsmartHelpLogic->getDefaultConfig();

        $idcsmartHelp = $this->field('id,addon_idcsmart_help_type_id,title,content,keywords,attachment,hidden,create_time,update_time,cron_release,cron_release_time')
            ->withAttr('attachment',function ($value) use ($config){
                $attachments = array_filter(explode(',', $value));
                $attachmentsFilter = [];
                if (!empty($attachments)){
                    foreach ($attachments as &$attachment){
                        $attachmentsFilter[] = getOssUrl(['file_path'=>$config['file_upload'],'file_name'=>$attachment]);//$config['get_file_upload'] . $attachment;
                    }
                }
                return $attachmentsFilter;
            })
            ->find($id);

        if(empty($idcsmartHelp)){
            return (object)[];
        }
        
        if($app=='home'){
            if($idcsmartHelp['hidden']==1){
                return (object)[];
            }
            $this->where('id', $id)->inc('read', 1)->update();
            $prev = $this->field('id,title')->where('hidden', 0)->where('addon_idcsmart_help_type_id', $idcsmartHelp['addon_idcsmart_help_type_id'])->where('id', '<', $idcsmartHelp['id'])->order('id', 'desc')->find();
            $next = $this->field('id,title')->where('hidden', 0)->where('addon_idcsmart_help_type_id', $idcsmartHelp['addon_idcsmart_help_type_id'])->where('id', '>', $idcsmartHelp['id'])->order('id', 'asc')->find();
            $idcsmartHelp['prev'] = !empty($prev) ? ['id'=>$prev['id'], 'title'=>$prev['title']] : (object)[];
            $idcsmartHelp['next'] = !empty($next) ? ['id'=>$next['id'], 'title'=>$next['title']] : (object)[];

            // 计算发布时间
            if($idcsmartHelp['cron_release'] == 1 && $idcsmartHelp['cron_release_time'] <= time() && $idcsmartHelp['update_time'] < $idcsmartHelp['cron_release_time']){
                // $idcsmartHelp['create_time'] = $idcsmartHelp['cron_release_time'];
                $idcsmartHelp['update_time'] = $idcsmartHelp['cron_release_time'];
            }
            unset($idcsmartHelp['addon_idcsmart_help_type_id'], $idcsmartHelp['hidden'], $idcsmartHelp['cron_release'], $idcsmartHelp['cron_release_time']);
        }else{
            unset($idcsmartHelp['create_time']);
        }
        return $idcsmartHelp;
    }

    /**
     * 时间 2022-06-20
     * @title 添加帮助文档
     * @desc 添加帮助文档
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param int param.addon_idcsmart_help_type_id - 分类ID required
     * @param string param.keywords - 关键字 
     * @param array param.attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string param.content - 内容 required
     * @param int param.cron_release - 是否定时发布(0=否,1=是) required
     * @param int param.cron_release_time - 定时发布时间 requireIf,cron_release=1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartHelp($param)
    {
        $idcsmartHelpType = IdcsmartHelpTypeModel::find($param['addon_idcsmart_help_type_id']);
        if(empty($idcsmartHelpType)){
            return ['status'=>400, 'msg'=>lang_plugins('help_type_is_not_exist')];
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
            $IdcsmartHelpLogic = new IdcsmartHelpLogic();
            $fileUpload = $IdcsmartHelpLogic->getDefaultConfig('file_upload');
            $UploadLogic = new UploadLogic($fileUpload);
            if (!empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $help = $this->create([
                'admin_id'                      => $adminId,
                'addon_idcsmart_help_type_id'   => $param['addon_idcsmart_help_type_id'],
                'title'                         => $param['title'],
                'keywords'                      => $param['keywords'] ?? '',
                'attachment'                    => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content'                       => $param['content'],
                'hidden'                        => $hidden,
                'create_time'                   => time(),
                'cron_release'                  => $param['cron_release'],
                'cron_release_time'             => $param['cron_release_time'] ?? 0,
            ]);

            # 记录日志
            active_log(lang_plugins('log_admin_add_help', ['{admin}'=>request()->admin_name,'{title}'=>$param['title']]), 'addon_idcsmart_help', $help->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            //return ['status' => 400, 'msg' => lang_plugins('create_fail')];
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    /**
     * 时间 2022-06-20
     * @title 修改帮助文档
     * @desc 修改帮助文档
     * @author theworld
     * @version v1
     * @param int param.id - 帮助文档ID required
     * @param string param.title - 标题 required
     * @param int param.addon_idcsmart_help_type_id - 分类ID required
     * @param string param.keywords - 关键字 
     * @param array param.attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string param.content - 内容 required
     * @param int param.cron_release - 是否定时发布(0=否,1=是) required
     * @param int param.cron_release_time - 定时发布时间 requireIf,cron_release=1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateIdcsmartHelp($param)
    {
        // 验证帮助文档ID
        $idcsmartHelp = $this->find($param['id']);
        if(empty($idcsmartHelp)){
            return ['status'=>400, 'msg'=>lang_plugins('help_is_not_exist')];
        }

        $idcsmartHelpType = IdcsmartHelpTypeModel::find($param['addon_idcsmart_help_type_id']);
        if(empty($idcsmartHelpType)){
            return ['status'=>400, 'msg'=>lang_plugins('help_type_is_not_exist')];
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();

            # 移动附件
            $IdcsmartHelpLogic = new IdcsmartHelpLogic();
            $fileUpload = $IdcsmartHelpLogic->getDefaultConfig('file_upload');
            $UploadLogic = new UploadLogic($fileUpload);
            if (!empty($param['attachment'])){
                foreach ($param['attachment'] as $key => $value) {
                    $result = $UploadLogic->moveTo($value);
                    if (isset($result['error'])){
                        throw new \Exception($result['error']);
                    }
                }
            }

            $this->update([
                'admin_id'                      => $adminId,
                'addon_idcsmart_help_type_id'   => $param['addon_idcsmart_help_type_id'],
                'title'                         => $param['title'],
                'keywords'                      => $param['keywords'] ?? '',
                'attachment'                    => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content'                       => $param['content'],
                // 'hidden' => $param['hidden'],
                'update_time'                   => time(),
                'cron_release'                  => $param['cron_release'],
                'cron_release_time'             => $param['cron_release_time'],
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang_plugins('log_admin_edit_help', ['{admin}'=>request()->admin_name,'{title}'=>$param['title']]), 'addon_idcsmart_help', $idcsmartHelp->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    /**
     * 时间 2022-06-20
     * @title 删除帮助文档
     * @desc 删除帮助文档
     * @author theworld
     * @version v1
     * @param int id - 帮助文档ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteIdcsmartHelp($id)
    {
        // 验证帮助文档ID
        $idcsmartHelp = $this->find($id);
        if(empty($idcsmartHelp)){
            return ['status'=>400, 'msg'=>lang_plugins('help_is_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_help', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartHelp['title']]), 'addon_idcsmart_help', $idcsmartHelp->id);

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
     * 时间 2022-06-20
     * @title 隐藏/显示帮助文档
     * @desc 隐藏/显示帮助文档
     * @author theworld
     * @version v1
     * @param int param.id - 帮助文档ID required
     * @param int param.hidden - 0显示1隐藏 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function hiddenIdcsmartHelp($param)
    {
        // 验证帮助文档ID
        $idcsmartHelp = $this->find($param['id']);
        if(empty($idcsmartHelp)){
            return ['status'=>400, 'msg'=>lang_plugins('help_is_not_exist')];
        }

        $hidden = $param['hidden'];

        if ($idcsmartHelp['hidden'] == $hidden){
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
                active_log(lang_plugins('log_admin_hide_help', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartHelp['title']]), 'addon_idcsmart_help', $idcsmartHelp->id);
            }else{
                active_log(lang_plugins('log_admin_show_help', ['{admin}'=>request()->admin_name,'{title}'=>$idcsmartHelp['title']]), 'addon_idcsmart_help', $idcsmartHelp->id);
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