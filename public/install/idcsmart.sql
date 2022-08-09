DROP TABLE IF EXISTS `idcsmart_admin`;

CREATE TABLE `idcsmart_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(60) NOT NULL DEFAULT '' COMMENT '名称',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名,登录用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '登录密码,加密函数:',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态;0:禁用,1:正常',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_action_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`),
  KEY `nickname` (`nickname`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='管理员表';

/*Table structure for table `idcsmart_admin_login` */

DROP TABLE IF EXISTS `idcsmart_admin_login`;

CREATE TABLE `idcsmart_admin_login` (
  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_action_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '记录创建时间(此表创建是用来实现2小时未操作自动退出登录)',
  `jwt_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'jwt的签发密钥',
  KEY `admin_login_ip` (`admin_id`,`last_login_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `idcsmart_admin_role` */

DROP TABLE IF EXISTS `idcsmart_admin_role`;

CREATE TABLE `idcsmart_admin_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态;0:禁用;1:正常',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '分组名称',
  `description` varchar(2000) NOT NULL DEFAULT '' COMMENT '分组说明',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='管理员分组表';

/*Table structure for table `idcsmart_admin_role_link` */

DROP TABLE IF EXISTS `idcsmart_admin_role_link`;

CREATE TABLE `idcsmart_admin_role_link` (
  `admin_role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  KEY `admin_role_id` (`admin_role_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员分组对应表';

/*Table structure for table `idcsmart_api` */

DROP TABLE IF EXISTS `idcsmart_api`;

CREATE TABLE `idcsmart_api` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'API密钥ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `token` varchar(200) NOT NULL DEFAULT '' COMMENT 'token',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '白名单状态0关闭1开启',
  `ip` text NOT NULL COMMENT '白名单IP',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API密钥表';

/*Table structure for table `idcsmart_api_log` */

DROP TABLE IF EXISTS `idcsmart_api_log`;

CREATE TABLE `idcsmart_api_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'API日志ID',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `description` text NOT NULL COMMENT '描述',
  `user_type` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人类型api',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `user_name` varchar(100) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '端口',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `rel_id` (`type`,`rel_id`),
  KEY `user_id` (`user_type`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='API日志表';

/*Table structure for table `idcsmart_auth_link` */

DROP TABLE IF EXISTS `idcsmart_auth_link`;

CREATE TABLE `idcsmart_auth_link` (
  `auth_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  `admin_role_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员分组ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限对应表';

/*Table structure for table `idcsmart_auth_rule` */

DROP TABLE IF EXISTS `idcsmart_auth_rule`;
CREATE TABLE `idcsmart_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限规则ID',
  `name` varchar(150) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识,全小写',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '规则描述',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='权限规则表';
INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (1, 'app\\admin\\controller\\AdminController::adminList', 'auth_rule_admin_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (2, 'app\\admin\\controller\\AdminController::index', 'auth_rule_admin_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (3, 'app\\admin\\controller\\AdminController::create', 'auth_rule_admin_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (4, 'app\\admin\\controller\\AdminController::update', 'auth_rule_admin_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (5, 'app\\admin\\controller\\AdminController::delete', 'auth_rule_admin_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (6, 'app\\admin\\controller\\AdminController::status', 'auth_rule_admin_status');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (7, 'app\\admin\\controller\\AdminRoleController::adminRoleList', 'auth_rule_admin_role_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (8, 'app\\admin\\controller\\AdminRoleController::index', 'auth_rule_admin_role_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (9, 'app\\admin\\controller\\AdminRoleController::create', 'auth_rule_admin_role_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (10, 'app\\admin\\controller\\AdminRoleController::update', 'auth_rule_admin_role_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (11, 'app\\admin\\controller\\AdminRoleController::delete', 'auth_rule_admin_role_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (12, 'app\\admin\\controller\\ClientController::clientList', 'auth_rule_client_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (13, 'app\\admin\\controller\\ClientController::index', 'auth_rule_client_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (14, 'app\\admin\\controller\\ClientController::create', 'auth_rule_client_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (15, 'app\\admin\\controller\\ClientController::update', 'auth_rule_client_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (16, 'app\\admin\\controller\\ClientController::delete', 'auth_rule_client_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (17, 'app\\admin\\controller\\ClientController::search', 'auth_rule_client_search');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (18, 'app\\admin\\controller\\ClientCreditController::clientCreditList', 'auth_rule_client_credit_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (19, 'app\\admin\\controller\\ClientCreditController::update', 'auth_rule_client_credit_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (20, 'app\\admin\\controller\\ConfigurationController::systemList', 'auth_rule_configuration_system');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (21, 'app\\admin\\controller\\ConfigurationController::systemUpdate', 'auth_rule_configuration_system_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (22, 'app\\admin\\controller\\ConfigurationController::loginList', 'auth_rule_configuration_login');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (23, 'app\\admin\\controller\\ConfigurationController::loginUpdate', 'auth_rule_configuration_login_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (24, 'app\\admin\\controller\\ConfigurationController::securityList', 'auth_rule_configuration_security');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (25, 'app\\admin\\controller\\ConfigurationController::securityUpdate', 'auth_rule_configuration_security_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (26, 'app\\admin\\controller\\ConfigurationController::currencyList', 'auth_rule_configuration_currency');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (27, 'app\\admin\\controller\\ConfigurationController::currencyUpdate', 'auth_rule_configuration_currency_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (28, 'app\\admin\\controller\\OrderController::orderList', 'auth_rule_order_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (29, 'app\\admin\\controller\\OrderController::index', 'auth_rule_order_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (30, 'app\\admin\\controller\\OrderController::create', 'auth_rule_order_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (31, 'app\\admin\\controller\\OrderController::updateAmount', 'auth_rule_order_amount_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (32, 'app\\admin\\controller\\OrderController::paid', 'auth_rule_order_status_paid');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (33, 'app\\admin\\controller\\OrderController::delete', 'auth_rule_order_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (34, 'app\\admin\\controller\\TransactionController::transactionList', 'auth_rule_transaction_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (35, 'app\\admin\\controller\\TransactionController::create', 'auth_rule_transaction_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (36, 'app\\admin\\controller\\TransactionController::delete', 'auth_rule_transaction_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (37, 'app\\admin\\controller\\HostController::hostList', 'auth_rule_host_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (38, 'app\\admin\\controller\\HostController::index', 'auth_rule_host_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (39, 'app\\admin\\controller\\HostController::update', 'auth_rule_host_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (40, 'app\\admin\\controller\\HostController::delete', 'auth_rule_host_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (41, 'app\\admin\\controller\\PluginController::pluginList', 'auth_rule_plugin_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (42, 'app\\admin\\controller\\PluginController::setting', 'auth_rule_plugin_setting');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (43, 'app\\admin\\controller\\PluginController::status', 'auth_rule_plugin_status');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (44, 'app\\admin\\controller\\PluginController::install', 'auth_rule_plugin_install');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (45, 'app\\admin\\controller\\PluginController::uninstall', 'auth_rule_plugin_uninstall');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (46, 'app\\admin\\controller\\PluginController::settingPost', 'auth_rule_plugin_setting_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (47, 'app\\admin\\controller\\NoticeEmailController::emailTemplateList', 'auth_rule_email_template_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (48, 'app\\admin\\controller\\NoticeEmailController::create', 'auth_rule_email_template_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (49, 'app\\admin\\controller\\NoticeEmailController::index', 'auth_rule_email_template_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (50, 'app\\admin\\controller\\NoticeEmailController::update', 'auth_rule_email_template_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (51, 'app\\admin\\controller\\NoticeEmailController::delete', 'auth_rule_email_template_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (52, 'app\\admin\\controller\\NoticeEmailController::test', 'auth_rule_email_template_test');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (53, 'app\\admin\\controller\\NoticeSmsController::templateList', 'auth_rule_sms_template_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (54, 'app\\admin\\controller\\NoticeSmsController::create', 'auth_rule_sms_template_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (55, 'app\\admin\\controller\\NoticeSmsController::index', 'auth_rule_sms_template_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (56, 'app\\admin\\controller\\NoticeSmsController::update', 'auth_rule_sms_template_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (57, 'app\\admin\\controller\\NoticeSmsController::delete', 'auth_rule_sms_template_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (58, 'app\\admin\\controller\\NoticeSmsController::test', 'auth_rule_sms_template_test');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (59, 'app\\admin\\controller\\NoticeSettingController::settingList', 'auth_rule_notice_setting_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (60, 'app\\admin\\controller\\NoticeSettingController::update', 'auth_rule_notice_setting_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (61, 'app\\admin\\controller\\TaskController::taskList', 'auth_rule_task_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (62, 'app\\admin\\controller\\TaskController::retry', 'auth_rule_task_retry');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (63, 'app\\admin\\controller\\LogController::systemLogList', 'auth_rule_system_log_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (64, 'app\\admin\\controller\\LogController::emailLogList', 'auth_rule_email_log_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (65, 'app\\admin\\controller\\LogController::smsLogList', 'auth_rule_sms_log_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (66, 'app\\admin\\controller\\ProductController::productList', 'auth_rule_product_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (67, 'app\\admin\\controller\\ProductController::index', 'auth_rule_product_index');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (68, 'app\\admin\\controller\\ProductController::create', 'auth_rule_product_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (69, 'app\\admin\\controller\\ProductController::update', 'auth_rule_product_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (70, 'app\\admin\\controller\\ProductController::order', 'auth_rule_product_order');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (71, 'app\\admin\\controller\\ProductController::delete', 'auth_rule_product_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (72, 'app\\admin\\controller\\ProductController::hidden', 'auth_rule_product_hidden');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (73, 'app\\admin\\controller\\ProductGroupController::create', 'auth_rule_product_group_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (74, 'app\\admin\\controller\\ProductGroupController::moveProduct', 'auth_rule_product_group_move_product');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (75, 'app\\admin\\controller\\ProductGroupController::delete', 'auth_rule_product_group_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (76, 'app\\admin\\controller\\ProductGroupController::productGroupFirstList', 'auth_rule_product_group_first_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (77, 'app\\admin\\controller\\ProductGroupController::productGroupSecondList', 'auth_rule_product_group_second_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (78, 'app\\admin\\controller\\ClientController::login', 'auth_rule_client_login');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (79, 'app\\admin\\controller\\ConfigurationController::cronList', 'auth_rule_configuration_cron');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (80, 'app\\admin\\controller\\ConfigurationController::cronUpdate', 'auth_rule_configuration_cron_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (81, 'app\\admin\\controller\\ProductGroupController::update', 'auth_rule_product_group_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (82, 'app\\admin\\controller\\ProductController::upgrade', 'auth_rule_product_upgrade');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (83, 'app\\admin\\controller\\ServerGroupController::serverGroupList', 'auth_rule_server_group_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (84, 'app\\admin\\controller\\ServerGroupController::create', 'auth_rule_server_group_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (85, 'app\\admin\\controller\\ServerGroupController::update', 'auth_rule_server_group_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (86, 'app\\admin\\controller\\ServerGroupController::delete', 'auth_rule_server_group_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (87, 'app\\admin\\controller\\ServerController::serverList', 'auth_rule_server_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (88, 'app\\admin\\controller\\ServerController::create', 'auth_rule_server_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (89, 'app\\admin\\controller\\ServerController::update', 'auth_rule_server_update');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (90, 'app\\admin\\controller\\ServerController::delete', 'auth_rule_server_delete');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (91, 'app\\admin\\controller\\ServerController::status', 'auth_rule_server_status');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (92, 'app\\admin\\controller\\ModuleController::moduleList', 'auth_rule_module_list');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (93, 'app\\admin\\controller\\HostController::adminArea', 'auth_rule_host_module');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (94, 'app\\admin\\controller\\HostController::changeConfigOption', 'auth_rule_host_upgrade_config_option');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (95, 'app\\admin\\controller\\HostController::changeConfigOptionCalculatePrice', 'auth_rule_host_upgrade_config_option_price');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (96, 'app\\admin\\controller\\HostController::createAccount', 'auth_rule_host_module_create');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (97, 'app\\admin\\controller\\HostController::suspendAccount', 'auth_rule_host_module_suspend');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (98, 'app\\admin\\controller\\HostController::unsuspendAccount', 'auth_rule_host_module_unsuspend');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (99, 'app\\admin\\controller\\HostController::terminateAccount', 'auth_rule_host_module_terminate');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (100, 'app\\admin\\controller\\ProductController::moduleServerConfigOption', 'auth_rule_product_server_config_option');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (101, 'app\\admin\\controller\\ProductController::moduleAdminConfigOption', 'auth_rule_product_config_option');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (102, 'app\\admin\\controller\\ProductController::moduleCalculatePrice', 'auth_rule_product_config_option_price');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (103, 'app\\admin\\controller\\ClientController::status', 'auth_rule_client_status');

INSERT INTO `idcsmart_auth_rule` (`id`, `name`, `title`) VALUES (104, 'app\\admin\\controller\\OrderController::getUpgradeAmount', 'auth_rule_order_upgrade_amount');

DROP TABLE IF EXISTS `idcsmart_auth`;
CREATE TABLE `idcsmart_auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '权限标题,存语言的键',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '页面地址',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父权限ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';
DROP TABLE IF EXISTS `idcsmart_auth_rule_link`;
CREATE TABLE `idcsmart_auth_rule_link` (
  `auth_rule_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限规则ID',
  `auth_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  KEY `auth_rule_id` (`auth_rule_id`),
  KEY `auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限规则对应表';
INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (1, 'auth_user_management', '', 1, 0);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (2, 'auth_user_list', 'client.html', 2, 1);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (3, 'auth_view', '', 3, 2);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (12, 3);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (4, 'auth_add', '', 4, 2);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (14, 4);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (5, 'auth_user_details', '', 5, 1);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (6, 'auth_view', '', 6, 5);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (13, 6);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (7, 'auth_management', '', 7, 5);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (15, 7);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (103, 7);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (8, 'auth_delete', '', 8, 5);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (16, 8);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (9, 'auth_recharge_record', '', 9, 1);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (10, 'auth_view', '', 10, 9);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (18, 10);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (11, 'auth_management', '', 11, 9);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (19, 11);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (12, 'auth_log', '', 12, 1);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (63, 12);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (64, 12); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (65, 12); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (13, 'auth_business_management', '', 13, 0);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (14, 'auth_order_management', 'order.html', 14, 13);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (15, 'auth_view', '', 15, 14);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (28, 15);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (29, 15);

# 新建订单功能待开发

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (16, 'auth_add', '', 16, 14);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (30, 16);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (17, 16);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (82, 16);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (66, 16);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (101, 16);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (102, 16);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (104, 16);

# 新建订单功能结束

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (17, 'auth_delete', '', 17, 14);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (33, 17);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (18, 'auth_marker_payment', '', 18, 14);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (32, 18);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (19, 'auth_adjustment_amount', '', 19, 13);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (31, 19);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (20, 'auth_host_management', 'host.html', 20, 13);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (21, 'auth_view', '', 21, 20);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (37, 21);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (22, 'auth_delete', '', 22, 20);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (40, 22);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (23, 'auth_host_details', '', 23, 20);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (38, 23);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (39, 23);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (24, 'auth_module_management', '', 24, 20);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (93, 24);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (94, 24);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (95, 24);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (96, 24);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (97, 24);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (98, 24);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (99, 24);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (25, 'auth_transaction', 'transaction.html', 25, 13);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (26, 'auth_view', '', 26, 25);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (34, 26);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (27, 'auth_add', '', 27, 25);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (35, 27);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (17, 27);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (28, 'auth_delete', '', 28, 25);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (36, 28);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (29, 'auth_product_management', '', 29, 0);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (30, 'auth_product_management', 'product.html', 30, 29);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (31, 'auth_view', '', 31, 30);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (66, 31);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (67, 31);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (32, 'auth_add', '', 32, 30);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (68, 32);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (76, 32);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (77, 32);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (33, 'auth_delete', '', 33, 30);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (71, 33);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (34, 'auth_management', '', 34, 30);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (69, 34);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (70, 34);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (72, 34);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (83, 34);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (87, 34);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (100, 34);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (35, 'auth_product_group', '', 35, 29);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (36, 'auth_add', '', 36, 35);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (73, 36);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (76, 36);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (37, 'auth_delete', '', 37, 35);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (75, 37);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (38, 'auth_management', '', 38, 35);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (74, 38);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (81, 38);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (39, 'auth_server_management', 'server.html', 39, 29);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (40, 'auth_view', '', 40, 39);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (87, 40);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (41, 'auth_add', '', 41, 39);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (88, 41);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (92, 41);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (42, 'auth_delete', '', 42, 39);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (90, 42);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (43, 'auth_management', '', 43, 39);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (89, 43);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (91, 43);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (92, 43);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (44, 'auth_server_group', 'server_group.html', 44, 29);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (45, 'auth_view', '', 45, 44);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (83, 45);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (46, 'auth_add', '', 46, 44);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (84, 46);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (87, 46);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (47, 'auth_delete', '', 47, 44);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (86, 47);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (48, 'auth_management', '', 48, 44);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (85, 48);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (87, 48);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (49, 'auth_system_settings', '', 49, 0);  

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (50, 'auth_system_settings', 'configuration_system.html', 50, 49); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (51, 'auth_system_settings', '', 51, 50);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (20, 51); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (21, 51); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (52, 'auth_login_settings', '', 52, 50);  

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (22, 52); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (23, 52);  

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (53, 'auth_admin_settings', 'admin.html', 53, 49); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (54, 'auth_view', '', 54, 53); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (1, 54); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (55, 'auth_add', '', 55, 53); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (3, 55); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (7, 55); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (56, 'auth_delete', '', 56, 53); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (5, 56); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (57, 'auth_management', '', 57, 53); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (2, 57); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (4, 57); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (6, 57); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (7, 57); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (58, 'auth_admin_group', 'admin_role.html', 58, 53); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (59, 'auth_view', '', 59, 58); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (7, 59); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (60, 'auth_add', '', 60, 58); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (9, 60); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (61, 'auth_delete', '', 61, 58);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (11, 61); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (62, 'auth_management', '', 62, 58);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (8, 62); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (10, 62); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (63, 'auth_security_settings', 'configuration_security.html', 63, 49);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (24, 63); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (25, 63); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (64, 'auth_currency_settings', 'configuration_currency.html', 64, 49);

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (26, 64); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (27, 64); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (65, 'auth_payment_gateway', 'gateway.html', 65, 49);

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (66, 'auth_view', '', 66, 65); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (41, 66); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (67, 'auth_enable_disable', '', 67, 65); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (43, 67); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (68, 'auth_install_uninstall_config', '', 68, 65); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (42, 68); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (44, 68); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (45, 68); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (46, 68); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (69, 'auth_notice', '', 69, 0); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (70, 'auth_sms_notice', 'notice_sms.html', 70, 69); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (71, 'auth_view', '', 71, 70); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (41, 71); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (72, 'auth_enable_disable', '', 72, 70); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (43, 72); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (73, 'auth_install_uninstall_config', '', 73, 70); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (42, 73); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (44, 73); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (45, 73); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (46, 73); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (74, 'auth_template_management', '', 74, 70); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (53, 74); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (54, 74); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (55, 74); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (56, 74); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (57, 74); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (58, 74); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (75, 'auth_email_notice', 'notice_email.html', 75, 69); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (76, 'auth_view', '', 76, 75); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (41, 76); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (77, 'auth_enable_disable', '', 77, 75); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (43, 77); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (78, 'auth_install_uninstall_config', '', 78, 75); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (42, 78); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (44, 78); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (45, 78); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (46, 78); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (79, 'auth_template_management', '', 79, 75); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (47, 79); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (48, 79); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (49, 79); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (50, 79); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (51, 79); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (52, 79); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (80, 'auth_send_settings', 'notice_send.html', 80, 69); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (59, 80); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (60, 80); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (81, 'auth_management', '', 81, 0); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (82, 'auth_task', 'task.html', 82, 81); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (83, 'auth_view', '', 83, 82); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (61, 83); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (84, 'auth_management', '', 84, 82); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (62, 84); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (85, 'auth_log', 'log_system.html', 85, 81); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (86, 'auth_system_log', 'log_system.html', 86, 85); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (63, 86); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (87, 'auth_notice_log', 'log_notice_sms.html', 87, 85); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (64, 87); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (65, 87); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (88, 'auth_auto', 'cron.html', 88, 81); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (89, 'auth_view', '', 89, 88); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (79, 89); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (90, 'auth_management', '', 90, 88); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (80, 90); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (91, 'auth_plugin', '', 91, 0); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (92, 'auth_plugin_list', 'plugin.html', 92, 91); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (93, 'auth_view', '', 93, 92); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (41, 93); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (94, 'auth_enable_disable', '', 94, 92); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (43, 94); 

INSERT INTO `idcsmart_auth` (`id`, `title`, `url`, `order`, `parent_id`) VALUES (95, 'auth_install_uninstall_config', '', 95, 92); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (42, 95); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (44, 95); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (45, 95); 

INSERT INTO `idcsmart_auth_rule_link` (`auth_rule_id`, `auth_id`) VALUES (46, 95); 

/*Table structure for table `idcsmart_cart` */

DROP TABLE IF EXISTS `idcsmart_cart`;

CREATE TABLE `idcsmart_cart` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `data` text NOT NULL COMMENT '购物车数据',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='购物车表';

/*Table structure for table `idcsmart_client` */

DROP TABLE IF EXISTS `idcsmart_client`;

CREATE TABLE `idcsmart_client` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态:0禁用',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮件',
  `phone_code` int(11) NOT NULL DEFAULT '44' COMMENT '国际电话区号  默认44中国为+86',
  `phone` varchar(100) NOT NULL DEFAULT '' COMMENT '电话',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `credit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
  `country` varchar(100) NOT NULL DEFAULT '' COMMENT '国家',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `language` varchar(100) NOT NULL DEFAULT '' COMMENT '语言',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_action_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

/*Table structure for table `idcsmart_client_credit` */

DROP TABLE IF EXISTS `idcsmart_client_credit`;

CREATE TABLE `idcsmart_client_credit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户余额变更记录ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型:人工Artificial 充值Recharge 应用至订单Applied 超付Overpayment 少付Underpayment 退款Refund',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `credit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更后余额',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户余额变更记录表';

/*Table structure for table `idcsmart_client_login` */

DROP TABLE IF EXISTS `idcsmart_client_login`;

CREATE TABLE `idcsmart_client_login` (
  `client_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `last_action_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '记录创建时间',
  `jwt_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'jwt的签发密钥',
  KEY `client_login_ip` (`client_id`,`last_login_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `idcsmart_config_option` */

DROP TABLE IF EXISTS `idcsmart_config_option`;

CREATE TABLE `idcsmart_config_option` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自定义配置ID',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '模块名称',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `field` varchar(100) NOT NULL DEFAULT '' COMMENT '字段名',
  `data` text NOT NULL COMMENT '配置规则',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义配置表';

/*Table structure for table `idcsmart_config_option_sub` */

DROP TABLE IF EXISTS `idcsmart_config_option_sub`;

CREATE TABLE `idcsmart_config_option_sub` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自定义配置子项ID',
  `config_option_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义配置ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `field` varchar(100) NOT NULL DEFAULT '' COMMENT '字段名',
  `data` text NOT NULL COMMENT '子项规则',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `config_option_id` (`config_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义配置子项';

/*Table structure for table `idcsmart_configuration` */

DROP TABLE IF EXISTS `idcsmart_configuration`;

CREATE TABLE `idcsmart_configuration` (
  `setting` text NOT NULL,
  `value` text NOT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `description` text NOT NULL COMMENT '说明',
  KEY `setting` (`setting`(32)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

/*Table structure for table `idcsmart_country` */

DROP TABLE IF EXISTS `idcsmart_country`;

CREATE TABLE `idcsmart_country` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `iso` varchar(80) NOT NULL,
  `iso3` varchar(80) NOT NULL,
  `name` varchar(80) NOT NULL,
  `name_zh` varchar(80) NOT NULL,
  `nicename` varchar(80) NOT NULL,
  `num_code` smallint(6) NOT NULL,
  `phone_code` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_code` (`phone_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

/*Table structure for table `idcsmart_email_log` */

DROP TABLE IF EXISTS `idcsmart_email_log`;

CREATE TABLE `idcsmart_email_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '邮件日志ID',
  `subject` varchar(100) NOT NULL DEFAULT '' COMMENT '邮件标题',
  `message` text NOT NULL COMMENT '邮件内容',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送状态:1成功,0失败',
  `to` varchar(100) NOT NULL DEFAULT '' COMMENT '收件邮箱',
  `fail_reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '失败原因',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `type` varchar(20) NOT NULL DEFAULT 'client' COMMENT '关联类型:client表示rel_id关联客户id,admin表示rel_id关联管理员id',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'ip',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '端口号',
  PRIMARY KEY (`id`),
  KEY `rel_id_type` (`rel_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

/*Table structure for table `idcsmart_email_template` */

DROP TABLE IF EXISTS `idcsmart_email_template`;

CREATE TABLE `idcsmart_email_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '邮件模板ID',
  `subject` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
  `message` text NOT NULL COMMENT '模板内容',
  `attachment` text NOT NULL COMMENT '附件',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='邮件模板表';

/*Table structure for table `idcsmart_host` */

DROP TABLE IF EXISTS `idcsmart_host`;

CREATE TABLE `idcsmart_host` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '产品ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `server_id` int(11) NOT NULL DEFAULT '0' COMMENT '服务器ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '标识名称',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败',
  `suspend_reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '暂停原因',
  `suspend_time` int(11) NOT NULL DEFAULT '0' COMMENT '暂停时间',
  `gateway` varchar(100) NOT NULL DEFAULT '' COMMENT '支付方式',
  `gateway_name` varchar(100) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `first_payment_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '首付金额',
  `renew_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费金额',
  `billing_cycle` varchar(100) NOT NULL DEFAULT '' COMMENT '计费周期免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid',
  `billing_cycle_name` varchar(100) NOT NULL DEFAULT '' COMMENT '模块计费周期名称',
  `billing_cycle_time` int(11) NOT NULL DEFAULT '0' COMMENT '模块计费周期时间,秒',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `active_time` int(11) NOT NULL DEFAULT '0' COMMENT '开通时间',
  `due_time` int(11) NOT NULL DEFAULT '0' COMMENT '到期时间',
  `termination_time` int(11) NOT NULL DEFAULT '0' COMMENT '终止时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='产品表';

/*Table structure for table `idcsmart_host_config_option` */

DROP TABLE IF EXISTS `idcsmart_host_config_option`;

CREATE TABLE `idcsmart_host_config_option` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '产品和自定义配置关联ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `config_option_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义配置ID',
  `config_option_sub_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义配置子项ID',
  `data` text CHARACTER SET utf8 NOT NULL COMMENT '配置数据',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `config_option_id` (`config_option_id`),
  KEY `config_option_sub_id` (`config_option_sub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品和自定义配置关联表';

/*Table structure for table `idcsmart_menu` */

DROP TABLE IF EXISTS `idcsmart_menu`;

CREATE TABLE `idcsmart_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT 'client会员中心,www_top官网顶部,www_bottom官网底部',
  `nav_list` text NOT NULL COMMENT '导航列表',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='导航菜单表';

/*Table structure for table `idcsmart_menu_active` */

DROP TABLE IF EXISTS `idcsmart_menu_active`;

CREATE TABLE `idcsmart_menu_active` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT 'client会员中心,www_top官网顶部,www_bottom官网底部',
  `menu_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_package`;

/*Table structure for table `idcsmart_nav` */

DROP TABLE IF EXISTS `idcsmart_nav`;

CREATE TABLE `idcsmart_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `nav_type` tinyint(7) NOT NULL DEFAULT '0' COMMENT '导航类型 0系统类型,1自定义页面,2产品中心',
  `plugin` varchar(50) NOT NULL DEFAULT '' COMMENT '插件名称:插件菜单,此值用于卸载时删除菜单',
  `menu_type` varchar(20) NOT NULL DEFAULT 'client' COMMENT 'client会员中心,www_top官网顶部,www_bottom官网底部',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='导航表';

/*Table structure for table `idcsmart_notice_setting` */

DROP TABLE IF EXISTS `idcsmart_notice_setting`;

CREATE TABLE `idcsmart_notice_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '发送管理',
  `name` varchar(100) NOT NULL COMMENT '动作标识名称',
  `name_lang` varchar(100) NOT NULL DEFAULT '' COMMENT '动作名称',
  `sms_global_name` varchar(50) NOT NULL DEFAULT '' COMMENT '短信国际接口名称',
  `sms_global_template` int(10) NOT NULL DEFAULT '0' COMMENT '短信国际接口模板id',
  `sms_name` varchar(50) NOT NULL DEFAULT '' COMMENT '短信国内接口名称',
  `sms_template` int(10) NOT NULL DEFAULT '0' COMMENT '短信国内接口模板id',
  `sms_enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0禁用,1启用',
  `email_name` varchar(50) NOT NULL DEFAULT '' COMMENT '邮件接口名称',
  `email_template` int(10) NOT NULL DEFAULT '0' COMMENT '邮件接口模板id',
  `email_enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0禁用,1启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `idcsmart_order` */

DROP TABLE IF EXISTS `idcsmart_order`;

CREATE TABLE `idcsmart_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(10) NOT NULL DEFAULT 'new' COMMENT '类型new新订单renew续费订单upgrade升降级订单artificial人工订单',
  `status` varchar(10) NOT NULL DEFAULT 'Unpaid' COMMENT '状态Paid已付款Unpaid未付款',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `credit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `amount_unpaid` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '未付款金额',
  `upgrade_refund` tinyint(1) NOT NULL DEFAULT '1' COMMENT '升降级是否退款0否1是',
  `gateway` varchar(100) NOT NULL DEFAULT '' COMMENT '支付方式',
  `gateway_name` varchar(100) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `pay_time` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `due_time` int(11) NOT NULL DEFAULT '0' COMMENT '到期时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `amount` (`amount`),
  KEY `pay_time` (`pay_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='订单表';

/*Table structure for table `idcsmart_order_item` */

DROP TABLE IF EXISTS `idcsmart_order_item`;

CREATE TABLE `idcsmart_order_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单子项ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '关联类型',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `gateway` varchar(100) NOT NULL DEFAULT '' COMMENT '支付方式',
  `gateway_name` varchar(100) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `client_id` (`client_id`),
  KEY `rel_id` (`type`,`rel_id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='订单子项表';

/*Table structure for table `idcsmart_order_tmp` */

DROP TABLE IF EXISTS `idcsmart_order_tmp`;

CREATE TABLE `idcsmart_order_tmp` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `tmp_order_id` varchar(50) NOT NULL DEFAULT '' COMMENT '21-22位临时订单ID,传入三方接口',
  `tmp_order_id2` varchar(30) NOT NULL DEFAULT '' COMMENT '18位临时订单ID规则2,传入三方接口',
  `tmp_order_id3` varchar(20) NOT NULL DEFAULT '' COMMENT '10位临时订单ID规则3,传入三方接口',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间,72小时定时任务清除',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `tmp_order_id` (`tmp_order_id`),
  KEY `tmp_order_id2` (`tmp_order_id2`),
  KEY `tmp_order_id3` (`tmp_order_id3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `idcsmart_plugin` */

DROP TABLE IF EXISTS `idcsmart_plugin`;

CREATE TABLE `idcsmart_plugin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态;1:开启;0:禁用',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识名,英文字母(唯一)',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '插件名称',
  `url` text COMMENT '图标地址',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '作者',
  `author_url` text COMMENT '作者链接',
  `version` varchar(20) NOT NULL DEFAULT '1.0' COMMENT '插件版本号',
  `description` text COMMENT '插件描述',
  `config` text COMMENT '插件配置',
  `module` varchar(25) NOT NULL DEFAULT '' COMMENT '插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `help_url` varchar(1000) NOT NULL DEFAULT '' COMMENT '帮助文档',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '插件安装时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '插件更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='插件表';

/*Table structure for table `idcsmart_plugin_hook` */

DROP TABLE IF EXISTS `idcsmart_plugin_hook`;

CREATE TABLE `idcsmart_plugin_hook` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `plugin` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识名',
  `module` varchar(25) NOT NULL DEFAULT 'addon' COMMENT '插件所属模块',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `plugin` (`plugin`),
  KEY `module` (`plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='插件钩子表';

/*Table structure for table `idcsmart_price` */

DROP TABLE IF EXISTS `idcsmart_price`;

CREATE TABLE `idcsmart_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模块周期价格表ID',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '模块名称',
  `config_option_sub_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义配置子项ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '标识',
  `billing_cycle` int(11) NOT NULL DEFAULT '0' COMMENT '计费周期,按秒存储',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模块周期价格表';

/*Table structure for table `idcsmart_product` */

DROP TABLE IF EXISTS `idcsmart_product`;

CREATE TABLE `idcsmart_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品表ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
  `product_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属商品组ID',
  `description` text COMMENT '商品描述',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0显示默认，1隐藏',
  `stock_control` tinyint(1) NOT NULL DEFAULT '0' COMMENT '库存控制(1:启用)默认0',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '库存数量(与stock_control有关)',
  `pay_type` varchar(255) NOT NULL DEFAULT '' COMMENT '付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid',
  `auto_setup` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否自动开通:1是默认,0否',
  `type` varchar(50) NOT NULL DEFAULT 'server_group' COMMENT '关联类型:server,server_group',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `creating_notice_sms` tinyint(1) NOT NULL DEFAULT '1' COMMENT '开通中短信通知是否开启:1开启默认,0关闭',
  `creating_notice_sms_api` int(11) NOT NULL DEFAULT '0' COMMENT '开通中短信通知接口,默认0',
  `creating_notice_sms_api_template` int(11) NOT NULL DEFAULT '0' COMMENT '开通中短信通知接口模板,默认0',
  `created_notice_sms` tinyint(1) NOT NULL DEFAULT '1' COMMENT '已开通短信通知是否开启:1开启默认,0关闭',
  `created_notice_sms_api` int(11) NOT NULL DEFAULT '0' COMMENT '已开通短信通知接口,默认0',
  `created_notice_sms_api_template` int(11) NOT NULL DEFAULT '0' COMMENT '已开通短信通知接口模板,默认0',
  `creating_notice_mail` tinyint(1) NOT NULL DEFAULT '1' COMMENT '开通中邮件通知是否开启:1开启默认,0关闭',
  `creating_notice_mail_api` int(11) NOT NULL DEFAULT '0' COMMENT '开通中邮件通知接口',
  `creating_notice_mail_template` int(11) NOT NULL DEFAULT '0' COMMENT '开通中邮件通知模板,默认0',
  `created_notice_mail` tinyint(1) NOT NULL DEFAULT '1' COMMENT '已开通邮件通知是否开启:1开启默认,0关闭',
  `created_notice_mail_api` int(11) NOT NULL DEFAULT '0' COMMENT '已开通邮件通知接口',
  `created_notice_mail_template` int(11) NOT NULL DEFAULT '0' COMMENT '已开通邮件通知模板,默认0',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级商品ID',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_group_id` (`product_group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='商品表';

/*Table structure for table `idcsmart_product_group` */

DROP TABLE IF EXISTS `idcsmart_product_group`;

CREATE TABLE `idcsmart_product_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品组表ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品组名称',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认0，1：隐藏',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序，默认处理为添加自增长',
  `parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '父分组ID,0表示此分组为一级分组',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order` (`order`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='商品组表';

/*Table structure for table `idcsmart_product_upgrade_product` */

DROP TABLE IF EXISTS `idcsmart_product_upgrade_product`;

CREATE TABLE `idcsmart_product_upgrade_product` (
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `upgrade_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '可升降级商品ID',
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Table structure for table `idcsmart_server` */

DROP TABLE IF EXISTS `idcsmart_server`;

CREATE TABLE `idcsmart_server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '接口ID',
  `server_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '接口分组ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '接口名称',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '模块名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `hash` text NOT NULL COMMENT '哈希',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态0禁用1启用',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  KEY `server_group_id` (`server_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='接口表';

/*Table structure for table `idcsmart_server_group` */

DROP TABLE IF EXISTS `idcsmart_server_group`;

CREATE TABLE `idcsmart_server_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '接口分组ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '接口分组名称',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='接口分组表';

/*Table structure for table `idcsmart_sms_log` */

DROP TABLE IF EXISTS `idcsmart_sms_log`;

CREATE TABLE `idcsmart_sms_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '短信日志ID',
  `phone_code` int(11) NOT NULL DEFAULT '44' COMMENT '国际区号',
  `phone` varchar(100) NOT NULL DEFAULT '' COMMENT '手机号',
  `template_code` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标识',
  `content` text NOT NULL COMMENT '模板内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送状态:1成功,0失败',
  `fail_reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '失败原因',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `type` varchar(20) NOT NULL DEFAULT 'client' COMMENT '关联类型:client表示rel_id关联客户id,admin表示rel_id关联管理员id',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'ip',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '端口号',
  PRIMARY KEY (`id`),
  KEY `rel_id_type` (`rel_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='短信日志';

/*Table structure for table `idcsmart_sms_template` */

DROP TABLE IF EXISTS `idcsmart_sms_template`;

CREATE TABLE `idcsmart_sms_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` varchar(100) NOT NULL DEFAULT '' COMMENT '模板ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0大陆，1国际',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
  `content` text NOT NULL COMMENT '模板内容',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未提交，1审核中，2通过，3未通过',
  `sms_name` varchar(50) NOT NULL DEFAULT '' COMMENT '模板接口标识名称',
  `error` varchar(1000) NOT NULL DEFAULT '' COMMENT '模板提交接口审核错误信息',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='短信模板';

/*Table structure for table `idcsmart_system_log` */

DROP TABLE IF EXISTS `idcsmart_system_log`;

CREATE TABLE `idcsmart_system_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统日志ID',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `description` text NOT NULL COMMENT '描述',
  `user_type` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人类型client用户admin管理员system系统cron定时任务',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `user_name` varchar(100) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '端口',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `rel_id` (`type`,`rel_id`),
  KEY `user_id` (`user_type`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='系统日志表';

/*Table structure for table `idcsmart_task` */

DROP TABLE IF EXISTS `idcsmart_task`;

CREATE TABLE `idcsmart_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '关联类型',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '状态Wait未开始Exec执行中Finish完成Failed失败',
  `retry` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已重试0否1是',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `task_data` text NOT NULL COMMENT '任务数据',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `finish_time` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `fail_reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '执行失败原因',
  PRIMARY KEY (`id`),
  KEY `rel_id` (`type`,`rel_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='任务表';

/*Table structure for table `idcsmart_task_cron` */

DROP TABLE IF EXISTS `idcsmart_task_cron`;

CREATE TABLE `idcsmart_task_cron` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='任务表';

/*Table structure for table `idcsmart_task_wait` */

DROP TABLE IF EXISTS `idcsmart_task_wait`;

CREATE TABLE `idcsmart_task_wait` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '关联类型',
  `rel_id` int(10) NOT NULL DEFAULT '0',
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT 'taskID',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '状态Wait未开始Exec执行中Finish完成Failed失败',
  `retry` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已重试0否1是',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `task_data` text NOT NULL COMMENT '任务数据',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `finish_time` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `rel_id` (`type`,`task_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='任务表';

/*Table structure for table `idcsmart_transaction` */

DROP TABLE IF EXISTS `idcsmart_transaction`;

CREATE TABLE `idcsmart_transaction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '交易流水ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `gateway` varchar(50) NOT NULL DEFAULT '' COMMENT '支付方式',
  `gateway_name` varchar(50) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `transaction_number` varchar(100) NOT NULL DEFAULT '' COMMENT '交易流水号',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `client_id` (`client_id`),
  KEY `transaction_number` (`transaction_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='交易流水表';

/*Table structure for table `idcsmart_upgrade` */

DROP TABLE IF EXISTS `idcsmart_upgrade`;

CREATE TABLE `idcsmart_upgrade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '升降级ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `type` varchar(20) NOT NULL DEFAULT 'product' COMMENT '类型product商品,config_option配置',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `data` text NOT NULL COMMENT '配置',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '模块价格',
  `billing_cycle_name` varchar(100) NOT NULL DEFAULT '' COMMENT '模块计费周期名称',
  `billing_cycle_time` int(11) NOT NULL DEFAULT '0' COMMENT '模块计费周期时间,秒',
  `status` varchar(20) NOT NULL DEFAULT 'Unpaid' COMMENT '状态Unpaid未支付,Pending待执行,Completed已完成',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`),
  KEY `host_id` (`host_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='升降级表';