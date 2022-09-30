<?php
namespace app\event\controller;

use think\facade\App;

/*
 * 实名认证接口路由映射控制器
 * @author wyh
 * @time 2022-05-26
 *
 * */
class CertificationController extends BaseController
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

        $pluginControllerClass = "certification\\{$_plugin}\\controller\\{$_controller}Controller";
        $vars = [];
        return App::invokeMethod([$pluginControllerClass, $_action, $vars]);
    }
}