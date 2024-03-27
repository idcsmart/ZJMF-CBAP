<?php
/*
 *  定义权限,系统会默认插入名称为插件名的一级权限,以下仅需定义二级/三级权限;(首先，要使用二级权限，插件后台控制器需要继承app\event\controller\PluginAdminBaseController基类控制器)
 */
return [
    [
        'title' => 'clientarea_auth_plugin_addon_ticket', # y用户工单
        'url' => 'ticket',
        'child' => [ # 操作权限
            [
                'title' => 'clientarea_auth_plugin_addon_ticket_view', # 工单查看   
                'url' => '',
                'auth_rule' => ['TicketController::ticketList', 'TicketController::statistic', 'TicketController::index', 'TicketController::download'],  # 工单列表具体控制器方法
                'auth_rule_title' => ['clientarea_auth_rule_plugin_addon_ticket_list', 'clientarea_auth_rule_plugin_addon_ticket_statistic', 'clientarea_auth_rule_plugin_addon_ticket_index', 'clientarea_auth_rule_plugin_addon_ticket_download']  # 具体权限名称
            ],
            [
                'title' => 'clientarea_auth_plugin_addon_ticket_manager',
                'url' => '',
                'auth_rule' => ['TicketController::create', 'TicketController::reply', 'TicketController::urge', 'TicketController::close'],
                'auth_rule_title' => ['clientarea_auth_rule_plugin_addon_ticket_create', 'clientarea_auth_rule_plugin_addon_ticket_reply', 'clientarea_auth_rule_plugin_addon_ticket_urge', 'clientarea_auth_rule_plugin_addon_ticket_close']  # 具体权限名称
            ],
        ]
    ],
];