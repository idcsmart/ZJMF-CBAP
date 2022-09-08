<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp
 * @author theworld
 * @time 2022-07-22
 */
use think\facade\Route;

# 前台
Route::group('console/v1',function (){
    # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 提现插件
    Route::get('withdraw/rule', "\\addon\\idcsmart_withdraw\\controller\\clientarea\\IndexController@idcsmartWithdrawRuleDetail")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'index','_action'=>'idcsmart_withdraw_rule_detail']);
    Route::post('withdraw', "\\addon\\idcsmart_withdraw\\controller\\clientarea\\IndexController@idcsmartWithdraw")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'index','_action'=>'idcsmart_withdraw']);
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
    # 提现插件
    Route::get('withdraw', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawList")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_list']);
    Route::put('withdraw/:id/audit', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawAudit")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_audit']);
    Route::get('withdraw/rule', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawRuleList")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_rule_list']);
    Route::get('withdraw/rule/:id', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawRuleDetail")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_rule_detail']);
    Route::post('withdraw/rule', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@createIdcsmartWithdrawRule")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'create_idcsmart_withdraw_rule']);
    Route::put('withdraw/rule/:id', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@updateIdcsmartWithdrawRule")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'update_idcsmart_withdraw_rule']);
    Route::delete('withdraw/rule/:id', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@deleteIdcsmartWithdrawRule")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'delete_idcsmart_withdraw_rule']);
    Route::put('withdraw/rule/:id/status', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawRuleStatus")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_rule_status']);
    Route::get('withdraw/source', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawSource")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_source']);
    Route::put('withdraw/source', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawSourceSave")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_source_save']);
    Route::get('withdraw/client/:id', "\\addon\\idcsmart_withdraw\\controller\\AdminIndexController@idcsmartWithdrawClient")
        ->append(['_plugin'=>'idcsmart_withdraw','_controller'=>'admin_index','_action'=>'idcsmart_withdraw_client']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);
