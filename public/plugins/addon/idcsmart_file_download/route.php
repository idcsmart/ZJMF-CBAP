<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addnews;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addnews
 * @author theworld
 * @time 2022-06-22
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

Route::get('console/v1/file/:id/download', "\\addon\\idcsmart_file_download\\controller\\clientarea\\IndexController@idcsmartFileDownload")
    ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'index','_action'=>'idcsmart_file_download'])
    ->middleware(\app\http\middleware\Check::class);

# 前台
Route::group('console/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 文件下载
    Route::get('file/folder', "\\addon\\idcsmart_file_download\\controller\\clientarea\\IndexController@idcsmartFileFolderList")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'index','_action'=>'idcsmart_file_folder_list']);
    Route::get('file', "\\addon\\idcsmart_file_download\\controller\\clientarea\\IndexController@idcsmartFileList")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'index','_action'=>'idcsmart_file_list']);
    /*Route::get('file/:id/download', "\\addon\\idcsmart_file_download\\controller\\clientarea\\IndexController@idcsmartFileDownload")
    ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'index','_action'=>'idcsmart_file_download']);*/
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 文件下载
    Route::get('file', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@idcsmartFileList")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'idcsmart_file_list']);
    Route::get('file/:id', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@idcsmartFileDetail")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'idcsmart_file_detail']);
    Route::post('file', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@createIdcsmartFile")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'create_idcsmart_file']);
    Route::put('file/:id', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@updateIdcsmartFile")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'update_idcsmart_file']);
    Route::delete('file/:id', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@deleteIdcsmartFile")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'delete_idcsmart_file']);
    Route::put('file/:id/hidden', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@hiddenIdcsmartFile")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'hidden_idcsmart_file']);
    Route::put('file/:id/move', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@moveIdcsmartFile")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'move_idcsmart_file']);
    Route::get('file/:id/download', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@idcsmartFileDownload")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'idcsmart_file_download']);
    Route::get('file/folder', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@idcsmartFileFolderList")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'idcsmart_file_folder_list']);
    Route::post('file/folder', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@createIdcsmartFileFolder")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'create_idcsmart_file_folder']);
    Route::put('file/folder/:id', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@updateIdcsmartFileFolder")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'update_idcsmart_file_folder']);
    Route::delete('file/folder/:id', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@deleteIdcsmartFileFolder")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'delete_idcsmart_file_folder']);
    Route::put('file/folder/:id/default', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@setDefaultFileFolder")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'set_default_file_folder']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
