<?php
// +----------------------------------------------------------------------
// | idcsmart
// +----------------------------------------------------------------------

return [
	// 默认页码
    'page' => 1,
	// 条数
    'limit' => 20,
	// 排序
    'sort' => 'desc',
    // jwt key 前台使用 取系统唯一识别码,或者动态取数据存数据库
    'jwt_key_client'    =>  'op8FhjzGHRUaPsOXLdu24CmD90EJ3l',
    // 后台使用
    'jwt_key_admin'    =>  'qp8FhjzGHRUaPsBXSdu24CmD90EJ3l',
    // 不操作,登录失效时间,单位秒(s)
    'auto_logout' => 7200,
    // 支持的插件类型
    'plugin_module' => ['addon','gateway','sms','mail'],
    // 模板钩子
    'template_hooks' => [
        'template_after_servicedetail_suspended',
    ],
    'aes' => [
        'key' => 'idcsmart.finance',
        'iv' => '9311019310287172'
    ],
    // 插件模板模板定义
    'template' => [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写
        'auto_rule'               => 1,
        // 模板引擎类型 支持 php think 支持扩展
        'type'                    => 'Think',
        // 视图基础目录，配置目录为所有模块的视图起始目录
        'view_base'               => '',
        // 当前模板的视图目录 留空为自动获取
        'view_path'               => '',
        // 模板后缀
        'view_suffix'             => 'php',
        // 模板文件名分隔符
        'view_depr'               => DIRECTORY_SEPARATOR,
        // 模板引擎普通标签开始标记
        'tpl_begin'               => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'                 => '}',
        // 标签库标签开始标记
        'taglib_begin'            => '{',
        // 标签库标签结束标记
        'taglib_end'              => '}',
    ],
    // 自定义根目录命名空间
    'root_namespace' => [
        'gateway' => WEB_ROOT . 'plugins/gateway/',
        'addon' => WEB_ROOT . 'plugins/addon/',
        'captcha' => WEB_ROOT . 'plugins/captcha/',
        'sms' => WEB_ROOT . 'plugins/sms/',
        'mail' => WEB_ROOT . 'plugins/mail/',
        'server' => WEB_ROOT . 'plugins/server/',
    ],
    // 订单类型
    'order_type' => [
        'new',          // 新订单
        'renew',        // 续费订单
        'upgrade',      // 升降级订单
        'artificial',   // 人工订单
    ],
    // 订单状态
    'order_status' => [
        'Paid',     // 已付款
        'Unpaid',   // 未付款
    ],
    // 产品状态
    'host_status' => [
        'Unpaid',   // 未付款
        'Pending',  // 开通中
        'Active',   // 使用中
        'Suspended',// 已暂停
        'Deleted',  // 已删除
        'Failed',   // 开通失败
    ],
    // 任务状态
    'task_status' => [
        'Wait',     // 未开始
        'Exec',     // 执行中
        'Finish',   // 完成
        'Failed',   // 失败
    ],
    // 计费周期
    'billing_cycle' => [
        'free',                 // 免费
        'onetime',              // 一次性
        'recurring_prepayment',            // 周期先付
        'recurring_postpaid',   // 周期后付
    ],
	// 通知动作
	'notice_action' => [
		'code', 			 				 // 验证码
		'client_login_success', 	 		 // 用户登录
		'client_register_success',  		 // 用户注册
		'client_change_phone', 				 // 用户更改手机
		'client_change_email', 			 	 // 用户更改邮箱
		'client_change_password', 			 // 用户更改密码
		'order_create', 			 		 // 订单创建
		'host_pending', 			 		 // 产品开通中
		'host_active', 			 		     // 开通成功
		'host_suspend', 			 		 // 产品暂停通知
		'host_unsuspend', 			 		 // 产品解除暂停通知
		'host_terminate', 			 		 // 产品删除通知
		'host_upgrad', 			 		     // 产品升降级
		'admin_create_account', 			 // 超级管理员添加后台管理员
		'host_renewal_first', 			 	 // 客户续费第一次提醒
		'host_renewal_second', 			 	 // 客户续费第二次提醒
		'host_overdue_first', 			 	 // 逾期第一次提醒
		'host_overdue_second', 			 	 // 逾期第二次提醒
		'host_overdue_third', 			 	 // 逾期第二次提醒
		'order_overdue', 			 		 // 订单未付款通知
		'admin_order_amount', 			 	 // 订单金额修改
		'order_pay', 			 			 // 订单支付通知
		'order_recharge', 			 		 // 充值成功通知

	],
];
