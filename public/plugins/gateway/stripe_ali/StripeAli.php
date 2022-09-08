<?php
namespace gateway\stripe_ali;

use app\common\lib\Plugin;
use think\facade\Db;

class StripeAli extends Plugin
{
    public $info = array(
        'name'        => 'StripeAli',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '(stripe)支付宝支付插件',
        'description' => '(stripe)支付宝支付插件',
        'author'      => 'idcsmart',
        'version'     => '1.0',  // 必填 插件版本
        'help_url'    => '', // 选填 申请链接
        'author_url'  => '', // 选填 作者链接
        'url'         => '', // 选填 图标地址(可以自定义支付图片地址)
    );

    // 临时订单生成规则,1:毫秒时间戳+8位随机数(21-22位长度订单号,默认规则),2:时间戳+8位随机数(18位长度订单号),3:10位随机数(10位长度订单号)
    public $orderRule=1;

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function stripeAliHandle($param)
    {
        if ($param['finance']['total']<4 || $param['finance']['total']>999999.99){
            return ['status'=>400,'msg'=>'金额需大于4且小于999999.99'];
        }

        $config = $this->config();
        if(!file_exists($config['credit_pay_key'])){
            file_put_contents($config['credit_pay_key'],time());
        }
        $key=file_get_contents($config['credit_pay_key']);
        $param['pay_type']='stripe_ali';
        $data=bin2hex(openssl_encrypt(serialize($param),'aes-128-cbc',  mb_substr(md5($key),8,16) , true, mb_substr(md5("798yyhhhjio^*(uihkjh"),8,16)));

        $notify_url = "https://{$_SERVER['HTTP_HOST']}/gateway?_plugin=stripe_ali&_controller=Index&_action=index&token={$data}";

        require_once(dirname(__DIR__) . "/stripe_ali/stripe-php/init.php");
        //订单标题，必填
        $subject        = isset($param['product'][0])?$param['product'][0]:'商品';
        //付款金额，必填
        $amount   = trim($param['finance']['total']) * 100;

        $stripe = new \Stripe\StripeClient($config['sk']);
        $result = $stripe->sources->create([
            "type" => "alipay",
            "currency" => $config['currency'] ? $config['currency'] : 'hkd',
            "amount" => $amount,
            'redirect' => [
                'return_url' =>  $notify_url,
            ],
            "metadata" => [
                'token'=>$data
            ],
            "owner" => [
                "name" => $subject ?? '..',
            ]
        ]);

        $url = $result->redirect->url;
        $html = "<a href='$url' target='_blank'>去支付</a>";

        return $html;
    }

    // 获取配置
    public function config()
    {
        $config = Db::name('plugin')->where('name', $this->info['name'])->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = [];
        }
        $con = require dirname(__DIR__).'/stripe_ali/config/config.php';
        $config = array_merge($con,$config);

        return $config;
    }

}