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
        'create_time'                   => 'int',
        'update_time'                   => 'int',
    ];

    # 帮助文档列表
    public function idcsmartHelpList($param)
    {
        $param['addon_idcsmart_help_type_id'] = $param['addon_idcsmart_help_type_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aih.'.$param['orderby'] : 'aih.id';

    	$count = $this->alias('aih')
            ->field('aih.id')
            ->where(function ($query) use($param) {
                if(!empty($param['keywords'])){
                    $query->where('aih.title', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['addon_idcsmart_help_type_id'])){
                    $query->where('aih.addon_idcsmart_help_type_id', $param['addon_idcsmart_help_type_id']);
                }
            })
            ->count();
        $list = $this->alias('aih')
            ->field('aih.id,aih.title,aiht.name type,a.name admin,aih.create_time,aih.hidden')
            ->leftJoin('addon_idcsmart_help_type aiht', 'aiht.id=aih.addon_idcsmart_help_type_id')
            ->leftJoin('admin a', 'a.id=aih.admin_id')
            ->where(function ($query) use($param) {
                if(!empty($param['keywords'])){
                    $query->where('aih.title', 'like', "%{$param['keywords']}%");
                }
                if(!empty($param['addon_idcsmart_help_type_id'])){
                    $query->where('aih.addon_idcsmart_help_type_id', $param['addon_idcsmart_help_type_id']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        return ['list' => $list, 'count' => $count];
    }

    # 帮助文档详情
    public function idcsmartHelpDetail($id, $app = '')
    {
        $IdcsmartHelpLogic = new IdcsmartHelpLogic();
        $config = $IdcsmartHelpLogic->getDefaultConfig();

        $idcsmartHelp = $this->field('id,addon_idcsmart_help_type_id,title,content,keywords,attachment,hidden,create_time,update_time')
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
            unset($idcsmartHelp['addon_idcsmart_help_type_id'], $idcsmartHelp['hidden']);
        }else{
            unset($idcsmartHelp['create_time']);
        }

        return $idcsmartHelp;
    }

    # 添加帮助文档
    public function createIdcsmartHelp($param)
    {
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
                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $help = $this->create([
                'admin_id' => $adminId,
                'addon_idcsmart_help_type_id' => $param['addon_idcsmart_help_type_id'],
                'title' => $param['title'],
                'keywords' => $param['keywords'] ?? '',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content' => $param['content'],
                'hidden' => $param['hidden'],
                'create_time' => time()
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

    # 修改帮助文档
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
                'addon_idcsmart_help_type_id' => $param['addon_idcsmart_help_type_id'],
                'title' => $param['title'],
                'keywords' => $param['keywords'] ?? '',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment'])) ? implode(',', $param['attachment']) : '',
                'content' => $param['content'],
                'hidden' => $param['hidden'],
                'update_time' => time()
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

    # 删除帮助文档
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

    # 隐藏/显示帮助文档
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
}