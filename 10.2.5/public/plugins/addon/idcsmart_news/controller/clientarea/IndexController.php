<?php
namespace addon\idcsmart_news\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_news\model\IdcsmartNewsModel;
use addon\idcsmart_news\model\IdcsmartNewsTypeModel;

/**
 * @title 新闻中心
 * @desc 新闻中心
 * @use addon\idcsmart_news\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    /**
     * 时间 2022-06-21
     * @title 获取新闻分类
     * @desc 获取新闻分类
     * @author theworld
     * @version v1
     * @url /console/v1/news/type
     * @method  GET
     * @return array list - 新闻分类
     * @return int list[].id - 新闻分类ID
     * @return string list[].name - 名称
     * @return int list[].news_num - 新闻数量 
     * @return int count - 全部新闻数量 
     */
    public function idcsmartNewsTypeList()
    {
        // 实例化模型类
        $IdcsmartNewsTypeModel = new IdcsmartNewsTypeModel();

        // 获取新闻列表
        $data = $IdcsmartNewsTypeModel->idcsmartNewsTypeList('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页新闻列表
     * @desc 会员中心首页新闻列表
     * @author theworld
     * @version v1
     * @url /console/v1/news/index
     * @method  GET
     * @param string keywords - 关键字,搜索范围:标题
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 新闻
     * @return int list[].id - 新闻ID
     * @return string list[].title - 标题
     * @return string list[].img - 新闻缩略图
     * @return string list[].type - 类型 
     * @return int list[].create_time - 创建时间 
     */
    public function indexIdcsmartNewsList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        $param['limit'] = 5;

        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻列表
        $data = $IdcsmartNewsModel->idcsmartNewsList($param, 'index');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 新闻列表
     * @desc 新闻列表
     * @author theworld
     * @version v1
     * @url /console/v1/news
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
     * @return string list[].img - 新闻缩略图
     * @return int list[].create_time - 创建时间 
     */
    public function idcsmartNewsList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻列表
        $data = $IdcsmartNewsModel->idcsmartNewsList($param, 'home');

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
     * @url /console/v1/news/:id
     * @method  GET
     * @param int id - 新闻ID required
     * @return object news - 新闻
     * @return int news.id - 新闻ID
     * @return int news.addon_idcsmart_news_type_id - 分类ID
     * @return string news.type - 分类名
     * @return string news.title - 标题 
     * @return string news.content - 内容 
     * @return string news.keywords - 关键字 
     * @return string news.attachment - 附件
     * @return int news.create_time - 创建时间 
     * @return object news.prev - 上一条新闻
     * @return string news.prev.id - 新闻ID
     * @return string news.prev.title - 标题
     * @return object news.next - 下一条新闻
     * @return string news.next.id - 新闻ID
     * @return string news.next.title - 标题
     */
    public function idcsmartNewsDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartNewsModel = new IdcsmartNewsModel();

        // 获取新闻
        $news = $IdcsmartNewsModel->idcsmartNewsDetail($param['id'], 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'news' => $news
            ]
        ];
        return json($result);
    }
}