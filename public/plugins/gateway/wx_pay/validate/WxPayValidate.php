<?php


namespace gateway\wx_pay\validate;

use think\Validate;

class WxPayValidate extends Validate{

    protected $rule = [
        'body' => 'chsDash|length:2,50',
        'product_id' => 'number|length:3,15',
        'out_trade_no|订单号' => 'alphaDash|length:2,30',
        'total_fee' => 'float|length:1,11',
//        'trade_type' => 'alpha|require|max:6',
        'attach' => 'graph|length:1,100',
    ];
    protected $message = [

    ];



}
