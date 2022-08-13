<?php
$domain = configuration('domain');
return array(
	//同步跳转
	'return_url'    => $domain . '/gateway/stripe_wx/index/returnHandle',
	'notify_url'    => $domain . '/gateway/stripe_wx/index/notifyHandle',
    #
    'credit_pay_key'  => WEB_ROOT ."plugins/gateway/stripe_ali/CreditPay.key",

    'url' => $domain . '/reactmember'
);
