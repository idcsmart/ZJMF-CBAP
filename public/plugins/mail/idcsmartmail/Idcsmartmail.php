<?php
namespace mail\idcsmartmail;

use app\common\lib\Plugin;

class Idcsmartmail extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Idcsmartmail',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '智简魔方',
        'description' => '智简魔方官方邮件平台接口',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0',
        'help_url'     => 'https://my.idcsmart.com/goods.html?id=337',//申请接口地址
    );

    # 插件安装
    public function install()
    {
		return true;
    }

    # 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function send($params)
    {
        $param = [
            'email' => $params['email'],
            'subject' => $params['subject'],
            'content' => $params['content'],
			'from' => $params['config']['from'],
            'from_name' => $params['config']['from_name'],
        ];

        if(!empty($params['attachments']))
        {
            $attachments = explode(',', $params['attachments']);

            foreach($attachments as $k=>$attachment)
            {
                $file=CMF_ROOT."public/upload/common/email/".$attachment;
                if(file_exists($file)){
					$param["attachments[{$k}]"] =curl_file_create($file);  
                }
                
            }
        }
 
        $result = $this ->APIHttpRequestCURL("send", $param,$params['config'], 'POST');
		if($result['status']==200){
			$data['status']="success";
		}else{
			$data['status']="error";
			$data['msg']=$result['msg'];
		}
        return $data;
    }

	private function APIHttpRequestCURL($action,$param,$config,$method='POST'){			
		$url='http://api1.idcsmart.com/emailapi.php?action='.$action;
		$headers = array(
			"api:".$config['api'],
			"key:".$config['key'],
			"Content-Type: multipart/form-data"
		);
		$ch = curl_init();
		curl_setopt_array($ch , array(
			CURLOPT_URL             => $url,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_POST            => 1 ,
			CURLOPT_POSTFIELDS      => $param,
			CURLOPT_HTTPHEADER => $headers
		));
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
		$output = curl_exec($ch);
		curl_close($ch);
        return json_decode($output,true);
    }

}