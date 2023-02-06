<?php
namespace app\home\controller;

/**
 * title 钩子文档
 * description 接口说明：这这里编写添加的钩子文档(hook 名和hook中参数)
 */
class HooksController
{
    /*               前台钩子             */
    /**
     * @title 订单支付后执行
     * @author wyh
     * @time 2022-06-01
     * @url order_paid
     * @param int id 1 订单ID required
     * @return bool
     */
    public function order_paid(){}

    /**
     * @title 注册后
     * @author wyh
     * @time 2022-08-02
     * @url after_register
     * @param int id 1 客户ID required
     * @return bool
     */
    public function after_register(){}

    /**
     * 时间 2022-07-18
     * @title 订单生成前
     * @url before_order_create
     * @method  
     * @author wyh
     * @version v1
     * @param   int client_id - 用户ID
     */
    public function before_order_create(){}

    /**
     * @title 产品退款
     * @author wyh
     * @time 2022-07-28
     * @url host_refund
     * @param int id 1 产品ID required
     * @return bool
     */
    public function host_refund(){}

    /**
     * @title 优惠码应用
     * @desc 优惠码应用,多个插件都会执行
     * @author wyh
     * @time 2022-06-09
     * @url client_promo_code
     * @param array promo_code ["fKwUIZ91","nG0aWo55"] 优惠码,数组格式 required
     * @param int client_id 1 用户ID required
     * @param int host_id 1 产品ID
     * @param int product_id 1 商品ID
     * @param string scene New 优惠码应用场景:Renew续费,New新购,Upgrade升降级 required
     * @param float amount 1.00 优惠前金额 required
     * @param float total 1.00 优惠前总金额(可选参数)
     * @param int billing_cycle_time 16238423473 周期对应时间(时间戳) required
     * @return int status 200/400 状态码:200应用优惠码成功,400应用优惠码失败
     * @return string msg 应用优惠码成功 返回信息,成功失败都会有此值
     * @return float data.discount 优惠金额
     * @return array data.order_items 订单子项
     * @return int data.order_items.host_id 产品ID
     * @return string data.order_items.type 优惠码表名(除前缀)
     * @return int data.order_items.rel_id 优惠码ID
     * @return float data.order_items.amount 折扣金额
     * @return string data.order_items.description 描述
     */
    public function client_promo_code(){}

    /*               后台钩子             */

    /**
     * 时间 2022-06-15
     * @title 接口删除后调用
     * @url after_server_delete
     * @method  
     * @author hh
     * @version v1
     * @param   int id - 接口ID
     */
    public function after_server_delete(){}


    /**
     * 时间 2022-07-18
     * @title 订单生成后
     * @url after_order_create
     * @method  
     * @author wyh
     * @version v1
     * @param   int id - 订单ID
     */
    public function after_order_create(){}

    /**
     * 时间 2022-06-17
     * @title 商品删除后调用
     * @url after_product_delete
     * @method
     * @author hh
     * @version v1
     * @param   int id - 商品ID
     */
    public function after_product_delete(){}

    /**
     * 时间 2022-07-20
     * @title 每日定时任务钩子
     * @url daily_cron
     * @method
     * @author wyh
     * @version v1
     */
    public function daily_cron(){}
	//定时任务 每分钟执行一次hook
    public function minute_cron(){}

    /*               前台模板钩子             */
    
    public function template_client_after_host_list_button(){}

    public function template_client_host_list_on_table_header(){}

    public function template_client_promo_code(){}

    public function template_client_footer(){}

    /**
     * 时间 2022-07-07
     * @title 产品内页钩子
     * @url template_after_servicedetail_suspended
     * @method
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID
     */
    public function template_after_servicedetail_suspended(){}

    /*               后台模板钩子             */

    public function template_admin_footer(){}

    public function template_admin_after_host_list_button(){}

    /**
     * @title 队列执行hook
     */
    public function task_run(){}

    /**
     * 时间 2022-10-24
     * @title 订单取消前
     * @url before_order_cancel
     * @author hh
     * @version v1
     * @param int id - 订单ID
     */
    public function before_order_cancel(){}

    /**
     * 时间 2023-01-04
     * @title 获取用户详情后
     * @url before_order_cancel
     * @author theworld
     * @version v1
     * @param int id - 用户ID
     */
    public function after_client_index(){}
}