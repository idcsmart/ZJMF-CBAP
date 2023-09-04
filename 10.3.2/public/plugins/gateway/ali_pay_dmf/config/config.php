<?php
$domain = configuration('website_url');
return array(
    //异步通知地址
    'notify_url'           => "{$domain}/gateway/ali_pay_dmf/index/notifyHandle",

    //签名方式,默认为RSA2(RSA2048)
    'sign_type'            => "RSA2",

    'alipay_public_key'    => "",

    //编码格式
    'charset'              => "UTF-8",

    //支付宝网关
    'gatewayUrl'           => "https://openapi.alipay.com/gateway.do",

    //应用ID
    'app_id'               => "",

    //最大查询重试次数
    'MaxQueryRetry'        => "10",

    //查询间隔
    'QueryDuration'        => "3",
);