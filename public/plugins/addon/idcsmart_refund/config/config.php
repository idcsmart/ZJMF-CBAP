<?php

return [
    # 短信/邮件模板初始化
	'client_create_refund' => [
		'name_lang' => '产品退款申请',
		'sms_name' => 'Idcsmart',           
		'sms_template' => [
			'title' => '产品退款申请',
			'content' => '您的退款申请（产品@var(product_name)），正在处理'
		],
		'sms_global_name' => 'Idcsmart',
		'sms_global_template' => [
			'title' => '产品退款申请',
			'content' => '您的退款申请（产品@var(product_name)），正在处理'
		],
		'email_name' => 'Smtp',
		'email_template' => [
			'name' => '产品退款申请',
			'title' => '[{system_website_name}]产品退款申请',
			'content' => file_get_contents(__DIR__ . '/email_template/client_create_refund.html')
		],
		
	],
	'client_refund_success' => [
		'name_lang' => '产品退款成功',
		'sms_name' => 'Idcsmart',           
		'sms_template' => [
			'title' => '产品退款成功',
			'content' => '您的退款申请（产品@var(product_name)），已成功退款'
		],
		'sms_global_name' => 'Idcsmart',
		'sms_global_template' => [
			'title' => '产品退款成功',
			'content' => '您的退款申请（产品@var(product_name)），已成功退款'
		],
		'email_name' => 'Smtp',
		'email_template' => [
			'name' => '产品退款成功',
			'title' => '[{system_website_name}]产品退款成功',
			'content' => file_get_contents(__DIR__ . '/email_template/client_refund_success.html')
		],
	],
	'admin_refund_reject' => [
		'name_lang' => '产品退款驳回',
		'sms_name' => 'Idcsmart',           
		'sms_template' => [
			'title' => '产品退款驳回',
			'content' => '您的退款申请（产品@var(product_name)），被驳回'
		],
		'sms_global_name' => 'Idcsmart',
		'sms_global_template' => [
			'title' => '产品退款驳回',
			'content' => '您的退款申请（产品@var(product_name)），被驳回'
		],
		'email_name' => 'Smtp',
		'email_template' => [
			'name' => '产品退款驳回',
			'title' => '[{system_website_name}]产品退款驳回',
			'content' => file_get_contents(__DIR__ . '/email_template/admin_refund_reject.html')
		],
	],
	'client_refund_cancel' => [
		'name_lang' => '产品取消请求',
		'sms_name' => 'Idcsmart',           
		'sms_template' => [
			'title' => '产品取消请求',
			'content' => '您已成功申请取消（产品@var(product_name)），感谢您的支持'
		],
		'sms_global_name' => 'Idcsmart',
		'sms_global_template' => [
			'title' => '产品取消请求',
			'content' => '您已成功申请取消（产品@var(product_name)），感谢您的支持'
		],
		'email_name' => 'Smtp',
		'email_template' => [
			'name' => '产品取消请求',
			'title' => '[{system_website_name}]产品取消请求',
			'content' => file_get_contents(__DIR__ . '/email_template/client_refund_cancel.html')
		],
	],
        

];