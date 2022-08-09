<?php

$domain = request()->domain();
return [
    # 工单状态
    'ticket_status' => [
        'Pending',
        'Handling',
        'Reply',
        'Replied',
        'Resolved',
        'Closed',
    ],
    # 工单附件保存地址
    'ticket_upload' => WEB_ROOT . 'plugins/addon/idcsmart_ticket/upload/',
    # 工单附件访问地址
    'get_ticket_upload' => $domain . '/plugins/addon/idcsmart_ticket/upload/',
    # 短信/邮件模板初始化
    "ticket_notice_template" => [
        'client_create_ticket' => [
            'name_lang' => '客户新增工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'client_reply_ticket' => [
            'name_lang' => '客户回复工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'client_urge_ticket' => [
            'name_lang' => '客户催单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'client_close_ticket' => [
            'name_lang' => '客户关闭工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_receive_ticket' => [
            'name_lang' => '管理员接收工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_reply_ticket' => [
            'name_lang' => '管理员回复工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_resolved_ticket' => [
            'name_lang' => '管理员已解决工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_receive_ticket_internal' => [
            'name_lang' => '管理员收到内部工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_handling_ticket_internal' => [
            'name_lang' => '管理员接收内部工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_resolved_ticket_internal' => [
            'name_lang' => '管理员已解决内部工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
        'admin_create_ticket_internal' => [
            'name_lang' => '管理员新增内部工单',
            'sms_name' => '',
            'sms_template' => [
                'title' => '',
                'template' => ''
            ],
            'sms_global_template' => [
                'title' => '',
                'template' => ''
            ],
            'email_template' => ''
        ],
    ],

];