<?php
namespace gateway\user_custom\controller; //Demo插件英文名，改成你的插件英文就行了
use cmf\controller\PluginBaseController;
//use plugins\Demo\Model\PluginDemoModel;
use think\Db;

class IndexController extends PluginBaseController
{

    function index($id)
    {

        echo '插件控制器:IndexController';exit;
//        $users = Db::name("user")->limit(0, 5)->select();
//        $demos=PluginDemoModel::all();

        // print_r($demos);

//        $this->assign("users", $users);
//
//        return $this->fetch("/index");
    }

}