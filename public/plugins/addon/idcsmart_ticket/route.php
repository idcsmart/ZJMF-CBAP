<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp
 * @author wyh
 * @time 2022-06-20
 */
use think\facade\Route;

# 前台
Route::group('console/v1',function (){
    # 工单列表
    Route::get('ticket', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@ticketList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_list']);# 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 工单统计
    Route::get('ticket/statistic', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@statistic")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'statistic']);
    # 工单类型
    Route::get('ticket/type', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@type")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'type']);
    # 工单详情
    Route::get('ticket/:id', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'index']);
    # 创建工单
    Route::post('ticket', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'create']);
    # 回复工单
    Route::post('ticket/:id/reply', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@reply")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'reply']);
    # 催单
    Route::put('ticket/:id/urge', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@urge")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'urge']);
    # 关闭工单
    Route::put('ticket/:id/close', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@close")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'close']);
    # 附件下载
    Route::post('ticket/download', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@download")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'download']);
})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckHome::class);

# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    # 工单列表
    Route::get('ticket', "\\addon\\idcsmart_ticket\\controller\\TicketController@ticketList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_list']);
    # 接受工单
    Route::put('ticket/:id/receive', "\\addon\\idcsmart_ticket\\controller\\TicketController@receive")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'receive']);
    # 已解决工单
    Route::put('ticket/:id/resolved', "\\addon\\idcsmart_ticket\\controller\\TicketController@resolved")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'resolved']);
    # 工单详情
    Route::get('ticket/:id', "\\addon\\idcsmart_ticket\\controller\\TicketController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'index']);
    # 回复工单
    Route::post('ticket/:id/reply', "\\addon\\idcsmart_ticket\\controller\\TicketController@reply")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'reply']);
    # 创建内部工单
    Route::post('ticket/internal', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'create']);
    # 内部工单列表
    Route::get('ticket/internal', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@ticketInternalList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'ticket_internal_list']);
    # 内部工单详情
    Route::get('ticket/internal/:id', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'index']);
    # 接收内部工单
    Route::put('ticket/internal/:id/receive', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@receive")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'receive']);
    # 已解决内部工单
    Route::put('ticket/internal/:id/resolved', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@resolved")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'resolved']);
    # 回复内部工单
    Route::post('ticket/internal/:id/reply', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@reply")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'reply']);
    # 转发内部工单
    Route::put('ticket/internal/:id/forward', "\\addon\\idcsmart_ticket\\controller\\TicketInternalController@forward")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_internal','_action'=>'forward']);
    # 工单类型列表
    Route::get('ticket/type', "\\addon\\idcsmart_ticket\\controller\\TicketTypeController@ticketTypeList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_type','_action'=>'ticket_type_list']);
    # 创建工单类型
    Route::post('ticket/type', "\\addon\\idcsmart_ticket\\controller\\TicketTypeController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_type','_action'=>'create']);
    # 编辑工单类型
    Route::put('ticket/type/:id', "\\addon\\idcsmart_ticket\\controller\\TicketTypeController@update")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_type','_action'=>'update']);
    # 删除工单类型
    Route::delete('ticket/type/:id', "\\addon\\idcsmart_ticket\\controller\\TicketTypeController@delete")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_type','_action'=>'delete']);
    # 工单类型详情
    Route::get('ticket/type/:id', "\\addon\\idcsmart_ticket\\controller\\TicketTypeController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_type','_action'=>'index']);
    # 附件下载
    Route::post('ticket/download', "\\addon\\idcsmart_ticket\\controller\\TicketController@download")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'download']);
})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckAdmin::class);