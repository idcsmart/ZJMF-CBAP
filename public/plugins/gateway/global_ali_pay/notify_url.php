<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 * function:Server asynchronous notification page
 * version:3.3
 *modify date：2012-08-17
 *instruction:
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
 *************************function description*************************
 * When creating this page file, please pay attention and ensure there's not any HTML code and space in the page file.
 * This page cannot be tested locally. Please do the test on the server and make sure that it is accessable from outer net.
 * The page debugging tool, please use the function logResult to write the log which is closed by default.Please refer to function verifyNotify in page alipay_notify_class.php.
 *If Alipay system do not receive success returned on this page, If not, Alipay server would keep re-sending notification until over 24 hour according to retransmission strategy
 * */
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

//计算得出通知验证结果
//caculate and get the result of verification
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功 verification is succeeded
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代
	//Please add yourprogram code here according to your business logic.

	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	//——Please write program according to your business logic.(The below code is for your reference.)
	
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
    //To get the returned parameters from notification.You can refer to alipay's notification parameters list in integration documents.
	//商户订单号
	//out_trade_no
	$out_trade_no = $_POST['out_trade_no'];

	//支付宝交易号
	//trade_no
	$trade_no = $_POST['trade_no'];

	//交易状态
	//trade_status
	$trade_status = $_POST['trade_status'];
	


    if($_POST['trade_status'] == 'TRADE_FINISHED') {
		//判断该笔订单是否在商户网站中已经做过处理
			//Check whether the order has been processed in the partner's website.
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//If it has not been processed,query the detail of the order in order system of your website according to the order number (out_trade_no) and perform program code of your business logic.
			//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
			//Please make sure the total_fee, seller_id get from notification are the same with the parameters in request.
			//如果有做过处理，不执行商户的业务程序
			//If the order has been processed in the partner's website,do not perform your program code of business logic.
				


        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
	//For debug,write text function to record how your program behaves
        //logResult("Write the variable or record of program you want to debug with");	
    }
    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
		//判断该笔订单是否在商户网站中已经做过处理
		//To check whether the order has been processed in the partner's website.
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//If it has not been processed,query the detail of the order in order system of your website according to the order number (out_trade_no) and perform program code of your business logic.
			//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
			//Please make sure the total_fee, seller_id get from notification are the same with the parameters in request.
			//如果有做过处理，不执行商户的业务程序
			//If the order has been processed in the partner's website,do not perform your program code of business logic.
				
		//注意：
			//note:
		//付款完成后，支付宝系统发送该交易状态通知
		//When transanction finished,Alipay system will notification with trade status 
        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
	//For debug,write text function to record how your program behaves
        //logResult("Write the variable or record of program you want to debug with");
    }

	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	//Please write program according to your business logic.(The above code is only for reference.)    
	echo "success";		//请不要修改或删除 do not modify or delete
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    echo "fail";

    //调试用，写文本函数记录程序运行情况是否正常
    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    //For debug,write text function to record how your program behaves
    //logResult("Write the variable or record of program you want to debug with");
}
?>