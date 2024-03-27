<?php
/**
 * @author theworld
 * @time 2022-06-20
 */
use think\facade\Route;

# 前台
Route::group('console/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 新闻中心
    Route::get('news/index', "\\addon\\idcsmart_news\\controller\\clientarea\\IndexController@indexIdcsmartNewsList")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'index','_action'=>'index_idcsmart_news_list']);
    Route::get('news/type', "\\addon\\idcsmart_news\\controller\\clientarea\\IndexController@idcsmartNewsTypeList")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'index','_action'=>'idcsmart_news_type']);
    Route::get('news', "\\addon\\idcsmart_news\\controller\\clientarea\\IndexController@idcsmartNewsList")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'index','_action'=>'idcsmart_news_list']);
    Route::get('news/:id', "\\addon\\idcsmart_news\\controller\\clientarea\\IndexController@idcsmartNewsDetail")
    ->append(['_plugin'=>'idcsmart_news','_controller'=>'index','_action'=>'idcsmart_news_detail']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\Check::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 新闻中心
    Route::get('news', "\\addon\\idcsmart_news\\controller\\AdminIndexController@idcsmartNewsList")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'idcsmart_news_list']);
    Route::get('news/:id', "\\addon\\idcsmart_news\\controller\\AdminIndexController@idcsmartNewsDetail")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'idcsmart_news_detail']);
    Route::post('news', "\\addon\\idcsmart_news\\controller\\AdminIndexController@createIdcsmartNews")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'create_idcsmart_news']);
    Route::put('news/:id', "\\addon\\idcsmart_news\\controller\\AdminIndexController@updateIdcsmartNews")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'update_idcsmart_news']);
    Route::delete('news/:id', "\\addon\\idcsmart_news\\controller\\AdminIndexController@deleteIdcsmartNews")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'delete_idcsmart_news']);
    Route::put('news/:id/hidden', "\\addon\\idcsmart_news\\controller\\AdminIndexController@hiddenIdcsmartNews")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'hidden_idcsmart_news']);
    Route::get('news/type', "\\addon\\idcsmart_news\\controller\\AdminIndexController@idcsmartNewsTypeList")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'idcsmart_news_type_list']);
    Route::post('news/type', "\\addon\\idcsmart_news\\controller\\AdminIndexController@createIdcsmartNewsType")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'create_idcsmart_news_type']);
    Route::put('news/type/:id', "\\addon\\idcsmart_news\\controller\\AdminIndexController@updateIdcsmartNewsType")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'update_idcsmart_news_type']);
    Route::delete('news/type/:id', "\\addon\\idcsmart_news\\controller\\AdminIndexController@deleteIdcsmartNewsType")
        ->append(['_plugin'=>'idcsmart_news','_controller'=>'admin_index','_action'=>'delete_idcsmart_news_type']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
