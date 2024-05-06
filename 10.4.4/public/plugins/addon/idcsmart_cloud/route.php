<?php
/**
 * @author theworld
 * @time 2022-05-26
 */
use think\facade\Route;

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
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class)
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\RejectRepeatRequest::class);
