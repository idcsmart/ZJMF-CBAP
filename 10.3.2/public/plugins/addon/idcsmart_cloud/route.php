<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp
 * @author wyh
 * @time 2022-05-26
 */
use think\facade\Route;

# 不需要登录
// Route::get('country1', 'home/common/countryList')
//     ->middleware(\app\http\middleware\Check::class); // 国家列表
// # 前台需要登录授权,使用\app\http\middleware\CheckHome中间件
// Route::get('country2', 'home/common/countryList')
//     ->middleware(\app\http\middleware\CheckHome::class); // 国家列表
// # 后台需要登录授权,使用\app\http\middleware\CheckAdmin中间件
// Route::get('country3', 'home/common/countryList')
//     ->middleware(\app\http\middleware\CheckAdmin::class); // 国家列表
// # 允许跨域
// $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
// Route::get('country4', 'home/common/countryList')
//     ->allowCrossDomain([
//         'Access-Control-Allow-Origin'        => $origin,
//         'Access-Control-Allow-Credentials'   => 'true',
//         'Access-Control-Max-Age'             => 600,
//     ]);


# 前台
Route::group('console/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 安全组
    Route::get('security_group', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@list")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'list']);
    Route::get('security_group/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@index")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'index']);
    Route::post('security_group', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@create")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'create']);
    Route::put('security_group/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@update")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'update']);
    Route::delete('security_group/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@delete")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'delete']);
    Route::get('security_group/:id/host', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@securityGroupHostList")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'security_group_host_list']);
    Route::post('security_group/:id/host/:host_id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@linkSecurityGroup")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'link_security_group']);
    Route::delete('security_group/:id/host/:host_id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupController@unlinkSecurityGroup")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group','_action'=>'unlink_security_group']);
    # 安全组规则
    Route::get('security_group/:id/rule', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupRuleController@list")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group_rule','_action'=>'list']);
    Route::get('security_group/rule/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupRuleController@index")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group_rule','_action'=>'index']);
    Route::post('security_group/:id/rule', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupRuleController@create")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group_rule','_action'=>'create']);
    Route::post('security_group/:id/rule/batch', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupRuleController@batchCreate")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group_rule','_action'=>'batch_create']);
    Route::put('security_group/rule/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupRuleController@update")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group_rule','_action'=>'update']);
    Route::delete('security_group/rule/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SecurityGroupRuleController@delete")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'security_group_rule','_action'=>'delete']);

    Route::get('vpc', "\\addon\\idcsmart_cloud\\controller\\clientarea\\VpcController@list")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'vpc','_action'=>'list']);
    Route::post('vpc', "\\addon\\idcsmart_cloud\\controller\\clientarea\\VpcController@create")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'vpc','_action'=>'create']);
    Route::put('vpc/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\VpcController@update")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'vpc','_action'=>'update']);
    Route::delete('vpc/:id', "\\addon\\idcsmart_cloud\\controller\\clientarea\\VpcController@delete")
    ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'vpc','_action'=>'delete']);

    Route::get('snapshot', "\\addon\\idcsmart_cloud\\controller\\clientarea\\SnapshotController@list")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'snapshot','_action'=>'list']);
    Route::get('backup', "\\addon\\idcsmart_cloud\\controller\\clientarea\\BackupController@list")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'backup','_action'=>'list']);
    Route::get('template', "\\addon\\idcsmart_cloud\\controller\\clientarea\\TemplateController@list")
        ->append(['_plugin'=>'idcsmart_cloud','_controller'=>'template','_action'=>'list']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
