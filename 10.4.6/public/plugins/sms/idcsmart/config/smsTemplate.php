<?php
return [
    [
        'title' => '验证码',
        'content' => '验证码@var(code),5分钟内有效！请勿泄漏于他人',
        'name'=>'code'
    ],
    [
        'title' => '用户登录',
        'content' => '您的账号@var(account)成功登录系统，如不是本人操作请及时修改密码',
        'name'=>'client_login_success'
    ],
    [
        'title' => '用户注册',
        'content' => '@var(account)，感谢您支持@var(system_website_name)',
        'name'=>'client_register_success'
    ],
    [
        'title'=> '客户更改手机',
        'content' => '您的手机号被改为：@var(client_phone)，请注意账户安全',
        'name'=>'client_change_phone'
    ],
    [
        'title' => '客户更改密码',
        'content' => '您的密码被改为：@var(client_password)，请注意账户安全',
        'name'=>'client_change_password'
    ],
    [
        'title' => '订单创建',
        'content' => '您已下单，订单：@var(order_id)（订单号），请及时支付',
        'name'=>'order_create'
    ],
    [
        'title' => '产品开通中',
        'content' => '您的产品：@var(product_name)（产品名称）正在开通，请耐心等待',
        'name'=>'host_pending'
    ],
    [
        'title' => '开通成功',
        'content' => '您的产品：@var(product_name)（产品名称），已开通可使用',
        'name'=>'host_active'
    ],
    [
        'title' => '产品暂停通知',
        'content' => '您的产品：@var(product_name)（产品名称），由于@var(product_suspend_reason)，已停用',
        'name'=>'host_suspend'
    ],
    [
        'title' => '产品解除暂停通知',
        'content' => '您的产品：@var(product_name)（产品名称），已解除暂停',
        'name'=>'host_unsuspend'
    ],
    [
        'title' => '产品删除通知',
        'content' => '您的产品：@var(product_name)（产品名称），由于到期未续费，已删除',
        'name'=>'host_terminate'
    ],
    [
        'title' => '产品升降级',
        'content' => '您已成功升级产品@var(product_name)，感谢您的支持',
        'name'=>'host_upgrad'
    ],
    [
        'title' => '第一次续费提醒',
        'content' => '您的产品：@var(product_name)（产品名称），还有@var(renewal_first)天到期，请及时续费',
        'name'=>'host_renewal_first'
    ],
    [
        'title' => '第二次续费提醒',
        'content' => '您的产品：@var(product_name)（产品名称），还有@var(renewal_second)天到期，请及时续费',
        'name'=>'host_renewal_second'
    ],
    [
        'title' => '逾期付款第一次提醒',
        'content' => '您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费',
        'name'=>'host_overdue_first'
    ],
    [
        'title'=>'逾期付款第二次提醒',
        'content'=> '您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费',
        'name'=>'host_overdue_second'
    ],
    [
        'title' => '逾期付款第三次提醒',
        'content' => '您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费',
        'name'=>'host_overdue_third'
    ],
    [
        'title' => '订单未付款通知',
        'content' => '您的订单：@var(order_id)（订单号）尚未支付，金额@var(order_amount)，请及时支付',
        'name'=>'order_overdue'
    ],
    [
        'title' => '订单金额修改',
        'content' => '您的订单：@var(order_id)（订单号）金额修改为@var(order_amount)，请及时支付',
        'name'=>'admin_order_amount'
    ],
    [
        'title' => '订单支付通知',
        'content' => '您的订单：@var(order_id)（订单号）支付成功，支付金额为：@var(order_amount)元',
        'name'=>'order_pay'
    ],
    [
        'title' => '充值成功通知',
        'content' => '充值成功，本次充值金额为：@var(order_amount)元',
        'name'=>'order_recharge'
    ],
    [
        'title' => '对象存储联通异常通知',
        'content' => '对象存储链接失败！请及时检查处理！',
        'name'=>'oss_exception_notice'
    ],
];