<?php
namespace app\event\controller;

use think\facade\App;
use think\facade\Db;

/*
 * 插件路由映射控制器(前台)
 * @author wyh
 * @time 2022-05-26
 *
 * */
class AddonHomeController extends BaseController
{
    public function index($_plugin, $_controller, $_action)
    {
        $_controller = parse_name($_controller, 1);

        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $_controller)) {
            abort(404, 'controller not exists:' . $_controller);
        }

        if (is_numeric($_plugin)){
            $_plugin = intval($_plugin);
            $_plugin = Db::name('plugin')->where('id',$_plugin)->value('name');
            $_plugin = parse_name($_plugin,0);
        }

        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $_plugin)) {
            abort(404, 'plugin not exists:' . $_plugin);
        }

        $pluginControllerClass = "addons\\{$_plugin}\\controller\\clientarea\\{$_controller}Controller";
        $vars = [];
        $_action = parse_name($_action,1);
        return App::invokeMethod([$pluginControllerClass, $_action, $vars]);
    }
}