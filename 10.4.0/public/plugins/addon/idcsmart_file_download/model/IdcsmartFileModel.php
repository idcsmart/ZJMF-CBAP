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
        'description'                   => 'string',
        'global_order'                  => 'int',
        'order'                         => 'int',
    ];

    /**
     * 时间 2022-06-21
     * @title 文件列表
     * @desc 文件列表
     * @author theworld
     * @version v1
     * @url /admin/v1/file
     * @method  GET
     * @param int param.addon_idcsmart_file_folder_id - 文件夹ID 
     * @param string param.keywords - 关键字,搜索范围:文件名
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @param string app - 前后台home前台admin后台 
     * @return array list - 文件
     * @return int list[].id - 文件ID
     * @return string list[].name - 文件名
     * @return string list[].admin - 上传人,仅后台返回
     * @return string list[].filetype - 文件类型 
     * @return string list[].filesize - 文件大小 
     * @return int list[].create_time - 上传时间 
     * @return int list[].hidden - 0显示1隐藏,仅后台返回 
     * @return string list[].description - 描述 
     * @return int count - 文件总数
     */
    public function idcsmartFileList($param, $app = '')
    {
        $param['addon_idcsmart_file_folder_id'] = $param['addon_idcsmart_file_folder_id'] ?? 0;
        $param['keywords'] = $param['keywords'] ?? '';
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

        $where = function (Query $query) use($param, $app, $hostCount, $fileId) {
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
        };

    	$count = $this->alias('aif')
            ->field('aif.id')
            ->where($where)
            ->count();
        $list = $this->alias('aif')
            ->field('aif.id,aif.name,a.name admin,aif.filetype,aif.filesize,aif.create_time,aif.hidden,aif.description')
            ->leftJoin('admin a', 'a.id=aif.admin_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('aif.global_order', 'asc')
            ->order('aif.order', 'asc')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($app=='home'){
                unset($list[$key]['admin'], $list[$key]['hidden']);
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2022-06-20
     * @title 文件详情
     * @desc 文件详情
     * @author theworld
     * @version v1
     * @param int id - 文件ID required
     * @param string app - 前后台home前台admin后台 
     * @return int id - 文件ID
     * @return string name - 名称 
     * @return string filename - 文件名 
     * @return int addon_idcsmart_file_folder_id - 文件夹ID,仅后台返回 
     * @return string visible_range - 可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户,仅后台返回 
     * @return array product_id - 商品ID,visible_range为product时需要 
     * @return string description - 描述 
     */
    public function idcsmartFileDetail($id, $app = '')
    {
        $idcsmartFile = $this->field('id,addon_idcsmart_file_folder_id,name,filename,visible_range,hidden,description')->find($id);

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

    /**
     * 时间 2022-06-21
     * @title 上传文件
     * @desc 上传文件
     * @author theworld
     * @version v1
     * @param array param.file - 文件 required
     * @param string param.file[].name - 名称 required
     * @param int param.file[].addon_idcsmart_file_folder_id - 文件夹ID required
     * @param string param.file[].filename - 文件真实名称,需调用后台公共接口文件上传获取新的save_name传入 required
     * @param string param.file[].visible_range - 可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户 required
     * @param array param.file[].product_id - 商品ID,visible_range为product时需要
     * @param int param.file[].hidden - 0显示1隐藏 required
     * @param string param.file[].description - 描述
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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
                    'description' => $value['description'] ?? '',
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

    /**
     * 时间 2022-06-21
     * @title 编辑文件
     * @desc 编辑文件
     * @author theworld
     * @version v1
     * @param int param.id - 文件ID required
     * @param string param.name - 名称 required
     * @param int param.addon_idcsmart_file_folder_id - 文件夹ID required
     * @param string param.visible_range - 可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户 required
     * @param array param.product_id - 商品ID,visible_range为product时需要
     * @param string param.description - 描述
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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
                'description' => $param['description'] ?? '',
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

    /**
     * 时间 2022-06-21
     * @title 删除文件
     * @desc 删除文件
     * @author theworld
     * @version v1
     * @param int id - 文件ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 隐藏/显示文件
     * @desc 隐藏/显示文件
     * @author theworld
     * @version v1
     * @param int param.id - 文件ID required
     * @param int param.hidden - 0显示1隐藏 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-21
     * @title 移动文件
     * @desc 移动文件
     * @author theworld
     * @version v1
     * @param int param.id - 文件ID required
     * @param int param.addon_idcsmart_file_folder_id - 文件夹ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

    /**
     * 时间 2022-06-22
     * @title 文件排序
     * @desc 文件排序
     * @author theworld
     * @version v1
     * @param int param.addon_idcsmart_file_folder_id - 文件夹ID required
     * @param array param.id - 文件ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function idcsmartFileOrder($param)
    {
        $id = $param['id'] ?? [];

        # 基础验证
        if(empty($param['addon_idcsmart_file_folder_id'])){
            $files = $this->select()->toArray();
        }else{
            $files = $this->where('addon_idcsmart_file_folder_id', $param['addon_idcsmart_file_folder_id'])->select()->toArray();
        }
        
        if (count($files)!=count($id)){
            return ['status'=>400,'msg'=>lang_plugins('file_is_not_exist')];
        }

        # 排序处理
        $this->startTrans();
        try{
            if(empty($param['addon_idcsmart_file_folder_id'])){
                foreach ($id as $key => $value) {
                    $this->update([
                        'global_order' => $key
                    ], ['id' => $value]);
                }
            }else{
                foreach ($id as $key => $value) {
                    $this->update([
                        'order' => $key
                    ], ['id' => $value]);
                }
            }
            

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>lang_plugins('fail_message') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }
}