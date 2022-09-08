<?php
$domain = configuration('website_url');

return [
    //异步通知地址
    'notify_url' => "{$domain}/gateway/epusdt/index/notifyHandle",
    //同步跳转
    'redirect_url' => "{$domain}/gateway/epusdt/index/returnHandle",
    //支付完成跳转地址至系统首页
    'url' => "{$domain}",
];