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
    # 工单部门
    Route::get('ticket/department', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@department")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'department']);
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

    # 工单状态
    Route::get('ticket/status', "\\addon\\idcsmart_ticket\\controller\\clientarea\\TicketController@ticketStatusList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_status_list']);
})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckHome::class);

# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    # 设置
    Route::get('ticket/config', "\\addon\\idcsmart_ticket\\controller\\TicketController@getConfig")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'get_config']);
    # 设置
    Route::post('ticket/config', "\\addon\\idcsmart_ticket\\controller\\TicketController@setConfig")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'set_config']);
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
    # 转内部工单
    Route::post('ticket/convert', "\\addon\\idcsmart_ticket\\controller\\TicketController@convert")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'convert']);
    # 内部工单类型列表
    Route::get('ticket/internal/type', "\\addon\\idcsmart_ticket\\controller\\TicketController@ticketInternalType")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_internal_type']);
    # 创建工单
    Route::post('ticket', "\\addon\\idcsmart_ticket\\controller\\TicketController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'create']);
    # 转交工单
    Route::post('ticket/:id/forward', "\\addon\\idcsmart_ticket\\controller\\TicketController@forward")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'forward']);
    # 工单部门
    Route::get('ticket/department', "\\addon\\idcsmart_ticket\\controller\\TicketController@department")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'department']);
    Route::get('ticket/:id/log', "\\addon\\idcsmart_ticket\\controller\\TicketController@ticketLog")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_log']);
    Route::put('ticket/:id/content', "\\addon\\idcsmart_ticket\\controller\\TicketController@updateContent")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'update_content']);
    # 工单状态
    Route::put('ticket/:id/status', "\\addon\\idcsmart_ticket\\controller\\TicketController@status")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'status']);
    Route::put('ticket/reply/:id', "\\addon\\idcsmart_ticket\\controller\\TicketController@ticketReplyUpdate")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_reply_update']);
    Route::delete('ticket/reply/:id', "\\addon\\idcsmart_ticket\\controller\\TicketController@ticketReplyDelete")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket','_action'=>'ticket_reply_delete']);

    # 工单状态
    Route::get('ticket/status', "\\addon\\idcsmart_ticket\\controller\\TicketStatusController@ticketStatusList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_status','_action'=>'ticket_status_list']);
    Route::get('ticket/status/:id', "\\addon\\idcsmart_ticket\\controller\\TicketStatusController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_status','_action'=>'index']);
    Route::post('ticket/status', "\\addon\\idcsmart_ticket\\controller\\TicketStatusController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_status','_action'=>'create']);
    Route::put('ticket/status/:id', "\\addon\\idcsmart_ticket\\controller\\TicketStatusController@update")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_status','_action'=>'update']);
    Route::delete('ticket/status/:id', "\\addon\\idcsmart_ticket\\controller\\TicketStatusController@delete")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_status','_action'=>'delete']);
    # 工单预设回复
    Route::get('ticket/prereply', "\\addon\\idcsmart_ticket\\controller\\TicketPrereplyController@ticketPrereplyList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_prereply','_action'=>'ticket_prereply_list']);
    Route::get('ticket/prereply/:id', "\\addon\\idcsmart_ticket\\controller\\TicketPrereplyController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_prereply','_action'=>'index']);
    Route::post('ticket/prereply', "\\addon\\idcsmart_ticket\\controller\\TicketPrereplyController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_prereply','_action'=>'create']);
    Route::put('ticket/prereply/:id', "\\addon\\idcsmart_ticket\\controller\\TicketPrereplyController@update")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_prereply','_action'=>'update']);
    Route::delete('ticket/prereply/:id', "\\addon\\idcsmart_ticket\\controller\\TicketPrereplyController@delete")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_prereply','_action'=>'delete']);
    # 工单备注
    Route::get('ticket/notes', "\\addon\\idcsmart_ticket\\controller\\TicketNotesController@ticketNotesList")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_notes','_action'=>'ticket_status_list']);
    Route::get('ticket/:ticket_id/notes/:id', "\\addon\\idcsmart_ticket\\controller\\TicketNotesController@index")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_notes','_action'=>'index']);
    Route::post('ticket/:ticket_id/notes', "\\addon\\idcsmart_ticket\\controller\\TicketNotesController@create")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_notes','_action'=>'create']);
    Route::put('ticket/:ticket_id/notes/:id', "\\addon\\idcsmart_ticket\\controller\\TicketNotesController@update")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_notes','_action'=>'update']);
    Route::delete('ticket/:ticket_id/notes/:id', "\\addon\\idcsmart_ticket\\controller\\TicketNotesController@delete")
        ->append(['_plugin'=>'idcsmart_ticket','_controller'=>'ticket_notes','_action'=>'delete']);

})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckAdmin::class);