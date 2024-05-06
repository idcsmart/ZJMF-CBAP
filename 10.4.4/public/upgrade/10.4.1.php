<?php 

require dirname(dirname(__DIR__ )) . '/config.php';
require dirname(dirname(__DIR__ )) .'/vendor/autoload.php';

define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ) . '/'); # 网站入口目录

set_time_limit(0);
ini_set('max_execution_time', 3600);

$App=new \think\App();
$App->debug(APP_DEBUG);
$http = $App->http;
$response = $http->run();

use think\facade\Db;
use app\common\model\ConfigurationModel;

$sql = [
	"CREATE TABLE `idcsmart_web_nav` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '导航名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否展示(0=关闭,1=开启)',
  `web_nav_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级导航ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='官网导航表';",
    "DROP TABLE IF EXISTS `idcsmart_auth`;",
    "CREATE TABLE `idcsmart_auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `title` varchar(1000) NOT NULL DEFAULT '' COMMENT '权限标题,存语言的键',
  `url` varchar(1000) NOT NULL DEFAULT '' COMMENT '页面地址',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父权限ID',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件',
  `plugin` varchar(100) NOT NULL DEFAULT '' COMMENT '插件',
  `description` text NOT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';",
    "DROP TABLE IF EXISTS `idcsmart_auth_link`;",
    "CREATE TABLE `idcsmart_auth_link` (
  `auth_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  `admin_role_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员分组ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限对应表';",
    "DROP TABLE IF EXISTS `idcsmart_auth_rule`;",
    "CREATE TABLE `idcsmart_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限规则ID',
  `name` varchar(150) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识,全小写',
  `title` varchar(1000) NOT NULL DEFAULT '' COMMENT '规则描述',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件',
  `plugin` varchar(100) NOT NULL DEFAULT '' COMMENT '插件',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='权限规则表';",
    "DROP TABLE IF EXISTS `idcsmart_auth_rule_link`;",
    "CREATE TABLE `idcsmart_auth_rule_link` (
  `auth_rule_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限规则ID',
  `auth_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  KEY `auth_rule_id` (`auth_rule_id`),
  KEY `auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限规则对应表';",
    "ALTER TABLE `idcsmart_order` ADD COLUMN `is_lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否锁定(0=否,1=是)';",
    "ALTER TABLE `idcsmart_order` ADD COLUMN `recycle_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '放入回收站时间';",
    "ALTER TABLE `idcsmart_order` ADD COLUMN `will_delete_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '彻底删除时间';",
    "ALTER TABLE `idcsmart_order` ADD COLUMN `is_recycle` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否在回收站(0=否,1=是)';",
    "ALTER TABLE `idcsmart_host` ADD COLUMN `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除(0=否,1=是)';",
    "ALTER TABLE `idcsmart_host` ADD COLUMN `delete_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间';",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cart_theme','default','购物车主题');",
    "UPDATE `idcsmart_nav` SET `url`='cart/goodsList.htm' WHERE `type`='home' AND `name`='nav_goods_list';",
    "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('order_recycle_bin', '1', 0, 0, '订单回收站开关(0=关闭,1=开启)');",
    "INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('order_recycle_bin_save_days', '1', 0, 0, '订单存放时间(0=永不删除)');",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartRenew';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartRefund';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartFileDownload';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='PromoCode';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartNews';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartCertification';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartWithdraw';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartHelp';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartTicket';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartSubAccount';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartCloud';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.1' WHERE `name`='IdcsmartAnnouncement';",
    "insert  into `idcsmart_notice_setting`(`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values ('client_reply_ticket','客户回复工单','',0,'',0,0,'',0,0);",
    "insert  into `idcsmart_email_template`(`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values ('客户回复工单','[{system_website_name}]客户回复工单','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>
<div class=\"box\">
<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>
<div class=\"card\">
<h2 style=\"text-align: center;\">[{system_website_name}]客户回复工单</h2>
<br /><strong>尊敬的管理员</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>
<div class=\"card\">您的工单：{subject}有新回复<br /><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>
<ul class=\"banquan\">
<li>{system_website_name}</li>
</ul>
</div>
</body> </html>','',1673429870,0);",
];

$auth = [
    'auth_index' => [],
    'auth_user' => [
        'auth_user_list' => [
            'auth_user_list_view' => [],
            'auth_user_list_create_user' => [],
        ],
        'auth_user_detail' => [
            'auth_user_detail_personal_information' => [
                'auth_user_detail_personal_information_view' => [],
                'auth_user_detail_personal_information_recharge' => [],
                'auth_user_detail_personal_information_change_credit' => [],
                'auth_user_detail_personal_information_change_credit_log' => [],
                'auth_user_detail_personal_information_user_login' => [],
                'auth_user_detail_personal_information_save_user_info' => [],
                'auth_user_detail_personal_information_deactivate_enable_user' => [],
                'auth_user_detail_personal_information_delete_user' => [],
            ],
            'auth_user_detail_host_info' => [
                'auth_user_detail_host_info_view' => [],
                'auth_user_detail_host_info_batch_renew' => [],
                'auth_user_detail_host_info_batch_delete' => [],
                'auth_user_detail_host_info_host_detail' => [],
            ],
            'auth_user_detail_order' => [
                'auth_user_detail_order_view' => [],
                'auth_user_detail_order_create_order' => [],
                'auth_user_detail_order_check_order' => [],
                'auth_user_detail_order_adjust_order_amount' => [],
                'auth_user_detail_order_delete_order' => [],
            ],
            'auth_user_detail_transaction' => [
                'auth_user_detail_transaction_view' => [],
                'auth_user_detail_transaction_create_transaction' => [],
                'auth_user_detail_transaction_update_transaction' => [],
                'auth_user_detail_transaction_delete_transaction' => [],
            ],
            'auth_user_detail_operation_log' => [],
            'auth_user_detail_notification_log' => [
                'auth_user_detail_notification_log_sms_notification' => [],
                'auth_user_detail_notification_log_email_notification' => [],
            ],
            'auth_user_detail_ticket' => [
                'auth_user_detail_ticket_view' => [],
                'auth_user_detail_ticket_transfer_ticket' => [],
                //'auth_user_detail_ticket_create_internal_ticket' => [],
                'auth_user_detail_ticket_close_ticket' => [],
                'auth_user_detail_ticket_detail' => [],
            ],
            'auth_user_detail_info_record' => [
                'auth_user_detail_info_record_view' => [],
                'auth_user_detail_info_record_create_record' => [],
                'auth_user_detail_info_record_update_record' => [],
                'auth_user_detail_info_record_delete_record' => [],
            ],
        ],
        'auth_user_ticket' => [
            'auth_user_ticket_list' => [
                'auth_user_ticket_list_view' => [],
                'auth_user_ticket_list_create_ticket' => [],
                'auth_user_ticket_list_transfer_ticket' => [],
                //'auth_user_ticket_list_create_internal_ticket' => [],
                'auth_user_ticket_list_close_ticket' => [],
                'auth_user_ticket_list_ticket_detail' => [],
            ],
            'auth_user_ticket_configuration' => [
                'auth_user_ticket_configuration_view' => [],
                'auth_user_ticket_configuration_ticket_department' => [],
                'auth_user_ticket_configuration_ticket_status' => [],
                'auth_user_ticket_configuration_save_ticket_notice' => [],
                'auth_user_ticket_configuration_prereply' => [],
            ],
            'auth_user_ticket_detail' => [
                'auth_user_ticket_detail_view' => [],
                'auth_user_ticket_detail_reply_ticket' => [],
                'auth_user_ticket_detail_create_notes' => [],
                'auth_user_ticket_detail_use_prereply' => [],
                'auth_user_ticket_detail_ticket_log' => [],
                'auth_user_ticket_detail_save_ticket' => [],
            ],
        ],
        'auth_user_certification' => [
            'auth_user_certification_approval' => [
                'auth_user_certification_approval_view' => [],
                'auth_user_certification_approval_pass_approval' => [],
                'auth_user_certification_approval_deny_approval' => [],
                'auth_user_certification_approval_certification_detail' => [],
            ],
            'auth_user_certification_configuration' => [
                'auth_user_certification_configuration_view' => [],
                'auth_user_certification_configuration_save_configuration' => [],
            ],
            'auth_user_certification_interface' => [
                'auth_user_certification_interface_view' => [],
                'auth_user_certification_interface_jump_app_store' => [],
                'auth_user_certification_interface_configure_interface' => [],
                'auth_user_certification_interface_deactivate_enable_interface' => [],
                'auth_user_certification_interface_install_uninstall_interface' => [],
            ],
        ],
        'auth_user_refund' => [
            'auth_user_refund_apply_list' => [
                'auth_user_refund_apply_list_view' => [],
                'auth_user_refund_apply_list_approve' => [],
                'auth_user_refund_apply_list_reject' => [],
                'auth_user_refund_apply_list_cancel_apply' => [],
            ],
            'auth_user_refund_product' => [
                'auth_user_refund_product_view' => [],
                'auth_user_refund_product_create_product' => [],
                'auth_user_refund_product_suspend_reason' => [],
                'auth_user_refund_product_update_product' => [],
                'auth_user_refund_product_delete_product' => [],
            ],
        ],
    ],
    'auth_business' => [
        'auth_business_order' => [
            'auth_business_order_view' => [],
            'auth_business_order_create_order' => [],
            'auth_business_order_batch_delete_order' => [],
            'auth_business_order_check_order' => [],
            'auth_business_order_adjust_order_amount' => [],
            'auth_business_order_delete_order' => [],
            'auth_business_order_enable_recycle_bin' => [],
            'auth_business_order_recycle_bin' => [
                'auth_business_order_recycle_bin_view' => [],
                'auth_business_order_recycle_bin_config' => [],
                'auth_business_order_recycle_bin_order_detail' => [],
                'auth_business_order_recycle_bin_recover_order' => [],
                'auth_business_order_recycle_bin_delete_order' => [],
                'auth_business_order_recycle_bin_clear' => [],
                'auth_business_order_recycle_bin_lock_order' => [],
                'auth_business_order_recycle_bin_unlock_order' => [],
            ],
        ],
        'auth_business_order_detail' => [
            'auth_business_order_detail_order_detail' => [
                'auth_business_order_detail_order_detail_view' => [],
                'auth_business_order_detail_order_detail_paid' => [],
                'auth_business_order_detail_order_detail_apply_credit' => [],
                'auth_business_order_detail_order_detail_remove_credit' => [],
                'auth_business_order_detail_order_detail_change_log' => [],
                'auth_business_order_detail_order_detail_delete_order_item' => [],
                'auth_business_order_detail_order_detail_save_order_item' => [],
                'auth_business_order_detail_order_detail_create_order_item' => [],
            ],
            'auth_business_order_detail_refund_record' => [
                'auth_business_order_detail_refund_record_view' => [],
                'auth_business_order_detail_refund_record_refund' => [],
                'auth_business_order_detail_refund_record_delete_record' => [],
            ],
            'auth_business_order_detail_transaction' => [],
            'auth_business_order_detail_notes' => [
                'auth_business_order_detail_notes_view' => [],
                'auth_business_order_detail_notes_save_notes' => [],
            ],
        ],
        'auth_business_host' => [
            'auth_business_host_view' => [],
            'auth_business_host_check_host_detail' => [],
        ],
        'auth_business_host_detail' => [
            'auth_business_host_detail_view' => [],
            'auth_business_host_detail_create_account' => [],
            'auth_business_host_detail_suspend_account' => [],
            'auth_business_host_detail_unsuspend_account' => [],
            'auth_business_host_detail_terminate_account' => [],
            'auth_business_host_detail_host_renew' => [],
            'auth_business_host_detail_save_basic_finance_info' => [],
            'auth_business_host_detail_delete' => [],
            'auth_business_host_detail_save_details' => [],
            'auth_business_host_detail_dcim_host_allot' => [],
            'auth_business_host_detail_host_transfer' => [],
        ],
        'auth_business_transaction' => [
            'auth_business_transaction_view' => [],
            'auth_business_transaction_create_transaction' => [],
            'auth_business_transaction_update_transaction' => [],
            'auth_business_transaction_delete_transaction' => [],
        ],
        'auth_business_withdraw' => [
            'auth_business_withdraw_apply_list' => [
                'auth_business_withdraw_apply_list_view' => [],
                'auth_business_withdraw_apply_list_approve_reject' => [],
                'auth_business_withdraw_apply_list_reject_status_edit' => [],
                'auth_business_withdraw_apply_list_approve_status_edit' => [],
                'auth_business_withdraw_apply_list_confirm_status_edit' => [],
            ],
            'auth_business_withdraw_credit_withdraw_configuration' => [
                'auth_business_withdraw_credit_withdraw_configuration_view' => [],
                'auth_business_withdraw_credit_withdraw_configuration_save_configuration' => [],
            ],
            'auth_business_withdraw_configuration' => [
                'auth_business_withdraw_configuration_view' => [],
                'auth_business_withdraw_configuration_withdraw_method' => [],
                'auth_business_withdraw_configuration_reject_reason' => [],
            ],
        ],
    ],
    'auth_product' => [
        'auth_product_management' => [
            'auth_product_management_view' => [],
            'auth_product_management_create_group' => [],
            'auth_product_management_update_group' => [],
            'auth_product_management_delete_group' => [],
            'auth_product_management_create_product' => [],
            'auth_product_management_list_order' => [],
            'auth_product_management_agent_product' => [],
            'auth_product_management_agentable_product' => [],
            'auth_product_management_product_show_hide' => [],
            'auth_product_management_product_copy' => [],
            'auth_product_management_update_product' => [],
            'auth_product_management_delete_product' => [],
        ],
        'auth_product_detail' => [
            'auth_product_detail_basic_info' => [
                'auth_product_detail_basic_info_view' => [],
                'auth_product_detail_basic_info_save_info' => [],
            ],
            'auth_product_detail_server' => [
                'auth_product_detail_server_view' => [],
                'auth_product_detail_server_save_server' => [],
                'auth_product_detail_server_product_configuration' => [],
            ],
            'auth_product_detail_custom_field' => [
                'auth_product_detail_custom_field_view' => [],
                'auth_product_detail_custom_field_create_field' => [],
                'auth_product_detail_custom_field_update_field' => [],
                'auth_product_detail_custom_field_delete_field' => [],
            ],
        ],
        'auth_product_server' => [
            'auth_product_server_view' => [],
            'auth_product_server_create_server' => [],
            'auth_product_server_update_server' => [],
            'auth_product_server_delete_server' => [],
            'auth_product_server_sub_server' => [
                'auth_product_server_sub_server_sub_server' => [
                    'auth_product_server_sub_server_sub_server_view' => [],
                    'auth_product_server_sub_server_sub_server_create_server' => [],
                    'auth_product_server_sub_server_sub_server_update_server' => [],
                    'auth_product_server_sub_server_sub_server_delete_server' => [],
                ],
                'auth_product_server_sub_server_group' => [
                    'auth_product_server_sub_server_group_view' => [],
                    'auth_product_server_sub_server_group_create_group' => [],
                    'auth_product_server_sub_server_group_update_group' => [],
                    'auth_product_server_sub_server_group_delete_group' => [],
                ],
            ],
        ],
        'auth_product_server_group' => [
            'auth_product_server_group_view' => [],
            'auth_product_server_group_create_group' => [],
            'auth_product_server_group_update_group' => [],
            'auth_product_server_group_delete_group' => [],
        ],
        'auth_product_promo_code' => [
            'auth_product_promo_code_view' => [],
            'auth_product_promo_code_create_promo_code' => [],
            'auth_product_promo_code_deactivate_enable_promo_code' => [],
            'auth_product_promo_code_update_promo_code' => [],
        ],
    ],
    'auth_system_configuration' => [
        'auth_system_configuration_system_configuration' => [
            'auth_system_configuration_system_configuration_system_configuration' => [
                'auth_system_configuration_system_configuration_system_configuration_view' => [],
                'auth_system_configuration_system_configuration_system_configuration_save_configuration' => [],
            ],
            'auth_system_configuration_system_configuration_debug' => [],
            'auth_system_configuration_system_configuration_access_configuration' => [
                'auth_system_configuration_system_configuration_access_configuration_view' => [],
                'auth_system_configuration_system_configuration_access_configuration_save_configuration' => [],
            ],
            'auth_system_configuration_system_configuration_theme_configuration' => [
                'auth_system_configuration_system_configuration_theme_configuration_view' => [],
                'auth_system_configuration_system_configuration_theme_configuration_save_configuration' => [],
            ],
            'auth_system_configuration_system_configuration_web_configuration' => [],
            'auth_system_configuration_system_configuration_system_info' => [
                'auth_system_configuration_system_configuration_system_info_view' => [],
                'auth_system_configuration_system_configuration_system_info_system_upgrade' => [],
                'auth_system_configuration_system_configuration_system_info_change_license' => [],
                'auth_system_configuration_system_configuration_system_info_update_license' => [],
            ],
        ],
        'auth_system_configuration_admin' => [
            'auth_system_configuration_admin_management' => [
                'auth_system_configuration_admin_management_view' => [],
                'auth_system_configuration_admin_management_create_admin' => [],
                'auth_system_configuration_admin_management_update_admin' => [],
                'auth_system_configuration_admin_management_deactivate_enable_admin' => [],
                'auth_system_configuration_admin_management_delete_admin' => [],
            ],
            'auth_system_configuration_admin_group' => [
                'auth_system_configuration_admin_group_view' => [],
                'auth_system_configuration_admin_group_create_group' => [],
                'auth_system_configuration_admin_group_update_group' => [],
                'auth_system_configuration_admin_group_delete_group' => [],
            ],
        ],
        'auth_system_configuration_captcha_configuration' => [
            'auth_system_configuration_captcha_configuration_captcha_configuration' => [
                'auth_system_configuration_captcha_configuration_captcha_configuration_view' => [],
                'auth_system_configuration_captcha_configuration_captcha_configuration_save_configuration' => [],
            ],
            'auth_system_configuration_captcha_configuration_captcha_interface' => [
                'auth_system_configuration_captcha_configuration_captcha_interface_view' => [],
                'auth_system_configuration_captcha_configuration_captcha_interface_get_more_interfaces' => [],
                'auth_system_configuration_captcha_configuration_captcha_interface_deactivate_enable_interface' => [],
                'auth_system_configuration_captcha_configuration_captcha_interface_configuration' => [],
                'auth_system_configuration_captcha_configuration_captcha_interface_install_uninstall_interface' => [],
            ],
        ],
        'auth_system_configuration_currency_configuration' => [
            'auth_system_configuration_currency_configuration_view' => [],
            'auth_system_configuration_currency_configuration_save_configuration' => [],
        ],
        'auth_system_configuration_menu' => [
            'auth_system_configuration_menu_home_menu' => [
                'auth_system_configuration_menu_home_menu_view' => [],
                'auth_system_configuration_menu_home_menu_save_menu' => [],
            ],
            'auth_system_configuration_menu_admin_menu' => [
                'auth_system_configuration_menu_admin_menu_view' => [],
                'auth_system_configuration_menu_admin_menu_save_menu' => [],
            ],
        ],
        'auth_system_configuration_web_feedback' => [],
        'auth_system_configuration_oauth' => [
            'auth_system_configuration_oauth_view' => [],
            'auth_system_configuration_oauth_get_more_interfaces' => [],
            'auth_system_configuration_oauth_deactivate_enable_interface' => [],
            'auth_system_configuration_oauth_interface_configuration' => [],
            'auth_system_configuration_oauth_install_uninstall_interface' => [],
        ],
    ],
    'auth_system_interface' => [
        'auth_system_interface_sms_notice' => [
            'auth_system_interface_sms_notice_view' => [],
            'auth_system_interface_sms_notice_get_more_interfaces' => [],
            'auth_system_interface_sms_notice_deactivate_enable_interface' => [],
            'auth_system_interface_sms_notice_sms_template' => [
                'auth_system_interface_sms_notice_sms_template_view' => [],
                'auth_system_interface_sms_notice_sms_template_create_template' => [],
                'auth_system_interface_sms_notice_sms_template_batch_create_template' => [],
                'auth_system_interface_sms_notice_sms_template_update_template' => [],
                'auth_system_interface_sms_notice_sms_template_test_template' => [],
                'auth_system_interface_sms_notice_sms_template_delete_template' => [],
            ],
            'auth_system_interface_sms_notice_interface_configuration' => [],
            'auth_system_interface_sms_notice_install_uninstall_interface' => [],
        ],
        'auth_system_interface_email_notice' => [
            'auth_system_interface_email_notice_view' => [],
            'auth_system_interface_email_notice_get_more_interfaces' => [],
            'auth_system_interface_email_notice_deactivate_enable_interface' => [],
            'auth_system_interface_email_notice_email_template' => [
                'auth_system_interface_email_notice_email_template_view' => [],
                'auth_system_interface_email_notice_email_template_create_template' => [],
                'auth_system_interface_email_notice_email_template_update_template' => [],
                'auth_system_interface_email_notice_email_template_test_template' => [],
                'auth_system_interface_email_notice_email_template_delete_template' => [],
            ],
            'auth_system_interface_email_notice_interface_configuration' => [],
            'auth_system_interface_email_notice_install_uninstall_interface' => [],
        ],
        'auth_system_interface_send_configuration' => [
            'auth_system_interface_send_configuration_view' => [],
            'auth_system_interface_send_configuration_save_configuration' => [],
        ],
        'auth_system_interface_gateway' => [
            'auth_system_interface_gateway_view' => [],
            'auth_system_interface_gateway_get_more_interfaces' => [],
            'auth_system_interface_gateway_deactivate_enable_interface' => [],
            'auth_system_interface_gateway_interface_configuration' => [],
            'auth_system_interface_gateway_install_uninstall_interface' => [],
        ],
    ],
    'auth_upstream_downstream' => [
        'auth_upstream_downstream_supplier' => [
            'auth_upstream_downstream_supplier_view' => [],
            'auth_upstream_downstream_supplier_create_supplier' => [],
            'auth_upstream_downstream_supplier_update_supplier' => [],
            'auth_upstream_downstream_supplier_delete_supplier' => [],
            'auth_upstream_downstream_supplier_detail' => [
                'auth_upstream_downstream_supplier_detail_order_list' => [
                    'auth_upstream_downstream_supplier_detail_order_list_view' => [],
                    'auth_upstream_downstream_supplier_detail_order_list_check_order' => [],
                    'auth_upstream_downstream_supplier_detail_order_list_update_order' => [],
                    'auth_upstream_downstream_supplier_detail_order_list_delete_order' => [],
                ],
                'auth_upstream_downstream_supplier_detail_product_list' => [
                    'auth_upstream_downstream_supplier_detail_product_list_view' => [],
                    'auth_upstream_downstream_supplier_detail_product_list_product_show_hide' => [],
                    'auth_upstream_downstream_supplier_detail_product_list_update_upstream_product' => [],
                    'auth_upstream_downstream_supplier_detail_product_list_delete_upstream_product' => [],
                ],
                'auth_upstream_downstream_supplier_detail_host_list' => [
                    'auth_upstream_downstream_supplier_detail_host_list_view' => [],
                    'auth_upstream_downstream_supplier_detail_host_list_check_host_detail' => [],
                ],
            ],
        ],
        'auth_upstream_downstream_upstream_order' => [
            'auth_upstream_downstream_upstream_order_view' => [],
            'auth_upstream_downstream_upstream_order_check_order' => [],
            'auth_upstream_downstream_upstream_order_update_order' => [],
            'auth_upstream_downstream_upstream_order_delete_order' => [],
            'auth_upstream_downstream_upstream_order_agent' => [],
        ],
        'auth_upstream_downstream_upstream_host' => [
            'auth_upstream_downstream_upstream_host_view' => [],
            'auth_upstream_downstream_upstream_host_check_host_detail' => [],
        ],
        'auth_upstream_downstream_upstream_product' => [
            'auth_upstream_downstream_upstream_product_view' => [],
            'auth_upstream_downstream_upstream_product_create_group' => [],
            'auth_upstream_downstream_upstream_product_create_upstream_product' => [],
            'auth_upstream_downstream_upstream_product_show_hide' => [],
            'auth_upstream_downstream_upstream_product_update_upstream_product' => [],
            'auth_upstream_downstream_upstream_product_delete_upstream_product' => [],
        ],
    ],
    'auth_management' => [
        'auth_management_task' => [
            'auth_management_task_view' => [],
            'auth_management_task_retry' => [],
        ],
        'auth_management_log' => [
            'auth_management_log_system_log' => [],
            'auth_management_log_notice_log' => [],
        ],
        'auth_management_cron' => [
            'auth_management_cron_view' => [],
            'auth_management_cron_save_cron' => [],
        ],
    ],
    'auth_site_management' => [
        'auth_site_management_news' => [
            'auth_site_management_news_view' => [],
            'auth_site_management_news_create_news' => [],
            'auth_site_management_news_type' => [],
            'auth_site_management_news_show_hide' => [],
            'auth_site_management_news_update_news' => [],
            'auth_site_management_news_delete_news' => [],
        ],
        'auth_site_management_help' => [
            'auth_site_management_help_view' => [],
            'auth_site_management_help_create_help' => [],
            'auth_site_management_help_index' => [],
            'auth_site_management_help_type' => [],
            'auth_site_management_help_show_hide' => [],
            'auth_site_management_help_update_help' => [],
            'auth_site_management_help_delete_help' => [],
        ],
        'auth_site_management_announcement' => [
            'auth_site_management_announcement_view' => [],
            'auth_site_management_announcement_create_announcement' => [],
            'auth_site_management_announcement_type' => [],
            'auth_site_management_announcement_show_hide' => [],
            'auth_site_management_announcement_update_announcement' => [],
            'auth_site_management_announcement_delete_announcement' => [],
        ],
        'auth_site_management_file_download' => [
            'auth_site_management_file_download_view' => [],
            'auth_site_management_file_download_upload_file' => [],
            'auth_site_management_file_download_update_file' => [],
            'auth_site_management_file_download_move_file' => [],
            'auth_site_management_file_download_delete_file' => [],
            'auth_site_management_file_download_file_order' => [],
            'auth_site_management_file_download_file_show_hide' => [],
            'auth_site_management_file_download_file_folder' => [],
        ],
    ],
    'auth_app' => [
        'auth_app_list' => [
            'auth_app_list_view' => [],
            'auth_app_list_more_app' => [],
            'auth_app_list_sync_app' => [
                'auth_app_list_sync_app_download_upgrade' => [],
            ],
            'auth_app_list_plugin_hook_order' => [],
            'auth_app_list_upgrade' => [],
            'auth_app_list_deactivate_enable_app' => [],
            'auth_app_list_install_uninstall_app' => [],
        ],
    ],
];

$auth_description = [
    'auth_index' => '首页',
    'auth_user' => '用户管理',
    'auth_user_list' => '用户列表',
    'auth_user_list_view' => '查看页面',
    'auth_user_list_create_user' => '新建用户',
    'auth_user_detail' => '用户详情',
    'auth_user_detail_personal_information' => '个人资料',
    'auth_user_detail_personal_information_view' => '查看页面',
    'auth_user_detail_personal_information_recharge' => '充值',
    'auth_user_detail_personal_information_change_credit' => '强制变更余额',
    'auth_user_detail_personal_information_change_credit_log' => '余额变更记录',
    'auth_user_detail_personal_information_user_login' => '以用户登录',
    'auth_user_detail_personal_information_save_user_info' => '保存用户信息',
    'auth_user_detail_personal_information_deactivate_enable_user' => '停/启用用户',
    'auth_user_detail_personal_information_delete_user' => '删除用户',
    'auth_user_detail_host_info' => '产品信息',
    'auth_user_detail_host_info_view' => '查看页面',
    'auth_user_detail_host_info_batch_renew' => '批量续费',
    'auth_user_detail_host_info_batch_delete' => '批量删除',
    'auth_user_detail_host_info_host_detail' => '查看产品详情',
    'auth_user_detail_order' => '用户订单管理',
    'auth_user_detail_order_view' => '查看页面',
    'auth_user_detail_order_create_order' => '新建订单',
    'auth_user_detail_order_check_order' => '查看订单',
    'auth_user_detail_order_adjust_order_amount' => '调整订单金额',
    'auth_user_detail_order_delete_order' => '删除订单',
    'auth_user_detail_transaction' => '交易流水',
    'auth_user_detail_transaction_view' => '查看页面',
    'auth_user_detail_transaction_create_transaction' => '新增流水',
    'auth_user_detail_transaction_update_transaction' => '编辑流水',
    'auth_user_detail_transaction_delete_transaction' => '删除流水',
    'auth_user_detail_operation_log' => '操作日志',
    'auth_user_detail_notification_log' => '通知日志',
    'auth_user_detail_notification_log_sms_notification' => '短信通知',
    'auth_user_detail_notification_log_email_notification' => '邮件通知',
    'auth_user_detail_ticket' => '工单', 
    'auth_user_detail_ticket_view' => '查看页面',
    'auth_user_detail_ticket_transfer_ticket' => '转单',
    //'auth_user_detail_ticket_create_internal_ticket' => '新建内部工单',
    'auth_user_detail_ticket_close_ticket' => '关闭工单',
    'auth_user_detail_ticket_detail' => '查看工单详情',
    'auth_user_detail_info_record' => '信息记录',
    'auth_user_detail_info_record_view' => '查看页面',
    'auth_user_detail_info_record_create_record' => '新增记录',
    'auth_user_detail_info_record_update_record' => '编辑记录',
    'auth_user_detail_info_record_delete_record' => '删除记录',
    'auth_user_ticket' => '用户工单',
    'auth_user_ticket_list' => '工单列表',
    'auth_user_ticket_list_view' => '查看页面',
    'auth_user_ticket_list_create_ticket' => '新建工单',
    'auth_user_ticket_list_transfer_ticket' => '转单',
    //'auth_user_ticket_list_create_internal_ticket' => '新建内部工单',
    'auth_user_ticket_list_close_ticket' => '关闭工单',
    'auth_user_ticket_list_ticket_detail' => '查看工单详情',
    'auth_user_ticket_configuration' => '工单配置',
    'auth_user_ticket_configuration_view' => '查看页面',
    'auth_user_ticket_configuration_ticket_department' => '工单部门',
    'auth_user_ticket_configuration_ticket_status' => '工单状态',
    'auth_user_ticket_configuration_save_ticket_notice' => '保存工单通知',
    'auth_user_ticket_configuration_prereply' => '预设回复',
    'auth_user_ticket_detail' => '工单详情',
    'auth_user_ticket_detail_view' => '查看页面',
    'auth_user_ticket_detail_reply_ticket' => '回复工单',
    'auth_user_ticket_detail_create_notes' => '添加备注',
    'auth_user_ticket_detail_use_prereply' => '使用预设回复',
    'auth_user_ticket_detail_ticket_log' => '工单日志记录',
    'auth_user_ticket_detail_save_ticket' => '保存工单信息',
    'auth_user_certification' => '实名认证',
    'auth_user_certification_approval' => '实名审批',
    'auth_user_certification_approval_view' => '查看页面',
    'auth_user_certification_approval_pass_approval' => '通过审批',
    'auth_user_certification_approval_deny_approval' => '拒绝审批',
    'auth_user_certification_approval_certification_detail' => '查看实名详情',
    'auth_user_certification_configuration' => '实名设置',
    'auth_user_certification_configuration_view' => '查看页面',
    'auth_user_certification_configuration_save_configuration' => '保存设置',
    'auth_user_certification_interface' => '接口管理',
    'auth_user_certification_interface_view' => '查看页面',
    'auth_user_certification_interface_jump_app_store' => '跳转应用商店',
    'auth_user_certification_interface_configure_interface' => '配置接口',
    'auth_user_certification_interface_deactivate_enable_interface' => '停/启用接口',
    'auth_user_certification_interface_install_uninstall_interface' => '安装/卸载接口',
    'auth_user_refund' => '退款管理',
    'auth_user_refund_apply_list' => '申请列表',
    'auth_user_refund_apply_list_view' => '查看页面',
    'auth_user_refund_apply_list_approve' => '通过审核',
    'auth_user_refund_apply_list_reject' => '审核驳回',
    'auth_user_refund_apply_list_cancel_apply' => '取消申请',
    'auth_user_refund_product' => '商品管理',
    'auth_user_refund_product_view' => '查看页面',
    'auth_user_refund_product_create_product' => '新增可退款商品',
    'auth_user_refund_product_suspend_reason' => '停用原因管理',
    'auth_user_refund_product_update_product' => '编辑退款商品',
    'auth_user_refund_product_delete_product' => '删除退款商品',
    'auth_business' => '业务管理',
    'auth_business_order' => '订单管理',
    'auth_business_order_view' => '查看页面',
    'auth_business_order_create_order' => '新建订单',
    'auth_business_order_batch_delete_order' => '批量删除订单',
    'auth_business_order_check_order' => '查看订单',
    'auth_business_order_adjust_order_amount' => '调整订单金额',
    'auth_business_order_delete_order' => '删除订单',
    'auth_business_order_detail' => '订单详情',
    'auth_business_order_detail_order_detail' => '订单详情',
    'auth_business_order_detail_order_detail_view' => '查看页面',
    'auth_business_order_detail_order_detail_paid' => '标记支付',
    'auth_business_order_detail_order_detail_apply_credit' => '应用余额',
    'auth_business_order_detail_order_detail_remove_credit' => '扣除余额',
    'auth_business_order_detail_order_detail_change_log' => '变更记录',
    'auth_business_order_detail_order_detail_delete_order_item' => '删除订单子项',
    'auth_business_order_detail_order_detail_save_order_item' => '保存订单子项',
    'auth_business_order_detail_order_detail_create_order_item' => '新增订单子项',
    'auth_business_order_detail_refund_record' => '退款记录',
    'auth_business_order_detail_refund_record_view' => '查看页面',
    'auth_business_order_detail_refund_record_refund' => '发起退款',
    'auth_business_order_detail_refund_record_delete_record' => '删除退款记录',
    'auth_business_order_detail_transaction' => '交易流水',
    'auth_business_order_detail_notes' => '备注',
    'auth_business_order_detail_notes_view' => '查看页面',
    'auth_business_order_detail_notes_save_notes' => '保存备注',
    'auth_business_order_enable_recycle_bin' => '开启回收站',
    'auth_business_order_recycle_bin' => '回收站',
    'auth_business_order_recycle_bin_view' => '查看页面',
    'auth_business_order_recycle_bin_config' => '回收站设置',
    'auth_business_order_recycle_bin_order_detail' => '查看订单详情',
    'auth_business_order_recycle_bin_recover_order' => '恢复订单',
    'auth_business_order_recycle_bin_delete_order' => '删除订单',
    'auth_business_order_recycle_bin_clear' => '清空回收站',
    'auth_business_order_recycle_bin_lock_order' => '锁定订单',
    'auth_business_order_recycle_bin_unlock_order' => '取消锁定',
    'auth_business_host' => '产品管理',
    'auth_business_host_view' => '查看页面',
    'auth_business_host_check_host_detail' => '查看产品详情',
    'auth_business_host_detail' => '产品详情',
    'auth_business_host_detail_view' => '查看页面',
    'auth_business_host_detail_create_account' => '开通产品',
    'auth_business_host_detail_suspend_account' => '暂停产品',
    'auth_business_host_detail_unsuspend_account' => '解除暂停产品',
    'auth_business_host_detail_terminate_account' => '删除产品',
    'auth_business_host_detail_host_renew' => '产品续费',
    'auth_business_host_detail_save_basic_finance_info' => '保存基础/财务信息',
    'auth_business_host_detail_delete' => '删除',
    'auth_business_host_detail_save_details' => '保存明细信息',
    'auth_business_host_detail_dcim_host_allot' => 'DCIM产品分配',
    'auth_business_host_detail_host_transfer' => '产品转移',
    'auth_business_transaction' => '交易流水',
    'auth_business_transaction_view' => '查看页面',
    'auth_business_transaction_create_transaction' => '新增流水',
    'auth_business_transaction_update_transaction' => '编辑流水',
    'auth_business_transaction_delete_transaction' => '删除流水',
    'auth_business_withdraw' => '提现管理',
    'auth_business_withdraw_apply_list' => '申请列表',
    'auth_business_withdraw_apply_list_view' => '查看页面',
    'auth_business_withdraw_apply_list_approve_reject' => '通过/驳回审核',
    'auth_business_withdraw_apply_list_reject_status_edit' => '驳回状态编辑',
    'auth_business_withdraw_apply_list_approve_status_edit' => '通过状态编辑',
    'auth_business_withdraw_apply_list_confirm_status_edit' => '确认状态编辑',
    'auth_business_withdraw_credit_withdraw_configuration' => '余额提现设置',
    'auth_business_withdraw_credit_withdraw_configuration_view' => '查看页面',
    'auth_business_withdraw_credit_withdraw_configuration_save_configuration' => '保存设置',
    'auth_business_withdraw_configuration' => '提现设置',
    'auth_business_withdraw_configuration_view' => '查看页面',
    'auth_business_withdraw_configuration_withdraw_method' => '提现方式',
    'auth_business_withdraw_configuration_reject_reason' => '驳回原因',
    'auth_product' => '商品管理',
    'auth_product_management' => '商品管理',
    'auth_product_management_view' => '查看页面',
    'auth_product_management_create_group' => '新建分组',
    'auth_product_management_update_group' => '编辑分组',
    'auth_product_management_delete_group' => '删除分组',
    'auth_product_management_create_product' => '新建商品',
    'auth_product_management_list_order' => '列表拖动排序',
    'auth_product_management_agent_product' => '立即代理商品',
    'auth_product_management_agentable_product' => '管理可被代理商品',
    'auth_product_management_product_show_hide' => '商品显隐开关',
    'auth_product_management_product_copy' => '商品复制',
    'auth_product_management_update_product' => '编辑商品',
    'auth_product_management_delete_product' => '删除商品',
    'auth_product_detail' => '商品详情',
    'auth_product_detail_basic_info' => '基础信息',
    'auth_product_detail_basic_info_view' => '查看页面',
    'auth_product_detail_basic_info_save_info' => '保存信息',
    'auth_product_detail_server' => '接口管理',
    'auth_product_detail_server_view' => '查看页面',
    'auth_product_detail_server_save_server' => '保存接口管理',
    'auth_product_detail_server_product_configuration' => '商品配置（显示商品详细配置）',
    'auth_product_detail_custom_field' => '自定义字段',
    'auth_product_detail_custom_field_view' => '查看页面',
    'auth_product_detail_custom_field_create_field' => '新增字段',
    'auth_product_detail_custom_field_update_field' => '编辑字段',
    'auth_product_detail_custom_field_delete_field' => '删除字段',
    'auth_product_server' => '接口管理',
    'auth_product_server_view' => '查看页面',
    'auth_product_server_create_server' => '新建商品接口',
    'auth_product_server_update_server' => '编辑商品接口',
    'auth_product_server_delete_server' => '删除商品接口',
    'auth_product_server_sub_server' => '子接口管理',
    'auth_product_server_sub_server_sub_server' => '子接口',
    'auth_product_server_sub_server_sub_server_view' => '查看页面',
    'auth_product_server_sub_server_sub_server_create_server' => '新建商品子接口',
    'auth_product_server_sub_server_sub_server_update_server' => '编辑商品子接口',
    'auth_product_server_sub_server_sub_server_delete_server' => '删除商品子接口',
    'auth_product_server_sub_server_group' => '子接口分组',
    'auth_product_server_sub_server_group_view' => '查看页面',
    'auth_product_server_sub_server_group_create_group' => '新建商品子接口分组',
    'auth_product_server_sub_server_group_update_group' => '编辑商品子接口分组',
    'auth_product_server_sub_server_group_delete_group' => '删除商品子接口分组',
    'auth_product_server_group' => '接口分组',
    'auth_product_server_group_view' => '查看页面',
    'auth_product_server_group_create_group' => '新建商品接口分组',
    'auth_product_server_group_update_group' => '编辑商品接口分组',
    'auth_product_server_group_delete_group' => '删除商品接口分组',
    'auth_product_promo_code' => '优惠码',
    'auth_product_promo_code_view' => '查看页面',
    'auth_product_promo_code_create_promo_code' => '新增优惠码',
    'auth_product_promo_code_deactivate_enable_promo_code' => '停/启用优惠码',
    'auth_product_promo_code_update_promo_code' => '编辑优惠码',
    'auth_system_configuration' => '系统设置',
    'auth_system_configuration_system_configuration' => '系统设置',
    'auth_system_configuration_system_configuration_system_configuration' => '系统设置',
    'auth_system_configuration_system_configuration_system_configuration_view' => '查看页面',
    'auth_system_configuration_system_configuration_system_configuration_save_configuration' => '保存设置',
    'auth_system_configuration_system_configuration_debug' => 'Debug调试',
    'auth_system_configuration_system_configuration_access_configuration' => '访问设置',
    'auth_system_configuration_system_configuration_access_configuration_view' => '查看页面',
    'auth_system_configuration_system_configuration_access_configuration_save_configuration' => '保存设置',
    'auth_system_configuration_system_configuration_theme_configuration' => '主题设置',
    'auth_system_configuration_system_configuration_theme_configuration_view' => '查看页面',
    'auth_system_configuration_system_configuration_theme_configuration_save_configuration' => '保存设置',
    'auth_system_configuration_system_configuration_web_configuration' => '官网设置',
    'auth_system_configuration_system_configuration_system_info' => '系统信息',
    'auth_system_configuration_system_configuration_system_info_view' => '查看页面',
    'auth_system_configuration_system_configuration_system_info_system_upgrade' => '系统升级',
    'auth_system_configuration_system_configuration_system_info_change_license' => '更换识别码',
    'auth_system_configuration_system_configuration_system_info_update_license' => '更新识别码',
    'auth_system_configuration_admin' => '管理员设置',
    'auth_system_configuration_admin_management' => '管理员设置',
    'auth_system_configuration_admin_management_view' => '查看页面',
    'auth_system_configuration_admin_management_create_admin' => '添加管理员',
    'auth_system_configuration_admin_management_update_admin' => '编辑管理员',
    'auth_system_configuration_admin_management_deactivate_enable_admin' => '停/启用管理员',
    'auth_system_configuration_admin_management_delete_admin' => '删除管理员',
    'auth_system_configuration_admin_group' => '管理员分组设置',
    'auth_system_configuration_admin_group_view' => '查看页面',
    'auth_system_configuration_admin_group_create_group' => '新增管理员分组',
    'auth_system_configuration_admin_group_update_group' => '编辑管理员分组',
    'auth_system_configuration_admin_group_delete_group' => '删除管理员分组',
    'auth_system_configuration_captcha_configuration' => '验证码设置',
    'auth_system_configuration_captcha_configuration_captcha_configuration' => '验证码设置',
    'auth_system_configuration_captcha_configuration_captcha_configuration_view' => '查看页面',
    'auth_system_configuration_captcha_configuration_captcha_configuration_save_configuration' => '保存设置',
    'auth_system_configuration_captcha_configuration_captcha_interface' => '验证码接口管理',
    'auth_system_configuration_captcha_configuration_captcha_interface_view' => '查看页面',
    'auth_system_configuration_captcha_configuration_captcha_interface_get_more_interfaces' => '获取更多接口',
    'auth_system_configuration_captcha_configuration_captcha_interface_deactivate_enable_interface' => '停/启用验证码接口',
    'auth_system_configuration_captcha_configuration_captcha_interface_configuration' => '配置验证码接口',
    'auth_system_configuration_captcha_configuration_captcha_interface_install_uninstall_interface' => '安装/卸载验证码接口',
    'auth_system_configuration_currency_configuration' => '货币设置',
    'auth_system_configuration_currency_configuration_view' => '查看页面',
    'auth_system_configuration_currency_configuration_save_configuration' => '保存设置',
    'auth_system_configuration_menu' => '导航设置',
    'auth_system_configuration_menu_home_menu' => '前台导航管理',
    'auth_system_configuration_menu_home_menu_view' => '查看页面',
    'auth_system_configuration_menu_home_menu_save_menu' => '应用导航',
    'auth_system_configuration_menu_admin_menu' => '后台导航管理',
    'auth_system_configuration_menu_admin_menu_view' => '查看页面',
    'auth_system_configuration_menu_admin_menu_save_menu' => '应用导航',
    'auth_system_configuration_web_feedback' => '官网反馈',
    'auth_system_configuration_oauth' => '三方登录',
    'auth_system_configuration_oauth_view' => '查看页面',
    'auth_system_configuration_oauth_get_more_interfaces' => '获取更多接口',
    'auth_system_configuration_oauth_deactivate_enable_interface' => '停/启用三方登录接口',
    'auth_system_configuration_oauth_interface_configuration' => '配置三方登录接口',
    'auth_system_configuration_oauth_install_uninstall_interface' => '安装/卸载三方登录接口',
    'auth_system_interface' => '系统接口',
    'auth_system_interface_sms_notice' => '短信通知',
    'auth_system_interface_sms_notice_view' => '查看页面',
    'auth_system_interface_sms_notice_get_more_interfaces' => '获取更多接口',
    'auth_system_interface_sms_notice_deactivate_enable_interface' => '停/启用短信接口',
    'auth_system_interface_sms_notice_sms_template' => '短信模板管理',
    'auth_system_interface_sms_notice_sms_template_view' => '查看页面',
    'auth_system_interface_sms_notice_sms_template_create_template' => '创建模板',
    'auth_system_interface_sms_notice_sms_template_batch_create_template' => '批量提交模板',
    'auth_system_interface_sms_notice_sms_template_update_template' => '编辑模版',
    'auth_system_interface_sms_notice_sms_template_test_template' => '测试模板',
    'auth_system_interface_sms_notice_sms_template_delete_template' => '删除模板',
    'auth_system_interface_sms_notice_interface_configuration' => '配置短信接口',
    'auth_system_interface_sms_notice_install_uninstall_interface' => '安装/卸载短信接口',
    'auth_system_interface_email_notice' => '邮件通知',
    'auth_system_interface_email_notice_view' => '查看页面',
    'auth_system_interface_email_notice_get_more_interfaces' => '获取更多接口',
    'auth_system_interface_email_notice_deactivate_enable_interface' => '停/启用邮件接口',
    'auth_system_interface_email_notice_email_template' => '邮件模板管理',
    'auth_system_interface_email_notice_email_template_view' => '查看页面',
    'auth_system_interface_email_notice_email_template_create_template' => '创建模板',
    'auth_system_interface_email_notice_email_template_update_template' => '编辑模版',
    'auth_system_interface_email_notice_email_template_test_template' => '测试模板',
    'auth_system_interface_email_notice_email_template_delete_template' => '删除模板',
    'auth_system_interface_email_notice_interface_configuration' => '配置邮件接口',
    'auth_system_interface_email_notice_install_uninstall_interface' => '安装/卸载邮件接口',
    'auth_system_interface_send_configuration' => '发送设置',
    'auth_system_interface_send_configuration_view' => '查看页面',
    'auth_system_interface_send_configuration_save_configuration' => '保存设置',
    'auth_system_interface_gateway' => '支付接口',
    'auth_system_interface_gateway_view' => '查看页面',
    'auth_system_interface_gateway_get_more_interfaces' => '获取更多接口',
    'auth_system_interface_gateway_deactivate_enable_interface' => '停/启用支付接口',
    'auth_system_interface_gateway_interface_configuration' => '配置支付接口',
    'auth_system_interface_gateway_install_uninstall_interface' => '安装/卸载支付接口',
    'auth_upstream_downstream' => '上下游管理',
    'auth_upstream_downstream_supplier' => '供应商管理',
    'auth_upstream_downstream_supplier_view' => '查看页面',
    'auth_upstream_downstream_supplier_create_supplier' => '添加供应商',
    'auth_upstream_downstream_supplier_update_supplier' => '编辑供应商',
    'auth_upstream_downstream_supplier_delete_supplier' => '删除供应商',
    'auth_upstream_downstream_supplier_detail' => '查看供应商详情',
    'auth_upstream_downstream_supplier_detail_order_list' => '订单列表',
    'auth_upstream_downstream_supplier_detail_order_list_view' => '查看页面',
    'auth_upstream_downstream_supplier_detail_order_list_check_order' => '查看订单详情',
    'auth_upstream_downstream_supplier_detail_order_list_update_order' => '编辑订单',
    'auth_upstream_downstream_supplier_detail_order_list_delete_order' => '删除订单',
    'auth_upstream_downstream_supplier_detail_product_list' => '商品列表',
    'auth_upstream_downstream_supplier_detail_product_list_view' => '查看页面',
    'auth_upstream_downstream_supplier_detail_product_list_product_show_hide' => '商品显隐开关',
    'auth_upstream_downstream_supplier_detail_product_list_update_upstream_product' => '编辑上游商品',
    'auth_upstream_downstream_supplier_detail_product_list_delete_upstream_product' => '删除上游商品',
    'auth_upstream_downstream_supplier_detail_host_list' => '产品列表',
    'auth_upstream_downstream_supplier_detail_host_list_view' => '查看页面',
    'auth_upstream_downstream_supplier_detail_host_list_check_host_detail' => '查看产品详情',
    'auth_upstream_downstream_upstream_order' => '上游订单管理',
    'auth_upstream_downstream_upstream_order_view' => '查看页面',
    'auth_upstream_downstream_upstream_order_check_order' => '查看订单详情',
    'auth_upstream_downstream_upstream_order_update_order' => '编辑订单',
    'auth_upstream_downstream_upstream_order_delete_order' => '删除订单',
    'auth_upstream_downstream_upstream_order_agent' => '立即代理',
    'auth_upstream_downstream_upstream_host' => '上游产品管理',
    'auth_upstream_downstream_upstream_host_view' => '查看页面',
    'auth_upstream_downstream_upstream_host_check_host_detail' => '查看产品详情',
    'auth_upstream_downstream_upstream_product' => '上游商品管理',
    'auth_upstream_downstream_upstream_product_view' => '查看页面',
    'auth_upstream_downstream_upstream_product_create_group' => '新增分组',
    'auth_upstream_downstream_upstream_product_create_upstream_product' => '添加上游商品',
    'auth_upstream_downstream_upstream_product_show_hide' => '商品显隐开关',
    'auth_upstream_downstream_upstream_product_update_upstream_product' => '编辑上游商品',
    'auth_upstream_downstream_upstream_product_delete_upstream_product' => '删除上游商品',
    'auth_management' => '管理',
    'auth_management_task' => '任务',
    'auth_management_task_view' => '查看页面',
    'auth_management_task_retry' => '重新执行',
    'auth_management_log' => '日志',
    'auth_management_log_system_log' => '系统日志',
    'auth_management_log_notice_log' => '通知日志',
    'auth_management_cron' => '自动化',
    'auth_management_cron_view' => '查看页面',
    'auth_management_cron_save_cron' => '保存自动化任务',
    'auth_site_management' => '站务管理',
    'auth_site_management_news' => '新闻中心',
    'auth_site_management_news_view' => '查看页面',
    'auth_site_management_news_create_news' => '新增新闻',
    'auth_site_management_news_type' => '新闻分类管理',
    'auth_site_management_news_show_hide' => '显隐开关',
    'auth_site_management_news_update_news' => '编辑新闻',
    'auth_site_management_news_delete_news' => '删除新闻',
    'auth_site_management_help' => '帮助中心',
    'auth_site_management_help_view' => '查看页面',
    'auth_site_management_help_create_help' => '新增文档',
    'auth_site_management_help_index' => '首页管理',
    'auth_site_management_help_type' => '帮助分类管理',
    'auth_site_management_help_show_hide' => '显隐开关',
    'auth_site_management_help_update_help' => '编辑文档',
    'auth_site_management_help_delete_help' => '删除文档',
    'auth_site_management_announcement' => '公告中心',
    'auth_site_management_announcement_view' => '查看页面',
    'auth_site_management_announcement_create_announcement' => '新增公告',
    'auth_site_management_announcement_type' => '公告分类管理',
    'auth_site_management_announcement_show_hide' => '显隐开关',
    'auth_site_management_announcement_update_announcement' => '编辑公告',
    'auth_site_management_announcement_delete_announcement' => '删除公告',
    'auth_site_management_file_download' => '文件下载',
    'auth_site_management_file_download_view' => '查看页面',
    'auth_site_management_file_download_upload_file' => '上传文件',
    'auth_site_management_file_download_update_file' => '编辑文件',
    'auth_site_management_file_download_move_file' => '移动文件',
    'auth_site_management_file_download_delete_file' => '删除文件',
    'auth_site_management_file_download_file_order' => '拖动文件排序',
    'auth_site_management_file_download_file_show_hide' => '文件显隐开关',
    'auth_site_management_file_download_file_folder' => '文件夹管理',
    'auth_app' => '应用',
    'auth_app_list' => '应用列表',
    'auth_app_list_view' => '查看页面',
    'auth_app_list_more_app' => '更多应用',
    'auth_app_list_sync_app' => '同步应用',
    'auth_app_list_sync_app_download_upgrade' => '下载/升级',
    'auth_app_list_plugin_hook_order' => '插件hook排序',
    'auth_app_list_upgrade' => '升级',
    'auth_app_list_deactivate_enable_app' => '停/启用应用',
    'auth_app_list_install_uninstall_app' => '安装/卸载应用',
];

$auth_plugin = [
    #工单
    'auth_user_detail_ticket' => 'addon,IdcsmartTicket', 
    'auth_user_detail_ticket_view' => 'addon,IdcsmartTicket', 
    'auth_user_detail_ticket_transfer_ticket' => 'addon,IdcsmartTicket',
    //'auth_user_detail_ticket_create_internal_ticket' => 'addon,IdcsmartTicketInternal',
    'auth_user_detail_ticket_close_ticket' => 'addon,IdcsmartTicket',
    'auth_user_detail_ticket_detail' => 'addon,IdcsmartTicket',
    'auth_user_ticket' => 'addon,IdcsmartTicket',
    'auth_user_ticket_list' => 'addon,IdcsmartTicket',
    'auth_user_ticket_list_view' => 'addon,IdcsmartTicket',
    'auth_user_ticket_list_create_ticket' => 'addon,IdcsmartTicket',
    'auth_user_ticket_list_transfer_ticket' => 'addon,IdcsmartTicket',
    //'auth_user_ticket_list_create_internal_ticket' => 'addon,IdcsmartTicketInternal',
    'auth_user_ticket_list_close_ticket' => 'addon,IdcsmartTicket',
    'auth_user_ticket_list_ticket_detail' => 'addon,IdcsmartTicket',
    'auth_user_ticket_configuration' => 'addon,IdcsmartTicket',
    'auth_user_ticket_configuration_view' => 'addon,IdcsmartTicket',
    'auth_user_ticket_configuration_ticket_department' => 'addon,IdcsmartTicket',
    'auth_user_ticket_configuration_ticket_status' => 'addon,IdcsmartTicket',
    'auth_user_ticket_configuration_save_ticket_notice' => 'addon,IdcsmartTicket',
    'auth_user_ticket_configuration_prereply' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail_view' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail_reply_ticket' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail_create_notes' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail_use_prereply' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail_ticket_log' => 'addon,IdcsmartTicket',
    'auth_user_ticket_detail_save_ticket' => 'addon,IdcsmartTicket',
    #实名
    'auth_user_certification' => 'addon,IdcsmartCertification',
    'auth_user_certification_approval' => 'addon,IdcsmartCertification',
    'auth_user_certification_approval_view' => 'addon,IdcsmartCertification',
    'auth_user_certification_approval_pass_approval' => 'addon,IdcsmartCertification',
    'auth_user_certification_approval_deny_approval' => 'addon,IdcsmartCertification',
    'auth_user_certification_approval_certification_detail' => 'addon,IdcsmartCertification',
    'auth_user_certification_configuration' => 'addon,IdcsmartCertification',
    'auth_user_certification_configuration_view' => 'addon,IdcsmartCertification',
    'auth_user_certification_configuration_save_configuration' => 'addon,IdcsmartCertification',
    'auth_user_certification_interface' => 'addon,IdcsmartCertification',
    'auth_user_certification_interface_view' => 'addon,IdcsmartCertification',
    'auth_user_certification_interface_jump_app_store' => 'addon,IdcsmartCertification',
    'auth_user_certification_interface_configure_interface' => 'addon,IdcsmartCertification',
    'auth_user_certification_interface_deactivate_enable_interface' => 'addon,IdcsmartCertification',
    'auth_user_certification_interface_install_uninstall_interface' => 'addon,IdcsmartCertification',
    #退款
    'auth_user_refund' => 'addon,IdcsmartRefund',
    'auth_user_refund_apply_list' => 'addon,IdcsmartRefund',
    'auth_user_refund_apply_list_view' => 'addon,IdcsmartRefund',
    'auth_user_refund_apply_list_approve' => 'addon,IdcsmartRefund',
    'auth_user_refund_apply_list_reject' => 'addon,IdcsmartRefund',
    'auth_user_refund_apply_list_cancel_apply' => 'addon,IdcsmartRefund',
    'auth_user_refund_product' => 'addon,IdcsmartRefund',
    'auth_user_refund_product_view' => 'addon,IdcsmartRefund',
    'auth_user_refund_product_create_product' => 'addon,IdcsmartRefund',
    'auth_user_refund_product_suspend_reason' => 'addon,IdcsmartRefund',
    'auth_user_refund_product_update_product' => 'addon,IdcsmartRefund',
    'auth_user_refund_product_delete_product' => 'addon,IdcsmartRefund',
    #提现
    'auth_business_withdraw' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_apply_list' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_apply_list_view' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_apply_list_approve_reject' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_apply_list_reject_status_edit' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_apply_list_approve_status_edit' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_apply_list_confirm_status_edit' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_credit_withdraw_configuration' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_credit_withdraw_configuration_view' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_credit_withdraw_configuration_save_configuration' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_configuration' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_configuration_view' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_configuration_withdraw_method' => 'addon,IdcsmartWithdraw',
    'auth_business_withdraw_configuration_reject_reason' => 'addon,IdcsmartWithdraw',
    #优惠码
    'auth_product_promo_code' => 'addon,PromoCode',
    'auth_product_promo_code_view' => 'addon,PromoCode',
    'auth_product_promo_code_create_promo_code' => 'addon,PromoCode',
    'auth_product_promo_code_deactivate_enable_promo_code' => 'addon,PromoCode',
    'auth_product_promo_code_update_promo_code' => 'addon,PromoCode',
    #新闻中心
    'auth_site_management_news' => 'addon,IdcsmartNews',
    'auth_site_management_news_view' => 'addon,IdcsmartNews',
    'auth_site_management_news_create_news' => 'addon,IdcsmartNews',
    'auth_site_management_news_type' => 'addon,IdcsmartNews',
    'auth_site_management_news_show_hide' => 'addon,IdcsmartNews',
    'auth_site_management_news_update_news' => 'addon,IdcsmartNews',
    'auth_site_management_news_delete_news' => 'addon,IdcsmartNews',
    #帮助中心
    'auth_site_management_help' => 'addon,IdcsmartHelp',
    'auth_site_management_help_view' => 'addon,IdcsmartHelp',
    'auth_site_management_help_create_help' => 'addon,IdcsmartHelp',
    'auth_site_management_help_index' => 'addon,IdcsmartHelp',
    'auth_site_management_help_type' => 'addon,IdcsmartHelp',
    'auth_site_management_help_show_hide' => 'addon,IdcsmartHelp',
    'auth_site_management_help_update_help' => 'addon,IdcsmartHelp',
    'auth_site_management_help_delete_help' => 'addon,IdcsmartHelp',
    #公告中心
    'auth_site_management_announcement' => 'addon,IdcsmartAnnouncement',
    'auth_site_management_announcement_view' => 'addon,IdcsmartAnnouncement',
    'auth_site_management_announcement_create_announcement' => 'addon,IdcsmartAnnouncement',
    'auth_site_management_announcement_type' => 'addon,IdcsmartAnnouncement',
    'auth_site_management_announcement_show_hide' => 'addon,IdcsmartAnnouncement',
    'auth_site_management_announcement_update_announcement' => 'addon,IdcsmartAnnouncement',
    'auth_site_management_announcement_delete_announcement' => 'addon,IdcsmartAnnouncement',
    #文件下载
    'auth_site_management_file_download' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_view' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_upload_file' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_update_file' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_move_file' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_delete_file' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_file_order' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_file_show_hide' => 'addon,IdcsmartFileDownload',
    'auth_site_management_file_download_file_folder' => 'addon,IdcsmartFileDownload',
];

$auth_url = [
    'auth_index' => 'index.htm',
    'auth_user_list_view' => 'client.htm',
    'auth_user_detail_personal_information_view' => 'client_detail.htm',
    'auth_user_detail_host_info_view' => 'client_host.htm',
    'auth_user_detail_order_view' => 'client_order.htm',
    'auth_user_detail_order_create_order' => 'create_order.htm',
    'auth_user_detail_transaction_view' => 'client_transaction.htm',
    'auth_user_detail_operation_log' => 'client_log.htm',
    'auth_user_detail_notification_log_sms_notification' => 'client_notice_sms.htm',
    'auth_user_detail_notification_log_email_notification' => 'client_notice_email.htm',
    'auth_user_detail_ticket_view' => 'plugin/idcsmart_ticket/client_ticket.htm', 
    //'auth_user_detail_ticket_create_internal_ticket' => 'plugin/idcsmart_ticket_internal/ticket_internal_add.htm',
    'auth_user_detail_info_record_view' => 'client_records.htm',
    'auth_user_ticket_list_view' => 'plugin/idcsmart_ticket/index.htm',
    'auth_user_ticket_list_create_ticket' => 'plugin/idcsmart_ticket/ticket_add.htm',
    //'auth_user_ticket_list_create_internal_ticket' => 'plugin/idcsmart_ticket_internal/ticket_internal_add.htm',
    'auth_user_ticket_configuration_view' => 'plugin/idcsmart_ticket/ticket_setting.htm',
    'auth_user_ticket_detail_view' => 'plugin/idcsmart_ticket/ticket_detail.htm',
    'auth_user_certification_approval_view' => 'plugin/idcsmart_certification/index.htm',
    'auth_user_certification_configuration_view' => 'plugin/idcsmart_certification/real_name_setting.htm',
    'auth_user_certification_interface_view' => 'plugin/idcsmart_certification/real_name_interface.htm',
    'auth_user_refund_apply_list_view' => 'plugin/idcsmart_refund/index.htm',
    'auth_user_refund_product_view' => 'plugin/idcsmart_refund/refund.htm',
    'auth_user_refund_product_create_product' => 'plugin/idcsmart_refund/add_refund_product.htm',
    'auth_user_refund_product_update_product' => 'plugin/idcsmart_refund/add_refund_product.htm',
    'auth_business_order_view' => 'order.htm',
    'auth_business_order_create_order' => 'create_order.htm',
    'auth_business_order_detail_order_detail_view' => 'order_details.htm',
    'auth_business_order_detail_refund_record_view' => 'order_refund.htm',
    'auth_business_order_detail_transaction' => 'order_flow.htm',
    'auth_business_order_detail_notes_view' => 'order_notes.htm',
    'auth_business_order_recycle_bin_view' => 'order_recycle_bin.htm',
    'auth_business_host_view' => 'host.htm',
    'auth_business_host_detail_view' => 'host_detail.htm',
    'auth_business_transaction_view' => 'transaction.htm',
    'auth_business_withdraw_apply_list_view' => 'plugin/idcsmart_withdraw/index.htm',
    'auth_business_withdraw_credit_withdraw_configuration_view' => 'plugin/idcsmart_withdraw/balance_withdrawal_settings.htm',
    'auth_business_withdraw_configuration_view' => 'plugin/idcsmart_withdraw/withdrawal_setting.htm',
    'auth_product_management_view' => 'product.htm',
    'auth_product_management_agent_product' => 'agentList.htm',
    'auth_product_detail_basic_info_view' => 'product_detail.htm',
    'auth_product_detail_server_view' => 'product_api.htm',
    'auth_product_detail_custom_field_view' => 'product_self_field.htm',
    'auth_product_server_view' => 'server.htm',
    'auth_product_server_sub_server_sub_server_view' => 'child_server.htm',
    'auth_product_server_sub_server_group_view' => 'child_server_group.htm',
    'auth_product_server_group_view' => 'server_group.htm',
    'auth_product_promo_code_view' => 'plugin/promo_code/index.htm',
    'auth_product_promo_code_create_promo_code' => 'plugin/promo_code/create_promo_code.htm',
    'auth_product_promo_code_update_promo_code' => 'plugin/promo_code/create_promo_code.htm',
    'auth_system_configuration_system_configuration_system_configuration_view' => 'configuration_system.htm',
    'auth_system_configuration_system_configuration_debug' => 'configuration_debug.htm',
    'auth_system_configuration_system_configuration_access_configuration_view' => 'configuration_login.htm',
    'auth_system_configuration_system_configuration_theme_configuration_view' => 'configuration_theme.htm',
    'auth_system_configuration_system_configuration_web_configuration' => 'info_config.htm',
    'auth_system_configuration_system_configuration_system_info_view' => 'configuration_upgrade.htm',
    'auth_system_configuration_admin_management_view' => 'admin.htm',
    'auth_system_configuration_admin_group_view' => 'admin_role.htm',
    'auth_system_configuration_captcha_configuration_captcha_configuration_view' => 'configuration_security.htm',
    'auth_system_configuration_captcha_configuration_captcha_interface_view' => 'captcha.htm',
    'auth_system_configuration_currency_configuration_view' => 'configuration_currency.htm',
    'auth_system_configuration_menu_home_menu_view' => 'navigation.htm',
    'auth_system_configuration_menu_admin_menu_view' => 'navigation.htm',
    'auth_system_configuration_web_feedback' => 'template.htm',
    'auth_system_configuration_oauth_view' => 'oauth.htm',
    'auth_system_interface_sms_notice_view' => 'notice_sms.htm',
    'auth_system_interface_sms_notice_sms_template_view' => 'notice_sms_template.htm',
    'auth_system_interface_email_notice_view' => 'notice_email.htm',
    'auth_system_interface_email_notice_email_template_view' => 'notice_email_template.htm',
    'auth_system_interface_email_notice_email_template_create_template' => 'notice_email_template_create.htm',
    'auth_system_interface_email_notice_email_template_update_template' => 'notice_email_template_update.htm',
    'auth_system_interface_send_configuration_view' => 'notice_send.htm',
    'auth_system_interface_gateway_view' => 'gateway.htm',
    'auth_upstream_downstream_supplier_view' => 'supplier_list.htm',
    'auth_upstream_downstream_supplier_detail_order_list_view' => 'supplier_order.htm',
    'auth_upstream_downstream_supplier_detail_product_list_view' => 'supplier_goods.htm',
    'auth_upstream_downstream_supplier_detail_host_list_view' => 'supplier_product.htm',
    'auth_upstream_downstream_upstream_order_view' => 'upstream_order.htm',
    'auth_upstream_downstream_upstream_order_agent' => 'agentList.htm',
    'auth_upstream_downstream_upstream_host_view' => 'upstream_product.htm',
    'auth_upstream_downstream_upstream_product_view' => 'upstream_goods.htm',
    'auth_management_task_view' => 'task.htm',
    'auth_management_log_system_log' => 'log_system.htm',
    'auth_management_log_notice_log' => 'log_notice_sms.htm',
    'auth_management_cron_view' => 'cron.htm',
    'auth_site_management_news_view' => 'plugin/idcsmart_news/index.htm',
    'auth_site_management_news_create_news' => 'plugin/idcsmart_news/news_create.htm',
    'auth_site_management_news_update_news' => 'plugin/idcsmart_news/news_create.htm',
    'auth_site_management_help_view' => 'plugin/idcsmart_help/index.htm',
    'auth_site_management_help_create_help' => 'plugin/idcsmart_help/help_create.htm',
    'auth_site_management_help_index' => 'plugin/idcsmart_help/help_index.htm',
    'auth_site_management_help_update_help' => 'plugin/idcsmart_help/help_create.htm',
    'auth_site_management_announcement_view' => 'plugin/idcsmart_announcement/index.htm',
    'auth_site_management_announcement_create_announcement' => 'plugin/idcsmart_announcement/announcement_create.htm',
    'auth_site_management_announcement_update_announcement' => 'plugin/idcsmart_announcement/announcement_create.htm',
    'auth_site_management_file_download_view' => 'plugin/idcsmart_file_download/index.htm',
    'auth_app_list_view' => 'plugin.htm',
];

$auth_rule = [
    'auth_index' => [
        'app\admin\controller\WidgetController::index',
        'app\admin\controller\WidgetController::output',
    ],
    'auth_user' => [],
    'auth_user_list' => [],
    'auth_user_list_view' => [
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_list_create_user' => [
        'app\admin\controller\ClientController::create',
    ],
    'auth_user_detail' => [],
    'auth_user_detail_personal_information' => [],
    'auth_user_detail_personal_information_view' => [
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ConfigurationController::systemList',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_personal_information_recharge' => [
        'app\admin\controller\ClientCreditController::recharge',
    ],
    'auth_user_detail_personal_information_change_credit' => [
        'app\admin\controller\ClientCreditController::update',
    ],
    'auth_user_detail_personal_information_change_credit_log' => [
        'app\admin\controller\ClientCreditController::clientCreditList',
    ],
    'auth_user_detail_personal_information_user_login' => [
        'app\admin\controller\ClientController::login',
    ],
    'auth_user_detail_personal_information_save_user_info' => [
        'app\admin\controller\ClientController::update',
    ],
    'auth_user_detail_personal_information_deactivate_enable_user' => [
        'app\admin\controller\ClientController::status',
    ],
    'auth_user_detail_personal_information_delete_user' => [
        'app\admin\controller\ClientController::delete',
    ],
    'auth_user_detail_host_info' => [],
    'auth_user_detail_host_info_view' => [
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_host_info_batch_renew' => [
        'addon\idcsmart_renew\controller\AdminIndexController::renewBatchPage',
        'addon\idcsmart_renew\controller\AdminIndexController::renewBatch',
    ],
    'auth_user_detail_host_info_batch_delete' => [
        'app\admin\controller\HostController::batchDelete',
    ],
    'auth_user_detail_host_info_host_detail' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\HostController::index',
        'app\admin\controller\HostController::adminArea',
        'app\admin\controller\HostController::moduleButton',
        'app\admin\controller\HostController::moduleField',
        'app\admin\controller\UpstreamHostController::index',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_user_detail_order' => [],
    'auth_user_detail_order_view' => [
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\OrderController::orderList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_order_create_order' => [
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\OrderController::create',
        'app\admin\controller\ClientController::login',
    ],
    'auth_user_detail_order_check_order' => [
        'app\admin\controller\OrderController::index',
        'app\admin\controller\ClientController::index',
    ],
    'auth_user_detail_order_adjust_order_amount' => [
        'app\admin\controller\OrderController::updateAmount',
    ],
    'auth_user_detail_order_delete_order' => [
        'app\admin\controller\OrderController::delete',
    ],
    'auth_user_detail_transaction' => [],
    'auth_user_detail_transaction_view' => [
        'app\admin\controller\TransactionController::transactionList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_transaction_create_transaction' => [
        'app\admin\controller\TransactionController::create',
    ],
    'auth_user_detail_transaction_update_transaction' => [
        'app\admin\controller\TransactionController::update',
    ],
    'auth_user_detail_transaction_delete_transaction' => [
        'app\admin\controller\TransactionController::delete',
    ],
    'auth_user_detail_operation_log' => [
        'app\admin\controller\LogController::systemLogList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_notification_log' => [],
    'auth_user_detail_notification_log_sms_notification' => [
        'app\admin\controller\LogController::smsLogList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_notification_log_email_notification' => [
        'app\admin\controller\LogController::emailLogList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_ticket' => [], 
    'auth_user_detail_ticket_view' => [
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\AdminRoleController::adminRoleList',
        'app\admin\controller\AdminController::adminList',
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketController::ticketList',
        'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
    ],
    'auth_user_detail_ticket_transfer_ticket' => [
        'addon\idcsmart_ticket\controller\TicketController::forward',
    ],
    // 'auth_user_detail_ticket_create_internal_ticket' => [
    //  'addon\idcsmart_ticket_internal\controller\TicketInternalTypeController::ticketTypeList',
    //  'addon\idcsmart_ticket_internal\controller\TicketInternalController::department',
    //  'addon\idcsmart_ticket_internal\controller\TicketInternalController::create',
    //  'app\admin\controller\ClientController::clientList',
    // ],
    'auth_user_detail_ticket_close_ticket' => [
        'addon\idcsmart_ticket\controller\TicketController::resolved',
    ],
    'auth_user_detail_ticket_detail' => [
        'addon\idcsmart_ticket\controller\TicketController::index',
        'addon\idcsmart_ticket\controller\TicketController::ticketLog',
        'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
        'addon\idcsmart_ticket\controller\TicketNotesController::ticketNotesList',
        'app\admin\controller\HostController::hostList',
    ],
    'auth_user_detail_info_record' => [],
    'auth_user_detail_info_record_view' => [
        'app\admin\controller\ClientRecordController::list',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_detail_info_record_create_record' => [
        'app\admin\controller\ClientRecordController::create',
    ],
    'auth_user_detail_info_record_update_record' => [
        'app\admin\controller\ClientRecordController::update',
    ],
    'auth_user_detail_info_record_delete_record' => [
        'app\admin\controller\ClientRecordController::delete',
    ],
    'auth_user_ticket' => [], 
    'auth_user_ticket_list' => [], 
    'auth_user_ticket_list_view' => [
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\AdminRoleController::adminRoleList',
        'app\admin\controller\AdminController::adminList',
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketController::ticketList',
        'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
    ], 
    'auth_user_ticket_list_create_ticket' => [
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketController::department',
        'addon\idcsmart_ticket\controller\TicketController::create',
        'app\admin\controller\ClientController::clientList',
    ],
    'auth_user_ticket_list_transfer_ticket' => [
        'addon\idcsmart_ticket\controller\TicketController::forward',
    ],
    // 'auth_user_ticket_list_create_internal_ticket' => [
    //  'addon\idcsmart_ticket_internal\controller\TicketInternalTypeController::ticketTypeList',
    //  'addon\idcsmart_ticket_internal\controller\TicketInternalController::department',
    //  'addon\idcsmart_ticket_internal\controller\TicketInternalController::create',
    //  'app\admin\controller\ClientController::clientList',
    // ],
    'auth_user_ticket_list_close_ticket' => [
        'addon\idcsmart_ticket\controller\TicketController::resolved',
    ],
    'auth_user_ticket_list_ticket_detail' => [
        'addon\idcsmart_ticket\controller\TicketController::index',
        'addon\idcsmart_ticket\controller\TicketController::ticketLog',
        'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
        'addon\idcsmart_ticket\controller\TicketNotesController::ticketNotesList',
        'app\admin\controller\HostController::hostList',
    ],
    'auth_user_ticket_configuration' => [],
    'auth_user_ticket_configuration_view' => [
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
        'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
        'addon\idcsmart_ticket\controller\TicketController::getConfig',
        'app\admin\controller\AdminController::adminList',
    ],
    'auth_user_ticket_configuration_ticket_department' => [
        'addon\idcsmart_ticket\controller\TicketTypeController::create',
        'addon\idcsmart_ticket\controller\TicketTypeController::update',
        'addon\idcsmart_ticket\controller\TicketTypeController::delete',
    ],
    'auth_user_ticket_configuration_ticket_status' => [
        'addon\idcsmart_ticket\controller\TicketStatusController::create',
        'addon\idcsmart_ticket\controller\TicketStatusController::update',
        'addon\idcsmart_ticket\controller\TicketStatusController::delete',
    ],
    'auth_user_ticket_configuration_save_ticket_notice' => [
        'addon\idcsmart_ticket\controller\TicketController::setConfig',
    ],
    'auth_user_ticket_configuration_prereply' => [
        'addon\idcsmart_ticket\controller\TicketPrereplyController::create',
        'addon\idcsmart_ticket\controller\TicketPrereplyController::update',
        'addon\idcsmart_ticket\controller\TicketPrereplyController::delete',
    ],
    'auth_user_ticket_detail' => [],
    'auth_user_ticket_detail_view' => [
        'addon\idcsmart_ticket\controller\TicketController::index',
        'addon\idcsmart_ticket\controller\TicketController::ticketLog',
        'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
        'addon\idcsmart_ticket\controller\TicketTypeController::ticketTypeList',
        'addon\idcsmart_ticket\controller\TicketStatusController::ticketStatusList',
        'addon\idcsmart_ticket\controller\TicketNotesController::ticketNotesList',
        'app\admin\controller\HostController::hostList',
    ],
    'auth_user_ticket_detail_reply_ticket' => [
        'addon\idcsmart_ticket\controller\TicketController::reply',
        'addon\idcsmart_ticket\controller\TicketController::ticketReplyUpdate',
        'addon\idcsmart_ticket\controller\TicketController::ticketReplyDelete',
    ],
    'auth_user_ticket_detail_create_notes' => [
        'addon\idcsmart_ticket\controller\TicketNotesController::create',
        'addon\idcsmart_ticket\controller\TicketNotesController::update',
        'addon\idcsmart_ticket\controller\TicketNotesController::delete',
    ],
    'auth_user_ticket_detail_use_prereply' => [
        'addon\idcsmart_ticket\controller\TicketPrereplyController::ticketPrereplyList',
    ],
    'auth_user_ticket_detail_ticket_log' => [
        'addon\idcsmart_ticket\controller\TicketController::ticketLog',
    ],
    'auth_user_ticket_detail_save_ticket' => [
        'addon\idcsmart_ticket\controller\TicketController::status',
    ],
    'auth_user_certification' => [],
    'auth_user_certification_approval' => [],
    'auth_user_certification_approval_view' => [
        'addon\idcsmart_certification\controller\CertificationController::certificationList',
    ],
    'auth_user_certification_approval_pass_approval' => [
        'addon\idcsmart_certification\controller\CertificationController::approve',
    ],
    'auth_user_certification_approval_deny_approval' => [
        'addon\idcsmart_certification\controller\CertificationController::reject',
    ],
    'auth_user_certification_approval_certification_detail' => [
        'addon\idcsmart_certification\controller\CertificationController::index',
    ],
    'auth_user_certification_configuration' => [],
    'auth_user_certification_configuration_view' => [
        'addon\idcsmart_certification\controller\CertificationController::getConfig',
    ],
    'auth_user_certification_configuration_save_configuration' => [
        'addon\idcsmart_certification\controller\CertificationController::setConfig',
    ],
    'auth_user_certification_interface' => [],
    'auth_user_certification_interface_view' => [
        'app\admin\controller\PluginController::certificationPluginList',
    ],
    'auth_user_certification_interface_jump_app_store' => [],
    'auth_user_certification_interface_configure_interface' => [
        'app\admin\controller\PluginController::certificationSetting',
        'app\admin\controller\PluginController::certificationSettingPost',
    ],
    'auth_user_certification_interface_deactivate_enable_interface' => [
        'app\admin\controller\PluginController::certificationStatus',
    ],
    'auth_user_certification_interface_install_uninstall_interface' => [
        'app\admin\controller\PluginController::certificationInstall',
        'app\admin\controller\PluginController::certificationUninstall',
    ],
    'auth_user_refund' => [],
    'auth_user_refund_apply_list' => [],
    'auth_user_refund_apply_list_view' => [
        'addon\idcsmart_refund\controller\RefundController::refundList',
    ],
    'auth_user_refund_apply_list_approve' => [
        'addon\idcsmart_refund\controller\RefundController::pending',
    ],
    'auth_user_refund_apply_list_reject' => [
        'addon\idcsmart_refund\controller\RefundController::reject',
    ],
    'auth_user_refund_apply_list_cancel_apply' => [
        'addon\idcsmart_refund\controller\RefundController::cancel',
    ],
    'auth_user_refund_product' => [],
    'auth_user_refund_product_view' => [
        'addon\idcsmart_refund\controller\RefundProductController::refundProductList',
    ],
    'auth_user_refund_product_create_product' => [
        'addon\idcsmart_refund\controller\RefundProductController::create',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_user_refund_product_suspend_reason' => [
        'addon\idcsmart_refund\controller\RefundReasonController::refundReasonList',
        'addon\idcsmart_refund\controller\RefundReasonController::create',
        'addon\idcsmart_refund\controller\RefundReasonController::update',
        'addon\idcsmart_refund\controller\RefundReasonController::delete',
        'addon\idcsmart_refund\controller\RefundReasonController::index',
        'addon\idcsmart_refund\controller\RefundReasonController::custom',
        'addon\idcsmart_refund\controller\RefundReasonController::customSet',
    ],
    'auth_user_refund_product_update_product' => [
        'addon\idcsmart_refund\controller\RefundProductController::index',
        'addon\idcsmart_refund\controller\RefundProductController::update',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_user_refund_product_delete_product' => [
        'addon\idcsmart_refund\controller\RefundProductController::delete',
        'app\admin\controller\OrderController::getOrderRecycleBinConfig',
    ],
    'auth_business' => [],
    'auth_business_order' => [],
    'auth_business_order_view' => [
        'app\admin\controller\OrderController::orderList',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_business_order_create_order' => [
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\OrderController::create',
        'app\admin\controller\ClientController::login',
    ],
    'auth_business_order_batch_delete_order' => [
        'app\admin\controller\OrderController::batchDelete',
    ],
    'auth_business_order_check_order' => [
        'app\admin\controller\OrderController::index',
        'app\admin\controller\ClientController::index',
    ],
    'auth_business_order_adjust_order_amount' => [
        'app\admin\controller\OrderController::updateAmount',
    ],
    'auth_business_order_delete_order' => [
        'app\admin\controller\OrderController::delete',
    ],
    'auth_business_order_enable_recycle_bin' => [
        'app\admin\controller\OrderController::enableOrderRecycleBin',
    ],
    'auth_business_order_recycle_bin' => [],
    'auth_business_order_recycle_bin_view' => [
        'app\admin\controller\OrderController::recycleBinOrderList',
    ],
    'auth_business_order_recycle_bin_config' => [
        'app\admin\controller\OrderController::getOrderRecycleBinConfig',
        'app\admin\controller\OrderController::orderRecycleBinConfigUpdate',
    ],
    'auth_business_order_recycle_bin_order_detail' => [
        'app\admin\controller\OrderController::index',
        'app\admin\controller\ClientController::index',
    ],
    'auth_business_order_recycle_bin_recover_order' => [
        'app\admin\controller\OrderController::recoverOrder',
    ],
    'auth_business_order_recycle_bin_delete_order' => [
        'app\admin\controller\OrderController::deleteOrderFromRecycleBin',
    ],
    'auth_business_order_recycle_bin_clear' => [
        'app\admin\controller\OrderController::clearRecycleBin',
    ],
    'auth_business_order_recycle_bin_lock_order' => [
        'app\admin\controller\OrderController::lockOrder',
    ],
    'auth_business_order_recycle_bin_unlock_order' => [
        'app\admin\controller\OrderController::unlockOrder',
    ],
    'auth_business_order_detail' => [],
    'auth_business_order_detail_order_detail' => [],
    'auth_business_order_detail_order_detail_view' => [
        'app\admin\controller\OrderController::index',
        'app\admin\controller\ClientController::index',
    ],
    'auth_business_order_detail_order_detail_paid' => [
        'app\admin\controller\OrderController::paid',
        'app\admin\controller\OrderController::updateGateway',
    ],
    'auth_business_order_detail_order_detail_apply_credit' => [
        'app\admin\controller\OrderController::orderApplyCredit',
    ],
    'auth_business_order_detail_order_detail_remove_credit' => [
        'app\admin\controller\OrderController::orderRemoveCredit',
    ],
    'auth_business_order_detail_order_detail_change_log' => [
        'app\admin\controller\ClientCreditController::clientCreditList',
    ],
    'auth_business_order_detail_order_detail_delete_order_item' => [
        'app\admin\controller\OrderController::deleteOrderItem',
    ],
    'auth_business_order_detail_order_detail_save_order_item' => [
        'app\admin\controller\OrderController::updateOrderItem',
    ],
    'auth_business_order_detail_order_detail_create_order_item' => [
        'app\admin\controller\OrderController::updateAmount',
    ],
    'auth_business_order_detail_refund_record' => [],
    'auth_business_order_detail_refund_record_view' => [
        'app\admin\controller\OrderController::refundRecordList',
        'app\admin\controller\OrderController::index',
    ],
    'auth_business_order_detail_refund_record_refund' => [
        'app\admin\controller\OrderController::orderRefund',
    ],
    'auth_business_order_detail_refund_record_delete_record' => [
        'app\admin\controller\OrderController::deleteRefundRecord',
    ],
    'auth_business_order_detail_transaction' => [
        'app\admin\controller\TransactionController::transactionList',
    ],
    'auth_business_order_detail_notes' => [],
    'auth_business_order_detail_notes_view' => [
        'app\admin\controller\OrderController::index',
    ],
    'auth_business_order_detail_notes_save_notes' => [
        'app\admin\controller\OrderController::updateNotes',
    ],
    'auth_business_host' => [],
    'auth_business_host_view' => [
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_business_host_check_host_detail' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\HostController::index',
        'app\admin\controller\HostController::adminArea',
        'app\admin\controller\HostController::moduleButton',
        'app\admin\controller\HostController::moduleField',
        'app\admin\controller\UpstreamHostController::index',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_business_host_detail' => [],
    'auth_business_host_detail_view' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\HostController::index',
        'app\admin\controller\HostController::adminArea',
        'app\admin\controller\HostController::moduleButton',
        'app\admin\controller\HostController::moduleField',
        'app\admin\controller\UpstreamHostController::index',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_business_host_detail_create_account' => [
        'app\admin\controller\HostController::createAccount',
    ],
    'auth_business_host_detail_suspend_account' => [
        'app\admin\controller\HostController::suspendAccount',
    ],
    'auth_business_host_detail_unsuspend_account' => [
        'app\admin\controller\HostController::unsuspendAccount',
    ],
    'auth_business_host_detail_terminate_account' => [
        'app\admin\controller\HostController::terminateAccount',
    ],
    'auth_business_host_detail_host_renew' => [
        'addon\idcsmart_renew\controller\AdminIndexController::renewPage',
        'addon\idcsmart_renew\controller\AdminIndexController::renew',
    ],
    'auth_business_host_detail_save_basic_finance_info' => [
        'app\admin\controller\HostController::update',
    ],
    'auth_business_host_detail_delete' => [
        'app\admin\controller\HostController::delete',
    ],
    'auth_business_host_detail_save_details' => [],
    'auth_business_host_detail_dcim_host_allot' => [],
    'auth_business_host_detail_host_transfer' => [
        'addon\host_transfer\controller\AdminIndexController::getTransferInfo',
        'addon\host_transfer\controller\AdminIndexController::hostTransfer',
    ],
    'auth_business_transaction' => [],
    'auth_business_transaction_view' => [
        'app\admin\controller\TransactionController::transactionList',
    ],
    'auth_business_transaction_create_transaction' => [
        'app\admin\controller\TransactionController::create',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\ClientController::index',
    ],
    'auth_business_transaction_update_transaction' => [
        'app\admin\controller\TransactionController::update',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\ClientController::index',
    ],
    'auth_business_transaction_delete_transaction' => [
        'app\admin\controller\TransactionController::delete',
    ],
    'auth_business_withdraw' => [],
    'auth_business_withdraw_apply_list' => [],
    'auth_business_withdraw_apply_list_view' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawList',
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawRejectReasonList',
    ],
    'auth_business_withdraw_apply_list_approve_reject' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawAudit',
    ],
    'auth_business_withdraw_apply_list_reject_status_edit' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawStatus',
    ],
    'auth_business_withdraw_apply_list_approve_status_edit' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::confirmRemit',
    ],
    'auth_business_withdraw_apply_list_confirm_status_edit' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawTransaction',
    ],
    'auth_business_withdraw_credit_withdraw_configuration' => [],
    'auth_business_withdraw_credit_withdraw_configuration_view' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawRuleCredit',
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawMethodList',
    ],
    'auth_business_withdraw_credit_withdraw_configuration_save_configuration' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::saveIdcsmartWithdrawRuleCredit',
    ],
    'auth_business_withdraw_configuration' => [],
    'auth_business_withdraw_configuration_view' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawRejectReasonList',
        'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawMethodList',
    ],
    'auth_business_withdraw_configuration_withdraw_method' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::createIdcsmartWithdrawMethod',
        'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawMethod',
        'addon\idcsmart_withdraw\controller\AdminIndexController::deleteIdcsmartWithdrawMethod',
    ],
    'auth_business_withdraw_configuration_reject_reason' => [
        'addon\idcsmart_withdraw\controller\AdminIndexController::createIdcsmartWithdrawRejectReason',
        'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawRejectReason',
        'addon\idcsmart_withdraw\controller\AdminIndexController::deleteIdcsmartWithdrawRejectReason',
    ],
    'auth_product' => [],
    'auth_product_management' => [],
    'auth_product_management_view' => [
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_product_management_create_group' => [
        'app\admin\controller\ProductGroupController::create',
    ],
    'auth_product_management_update_group' => [
        'app\admin\controller\ProductGroupController::update',
    ],
    'auth_product_management_delete_group' => [
        'app\admin\controller\ProductGroupController::delete',
    ],
    'auth_product_management_create_product' => [
        'app\admin\controller\ProductController::create',
    ],
    'auth_product_management_list_order' => [
        'app\admin\controller\ProductController::order',
        'app\admin\controller\ProductGroupController::order',
        'app\admin\controller\ProductGroupController::orderFirst',
    ],
    'auth_product_management_agent_product' => [
        'app\admin\controller\UpstreamProductController::recommendProductList',
        'app\admin\controller\UpstreamProductController::agentRecommendProduct',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_product_management_agentable_product' => [
        'app\admin\controller\ProductController::saveAgentableProduct',
    ],
    'auth_product_management_product_show_hide' => [
        'app\admin\controller\ProductController::hidden',
    ],
    'auth_product_management_product_copy' => [
        'app\admin\controller\ProductController::copy',
    ],
    'auth_product_management_update_product' => [],
    'auth_product_management_delete_product' => [
        'app\admin\controller\ProductController::delete',
    ],
    'auth_product_detail' => [],
    'auth_product_detail_basic_info' => [],
    'auth_product_detail_basic_info_view' => [
        'app\admin\controller\ProductController::index',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
        'app\admin\controller\NoticeEmailController::emailTemplateList',
        'app\admin\controller\NoticeSmsController::templateList',
    ],
    'auth_product_detail_basic_info_save_info' => [
        'app\admin\controller\ProductController::update',
    ],
    'auth_product_detail_server' => [],
    'auth_product_detail_server_view' => [
        'app\admin\controller\ProductController::index',
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ServerGroupController::serverGroupList',
    ],
    'auth_product_detail_server_save_server' => [
        'app\admin\controller\ProductController::updateServer',
    ],
    'auth_product_detail_server_product_configuration' => [
        'app\admin\controller\ProductController::moduleServerConfigOption',
    ],
    'auth_product_detail_custom_field' => [],
    'auth_product_detail_custom_field_view' => [
        'app\admin\controller\ProductController::index',
        'app\admin\controller\SelfDefinedFieldController::selfDefinedFieldList',
    ],
    'auth_product_detail_custom_field_create_field' => [
        'app\admin\controller\SelfDefinedFieldController::create',
    ],
    'auth_product_detail_custom_field_update_field' => [
        'app\admin\controller\SelfDefinedFieldController::update',
        'app\admin\controller\SelfDefinedFieldController::dragToSort',
    ],
    'auth_product_detail_custom_field_delete_field' => [
        'app\admin\controller\SelfDefinedFieldController::delete',
    ],
    'auth_product_server' => [],
    'auth_product_server_view' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ServerController::status',
        'app\admin\controller\ModuleController::moduleList',
    ],
    'auth_product_server_create_server' => [
        'app\admin\controller\ServerController::create',
    ],
    'auth_product_server_update_server' => [
        'app\admin\controller\ServerController::update',
    ],
    'auth_product_server_delete_server' => [
        'app\admin\controller\ServerController::delete',
    ],
    'auth_product_server_sub_server' => [],
    'auth_product_server_sub_server_sub_server' => [],
    'auth_product_server_sub_server_sub_server_view' => [],
    'auth_product_server_sub_server_sub_server_create_server' => [],
    'auth_product_server_sub_server_sub_server_update_server' => [],
    'auth_product_server_sub_server_sub_server_delete_server' => [],
    'auth_product_server_sub_server_group' => [],
    'auth_product_server_sub_server_group_view' => [],
    'auth_product_server_sub_server_group_create_group' => [],
    'auth_product_server_sub_server_group_update_group' => [],
    'auth_product_server_sub_server_group_delete_group' => [],
    'auth_product_server_group' => [],
    'auth_product_server_group_view' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ServerGroupController::serverGroupList',
    ],
    'auth_product_server_group_create_group' => [
        'app\admin\controller\ServerGroupController::create',
    ],
    'auth_product_server_group_update_group' => [
        'app\admin\controller\ServerGroupController::update',
    ],
    'auth_product_server_group_delete_group' => [
        'app\admin\controller\ServerGroupController::delete',
    ],
    'auth_product_promo_code' => [],
    'auth_product_promo_code_view' => [
        'addon\promo_code\controller\AdminIndexController::promoCodeList',
    ],
    'auth_product_promo_code_create_promo_code' => [
        'addon\promo_code\controller\AdminIndexController::create',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_product_promo_code_deactivate_enable_promo_code' => [
        'addon\promo_code\controller\AdminIndexController::status',
    ],
    'auth_product_promo_code_update_promo_code' => [
        'addon\promo_code\controller\AdminIndexController::index',
        'addon\promo_code\controller\AdminIndexController::update',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_system_configuration' => [],
    'auth_system_configuration_system_configuration' => [],
    'auth_system_configuration_system_configuration_system_configuration' => [],
    'auth_system_configuration_system_configuration_system_configuration_view' => [
        'app\admin\controller\ConfigurationController::systemList',
        'app\admin\controller\UpgradeSystemController::upgradeContent',
    ],
    'auth_system_configuration_system_configuration_system_configuration_save_configuration' => [
        'app\admin\controller\ConfigurationController::systemUpdate',
    ],
    'auth_system_configuration_system_configuration_debug' => [
        'app\admin\controller\ConfigurationController::debugInfo',
        'app\admin\controller\ConfigurationController::debug',
    ],
    'auth_system_configuration_system_configuration_access_configuration' => [],
    'auth_system_configuration_system_configuration_access_configuration_view' => [
        'app\admin\controller\ConfigurationController::loginList',
    ],
    'auth_system_configuration_system_configuration_access_configuration_save_configuration' => [
        'app\admin\controller\ConfigurationController::loginUpdate',
    ],
    'auth_system_configuration_system_configuration_theme_configuration' => [],
    'auth_system_configuration_system_configuration_theme_configuration_view' => [
        'app\admin\controller\ConfigurationController::themeList',
    ],
    'auth_system_configuration_system_configuration_theme_configuration_save_configuration' => [
        'app\admin\controller\ConfigurationController::themeUpdate',
    ],
    'auth_system_configuration_system_configuration_web_configuration'  => [
        'app\admin\controller\ConfigurationController::infoList',
        'app\admin\controller\ConfigurationController::infoUpdate',
        'app\admin\controller\FriendlyLinkController::list',
        'app\admin\controller\FriendlyLinkController::create',
        'app\admin\controller\FriendlyLinkController::update',
        'app\admin\controller\FriendlyLinkController::delete',
        'app\admin\controller\HonorController::list',
        'app\admin\controller\HonorController::create',
        'app\admin\controller\HonorController::update',
        'app\admin\controller\HonorController::delete',
        'app\admin\controller\PartnerController::list',
        'app\admin\controller\PartnerController::create',
        'app\admin\controller\PartnerController::update',
        'app\admin\controller\PartnerController::delete',
    ],
    'auth_system_configuration_system_configuration_system_info' => [],
    'auth_system_configuration_system_configuration_system_info_view' => [
        'app\admin\controller\UpgradeSystemController::upgradeContent',
    ],
    'auth_system_configuration_system_configuration_system_info_system_upgrade' => [
        'app\admin\controller\UpgradeSystemController::upgradeDownload',
        'app\admin\controller\UpgradeSystemController::upgradeDownloadProgress',
    ],
    'auth_system_configuration_system_configuration_system_info_change_license' => [
        'app\admin\controller\UpgradeSystemController::updateLicense',
    ],
    'auth_system_configuration_system_configuration_system_info_update_license' => [
        'app\admin\controller\UpgradeSystemController::getAuth',
    ],
    'auth_system_configuration_admin' => [],
    'auth_system_configuration_admin_management' => [],
    'auth_system_configuration_admin_management_view' => [
        'app\admin\controller\AdminController::adminList',
        'app\admin\controller\AdminRoleController::adminRoleList',
    ],
    'auth_system_configuration_admin_management_create_admin' => [
        'app\admin\controller\AdminController::create',
    ],
    'auth_system_configuration_admin_management_update_admin' => [
        'app\admin\controller\AdminController::index',
        'app\admin\controller\AdminController::update',
    ],
    'auth_system_configuration_admin_management_deactivate_enable_admin' => [
        'app\admin\controller\AdminController::status',
    ],
    'auth_system_configuration_admin_management_delete_admin' => [
        'app\admin\controller\AdminController::delete',
    ],
    'auth_system_configuration_admin_group' => [],
    'auth_system_configuration_admin_group_view' => [
        'app\admin\controller\AdminRoleController::adminRoleList',
    ],
    'auth_system_configuration_admin_group_create_group' => [
        'app\admin\controller\AdminRoleController::create',
    ],
    'auth_system_configuration_admin_group_update_group' => [
        'app\admin\controller\AdminRoleController::index',
        'app\admin\controller\AdminRoleController::update',
    ],
    'auth_system_configuration_admin_group_delete_group' => [
        'app\admin\controller\AdminRoleController::delete',
    ],
    'auth_system_configuration_captcha_configuration' => [],
    'auth_system_configuration_captcha_configuration_captcha_configuration' => [],
    'auth_system_configuration_captcha_configuration_captcha_configuration_view' => [
        'app\admin\controller\ConfigurationController::securityList',
    ],
    'auth_system_configuration_captcha_configuration_captcha_configuration_save_configuration' => [
        'app\admin\controller\ConfigurationController::securityUpdate',
    ],
    'auth_system_configuration_captcha_configuration_captcha_interface' => [],
    'auth_system_configuration_captcha_configuration_captcha_interface_view' => [
        'app\admin\controller\PluginController::captchaPluginList',
    ],
    'auth_system_configuration_captcha_configuration_captcha_interface_get_more_interfaces' => [],
    'auth_system_configuration_captcha_configuration_captcha_interface_deactivate_enable_interface' => [
        'app\admin\controller\PluginController::captchaStatus',
    ],
    'auth_system_configuration_captcha_configuration_captcha_interface_configuration' => [
        'app\admin\controller\PluginController::captchaSetting',
        'app\admin\controller\PluginController::captchaSettingPost',
    ],
    'auth_system_configuration_captcha_configuration_captcha_interface_install_uninstall_interface' => [
        'app\admin\controller\PluginController::captchaInstall',
        'app\admin\controller\PluginController::captchaUninstall',
    ],
    'auth_system_configuration_currency_configuration' => [],
    'auth_system_configuration_currency_configuration_view' => [
        'app\admin\controller\ConfigurationController::currencyList',
    ],
    'auth_system_configuration_currency_configuration_save_configuration' => [
        'app\admin\controller\ConfigurationController::currencyUpdate',
    ],
    'auth_system_configuration_menu' => [],
    'auth_system_configuration_menu_home_menu' => [],
    'auth_system_configuration_menu_home_menu_view' => [
        'app\admin\controller\MenuController::getHomeMenu',
        'app\admin\controller\ProductController::moduleProductList',
    ],
    'auth_system_configuration_menu_home_menu_save_menu' => [
        'app\admin\controller\MenuController::saveHomeMenu',
    ],
    'auth_system_configuration_menu_admin_menu' => [],
    'auth_system_configuration_menu_admin_menu_view' => [
        'app\admin\controller\MenuController::getAdminMenu',
    ],
    'auth_system_configuration_menu_admin_menu_save_menu' => [
        'app\admin\controller\MenuController::saveAdminMenu',
    ],
    'auth_system_configuration_web_feedback' => [
        'app\admin\controller\FeedbackController::feedbackList',
        'app\admin\controller\FeedbackController::feedbackTypeList',
        'app\admin\controller\FeedbackController::createFeedbackType',
        'app\admin\controller\FeedbackController::updateFeedbackType',
        'app\admin\controller\FeedbackController::deleteFeedbackType',
        'app\admin\controller\ConsultController::list',
    ],
    'auth_system_configuration_oauth' => [],
    'auth_system_configuration_oauth_view' => [
        'app\admin\controller\PluginController::oauthPluginList',
    ],
    'auth_system_configuration_oauth_get_more_interfaces' => [],
    'auth_system_configuration_oauth_deactivate_enable_interface' => [
        'app\admin\controller\PluginController::oauthStatus',
    ],
    'auth_system_configuration_oauth_interface_configuration' => [
        'app\admin\controller\PluginController::oauthSetting',
        'app\admin\controller\PluginController::oauthSettingPost',
    ],
    'auth_system_configuration_oauth_install_uninstall_interface' => [
        'app\admin\controller\PluginController::oauthInstall',
        'app\admin\controller\PluginController::oauthUninstall',
    ],
    'auth_system_interface' => [],
    'auth_system_interface_sms_notice' => [],
    'auth_system_interface_sms_notice_view' => [
        'app\admin\controller\PluginController::smsPluginList',
    ],
    'auth_system_interface_sms_notice_get_more_interfaces' => [],
    'auth_system_interface_sms_notice_deactivate_enable_interface' => [
        'app\admin\controller\PluginController::smsStatus',
    ],
    'auth_system_interface_sms_notice_sms_template' => [],
    'auth_system_interface_sms_notice_sms_template_view' => [
        'app\admin\controller\NoticeSmsController::templateList',
        'app\admin\controller\NoticeSmsController::status',
    ],
    'auth_system_interface_sms_notice_sms_template_create_template' => [
        'app\admin\controller\NoticeSmsController::create',
    ],
    'auth_system_interface_sms_notice_sms_template_batch_create_template' => [
        'app\admin\controller\NoticeSmsController::audit',
    ],
    'auth_system_interface_sms_notice_sms_template_update_template' => [
        'app\admin\controller\NoticeSmsController::index',
        'app\admin\controller\NoticeSmsController::update',
    ],
    'auth_system_interface_sms_notice_sms_template_test_template' => [
        'app\admin\controller\NoticeSmsController::test',
    ],
    'auth_system_interface_sms_notice_sms_template_delete_template' => [
        'app\admin\controller\NoticeSmsController::delete',
    ],
    'auth_system_interface_sms_notice_interface_configuration' => [
        'app\admin\controller\PluginController::smsSetting',
        'app\admin\controller\PluginController::smsSettingPost',
    ],
    'auth_system_interface_sms_notice_install_uninstall_interface' => [
        'app\admin\controller\PluginController::smsInstall',
        'app\admin\controller\PluginController::smsUninstall',
    ],
    'auth_system_interface_email_notice' => [],
    'auth_system_interface_email_notice_view' => [
        'app\admin\controller\PluginController::mailPluginList',
    ],
    'auth_system_interface_email_notice_get_more_interfaces' => [],
    'auth_system_interface_email_notice_deactivate_enable_interface' => [
        'app\admin\controller\PluginController::mailStatus',
    ],
    'auth_system_interface_email_notice_email_template' => [],
    'auth_system_interface_email_notice_email_template_view' => [
        'app\admin\controller\NoticeEmailController::emailTemplateList',
        'app\admin\controller\PluginController::mailPluginList',
    ],
    'auth_system_interface_email_notice_email_template_create_template' => [
        'app\admin\controller\NoticeEmailController::create',
    ],
    'auth_system_interface_email_notice_email_template_update_template' => [
        'app\admin\controller\NoticeEmailController::index',
        'app\admin\controller\NoticeEmailController::update',
    ],
    'auth_system_interface_email_notice_email_template_test_template' => [
        'app\admin\controller\NoticeEmailController::test',
    ],
    'auth_system_interface_email_notice_email_template_delete_template' => [
        'app\admin\controller\NoticeEmailController::delete',
    ],
    'auth_system_interface_email_notice_interface_configuration' => [
        'app\admin\controller\PluginController::mailSetting',
        'app\admin\controller\PluginController::mailSettingPost',
    ],
    'auth_system_interface_email_notice_install_uninstall_interface' => [
        'app\admin\controller\PluginController::mailInstall',
        'app\admin\controller\PluginController::mailUninstall',
    ],
    'auth_system_interface_send_configuration' => [],
    'auth_system_interface_send_configuration_view' => [
        'app\admin\controller\NoticeSettingController::settingList',
        'app\admin\controller\NoticeSmsController::templateList',
        'app\admin\controller\NoticeEmailController::emailTemplateList',
    ],
    'auth_system_interface_send_configuration_save_configuration' => [
        'app\admin\controller\NoticeSettingController::update',
    ],
    'auth_system_interface_gateway' => [],
    'auth_system_interface_gateway_view' => [
        'app\admin\controller\PluginController::gatewayPluginList',
    ],
    'auth_system_interface_gateway_get_more_interfaces' => [],
    'auth_system_interface_gateway_deactivate_enable_interface' => [
        'app\admin\controller\PluginController::gatewayStatus',
    ],
    'auth_system_interface_gateway_interface_configuration' => [
        'app\admin\controller\PluginController::gatewaySetting',
        'app\admin\controller\PluginController::gatewaySettingPost',
    ],
    'auth_system_interface_gateway_install_uninstall_interface' => [
        'app\admin\controller\PluginController::gatewayInstall',
        'app\admin\controller\PluginController::gatewayUninstall',
    ],
    'auth_upstream_downstream' => [],
    'auth_upstream_downstream_supplier' => [],
    'auth_upstream_downstream_supplier_view' => [
        'app\admin\controller\SupplierController::list',
        'app\admin\controller\SupplierController::status',
    ],
    'auth_upstream_downstream_supplier_create_supplier' => [
        'app\admin\controller\SupplierController::create',
    ],
    'auth_upstream_downstream_supplier_update_supplier' => [
        'app\admin\controller\SupplierController::index',
        'app\admin\controller\SupplierController::update',
    ],
    'auth_upstream_downstream_supplier_delete_supplier' => [
        'app\admin\controller\SupplierController::delete',
    ],
    'auth_upstream_downstream_supplier_detail' => [],
    'auth_upstream_downstream_supplier_detail_order_list' => [],
    'auth_upstream_downstream_supplier_detail_order_list_view' => [
        'app\admin\controller\UpstreamOrderController::list',
        'app\admin\controller\UpstreamOrderController::sellInfo',
    ],
    'auth_upstream_downstream_supplier_detail_order_list_check_order' => [
        'app\admin\controller\OrderController::index',
        'app\admin\controller\ClientController::index',
    ],
    'auth_upstream_downstream_supplier_detail_order_list_update_order' => [
        'app\admin\controller\OrderController::updateAmount',
    ],
    'auth_upstream_downstream_supplier_detail_order_list_delete_order' => [
        'app\admin\controller\OrderController::delete',
    ],
    'auth_upstream_downstream_supplier_detail_product_list' => [],
    'auth_upstream_downstream_supplier_detail_product_list_view' => [
        'app\admin\controller\UpstreamProductController::list',
        'app\admin\controller\SupplierController::list',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_upstream_downstream_supplier_detail_product_list_product_show_hide' => [
        'app\admin\controller\ProductController::hidden',
    ],
    'auth_upstream_downstream_supplier_detail_product_list_update_upstream_product' => [
        'app\admin\controller\UpstreamProductController::update',
        'app\admin\controller\SupplierController::product',
    ],
    'auth_upstream_downstream_supplier_detail_product_list_delete_upstream_product' => [
        'app\admin\controller\ProductController::delete',
    ],
    'auth_upstream_downstream_supplier_detail_host_list' => [],
    'auth_upstream_downstream_supplier_detail_host_list_view' => [
        'app\admin\controller\UpstreamHostController::list',
    ],
    'auth_upstream_downstream_supplier_detail_host_list_check_host_detail' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\HostController::index',
        'app\admin\controller\HostController::adminArea',
        'app\admin\controller\HostController::moduleButton',
        'app\admin\controller\HostController::moduleField',
        'app\admin\controller\UpstreamHostController::index',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_upstream_downstream_upstream_order' => [],
    'auth_upstream_downstream_upstream_order_view' => [
        'app\admin\controller\UpstreamOrderController::list',
        'app\admin\controller\UpstreamOrderController::sellInfo',
    ],
    'auth_upstream_downstream_upstream_order_check_order' => [
        'app\admin\controller\OrderController::index',
        'app\admin\controller\ClientController::index',
    ],
    'auth_upstream_downstream_upstream_order_update_order' => [
        'app\admin\controller\OrderController::updateAmount',
    ],
    'auth_upstream_downstream_upstream_order_delete_order' => [
        'app\admin\controller\OrderController::delete',
    ],
    'auth_upstream_downstream_upstream_order_agent' => [
        'app\admin\controller\UpstreamProductController::recommendProductList',
        'app\admin\controller\UpstreamProductController::agentRecommendProduct',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_upstream_downstream_upstream_host' => [],
    'auth_upstream_downstream_upstream_host_view' => [
        'app\admin\controller\UpstreamHostController::list',
    ],
    'auth_upstream_downstream_upstream_host_check_host_detail' => [
        'app\admin\controller\ServerController::serverList',
        'app\admin\controller\ClientController::index',
        'app\admin\controller\ClientController::clientList',
        'app\admin\controller\HostController::hostList',
        'app\admin\controller\HostController::index',
        'app\admin\controller\HostController::adminArea',
        'app\admin\controller\HostController::moduleButton',
        'app\admin\controller\HostController::moduleField',
        'app\admin\controller\UpstreamHostController::index',
        'app\admin\controller\ProductController::productList',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_upstream_downstream_upstream_product' => [],
    'auth_upstream_downstream_upstream_product_view' => [
        'app\admin\controller\UpstreamProductController::list',
        'app\admin\controller\SupplierController::list',
        'app\admin\controller\ProductGroupController::productGroupFirstList',
        'app\admin\controller\ProductGroupController::productGroupSecondList',
    ],
    'auth_upstream_downstream_upstream_product_create_group' => [
        'app\admin\controller\ProductGroupController::create',
    ],
    'auth_upstream_downstream_upstream_product_create_upstream_product' => [
        'app\admin\controller\UpstreamProductController::create',
        'app\admin\controller\SupplierController::product',
    ],
    'auth_upstream_downstream_upstream_product_show_hide' => [
        'app\admin\controller\ProductController::hidden',
    ],
    'auth_upstream_downstream_upstream_product_update_upstream_product' => [
        'app\admin\controller\UpstreamProductController::update',
        'app\admin\controller\SupplierController::product',
    ],
    'auth_upstream_downstream_upstream_product_delete_upstream_product' => [
        'app\admin\controller\ProductController::delete',
    ],
    'auth_management' => [],
    'auth_management_task' => [],
    'auth_management_task_view' => [
        'app\admin\controller\TaskController::taskList',
    ],
    'auth_management_task_retry' => [
        'app\admin\controller\TaskController::retry',
    ],
    'auth_management_log' => [],
    'auth_management_log_system_log' => [
        'app\admin\controller\LogController::systemLogList',
    ],
    'auth_management_log_notice_log' => [
        'app\admin\controller\LogController::smsLogList',
        'app\admin\controller\LogController::emailLogList',
    ],
    'auth_management_cron' => [],
    'auth_management_cron_view' => [
        'app\admin\controller\ConfigurationController::cronList',
    ],
    'auth_management_cron_save_cron' => [
        'app\admin\controller\ConfigurationController::cronUpdate',
    ],
    'auth_site_management' => [],
    'auth_site_management_news' => [],
    'auth_site_management_news_view' => [
        'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsList',
    ],
    'auth_site_management_news_create_news' => [
        'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsTypeList',
        'addon\idcsmart_news\controller\AdminIndexController::createIdcsmartNews',
    ],
    'auth_site_management_news_type' => [
        'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsTypeList',
        'addon\idcsmart_news\controller\AdminIndexController::createIdcsmartNewsType',
        'addon\idcsmart_news\controller\AdminIndexController::updateIdcsmartNewsType',
        'addon\idcsmart_news\controller\AdminIndexController::deleteIdcsmartNewsType',
    ],
    'auth_site_management_news_show_hide' => [
        'addon\idcsmart_news\controller\AdminIndexController::hiddenIdcsmartNews',
    ],
    'auth_site_management_news_update_news' => [
        'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsDetail',
        'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsTypeList',
        'addon\idcsmart_news\controller\AdminIndexController::updateIdcsmartNews',
    ],
    'auth_site_management_news_delete_news' => [
        'addon\idcsmart_news\controller\AdminIndexController::deleteIdcsmartNews',
    ],
    'auth_site_management_help' => [],
    'auth_site_management_help_view' => [
        'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpList',
    ],
    'auth_site_management_help_create_help' => [
        'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
        'addon\idcsmart_help\controller\AdminIndexController::createIdcsmartHelp',
    ],
    'auth_site_management_help_index' => [
        'addon\idcsmart_help\controller\AdminIndexController::indexIdcsmartHelp',
        'addon\idcsmart_help\controller\AdminIndexController::indexIdcsmartHelpSave',
        'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
    ],
    'auth_site_management_help_type' => [
        'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
        'addon\idcsmart_help\controller\AdminIndexController::createIdcsmartHelpType',
        'addon\idcsmart_help\controller\AdminIndexController::updateIdcsmartHelpType',
        'addon\idcsmart_help\controller\AdminIndexController::deleteIdcsmartHelpType',
    ],
    'auth_site_management_help_show_hide' => [
        'addon\idcsmart_help\controller\AdminIndexController::hiddenIdcsmartHelp',
    ],
    'auth_site_management_help_update_help' => [
        'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpDetail',
        'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
        'addon\idcsmart_help\controller\AdminIndexController::updateIdcsmartHelp',
    ],
    'auth_site_management_help_delete_help' => [
        'addon\idcsmart_help\controller\AdminIndexController::deleteIdcsmartHelp',
    ],
    'auth_site_management_announcement' => [],
    'auth_site_management_announcement_view' => [
        'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementList',
    ],
    'auth_site_management_announcement_create_announcement' => [
        'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementTypeList',
        'addon\idcsmart_announcement\controller\AdminIndexController::createIdcsmartAnnouncement',
    ],
    'auth_site_management_announcement_type' => [
        'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementTypeList',
        'addon\idcsmart_announcement\controller\AdminIndexController::createIdcsmartAnnouncementType',
        'addon\idcsmart_announcement\controller\AdminIndexController::updateIdcsmartAnnouncementType',
        'addon\idcsmart_announcement\controller\AdminIndexController::deleteIdcsmartAnnouncementType',
    ],
    'auth_site_management_announcement_show_hide' => [
        'addon\idcsmart_announcement\controller\AdminIndexController::hiddenIdcsmartAnnouncement',
    ],
    'auth_site_management_announcement_update_announcement' => [
        'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementDetail',
        'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementTypeList',
        'addon\idcsmart_announcement\controller\AdminIndexController::updateIdcsmartAnnouncement',
    ],
    'auth_site_management_announcement_delete_announcement' => [
        'addon\idcsmart_announcement\controller\AdminIndexController::deleteIdcsmartAnnouncement',
    ],
    'auth_site_management_file_download' => [],
    'auth_site_management_file_download_view' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileList',
        'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileFolderList',
        'app\admin\controller\ProductController::productList',
    ],
    'auth_site_management_file_download_upload_file' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::createIdcsmartFile',
    ],
    'auth_site_management_file_download_update_file' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileDetail',
        'addon\idcsmart_file_download\controller\AdminIndexController::updateIdcsmartFile',
    ],
    'auth_site_management_file_download_move_file' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::moveIdcsmartFile',
    ],
    'auth_site_management_file_download_delete_file' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::deleteIdcsmartFile',
    ],
    'auth_site_management_file_download_file_order' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileOrder',
    ],
    'auth_site_management_file_download_file_show_hide' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::hiddenIdcsmartFile',
    ],
    'auth_site_management_file_download_file_folder' => [
        'addon\idcsmart_file_download\controller\AdminIndexController::createIdcsmartFileFolder',
        'addon\idcsmart_file_download\controller\AdminIndexController::updateIdcsmartFileFolder',
        'addon\idcsmart_file_download\controller\AdminIndexController::deleteIdcsmartFileFolder',
        'addon\idcsmart_file_download\controller\AdminIndexController::setDefaultFileFolder',
    ],
    'auth_app' => [],
    'auth_app_list' => [],
    'auth_app_list_view' => [
        'app\admin\controller\PluginController::addonPluginList',
        'app\admin\controller\PluginController::sync',
    ],
    'auth_app_list_more_app' => [],
    'auth_app_list_sync_app' => [
        'app\admin\controller\PluginController::sync',
    ],
    'auth_app_list_sync_app_download_upgrade' => [
        'app\admin\controller\PluginController::download',
    ],
    'auth_app_list_plugin_hook_order' => [
        'app\admin\controller\PluginController::pluginHookList',
        'app\admin\controller\PluginController::pluginHookOrder',
    ],
    'auth_app_list_upgrade' => [
        'app\admin\controller\PluginController::upgrade',
    ],
    'auth_app_list_deactivate_enable_app' => [
        'app\admin\controller\PluginController::addonStatus',
    ],
    'auth_app_list_install_uninstall_app' => [
        'app\admin\controller\PluginController::addonInstall',
        'app\admin\controller\PluginController::addonUninstall',
    ],
];

$install_plugin = Db::name('plugin')->column('name');

$sqls = generateSqls($install_plugin, $auth, $auth_url, $auth_plugin, $auth_description, $auth_rule);

$sql = array_merge($sql, $sqls['auth'], $sqls['auth_link'], $sqls['auth_rule'], $sqls['auth_rule_link']);

foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

function generateSqls($install_plugin = [], $auth = [], $auth_url = [], $auth_plugin = [], $auth_description = [], $auth_rule = [], $sqls = [], &$id = 0, $parent = 0, &$rule = [], &$rule_id = 0)
{
    if(empty($sqls)){
        $sqls = [
            'auth' => [],
            'auth_link' => [],
            'auth_rule' => [],
            'auth_rule_link' => [],
        ];
    }
    foreach ($auth as $key => $value) {
        $id++;
        $title = $key;
        $url = $auth_url[$key] ?? '';
        $modulePlugin = explode(',', $auth_plugin[$key] ?? '');
        $module = $modulePlugin[0] ?? '';
        $plugin = $modulePlugin[1] ?? '';
        if(!empty($plugin)){
            if(!in_array($plugin, $install_plugin)){
                continue;
            }
        }

        $description = $auth_description[$key] ?? '';
        $rules = $auth_rule[$key] ?? [];
        $sqls['auth'][] = "insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`,`description`) values ({$id},'{$title}','{$url}',{$id}, {$parent}, '{$module}','{$plugin}','{$description}');";
        $sqls['auth_link'][] = "insert  into `idcsmart_auth_link`(`auth_id`,`admin_role_id`) values ({$id},1);";
        foreach ($rules as $v) {
            if(!isset($rule[$v])){
                $rule_id++; 
                $rule[$v] = $rule_id;
                if(strpos($v, 'addon\\')===0){
                    $modulePlugin = explode('\\', $v);
                    $module = $modulePlugin[0];
                    $plugin = parse_name($modulePlugin[1], 1);
                }else{
                    $module = '';
                    $plugin = '';
                }
                $sqls['auth_rule'][] = "insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values ({$rule[$v]},'".str_replace('\\', '\\\\', $v)."','','{$module}','{$plugin}');";
            }
            $sqls['auth_rule_link'][] = "insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values ({$rule[$v]},{$id});";

        }

        if(!empty($value)){
            $sqls = generateSqls($install_plugin, $value, $auth_url, $auth_plugin, $auth_description, $auth_rule, $sqls, $id, $id, $rule, $rule_id);
        }
    }

    return $sqls;
}