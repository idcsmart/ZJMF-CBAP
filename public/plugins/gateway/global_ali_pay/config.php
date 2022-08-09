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
        'value' => '支付宝国际支付(新)', // 表单的默认值
        'tip'   => '', //表单的帮助提示
    ],
//    'idcsmart_auth_code'          => [// 在后台插件配置表单中的键名 ,会是config[text]
//        'title' => '授权码', // 表单的label标题
//        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
//        'value' => '支付宝支付', // 表单的默认值
//        'tip'   => '友好的显示名称', //表单的帮助提示
//    ],
    'partner'   =>  [
        'title' => '境外收单香港产品商户在支付宝的用户ID',
        'type'  => 'text',
        'value' => '',
        'tip'   => '2088开头的16位数字',
    ],
    'key'      => [
        'title' => 'md5密钥',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'currency'      => [
        'title' => '支持货币单位',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
//    'currency'      => [
//        'title'   => '多选框',
//        'type'    => 'checkbox',
//        'options' => [
////            'CNY' => '人名币',
//            'USD' => '美元',
//            'PHP' => '菲律宾比索',
//            'IDR' => '印尼盾',
//            'KRW' => '韩元',
//        ],
//        'value'   => 'USD',
//        'tip'     => '签约货币',
//    ],
//    'currency'      => [
//        'title' => '签约货币',
//        'type'  => 'text',
//        'value' => 'USD,PHP,IDR,KRW',
//        'tip'   => '多个用英文逗号(,)隔开',
//    ],

//    'custom_config' => [// 在后台插件配置表单中的键名 ,会是config[custom_config]，这个键值很特殊，是自定义插件配置的开关
//        'title' => '自定义配置处理', // 表单的label标题
//        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
//        'value' => '0', // 如果值为1，表示由插件自己处理插件配置，配置入口在 AdminIndex/setting
//        'tip'   => '自定义配置处理', //表单的帮助提示
//    ],
//    'FriendlyName'          => [// 在后台插件配置表单中的键名 ,会是config[text]
//        'title' => '显示名称', // 表单的label标题
//        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
//        'value' => '国际支付宝（IDCSMART）', // 表单的默认值
//        'tip'   => '友好的显示名称', //表单的帮助提示
//    ],
//    'authCode'          => [// 在后台插件配置表单中的键名 ,会是config[text]
//        'title' => '授权码', // 表单的label标题
//        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
//        'value' => '', // 表单的默认值
//        'tip'   => '友好的显示名称', //表单的帮助提示
//    ],
//    'partner'      => [// 在后台插件配置表单中的键名 ,会是config[password]
//        'title' => '应用ID',
//        'type'  => 'text',
//        'value' => '',
//        'tip'   => '合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串,查看地址：https://globalprod.alipay.com/order/myOrder.htm',
//    ],
//    'key'      => [// 在后台插件配置表单中的键名 ,会是config[password]
//        'title' => 'MD5密钥',
//        'type'  => 'text',
//        'value' => '',
//        'tip'   => 'MD5密钥，安全检验码，由数字和字母组成的32位字符串,查看地址：https://globalprod.alipay.com/order/myOrder.htm',
//    ],
//    'transport'      => [// 在后台插件配置表单中的键名 ,会是config[password]
//        'title' => '访问模式',
//        'type'  => 'select',
//        'options' => [//select 和radio,checkbox的子选项
//            '1' => 'https', // 值=>显示
//            '2' => 'http',
//        ],
//        'value' => '1',
//        'tip'   => '根据自己的服务器是否支持ssl访问，若支持请选择https，https需要参看README.txt文件将ca证书文件上传到指定文件夹；若不支持请选择http',
//    ],
//    'multi_site'      => [// 在后台插件配置表单中的键名 ,会是config[password]
//        'title' => '启动多站点兼容模式',
//        'type'    => 'radio',
//        'options' => [
//            '1' => '启动',
//            '2' => '不启动',
//        ],
//        'value' => '1',
//        'tip'   => '用于多个站点同一支付宝商家接口',
//    ],
//
//    'site_security_code'         => [
//        'title'   => '站点识别码',
//        'type'    => 'text',
//        'value'   => '1',
//        'tip'     => '兼容模式下站点识别码 , 避免支付宝重单',
//    ],
//    'debug'        => [
//        'title' => '调试模式',
//        'type'    => 'radio',
//        'options' => [
//            '1' => '开启',
//            '2' => '关闭',
//        ],
//        'value' => '1',
//        'tip'   => '调试模式(数据写入 global_ali_pay/log.txt)',
//    ],


];
