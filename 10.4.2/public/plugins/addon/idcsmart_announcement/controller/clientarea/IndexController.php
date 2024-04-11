<?php
namespace addon\idcsmart_announcement\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_announcement\model\IdcsmartAnnouncementModel;
use addon\idcsmart_announcement\model\IdcsmartAnnouncementTypeModel;

/**
 * @title 公告中心
 * @desc 公告中心
 * @use addon\idcsmart_announcement\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    /**
     * 时间 2022-06-21
     * @title 获取公告分类
     * @desc 获取公告分类
     * @author theworld
     * @version v1
     * @url /console/v1/announcement/type
     * @method  GET
     * @return array list - 公告分类
     * @return int list[].id - 公告分类ID
     * @return string list[].name - 名称
     * @return int list[].announcement_num - 公告数量 
     * @return int count - 全部公告数量 
     */
    public function idcsmartAnnouncementTypeList()
    {
        // 实例化模型类
        $IdcsmartAnnouncementTypeModel = new IdcsmartAnnouncementTypeModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementTypeModel->idcsmartAnnouncementTypeList('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页公告列表
     * @desc 会员中心首页公告列表
     * @author theworld
     * @version v1
     * @url /console/v1/announcement/index
     * @method  GET
     * @param string keywords - 关键字,搜索范围:标题
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 公告
     * @return int list[].id - 公告ID
     * @return string list[].title - 标题
     * @return string list[].img - 公告缩略图
     * @return string list[].type - 类型 
     * @return int list[].create_time - 创建时间 
     */
    public function indexIdcsmartAnnouncementList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        $param['limit'] = 5;

        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementModel->idcsmartAnnouncementList($param, 'index');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 公告列表
     * @desc 公告列表
     * @author theworld
     * @version v1
     * @url /console/v1/announcement
     * @method  GET
     * @param int addon_idcsmart_announcement_type_id - 分类ID 
     * @param string keywords - 关键字,搜索范围:标题
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 公告
     * @return int list[].id - 公告ID
     * @return string list[].title - 标题
     * @return string list[].img - 公告缩略图
     * @return int list[].create_time - 创建时间 
     */
    public function idcsmartAnnouncementList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告列表
        $data = $IdcsmartAnnouncementModel->idcsmartAnnouncementList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 公告详情
     * @desc 公告详情
     * @author theworld
     * @version v1
     * @url /console/v1/announcement/:id
     * @method  GET
     * @param int id - 公告ID required
     * @return object announcement - 公告
     * @return int announcement.id - 公告ID
     * @return int announcement.addon_idcsmart_announcement_type_id - 分类ID
     * @return string announcement.type - 分类名
     * @return string announcement.title - 标题 
     * @return string announcement.content - 内容 
     * @return string announcement.keywords - 关键字 
     * @return string announcement.attachment - 附件
     * @return int announcement.create_time - 创建时间 
     * @return int announcement.update_time - 更新时间 
     * @return object announcement.prev - 上一条公告
     * @return string announcement.prev.id - 公告ID
     * @return string announcement.prev.title - 标题
     * @return object announcement.next - 下一条公告
     * @return string announcement.next.id - 公告ID
     * @return string announcement.next.title - 标题
     */
    public function idcsmartAnnouncementDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartAnnouncementModel = new IdcsmartAnnouncementModel();

        // 获取公告
        $announcement = $IdcsmartAnnouncementModel->idcsmartAnnouncementDetail($param['id'], 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'announcement' => $announcement
            ]
        ];
        return json($result);
    }
}