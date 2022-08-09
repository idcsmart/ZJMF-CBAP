<?php
/* *
 * 支付宝接口公用函数
 * 详细：该类是请求、通知返回两个文件所调用的公用函数核心处理文件
 * 版本：3.3
 * 日期：2012-07-19
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 *Function:Alipay public constructor
 *Detail:This is the core public constructor processing file of AlipaySubmit and AlipayNotify.No need to modify.
 *version:3.3
 *modify date:2012-07-19
 *instructions:
 *This code below is a sample demo for merchants to do test.Merchants can refer to the integration documents and write your own code to fit your website.Not necessarily to use this code.  
 *Alipay provide this code for you to study and research on Alipay interface, just for your reference.
 */

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组 array needs to be connected
 * return 拼接完成以后的字符串 String with connected parameters
 * connect parameters with & like "parameter name=value"
 */
function createLinkstring($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".$val."&";
	}
	//去掉最后一个&字符
	//remove the last &
	$arg = substr($arg,0,count($arg)-2);
	//如果存在转义字符，那么去掉转义
	//remove escape character if there's any
	
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}
/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
 * connect parameters to a string with & like "parameter name=value",get the string urlencoded
 * @param $para 需要拼接的数组array needs to be connected
 * return 拼接完成以后的字符串String with connected parameters
 */
function createLinkstringUrlencode($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".urlencode($val)."&";
	}
	//去掉最后一个&字符
	//remove the last &
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	//remove escape character if there's any
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}
/**
 * 除去数组中的空值和签名参数
 * Remove the blank ,sign and sign_type
 * @param $para 签名参数组A set of signature parameters
 * return 去掉空值与签名参数后的新签名参数组The new signature paramaters with the blank ,sign and sign_type removed
 */
function paraFilter($para) {
	$para_filter = array();
	while (list ($key, $val) = each ($para)) {
		if($key == "sign" || $key == "sign_type" || $val == "")continue;
		else	$para_filter[$key] = $para[$key];
	}
	return $para_filter;
}
/**
 * 对数组排序 rearrange
 * @param $para 排序前的数组 before rearrange
 * return 排序后的数组 rearranged
 */
function argSort($para) {
	ksort($para);
	reset($para);
	return $para;
}
/**
 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
 * Write the log for your convienence in testing(You can also load these record into database ,it depends on your requirement)
 * 注意：服务器需要开通fopen配置 note:the sever needs to be configured with fopen
 * @param $word 要写入日志里的文本内容 默认值：空值 text needs to be included into the log 
 */
function logResult($word='') {
	$fp = fopen("log.txt","a");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"执行日期executetime：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

/**
 * 远程获取数据，POST模式
 * remote data access,POST
 * 注意：
 * note:
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * 1.To use Crul,you need to change the setting of php.ini,just remove the ";" of php_curl.dll
 * 2.cacert.pem in the folder is SSL certificate,pls make sure it is accessible，the default path is：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址 the full path of URL
 * @param $cacert_url 指定当前工作目录绝对路径 The absolute path to the current working directory
 * @param $para 请求的数据 request data
 * @param $input_charset 编码格式。默认值：空值 charset 
 * return 远程输出的数据 remote output data
 */
function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {

	if (trim($input_charset) != '') {
		$url = $url."_input_charset=".$input_charset;
	}
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证SSL certificate authentication
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证Strict certification
	curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址Address of the certificate
	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头 filter header
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果 show the output
	curl_setopt($curl,CURLOPT_POST,true); // post传输数据 transfer the data with post
	curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据transfer the data with post
	$responseText = curl_exec($curl);
	//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容uncomment this to check the error that happens when executing the curl
	curl_close($curl);
	
	return $responseText;
}

/**
 * 远程获取数据，GET模式
  * remote data access,GET
 * 注意：
 * note:
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * 1.To use Crul,you need to change the setting of php.ini,just remove the ";" of php_curl.dll
 * 2.cacert.pem in the folder is SSL certificate,pls make sure it is accessible，the default path is：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址the full path of URL
 * @param $cacert_url 指定当前工作目录绝对路径The absolute path to the current working directory
 * return 远程输出的数据remote output data
 */
function getHttpResponseGET($url,$cacert_url) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头filter header
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果show the output
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证SSL certificate authentication
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证Strict certification
	curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址Address of the certificate
	$responseText = curl_exec($curl);
	//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容uncomment this to check the error that happens when executing the curl
	curl_close($curl);
	
	return $responseText;
}

/**
 * 实现多种字符编码方式
 * to support variety of character encoding
 * @param $input 需要编码的字符串 string needs to be encoded
 * @param $_output_charset 输出的编码格式 
 * @param $_input_charset 输入的编码格式
 * return 编码后的字符串 the string encoded
 */
function charsetEncode($input,$_output_charset ,$_input_charset) {
	$output = "";
	if(!isset($_output_charset) )$_output_charset  = $_input_charset;
	if($_input_charset == $_output_charset || $input ==null ) {
		$output = $input;
	} elseif (function_exists("mb_convert_encoding")) {
		$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
	} elseif(function_exists("iconv")) {
		$output = iconv($_input_charset,$_output_charset,$input);
	} else die("sorry, you have no libs support for charset change.");
	return $output;
}
/**
 * 实现多种字符解码方式
 * to support variety of character encoding
 * @param $input 需要解码的字符串string needs to be encoded
 * @param $_output_charset 输出的解码格式
 * @param $_input_charset 输入的解码格式
 * return 解码后的字符串the string encoded
 */
function charsetDecode($input,$_input_charset ,$_output_charset) {
	$output = "";
	if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
	if($_input_charset == $_output_charset || $input ==null ) {
		$output = $input;
	} elseif (function_exists("mb_convert_encoding")) {
		$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
	} elseif(function_exists("iconv")) {
		$output = iconv($_input_charset,$_output_charset,$input);
	} else die("sorry, you have no libs support for charset changes.");
	return $output;
}
?>