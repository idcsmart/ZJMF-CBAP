<?php
namespace gateway\ali_pay_h5\controller;

use app\home\controller\BaseController;
use gateway\ali_pay_h5\AliPayH5;
use gateway\ali_pay_h5\pagepay\service\AlipayTradeService;

/**
 * Class IndexController.
 */
class IndexController extends BaseController
{

    /**
     * 异步回调
     */
    public function notify_handle()
    {
        $arr = $_POST;

        $AliPayH5 = new AliPayH5();
        $config = $AliPayH5->Config();

        //> 4.验证app_id是否为该商户本身，
        if ($arr['app_id'] != $config['app_id']) {
            echo 'fail';
            exit;
        }

        $alipaySevice = new AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);
        if ($result) {
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                $data = $arr;

                $up_data = [];
                $up_data['tmp_order_id'] = $data['out_trade_no']; //账单ID
                $up_data['amount'] = $data['total_amount'];//账单总价
                $up_data['trans_id'] = $data['trade_no'];//交易流水号
                $up_data['currency'] = $data['currency'] ?? 'CNY';//货币
                $up_data['paid_time'] = $data['gmt_payment'];//支付时间
                $up_data['gateway'] = 'AliPayH5';//支付网关名称

                order_pay_handle($up_data);

            }
            echo "success";    //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }

    }

    /**
     * 同步回调
     */
    public function return_handle()
    {
        $AliPayH5 = new AliPayH5();
        $config = $AliPayH5->Config();
        redirect($config['url']);
    }

}