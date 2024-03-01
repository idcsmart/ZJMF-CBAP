<?php
/**
 * @author theworld
 * @time 2022-06-20
 */
use think\facade\Route;

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
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\RejectRepeatRequest::class);
# 后台
Route::group(DIR_ADMIN . '/v1',function (){
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
