<?php
/* *
 * MD5
 * 详细：MD5加密
 * 版本：3.3
 * 日期：2012-07-19
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 *Function:Alipay MD5 signature processing file,no need to modify
 *version:3.3
 *modify date:2012-08-17
 *instructions
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.
 */

/**
 * sign  签名字符串
 * @param $prestr 需要签名的字符串
 * @param $key 私钥
 * return 签名结果 sign generated
 */
function md5Sign($prestr, $key) {
	$prestr = $prestr . $key;
	return md5($prestr);
}

/**
 * 验证签名 sign verify
 * @param $prestr 需要签名的字符串pre-sign string
 * @param $sign 签名结果
 * @param $key 私钥
 * return 签名结果sign generated
 */
function md5Verify($prestr, $sign, $key) {
	$prestr = $prestr . $key;
	$mysgin = md5($prestr);

	if($mysgin == $sign) {
		return true;
	}
	else {
		return false;
	}
}
?>