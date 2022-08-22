<?php
/*
 * @author  xiong
 * @description 智简魔方官方短信平台接口
 *
 */
return [
	'api'=> [
		'title' => '应用ID', 
		'type'  => 'text', 
		'value' => '',
		'tip'   => '申请的api',
	],
	'key'=> [
		'title' => '应用秘钥', 
		'type'  => 'text', 
		'value' => '',
		'tip'   => '申请的key',	
	],
	'sign'=> [
		'title' => '短信SignName', 
		'type'  => 'text',
		'value' => '',
		'tip'   => '短信签名,必须用中文括号，例如：【智简魔方】',	
	],
	'global_api'=> [
		'title' => '国际短信应用ID', 
		'type'  => 'text', 
		'value' => '', 
		'tip'   => '申请的国际短信api',
	],
	'global_key'=> [
		'title' => '国际短信应用秘钥', 
		'type'  => 'text', 
		'value' => '',
		'tip'   => '申请的国际短信key',	
	],
	'global_sign'=> [
		'title' => '国际短信SignName', 
		'type'  => 'text', 
		'value' => '',
		'tip'   => '短信签名,必须用中文括号，例如：【智简魔方】',
	],	
];
