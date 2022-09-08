<?php
$domain = configuration('website_url');

return [
    //live生产环境,sandbox沙箱环境
    'mode' => 'live',
    //ID
    'clientId' => '',
    //密码
    'clientSecret' => '',
    //异步通知地址
    'notify_url' => "{$domain}/gateway/paypal/index/notify_handle",
    //同步跳转
    'return_url' => "{$domain}/gateway/paypal/index/return_handle",
    //取消支付地址
    'cancel_url' => "{$domain}/gateway/paypal/index/cancel_handle",
    //支付完成跳转地址至系统首页
    'url' => "{$domain}",
];