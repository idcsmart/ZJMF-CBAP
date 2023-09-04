<?php
namespace addon\idcsmart_file_download\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UploadLogic;
use addon\idcsmart_file_download\logic\IdcsmartFileDownloadLogic;
use app\common\model\ProductModel;
use app\common\model\HostModel;

/**
 * @title 文件模型
 * @desc 文件模型
 * @use addon\idcsmart_file_download\model\IdcsmartFileModel
 */
class IdcsmartFileModel extends Model
{
    protected $name = 'addon_idcsmart_file';

    // 设置字段信息
    protected $schema = [
        'id'      		                => 'int',
        'addon_idcsmart_file_folder_id' => 'int',
        'name'     		                => 'string',
        'filename'     		            => 'string',
        'filetype'                      => 'string',
        'filesize'                      => 'int',
        'visible_range'                 => 'string',
        'hidden'                        => 'int',
        'admin_id'                      => 'int',
        'create_time'                   => 'int',
        'update_time'                   => 'int',
    ];

    # 文件列表
    public function idcsmartFileList($param, $app = '')
    {
        $param['addon_idcsmart_file_folder_id'] = $param['addon_idcsmart_file_folder_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'create_time']) ? 'aif.'.$param['orderby'] : 'aif.create_time';

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

    	$count = $this->alias('aif')
            ->field('aif.id')
            ->where(function ($query) use($param, $app, $hostCount, $fileId) {
                if(!empty($param['addon_idcsmart_file_folder_id'])){
                    $query->where('aif.addon_idcsmart_file_folder_id', $param['addon_idcsmart_file_folder_id']);
                }
                if(!empty($param['keywords'])){
                    $query->where('aif.name', 'like', "%{$param['keywords']}%");
                }
                if($app=='home'){
                    $query->where('aif.hidden', 0);
                    if($hostCount>0){
                        $query->whereIn('aif.id', $fileId);
                    }else{
                        $query->where('aif.visible_range', 'all');
                    }
                }
            })
            ->count();
        $list = $this->alias('aif')
            ->field('aif.id,aif.name,a.name admin,aif.filetype,aif.filesize,aif.create_time,aif.hidden')
            ->leftJoin('admin a', 'a.id=aif.admin_id')
            ->where(function ($query) use($param, $app, $hostCount, $fileId) {
                if(!empty($param['addon_idcsmart_file_folder_id'])){
                    $query->where('aif.addon_idcsmart_file_folder_id', $param['addon_idcsmart_file_folder_id']);
                }
                if(!empty($param['keywords'])){
                    $query->where('aif.name', 'like', "%{$param['keywords']}%");
                }
                if($app=='home'){
                    $query->where('aif.hidden', 0);
                    if($hostCount>0){
                        $query->whereIn('aif.id', $fileId);
                    }else{
                        $query->where('aif.visible_range', 'all');
                    }
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($app=='home'){
                unset($list[$key]['admin'], $list[$key]['hidden']);
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    # 文件详情
    public function idcsmartFileDetail($id, $app = '')
    {
        $idcsmartFile = $this->field('id,addon_idcsmart_file_folder_id,name,filename,filetype,visible_range,hidden')->find($id);

        if(empty($idcsmartFile)){
            return [];
        }

        if($app=='home'){
            if($idcsmartFile['hidden']==1){
                return [];
            }
            if($idcsmartFile['visible_range']!='all'){
                $clientId = get_client_id();
                $hostCount = HostModel::where('status', 'Active')->where('client_id', $clientId)->count();
                $productId = HostModel::where('status', 'Active')->where('client_id', $clientId)->column('product_id');
                $fileId = IdcsmartFileLinkModel::whereIn('product_id', $productId)->column('addon_idcsmart_file_id');
                if($hostCount==0){
                    return [];
                }
                if($idcsmartFile['visible_range']=='product' && !in_array($idcsmartFile['id'], $fileId)){
                    return [];
                }
            }
            $IdcsmartFileDownloadLogic = new IdcsmartFileDownloadLogic();
            $fileUpload = $IdcsmartFileDownloadLogic->getDefaultConfig('file_upload');

            $idcsmartFile['filename'] = $fileUpload.$idcsmartFile['filename'];
            
            unset($idcsmartFile['addon_idcsmart_file_folder_id'], $idcsmartFile['visible_range'], $idcsmartFile['hidden']);
        }else{
            $IdcsmartFileDownloadLogic = new IdcsmartFileDownloadLogic();
            $fileUpload = $IdcsmartFileDownloadLogic->getDefaultConfig('file_upload');

            $idcsmartFile['filename'] = $fileUpload.$idcsmartFile['filename'];
            if($idcsmartFile['visible_range']=='product'){
                $idcsmartFile['product_id'] = IdcsmartFileLinkModel::where('addon_idcsmart_file_id', $id)->column('product_id');
            }else{
                $idcsmartFile['product_id'] = [];
            }
            

            unset($idcsmartFile['hidden']);
        }

        return $idcsmartFile;
    }

    # 上传文件
    public function createIdcsmartFile($param)
    {
        foreach ($param['file'] as $key => $value) {
            $idcsmartFileFolder = IdcsmartFileFolderModel::find($value['addon_idcsmart_file_folder_id']);
            if(empty($idcsmartFileFolder)){
                return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_not_exist')];
            }
            $value['visible_range'] = $value['visible_range'] ?? 'all';
            if($value['visible_range']!='product'){
                $value['product_id'] = [];
            }
            if(!empty($value['product_id'])){
                foreach ($value['product_id'] as $k => $v) {
                    if(!is_integer($v)){
                        return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
                    }
                    if($v<=0){
                        return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
                    }   
                    $product = productModel::find($v);
                    if(empty($product)){
                        return ['status'=>400, 'msg'=>lang_plugins('product_is_not_exist')];
                    }
                }
                
            }
        }

        $this->startTrans();
        try {
            $adminId = get_admin_id();
            $time = time();
            $IdcsmartFileDownloadLogic = new IdcsmartFileDownloadLogic();
            $fileUpload = $IdcsmartFileDownloadLogic->getDefaultConfig('file_upload');
            $UploadLogic = new UploadLogic($fileUpload);

            $fileLinkArr = [];
            foreach ($param['file'] as $key => $value) {
                # 移动附件
                if (!empty($value['filename'])){
                    $result = $UploadLogic->moveTo($value['filename']);
                    if (isset($result['error'])){
                        throw new \Exception($result['error']);
                    }
                    $value['filesize'] = filesize($fileUpload.$value['filename']);
                    $value['filetype'] = pathinfo($fileUpload.$value['filename'], PATHINFO_EXTENSION);
                }

                $file = $this->create([
                    'admin_id' => $adminId,
                    'addon_idcsmart_file_folder_id' => $value['addon_idcsmart_file_folder_id']  ?? 0,
                    'name' => $value['name'] ?? '',
                    'filename' => $value['filename'] ?? '',
                    'filetype' => $value['filetype'] ?? '',
                    'filesize' => $value['filesize'] ?? '',
                    'visible_range' => $value['visible_range'] ?? 'all',
                    'hidden' => $value['hidden'] ?? 0,
                    'create_time' => $time
                ]);

                foreach ($value['product_id'] as $k => $v) {
                    $fileLinkArr[] = [
                        'addon_idcsmart_file_id' => $file->id,
                        'product_id' => $v
                    ];
                }
            }
            $IdcsmartFileLinkModel = new IdcsmartFileLinkModel();
            $IdcsmartFileLinkModel->saveAll($fileLinkArr);

            # 记录日志
            active_log(lang_plugins('log_admin_add_file', ['{admin}'=>request()->admin_name,'{name}'=>implode(',', array_column($param['file'], 'name'))]), 'addon_idcsmart_file');

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 编辑文件
    public function updateIdcsmartFile($param)
    {
        // 验证文件ID
        $idcsmartFile = $this->find($param['id']);
        if(empty($idcsmartFile)){
            return ['status'=>400, 'msg'=>lang_plugins('file_is_not_exist')];
        }

        $idcsmartFileFolder = IdcsmartFileFolderModel::find($param['addon_idcsmart_file_folder_id']);
        if(empty($idcsmartFileFolder)){
            return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_not_exist')];
        }

        $param['visible_range'] = $param['visible_range'] ?? 'all';
        if($param['visible_range']!='product'){
            $param['product_id'] = [];
        }
        if(!empty($param['product_id'])){
            foreach ($param['product_id'] as $k => $v) {
                if(!is_integer($v)){
                    return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
                }
                if($v<=0){
                    return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
                }  
                $product = productModel::find($v);
                if(empty($product)){
                    return ['status'=>400, 'msg'=>lang_plugins('product_is_not_exist')];
                }
            }
        }

        $this->startTrans();
        try {
            
            $this->update([
                'addon_idcsmart_file_folder_id' => $param['addon_idcsmart_file_folder_id']  ?? 0,
                'name' => $param['name'] ?? '',
                'visible_range' => $param['visible_range'] ?? 'all',
                'update_time' => time()
            ], ['id' => $param['id']]);

            $fileLinkArr = [];

            foreach ($param['product_id'] as $k => $v) {
                $fileLinkArr[] = [
                    'addon_idcsmart_file_id' => $param['id'],
                    'product_id' => $v
                ];
            }
            $IdcsmartFileLinkModel = new IdcsmartFileLinkModel();
            $IdcsmartFileLinkModel->where('addon_idcsmart_file_id', $param['id'])->delete();
            $IdcsmartFileLinkModel->saveAll($fileLinkArr);

            # 记录日志
            active_log(lang_plugins('log_admin_edit_file', ['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'addon_idcsmart_file', $idcsmartFile->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除文件
    public function deleteIdcsmartFile($id)
    {
        // 验证文件ID
        $idcsmartFile = $this->find($id);
        if(empty($idcsmartFile)){
            return ['status'=>400, 'msg'=>lang_plugins('file_is_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang_plugins('log_admin_delete_file', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartFile['name']]), 'addon_idcsmart_file', $idcsmartFile->id);

            $this->destroy($id);
            IdcsmartFileLinkModel::where('addon_idcsmart_file_id', $id)->delete();
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    # 隐藏/显示文件
    public function hiddenIdcsmartFile($param)
    {
        // 验证文件ID
        $idcsmartFile = $this->find($param['id']);
        if(empty($idcsmartFile)){
            return ['status'=>400, 'msg'=>lang_plugins('file_is_not_exist')];
        }

        $hidden = $param['hidden'];

        if ($idcsmartFile['hidden'] == $hidden){
            return ['status' => 400, 'msg' => lang_plugins('cannot_repeat_opreate')];
        }

        $this->startTrans();
        try {
            $this->update([
                'hidden' => $param['hidden'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            if($param['hidden']==1){
                active_log(lang_plugins('log_admin_hide_file', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartFile['name']]), 'addon_idcsmart_file', $idcsmartFile->id);
            }else{
                active_log(lang_plugins('log_admin_show_file', ['{admin}'=>request()->admin_name,'{name}'=>$idcsmartFile['name']]), 'addon_idcsmart_file', $idcsmartFile->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }

    # 移动文件
    public function moveIdcsmartFile($param)
    {
        // 验证文件ID
        $idcsmartFile = $this->find($param['id']);
        if(empty($idcsmartFile)){
            return ['status'=>400, 'msg'=>lang_plugins('file_is_not_exist')];
        }

        if ($idcsmartFile['addon_idcsmart_file_folder_id'] == $param['addon_idcsmart_file_folder_id']){
            return ['status' => 400, 'msg' => lang_plugins('cannot_repeat_opreate')];
        }

        $idcsmartFileFolder = IdcsmartFileFolderModel::find($param['addon_idcsmart_file_folder_id']);
        if(empty($idcsmartFileFolder)){
            return ['status'=>400, 'msg'=>lang_plugins('file_folder_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'addon_idcsmart_file_folder_id' => $param['addon_idcsmart_file_folder_id']  ?? 0,
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }
        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }
}