<?php
namespace app\event\controller;

use think\facade\App;

/*
 * 模板控制器路由映射控制器
 * @author theworld
 * @time 2024-05-21
 *
 * */
class TemplateController extends BaseController
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

        $pluginControllerClass = "template\\{$_plugin}\\controller\\controller\\{$_controller}Controller";
        $vars = [];
        return App::invokeMethod([$pluginControllerClass, $_action, $vars]);
    }
}