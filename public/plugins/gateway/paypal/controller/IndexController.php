<?php
namespace gateway\paypal\controller;

use app\home\controller\BaseController;
use \PayPal\Auth\OAuthTokenCredential;
use \PayPal\Rest\ApiContext;
use \PayPal\Api\Payment;
use \PayPal\Api\PaymentExecution;
use gateway\paypal\Paypal;

class IndexController extends BaseController
{
    /**
     * 异步回调
     */
    public function notify_handle()
    {
        $arr = $this->request->param();
        if(!isset($arr['token'],$arr['paymentId'], $arr['PayerID'])){
            die('fail');
        }
        $paymentID = $arr['paymentId'];
        $payerId = $arr['PayerID'];

        $PayPal = new Paypal();
        $config = $PayPal->config();
        $paypal = new ApiContext(new OAuthTokenCredential($config['clientId'],$config['clientSecret']));
        $paypal->setConfig(array('mode' => $config['mode']));
        $payment = Payment::get($paymentID, $paypal);

        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);
        try{
            $result = $payment->execute($execute, $paypal);
            if ($result && isset ( $result->state ) && $result->state == 'approved') {
                $data = json_decode($result,true)['transactions'][0];
                $up_data = [];
                $up_data['tmp_order_id'] = $data['invoice_number']; // 订单ID
                $up_data['amount'] = $data['amount']['total']??0; // 总价
                $up_data['trans_id'] = $data['related_resources'][0]['sale']['id']??''; // 交易流水号
                $up_data['currency'] = $data['amount']['currency'] ?? 'HKD';// 货币
                $up_data['paid_time'] = $data['related_resources'][0]['sale']['create_time']??date('Y-m-d H:i:s'); // 支付时间
                $up_data['gateway'] = $PayPal->info['name']; // 支付网关名称
                //> 支付成功 回调处理订单
                order_pay_handle($up_data);
                return redirect($config['url']);
            }else{
                echo "fail";
            }
        }catch(\Exception $e){
            die($e);
        }
        return redirect($config['url']);
    }

    /**
     * 同步回调
     */
    public function return_handle()
    {
        $PayPal = new Paypal();
        $config = $PayPal->config();
        return redirect($config['url']);
    }

    public function cancel_handle()
    {
        //echo "取消支付";
        $PayPal = new Paypal();
        $config = $PayPal->config();
        return redirect($config['url']);
    }
}