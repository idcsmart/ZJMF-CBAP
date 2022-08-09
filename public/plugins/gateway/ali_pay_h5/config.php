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
        'value' => '支付宝网页支付', // 表单的默认值
        'tip'   => '友好的显示名称', //表单的帮助提示
    ],
//    'idcsmart_auth_code'          => [// 在后台插件配置表单中的键名 ,会是config[text]
//        'title' => '授权码', // 表单的label标题
//        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
//        'value' => '支付宝支付', // 表单的默认值
//        'tip'   => '友好的显示名称', //表单的帮助提示
//    ],
//    'seller_id'      => [//
//        'title' => '卖家支付宝帐户',
//        'type'  => 'text',
//        'value' => '',
//        'tip'   => '需要申请支付宝商家集成',
//    ],
    'app_id'      => [
        'title' => 'appID',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'merchant_private_key'      => [
        'title' => '商户私钥',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'alipay_public_key'      => [
        'title' => '支付宝公钥',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
//    'sign_type'      => [
//        'title' => '加密方式',
//        'type'  => 'select',
//        'options' => [
//            'RSA2' => 'RSA2',
//            'MD5' => 'MD5',
//        ],
//        'value' => '',
//        'tip'   => '',
//    ],
    'currency'      => [
        'title' => '支持货币单位',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
//    'radio'         => [
//        'title'   => '模式',
//        'type'    => 'radio',
//        'options' => [
//            '1' => '调试',
//            '2' => '上线',
//        ],
//        'value'   => '1',
//        'tip'     => '开发组件',
//    ],

];
