<?php
namespace app\admin\controller;

use app\common\model\BottomBarNavModel;
use app\admin\validate\BottomBarNavValidate;

/**
 * @title 模板控制器-底部栏导航
 * @desc 模板控制器-底部栏导航
 * @use app\admin\controller\BottomBarNavController
 */
class BottomBarNavController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new BottomBarNavValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 底部栏导航列表
     * @desc 底部栏导航列表
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_nav
     * @method  GET
     * @param string theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list -  分组
     * @return int list[].id - 分组ID
     * @return string list[].name - 名称
     * @return array list[].children - 导航
     * @return int list[].children[].id - 导航ID
     * @return int list[].children[].group_id - 分组ID
     * @return string list[].children[].name - 名称
     * @return string list[].children[].url - 跳转地址
     * @return int list[].children[].show - 是否展示
     */
    public function list()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $BottomBarNavModel = new BottomBarNavModel();

        // 导航列表
        $data = $BottomBarNavModel->navList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建底部栏导航
     * @desc 创建底部栏导航
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_nav
     * @method  POST
     * @param int group_id - 分组ID required
     * @param string name - 名称 required
     * @param string url - 跳转地址 required
     * @param int show - 是否展示0否1是 required
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
        $BottomBarNavModel = new BottomBarNavModel();
        
        // 创建底部栏导航
        $result = $BottomBarNavModel->createNav($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑底部栏导航
     * @desc 编辑底部栏导航
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_nav/:id
     * @method  PUT
     * @param int id - 导航ID required
     * @param int group_id - 分组ID required
     * @param string name - 名称 required
     * @param string url - 跳转地址 required
     * @param int show - 是否展示0否1是 required
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
        $BottomBarNavModel = new BottomBarNavModel();
        
        // 编辑底部栏导航
        $result = $BottomBarNavModel->updateNav($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除底部栏导航
     * @desc 删除底部栏导航
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_nav/:id
     * @method  DELETE
     * @param int id - 导航ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $BottomBarNavModel = new BottomBarNavModel();
        
        // 删除底部栏导航
        $result = $BottomBarNavModel->deleteNav($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 底部栏导航显示
     * @desc 底部栏导航显示
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_nav/:id/show
     * @method  PUT
     * @param int id - 导航ID required
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
        $BottomBarNavModel = new BottomBarNavModel();
        
        // 底部栏导航显示
        $result = $BottomBarNavModel->navShow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 底部栏导航排序
     * @desc 底部栏导航排序
     * @author theworld
     * @version v1
     * @url /admin/v1/bottom_bar_nav/order
     * @method  PUT
     * @param int group_id - 分组ID required
     * @param array id - 导航ID required
     */
    public function order()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $BottomBarNavModel = new BottomBarNavModel();
        
        // 底部栏导航排序
        $result = $BottomBarNavModel->navOrder($param);

        return json($result);
    }
}