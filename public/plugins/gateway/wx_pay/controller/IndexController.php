<?php

namespace gateway\wx_pay\controller;

use app\home\controller\BaseController;
use gateway\wx_pay\lib\WxPayConfig;
use gateway\wx_pay\lib\WxPayApi;
use gateway\wx_pay\lib\WxPayOrderQuery;

require_once dirname(__DIR__) . '/lib/WxPayData.php';


class IndexController extends BaseController
{

    /**
     * 回调入口
     */
    public function notifyHandle()
    {
        $config = new WxPayConfig();
        $noyify = new NotifyController();
        $noyify->Handle($config, false);
        exit;
    }

    /**
     * 查询订单
     * @param $transaction_id
     * @return bool
     * @throws \plugins\wx_pay\lib\WxPayException
     */
    public function queryOrder($transaction_id)
    {

        trace('进入了queryOrder:','info');
        $transaction_id = $transaction_id??'test_product_2019111226';
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);

        $config = new \gateway\wx_pay\lib\WxPayConfig();
        trace('config:'.json_encode($config),'info');

        $result = WxPayApi::orderQuery($config, $input);
        trace("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return json(['msg'=>'已支付','status'=>200]);
        }
        return json(['msg'=>'未支付','status'=>400]);
    }

    /**
     * 通过商户订单号查询订单
     */
    public function queryMerchantOrder(Request $request)
    {
        $out_trade_no = $request->post('out_trade_no');
        if(empty($out_trade_no)){
            return json(['status'=>400,'msg'=>'error']);
        }
        try{
            $input = new WxPayOrderQuery();
            $input->SetOut_trade_no($out_trade_no);
            $config = new WxPayConfig();
            $res = WxPayApi::orderQuery($config, $input);
            if($res['trade_state'] == 'SUCCESS'){
                # TODO WYH 20201029
                //$out_trade_no = explode('a',$out_trade_no)[0];

                $subtotal = db('invoices')->where('id',$out_trade_no)->value('subtotal');
                if($res['total_fee'] == $subtotal){
                    return json(['type'=>'SUCCESS','msg'=>'已支付','status'=>200]);
                }else{
                    $data = ['subtotal'=>$subtotal,'total_fee'=>$res['total_fee'],'id'=>$out_trade_no];
                    trace('异常的支付:'.json_encode($data),'info');
                    hook('user_action_log');
                    return json(['type'=>'SUCCESS','msg'=>'异常的支付','status'=>200]);
                }
            }elseif($res['trade_state'] == 'USERPAYING'){
                return json(['type'=>'USERPAYING','msg'=>'支付中','status'=>200]);
            }elseif($res['trade_state'] == 'PAYERROR'){

                return json(['type'=>'PAYERROR','msg'=>'支付失败','status'=>200]);
            }elseif($res['trade_state'] == 'NOTPAY'){
                return json(['type'=>'NOTPAY','msg'=>'未支付','status'=>200]);
            }
        } catch(\Exception $e) {
            \Log::ERROR(json_encode($e));
        }
        return json(['status'=>400,'msg'=>'交易未完成']);
    }

}