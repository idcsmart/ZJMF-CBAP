<?php
namespace gateway\global_ali_pay\lib;
/* *
 * 类名：AlipaySubmit
 * 功能：支付宝各接口请求提交类
 * 详细：构造支付宝各接口表单HTML文本，获取远程HTTP数据
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 *class name:AlipaySubmit
 *Function:The class Alipay use to submit request
 *Detail:Construct the HTML form of Alipay interface,get the data from remote HTTP
 *version:3.3
 *modify date:2012-08-13
 *instructions:
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.
 **/
require_once("alipay_core.function.php");
require_once("alipay_md5.function.php");

class AlipaySubmit {

	var $alipay_config;
	/**
	 *支付宝网关地址（新）
	 * The Alipay gateway provided to merchants
	 */
	//沙箱网关The Alipay gateway of sandbox environment.
	//var $alipay_gateway_new = 'https://mapi.alipaydev.com/gateway.do?';
	//生产环境网关，如果商户用的生产环境请换成下面的正式网关
	//The Alipay gateway of production environment.(pls use the below line instead if you were in production environment)
	var $alipay_gateway_new = 'https://intlmapi.alipay.com/gateway.do?';
	
	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
	}
    function AlipaySubmit($alipay_config) {
    	$this->__construct($alipay_config);
    }
	
	/**
	 * 生成签名结果
	 * Generate the sign
	 * @param $para_sort 已排序要签名的数组Parameters to sign
	 * return 签名结果字符串sign generated
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    	//Rearrange parameters in the data set alphabetically and connect rearranged parameters with & like "parametername=value"
		$prestr = createLinkstring($para_sort);
		
		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$mysign = md5Sign($prestr, $this->alipay_config['key']);
				break;
			default :
				$mysign = "";
		}
		
		return $mysign;
	}

	/**
     * 生成要请求给支付宝的参数数组
	 * Generate a set of parameters need in the request of Alipay
     * @param $para_temp 请求前的参数数组Pre-sign string
     * @return 要请求的参数数组parameters need to be in the request
     */
	function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		//Remove the blank ,sign and sign_type
		$para_filter = paraFilter($para_temp);

		//对待签名参数数组排序
		//sort the presign string
		$para_sort = argSort($para_filter);

		//生成签名结果
		//Generate the sign
		$mysign = $this->buildRequestMysign($para_sort);
		
		//签名结果与签名方式加入请求提交参数组中
		//Add the sign and sign_type into the sPara
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		
		return $para_sort;
	}

	/**
     * 生成要请求给支付宝的参数数组
	 * Generate a set of parameters need in the request of Alipay
     * @param $para_temp 请求前的参数数组Pre-sign string
     * @return 要请求的参数数组字符串parameters need to be in the request
     */
	function buildRequestParaToString($para_temp) {
		//待请求参数数组
		//Pre-sign 
		$para = $this->buildRequestPara($para_temp);
		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		//connect rearranged parameters with & like "parametername=value",get the string urlencoded
		# $request_data = createLinkstringUrlencode($para);
        $request_data = $this->alipay_gateway_new.createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立请求，以表单HTML形式构造（默认）
	 * Build the request,costruct in the format of HTML form
     * @param $para_temp 请求参数数组the request params
     * @param $method 提交方式。两个值可选：post、getrequest form.support two types:post and get
     * @param $button_name 确认按钮显示文字The text of confirmation button
     * @return 提交表单HTML文本the text of requested HTML form
     */
	function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组pre-request params
		$para = $this->buildRequestPara($para_temp);
		
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->alipay_config['input_charset']))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
		//Pls don't set name attribute for the submit button 
        $sHtml = $sHtml."<input type='submit'  value='".$button_name."' style='display:none;'></form>";
		
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	
	/**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
	 * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
	 * Used to anti-phishing，use interface "query_timestamp" to get the function to get the timestamp
     * note：this support only PHP5 or above.So there should be DOMDocument and PHP environment of SLL of your sever and computer .Recommend you use PHP development tools in local debugging
     * return 时间戳字符串String of timestamp
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->alipay_config['partner']))."&_input_charset=".trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
}
?>