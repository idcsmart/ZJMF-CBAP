<?php
namespace app\admin\controller;

use app\common\model\PhysicalServerBannerModel;
use app\admin\validate\PhysicalServerBannerValidate;

/**
 * @title 模板控制器-物理服务器轮播图
 * @desc 模板控制器-物理服务器轮播图
 * @use app\admin\controller\PhysicalServerBannerController
 */
class PhysicalServerBannerController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PhysicalServerBannerValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 物理服务器轮播图列表
     * @desc 物理服务器轮播图列表
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_banner
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
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();

        // 物理服务器轮播图列表
        $data = $PhysicalServerBannerModel->bannerList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 添加物理服务器轮播图
     * @desc 添加物理服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_banner
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
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();
        
        // 添加物理服务器轮播图
        $result = $PhysicalServerBannerModel->createBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 修改物理服务器轮播图
     * @desc 修改物理服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_banner/:id
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
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();
        
        // 修改物理服务器轮播图
        $result = $PhysicalServerBannerModel->updateBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除物理服务器轮播图
     * @desc 删除物理服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_banner/:id
     * @method  DELETE
     * @param int id - 轮播图ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();
        
        // 删除物理服务器轮播图
        $result = $PhysicalServerBannerModel->deleteBanner($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 展示物理服务器轮播图
     * @desc 展示物理服务器轮播图
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_banner/:id/show
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
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();
        
        // 展示物理服务器轮播图
        $result = $PhysicalServerBannerModel->showBanner($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 物理服务器轮播图排序
     * @desc 物理服务器轮播图排序
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_banner/order
     * @method  PUT
     * @param array id - 轮播图ID required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();
        
        // 物理服务器轮播图排序
        $result = $PhysicalServerBannerModel->orderBanner($param);

        return json($result);
    }
}