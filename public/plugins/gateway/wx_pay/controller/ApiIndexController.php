<?php

namespace gateway\wx_pay\controller;


use cmf\controller\PluginRestBaseController;
use think\Db;

require_once PLUGINS_PATH . 'wx_pay/lib/WxPayApi.php';
require_once PLUGINS_PATH . 'wx_pay/lib/WxPayData.php';
require_once PLUGINS_PATH . 'wx_pay/lib/WxPayConfig.Interface.php';
class ApiIndexController extends PluginRestBaseController
{

    public function wxpayHandle()
    {
        /*
         * @param
         * ['product_name','product_id','openId','trade_no','trade_type','notify_url']
         * 前面两个为必传参数,openid如若不传就必须传递用户id信息(除非header头有传递XX-token,那就不需要,最后一个回调地址参数请自定义),
         */
        $param = $this->request->param();
        if(!array_key_exists('openId',$param))
        {
            if(!array_key_exists('uid',$param)){
                $param['openId'] = $this->userId;
            }else{
                $param['openId'] = $this->getOpenId($param['uId']);
            }
        }
        //获取订单号,如未设置获取默认自定义规则
        if(!array_key_exists('trade_no',$param))
        {
            $param['trade_no'] = rand(100,999).date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        }
        //获取支付类型默认是小程序内部发起支付
        if(!array_key_exists('trade_type',$param))
        {
            $param['trade_type'] = 'JSAPI';
        }
        $this->setPay($param);
    }
    //设置相关数据
    public function setPay($param)
    {
//        rand(100,999).date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT)
        $config = new \WxPayConfig();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($param['product_name']); //商品名称
        $input->SetOut_trade_no($param['trade_no']); //商品订单号
        $input->SetTotal_fee($param['payFee']);  //商品价格以分为初始单位
        $input->SetNotify_url($param['notify_url']); //回调地址
        $input->SetTrade_type($param['trade_type']);  //支付方式
        $input->SetProduct_id($param['product_id']); //商品自定义id
        $input->SetOpenid($param['openId']);   //openid
        $re = \WxPayApi::unifiedOrder($config, $input);
        $this->success('', $this->getJsApiParameters($re));
    }

    private function getJsApiParameters($UnifiedOrderResult)
    {    //判断是否统一下单返回了prepay_id
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
            throw new \WxPayException("参数错误");
        }
        $jsapi = new \WxPayJsApiPay();
        $config = new \WxPayConfig();
        $jsapi->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(\WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign($config));
        $parameters = json_encode($jsapi->GetValues());
        return $parameters;
    }

    //获取open Id
    public function getOpenId($uId)
    {
        $re = Db::name('third_party_user')->where('user_id',$uId)->value('openid');
        return $re;
    }

}