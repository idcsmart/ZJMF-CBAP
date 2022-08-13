<?php
namespace gateway\paypal;

use app\common\lib\Plugin;
use \PayPal\Auth\OAuthTokenCredential;
use \PayPal\Rest\ApiContext;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Payment;
use \PayPal\Api\Payer;
use \PayPal\Api\Amount;
use \PayPal\Api\Transaction;
use \PayPal\Api\RedirectUrls;
use think\facade\Db;
require 'paypal/autoload.php';

/**
 * @desc Paypal支付
 * @author wyh
 * @version 1.0
 * @time 2022-07-13
 */
class Paypal extends Plugin
{
    public $info = array(
        'name'        => 'Paypal',
        'title'       => 'Paypal支付',
        'description' => 'Paypal支付,不支持人民币CNY,注意使用',
        'author'      => 'idcsmart',
        'version'     => '1.0',  // 必填 插件版本
        'help_url'    => '', // 选填 申请链接
        'author_url'  => '', // 选填 作者链接
        'url'         => '', // 选填 图标地址(可以自定义支付图片地址)
    );

    // 临时订单生成规则,1:毫秒时间戳+8位随机数(21-22位长度订单号,默认规则),2:时间戳+8位随机数(18位长度订单号),3:10位随机数(10位长度订单号)
    public $orderRule=1;

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    public function paypalHandle($param)
    {
        $data['currency'] = 'HKD'; # 不可使用CNY
        $data['body'] = isset($param['product'][0])?$param['product'][0]:'商品';
        $data['out_trade_no'] = $param['out_trade_no'];
        $data['subject'] = $data['body'];
        $data['total_amount'] = $param['finance']['total'];

        $config = $this->config();
        $currency = $data['currency'];
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = trim($data['out_trade_no']);
        //订单标题，必填
        $subject = trim($data['subject']);
        //付款金额，必填
        $total_amount = trim($data['total_amount']);
        //商品描述，可空
        $body = trim($data['body']);

        $apiContext = new ApiContext(new OAuthTokenCredential($config['clientId'],$config['clientSecret']));
        $apiContext->setConfig(array('mode' => $config['mode'])); # 生产环境
        # 支付
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        # 设置支付项
        $item1 = new Item();
        $item1->setName($body)
            ->setCurrency($currency)
            ->setQuantity(1)
            ->setSku($out_trade_no) // Similar to `item_number` in Classic API  物品号
            ->setPrice($total_amount);
        $itemList = new ItemList();
        $itemList->setItems(array($item1));
        # 金额
        $amount = new Amount();
        $amount->setTotal($total_amount);
        $amount->setCurrency($currency);
        # 交易
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($subject)
            ->setInvoiceNumber($out_trade_no);
        # 设置回调地址/取消支付地址
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($config['notify_url'])
            ->setCancelUrl($config['cancel_url']);
        # 创建支付链接
        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));
        try {
            $payment->create($apiContext);
            $approvalUrl = $payment->getApprovalLink();

        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            if (json_decode($ex->getData(),true)['message']) {
                throw new \Exception(json_decode($ex->getData(),true)['message']);
            }elseif (json_decode($ex->getData(),true)['error']) {
                throw new \Exception(json_decode($ex->getData(),true)['error']);
            }else{
                throw new \Exception('未知错误');
            }
        }

        $html = "<a href='$approvalUrl' target='_blank'>去支付</a>";
        return $html;
        header("location:{$approvalUrl}");die;
        return redirect($approvalUrl); # 直接重定向至Paypal支付页面
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
        $con = require dirname(__DIR__).'/paypal/config/config.php';
        $config = array_merge($con,$config);

        return $config;
    }

}