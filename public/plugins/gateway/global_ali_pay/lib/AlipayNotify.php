<?php

namespace gateway\global_ali_pay\lib;
/* *
 * 类名：AlipayNotify
 * 功能：支付宝通知处理类
 * 详细：处理支付宝各接口通知返回
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考
 *class name:AlipayNotify
 *Function:The class Alipay use to handle notification
 *Detail:Handle the notification of Alipay interfaces
 *version:3.3
 *modify date:2012-08-17
 *instructions:
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.

 *************************注意*************************
 * 调试通知返回时，可查看或改写log日志的写入TXT里的数据，来检查通知返回是否正常
 *************************Attention*************************
 *When debugging notification feedback，you can check or modify the text included into log to see whether the feedback is normal 
 */

require_once("alipay_core.function.php");
require_once("alipay_md5.function.php");

class AlipayNotify {
    /**
     * HTTPS形式消息验证地址
	 *The URL of verification of Alipay notification.
     */
     	//The verification URL of Alipay notification,sandbox environment.
	//var $https_verify_url = 'https://mapi.alipaydev.com/gateway.do/gateway.do?service=notify_verify&';
	//线上网关异步消息验证地址，如商户使用的生产环境，请换成下面的生产环境的地址
	//The verification URL of Alipay notification,production environment.(pls use the below line instead if you were in production environment)
	var $https_verify_url = 'https://intlmapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP形式消息验证地址
	 * The URL of verification of notification of HTTP type
     */
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	var $alipay_config;

	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
	}
    function AlipayNotify($alipay_config) {
    	$this->__construct($alipay_config);
    }
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
	 * Verify whether it's a legal notification sent from Alipay
     * @return 验证结果The result of verification
     */
	function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空check whether the info from POST is empty
			return false;
		}
		else {
			//验证MD5的结果
			//verify the MD5 sign
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
			
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			//Get the remote server ATN result(verify whether it's a legal notification sent from Alipay)
			$responseTxt = 'false';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}
			
			//写日志记录
			//write log
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_POST);
			//logResult($log_text);
			
			//验证
			//verify
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			//if responsetTxt is not true,the cause might be related to sever setting,merchant account and expiration time of notify_id(one minute).
            //if isSign is not true，the cause might be related to sign,charset and format of request str(eg:request with custom parameter etc.) 
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}
	
    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
	 * Verify whether it's a legal notification sent from Alipay
     * @return 验证结果result
     */
	function verifyReturn(){
		if(empty($_GET)) {//判断POST来的数组是否为空check the info from POST is blank
			return false;
		}
		else {
			//生成签名结果generate the sign
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
			return $isSign;
		}
	}
	
    /**
     * 获取返回时的签名验证结果
	 * Generate sign from feedback
     * @param $para_temp 通知返回来的参数数组the params from the feedback notification
     * @param $sign 返回的签名结果the sign to be compared
     * @return 签名验证结果the result of verification
     */
	function getSignVeryfy($para_temp, $sign) {
		//除去待签名参数数组中的空值和签名参数
		//Filter parameters with null value ,sign and sign_type
		$para_filter = paraFilter($para_temp);
		
		//对待签名参数数组排序
		//sort the to-be-signed 
		$para_sort = argSort($para_filter);
		
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		//connect all the parameters with "&" like "parameter=value"
		$prestr = createLinkstring($para_sort);
		
		$isSgin = false;
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $this->alipay_config['key']);
				break;
			default :
				$isSgin = false;
		}
		
		return $isSgin;
	}

    /**
     * 获取远程服务器ATN结果,验证返回URL
	* Get the remote server ATN result,return URL
     * @param $notify_id 通知校验IDThe ID for a particular notification. 
     * @return 服务器ATN结果Sever ATN result
     * 验证结果集：
	* Verification result:
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
	* invalid Pls check whether the partner and key are null from notification 
     * true 返回正确信息
	* true return the right info
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
	* false pls check the firewall or the server block certain port,also pls note the expiration time is 1 minute
     */
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
		
		return $responseTxt;
	}
}
?>
