<?php
/**
 * @author theworld
 * @time 2022-06-22
 */
use think\facade\Route;

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
    Route::put('file/order', "\\addon\\idcsmart_file_download\\controller\\AdminIndexController@idcsmartFileOrder")
        ->append(['_plugin'=>'idcsmart_file_download','_controller'=>'admin_index','_action'=>'idcsmart_file_order']);

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
