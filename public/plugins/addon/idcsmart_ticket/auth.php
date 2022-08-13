<?php
/*
 *  定义权限,系统会默认插入名称为插件名的一级权限,以下仅需定义二级/三级权限;(首先，要使用二级权限，插件后台控制器需要继承app\event\controller\PluginAdminBaseController基类控制器)
 */
return [
    [
        'title' => 'auth_plugin_addon_ticket_list', # y用户工单
        'url' => 'ticket',
        'child' => [ # 操作权限
            [
                'title' => 'auth_plugin_addon_ticket_view', # 工单查看
                'url' => '',
                'auth_rule' => 'TicketController::ticketList',  # 工单列表具体控制器方法
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_list'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_receive',
                'url' => '',
                'auth_rule' => 'TicketController::receive',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_receive'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_resolved',
                'url' => '',
                'auth_rule' => 'TicketController::resolved',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_resolved'  # 具体权限名称
            ],
        ]
    ],
    [
        'title' => 'auth_plugin_addon_ticket_detail', # 工单详情
        'url' => 'ticket_detail',
        'child' => [
            [
                'title' => 'auth_plugin_addon_ticket_view',
                'url' => '',
                'auth_rule' => 'TicketController::index',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_index'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_reply',
                'url' => '',
                'auth_rule' => 'TicketController::reply',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_reply'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_download',
                'url' => '',
                'auth_rule' => 'TicketController::download',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_download'  # 具体权限名称
            ],
        ]
    ],
    [
        'title' => 'auth_plugin_addon_ticket_internal_list', # 内部工单
        'url' => 'ticket_internal',
        'child' => [
            [
                'title' => 'auth_plugin_addon_ticket_view',
                'url' => '',
                'auth_rule' => 'TicketInternalController::ticketInternalList',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_list'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_view',
                'url' => '',
                'auth_rule' => 'TicketInternalController::index',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_index'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_add',
                'url' => '',
                'auth_rule' => 'TicketInternalController::create',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_create'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_receive',
                'url' => '',
                'auth_rule' => 'TicketInternalController::receive',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_receive'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_resolved',
                'url' => '',
                'auth_rule' => 'TicketInternalController::resolved',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_resolved'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_reply',
                'url' => '',
                'auth_rule' => 'TicketInternalController::reply',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_reply'  # 具体权限名称
            ],
            [
                'title' => 'auth_plugin_addon_ticket_forward',
                'url' => '',
                'auth_rule' => 'TicketInternalController::forward',
                'auth_rule_title' => 'auth_rule_plugin_addon_ticket_internal_forward'  # 具体权限名称
            ],
        ]
    ],
];