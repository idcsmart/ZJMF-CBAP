<?php
namespace gateway\epusdt\controller;

use app\home\controller\BaseController;
use gateway\epusdt\Epusdt;

/**
 * @desc Easy Payment Usdt
 * @author wyh
 * @version 1.0
 * @time 2022-07-22
 */
class IndexController extends BaseController
{
    # 异步回调
    public function notifyHandle()
    {
        $param = $this->request->param();

        # 验证签名
        $Epusdt = new Epusdt();
        $config = $Epusdt->Config();

        $signData = [
            'trade_id' => $param['trade_id'],
            'order_id' => $param['order_id'],
            'amount' => $param['amount'],
            'actual_amount' => $param['actual_amount'],
            'token' => $param['token'],
            'block_transaction_id' => $param['block_transaction_id'],
            'signature' => $param['signature'],
            'status' => $param['status'],
        ];

        $signature = $Epusdt->epusdtSign($signData,$config['api_auth_token']);

        if ($param['signature'] != $signature){
            return 'fail';
        }

        $this->orderHandle($param);

        return 'ok';

    }

    //> 支付成功 回调处理订单
    private function orderHandle($data)
    {
        $up_data = [];
        $up_data['tmp_order_id'] = $data['order_id'];   // 订单ID
        $up_data['amount'] = $data['amount'];         // 订单总价
        $up_data['trans_id'] = $data['trade_id'];           // 交易流水号
        $up_data['currency'] = $data['currency'] ?? 'CNY';  // 货币
        $up_data['paid_time'] = date('Y-m-d H:i:s');       // 支付时间,
        $up_data['gateway'] = 'Epusdt';                  // 支付网关名称
        order_pay_handle($up_data);
    }

    public function returnHandle()
    {
        $Epusdt = new Epusdt();
        $config = $Epusdt->Config();

        return redirect($config['url']);#->send();
        # header("location:{$config['url']}");
    }

}