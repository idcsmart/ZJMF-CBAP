<?php
/*
 * @author  xiong
 * @description 阿里云接口配置信息
 *
 */
return [
	/*'charset'=> [
		'title' => '邮件编码',
		'type'  => 'text', 
		'value' => '',
		'tip'   => '',
	],*/
    'charset'=> [
        'title' => '邮件编码',
        'type'  => 'select',
        'options' => [//select 和radio,checkbox的子选项
            'utf-8' => 'utf-8', // 值=>显示
            '8bit' => '8bit',
            '7bit' => '7bit',
            'binary' => 'binary',
            'base64' => 'base64',
            'quoted-printable' => 'quoted-printable',
        ],
        'value' => '',
        'tip'   => '',
		'size'  => 200,
    ],
    'port'=> [
        'title' => 'SMTP 端口',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
		'size'  => 200,
    ],
    'host'=> [
        'title' => 'SMTP 主机名',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
		'size'  => 200,
    ],
    'username'=> [
        'title' => 'SMTP 用户名',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
		'size'  => 200,
    ],
    'password'=> [
        'title' => ' SMTP 密码',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
		'size'  => 200,
    ],
    /*'smtpsecure'=> [
        'title' => 'SMTP SSL类型',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],*/
    'smtpsecure'        => [// 在后台插件配置表单中的键名 ,会是config[select]
        'title'   => 'SMTP SSL类型',
        'type'    => 'select',
        'options' => [//select 和radio,checkbox的子选项
            '' => '无', // 值=>显示
            'ssl' => 'ssl',
            'tls' => 'tls',
        ],
        'value'   => '',
        'tip'     => '',
		'size'  => 200,
    ],
    'fromname'=> [
        'title' => '系统邮件名',
        'type'  => 'text',
        'value' => '智简魔方',
        'tip'   => '',
		'size'  => 200,
    ],
    'systememail'=> [
        'title' => '系统邮箱名',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
		'size'  => 200,
    ],
];
