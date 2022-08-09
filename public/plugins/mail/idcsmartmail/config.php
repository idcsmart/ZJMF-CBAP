<?php

return [
	'api'=> [
		'title' => 'AppId',
		'type'  => 'text', 
		'value' => '',
		'tip'   => '',
	],
    'key'=> [
        'title' => 'AppKey',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
	'from'=> [
        'title' => '发件人邮箱',
        'type'  => 'text',
        'value' => '',
        'tip'   => '（只需填写邮箱@前面的部分，统一用 mailnoticesystem.com域名，例如admin@mailnoticesystem.com）',
    ],
    'from_name'=> [
        'title' => '发件人名称',
        'type'  => 'text',
        'value' => '',
        'tip'   => '显示的邮件发送人名称，比如智简魔方',
    ],
];
