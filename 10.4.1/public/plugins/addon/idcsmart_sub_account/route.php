<?php
/**
 * @author theworld
 * @time 2022-08-09
 */
use think\facade\Route;

# 前台
Route::group('console/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 子账户
    Route::get('sub_account', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@list")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'list']);
    Route::get('sub_account/:id', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@index")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'index']);
    Route::post('sub_account', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@create")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'create']);
    Route::put('sub_account/:id', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@update")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'update']);
    Route::delete('sub_account/:id', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@delete")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'delete']);
    Route::put('sub_account/:id/status', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@status")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'status']);
    Route::get('sub_account/:id/auth', "\\addon\\idcsmart_sub_account\\controller\\clientarea\\IndexController@subAccountAuthList")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'index','_action'=>'sub_account_auth_list']);
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
    # 子账户
    Route::get('sub_account', "\\addon\\idcsmart_sub_account\\controller\\AdminIndexController@list")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'admin_index','_action'=>'list']);
    Route::get('sub_account/:id', "\\addon\\idcsmart_sub_account\\controller\\AdminIndexController@index")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'admin_index','_action'=>'index']);
    Route::put('sub_account/:id', "\\addon\\idcsmart_sub_account\\controller\\AdminIndexController@update")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'admin_index','_action'=>'update']);
    Route::get('sub_account/parent', "\\addon\\idcsmart_sub_account\\controller\\AdminIndexController@parentList")
        ->append(['_plugin'=>'idcsmart_sub_account','_controller'=>'admin_index','_action'=>'parent_list']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
