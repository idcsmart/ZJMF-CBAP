<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addnews;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addnews
 * @author theworld
 * @time 2022-06-20
 */
use think\facade\Route;

# 不需要登录
Route::get('country1', 'home/common/countryList')
    ->middleware(\app\http\middleware\Check::class); // 国家列表
# 前台需要登录授权,使用\app\http\middleware\CheckHome中间件
Route::get('country2', 'home/common/countryList')
    ->middleware(\app\http\middleware\CheckHome::class); // 国家列表
# 后台需要登录授权,使用\app\http\middleware\CheckAdmin中间件
Route::get('country3', 'home/common/countryList')
    ->middleware(\app\http\middleware\CheckAdmin::class); // 国家列表
# 允许跨域
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
Route::get('country4', 'home/common/countryList')
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ]);


# 前台
Route::group('console/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # SSH密钥
    Route::get('ssh_key', "\\addon\\idcsmart_ssh_key\\controller\\clientarea\\IndexController@list")
        ->append(['_plugin'=>'idcsmart_ssh_key','_controller'=>'index','_action'=>'list']);
    Route::post('ssh_key', "\\addon\\idcsmart_ssh_key\\controller\\clientarea\\IndexController@create")
        ->append(['_plugin'=>'idcsmart_ssh_key','_controller'=>'index','_action'=>'create']);
    Route::put('ssh_key/:id', "\\addon\\idcsmart_ssh_key\\controller\\clientarea\\IndexController@update")
        ->append(['_plugin'=>'idcsmart_ssh_key','_controller'=>'index','_action'=>'update']);
    Route::delete('ssh_key/:id', "\\addon\\idcsmart_ssh_key\\controller\\clientarea\\IndexController@delete")
        ->append(['_plugin'=>'idcsmart_ssh_key','_controller'=>'index','_action'=>'delete']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
# 后台
Route::group('admin/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # SSH密钥
    Route::get('ssh_key', "\\addon\\idcsmart_ssh_key\\controller\\AdminIndexController@list")
        ->append(['_plugin'=>'idcsmart_ssh_key','_controller'=>'admin_index','_action'=>'list']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
