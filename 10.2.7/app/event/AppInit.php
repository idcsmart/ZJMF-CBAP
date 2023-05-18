<?php
namespace app\event;

use think\facade\Route;
use think\facade\Event;
use think\facade\Db;

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

        # 获取已安装且启用的插件路由
        $fun = function ($value){
            return parse_name($value,1);
        };
        $addons = array_map($fun,$addons);
        $addons = Db::name('plugin')->whereIn('name',$addons)
            ->where('status',1)
            ->column('name');
        foreach ($addons as $addon){
            $addon = parse_name($addon);
            if (is_file($addonDir . $addon . '/route.php')){
                include_once $addonDir . $addon . '/route.php';
            }
        }
        # 获取插件注册钩子
        $systemHookPlugins = $this->getCacheHook();
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

        # 加载RES模块钩子文件
        $serverDir = WEB_ROOT . 'plugins/reserver/';
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

    // 缓存插件钩子
    public function cacheHook()
    {
        $systemHookPlugins = Db::name('plugin_hook')->field('name,plugin')
            ->where('status',1)
            ->where('module','addon') # 仅插件
            ->select()->toArray();

        // 缓存文件为root用户且权限为644,导致无法写入,注释掉
        //cache('system_plugin_hooks',$systemHookPlugins);

        return $systemHookPlugins;
    }

    // 获取插件钩子
    public function getCacheHook()
    {
        return $this->cacheHook();
        if (empty(cache('system_plugin_hooks'))){
            $systemHookPlugins = $this->cacheHook();
        }else{
            $systemHookPlugins = cache('system_plugin_hooks');
        }

        return $systemHookPlugins;
    }

}
