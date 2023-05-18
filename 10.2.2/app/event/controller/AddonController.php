<?php
namespace app\event\controller;

use think\facade\App;

/*
 * 插件路由映射控制器(后台)
 * @author wyh
 * @time 2022-05-26
 *
 * */
class AddonController extends BaseController
{
    public function index($_plugin, $_controller, $_action)
    {
        $_controller = parse_name($_controller, 1);

        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $_controller)) {
            abort(404, 'controller not exists:' . $_controller);
        }

        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $_plugin)) {
            abort(404, 'plugin not exists:' . $_plugin);
        }

        $pluginControllerClass = "addon\\{$_plugin}\\controller\\{$_controller}Controller";
        $vars = [];
        $_action = parse_name($_action,1);
        return App::invokeMethod([$pluginControllerClass, $_action, $vars]);
    }
}