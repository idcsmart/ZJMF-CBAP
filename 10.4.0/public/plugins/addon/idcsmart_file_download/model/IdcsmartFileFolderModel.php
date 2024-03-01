<?php
namespace addon\idcsmart_file_download\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ProductModel;
use app\common\model\ClientModel;
use app\common\model\HostModel;

/**
 * @title 文件夹模型
 * @desc 文件夹模型
 * @use addon\idcsmart_file_download\model\IdcsmartFileFolderModel
 */
class IdcsmartFileFolderModel extends Model
{
    protected $name = 'addon_idcsmart_file_folder';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'name'              => 'string',
        'default'           => 'int',
        'admin_id'     		=> 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',

    ];

    /**
     * 时间 2022-06-22
     * @title 获取文件夹
     * @desc 获取文件夹
     * @author theworld
     * @version v1
     * @param string app - 前后台home前台admin后台 
     * @return array list - 文件夹
     * @return int list[].id - 文件夹ID
     * @return string list[].name - 名称
     * @return int list[].default - 默认文件夹0否1是
     * @return string list[].admin - 修改人,仅后台返回 
     * @return int list[].update_time - 修改时间,仅后台返回
     * @return int list[].file_num - 文件数量 
     * @return int count - 全部文件数量 
     */
    public function idcsmartFileFolderList($app = '')
    {
        $list = $this->alias('aiff')
            ->field('aiff.id,aiff.name,aiff.default,a.name admin,aiff.update_time')
            ->leftJoin('admin a', 'a.id=aiff.admin_id')
            ->select()
            ->toArray();

        // 前台过滤
        if($app=='home'){
            $clientId = get_client_id();
            $hostCount = HostModel::where('status', 'Active')->where('client_id', $clientId)->count();
            $productId = HostModel::where('status', 'Active')->where('client_id', $clientId)->column('product_id');
            $fileId1 = IdcsmartFileLinkModel::whereIn('product_id', $productId)->column('addon_idcsmart_file_id');
            $fileId2 = IdcsmartFileModel::whereIn('visible_range', ['all', 'host'])->column('id');
            $fileId = array_merge($fileId1, $fileId2);
        }else{
            $hostCount = 0;
            $fileId = [];
        }
        $file = IdcsmartFileModel::field('addon_idcsmart_file_folder_id,COUNT(id) file_num')
            ->where(function ($query) use($app, $hostCount, $fileId) {
                if($app=='home'){
                    $query->where('hidden', 0);
                    if($hostCount>0){
                        $query->whereIn('id', $fileId);
                    }else{
                        $query->where('visible_range', 'all');
                    }
                }
            })
            ->group('addon_idcsmart_file_folder_id')
            ->select()
            ->toArray();
        $file = array_column($file, 'file_num', 'addon_idcsmart_file_folder_id');
        foreach ($list as $key => $value) {
            $list[$key]['file_num'] = $file[$value['id']] ?? 0;
            if($app=='home'){
                unset($list[$key]['admin'], $list[$key]['update_time']);
            }
        }
        $count = array_sum(array_column($list, 'file_num'));

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-21
     * @title 添加文件夹
     * @desc 添加文件夹
     * @author theworld
     * @version v1
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartFileFolder($param)
    {
        $this->startTrans();
        try {
            $adminId = get_admin_id();

            $idcsmartFileFolder = $this->create([
                'admin_id' => $adminId,
                'name' => $param['name'],
                'create_time' => time(),
                'update_time' => time()
            ]);

            # 记录日志
            active_log(lang_plugins('log_admin_add_file_folder', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_file_folder', $idcsmartFileFolder->id);

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
     * @title 修改文件夹
     * @desc 修改文件夹
     * @author theworld
     * @version v1
     * @param int param.id - 文件夹ID required
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateIdcsmartFileFolder($param)
    {
        // 验证文件夹ID
        $idcsmartFileFolder = $this->find($param['id']);
        if(empty($idcsmartFileFolder)){
            return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_not_exist')];
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
            active_log(lang_plugins('log_admin_edit_file_folder', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_file_folder', $idcsmartFileFolder->id);

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
     * @title 删除文件夹
     * @desc 删除文件夹
     * @author theworld
     * @version v1
     * @param int id - 文件夹ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteIdcsmartFileFolder($id)
    {
        // 验证文件夹ID
        $idcsmartFileFolder = $this->find($id);
        if(empty($idcsmartFileFolder)){
            return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_not_exist')];
        }

        $count = IdcsmartFileModel::where('addon_idcsmart_file_folder_id', $id)->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_used')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_file_folder', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartFileFolder['name']]), 'addon_idcsmart_file_folder', $idcsmartFileFolder->id);

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
     * 时间 2022-09-23
     * @title 设置默认文件夹
     * @desc 设置默认文件夹
     * @author theworld
     * @version v1
     * @param int id - 文件夹ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function setDefaultFileFolder($id)
    {
        // 验证文件夹ID
        $idcsmartFileFolder = $this->find($id);
        if(empty($idcsmartFileFolder)){
            return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_not_exist')];
        }
        if($idcsmartFileFolder['default']==1){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_repeat_opreate')];
        }

        $this->startTrans();
        try {
            $this->update([
                'default' => 0
            ], ['default' => 1]);

            $this->update([
                'default' => 1
            ], ['id' => $id]);

            # 记录日志
            active_log(lang_plugins('log_admin_default_file_folder', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartFileFolder['name']]), 'addon_idcsmart_file_folder', $idcsmartFileFolder->id);

            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}