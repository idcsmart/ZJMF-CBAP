<?php
namespace sms\submail;

use app\common\lib\Plugin;


class Submail extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Submail',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '赛邮',
        'description' => '赛邮',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0',
        'help_url'     => 'https://www.mysubmail.com/',//申请接口地址
    );

    # 插件安装
    public function install()
    {
		//导入模板
		$smsTemplate= [];
		if (file_exists(__DIR__.'/config/smsTemplate.php')){
            $smsTemplate = require __DIR__.'/config/smsTemplate.php';
        }
		
        return $smsTemplate;
    }

    # 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }
	
	# 后台页面创建模板时可用参数
	public function description()
	{
		return file_get_contents(__DIR__.'/config/description.html');    
    } 
	
	#获取国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	template_id//模板的ID,
	template_status//只能是1,2,3（1正在审核，2审核通过，3未通过审核）
	msg//接口返回的错误消息传给msg参数
	[
		'status'=>'success',
		'template'=>[
			'template_id'=>'w34da',
			'template_status'=>2,
			'msg'=>"模板审核失败",
		]
	]
	获取失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function getCnTemplate($params=[])
	{		

		$param['template_id']=trim($params['template_id']);		
		$api_url='message/template.json';
		$resultTemplate=$this->APIHttpRequestCURL('cn',$api_url,$param,$params['config'],'GET');
		if($resultTemplate['status']=="success"){
			$data['status']="success";
			if($resultTemplate['template']){
				//单个模板
				$data['template']['template_id']=$resultTemplate['template']['template_id'];
				$data['template']['template_status']=$resultTemplate['template']['template_status'];
			}
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}

		return $data;
	}
	#创建国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	template_id//模板的ID,
	template_status//只能是1,2,3（1正在审核，2审核通过，3未通过审核）
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
		'template'=>[
			'template_id'=>'w34da',
			'template_status'=>2,
		]
	]
	失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function createCnTemplate($params=[])
	{
		$param['sms_title']=trim($params['title']);	
		$param['sms_signature']=$params['config']['app_sign'];	
		$param['sms_content']=trim($params['content']);
		$api_url='message/template.json';
        $resultTemplate= $this->APIHttpRequestCURL('cn',$api_url,$param,$params['config'],'POST');
		if($resultTemplate['status']=="success"){
			$data['status']="success";
			$data['template']['template_id']=$resultTemplate['template_id'];
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#修改国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	template_status//只能是1,2,3（1正在审核，2审核通过，3未通过审核）
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
		'template'=>[
			'template_status'=>2,
		]
	]
	失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function putCnTemplate($params=[])
	{
		$param['template_id']=trim($params['template_id']);
		if(!empty($params['title'])){
			$param['sms_title']=trim($params['title']);
		}		
		$param['sms_signature']=$params['config']['app_sign'];	
		$param['sms_content']=trim($params['content']);
		$api_url='message/template.json';
        $resultTemplate=  $this->APIHttpRequestCURL('cn',$api_url,$param,$params['config'],'PUT');
		if($resultTemplate['status']=="success"){
			$data['status']="success";
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#删除国内模板
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
	]
	失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function deleteCnTemplate($params=[])
	{
		$param['template_id']=trim($params['template_id']);
        $api_url='message/template.json';
        $resultTemplate=$this->APIHttpRequestCURL('cn',$api_url,$param,$params['config'],'DELETE');
		if($resultTemplate['status']=="success"){
			$data['status']="success";
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#发送国内短信
	/*
	返回数据格式
	status//状态只有两种（成功success，失败error）
	content//替换参数过后的模板内容
	msg//接口返回的错误消息传给msg参数
	成功
	[
		'status'=>'success',
		'content'=>'success',
	]
	失败
	[
		'status'=>'error',
		'content'=>'error',
		'msg'=>'手机号错误',
	]
	*/
    public function sendCnSms($params=[])
    {	
    	
        $content=$this->templateParam($params['content'],$params['templateParam']);
        $param['to']=trim($params['mobile']);
		$param['content']=$this->templateSign($params['config']['app_sign']).$content;
		$api_url='message/send.json';
        $resultTemplate= $this->APIHttpRequestCURL('cn',$api_url,$param,$params['config'],'POST');
		if($resultTemplate['status']=="success"){
			$data['status']="success";
			$data['content']=$content;
		}else{
			$data['status']="error";
			$data['content']=$content;
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
    }	
	#获取国际模板
	public function getGlobalTemplate($params=[])
	{		
		return $this->getCnTemplate($params);
		

	}
	#创建国际模板
	public function createGlobalTemplate($params=[])
	{
		return $this->createCnTemplate($params);
	}
	#修改国际模板
	public function putGlobalTemplate($params=[])
	{
		return $this->putCnTemplate($params);
	}
	#删除国际模板
	public function deleteGlobalTemplate($params=[])
	{
		return $this->deleteCnTemplate($params);
	}
	#发送国际短信
    public function sendGlobalSms($params=[])
    {
    	$content=$this->templateParam($params['content'],$params['templateParam']);
		$param['to']=trim($params['mobile']);
		$param['content']=$this->templateSign($params['config']['international_app_sign']).$content;
		$api_url='internationalsms/send.json';
        $resultTemplate= $this->APIHttpRequestCURL('global',$api_url,$param,$params['config'],'POST');
		if($resultTemplate['status']=="success"){
			$data['status']="success";
			$data['content']=$content;
		}else{
			$data['status']="error";
			$data['content']=$content;
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
    }	
	
	# 以下函数名自定义

	private function APIHttpRequestCURL($sms_type='cn',$api_url="",$post_data=[],$params=[],$method='POST'){
		$this->base_url='http://api.mysubmail.com/';
		if($sms_type=='cn'){			
			$request['appid']=$params['app_id'];
			$request['appkey']=$params['app_key'];
		}else if($sms_type=="global"){
			$request['appid']=$params['international_app_id'];
			$request['appkey']=$params['international_app_key'];
		}
		$api=$this->base_url.$api_url;
        
        $request['timestamp']=$this->getTimestamp();
		$request['signature']=$request['appkey'];
		$post_data=array_merge($request,$post_data);
		if($method!='GET'){
            $ch = curl_init();
            curl_setopt_array($ch, array(
               CURLOPT_URL => $api,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_POSTFIELDS => http_build_query($post_data),
               CURLOPT_CUSTOMREQUEST => strtoupper($method),
               CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded")
            ));
        }else{
            $url=$api.'?'.http_build_query($post_data);
            $ch = curl_init($url) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1) ;
        }
        $output = curl_exec($ch);
        curl_close($ch);
        $output = trim($output, "\xEF\xBB\xBF");
        return json_decode($output,true);
    }
    
    private function getTimestamp(){
        $api=$this->base_url.'service/timestamp.json';
        $ch = curl_init($api) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
        $output = curl_exec($ch) ;
        $timestamp=json_decode($output,true);       
        return $timestamp['timestamp'];
    }

    private function templateParam($content,$templateParam){
        foreach ($templateParam as $key => $para) {
            $content = str_replace('@var(' . $key . ')', $para, $content);//模板中的参数替换
        }  
		$content =preg_replace("/@var\(.*?\)/is","",$content);	
        return $content;
    }
	private function templateSign($sign){
		$sign = str_replace("【","",$sign);
		$sign = str_replace("】","",$sign);
		$sign = "【".$sign."】";  
        return $sign;
    }
}