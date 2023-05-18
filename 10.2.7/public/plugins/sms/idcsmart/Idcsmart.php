<?php
namespace sms\idcsmart;

use app\common\lib\Plugin;


class Idcsmart extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Idcsmart',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '智简魔方',
        'description' => '智简魔方官方短信平台接口',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0.0',
        'help_url'     => 'https://my.idcsmart.com/goods.html?id=922',//申请接口地址
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
		]
	]
	获取失败
	[
		'status'=>'error',
		'msg'=>'模板ID错误',
	]
	*/
	public function getCnTemplate($params)
	{		
		$param['template_id']=trim($params['template_id']);		
		$resultTemplate=$this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'GET');
		if($resultTemplate['status']==200){
			$data['status']="success";
			if($resultTemplate['template']){
				//单个模板
				$data['template']['template_id']=$resultTemplate['template']['template_id'];
				$data['template']['template_status']=$resultTemplate['template']['status'];
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
	public function createCnTemplate($params)
	{
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['sign']);	
		$param['content']=trim($params['content']);		
        $resultTemplate= $this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'POST');
		if($resultTemplate['status']==200){
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
	public function putCnTemplate($params)
	{
		$param['template_id']=trim($params['template_id']);
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['sign']);	
		$param['content']=trim($params['content']);
        $resultTemplate=  $this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'PUT');
		if($resultTemplate['status']==200){
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
	public function deleteCnTemplate($params)
	{
		$param['template_id']=trim($params['template_id']);
        $resultTemplate=$this->APIHttpRequestCURL('cn',"template",$param,$params['config'],'DELETE');
		if($resultTemplate['status']==200){
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
    public function sendCnSms($params)
    {
        $content=$this->templateParam($params['content'],$params['templateParam']);
        $param['to']=trim($params['mobile']);
		$param['content']=$this->templateSign($params['config']['sign']).$content;
        $resultTemplate= $this->APIHttpRequestCURL('cn',"send",$param,$params['config'],'POST');
        if($resultTemplate['status']==200){
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
		$param['template_id']=trim($params['template_id']);		
		$resultTemplate=$this->APIHttpRequestCURL('global',"template",$param,$params['config'],'GET');
		if($resultTemplate['status']==200){
			$data['status']="success";
			if($resultTemplate['template']){
				//单个模板
				$data['template']['template_id']=$resultTemplate['template']['template_id'];
				$data['template']['template_status']=$resultTemplate['template']['status'];
			}
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}

		return $data;
		

	}
	#创建国际模板
	public function createGlobalTemplate($params=[])
	{
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['global_sign']);	
		$param['content']=trim($params['content']);		
        $resultTemplate= $this->APIHttpRequestCURL('global',"template",$param,$params['config'],'POST');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['template']['template_id']=$resultTemplate['template_id'];
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#修改国际模板
	public function putGlobalTemplate($params=[])
	{
		$param['template_id']=trim($params['template_id']);
		$param['title']=trim($params['title']);	
		$param['signature']=$this->templateSign($params['config']['global_sign']);	
		$param['content']=trim($params['content']);
        $resultTemplate=  $this->APIHttpRequestCURL('global',"template",$param,$params['config'],'PUT');
		if($resultTemplate['status']==200){
			$data['status']="success";
			$data['template']['template_status']=1;
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#删除国际模板
	public function deleteGlobalTemplate($params=[])
	{
		$param['template_id']=trim($params['template_id']);
        $resultTemplate=$this->APIHttpRequestCURL('global',"template",$param,$params['config'],'DELETE');
		if($resultTemplate['status']==200){
			$data['status']="success";
		}else{
			$data['status']="error";
			$data['msg']=$resultTemplate['msg'];
		}
		return $data;
	}
	#发送国际短信
    public function sendGlobalSms($params=[])
    {
    	$content=$this->templateParam($params['content'],$params['templateParam']);
        $param['to']=trim($params['mobile']);
		$param['content']=$this->templateSign($params['config']['global_sign']).$content;
        $resultTemplate= $this->APIHttpRequestCURL('global',"send",$param,$params['config'],'POST');
		if($resultTemplate['status']==200){
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

	private function APIHttpRequestCURL($sms_type,$action,$param,$config,$method='POST'){			
		if($sms_type=='cn'){			
			$api='http://api1.idcsmart.com/smsapi.php?action='.$action;
			$headers = array(
				"api:".$config['api'],
				"key:".$config['key'],
				"Content-Type: application/x-www-form-urlencoded"
			);
		}else if($sms_type=="global"){
			$api='http://api1.idcsmart.com/smsglobalapi.php?action='.$action;
			$headers = array(
				"global-api:".$config['global_api'],
				"global-key:".$config['global_key'],
				"Content-Type: application/x-www-form-urlencoded"
			);
		}

		$postfields=http_build_query($param);
		/* var_dump($headers);
		var_dump($postfields);
		exit; */
		if($method!='GET'){
            $ch = curl_init();
            curl_setopt_array($ch, array(
               CURLOPT_URL => $api,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_POSTFIELDS => $postfields,
               CURLOPT_CUSTOMREQUEST => strtoupper($method),
               CURLOPT_HTTPHEADER => $headers
            ));
        }else{
            $url=$api."&".$postfields;
            $ch = curl_init($url) ;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1) ;
        }
        $output = curl_exec($ch);
        curl_close($ch);
        $output = trim($output, "\xEF\xBB\xBF");
        return json_decode($output,true);
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