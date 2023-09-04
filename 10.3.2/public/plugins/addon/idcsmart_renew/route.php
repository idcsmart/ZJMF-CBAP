<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp
 * @author wyh
 * @time 2022-06-02
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
    Route::get('host/:id/renew', "\\addon\\idcsmart_renew\\controller\\clientarea\\IndexController@renewPage")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'index','_action'=>'renew_page']);
    Route::post('host/:id/renew', "\\addon\\idcsmart_renew\\controller\\clientarea\\IndexController@renew")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'index','_action'=>'renew']);
    Route::get('host/renew/batch', "\\addon\\idcsmart_renew\\controller\\clientarea\\IndexController@renewBatchPage")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'index','_action'=>'renew_batch_page']);
    Route::post('host/renew/batch', "\\addon\\idcsmart_renew\\controller\\clientarea\\IndexController@renewBatch")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'index','_action'=>'renew_batch']);
    Route::get('host/:id/renew/auto', "\\addon\\idcsmart_renew\\controller\\clientarea\\IndexController@renewAutoStatus")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'index','_action'=>'renew_auto_status']);
    Route::put('host/:id/renew/auto', "\\addon\\idcsmart_renew\\controller\\clientarea\\IndexController@updateRenewAutoStatus")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'index','_action'=>'update_renew_auto_status']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class);

# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    Route::get('host/:id/renew', "\\addon\\idcsmart_renew\\controller\\AdminIndexController@renewPage")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'admin_index','_action'=>'renew_page']);
    Route::post('host/:id/renew', "\\addon\\idcsmart_renew\\controller\\AdminIndexController@renew")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'admin_index','_action'=>'renew']);
    Route::get('host/renew/batch', "\\addon\\idcsmart_renew\\controller\\AdminIndexController@renewBatchPage")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'admin_index','_action'=>'renew_batch_page']);
    Route::post('host/renew/batch', "\\addon\\idcsmart_renew\\controller\\AdminIndexController@renewBatch")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'admin_index','_action'=>'renew_batch']);
    Route::get('host/:id/renew/auto', "\\addon\\idcsmart_renew\\controller\\AdminIndexController@renewAutoStatus")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'admin_index','_action'=>'renew_auto_status']);
    Route::put('host/:id/renew/auto', "\\addon\\idcsmart_renew\\controller\\AdminIndexController@updateRenewAutoStatus")
        ->append(['_plugin'=>'idcsmart_renew','_controller'=>'admin_index','_action'=>'update_renew_auto_status']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class);