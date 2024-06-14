<?php
namespace app\admin\controller;

use app\common\model\ServerHostingAreaModel;
use app\admin\validate\ServerHostingAreaValidate;

/**
 * @title 模板控制器-服务器托管区域
 * @desc 模板控制器-服务器托管区域
 * @use app\admin\controller\ServerHostingAreaController
 */
class ServerHostingAreaController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ServerHostingAreaValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 服务器托管区域列表
     * @desc 服务器托管区域列表
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_area
     * @method  GET
     * @return array list -  区域
     * @return int list[].id - 区域ID
     * @return string list[].first_area - 一级区域
     */
    public function list()
    {
        // 实例化模型类
        $ServerHostingAreaModel = new ServerHostingAreaModel();

        // 服务器托管区域列表
        $data = $ServerHostingAreaModel->areaList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 添加服务器托管区域
     * @desc 添加服务器托管区域
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_area
     * @method  POST
     * @param string first_area - 一级区域 required
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
        $ServerHostingAreaModel = new ServerHostingAreaModel();
        
        // 添加服务器托管区域
        $result = $ServerHostingAreaModel->createArea($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 修改服务器托管区域
     * @desc 修改服务器托管区域
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_area/:id
     * @method  PUT
     * @param int id - 区域ID required
     * @param string first_area - 一级区域 required
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
        $ServerHostingAreaModel = new ServerHostingAreaModel();
        
        // 修改服务器托管区域
        $result = $ServerHostingAreaModel->updateArea($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除服务器托管区域
     * @desc 删除服务器托管区域
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_area/:id
     * @method  DELETE
     * @param int id - 区域ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ServerHostingAreaModel = new ServerHostingAreaModel();
        
        // 删除服务器托管区域
        $result = $ServerHostingAreaModel->deleteArea($param['id']);

        return json($result);
    }
}