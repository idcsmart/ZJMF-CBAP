<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_user_detail_ticket', // 权限标识，唯一，并且也用于语言文件中插件权限的多语言的键
        'url' => '', // 权限对应模板名，一般存在子权限时此项为空
        'description' => '工单', // 权限描述
        'parent' => 'auth_user_detail', // 父权限标识，如果需要加到其他权限下则需要填写 
        'child' => [ // 子权限
            [
                'title' => 'auth_user_detail_ticket_view',   
                'url' => 'client_ticket', // 对应到插件后台模板目录的client_ticket.html
                'auth_rule' => [ // 权限调用的接口方法，具体写法如下，命名空间::方法名
                    'app\admin\controller\HostController::hostList',
                    'app\admin\controller\ClientController::index',
                    'app\admin\controller\ClientController::clientList',
                    'app\admin\controller\AdminRoleController::adminRoleList',
                    'app\admin\controller\AdminController::adminList',
                    'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                    'addon\idcsmart_ticket\controller\TicketController::ticketList',
                    'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
                ],  # 具体调用方法
                'description' => '查看页面',
            ],
            [
                'title' => 'auth_user_detail_ticket_transfer_ticket',   
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_ticket\controller\TicketController::forward',
                ],
                'description' => '转单',
            ],
            [
                'title' => 'auth_user_detail_ticket_close_ticket',   
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_ticket\controller\TicketController::resolved',
                ],
                'description' => '关闭工单',
            ],
            [
                'title' => 'auth_user_detail_ticket_detail',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_ticket\controller\TicketController::index',
                    'addon\idcsmart_ticket\controller\TicketController::ticketLog',
                    'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
                    'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                    'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
                    'addon\idcsmart_ticket\controller\TicketNotesController::ticketNotesList',
                    'app\admin\controller\HostController::hostList',
                ],
                'description' => '查看工单详情',
            ],
        ]
    ],
    [
        'title' => 'auth_user_ticket',
        'url' => '',
        'description' => '用户工单', # 权限描述
        'parent' => 'auth_user', # 父权限 
        'child' => [
            [
                'title' => 'auth_user_ticket_list',
                'url' => '',
                'description' => '工单列表',
                'child' => [
                    [
                        'title' => 'auth_user_ticket_list_view',
                        'url' => 'index',
                        'auth_rule' => [
                            'app\admin\controller\ClientController::clientList',
                            'app\admin\controller\AdminRoleController::adminRoleList',
                            'app\admin\controller\AdminController::adminList',
                            'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                            'addon\idcsmart_ticket\controller\TicketController::ticketList',
                            'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_ticket_list_create_ticket',
                        'url' => 'ticket_add',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                            'addon\idcsmart_ticket\controller\TicketController::department',
                            'addon\idcsmart_ticket\controller\TicketController::create',
                            'app\admin\controller\ClientController::clientList',
                            'app\admin\controller\HostController::hostList',
                        ],
                        'description' => '新建工单',
                    ],
                    [
                        'title' => 'auth_user_ticket_list_transfer_ticket',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::forward',
                        ],
                        'description' => '转单',
                    ],
                    [
                        'title' => 'auth_user_ticket_list_close_ticket',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::resolved',
                        ],
                        'description' => '关闭工单',
                    ],
                    [
                        'title' => 'auth_user_ticket_list_ticket_detail',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::index',
                            'addon\idcsmart_ticket\controller\TicketController::ticketLog',
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
                            'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                            'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
                            'addon\idcsmart_ticket\controller\TicketNotesController::ticketNotesList',
                            'app\admin\controller\HostController::hostList',
                        ],
                        'description' => '查看工单详情',
                    ],
                ]
            ],
            [
                'title' => 'auth_user_ticket_configuration',
                'url' => '',
                'description' => '工单配置',
                'child' => [
                    [
                        'title' => 'auth_user_ticket_configuration_view',
                        'url' => 'ticket_setting',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                            'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
                            'addon\idcsmart_ticket\controller\TicketController::getConfig',
                            'app\admin\controller\AdminController::adminList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_ticket_configuration_ticket_department',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketTypeController::create',
                            'addon\idcsmart_ticket\controller\TicketTypeController::update',
                            'addon\idcsmart_ticket\controller\TicketTypeController::delete',
                        ],
                        'description' => '工单部门',
                    ],
                    [
                        'title' => 'auth_user_ticket_configuration_ticket_status',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketStatusController::create',
                            'addon\idcsmart_ticket\controller\TicketStatusController::update',
                            'addon\idcsmart_ticket\controller\TicketStatusController::delete',
                        ],
                        'description' => '工单状态',
                    ],
                    [
                        'title' => 'auth_user_ticket_configuration_save_ticket_notice',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::setConfig',
                        ],
                        'description' => '保存工单通知',
                    ],
                    [
                        'title' => 'auth_user_ticket_configuration_prereply',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::create',
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::update',
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::delete',
                        ],
                        'description' => '预设回复',
                    ],
                ]
            ],
            [
                'title' => 'auth_user_ticket_detail',
                'url' => '',
                'description' => '工单详情',
                'child' => [
                    [
                        'title' => 'auth_user_ticket_detail_view',
                        'url' => 'ticket_detail',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::index',
                            'addon\idcsmart_ticket\controller\TicketController::ticketLog',
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
                            'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
                            'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
                            'addon\idcsmart_ticket\controller\TicketNotesController::ticketNotesList',
                            'app\admin\controller\HostController::hostList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_ticket_detail_reply_ticket',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::reply',
                            'addon\idcsmart_ticket\controller\TicketController::ticketReplyUpdate',
                            'addon\idcsmart_ticket\controller\TicketController::ticketReplyDelete',
                        ],
                        'description' => '回复工单',
                    ],
                    [
                        'title' => 'auth_user_ticket_detail_create_notes',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketNotesController::create',
                            'addon\idcsmart_ticket\controller\TicketNotesController::update',
                            'addon\idcsmart_ticket\controller\TicketNotesController::delete',
                        ],
                        'description' => '添加备注',
                    ],
                    [
                        'title' => 'auth_user_ticket_detail_use_prereply',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
                        ],
                        'description' => '使用预设回复',
                    ],
                    [
                        'title' => 'auth_user_ticket_detail_ticket_log',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::ticketLog',
                        ],
                        'description' => '工单日志记录',
                    ],
                    [
                        'title' => 'auth_user_ticket_detail_save_ticket',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_ticket\controller\TicketController::status',
                        ],
                        'description' => '保存工单信息',
                    ],
                ]
            ],
        ]
    ],
];