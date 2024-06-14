<?php
use think\facade\Db;

upgradeData1045();
function upgradeData1045()
{
    $sql = array(
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('client_create_api','1','用户API创建权限');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('client_create_api_type','0','用户API创建权限类型(0=全部用户1=指定用户可创建2=指定用户不可创建)');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('client_create_api_client','','API指定用户');",
        "ALTER TABLE `idcsmart_supplier` ADD COLUMN `currency_code` varchar(10) NOT NULL DEFAULT '' COMMENT '货币代码';",
        "ALTER TABLE `idcsmart_supplier` ADD COLUMN `rate` decimal(10,5) NOT NULL DEFAULT '1.00000' COMMENT '汇率';",
        "ALTER TABLE `idcsmart_supplier` ADD COLUMN `auto_update_rate` tinyint(1) NOT NULL DEFAULT '1' COMMENT '自动更新汇率';",
        "ALTER TABLE `idcsmart_supplier` ADD COLUMN `rate_update_time` int(11) NOT NULL DEFAULT '0' COMMENT '汇率更新时间';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('prohibit_user_information_changes','','禁止用户信息变更');",
        "INSERT  INTO `idcsmart_plugin`(`status`,`name`,`title`,`url`,`author`,`author_url`,`version`,`description`,`config`,`module`,`order`,`help_url`,`create_time`,`update_time`) VALUES (1,'LocalOss','本地存储','','智简魔方','','1.0.0','本地存储','{\"module_name\":\"本地存储\"}','oss',0,'',1662529067,1662539097);",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_method','LocalOss',0,0,'对象存储方式');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_sms_plugin','',0,0,'短信接口');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_sms_plugin_template','',0,0,'短信模板');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_sms_plugin_admin','',0,0,'短信通知人员');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_mail_plugin','',0,0,'邮件接口');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_mail_plugin_template','',0,0,'邮件模板');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('oss_mail_plugin_admin','',0,0,'邮件通知人员');",
        "CREATE TABLE `idcsmart_file_log` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件表',
      `uuid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件唯一ID',
      `save_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件保存名',
      `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件原名',
      `type` VARCHAR(255) NOT NULL DEFAULT 'default' COMMENT '文件类型：defautl系统默认、ticket工单、app应用等',
      `oss_method` VARCHAR(255) NOT NULL DEFAULT 'LocalOss' COMMENT '存储方式：默认LocalOss本地存储',
      `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `client_id` INT(11) NOT NULL DEFAULT '0' COMMENT '所属客户ID',
      `admin_id` INT(11) NOT NULL DEFAULT '0' COMMENT '所属管理员ID',
      `source` VARCHAR(25) NOT NULL DEFAULT 'client' COMMENT '来源：admin管理员，client客户默认',
      `url` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '文件访问地址',
      PRIMARY KEY (`id`),
      UNIQUE KEY `uuid` (`uuid`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;",
        "INSERT  INTO `idcsmart_email_template`(`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) VALUES ('对象存储联通异常通知','[{system_website_name}]对象存储联通异常通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF - 8\"> <meta http-equiv=\"X - UA - Compatible\" content=\"IE = edge\"> <meta name=\"viewport\" content=\"width = device - width, initial - scale = 1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{
            system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text - align: center;\">[{system_website_name}]对象存储联通异常通知</h2>\r\n<br /><strong>尊敬的管理员</strong> <br /><span style=\"margin: 0; padding: 0; display: inline - block; margin - top: 55px;\">对象存储链接失败！请及时检查处理！<br /><span style=\"margin: 0; padding: 0; display: inline - block; margin - top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline - block; width: 100 %; text - align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin - top: 20px; display: inline - block; width: 100 %; text - align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);",
        "INSERT  INTO `idcsmart_sms_template`(`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`,`product_url`,`remark`) VALUES ('',0,'对象存储联通异常通知','对象存储链接失败！请及时检查处理！','',2,'Idcsmart','',1660877230,1660893584,'','');",
        "INSERT  INTO `idcsmart_sms_template`(`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`,`product_url`,`remark`) VALUES ('',1,'对象存储联通异常通知','对象存储链接失败！请及时检查处理！','',2,'Idcsmart','',1660877230,1660893584,'','');",
        "INSERT  INTO `idcsmart_notice_setting`(`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) VALUES ('oss_exception_notice','对象存储联通异常通知','',0,'',0,0,'',35,0);",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `status` VARCHAR(25) NOT NULL DEFAULT 'Pending' COMMENT '退款状态：Pending待审核，Reject已拒绝，Refunding退款中，Refunded已退款';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `reason` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '拒绝原因';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `renew_profit_percent` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费利润百分比或固定金额';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `renew_profit_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '续费利润方式，0百分比，1固定金额';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `upgrade_profit_percent` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '升降级利润百分比或固定金额';",
        "ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `upgrade_profit_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '升降级利润方式，0百分比，1固定金额';",
        "UPDATE `idcsmart_upstream_product` set `renew_profit_percent`=`profit_percent`,`renew_profit_type`=`profit_type`,`upgrade_profit_percent`=`profit_percent`,`upgrade_profit_type`=`profit_type`",
        "ALTER TABLE `idcsmart_host` ADD COLUMN `base_renew_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '订购时保存的基础续费金额，方便客户等级和优惠码算折扣';",
        "UPDATE `idcsmart_refund_record` SET `status`='Refunded';",
        "ALTER TABLE `idcsmart_self_defined_field` ADD COLUMN `show_admin_host_list` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '后台产品列表可见(0=否,1=是)';",
        "CREATE TABLE `idcsmart_admin_field` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `view` varchar(100) NOT NULL DEFAULT '' COMMENT '页面显示标识client=用户管理,order=订单管理,host=产品管理,transaction=交易流水',
      `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
      `select_field` varchar(3000) NOT NULL DEFAULT '' COMMENT '当前选择的字段',
      `create_time` int(11) unsigned NOT NULL DEFAULT '0',
      `update_time` int(11) unsigned NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `admin_id` (`admin_id`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='字段设置表';",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('home_login_check_common_ip','0','前台是否检测常用登录IP:1开启0关闭');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('home_login_ip_exception_verify','','用户异常登录验证方式(operate_password=操作密码),多个英文逗号分隔');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('home_enforce_safe_method','','前台强制安全选项(phone=手机,email=邮箱,operate_password=操作密码,certification=实名认证,oauth=三方登录扫码),多个英文逗号分隔');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('admin_enforce_safe_method','','后台强制安全选项(operate_password=操作密码),多个英文逗号分隔');",
        "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('admin_allow_remember_account','1','后台是否允许记住账号:1开启0关闭');",
        "ALTER TABLE `idcsmart_admin` ADD COLUMN `operate_password` varchar(255) NOT NULL DEFAULT '' COMMENT '操作密码';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `operate_password` varchar(255) NOT NULL DEFAULT '' COMMENT '操作密码';",
        "ALTER TABLE `idcsmart_client_login` ADD COLUMN `login_times` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '同个IP登录次数';",

        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartAnnouncement';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartCertification';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartFileDownload';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartHelp';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartNews';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartRefund';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartRenew';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartSshKey';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartTicket';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='IdcsmartCommon';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='MfCloud';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.0' WHERE `name`='MfDcim';",
    );

    $auth = Db::name('auth')->where('title', 'auth_system_configuration_system_configuration')->find();
    if(!empty($auth)){
        $maxOrder = Db::name('auth')->max('order');
        $authId = Db::name('auth')->insertGetId([
            'title'     => 'auth_system_configuration_system_configuration_user_api_management',
            'url'       => 'configuration_api.htm',
            'order'     => $maxOrder+1,
            'parent_id' => $auth['id'],
            'module' => '',
            'plugin' => '',
            'description' => '用户API管理',
        ]);
        
        $rules = [
            'app\admin\controller\ClientController::clientList',
            'app\admin\controller\ApiController::getConfig',
            'app\admin\controller\ApiController::updateConfig',
            'app\admin\controller\ApiController::clientList',
            'app\admin\controller\ApiController::addClient',
            'app\admin\controller\ApiController::removeClient',
        ];

        foreach ($rules as $value) {
            $authRule = Db::name('auth_rule')->where('name', $value)->find();
            if(empty($authRule)){
                $authRuleId = Db::name('auth_rule')->insertGetId([
                    'name' => $value,
                ]);
            }else{
                $authRuleId = $authRule['id'];
            }
            if(!empty($authRuleId)){
                Db::name('auth_rule_link')->insert([
                    'auth_rule_id' => $authRuleId,
                    'auth_id' => $authId,
                ]);
            }
        }

        Db::name('auth_link')->insert([
            'admin_role_id' => 1,
            'auth_id' => $authId,
        ]);
    }
    if(!empty($auth)){
        $maxOrder = Db::name('auth')->max('order');
        $authId = Db::name('auth')->insertGetId([
            'title'     => 'auth_system_configuration_system_configuration_oss_management',
            'url'       => 'configuration_oss.htm',
            'order'     => $maxOrder+1,
            'parent_id' => $auth['id'],
            'module' => '',
            'plugin' => '',
            'description' => '对象存储',
        ]);

        $rules = [
            'app\admin\controller\ConfigurationController::getOssConfig',
            'app\admin\controller\PluginController::ossPluginList',
            'app\admin\controller\PluginController::ossLink',
            'app\admin\controller\PluginController::ossData',
            'app\admin\controller\PluginController::ossSetting',
            'app\admin\controller\PluginController::ossSettingPost',
            'app\admin\controller\PluginController::ossUninstall',
            'app\admin\controller\PluginController::ossStatus',
            'app\admin\controller\PluginController::ossInstall',
            'app\admin\controller\AdminController::adminList',
            'app\admin\controller\NoticeEmailController::emailTemplateList',
            'app\admin\controller\NoticeSmsController::templateList',
            'app\admin\controller\ConfigurationController::ossConfig',
        ];

        foreach ($rules as $value) {
            $authRule = Db::name('auth_rule')->where('name', $value)->find();
            if(empty($authRule)){
                $authRuleId = Db::name('auth_rule')->insertGetId([
                    'name' => $value,
                ]);
            }else{
                $authRuleId = $authRule['id'];
            }
            if(!empty($authRuleId)){
                Db::name('auth_rule_link')->insert([
                    'auth_rule_id' => $authRuleId,
                    'auth_id' => $authId,
                ]);
            }
        }

        Db::name('auth_link')->insert([
            'admin_role_id' => 1,
            'auth_id' => $authId,
        ]);
    }
    $auth = Db::name('auth')->where('title', 'auth_business_order_detail_refund_record')->find();
    if (!empty($auth)){
        $maxOrder = Db::name('auth')->max('order');
        $authId = Db::name('auth')->insertGetId([
            'title'     => 'auth_business_order_detail_refund_record_approval',
            'url'       => '',
            'order'     => $maxOrder+1,
            'parent_id' => $auth['id'],
            'module' => '',
            'plugin' => '',
            'description' => '审批退款',
        ]);
        $rules = [
            'app\admin\controller\OrderController::pendingRefundRecord',
            'app\admin\controller\OrderController::rejectRefundRecord',
        ];

        foreach ($rules as $value) {
            $authRule = Db::name('auth_rule')->where('name', $value)->find();
            if(empty($authRule)){
                $authRuleId = Db::name('auth_rule')->insertGetId([
                    'name' => $value,
                ]);
            }else{
                $authRuleId = $authRule['id'];
            }
            if(!empty($authRuleId)){
                Db::name('auth_rule_link')->insert([
                    'auth_rule_id' => $authRuleId,
                    'auth_id' => $authId,
                ]);
            }
        }

        Db::name('auth_link')->insert([
            'admin_role_id' => 1,
            'auth_id' => $authId,
        ]);
    }
    $auth = Db::name('auth')->where('title', 'auth_business_order_detail_refund_record')->find();
    if (!empty($auth)){
        $maxOrder = Db::name('auth')->max('order');
        $authId = Db::name('auth')->insertGetId([
            'title'     => 'auth_business_order_detail_refund_record_confirm',
            'url'       => '',
            'order'     => $maxOrder+1,
            'parent_id' => $auth['id'],
            'module' => '',
            'plugin' => '',
            'description' => '确认退款',
        ]);
        $rules = [
            'app\admin\controller\OrderController::redundedRefundRecord',
        ];

        foreach ($rules as $value) {
            $authRule = Db::name('auth_rule')->where('name', $value)->find();
            if(empty($authRule)){
                $authRuleId = Db::name('auth_rule')->insertGetId([
                    'name' => $value,
                ]);
            }else{
                $authRuleId = $authRule['id'];
            }
            if(!empty($authRuleId)){
                Db::name('auth_rule_link')->insert([
                    'auth_rule_id' => $authRuleId,
                    'auth_id' => $authId,
                ]);
            }
        }

        Db::name('auth_link')->insert([
            'admin_role_id' => 1,
            'auth_id' => $authId,
        ]);
    }
    $auth = Db::name('auth')->where('title', 'auth_business_host_detail')->find();
    if (!empty($auth)){
        $order = Db::name('auth')->where('parent_id', $auth['id'])->where('title', 'auth_business_host_detail_terminate_account')->value('order');
        Db::name('auth')->where('parent_id', $auth['id'])->where('order', '>', $order)->inc('order')->update();
        $authId = Db::name('auth')->insertGetId([
            'title'   => 'auth_business_host_detail_module_operate',
            'url'   => '',
            'order'   => $order + 1,
            'parent_id' => $auth['id'],
            'module' => '',
            'plugin' => '',
            'description' => '实例操作',
        ]);

        $rules = [
            'app\admin\controller\HostController::adminAreaModuleOperate'
        ];

        foreach ($rules as $value) {
            $authRule = Db::name('auth_rule')->where('name', $value)->find();
            if(empty($authRule)){
                $authRuleId = Db::name('auth_rule')->insertGetId([
                    'name' => $value,
                ]);
            }else{
                $authRuleId = $authRule['id'];
            }
            if(!empty($authRuleId)){
                Db::name('auth_rule_link')->insert([
                    'auth_rule_id' => $authRuleId,
                    'auth_id' => $authId,
                ]);
            }
        }

        Db::name('auth_link')->insert([
            'admin_role_id' => 1,
            'auth_id' => $authId,
        ]);
    }

    $templateControllerAuth = [
        [
            'title' => 'auth_template_controller',
            'url' => '',
            'description' => '模板控制器', # 权限描述
            'parent' => 'auth_system_configuration_system_configuration_theme_configuration', # 父权限 
            'child' => [
                [
                    'title' => 'auth_template_controller_nav',
                    'url' => 'index',
                    'auth_rule' => [
                        'app\admin\controller\WebNavController::list',
                        'app\admin\controller\WebNavController::create',
                        'app\admin\controller\WebNavController::update',
                        'app\admin\controller\WebNavController::delete',
                        'app\admin\controller\WebNavController::show',
                        'app\admin\controller\WebNavController::firstNavOrder',
                        'app\admin\controller\WebNavController::secondNavOrder',
                    ],
                    'description' => '导航配置',
                ],
                [
                    'title' => 'auth_template_controller_default_product',
                    'url' => 'template_host_config',
                    'auth_rule' => [
                        'app\admin\controller\CloudServerBannerController::list',
                        'app\admin\controller\CloudServerBannerController::create',
                        'app\admin\controller\CloudServerBannerController::update',
                        'app\admin\controller\CloudServerBannerController::delete',
                        'app\admin\controller\CloudServerBannerController::show',
                        'app\admin\controller\CloudServerBannerController::order',
                        'app\admin\controller\CloudServerAreaController::list',
                        'app\admin\controller\CloudServerAreaController::create',
                        'app\admin\controller\CloudServerAreaController::update',
                        'app\admin\controller\CloudServerAreaController::delete',
                        'app\admin\controller\CloudServerProductController::list',
                        'app\admin\controller\CloudServerProductController::create',
                        'app\admin\controller\CloudServerProductController::update',
                        'app\admin\controller\CloudServerProductController::delete',
                        'app\admin\controller\ConfigurationController::cloudServerList',
                        'app\admin\controller\ConfigurationController::cloudServerUpdate',
                        'app\admin\controller\CloudServerDiscountController::list',
                        'app\admin\controller\CloudServerDiscountController::create',
                        'app\admin\controller\CloudServerDiscountController::update',
                        'app\admin\controller\CloudServerDiscountController::delete',
                        'app\admin\controller\PhysicalServerBannerController::list',
                        'app\admin\controller\PhysicalServerBannerController::create',
                        'app\admin\controller\PhysicalServerBannerController::update',
                        'app\admin\controller\PhysicalServerBannerController::delete',
                        'app\admin\controller\PhysicalServerBannerController::show',
                        'app\admin\controller\PhysicalServerBannerController::order',
                        'app\admin\controller\PhysicalServerAreaController::list',
                        'app\admin\controller\PhysicalServerAreaController::create',
                        'app\admin\controller\PhysicalServerAreaController::update',
                        'app\admin\controller\PhysicalServerAreaController::delete',
                        'app\admin\controller\PhysicalServerProductController::list',
                        'app\admin\controller\PhysicalServerProductController::create',
                        'app\admin\controller\PhysicalServerProductController::update',
                        'app\admin\controller\PhysicalServerProductController::delete',
                        'app\admin\controller\ConfigurationController::physicalServerList',
                        'app\admin\controller\ConfigurationController::physicalServerUpdate',
                        'app\admin\controller\PhysicalServerDiscountController::list',
                        'app\admin\controller\PhysicalServerDiscountController::create',
                        'app\admin\controller\PhysicalServerDiscountController::update',
                        'app\admin\controller\PhysicalServerDiscountController::delete',
                        'app\admin\controller\SslCertificateProductController::list',
                        'app\admin\controller\SslCertificateProductController::create',
                        'app\admin\controller\SslCertificateProductController::update',
                        'app\admin\controller\SslCertificateProductController::delete',
                        'app\admin\controller\SmsServiceProductController::list',
                        'app\admin\controller\SmsServiceProductController::create',
                        'app\admin\controller\SmsServiceProductController::update',
                        'app\admin\controller\SmsServiceProductController::delete',
                        'app\admin\controller\TrademarkRegisterProductController::list',
                        'app\admin\controller\TrademarkRegisterProductController::create',
                        'app\admin\controller\TrademarkRegisterProductController::update',
                        'app\admin\controller\TrademarkRegisterProductController::delete',
                        'app\admin\controller\TrademarkServiceProductController::list',
                        'app\admin\controller\TrademarkServiceProductController::create',
                        'app\admin\controller\TrademarkServiceProductController::update',
                        'app\admin\controller\TrademarkServiceProductController::delete',
                        'app\admin\controller\ServerHostingAreaController::list',
                        'app\admin\controller\ServerHostingAreaController::create',
                        'app\admin\controller\ServerHostingAreaController::update',
                        'app\admin\controller\ServerHostingAreaController::delete',
                        'app\admin\controller\ServerHostingProductController::list',
                        'app\admin\controller\ServerHostingProductController::create',
                        'app\admin\controller\ServerHostingProductController::update',
                        'app\admin\controller\ServerHostingProductController::delete',
                        'app\admin\controller\CabinetRentalProductController::list',
                        'app\admin\controller\CabinetRentalProductController::create',
                        'app\admin\controller\CabinetRentalProductController::update',
                        'app\admin\controller\CabinetRentalProductController::delete',
                        'app\admin\controller\ConfigurationController::icpList',
                        'app\admin\controller\ConfigurationController::icpUpdate',
                        'app\admin\controller\IcpServiceProductController::list',
                        'app\admin\controller\IcpServiceProductController::create',
                        'app\admin\controller\IcpServiceProductController::update',
                        'app\admin\controller\IcpServiceProductController::delete',
                    ],
                    'description' => '默认产品配置',
                ],
                [
                    'title' => 'auth_template_controller_bottom_bar',
                    'url' => 'template_bottom_nav',
                    'auth_rule' => [
                        'app\admin\controller\BottomBarGroupController::list',
                        'app\admin\controller\BottomBarGroupController::create',
                        'app\admin\controller\BottomBarGroupController::update',
                        'app\admin\controller\BottomBarGroupController::delete',
                        'app\admin\controller\BottomBarGroupController::order',
                        'app\admin\controller\BottomBarNavController::list',
                        'app\admin\controller\BottomBarNavController::create',
                        'app\admin\controller\BottomBarNavController::update',
                        'app\admin\controller\BottomBarNavController::delete',
                        'app\admin\controller\BottomBarNavController::show',
                        'app\admin\controller\BottomBarNavController::order',
                    ],
                    'description' => '底部栏配置',
                ],
                [
                    'title' => 'auth_template_controller_web',
                    'url' => 'template_web_config',
                    'auth_rule' => [
                        'app\admin\controller\ConfigurationController::webList',
                        'app\admin\controller\ConfigurationController::webUpdate',
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
                    'description' => '网站参数配置',
                ],
                [
                    'title' => 'auth_template_controller_seo',
                    'url' => 'template_seo_manage',
                    'auth_rule' => [
                        'app\admin\controller\SeoController::list',
                        'app\admin\controller\SeoController::create',
                        'app\admin\controller\SeoController::update',
                        'app\admin\controller\SeoController::delete',
                    ],
                    'description' => 'SEO管理',
                ],
                [
                    'title' => 'auth_template_controller_index_banner',
                    'url' => 'template_index_banner',
                    'auth_rule' => [
                        'app\admin\controller\IndexBannerController::list',
                        'app\admin\controller\IndexBannerController::create',
                        'app\admin\controller\IndexBannerController::update',
                        'app\admin\controller\IndexBannerController::delete',
                        'app\admin\controller\IndexBannerController::show',
                        'app\admin\controller\IndexBannerController::order',
                    ],
                    'description' => '首页轮播图',
                ],
                [
                    'title' => 'auth_template_controller_side_floating_window',
                    'url' => 'template_side_manage',
                    'auth_rule' => [
                        'app\admin\controller\SideFloatingWindowController::list',
                        'app\admin\controller\SideFloatingWindowController::create',
                        'app\admin\controller\SideFloatingWindowController::update',
                        'app\admin\controller\SideFloatingWindowController::delete',
                        'app\admin\controller\SideFloatingWindowController::order',
                    ],
                    'description' => '侧边浮窗管理',
                ],
            ]
        ],
    ];

    $AuthModel = new \app\admin\model\AuthModel();
    foreach ($templateControllerAuth as $value) {
        $AuthModel->createSystemAuth($value);
    }

    // 是否有云的接口
    $cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
    if(!empty($cloudServer)){
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `rand_ssh_port_start` varchar(10) NOT NULL DEFAULT '' COMMENT '随机端口开始端口';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `rand_ssh_port_end` varchar(10) NOT NULL DEFAULT '' COMMENT '随机端口结束端口';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `rand_ssh_port_windows` varchar(10) NOT NULL DEFAULT '' COMMENT '指定端口Windows';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `rand_ssh_port_linux` varchar(10) NOT NULL DEFAULT '' COMMENT '指定端口linux';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `default_one_ipv4` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '默认携带IPv4';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `ipv6_enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用IPv6';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `ipv6_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IPv6数量';";
        $sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `default_ipv4` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '是否有默认IPv4(-1默认以前逻辑,0=没有,1=有)';";
        $sql[] = "UPDATE `idcsmart_module_mf_cloud_config` SET `rand_ssh_port_start`='10000',`rand_ssh_port_end`='20000' WHERE `rand_ssh_port`=1;";
        $sql[] = "CREATE TABLE `idcsmart_module_mf_cloud_limit_rule` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
      `rule` text NOT NULL COMMENT '规则json',
      `result` text NOT NULL COMMENT '结果json',
      `rule_md5` char(32) NOT NULL DEFAULT '' COMMENT '规则md5用于判断是否重复',
      `create_time` int(11) unsigned NOT NULL DEFAULT '0',
      `update_time` int(11) unsigned NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `rule_md5` (`rule_md5`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '限制规则表';";
        // 是否设置了IPv6
        $ipv6Product = Db::name('module_mf_cloud_config')->field('id,product_id,ipv6_num')->where('ipv6_num', '>', 0)->select();
        foreach($ipv6Product as $v){
            $line = Db::name('module_mf_cloud_line')
                    ->alias('l')
                    ->join('module_mf_cloud_data_center dc', 'l.data_center_id=dc.id')
                    ->where('dc.product_id', $v['product_id'])
                    ->group('l.id')
                    ->column('l.id');
            foreach($line as $lineId){
                $sql[] = "UPDATE `idcsmart_module_mf_cloud_line` SET `ipv6_enable`=1 WHERE `id`=".$lineId;
                // 添加默认IPv6
                $insertIpv6 = Db::name('module_mf_cloud_option')
                            ->where('product_id', $v['product_id'])
                            ->where('rel_type', 9)
                            ->where('rel_id', $lineId)
                            ->where('value', 2)
                            ->find();
                if(empty($insertIpv6)){
                    $sql[] = "INSERT INTO `idcsmart_module_mf_cloud_option` (`product_id`,`rel_type`,`rel_id`,`type`,`value`,`other_config`) VALUES ('{$v['product_id']}',9,'{$lineId}','radio','2','{}');";
                }
            }
        }
    }

    // 是否有dcim的接口
    $dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
    if(!empty($dcimServer)){
        $sql[] =  "CREATE TABLE `idcsmart_module_mf_dcim_limit_rule` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
      `rule` text NOT NULL COMMENT '规则json',
      `result` text NOT NULL COMMENT '结果json',
      `rule_md5` char(32) NOT NULL DEFAULT '' COMMENT '规则md5用于判断是否重复',
      `create_time` int(11) unsigned NOT NULL DEFAULT '0',
      `update_time` int(11) unsigned NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `rule_md5` (`rule_md5`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '限制规则表';";
    }

    $PluginModel = new \app\admin\model\PluginModel();
    $PluginModel->pluginUpgradeAuth('addon', 'PromoCode');

    // 是否有模板控制器插件
    $templateControllerPlugin = Db::name('plugin')->where('name', 'TemplateController')->find();
    if (!empty($templateControllerPlugin)){
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_bottom_bar_group`";
        $sql[] = "CREATE TABLE `idcsmart_bottom_bar_group` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '底部栏分组ID',
      `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
      `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `theme` (`theme`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='底部栏分组'";
        $bottomBarGroup = Db::name('addon_template_controller_bottom_bar_group')->select()->toArray();
        foreach ($bottomBarGroup as $key => $value) {
            $sql[] = "insert into `idcsmart_bottom_bar_group`(`id`,`theme`,`name`,`order`,`create_time`,`update_time`) values ({$value['id']},'default','{$value['name']}',{$value['order']},{$value['create_time']},{$value['update_time']});";
        }
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_bottom_bar_nav`";
        $sql[] = "CREATE TABLE `idcsmart_bottom_bar_nav` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '底部栏导航ID',
      `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
      `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组ID',
      `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `theme` (`theme`),
      KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='底部栏导航'";
        $bottomBarNav = Db::name('addon_template_controller_bottom_bar_nav')->select()->toArray();
        foreach ($bottomBarNav as $key => $value) {
            $sql[] = "insert into `idcsmart_bottom_bar_nav`(`id`,`theme`,`group_id`,`name`,`url`,`show`,`order`,`create_time`,`update_time`) values ({$value['id']},'default',{$value['group_id']},'{$value['name']}','{$value['url']}',{$value['show']},{$value['order']},{$value['create_time']},{$value['update_time']});";
        }
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_web_nav`";
        $sql[] = "CREATE TABLE `idcsmart_web_nav` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
      `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
      `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父导航ID',
      `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
      `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
      `file_address` varchar(255) NOT NULL DEFAULT '' COMMENT '文件地址',
      `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `theme` (`theme`),
      KEY `parent_id` (`parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模板控制器导航表'";
        $webNav = Db::name('addon_template_controller_nav')->select()->toArray();
        foreach ($webNav as $key => $value) {
            $sql[] = "insert into `idcsmart_web_nav`(`id`,`theme`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values ({$value['id']},'default',{$value['parent_id']},'{$value['name']}','{$value['description']}','{$value['file_address']}','{$value['icon']}',{$value['show']},{$value['order']},{$value['create_time']},{$value['update_time']});";
        }
        $configuration = Db::name('addon_template_controller_configuration')->select()->toArray();
        foreach ($configuration as $key => $value) {
            $sql[] = "delete from `idcsmart_configuration` where `setting`='{$value['setting']}';";
            $sql[] = "insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('{$value['setting']}','{$value['value']}',{$value['create_time']},{$value['update_time']},'{$value['description']}');";
        }

        $sql[] = "DROP TABLE IF EXISTS `idcsmart_cloud_server_banner`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_cloud_server_banner` TO `idcsmart_cloud_server_banner`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_cloud_server_area`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_cloud_server_area` TO `idcsmart_cloud_server_area`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_cloud_server_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_cloud_server_product` TO `idcsmart_cloud_server_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_cloud_server_discount`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_cloud_server_discount` TO `idcsmart_cloud_server_discount`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_physical_server_banner`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_physical_server_banner` TO `idcsmart_physical_server_banner`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_physical_server_area`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_physical_server_area` TO `idcsmart_physical_server_area`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_physical_server_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_physical_server_product` TO `idcsmart_physical_server_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_physical_server_discount`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_physical_server_discount` TO `idcsmart_physical_server_discount`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_ssl_certificate_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_ssl_certificate_product` TO `idcsmart_ssl_certificate_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_sms_service_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_sms_service_product` TO `idcsmart_sms_service_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_trademark_register_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_trademark_register_product` TO `idcsmart_trademark_register_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_trademark_service_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_trademark_service_product` TO `idcsmart_trademark_service_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_server_hosting_area`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_server_hosting_area` TO `idcsmart_server_hosting_area`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_server_hosting_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_server_hosting_product` TO `idcsmart_server_hosting_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_cabinet_rental_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_cabinet_rental_product` TO `idcsmart_cabinet_rental_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_icp_service_product`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_icp_service_product` TO `idcsmart_icp_service_product`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_seo`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_seo` TO `idcsmart_seo`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_index_banner`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_index_banner` TO `idcsmart_index_banner`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_side_floating_window`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_side_floating_window` TO `idcsmart_side_floating_window`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_friendly_link`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_friendly_link` TO `idcsmart_friendly_link`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_honor`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_honor` TO `idcsmart_honor`;";
        $sql[] = "DROP TABLE IF EXISTS `idcsmart_partner`;";
        $sql[] = "RENAME TABLE `idcsmart_addon_template_controller_partner` TO `idcsmart_partner`;";
    }else{
        # 模板控制器
        $webSql = [
                "DROP TABLE IF EXISTS `idcsmart_web_nav`",
                "CREATE TABLE `idcsmart_web_nav` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
      `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
      `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父导航ID',
      `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
      `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
      `file_address` varchar(255) NOT NULL DEFAULT '' COMMENT '文件地址',
      `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `theme` (`theme`),
      KEY `parent_id` (`parent_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模板控制器导航表'",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (1,0,'首页','','index.html','',1,0,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (2,0,'产品','','','',1,1,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (3,2,'云服务器','高可用的弹性计算服务','cloud.html','/web/default/assets/img/nav/group-1.png',1,0,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (4,2,'物理裸机','高性能物理裸机租用服务','dedicated.html','/web/default/assets/img/nav/group-2.png',1,1,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (5,2,'SSL证书','一站式SSL证书管理服务','ssl.html','/web/default/assets/img/nav/group-4.png',1,3,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (6,2,'短信服务','10秒内精准触达全球客户','sms.html','/web/default/assets/img/nav/group-5.png',1,4,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (7,2,'商标注册','1V1全流程商标注册服务','trademark.html','/web/default/assets/img/nav/group-6.png',1,5,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (8,2,'服务器托管','安全无忧的高品质托管服务','trusteeship.html','/web/default/assets/img/nav/group-7.png',1,6,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (9,2,'机柜租用','覆盖全球的T3+机房资源','rent.html','/web/default/assets/img/nav/group-8.png',1,7,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (10,2,'ICP办理','极速获取专属解决方案','icp.html','/web/default/assets/img/nav/group-9.png',1,8,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (11,2,'域名注册','全球主流域名注册服务','domain.html','/web/default/assets/img/nav/group-2.png',1,2,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (12,0,'解决方案','','','',1,2,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (13,12,'电商行业','快速实现线上营销创新与业务增收','solution/ecommerce.html','',1,0,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (14,12,'金融行业','实现金融机构与用户的高效触达','solution/finance.html','',1,1,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (15,12,'游戏行业','提升研发效率，增强交互体验','solution/game.html','',1,2,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (16,12,'汽车行业','助力更开放的出行服务连接生态','solution/auto.html','',1,3,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (17,12,'文旅行业','推动文旅行业数智化转型升级','solution/travel.html','',1,4,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (18,12,'教育行业','打造云时代教育治理新模式','solution/education.html','',1,5,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (19,12,'医疗行业','提升各级医疗资源互联互通能力','solution/medical.html','',1,6,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (20,12,'农业行业','构建智慧农业生产服务体系','solution/agriculture.html','',1,7,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (21,0,'合作伙伴','','','',1,3,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (22,21,'CPS推广','邀好友上云，赢35%返现奖励','partner/cps.html','',1,0,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (23,21,'代理伙伴','品牌让利，多渠道扶持，互助共赢','partner/agent.html','',1,1,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (24,0,'客户支持','','','',1,4,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (25,24,'文档中心','全面细致的产品帮助文档','document.html','',1,0,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (26,24,'服务保障','多渠道不间断服务支撑','service-guarantee.html','',1,1,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (27,24,'联系我们','专业售前咨询和售后支持服务','contact.html','',1,2,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (28,24,'官方公告','最新官方服务动态','announce.html','',1,3,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (29,0,'关于我们','','','',1,5,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (30,29,'公司介绍','助力中小企业数智化转型升级','about.html','',1,0,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (31,29,'人才招聘','和我们一起，用云技术改变世界','recruit.html','',1,1,0,0);",
                "insert  into `idcsmart_web_nav`(`id`,`parent_id`,`name`,`description`,`file_address`,`icon`,`show`,`order`,`create_time`,`update_time`) values (32,29,'新闻资讯','快速掌握行业前沿资讯','news.html','',1,2,0,0);",
                "CREATE TABLE `idcsmart_cloud_server_banner` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '轮播图ID',
      `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
      `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
      `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `show` (`show`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='云服务器轮播图表'",
                "CREATE TABLE `idcsmart_cloud_server_area` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '区域ID',
      `first_area` varchar(100) NOT NULL DEFAULT '' COMMENT '一级区域',
      `second_area` varchar(100) NOT NULL DEFAULT '' COMMENT '二级区域',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='云服务器区域表'",
                "CREATE TABLE `idcsmart_cloud_server_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `area_id` int(11) NOT NULL DEFAULT '0' COMMENT '区域ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` varchar(100) NOT NULL DEFAULT '' COMMENT '描述',
      `cpu` text NOT NULL COMMENT '处理器',
      `memory` text NOT NULL COMMENT '内存',
      `system_disk` text NOT NULL COMMENT '系统盘',
      `bandwidth` text NOT NULL COMMENT '带宽',
      `duration` text NOT NULL COMMENT '时长',
      `tag` text NOT NULL COMMENT '标签',
      `original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
      `original_price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '原价单位,month月year年',
      `selling_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
      `selling_price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '售价单位,month月year年',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `area_id` (`area_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='云服务器商品表'",
                "CREATE TABLE `idcsmart_cloud_server_discount` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '优惠ID',
      `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
      `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='云服务器优惠表'",
                "CREATE TABLE `idcsmart_physical_server_banner` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '轮播图ID',
      `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
      `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
      `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `show` (`show`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='物理服务器轮播图表'",
                "CREATE TABLE `idcsmart_physical_server_area` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '区域ID',
      `first_area` varchar(100) NOT NULL DEFAULT '' COMMENT '一级区域',
      `second_area` varchar(100) NOT NULL DEFAULT '' COMMENT '二级区域',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='物理服务器区域表'",
                "CREATE TABLE `idcsmart_physical_server_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `area_id` int(11) NOT NULL DEFAULT '0' COMMENT '区域ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` varchar(100) NOT NULL DEFAULT '' COMMENT '描述',
      `cpu` text NOT NULL COMMENT '处理器',
      `memory` text NOT NULL COMMENT '内存',
      `disk` text NOT NULL COMMENT '硬盘',
      `ip_num` text NOT NULL COMMENT 'IP数量',
      `bandwidth` text NOT NULL COMMENT '带宽',
      `duration` text NOT NULL COMMENT '时长',
      `tag` text NOT NULL COMMENT '标签',
      `original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
      `original_price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '原价单位,month月year年',
      `selling_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
      `selling_price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '售价单位,month月year年',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `area_id` (`area_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='物理服务器商品表'",
                "CREATE TABLE `idcsmart_physical_server_discount` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '优惠ID',
      `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
      `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='物理服务器优惠表'",
                "CREATE TABLE `idcsmart_ssl_certificate_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` text NOT NULL COMMENT '描述',
      `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
      `price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '价格单位,month月year年',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SSL证书商品表'",
                "CREATE TABLE `idcsmart_sms_service_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` text NOT NULL COMMENT '描述',
      `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
      `price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '价格单位,month月year年',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信服务商品表'",
                "CREATE TABLE `idcsmart_trademark_register_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` text NOT NULL COMMENT '描述',
      `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商标注册商品表'",
                "CREATE TABLE `idcsmart_trademark_service_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` text NOT NULL COMMENT '描述',
      `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商标延伸服务商品表'",
                "CREATE TABLE `idcsmart_server_hosting_area` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '区域ID',
      `first_area` varchar(100) NOT NULL DEFAULT '' COMMENT '一级区域',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='服务器托管区域表'",
                "CREATE TABLE `idcsmart_server_hosting_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `area_id` int(11) NOT NULL DEFAULT '0' COMMENT '区域ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `region` varchar(100) NOT NULL DEFAULT '' COMMENT '地域',
      `ip_num` text NOT NULL COMMENT 'IP数量',
      `bandwidth` text NOT NULL COMMENT '带宽',
      `defense` text NOT NULL COMMENT '防御',
      `bandwidth_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '带宽价格',
      `bandwidth_price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '带宽价格单位,month/M/月year/M/年',
      `selling_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
      `selling_price_unit` varchar(10) NOT NULL DEFAULT '' COMMENT '售价单位,month月year年',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `area_id` (`area_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='服务器托管商品表'",
                "CREATE TABLE `idcsmart_cabinet_rental_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` text NOT NULL COMMENT '描述',
      `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='机柜租用商品表'",
                "CREATE TABLE `idcsmart_icp_service_product` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
      `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
      `description` text NOT NULL COMMENT '描述',
      `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
      `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ICP拓展服务商品表'",
                "CREATE TABLE `idcsmart_bottom_bar_group` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '底部栏分组ID',
      `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
      `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `theme` (`theme`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='底部栏分组'",
                "CREATE TABLE `idcsmart_bottom_bar_nav` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '底部栏导航ID',
      `theme` varchar(255) NOT NULL DEFAULT 'default' COMMENT '主题',
      `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组ID',
      `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `theme` (`theme`),
      KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='底部栏导航'",
                "insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cloud_server_more_offers','',0,0,'云服务器更多优惠开关');",
                "insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('physical_server_more_offers','',0,0,'物理服务器更多优惠开关');",
                "insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('icp_product_id','',0,0,'ICP购买/咨询商品ID');",
                
                "CREATE TABLE `idcsmart_seo` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'SEOID',
      `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
      `page_address` varchar(255) NOT NULL DEFAULT '' COMMENT '页面地址',
      `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
      `description` text NOT NULL COMMENT '描述',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SEO'",
                "CREATE TABLE `idcsmart_index_banner` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '轮播图ID',
      `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
      `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转链接',
      `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
      `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
      `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示0否1是',
      `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`),
      KEY `show` (`show`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页轮播图表'",
                "CREATE TABLE `idcsmart_side_floating_window` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '侧边浮窗ID',
      `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
      `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
      `content` text NOT NULL COMMENT '显示内容',
      `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
      `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='侧边浮窗'",
        ];
        $sql = array_merge($sql, $webSql);
    }

    foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    $PluginModel->uninstall(['module' => 'addon', 'name' => 'TemplateController']);

    Db::execute("update `idcsmart_configuration` set `value`='10.4.5' where `setting`='system_version';");
}