<?php
$path = WEB_ROOT ."plugins/gateway/stripe_wx/CreditPayWebHook.key";
if(!file_exists($path)){
    file_put_contents($path,time());
}
$webHook="https://{$_SERVER['HTTP_HOST']}/gateway?_plugin=stripe_ali&_controller=Index&_action=index&token=".file_get_contents($path);

return [
    'module_name'          => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '名称', // 表单的label标题
        'type'  => 'text', // 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '(stripe)微信二维码支付插件', // 表单的默认值
        'tip'   => '(stripe)微信二维码支付插件', //表单的帮助提示
    ],
    'pk'      => [
        'title' => 'clientSecret',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'sk'      => [
        'title' => 'ApiKey',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'webhook'      => [
        'title' => 'Webhook',
        'type'  => 'text',
        'value' => $webHook,
        'tip'   => '',
    ],
    'currency'      => [
        'title' => '货币类型',
        'type'  => 'text',
        'value' => '',
        'tip'   => '',
    ],
    'helper'=>[
        'title'=>'使用帮助',
        'type'=>'text',
        'value'=>"先到https://dashboard.stripe.com/login注册或者登录帐号\n左侧导航开发者获取API密钥（pk开头的为clientSecret，sk开头的为ApiKey）\n左侧导航Webhook，创建或者更新端点为上面WebHook里的地址",
        'tip'=>'使用帮助'
    ],

];
