<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 
 * function:Server sync notification page
 * version:3.3
 *modify date：2012-08-17
 *instruction:
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
* This page can be tested locally.
* You can add HTML code and so forth to beautify the page.You can add program code of your business logic  too.
* You can debug this page with PHP development tool, alternatively,you can use the function logResult to write the log.The function is closed by default.Please refer to function verifyReturn in page alipay_notify_class.php.



 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
//计算得出通知验证结果
//caculate and get the result of verification
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功 verification is succeeded
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	//Please add yourprogram code here according to your business logic.
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	//——Please write program according to your business logic.(The below code is for your reference.)
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
    //To get the returned parameters from notification.You can refer to alipay's notification parameters list in integration documents.
	//商户订单号
	//out_trade_no
	$out_trade_no = $_GET['out_trade_no'];

	//支付宝交易号
	//trade_no
	$trade_no = $_GET['trade_no'];

	//交易状态
	//trade_status
	$trade_status = $_GET['trade_status'];


    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
		//判断该笔订单是否在商户网站中已经做过处理
			//Check whether the order has been processed in the partner's website.
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//If it has not been processed,query the detail of the order in order system of your website according to the order number (out_trade_no) and perform program code of your business logic.
			//如果有做过处理，不执行商户的业务程序
			//If the order has been processed in the partner's website,do not perform your program code of business logic.
    }
    else {
      echo "trade_status=".$_GET['trade_status'];
    }
		
	echo "验证成功<br />";

	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	//Please write program according to your business logic.(The above code is only for reference.)    
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败 fail
    //如要调试，请看alipay_notify.php页面的verifyReturn函数
    //To debug,pls refer to the verifyReturn function in alipay_notify.php file
    echo "验证失败fail";
}
?>
        <title>支付宝境外收单交易接口(create_forex_trade)</title>
	</head>
    <body>
    </body>
</html>