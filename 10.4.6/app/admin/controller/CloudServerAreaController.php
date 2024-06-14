<?php
namespace app\admin\controller;

use app\common\model\CloudServerAreaModel;
use app\admin\validate\CloudServerAreaValidate;

/**
 * @title 模板控制器-云服务器区域
 * @desc 模板控制器-云服务器区域
 * @use app\admin\controller\CloudServerAreaController
 */
class CloudServerAreaController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CloudServerAreaValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器区域列表
     * @desc 云服务器区域列表
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_area
     * @method  GET
     * @return array list -  区域
     * @return int list[].id - 区域ID
     * @return string list[].first_area - 一级区域
     * @return string list[].second_area - 二级区域
     * @return array area - 区域选项
     * @return string area[].name - 一级区域名称
     * @return array area[].children - 二级区域
     * @return int list[].children[].id - 二级区域ID
     * @return string list[].children[].name - 二级区域名称
     */
    public function list()
    {
        // 实例化模型类
        $CloudServerAreaModel = new CloudServerAreaModel();

        // 云服务器区域列表
        $data = $CloudServerAreaModel->areaList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 添加云服务器区域
     * @desc 添加云服务器区域
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_area
     * @method  POST
     * @param string first_area - 一级区域 required
     * @param string second_area - 二级区域 required
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
        $CloudServerAreaModel = new CloudServerAreaModel();
        
        // 添加云服务器区域
        $result = $CloudServerAreaModel->createArea($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 修改云服务器区域
     * @desc 修改云服务器区域
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_area/:id
     * @method  PUT
     * @param int id - 区域ID required
     * @param string first_area - 一级区域 required
     * @param string second_area - 二级区域 required
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
        $CloudServerAreaModel = new CloudServerAreaModel();
        
        // 修改云服务器区域
        $result = $CloudServerAreaModel->updateArea($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除云服务器区域
     * @desc 删除云服务器区域
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_area/:id
     * @method  DELETE
     * @param int id - 区域ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CloudServerAreaModel = new CloudServerAreaModel();
        
        // 删除云服务器区域
        $result = $CloudServerAreaModel->deleteArea($param['id']);

        return json($result);
    }
}