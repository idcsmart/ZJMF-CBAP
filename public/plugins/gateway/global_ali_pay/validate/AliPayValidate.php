<?php


namespace gateway\global_ali_pay\validate;

use think\Validate;

class AliPayValidate extends Validate{

    protected $rule = [
//        'product_name' => 'chsDash|length:2,50',
        'out_trade_no|订单号' => 'alphaDash|length:2,30',
        'total_fee' => 'float|length:1,11',
        'qrcode_width|尺寸'  =>  'length:100,500',
        'currency'  =>  'upper|length:3'
    ];
    protected $message = [
            'total_fee.integer' =>  '请输入正确的金额',
//            'product_name' =>  '请输入正确的字符',
            'currency'  =>  '请输入正确的货币',
    ];



}
