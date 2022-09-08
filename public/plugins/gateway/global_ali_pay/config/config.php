<?php
$domain = configuration('website_url');
$info = parse_url($domain);

if (isset($info['scheme']) && $info['scheme'] == 'https'){
    $protocol = 'https';
}else{
    $protocol = 'http';
}

return array(
    //下面的值默认是一个沙箱测试账号，您可参考下面网址申请自己的沙箱测试账号：https://global.alipay.com/help/integration/23
    'partner'       => '2088621935295134',
    // MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://globalprod.alipay.com/order/myOrder.htm, see key
    'key'           => 'fk03jzhvxqf2ulwzmflyw7ysied2g6tq',
    //异步通知地址
    'notify_url'    => "{$domain}/gateway/global_ali_pay/index/notify_handle",
    //同步跳转
    'return_url'    => "{$domain}/gateway/global_ali_pay/index/return_handle",
    //    'return_url'    => $domain,
    // 参考地址
    'refer_url'     => $domain,
    //签名方式
    //sign_type
    'sign_type'     => strtoupper('MD5'),

    //字符编码格式 目前支持 gbk 或 utf-8
    'input_charset' => strtoupper('UTF-8'),

    //ca证书路径地址，用于curl中ssl校验,在verify_nofity中使用
    //请保证cacert.pem文件在当前文件夹目录中
    'cacert'        => getcwd() . '/cacert.pem',
    //    'cacert'               => 'D:\www\test\GlobalAliPay1\cacert.pem',

    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    'transport'     => $protocol,

    // 产品类型，无需修改
    //Service name of the interface.No need to modify.
    'service'       => "create_forex_trade",

    'url' => $domain

    //    'gatewayUrl'           => "https://openapi.alipay.com/gateway.do",
);

?>