<?php
namespace gateways\global_ali_pay\controller;

use app\home\controller\BaseController;
use gateway\global_ali_pay\GlobalAliPay;
use gateway\global_ali_pay\lib\AlipayNotify;

class IndexController extends BaseController
{
    protected $gatewaymodule = "global_ali_pay";

    public function notify_handle()
    {
        $class = new GlobalAliPay();
        $config = $class->Config();
        $alipayNotify  = new AlipayNotify($config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功 verification is succeeded

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            //> 逻辑处理

            $data = $_POST;

            $newData = array(
                'tmp_order_id'   => $data['out_trade_no'],
                'gateway'      => 'GlobalAliPay',
                'paid_time'    => $data['notify_time'],
                'trans_id'     => $data['trade_no'], //> 平台交易id
                'amount'    => $data['total_fee'], //> 订单金额
                'currency'     => $data['currency'],
            );

            order_pay_handle($newData);

            //Please write program according to your business logic.(The above code is only for reference.)
            echo "success";        //请不要修改或删除 do not modify or delete
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            trace('支付宝支付失败', 'info');

            echo "fail";

        }
    }

    /**
     * 同步回调
     */
    public function return_handle()
    {
        $class = new GlobalAliPay();
        $config = $class->Config();
        return redirect($config['url']);
    }


    /**
     * 验证签名
     */
    function aliCheck($params)
    {
        return true;
    }

}