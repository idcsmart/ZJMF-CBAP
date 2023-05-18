<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addannouncement;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addannouncement
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
    # 公告中心
    Route::get('announcement/index', "\\addon\\idcsmart_announcement\\controller\\clientarea\\IndexController@indexIdcsmartAnnouncementList")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'index','_action'=>'index_idcsmart_announcement_list']);
    Route::get('announcement/type', "\\addon\\idcsmart_announcement\\controller\\clientarea\\IndexController@idcsmartAnnouncementTypeList")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'index','_action'=>'idcsmart_announcement_type']);
    Route::get('announcement', "\\addon\\idcsmart_announcement\\controller\\clientarea\\IndexController@idcsmartAnnouncementList")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'index','_action'=>'idcsmart_announcement_list']);
    Route::get('announcement/:id', "\\addon\\idcsmart_announcement\\controller\\clientarea\\IndexController@idcsmartAnnouncementDetail")
    ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'index','_action'=>'idcsmart_announcement_detail']);
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
    # 公告中心
    Route::get('announcement', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@idcsmartAnnouncementList")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'idcsmart_announcement_list']);
    Route::get('announcement/:id', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@idcsmartAnnouncementDetail")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'idcsmart_announcement_detail']);
    Route::post('announcement', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@createIdcsmartAnnouncement")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'create_idcsmart_announcement']);
    Route::put('announcement/:id', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@updateIdcsmartAnnouncement")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'update_idcsmart_announcement']);
    Route::delete('announcement/:id', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@deleteIdcsmartAnnouncement")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'delete_idcsmart_announcement']);
    Route::put('announcement/:id/hidden', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@hiddenIdcsmartAnnouncement")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'hidden_idcsmart_announcement']);
    Route::get('announcement/type', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@idcsmartAnnouncementTypeList")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'idcsmart_announcement_type_list']);
    Route::post('announcement/type', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@createIdcsmartAnnouncementType")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'create_idcsmart_announcement_type']);
    Route::put('announcement/type/:id', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@updateIdcsmartAnnouncementType")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'update_idcsmart_announcement_type']);
    Route::delete('announcement/type/:id', "\\addon\\idcsmart_announcement\\controller\\AdminIndexController@deleteIdcsmartAnnouncementType")
        ->append(['_plugin'=>'idcsmart_announcement','_controller'=>'admin_index','_action'=>'delete_idcsmart_announcement_type']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
