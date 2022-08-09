<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>支付</title>
</head>
<?php
require_once dirname(dirname(__FILE__)).'/config.php';
require_once dirname(__FILE__).'/service/AlipayTradeService.php';
require_once dirname(__FILE__).'/buildermodel/AlipayTradePagePayContentBuilder.php';

    //商户订单号，商户网站订单系统中唯一订单号，必填
    $out_trade_no = trim($_POST['WIDout_trade_no']);

    //订单名称，必填
    $subject = trim($_POST['WIDsubject']);

    //付款金额，必填
    $total_amount = trim($_POST['WIDtotal_amount']);

    //商品描述，可空
    $body = trim($_POST['WIDbody']);

	//构造参数
	$payRequestBuilder = new AlipayTradePagePayContentBuilder();
	$payRequestBuilder->setBody($body);
	$payRequestBuilder->setSubject($subject);
	$payRequestBuilder->setTotalAmount($total_amount);
	$payRequestBuilder->setOutTradeNo($out_trade_no);

	$aop = new AlipayTradeService($config);

	/**
	 * pagePay 电脑网站支付请求
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @param $return_url 同步跳转地址，公网可以访问
	 * @param $notify_url 异步通知地址，公网可以访问
	 * @return $response 支付宝返回的信息
 	*/
	$response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

	//输出表单
	var_dump($response);
?>
<form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=UTF-8' method='POST'><input type='hidden' name='biz_content' value='{"product_code":"FAST_INSTANT_TRADE_PAY","body":"顺戴网络充值","subject":"顺戴网络充值","total_amount":"0.01","out_trade_no":"800509","qr_pay_mode":4,"qrcode_width":200,"passback_params":"recharge@58@800509@0.01@AliPayH5"}'/><input type='hidden' name='app_id' value='2019070965804091'/><input type='hidden' name='version' value='1.0'/><input type='hidden' name='format' value='json'/><input type='hidden' name='sign_type' value='RSA2'/><input type='hidden' name='method' value='alipay.trade.page.pay'/><input type='hidden' name='timestamp' value='2020-06-23 09:33:43'/><input type='hidden' name='alipay_sdk' value='alipay-sdk-php-20161101'/><input type='hidden' name='notify_url' value='http://f.test.idcsmart.com/gateway/ali_pay/index/notify_handle'/><input type='hidden' name='return_url' value='http://f.test.idcsmart.com/gateway/ali_pay/index/return_handle'/><input type='hidden' name='charset' value='UTF-8'/><input type='hidden' name='sign' value='JJZ8KwLAPdjWqp8DsUbxqz3Nn3FoKx4+pAOXV31hTAhOtltPMlOdn4VR/GU/Upp6EV8o2NBdtJFgdN47qPThjGbpr23KDhIBicYcWgeg+l2oNH0p0FUK5gs1jPM2xtBWgAHvvSd4+YQuL+w4bGCqmoQHwejIY2gI503a6d5ZUOihRIks0w3W4yLBUnx2X7JuddgZ9lx7QWCBSuVBGzHzKXh29wWzvoo0B2IStJpo10FEBqD6ymKfV92RtQV/nwyA7Q1oUSt8wqD+sU9wrhgY5D+ZjsQ8y/m9nMSHaf9WCsp5dhkh33emUmApyMmoK2MstvRmmhiLeb4OCB2DrvsRMQ=='/><input type='submit' value='立即支付'></form>"

</body>
</html>