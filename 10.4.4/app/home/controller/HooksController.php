<?php
namespace app\home\controller;

/**
 * @title hook钩子文档
 * @desc 接口说明：这里编写添加的钩子文档(hook名和hook中参数)
 * @use app\home\controller\HooksController
 */
class HooksController
{
    /*               前台钩子             */
    /**
     * @title 订单支付后执行
     * @desc 订单支付后执行
     * @author wyh
     * @time 2022-06-01
     * @url order_paid，通过实现插件主文件方法orderPaid($param)或者hooks.php文件实现add_hook('order_paid',function(){})，其他所有钩子类似
     * @param int id 1 订单ID required
     * @return bool
     */
    public function order_paid(){}

    /**
     * @title 注册后
     * @desc 注册后
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
     * @desc 订单生成前
     * @url before_order_create
     * @method
     * @author wyh
     * @version v1
     * @param   int client_id - 用户ID
     */
    public function before_order_create(){}

    /**
     * @title 产品退款
     * @desc 产品退款
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
     * @desc 接口删除后调用
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
     * @desc 订单生成后
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
     * @desc 商品删除后调用
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
     * @desc 每日定时任务钩子
     * @url daily_cron
     * @method
     * @author wyh
     * @version v1
     */
    public function daily_cron(){}

    /**
     * 时间 2022-07-20
     * @title 定时任务 每分钟执行一次hook
     * @desc 定时任务 每分钟执行一次hook
     * @url minute_cron
     * @method
     * @author wyh
     * @version v1
     */
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
     * 时间 2022-07-20
     * @title 任务队列执行hook
     * @desc 任务队列执行hook
     * @url minute_cron
     * @method
     * @author wyh
     * @version v1
     */
    public function task_run(){}

    /**
     * 时间 2022-10-24
     * @title 订单取消前
     * @desc 订单取消前
     * @url before_order_cancel
     * @author hh
     * @version v1
     * @param int id - 订单ID
     */
    public function before_order_cancel(){}

    /**
     * 时间 2023-01-04
     * @title 获取用户详情后
     * @desc 获取用户详情后
     * @url before_order_cancel
     * @author theworld
     * @version v1
     * @param int id - 用户ID
     */
    public function after_client_index(){}

    /**
     * 时间 2024-02-18
     * @title 添加接口分组后
     * @desc 添加接口分组后
     * @url after_server_group_create
     * @author wyh
     * @version v1
     * @param int id - 接口分组ID
     * @param object customfield - 自定义字段{"key":"value"}
     */
    public function after_server_group_create(){}

    /**
     * 时间 2024-02-18
     * @title 编辑接口分组后
     * @desc 编辑接口分组后
     * @url after_server_group_edit
     * @author wyh
     * @version v1
     * @param int id - 接口分组ID
     * @param object customfield - 自定义字段{"key":"value"}
     */
    public function after_server_group_edit(){}

    /**
     * 时间 2024-02-18
     * @title 删除接口分组后
     * @desc 删除接口分组后
     * @url after_server_group_delete
     * @author wyh
     * @version v1
     * @param int id - 接口分组ID
     */
    public function after_server_group_delete(){}

    /**
     * 时间 2024-02-18
     * @title 插件安装后
     * @desc 插件安装后
     * @url after_plugin_install
     * @author wyh
     * @version v1
     * @param string name - 插件标识
     * @param object customfield - 自定义字段{"key":"value"}
     */
    public function after_plugin_install(){}

    /**
     * 时间 2024-02-18
     * @title 插件卸载后
     * @desc 插件卸载后
     * @url after_plugin_uninstall
     * @author wyh
     * @version v1
     * @param string name - 插件标识
     */
    public function after_plugin_uninstall(){}

    /**
     * 时间 2024-02-18
     * @title 插件升级后
     * @desc 插件升级后
     * @url after_plugin_upgrade
     * @author wyh
     * @version v1
     * @param string name - 插件标识
     */
    public function after_plugin_upgrade(){}

    /**
     * 时间 2024-02-18
     * @title 应用优惠码
     * @desc 应用优惠码
     * @url apply_promo_code
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID
     * @param float price - 金额
     * @param string scene - 场景
     * @param int duration - 周期时间
     * @return int status - 状态码200
     * @return string msg - 返回消息
     * @return array data -
     * @return boolean data.loop - 是否循环优惠
     * @return float data.discount - 优惠金额
     */
    public function apply_promo_code(){}

    /**
     * 时间 2024-02-18
     * @title 应用客户等级
     * @desc 应用客户等级
     * @url client_discount_by_amount
     * @author wyh
     * @version v1
     * @param int client_id - 客户ID
     * @param int product_id - 商品ID
     * @param float amount - 金额
     * @return int status - 状态码200
     * @return string msg - 返回消息
     * @return array data -
     * @return float data.discount - 优惠金额
     */
    public function client_discount_by_amount(){}

    /**
     * 时间 2024-02-18
     * @title 删除产品续费日志
     * @desc 删除产品续费日志
     * @url delete_renew_log
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID
     */
    public function delete_renew_log(){}

    /**
     * 时间 2024-02-18
     * @title 创建商品分组后
     * @desc 创建商品分组后
     * @url after_product_group_create
     * @author wyh
     * @version v1
     * @param int id - 商品分组ID
     * @param object customfield - 自定义字段
     */
    public function after_product_group_create(){}

    /**
     * 时间 2024-02-18
     * @title 编辑商品分组后
     * @desc 编辑商品分组后
     * @url after_product_group_edit
     * @author wyh
     * @version v1
     * @param int id - 商品分组ID
     * @param object customfield - 自定义字段
     */
    public function after_product_group_edit(){}

    /**
     * 时间 2024-02-18
     * @title 删除商品分组后
     * @desc 删除商品分组后
     * @url after_product_group_delete
     * @author wyh
     * @version v1
     * @param int id - 商品分组ID
     */
    public function after_product_group_delete(){}

    /**
     * 时间 2024-02-18
     * @title 在后台用户详情追加输出
     * @desc 在后台用户详情追加输出
     * @url admin_client_index
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @return array data - 追加数组
     */
    public function admin_client_index(){}

    /**
     * 时间 2024-02-18
     * @title 用户创建后（后台）
     * @desc 用户创建后（后台）
     * @url after_client_register
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param object customfield - 自定义字段
     */
    public function after_client_register(){}

    /**
     * 时间 2024-02-18
     * @title 修改用户前（后台）
     * @desc 修改用户前（后台）
     * @url before_client_edit
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param object customfield - 自定义字段
     */
    public function before_client_edit(){}

    /**
     * 时间 2024-02-18
     * @title 编辑用户后（后台）
     * @desc 编辑用户后（后台）
     * @url after_client_edit
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param object customfield - 自定义字段
     */
    public function after_client_edit(){}

    /**
     * 时间 2024-02-18
     * @title 删除用户后（后台）
     * @desc 删除用户后（后台）
     * @url after_client_edit
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     */
    public function after_client_delete(){}

    /**
     * 时间 2024-02-18
     * @title 搜索用户前（后台）
     * @desc 搜索用户前（后台）
     * @url before_search_client
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @return array [] -
     * @return int [].client_id - 客户ID
     */
    public function before_search_client(){}

    /**
     * 时间 2024-02-18
     * @title 客户注册前
     * @desc 客户注册前
     * @url before_client_register
     * @author wyh
     * @version v1
     * @param string type phone 登录类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(登录类型为手机注册时需要传此参数)
     * @param string username wyh 姓名
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     * @return int status - 状态码200成功，400失败
     * @return string msg - 信息
     * @return array data - 自定义返回数据
     */
    public function before_client_register(){}

    /**
     * 时间 2024-02-18
     * @title 客户退出登录后
     * @desc 客户退出登录后
     * @url after_client_logout
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param object customfield - 自定义字段
     */
    public function after_client_logout(){}

    /**
     * 时间 2024-02-18
     * @title 客户登录后
     * @desc 客户登录后
     * @url after_client_login
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param object customfield - 自定义字段
     */
    public function after_client_login(){}

    /**
     * 时间 2024-02-18
     * @title 客户重置密码后
     * @desc 客户重置密码后
     * @url after_client_password_reset
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param object customfield - 自定义字段
     */
    public function after_client_password_reset(){}

    /**
     * 时间 2024-02-18
     * @title API鉴权登录完成
     * @desc API鉴权登录完成
     * @url client_api_login
     * @author wyh
     * @version v1
     * @param int id - 客户ID
     * @param string username - 用户名(用户注册时的邮箱或手机号)
     * @param string password - 密码(api信息的token)
     */
    public function client_api_login(){}

    /**
     * 时间 2024-02-18
     * @title 获取子账户父ID
     * @desc 获取子账户父ID
     * @url get_client_parent_id
     * @author wyh
     * @version v1
     * @param int client_id - 客户ID
     * @return int
     */
    public function get_client_parent_id(){}

    /**
     * 时间 2024-02-18
     * @title 检查客户是否实名认证
     * @desc 检查客户是否实名认证
     * @url check_certification
     * @author wyh
     * @version v1
     * @param int client_id - 客户ID
     * @return boolean
     */
    public function check_certification(){}

    /**
     * 时间 2024-02-18
     * @title 是否开启未认证无法充值功能
     * @desc 是否开启未认证无法充值功能
     * @url check_certification
     * @author wyh
     * @version v1
     * @return boolean
     */
    public function check_certification_recharge(){}

    /**
     * 时间 2024-02-18
     * @title 更新个人认证信息(需要安装实名认证插件)
     * @desc 更新个人认证信息(需要安装实名认证插件)
     * @url update_certification_person
     * @author wyh
     * @version v1
     * @param int client_id - 客户ID
     * @param int status - 实名认证状态：1已认证，2未通过，3待审核，4已提交资料
     * @param string auth_fail - 失败原因
     */
    public function update_certification_person(){}

    /**
     * 时间 2024-02-18
     * @title 更新企业认证信息(需要安装实名认证插件)
     * @desc 更新企业认证信息(需要安装实名认证插件)
     * @url update_certification_company
     * @author wyh
     * @version v1
     * @param int client_id - 客户ID
     * @param int status - 实名认证状态：1已认证，2未通过，3待审核，4已提交资料
     * @param string auth_fail - 失败原因
     */
    public function update_certification_company(){}

    /**
     * 时间 2024-02-18
     * @title 每五分钟定时任务钩子
     * @desc 每五分钟定时任务钩子
     * @url daily_cron
     * @method
     * @author wyh
     * @version v1
     */
    public function five_minute_cron(){}

    /**
     * 时间 2024-02-18
     * @title 产品第一次续费提醒前
     * @desc 产品第一次续费提醒前
     * @url before_host_renewal_first
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     * @param int client_id - 客户ID
     */
    public function before_host_renewal_first(){}

    /**
     * 时间 2024-02-18
     * @title 删除未支付升降级订单前
     * @desc 删除未支付升降级订单前
     * @url before_delete_host_unpaid_upgrade_order
     * @author wyh
     * @version v1
     * @param array id - 订单ID数组
     */
    public function before_delete_host_unpaid_upgrade_order(){}

    /**
     * 时间 2024-02-18
     * @title 删除未支付续费订单前
     * @desc 删除未支付续费订单前
     * @url before_delete_unpaid_renew_order
     * @author wyh
     * @version v1
     * @param array id - 订单ID数组
     */
    public function before_delete_unpaid_renew_order(){}

    /**
     * 时间 2024-02-18
     * @title 获取客户产品ID
     * @desc 获取客户产品ID
     * @url get_client_host_id
     * @author wyh
     * @version v1
     * @param int client_id - 客户ID
     * @return int status - 状态码200成功，400失败
     * @return string msg - 信息
     * @return array data -
     * @return array data.host - 产品ID数组
     */
    public function get_client_host_id(){}

    /**
     * 时间 2024-02-18
     * @title 搜索产品前
     * @desc 搜索产品前
     * @url before_search_host
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @return array host_id - 产品ID数组
     */
    public function before_search_host(){}

    /**
     * 时间 2024-02-18
     * @title 产品编辑后
     * @desc 产品编辑后
     * @url after_host_edit
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     * @param object customfield - 自定义字段
     */
    public function after_host_edit(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除后
     * @desc 产品删除后
     * @url after_host_delete
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     * @param int product_id - 商品ID
     * @param string module - 模块名称
     */
    public function after_host_delete(){}

    /**
     * 时间 2024-02-18
     * @title 产品创建后
     * @desc 产品创建后
     * @url before_host_create
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function before_host_create(){}

    /**
     * 时间 2024-02-18
     * @title 产品创建成功后
     * @desc 产品创建成功后
     * @url after_host_create_success
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_create_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品创建失败后
     * @desc 产品创建失败后
     * @url after_host_create_fail
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_create_fail(){}

    /**
     * 时间 2024-02-18
     * @title 产品暂停前
     * @desc 产品暂停前
     * @url before_host_suspend
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function before_host_suspend(){}

    /**
     * 时间 2024-02-18
     * @title 产品暂停成功后
     * @desc 产品暂停成功后
     * @url after_host_suspend_success
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_suspend_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品暂停失败后
     * @desc 产品暂停失败后
     * @url after_host_suspend_fail
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_suspend_fail(){}

    /**
     * 时间 2024-02-18
     * @title 产品解除暂停前
     * @desc 产品解除暂停前
     * @url before_host_unsuspend
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function before_host_unsuspend(){}

    /**
     * 时间 2024-02-18
     * @title 产品解除暂停成功后
     * @desc 产品解除暂停成功后
     * @url after_host_unsuspend_success
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_unsuspend_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品解除暂停失败后
     * @desc 产品解除暂停失败后
     * @url after_host_unsuspend_fail
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_unsuspend_fail(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除前
     * @desc 产品删除前
     * @url before_host_terminate
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function before_host_terminate(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除成功后
     * @desc 产品删除成功后
     * @url after_host_terminate_success
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_terminate_success(){}

    /**
     * 时间 2024-02-18
     * @title 产品删除失败后
     * @desc 产品删除失败后
     * @url after_host_terminate_fail
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function after_host_terminate_fail(){}

    /**
     * 时间 2024-02-18
     * @title 商品自定义字段
     * @desc 商品自定义字段
     * @url product_detail_custom_fields
     * @author wyh
     * @version v1
     * @param int id - 商品ID
     * @return object - 自定义字段键值对
     */
    public function product_detail_custom_fields(){}

    /**
     * 时间 2024-02-18
     * @title 商品创建后
     * @desc 商品创建后
     * @url after_product_create
     * @author wyh
     * @version v1
     * @param int id - 商品ID
     * @param object customfield - 自定义字段
     */
    public function after_product_create(){}

    /**
     * 时间 2024-02-18
     * @title 商品编辑后
     * @desc 商品编辑后
     * @url after_product_edit
     * @author wyh
     * @version v1
     * @param int id - 商品ID
     * @param object customfield - 自定义字段
     */
    public function after_product_edit(){}

    /**
     * 时间 2024-02-18
     * @title 商品复制后
     * @desc 商品复制后
     * @url after_product_copy
     * @author wyh
     * @version v1
     * @param int id - 复制后商品ID
     * @param int product_id - 商品ID
     * @param int son_product_id - 子商品ID
     * @param object customfield - 自定义字段
     */
    public function after_product_copy(){}

    /**
     * 时间 2024-03-07
     * @title 推介计划续费订单支付后
     * @desc 推介计划续费订单支付后
     * @url recommend_renew_order_paid
     * @author wyh
     * @version v1
     * @param int id - 订单ID
     */
    public function recommend_renew_order_paid(){}

    /**
     * 时间 2024-02-18
     * @title 活动促销折扣
     * @desc 活动促销折扣
     * @url event_promotion_by_amount
     * @author wyh
     * @version v1
     * @param int event_promotion - 活动ID
     * @param int product_id - 商品ID
     * @param int qty - 数量
     * @param float amount - 金额
     * @param int billing_cycle_time - 周期时间
     * @return int status - 状态码200成功，400失败
     * @return string msg - 信息
     * @return array data -
     * @return float data.discount - 折扣金额
     */
    public function event_promotion_by_amount(){}

    /**
     * 时间 2024-03-20
     * @title 在产品软删除后
     * @desc  在产品软删除后
     * @url after_host_soft_delete
     * @author hh
     * @version v1
     * @param   int id - 产品ID
     */
    public function after_host_soft_delete(){}

    /**
     * 时间 2024-04-09
     * @title 在订单放入回收站前
     * @desc  在订单放入回收站前
     * @url before_order_recycle
     * @author hh
     * @version v1
     * @param   int id - 订单ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function before_order_recycle(){}

    /**
     * 时间 2024-04-10
     * @title 通用自定义字段
     * @desc  通用自定义字段(钩子返回数据会放在console/v1/common通用接口返回的custom_fields字段下)
     * @url common_custom_fields
     * @author wyh
     * @version v1
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data - {"field1":"test1","field2":"test2"}
     */
    public function common_custom_fields(){}

    /**
     * 时间 2024-04-19
     * @title 产品续费前,单个和批量续费
     * @desc  产品续费前,单个和批量续费
     * @url before_host_renew
     * @author theworld
     * @version v1
     * @param   int|array host_id - 产品ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function beforeHostRenew(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款后
     * @desc  产品申请退款后
     * @url after_host_refund_create
     * @author theworld
     * @version v1
     * @param int host_id - 产品ID required
     * @param mixed suspend_reason - 停用原因,产品可以自定义原因时,输入框,传字符串;产品不可自定义原因时,传停用原因ID数组
     * @param string type - 停用时间:Expire到期,Immediate立即
     */
    public function afterHostRefundCreate(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款取消后
     * @desc  产品申请退款取消后
     * @url after_host_refund_cancel
     * @author theworld
     * @version v1
     * @param int id - 停用申请ID required
     */
    public function afterHostRefundCancel(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款通过后
     * @desc  产品申请退款通过后
     * @url after_host_refund_pending
     * @author theworld
     * @version v1
     * @param int id - 停用申请ID required
     */
    public function afterHostRefundPending(){}

    /**
     * 时间 2024-04-19
     * @title 产品申请退款驳回后
     * @desc  产品申请退款驳回后
     * @url after_host_refund_reject
     * @author theworld
     * @version v1
     * @param int id - 停用申请ID required
     * @param string reject_reason - 驳回原因 required
     */
    public function afterHostRefundReject(){}

    /**
     * 时间 2024-04-22
     * @title 系统设置自定义输出
     * @desc  系统设置自定义输出
     * @url configuration_system_list
     * @author hh
     * @version v1
     * @return int status - 状态(200=成功,400=失败)
     * @return array data - 追加数据(建议返回关联数组,防止冲突)
     */
    public function configurationSystemList(){}

    /**
     * 时间 2024-04-22
     * @title 保存系统设置前
     * @desc  保存系统设置前
     * @url before_configuration_system_update
     * @author hh
     * @version v1
     * @param  string lang_admin - 后台默认语言
     * @param  int lang_home_open - 前台多语言开关:1开启0关闭
     * @param  string lang_home - 前台默认语言
     * @param  int maintenance_mode - 维护模式开关:1开启0关闭
     * @param  string maintenance_mode_message - 维护模式内容
     * @param  string website_name - 网站名称
     * @param  string website_url - 网站域名地址
     * @param  string terms_service_url - 服务条款地址
     * @param  string terms_privacy_url - 隐私条款地址
     * @param  string system_logo - 系统LOGO
     * @param  int client_start_id_value - 用户注册开始ID
     * @param  int order_start_id_value - 用户订单开始ID
     * @param  string clientarea_url - 会员中心地址
     * @param  string tab_logo - 标签页LOGO
     * @param  int home_show_deleted_host - 前台是否展示已删除产品:1是0否
     * @param  object customfiled - 自定义参数
     * @return int status - 状态(200=成功,400=失败)
     * @return string msg - 信息
     */
    public function beforeConfigurationSystemUpdate(){}

    /**
     * 时间 2024-04-22
     * @title 保存系统设置后
     * @desc  保存系统设置后
     * @url after_configuration_system_update
     * @author hh
     * @version v1
     * @param  string lang_admin - 后台默认语言
     * @param  int lang_home_open - 前台多语言开关:1开启0关闭
     * @param  string lang_home - 前台默认语言
     * @param  int maintenance_mode - 维护模式开关:1开启0关闭
     * @param  string maintenance_mode_message - 维护模式内容
     * @param  string website_name - 网站名称
     * @param  string website_url - 网站域名地址
     * @param  string terms_service_url - 服务条款地址
     * @param  string terms_privacy_url - 隐私条款地址
     * @param  string system_logo - 系统LOGO
     * @param  int client_start_id_value - 用户注册开始ID
     * @param  int order_start_id_value - 用户订单开始ID
     * @param  string clientarea_url - 会员中心地址
     * @param  string tab_logo - 标签页LOGO
     * @param  int home_show_deleted_host - 前台是否展示已删除产品:1是0否
     * @param  object customfiled - 自定义参数
     */
    public function afterConfigurationSystemUpdate(){}

    /**
     * 时间 2024-04-19
     * @title 前台商品详情
     * @desc  前台商品详情
     * @url home_product_index
     * @author theworld
     * @version v1
     * @param int id - 商品ID required
     */
    public function homeProductIndex(){}

    /**
     * 时间 2024-04-29
     * @title 前台购物车详情
     * @desc  前台购物车详情
     * @url home_cart_index
     * @author theworld
     * @version v1
     * @return  array cart - 计算后数据 required
     * @return  int cart[].product_id - 商品ID
     * @return  object cart[].config_options - 自定义配置
     * @return  int cart[].qty - 数量
     * @return  object cart[].customfield - 自定义参数
     * @return  string cart[].name - 商品名称
     * @return  string cart[].description - 商品描述
     * @return  int cart[].stock_control - 库存控制0:关闭1:启用
     * @return  int cart[].stock_qty - 库存数量
     * @return  object cart[].self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     */
    public function homeCartIndex(){}
}