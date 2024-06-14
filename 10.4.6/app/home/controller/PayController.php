<?php
namespace app\home\controller;

use app\common\model\OrderTmpModel;

/**
 * @title 支付管理
 * @desc 支付管理
 * @use app\home\controller\PayController
 */
class PayController extends HomeBaseController
{
    /**
     * 时间 2022-05-24
     * @title 支付
     * @desc 支付
     * @author wyh
     * @version v1
     * @url /console/v1/pay
     * @method  post
     * @param int id 1 订单ID
     * @param string gateway WxPay 支付方式,支付插件标识
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string code - 当status==200,且code==Paid时,表示支付完成;Unpaid表示部分余额支付,需要将返回的data.html数据渲染出来
     * @return string data.html - 三方接口返回内容
     */
    public function pay()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->pay($param);

        return json($result);
    }

    /**
     * 时间 2022-05-24
     * @title 支付状态
     * @desc 支付状态(支付后,轮询调此接口,状态返回400时,停止调用;状态返回200且code==Paid时,停止调用)
     * @author wyh
     * @version v1
     * @url /console/v1/pay/:id/status
     * @method  get
     * @param int id 1 订单ID
     * @return array
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string code - Paid表示支付成功,停止调用接口;Upaid表示支付失败,持续调用
     */
    public function status()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->status($param);

        return json($result);
    }

    /**
     * 时间 2022-05-24
     * @title 充值
     * @desc 充值
     * @author wyh
     * @version v1
     * @url /console/v1/recharge
     * @method  post
     * @param float amount 1.00 金额
     * @param string gateway WxPay 支付方式
     */
    public function recharge()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->recharge($param);

        return json($result);
    }

    /**
     * 时间 2022-05-28
     * @title 使用(取消)余额
     * @desc 使用(取消)余额
     * @author wyh
     * @version v1
     * @url /console/v1/credit
     * @method  post
     * @param int id 1 订单ID
     * @param int use 1 1使用余额,0取消使用
     * @return array
     * @return int status - 状态码,200成功,400失败,报错:已使用过余额时.如果要重新使用,先取消余额,再使用
     * @return string msg - 提示信息
     * @return int data.id - 订单ID
     */
    public function credit()
    {
        $param = $this->request->param();

        $OrderTmpModel = new OrderTmpModel();

        $result = $OrderTmpModel->credit($param);

        return json($result);
    }
}