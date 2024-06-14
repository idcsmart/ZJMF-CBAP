<?php
namespace app\admin\controller;

use app\common\model\WebNavModel;
use app\admin\validate\WebNavValidate;

/**
 * @title 模板控制器-导航
 * @desc 模板控制器-导航
 * @use app\admin\controller\WebNavController
 */
class WebNavController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new WebNavValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 导航列表
     * @desc 导航列表
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav
     * @method  GET
     * @param string theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list -  一级导航
     * @return int list[].id - 一级导航ID
     * @return string list[].name - 名称
     * @return string list[].file_address - 文件地址
     * @return int list[].show - 是否展示
     * @return array list[].children - 二级导航
     * @return int list[].children[].id - 二级导航ID
     * @return int list[].children[].parent_id - 父导航ID
     * @return string list[].children[].name - 名称
     * @return string list[].children[].description - 描述
     * @return string list[].children[].file_address - 文件地址
     * @return string list[].children[].icon - 图标
     * @return int list[].children[].show - 是否展示
     */
    public function list()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $WebNavModel = new WebNavModel();

        // 导航列表
        $data = $WebNavModel->navList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建导航
     * @desc 创建导航
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav
     * @method  POST
     * @param string theme - 主题标识,不传递时默认为当前系统设置的主题
     * @param int parent_id - 父导航ID
     * @param string name - 名称 required
     * @param string description - 描述
     * @param string file_address - 文件地址
     * @param string icon - 导航图标
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
        $WebNavModel = new WebNavModel();
        
        // 创建导航
        $result = $WebNavModel->createNav($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑导航
     * @desc 编辑导航
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id
     * @method  PUT
     * @param int id - 导航ID required
     * @param int parent_id - 父导航ID
     * @param string name - 名称 required
     * @param string description - 描述
     * @param string file_address - 文件地址
     * @param string icon - 导航图标
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
        $WebNavModel = new WebNavModel();
        
        // 编辑导航
        $result = $WebNavModel->updateNav($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除导航
     * @desc 删除导航
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id
     * @method  DELETE
     * @param int id - 导航ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 删除导航
        $result = $WebNavModel->deleteNav($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 导航显示
     * @desc 导航显示
     * @author theworld
     * @version v1
     * @url /admin/v1/web_nav/:id/show
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
        $WebNavModel = new WebNavModel();
        
        // 导航显示
        $result = $WebNavModel->navShow($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 一级导航排序
     * @desc 一级导航排序
     * @author theworld
     * @version v1
     * @url /admin/v1/first_web_nav/order
     * @method  PUT
     * @param string theme - 主题标识,不传递时默认为当前系统设置的主题
     * @param array id - 一级导航ID required
     */
    public function firstNavOrder()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 一级导航排序
        $result = $WebNavModel->firstNavOrder($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 二级导航排序
     * @desc 二级导航排序
     * @author theworld
     * @version v1
     * @url /admin/v1/second_web_nav/order
     * @method  PUT
     * @param int parent_id - 父导航ID required
     * @param array id - 二级导航ID required
     */
    public function secondNavOrder()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $WebNavModel = new WebNavModel();
        
        // 二级导航排序
        $result = $WebNavModel->secondNavOrder($param);

        return json($result);
    }
}