<?php

$domain = request()->domain();

return [
    # 工单附件保存地址
    'ticket_upload' => WEB_ROOT . 'plugins/addon/idcsmart_ticket/upload/',
    # 工单附件访问地址
    'get_ticket_upload' => $domain . '/plugins/addon/idcsmart_ticket/upload/',
    # 短信/邮件模板初始化
    "ticket_notice_template" => [
        'client_create_ticket' => [
            'name_lang' => '客户新增工单',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '客户新增工单',
                'content' => '您的工单：【@var(subject)】正在处理中，请耐心等待'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '客户新增工单',
                'content' => '您的工单：【@var(subject)】正在处理中，请耐心等待'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '客户新增工单',
                'title' => '[{system_website_name}]客户新增工单',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_ticket/config/email_template/client_create_ticket.html')
            ],
			
        ],
        'client_close_ticket' => [
            'name_lang' => '客户关闭工单',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '客户关闭工单',
                'content' => '您的工单：【@var(subject)】已关闭'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '客户关闭工单',
                'content' => '您的工单：【@var(subject)】已关闭'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '客户关闭工单',
                'title' => '[{system_website_name}]客户关闭工单',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_ticket/config/email_template/client_close_ticket.html')
            ],
        ],
        'admin_reply_ticket' => [
            'name_lang' => '管理员回复工单',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '管理员回复工单',
                'content' => '您的工单：【@var(subject)】有新回复'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '管理员回复工单',
                'content' => '您的工单：【@var(subject)】有新回复'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '管理员回复工单',
                'title' => '[{system_website_name}]管理员回复工单',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_ticket/config/email_template/admin_reply_ticket.html')
            ],
        ],
        'client_reply_ticket' => [
            'name_lang' => '客户回复工单',
            'sms_name' => 'Idcsmart',
            'sms_template' => [
                'title' => '客户回复工单',
                'content' => '您的工单：【@var(subject)】有新回复'
            ],
            'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '客户回复工单',
                'content' => '您的工单：【@var(subject)】有新回复'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '客户回复工单',
                'title' => '[{system_website_name}]客户回复工单',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_ticket/config/email_template/client_reply_ticket.html')
            ],
        ],
        
    ],

];