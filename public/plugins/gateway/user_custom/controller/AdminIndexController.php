<?php

namespace plugins\user_custom\controller;

use cmf\controller\PluginBaseController;
use think\Db;

class AdminIndexController extends PluginBaseController
{

    protected function _initialize()
    {
        parent::_initialize();
        $adminId = cmf_get_current_admin_id();//获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign("admin_id", $adminId);
        }
    }

    /**
     * 演示插件
     * @adminMenu(
     *     'name'   => '演示插件',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '演示插件',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        echo '插件后台控制器';exit;
        $users = Db::name("user")->limit(0, 5)->select();
        //$demos = PluginDemoModel::all();

        // print_r($demos);

        $this->assign("users", $users);

        return $this->fetch('/admin_index');
    }

    /**
     * 演示插件设置
     * @adminMenu(
     *     'name'   => '演示插件设置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '演示插件设置',
     *     'param'  => ''
     * )
     */
    public function setting()
    {
        $users = Db::name("user")->limit(0, 5)->select();
        //$demos = PluginDemoModel::all();

        // print_r($demos);

        $this->assign("users", $users);

        return $this->fetch('/admin_index');
    }

}