<?php
namespace addon\idcsmart_news\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_news\model\IdcsmartNewsModel;
use addon\idcsmart_news\model\IdcsmartNewsTypeModel;
use addon\idcsmart_news\validate\IdcsmartNewsValidate;

/**
 * @title 新闻中心(后台)
 * @desc 新闻中心(后台)
 * @use addon\idcsmart_news\controller\AdminIndexController
 */
class AdminIndexController extends PluginAdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartNewsValidate();
    }

    /**
     * 时间 2022-06-21
     * @title 新闻列表
     * @desc 新闻列表
     * @author theworld
     * @version v1
     * @url /admin/v1/news
     * @method  GET
     * @param int addon_idcsmart_news_type_id - 分类ID 
     * @param string keywords - 关键字,搜索范围:标题
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 新闻
     * @return int list[].id - 新闻ID
     * @return string list[].title - 标题
     * @return string list[].type - 类型 
     * @return string list[].admin - 提交人 
     * @return int list[].create_time - 创建时间 
     * @return int list[].hidden - 0显示1隐藏 
     * @return int count - 新闻总数
     */
    public function idcsmartNewsList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻列表
        $data = $IdcsmartNewsModel->idcsmartNewsList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 新闻详情
     * @desc 新闻详情
     * @author theworld
     * @version v1
     * @url /admin/v1/news/:id
     * @method  GET
     * @param int id - 新闻ID required
     * @return object news - 新闻
     * @return int news.id - 新闻ID
     * @return int news.addon_idcsmart_news_type_id - 分类ID
     * @return string news.type - 分类名
     * @return string news.title - 标题 
     * @return string news.content - 内容 
     * @return string news.keywords - 关键字 
     * @return array news.attachment - 附件
     * @return int news.hidden - 0:显示1:隐藏
     * @return int news.cron_release - 是否定时发布(0=否,1=是)
     * @return int news.cron_release_time - 定时发布时间
     */
    public function idcsmartNewsDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻
        $news = $IdcsmartNewsModel->idcsmartNewsDetail($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'news' => $news
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 添加新闻
     * @desc 添加新闻
     * @author theworld
     * @version v1
     * @url /admin/v1/news
     * @method  POST
     * @param string title - 标题 required
	 * @param int addon_idcsmart_news_type_id - 分类ID required
     * @param string keywords - 关键字 
     * @param string img - 新闻缩略图
     * @param array attachment - 附件,上传附件需调用后台公共接口文件上传获取新的save_name传入
     * @param string content - 内容 required
     * @param int hidden - 0显示1隐藏 required
     * @param int cron_release - 是否定时发布(0=否,1=是) required
     * @param int cron_release_time - 定时发布时间 requireIf,cron_release=1
     */
    public function createIdcsmartNews()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 创建新闻
        $result = $IdcsmartNewsModel->createIdcsmartNews($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 修改新闻
     * @desc 修改新闻
     * @author theworld
     * @version v1
     * @url /admin/v1/news/:id
     * @method  PUT
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
     */
    public function updateIdcsmartNews()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 修改新闻
        $result = $IdcsmartNewsModel->updateIdcsmartNews($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除新闻
     * @desc 删除新闻
     * @author theworld
     * @version v1
     * @url /admin/v1/news/:id
     * @method  DELETE
     * @param int id - 新闻ID required
     */
    public function deleteIdcsmartNews()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 删除新闻
        $result = $IdcsmartNewsModel->deleteIdcsmartNews($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 隐藏/显示新闻
     * @desc 隐藏/显示新闻
     * @author theworld
     * @version v1
     * @url /admin/v1/news/:id/hidden
     * @method  PUT
     * @param int id - 新闻ID required
     * @param int hidden - 0显示1隐藏 required
     */
    public function hiddenIdcsmartNews()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 隐藏新闻
        $result = $IdcsmartNewsModel->hiddenIdcsmartNews($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 获取新闻分类
     * @desc 获取新闻分类
     * @author theworld
     * @version v1
     * @url /admin/v1/news/type
     * @method  GET
     * @return array list - 新闻分类
     * @return int list[].id - 新闻分类ID
     * @return string list[].name - 名称
     * @return string list[].admin - 修改人 
     * @return int list[].update_time - 修改时间 
     * @return int list[].news_num - 新闻数量 
     */
    public function idcsmartNewsTypeList()
    {  
        // 实例化模型类
        $IdcsmartNewsTypeModel = new IdcsmartNewsTypeModel();

        // 获取新闻分类
        $data = $IdcsmartNewsTypeModel->idcsmartNewsTypeList();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 添加新闻分类
     * @desc 添加新闻分类
     * @author theworld
     * @version v1
     * @url /admin/v1/news/type
     * @method  POST
     * @param array list - 分类数组 required
     * @param string list[].name - 名称 required
     */
    public function createIdcsmartNewsType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create_type')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartNewsTypeModel = new IdcsmartNewsTypeModel();

        // 创建新闻分类
        $result = $IdcsmartNewsTypeModel->createIdcsmartNewsType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 修改新闻分类
     * @desc 修改新闻分类
     * @author theworld
     * @version v1
     * @url /admin/v1/news/type/:id
     * @method  PUT
     * @param int id - 新闻分类ID required
     * @param string name - 名称 required
     */
    public function updateIdcsmartNewsType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_type')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartNewsTypeModel = new IdcsmartNewsTypeModel();

        // 修改新闻分类
        $result = $IdcsmartNewsTypeModel->updateIdcsmartNewsType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除新闻分类
     * @desc 删除新闻分类
     * @author theworld
     * @version v1
     * @url /admin/v1/news/type/:id
     * @method  DELETE
     * @param int id - 新闻分类ID required
     */
    public function deleteIdcsmartNewsType()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartNewsTypeModel = new IdcsmartNewsTypeModel();

        // 删除新闻分类
        $result = $IdcsmartNewsTypeModel->deleteIdcsmartNewsType($param['id']);

        return json($result);
    }
}