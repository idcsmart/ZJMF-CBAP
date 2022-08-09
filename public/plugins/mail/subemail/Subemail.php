<?php
namespace mail\subemail;

use app\common\lib\Plugin;
use PHPMailer\PHPMailer\PHPMailer;


class Subemail extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Subemail',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '赛邮',
        'description' => '赛邮',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0',
        'help_url'     => 'https://www.mysubmail.com/',//申请接口地址
    );

    const CODE = [
        101 => '不正确的 APP ID'
        ,102 => '此应用已被禁用，请至 submail > 应用集成 > 应用 页面开启此应用'
        ,103 => '未启用的开发者，此应用的开发者身份未验证，请更新您的开发者资料'
        ,104 => '此开发者未通过验证或此开发者资料发生更改。请至应用集成页面更新你的开发者资料'
        ,105 => '此账户已过期'
        ,106 => '此账户已被禁用'
        ,107 => 'sign_type （验证模式）必须设置为 MD5（MD5签名模式）或 SHA1（SHA1签名模式）或 normal (密匙模式).'
        ,108 => 'signature 参数无效'
        ,109 => 'appkey 无效'
        ,110 => 'sign_type 错误'
        ,111 => '空的 signature 参数'
        ,113 => '请求的 APPID 已设置 IP 白名单，您的 IP 不在此白名单范围'
        ,204 => '请将收件人名称 （to_name）控制在50个字符以内。'
        ,205 => '错误的发件人地址。'
        ,206 => '错误的发件域。在此域名被 SUBMAIL 验证之前，你不能使用此域 $fromDomain 发送任何邮件'
        ,207 => '请将发件人名称（from_name）控制在50个字符以内'
        ,301 => '邮件标题不能为空。'
        ,302 => '将邮件标题控制在100个字符以内。'
        ,303 => '没有填写邮件内容。'
        ,901 => '你今日的发送配额已用尽。如需提高发送配额，请至 submail > 应用集成 >应用 页面开启更多发送配额'
        ,902 => '您的邮件发送许可已用尽或您的余额不支持本次的请求数量。如需继续发送，请至 submail.cn > 商店 页面购买更多发送许可后重试。'
        ,903 => '您的短信发送许可已用尽或您的余额不支持本次的请求数量。如需继续发送，请至 submail.cn > 商店 页面购买更多发送许可后重试。'
        ,904 => '您的账户余额已用尽或您的余额不支持本次的请求数量。如需继续充值，请至 submail.cn > 商店 页面购买更多发送许可后重试。'
        ,905 => '您的账户余额已用尽或您的余额不支持本次的请求数量。如需继续充值，请至 submail.cn > 商店 页面购买更多发送许可后重试。'
    ];


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
        $config = [
            'appid' => (int)$params['config']['AppId']
            ,'from' => $params['config']['systememail']
            ,'from_name' => $params['config']['fromname']
            ,'signature' => $params['config']['AppKey']
        ];

        $data = [
            'to' => $params['email']
            ,'subject' => $params['subject']
            ,'html' => $params['content']
        ];

        if(!empty($params['attachments']))
        {
            $attachments = explode(',', $params['attachments']);

            foreach($attachments as $k=>$attachment)
            {
                $file=CMF_ROOT."public/upload/common/email/".$attachment;
                if(file_exists($file)){
                  $data["attachments[{$k}]"] =curl_file_create($file);  
                }
                
            }
        }

        $result = $this ->curl(array_merge($config, $data), 'POST', ['json' => true]);

        if($result['status'] == 'error')
        {
            $result['msg'] = self::CODE[$result['code']] ?: $result['msg'];
        }
        return $result;
    }

    private function curl( $data="", $type="POST", $options=null)
    {
        $ch = curl_init();
        curl_setopt_array($ch , array(
            CURLOPT_URL             => 'https://api.mysubmail.com/mail/send',
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_POST            => 1 ,
            CURLOPT_POSTFIELDS      => $data,
            CURLOPT_SSL_VERIFYPEER      => false,
            CURLOPT_SSL_VERIFYHOST      => false,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);

       
    }

}