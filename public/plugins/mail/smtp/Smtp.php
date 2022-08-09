<?php
namespace mail\smtp;

use app\common\lib\Plugin;
use PHPMailer\PHPMailer\PHPMailer;


class Smtp extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Smtp',//Demo插件英文名，改成你的插件英文就行了
        'title'       => 'Smtp',
        'description' => 'Smtp',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0',
        'help_url'     => '',//申请接口地址
    );

    const ATTACHMENTS_ADDRESS = './upload/common/email/';


    private $isDebug = 0;

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
		
		if(empty($params['config']['host']) || empty($params['config']['smtpsecure']) || empty($params['config']['port']) || empty($params['config']['charset']) || empty($params['config']['fromname']) || empty($params['config']['username']) || empty($params['config']['password']) || empty($params['config']['systememail'])){
			
			return ['status' => 'error', 'msg' => 'The mail configuration item cannot be empty'];
			
		}
	
        $mail = $this ->getMail($params['config']);
        $mail ->addAddress($params['email']);
        $mail ->addCC($params['email']);
        $mail ->addBCC($params['email']);
        if(!empty($params['attachments']))
        {
            $attachments = explode(',', $params['attachments']);

            foreach($attachments as $attachment)
            {
                $originalName = explode('^', $attachment)[1];
                $mail ->AddAttachment(self::ATTACHMENTS_ADDRESS . $attachment, $originalName);
            }
        }

        $mail ->Body = $params['content'];

        if($params['subject'])
        {
            $mail->Subject = $params['subject'];
        }

        $result = $mail ->send();
        $mail ->ClearAllRecipients();
        if(!$result)
        {
			$encoding=mb_detect_encoding($mail ->ErrorInfo, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
			if($encoding!="UTF-8"){
				$mail ->ErrorInfo=mb_convert_encoding($mail ->ErrorInfo,"UTF-8",$encoding);
			}
            return ['status' => 'error', 'msg' => $mail ->ErrorInfo];
        }
        return ['status' => 'success'];
    }

    private function getMail($config = [])
    {
        $mail = new PHPMailer();
        $mail->clearCCs();
        $mail->clearBCCs();
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->clearAllRecipients();
        //调试模式
        $mail->SMTPDebug = $this->isDebug;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Timeout = 10;
        $mail->Host = $config['host'];
        $mail->SMTPSecure = strtolower($config['smtpsecure']);
        $mail->Port = $config['port'];
        $mail->CharSet = $config['charset'];
        $mail->FromName = $config['fromname'];
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->From = $config['systememail'];
        $mail->isHTML(true);

        return $mail;
    }

}