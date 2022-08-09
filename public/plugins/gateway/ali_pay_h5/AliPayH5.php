<?php
namespace gateway\ali_pay_h5;

use app\common\lib\Plugin;
use gateway\ali_pay_h5\pagepay\service\AlipayTradeService;
use gateway\ali_pay_h5\pagepay\buildermodel\AlipayTradePagePayContentBuilder;
use think\facade\Db;

class AliPayH5 extends Plugin
{
    public $info = array(
        'name'        => 'AliPayH5',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '支付宝网页支付',
        'description' => '支付宝网页支付',
        'status'      => 1,
        'author'      => '顺戴网络',
        'version'     => '1.0',
        'module'        => 'gateway'
    );

    public $hasAdmin = 0;//插件是否有后台管理界面

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

    public function aliPayH5Handle($param)
    {
        $config = $this->config();

        $data['currency'] = $config['currency']??'CNY';
        $data['body'] = isset($param['product'][0])?$param['product'][0]:'商品';
        $data['out_trade_no'] = $param['out_trade_no'];
        $data['subject'] =  $data['body'];
        $data['total_amount'] = $param['finance']['total'];
        $data['qr_pay_mode'] = 4; //> 二维码模式 可选
        $data['qrcode_width'] = $param['qrcode_width']??200; //> 二维码宽度 可选
        $data['passback_params'] = $data['body']; //> 组合数据

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = trim($data['out_trade_no']);
        //订单标题，必填
        $subject = trim($data['subject']);
        //付款金额，必填
        $total_amount = trim($data['total_amount']);
        //商品描述，可空
        $body = trim($data['body']);

        //构造参数
        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

        return $response; # redirect($response); # 直接跳转至支付界面
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
        $con = require dirname(__DIR__).'/ali_pay_h5/config/config.php';
        $config = array_merge($con,$config);

        return $config;
    }

}