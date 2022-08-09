<?php
namespace addon\idcsmart_help\controller;

use app\event\controller\PluginBaseController;
use addon\idcsmart_help\model\IdcsmartHelpModel;
use addon\idcsmart_help\model\IdcsmartHelpTypeModel;
use addon\idcsmart_help\validate\IdcsmartHelpValidate;

/**
 * @title 帮助中心(后台)
 * @desc 帮助中心(后台)
 * @use addon\idcsmart_help\controller\AdminIndexController
 */
class AdminIndexController extends PluginBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartHelpValidate();
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档列表
     * @desc 帮助文档列表
     * @author theworld
     * @version v1
     * @url /admin/v1/help
     * @method  GET
     * @param int addon_idcsmart_help_type_id - 分类ID
     * @param string keywords - 关键字,搜索范围:标题
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 帮助文档
     * @return int list[].id - 帮助文档ID
     * @return string list[].title - 标题
     * @return string list[].type - 类型 
     * @return string list[].admin - 提交人 
     * @return int list[].create_time - 创建时间 
     * @return int list[].hidden - 0显示1隐藏 
     * @return int count - 帮助文档总数
     */
    public function idcsmartHelpList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 获取帮助文档列表
        $data = $IdcsmartHelpModel->idcsmartHelpList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档详情
     * @desc 帮助文档详情
     * @author theworld
     * @version v1
     * @url /admin/v1/help/:id
     * @method  GET
     * @param int id - 帮助文档ID required
     * @return object help - 帮助文档
     * @return int help.id - 帮助文档ID
     * @return int help.addon_idcsmart_help_type_id - 分类ID
     * @return string help.title - 标题 
     * @return string help.content - 内容 
     * @return string help.keywords - 关键字 
     * @return array help.attachment - 附件
     * @return int help.hidden - 0:显示1:隐藏
     * @return int help.create_time - 创建时间
     */
    public function idcsmartHelpDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 获取帮助文档
        $help = $IdcsmartHelpModel->idcsmartHelpDetail($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'help' => $help
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 添加帮助文档
     * @desc 添加帮助文档
     * @author theworld
     * @version v1
     * @url /admin/v1/help
     * @method  POST
     * @param string title - 标题 required
	 * @param int addon_idcsmart_help_type_id - 分类ID required
     * @param string keywords - 关键字 
     * @param array attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string content - 内容 required
     * @param int hidden - 0显示1隐藏 required
     */
    public function createIdcsmartHelp()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 创建帮助文档
        $result = $IdcsmartHelpModel->createIdcsmartHelp($param);

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 修改帮助文档
     * @desc 修改帮助文档
     * @author theworld
     * @version v1
     * @url /admin/v1/help/:id
     * @method  PUT
     * @param int id - 帮助文档ID required
     * @param string title - 标题 required
	 * @param int addon_idcsmart_help_type_id - 分类ID required
     * @param string keywords - 关键字 
     * @param array attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string content - 内容 required
     * @param int hidden - 0显示1隐藏 required
     */
    public function updateIdcsmartHelp()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 修改帮助文档
        $result = $IdcsmartHelpModel->updateIdcsmartHelp($param);

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 删除帮助文档
     * @desc 删除帮助文档
     * @author theworld
     * @version v1
     * @url /admin/v1/help/:id
     * @method  DELETE
     * @param int id - 帮助文档ID required
     */
    public function deleteIdcsmartHelp()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 删除帮助文档
        $result = $IdcsmartHelpModel->deleteIdcsmartHelp($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 隐藏/显示帮助文档
     * @desc 隐藏/显示帮助文档
     * @author theworld
     * @version v1
     * @url /admin/v1/help/:id/hidden
     * @method  PUT
     * @param int id - 帮助文档ID required
     * @param int hidden - 0显示1隐藏 required
     */
    public function hiddenIdcsmartHelp()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 隐藏帮助文档
        $result = $IdcsmartHelpModel->hiddenIdcsmartHelp($param);

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 获取帮助文档分类
     * @desc 获取帮助文档分类
     * @author theworld
     * @version v1
     * @url /admin/v1/help/type
     * @method  GET
     * @return array list - 帮助文档分类
     * @return int list[].id - 帮助文档分类ID
     * @return string list[].name - 名称
     * @return string list[].admin - 修改人 
     * @return int list[].update_time - 修改时间 
     */
    public function idcsmartHelpTypeList()
    {  
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 获取帮助文档分类
        $data = $IdcsmartHelpTypeModel->idcsmartHelpTypeList();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 添加帮助文档分类
     * @desc 添加帮助文档分类
     * @author theworld
     * @version v1
     * @url /admin/v1/help/type
     * @method  POST
     * @param string name - 名称 required
     */
    public function createIdcsmartHelpType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create_type')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 创建帮助文档分类
        $result = $IdcsmartHelpTypeModel->createIdcsmartHelpType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 修改帮助文档分类
     * @desc 修改帮助文档分类
     * @author theworld
     * @version v1
     * @url /admin/v1/help/type/:id
     * @method  PUT
     * @param int id - 帮助文档分类ID required
     * @param string name - 名称 required
     */
    public function updateIdcsmartHelpType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_type')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 修改帮助文档分类
        $result = $IdcsmartHelpTypeModel->updateIdcsmartHelpType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除帮助文档分类
     * @desc 删除帮助文档分类
     * @author theworld
     * @version v1
     * @url /admin/v1/help/type/:id
     * @method  DELETE
     * @param int id - 帮助文档分类ID required
     */
    public function deleteIdcsmartHelpType()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 删除帮助文档分类
        $result = $IdcsmartHelpTypeModel->deleteIdcsmartHelpType($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 获取帮助中心首页数据
     * @desc 获取帮助中心首页数据
     * @author theworld
     * @version v1
     * @url /admin/v1/help/index
     * @method  GET
     * @return array index - 帮助中心首页
     * @return int index[].id - 帮助文档分类ID
     * @return string index[].name - 帮助文档分类名称
     * @return int index[].index_hot_show - 首页是否根据热度显示文档0:否1:是
     * @return array index[].helps - 帮助文档 
     * @return int index[].helps[].id - 帮助文档ID 
     * @return string index[].helps[].title - 帮助文档标题
     */
    public function indexIdcsmartHelp()
    {
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 获取帮助中心首页数据
        $data = $IdcsmartHelpTypeModel->indexIdcsmartHelp();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 保存帮助中心首页数据
     * @desc 保存帮助中心首页数据
     * @author theworld
     * @version v1
     * @url /admin/v1/help/index
     * @method  PUT
     * @param array index - 帮助中心首页,需要包含6个元素 required
     * @param int index[].id - 帮助文档分类ID required
     * @param int index[].index_hot_show - 首页是否根据热度显示文档0:否1:是 required
     * @param array index[].helps - 帮助文档,最多三条
     * @param int index[].helps[].id - 帮助文档ID 
     */
    public function indexIdcsmartHelpSave()
    {
    	// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('index')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 修改帮助文档分类
        $result = $IdcsmartHelpTypeModel->indexIdcsmartHelpSave($param);

        return json($result);
    }

}