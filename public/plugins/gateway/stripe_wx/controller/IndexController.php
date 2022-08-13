<?php
namespace gateway\stripe_wx\controller;

use app\home\controller\BaseController;
use gateway\stripe_wx\StripeWx;

require_once(dirname(__DIR__) . "/stripe-php/init.php");

class IndexController extends BaseController
{
    /**
     * 异步回调
     */
    public function notifyHandle()
    {
        $payment = 'StripeWx';

        $StripeAli = new StripeWx();
        $config = $StripeAli->config();

        \Stripe\Stripe::setApiKey($config['sk']);  //私钥
        $endpoint_secret = $config['webhook']; //webhook私钥

        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
        $event = null;
        try {

            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            $data = $event->data->object;

            if ($event->type == 'charge.succeeded') {
                //succeeded 成功
                $metadataArr = $event->data->object->source->metadata;
                $up_data = [];
                $up_data['tmp_order_id'] = $metadataArr->out_trade_no; //账单ID
                $up_data['amount'] = $metadataArr->total_fee; //账单总价
                $up_data['trans_id'] = $data->id; //交易流水号
                $up_data['currency'] = $metadataArr->fee_type; //货币
                $up_data['paid_time'] = date('Y-m-d H:i:s'); //支付时间
                $up_data['gateway'] = $payment; //支付网关名称
                //> 支付成功 回调处理订单
                order_pay_handle($up_data);
            } elseif ($event->type == 'charge.pending') {
                trace('回调 审核中');
                //pending 审核
            } elseif ($event->type == 'charge.refunded') {
                //refunded 退款
                trace('回调 退款中');
            } elseif ($event->type == 'charge.failed') {
                //failed 失败，（信用卡验证失败也会发该请求）
                trace('回调 支付失败');
            } elseif ($event->type == 'source.chargeable') {
                trace('回调 source.chargeable');
                $source_id = $data->id;
                \Stripe\Charge::create([
                    'amount' => $data->amount,
                    'currency' => 'hkd',
                    'source' => $source_id,
                ]);
                //逻辑
            }
        } catch (\Exception $e) {
            trace($e->getMessage());
        } catch (\UnexpectedValueException $e) {
            http_response_code(400); // Invalid payload
            exit();
        }

        http_response_code(200);
        $urlStr = $_SERVER['SERVER_NAME'] . $config['url'];
        echo "<script> window.location = '" . $urlStr . "' </script>";
        exit;
    }

    /**
     * 同步回调
     */
    public function returnHandle()
    {
        $StripeAli = new StripeWx();
        $config = $StripeAli->config();
        return redirect($config['url']);
    }
}
