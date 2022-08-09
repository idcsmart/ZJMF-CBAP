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
    # 新闻中心
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
Route::group('admin/v1',function (){
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
