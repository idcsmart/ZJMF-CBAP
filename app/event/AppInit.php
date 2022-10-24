<?php
namespace app\event;

use think\facade\Db;
use think\facade\Route;
use think\facade\Event;

/*
 * AppInit事件类
 * @author wyh
 * @time 2022-05-26
 *
 * */
class  AppInit
{
    public function handle()
    {
        # 注册应用命名空间
        if (config('idcsmart.root_namespace')){
            \app\common\lib\Loader::addNamespace(config('idcsmart.root_namespace'));
            \app\common\lib\Loader::register(); # 实现自动加载
        }
        # 支付接口路由
        Route::any('gateway/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\GatewayController@index");

        # 验证码接口路由
        Route::any('captcha/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\CaptchaController@index");

        # 实名认证接口路由
        Route::any('certification/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\CertificationController@index");
            #->middleware(\app\http\middleware\CheckHome::class);

        # 插件后台路由(官方默认路由需要登录才能访问)
        Route::any(DIR_ADMIN.'/addon', "\\app\\event\\controller\\AddonController@index")
            ->middleware(\app\http\middleware\CheckAdmin::class); // 参数 ?_plugin=client_care&_controller=client_care&_action=index
        # 插件前台路由(官方默认路由需要登录才能访问)
        Route::any('console/addon', "\\app\\event\\controller\\AddonHomeController@index")
            ->middleware(\app\http\middleware\CheckHome::class); // 参数 ?_plugin=205&_controller=client_care&_action=index

        # 模块后台路由(官方默认路由需要登录才能访问)
        Route::any('console/module/[:module]/[:controller]/[:method]', "\\app\\event\\controller\\ModuleController@index")
            ->middleware(\app\http\middleware\CheckAdmin::class);
        # 模块前台路由(官方默认路由需要登录才能访问)
        Route::any(DIR_ADMIN.'/module/[:module]/[:controller]/[:method]', "\\app\\event\\controller\\ModuleHomeController@index")
            ->middleware(\app\http\middleware\CheckHome::class);

        # 允许插件自定义路由(不管是否与系统冲突)
        $addonDir = WEB_ROOT . 'plugins/addon/';
        $addons = array_map('basename', glob($addonDir . '*', GLOB_ONLYDIR));
        foreach ($addons as $addon){
            $parseName = parse_name($addon,1);
            # 说明:存在一定的安全性,判断是否安装且启用的插件
            $plugin = Db::name('plugin')->where('name',$parseName)
                ->where('status',1)
                ->find();
            if (!empty($plugin) && is_file($addonDir . $addon . '/route.php')){
                include_once $addonDir . $addon . '/route.php';
            }
        }

        # 获取系统允许钩子
        #$systemHook = get_system_hooks();
        # 获取插件注册钩子
        $systemHookPlugins = Db::name('plugin_hook')
            ->field('name,plugin')
            ->where('status',1)
            ->where('module','addon') # 仅插件
            #->whereIn('name',$systemHook)
            ->select()->toArray();
        if (!empty($systemHookPlugins)) {
            foreach ($systemHookPlugins as $hookPlugin) {
                $class = get_plugin_class($hookPlugin['plugin'],'addon');
                if (!class_exists($class)) { # 实例化插件失败忽略
                    continue;
                }
                # 监听(注册)插件钩子
                Event::listen($hookPlugin['name'],[$class,parse_name($hookPlugin['name'],1)]);
            }
        }

        # 加载模块钩子文件
        $serverDir = WEB_ROOT . 'plugins/server/';
        $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
        foreach ($servers as $server){
            if (is_file($serverDir . $server . '/hooks.php')){
                include_once  $serverDir . $server . '/hooks.php';
            }
            # 允许模块自定义路由(不管是否与系统冲突)
            if (is_file($serverDir . $server . '/route.php')){
                include_once  $serverDir . $server . '/route.php';
            }
        }
    }

}
