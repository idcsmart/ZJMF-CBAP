<?php
namespace app\admin\controller;

use app\common\model\SideFloatingWindowModel;
use app\admin\validate\SideFloatingWindowValidate;

/**
 * @title 模板控制器-侧边浮窗
 * @desc 模板控制器-侧边浮窗
 * @use app\admin\controller\SideFloatingWindowController
 */
class SideFloatingWindowController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new SideFloatingWindowValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 侧边浮窗列表
     * @desc 侧边浮窗列表
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window
     * @method  GET
     * @return array list -  侧边浮窗
     * @return int list[].id - 侧边浮窗ID
     * @return string list[].name - 名称
     * @return string list[].icon - 图标
     * @return string list[].content - 显示内容
     */
    public function list()
    {
        // 实例化模型类
        $SideFloatingWindowModel = new SideFloatingWindowModel();

        // 导航列表
        $data = $SideFloatingWindowModel->sideFloatingWindowList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建侧边浮窗
     * @desc 创建侧边浮窗
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window
     * @method  POST
     * @param string name - 名称 required
     * @param string icon - 图标 required
     * @param string content - 显示内容 required
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
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 创建侧边浮窗
        $result = $SideFloatingWindowModel->createSideFloatingWindow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑侧边浮窗
     * @desc 编辑侧边浮窗
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window/:id
     * @method  PUT
     * @param int id - 侧边浮窗ID required
     * @param string name - 名称 required
     * @param string icon - 图标 required
     * @param string content - 显示内容 required
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
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 编辑侧边浮窗
        $result = $SideFloatingWindowModel->updateSideFloatingWindow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除侧边浮窗
     * @desc 删除侧边浮窗
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window/:id
     * @method  DELETE
     * @param int id - 侧边浮窗ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 删除侧边浮窗
        $result = $SideFloatingWindowModel->deleteSideFloatingWindow($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 侧边浮窗排序
     * @desc 侧边浮窗排序
     * @author theworld
     * @version v1
     * @url /admin/v1/side_floating_window/order
     * @method  PUT
     * @param array id - 侧边浮窗ID required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SideFloatingWindowModel = new SideFloatingWindowModel();
        
        // 侧边浮窗排序
        $result = $SideFloatingWindowModel->sideFloatingWindowOrder($param);

        return json($result);
    }
}