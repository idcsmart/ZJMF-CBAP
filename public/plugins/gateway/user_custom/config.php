<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [

    'module_name'          => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '名称', // 表单的label标题
        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '线下支付', // 表单的默认值
        'tip'   => '友好的显示名称', //表单的帮助提示
    ],
    'seller_id'      => [//
        'title' => '基础信息',
        'type'  => 'textarea',
        'value' => '',
        'tip'   => '填写基础信息',
    ],
//    'currency'      => [
//        'title' => '支持货币单位',
//        'type'  => 'text',
//        'value' => '',
//        'tip'   => '',
//    ],
];
