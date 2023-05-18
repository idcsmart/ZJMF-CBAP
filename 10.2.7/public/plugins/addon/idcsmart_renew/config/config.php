<?php

$domain = request()->domain();
return [
    # 短信/邮件模板初始化
    "renew_notice_template" => [
        'host_renew' => [
            'name_lang' => '产品续费',
            'sms_name' => 'Idcsmart',           
            'sms_template' => [
                'title' => '产品续费',
                'content' => '您购买的产品：【@var(product_name)】，现已续费成功,到期时间（【@var(product_due_time)】）'
            ],
			'sms_global_name' => 'Idcsmart',
            'sms_global_template' => [
                'title' => '产品续费',
                'content' => '您购买的产品：【@var(product_name)】，现已续费成功,到期时间（【@var(product_due_time)】）'
            ],
            'email_name' => 'Smtp',
            'email_template' => [
                'name' => '产品续费',
                'title' => '[{system_website_name}]产品续费成功',
                'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_renew/config/email_template/host_renew.html')
            ],
			
        ],  
    ],

];