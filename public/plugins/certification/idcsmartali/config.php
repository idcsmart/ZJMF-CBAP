<?php
/*
 * 实名认证后台自定义配置
 */
return [
	'api'=> [
		'title' => 'api', 
		'type'  => 'text', 
		'value' => '',
		'tip'   => '申请的api',
	],
	'key'=> [
		'title' => 'key', 
		'type'  => 'text', 
		'value' => '',
		'tip'   => '申请的key',	
	],
	'biz_code'        => [// 在后台插件配置表单中的键名 ,会是config[select]
        'title'   => '认证方式',
        'type'    => 'select',
        'options' => [//select 和radio,checkbox的子选项
            'SMART_FACE'      => '快捷认证(无需识别)',
            'FACE'            => '人脸识别',
            'CERT_PHOTO'      => '身份证识别',
            'CERT_PHOTO_FACE' => '人脸+身份证',
        ],
        'value'   => 'FACE',
        'tip'     => '认证方式',
    ],
    /*'free' => [ # 无此配置,默认为0
        'title' => '免费认证次数',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '免费认证次数',
    ],
    'amount' => [ # 无此配置,默认为0
        'title' => '金额',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '支付金额',
    ],*/
];
