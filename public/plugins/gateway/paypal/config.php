<?php
return [

    'module_name'          => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '名称', // 表单的label标题
        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => 'Paypal', // 表单的默认值
        'tip'   => '友好的显示名称', //表单的帮助提示
    ],
    'clientId'      => [
        'title' => 'clientId',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'clientSecret'      => [
        'title' => 'clientSecret',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
];
