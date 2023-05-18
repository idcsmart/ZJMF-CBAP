<?php
namespace gateway\ali_pay_dmf\controller;

use app\home\controller\BaseController;
use gateway\ali_pay_dmf\f2fpay\service\AlipayTradeService;
use gateway\ali_pay_dmf\AliPayDmf;

/**
 * @desc 支付宝当面付支付插件回调处理文件
 * @author wyh
 * @version 1.0
 * @time 2022-05-27
 */
class IndexController extends BaseController
{
    # 异步回调
    public function notifyHandle()
    {
        $arr = $_POST;

        $AliPayDmf = new AliPayDmf();
        $configInDb = $AliPayDmf->getConfig();
        $config = require dirname(__DIR__).'/config/config.php';
        $config = array_merge($config,$configInDb);

        //> 校验
        //> 1.商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        //> 2.判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        //> 3.验证app_id是否为该商户本身，
        if ($arr['app_id'] != $config['app_id']) {
            echo 'fail';
            exit;
        }
        $alipaySevice = new AlipayTradeService($config);
        #$alipaySevice->writeLog(var_export($_POST, true));
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

                //> 逻辑处理
                $this->orderHandle($_POST);
            }
            echo "success";
        } else {

            echo "fail";
        }

    }

    //> 支付成功 回调处理订单
    private function orderHandle($data)
    {
        $up_data = [];
        $up_data['tmp_order_id'] = $data['out_trade_no'];   // 订单ID
        $up_data['amount'] = $data['total_amount'];         // 订单总价
        $up_data['trans_id'] = $data['trade_no'];           // 交易流水号
        $up_data['currency'] = $data['currency'] ?? 'CNY';  // 货币
        $up_data['paid_time'] = $data['gmt_payment'];       // 支付时间
        $up_data['gateway'] = 'AliPayDmf';                  // 支付网关名称
        order_pay_handle($up_data);
    }

}