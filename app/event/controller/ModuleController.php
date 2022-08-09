<?php
namespace app\event\controller;

use think\facade\App;

/*
 * 插件路由映射控制器(后台)
 * @author wyh
 * @time 2022-05-26
 *
 * */
class ModuleController extends BaseController
{
    public function index($module, $controller, $method)
    {
        $controller = parse_name($controller, 1);

        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $controller)) {
            abort(404, 'controller not exists:' . $controller);
        }

        if (!preg_match('/^[A-Za-z](\w|\.)*$/', $module)) {
            abort(404, 'module not exists:' . $module);
        }

        $pluginControllerClass = "server\\{$module}\\controller\\admin\\{$controller}Controller";
        $vars = [];
        $method = parse_name($method,1);
        return App::invokeMethod([$pluginControllerClass, $method, $vars]);
    }
}