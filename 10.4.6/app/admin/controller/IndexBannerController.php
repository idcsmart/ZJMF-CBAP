<?php
namespace app\admin\controller;

use app\common\model\IndexBannerModel;
use app\admin\validate\IndexBannerValidate;

/**
 * @title 模板控制器-首页轮播图
 * @desc 模板控制器-首页轮播图
 * @use app\admin\controller\IndexBannerController
 */
class IndexBannerController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IndexBannerValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 首页轮播图列表
     * @desc 首页轮播图列表
     * @author theworld
     * @version v1
     * @url /admin/v1/index_banner
     * @method  GET
     * @return array list - 轮播图
     * @return int list[].id - 轮播图ID
     * @return string list[].img - 图片
     * @return string list[].url - 跳转链接
     * @return int list[].start_time - 展示开始时间
     * @return int list[].end_time - 展示结束时间
     * @return int list[].show - 是否展示0否1是
     * @return string list[].notes - 备注
     */
    public function list()
    {
        // 实例化模型类
        $IndexBannerModel = new IndexBannerModel();

        // 首页轮播图列表
        $data = $IndexBannerModel->bannerList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 添加首页轮播图
     * @desc 添加首页轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/index_banner
     * @method  POST
     * @param string img - 图片 required
     * @param string url - 跳转链接 required
     * @param int start_time - 展示开始时间 required
     * @param int end_time - 展示结束时间 required
     * @param int show - 是否展示0否1是 required
     * @param string notes - 备注
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $IndexBannerModel = new IndexBannerModel();
        
        // 添加首页轮播图
        $result = $IndexBannerModel->createBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 修改首页轮播图
     * @desc 修改首页轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/index_banner/:id
     * @method  PUT
     * @param int id - 轮播图ID required
     * @param string img - 图片 required
     * @param string url - 跳转链接 required
     * @param int start_time - 展示开始时间 required
     * @param int end_time - 展示结束时间 required
     * @param int show - 是否展示0否1是 required
     * @param string notes - 备注
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $IndexBannerModel = new IndexBannerModel();
        
        // 修改首页轮播图
        $result = $IndexBannerModel->updateBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除首页轮播图
     * @desc 删除首页轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/index_banner/:id
     * @method  DELETE
     * @param int id - 轮播图ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $IndexBannerModel = new IndexBannerModel();
        
        // 删除首页轮播图
        $result = $IndexBannerModel->deleteBanner($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 展示首页轮播图
     * @desc 展示首页轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/index_banner/:id/show
     * @method  PUT
     * @param int id - 轮播图ID required
     * @param int show - 是否展示0否1是 required
     */
    public function show()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('show')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $IndexBannerModel = new IndexBannerModel();
        
        // 展示首页轮播图
        $result = $IndexBannerModel->showBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 首页轮播图排序
     * @desc 首页轮播图排序
     * @author theworld
     * @version v1
     * @url /admin/v1/index_banner/order
     * @method  PUT
     * @param array id - 轮播图ID required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $IndexBannerModel = new IndexBannerModel();
        
        // 首页轮播图排序
        $result = $IndexBannerModel->orderBanner($param);

        return json($result);
    }
}