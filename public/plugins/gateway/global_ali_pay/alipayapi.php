<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>支付宝境外收单交易接口接口</title>
</head>
<?php
/* *
 * 功能：境外收单交易接口接入页
 * 版本：3.4
 * 修改日期：2019-01-08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 *function:the access page of cross border payment interface 
 *version:3.4
 *modify date:2019-01-08
 *instructions:
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.

 *************************注意*****************
 
 *如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 *1、支持中心（https://global.alipay.com/service/service.htm）
 *2、支持邮箱（overseas_support@service.alibaba.com）
     业务支持邮箱(global.service@alipay.com)


 *如果想使用扩展功能,请按文档要求,自行添加到parameter数组即可。
 **********************************************
 *If you have problem during integration，we provide the below ways to help 
  
  *1、Development documentation center（https://global.alipay.com/service）
  *2、Technical assitant email（overseas_support@service.alibaba.com）
      Business assitant email (global.service@alipay.com)
  
  *If you want to use the extension,please add parameters according to the documentation.
 */

require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

/**************************请求参数**************************/
/**************************request parameter**************************/
        //商户订单号，商户网站订单系统中唯一订单号，必填
		//merchant order no,the unique transaction ID specified in merchant system ,not null
        $out_trade_no = $_POST['WIDout_trade_no'];

        //订单名称，必填
	    //order name  ,not null
        $subject = $_POST['WIDsubject'];

		//付款外币币种，必填
	    //The settlement currency code the merchant specifies in the contract. ,not null 
        $currency = $_POST['currency'];
		
        //付款外币金额，必填
		//payment amount in foreign currency ,not null
        $total_fee = $_POST['WIDtotal_fee'];

        //商品描述，可空
		//product description ,nullable
        $body = $_POST['WIDbody'];
	    //product_code could not be nullable for new_cross_border payment
	    $product_code = $_POST['WIDproduct_code'];
	    //split_fund_info could be nullable if the merchant does not need split fund to domerstic account; in JSON format
        //$split_fund_info = $_POST['WIDsplit_fund_info'];

         //trade_information : Information about the trade industry.
	    $trade_information = $_POST['WIDtrade_information'];
          //************************************************************/

//构造要请求的参数数组，无需改动
//package the request parameters
$parameter = array(
		"service"       => $alipay_config['service'],
		"partner"       => $alipay_config['partner'],
		"notify_url"	=> $alipay_config['notify_url'],
		"return_url"	=> $alipay_config['return_url'],
		"refer_url"	=> $alipay_config['refer_url'],
		"out_trade_no"	=> $out_trade_no,
		"subject"	=> $subject,
		"total_fee"	=> $total_fee,
		"body"	=> $body,
		"currency" => $currency,
		"product_code" => $product_code,
		//$split_fund_info => str_replace("\"", "'",'split_fund_info'),
		//"split_fund_info"=>$split_fund_info,	
			
		$trade_information => str_replace("\"", "'",'trade_information'),
		"trade_information"=>$trade_information,	


		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))



		//其他业务参数根据在线开发文档，添加参数.文档地址:https://global.alipay.com/service
		//To add other parameters,please refer to development documents.Document address:https://global.alipay.com/service
        //如"参数名"=>"参数值"
		//eg"parameter name"=>"parameter value"
		
);

//建立请求
//build request
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "OK");
echo $html_text;

?>
</body>
</html>