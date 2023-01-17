<?php
/*
 * 实名认证后台自定义配置
 */
return [
    'module_name'            => [    # 在后台插件配置表单中的键名(统一规范:小写+下划线),会是config[module_name]
        'title' => '名称',            # 表单的label标题
        'type'  => 'text',           # 表单的类型：text文本,password密码,checkbox复选框,select下拉,radio单选,textarea文本区域,tip提示
        'value' => '智简魔方-芝麻信用',     # 表单的默认值
        'tip'   => 'friendly name',  # 表单的帮助提示
        'size'  => 200,               # 输入框长度(当type类型为text,password,textarea,tip时,可传入此键)
    ],
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
