<?php

$domain = request()->domain();
return [
    # 实名附件保存地址
    'certification_upload_url' => WEB_ROOT . 'plugins/addon/idcsmart_certification/upload/',
    # 实名附件访问地址
    'get_certification_upload_url' => $domain . '/plugins/addon/idcsmart_certification/upload/',

    'certification_notice_template' => [
    	'idcsmart_certification_pass' => [
			'name_lang' => '实名认证通过',
			'sms_name' => 'Idcsmart',           
			'sms_template' => [
				'title' => '实名认证通过',
				'content' => '恭喜！您的账户实名认证已通过。'
			],
			'sms_global_name' => 'Idcsmart',
			'sms_global_template' => [
				'title' => '实名认证通过',
				'content' => '恭喜！您的账户实名认证已通过。'
			],
			'email_name' => 'Smtp',
			'email_template' => [
				'name' => '实名认证通过',
				'title' => '实名认证通过',
				'content' => file_get_contents(WEB_ROOT . 'plugins/addon/idcsmart_certification/config/email_template/idcsmart_certification_pass.html')
			],
			
		],
    ]
    
];