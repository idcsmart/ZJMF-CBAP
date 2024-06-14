<?php
namespace addon\idcsmart_file_download\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_file_download\model\IdcsmartFileModel;
use addon\idcsmart_file_download\model\IdcsmartFileFolderModel;
use addon\idcsmart_file_download\validate\IdcsmartFileDownloadValidate;

/**
 * @title 文件下载(后台)
 * @desc 文件下载(后台)
 * @use addon\idcsmart_file_download\controller\AdminIndexController
 */
class AdminIndexController extends PluginAdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartFileDownloadValidate();
    }

    /**
     * 时间 2022-06-21
     * @title 文件列表
     * @desc 文件列表
     * @author theworld
     * @version v1
     * @url /admin/v1/file
     * @method  GET
     * @param int addon_idcsmart_file_folder_id - 文件夹ID 
     * @param string keywords - 关键字,搜索范围:文件名
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 文件
     * @return int list[].id - 文件ID
     * @return string list[].name - 文件名
     * @return string list[].admin - 上传人
     * @return string list[].filetype - 文件类型 
     * @return string list[].filesize - 文件大小 
     * @return int list[].create_time - 上传时间 
     * @return int list[].hidden - 0显示1隐藏 
     * @return string list[].description - 描述 
     * @return int count - 文件总数
     */
    public function idcsmartFileList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 获取文件列表
        $data = $IdcsmartFileModel->idcsmartFileList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 文件详情
     * @desc 文件详情
     * @author theworld
     * @version v1
     * @url /admin/v1/file/:id
     * @method  GET
     * @param int id - 文件ID required
     * @return object file - 文件
     * @return int file.id - 文件ID
     * @return string file.name - 名称 
     * @return string file.filename - 文件名 
     * @return int file.addon_idcsmart_file_folder_id - 文件夹ID 
     * @return string file.visible_range - 可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户 
     * @return array file.product_id - 商品ID,visible_range为product时需要 
     * @return string file.description - 描述 
     */
    public function idcsmartFileDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 获取文件
        $file = $IdcsmartFileModel->idcsmartFileDetail($param['id']);
        if(!empty($file)){
            unset($file['filename'], $file['filetype']);
        }else{
            $file = (object)$file;
        }

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'file' => $file
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 上传文件
     * @desc 上传文件
     * @author theworld
     * @version v1
     * @url /admin/v1/file
     * @method  POST
     * @param array file - 文件 required
     * @param string file[].name - 名称 required
	 * @param int file[].addon_idcsmart_file_folder_id - 文件夹ID required
     * @param string file[].filename - 文件真实名称,需调用后台公共接口文件上传获取新的save_name传入 required
     * @param string file[].visible_range - 可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户 required
     * @param array file[].product_id - 商品ID,visible_range为product时需要
     * @param int file[].hidden - 0显示1隐藏 required
     * @param string file[].description - 描述
     */
    public function createIdcsmartFile()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 上传文件
        $result = $IdcsmartFileModel->createIdcsmartFile($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 编辑文件
     * @desc 编辑文件
     * @author theworld
     * @version v1
     * @url /admin/v1/file/:id
     * @method  PUT
     * @param int id - 文件ID required
     * @param string name - 名称 required
     * @param int addon_idcsmart_file_folder_id - 文件夹ID required
     * @param string visible_range - 可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户 required
     * @param array product_id - 商品ID,visible_range为product时需要
     * @param string description - 描述
     */
    public function updateIdcsmartFile()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 编辑文件
        $result = $IdcsmartFileModel->updateIdcsmartFile($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除文件
     * @desc 删除文件
     * @author theworld
     * @version v1
     * @url /admin/v1/file/:id
     * @method  DELETE
     * @param int id - 文件ID required
     */
    public function deleteIdcsmartFile()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 删除文件
        $result = $IdcsmartFileModel->deleteIdcsmartFile($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 隐藏/显示文件
     * @desc 隐藏/显示文件
     * @author theworld
     * @version v1
     * @url /admin/v1/file/:id/hidden
     * @method  PUT
     * @param int id - 文件ID required
     * @param int hidden - 0显示1隐藏 required
     */
    public function hiddenIdcsmartFile()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 隐藏文件
        $result = $IdcsmartFileModel->hiddenIdcsmartFile($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 移动文件
     * @desc 移动文件
     * @author theworld
     * @version v1
     * @url /admin/v1/file/:id/move
     * @method  PUT
     * @param int id - 文件ID required
     * @param int addon_idcsmart_file_folder_id - 文件夹ID required
     */
    public function moveIdcsmartFile()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('move')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 移动文件
        $result = $IdcsmartFileModel->moveIdcsmartFile($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 下载文件
     * @desc 下载文件
     * @author theworld
     * @version v1
     * @url /admin/v1/file/:id/download
     * @method  GET
     * @param int id - 文件ID required
     */
    public function idcsmartFileDownload()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 获取文件
        $data = $IdcsmartFileModel->idcsmartFileDetail($param['id']);

        if(!empty($data)){
            // 直接重定向返回资源有跨域问题
            return json(['status'=>200,'msg'=>lang_plugins("success_message"),'data'=>[
                'url' => getOssUrl(['file_path'=>$data['file_path'],'file_name'=>$data['file_name']])['url']
            ]]);
        }else{
            return json(['status' => 400, 'msg' => lang_plugins('file_is_not_exist')]);
        }
    }

    /**
     * 时间 2022-06-22
     * @title 文件排序
     * @desc 文件排序
     * @author theworld
     * @version v1
     * @url /admin/v1/file/order
     * @method  PUT
     * @param int addon_idcsmart_file_folder_id - 文件夹ID required
     * @param array id - 文件ID required
     */
    public function idcsmartFileOrder()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartFileModel = new IdcsmartFileModel();

        // 移动文件
        $result = $IdcsmartFileModel->idcsmartFileOrder($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 获取文件夹
     * @desc 获取文件夹
     * @author theworld
     * @version v1
     * @url /admin/v1/file/folder
     * @method  GET
     * @return array list - 文件夹
     * @return int list[].id - 文件夹ID
     * @return string list[].name - 名称
     * @return int list[].default - 默认文件夹0否1是
     * @return string list[].admin - 修改人 
     * @return int list[].update_time - 修改时间
     * @return int list[].file_num - 文件数量 
     * @return int count - 全部文件数量 
     */
    public function idcsmartFileFolderList()
    {  
        // 实例化模型类
        $IdcsmartFileFolderModel = new IdcsmartFileFolderModel();

        // 获取文件夹
        $data = $IdcsmartFileFolderModel->idcsmartFileFolderList();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 添加文件夹
     * @desc 添加文件夹
     * @author theworld
     * @version v1
     * @url /admin/v1/file/folder
     * @method  POST
     * @param string name - 名称 required
     */
    public function createIdcsmartFileFolder()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create_folder')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartFileFolderModel = new IdcsmartFileFolderModel();

        // 创建文件夹
        $result = $IdcsmartFileFolderModel->createIdcsmartFileFolder($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 修改文件夹
     * @desc 修改文件夹
     * @author theworld
     * @version v1
     * @url /admin/v1/file/folder/:id
     * @method  PUT
     * @param int id - 文件夹ID required
     * @param string name - 名称 required
     */
    public function updateIdcsmartFileFolder()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_folder')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartFileFolderModel = new IdcsmartFileFolderModel();

        // 修改文件夹
        $result = $IdcsmartFileFolderModel->updateIdcsmartFileFolder($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除文件夹
     * @desc 删除文件夹
     * @author theworld
     * @version v1
     * @url /admin/v1/file/folder/:id
     * @method  DELETE
     * @param int id - 文件夹ID required
     */
    public function deleteIdcsmartFileFolder()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartFileFolderModel = new IdcsmartFileFolderModel();

        // 删除文件夹
        $result = $IdcsmartFileFolderModel->deleteIdcsmartFileFolder($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 设置默认文件夹
     * @desc 设置默认文件夹
     * @author theworld
     * @version v1
     * @url /admin/v1/file/folder/:id/default
     * @method  PUT
     * @param int id - 文件夹ID required
     */
    public function setDefaultFileFolder()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartFileFolderModel = new IdcsmartFileFolderModel();

        // 设置默认文件夹
        $result = $IdcsmartFileFolderModel->setDefaultFileFolder($param['id']);

        return json($result);
    }
}