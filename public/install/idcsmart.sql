/*
SQLyog Ultimate v12.08 (64 bit)
MySQL - 5.7.37-log : Database - search
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `idcsmart_admin` */

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
) ENGINE=InnoDB AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8mb4 COMMENT='API密钥表';

/*Table structure for table `idcsmart_auth` */

DROP TABLE IF EXISTS `idcsmart_auth`;

CREATE TABLE `idcsmart_auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `title` varchar(1000) NOT NULL DEFAULT '' COMMENT '权限标题,存语言的键',
  `url` varchar(1000) NOT NULL DEFAULT '' COMMENT '页面地址',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父权限ID',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件',
  `plugin` varchar(100) NOT NULL DEFAULT '' COMMENT '插件',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

/*Data for the table `idcsmart_auth` */

insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (1,'auth_user_management','',1,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (2,'auth_user_list','client.html',2,1,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (3,'auth_view','',3,2,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (4,'auth_add','',4,2,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (5,'auth_user_details','',5,1,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (6,'auth_view','',6,5,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (7,'auth_management','',7,5,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (8,'auth_delete','',8,5,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (9,'auth_recharge_record','',9,1,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (10,'auth_view','',10,9,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (11,'auth_management','',11,9,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (12,'auth_user_log','client_log.html',12,1,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (13,'auth_business_management','',13,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (14,'auth_order_management','order.html',14,13,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (15,'auth_view','',15,14,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (16,'auth_add','',16,14,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (17,'auth_delete','',17,14,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (18,'auth_marker_payment','',18,14,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (19,'auth_adjustment_amount','',19,13,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (20,'auth_host_management','host.html',20,13,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (21,'auth_view','',21,20,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (22,'auth_delete','',22,20,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (23,'auth_host_details','',23,20,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (24,'auth_module_management','',24,20,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (25,'auth_transaction','transaction.html',25,13,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (26,'auth_view','',26,25,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (27,'auth_add','',27,25,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (28,'auth_delete','',28,25,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (29,'auth_product_management','',29,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (30,'auth_product_management','product.html',30,29,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (31,'auth_view','',31,30,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (32,'auth_add','',32,30,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (33,'auth_delete','',33,30,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (34,'auth_management','',34,30,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (35,'auth_product_group','',35,29,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (36,'auth_add','',36,35,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (37,'auth_delete','',37,35,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (38,'auth_management','',38,35,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (39,'auth_server_management','server.html',39,29,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (40,'auth_view','',40,39,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (41,'auth_add','',41,39,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (42,'auth_delete','',42,39,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (43,'auth_management','',43,39,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (44,'auth_server_group','server_group.html',44,29,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (45,'auth_view','',45,44,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (46,'auth_add','',46,44,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (47,'auth_delete','',47,44,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (48,'auth_management','',48,44,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (49,'auth_system_settings','',49,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (50,'auth_system_settings','configuration_system.html',50,49,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (51,'auth_system_settings','',51,50,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (52,'auth_login_settings','',52,50,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (53,'auth_admin_settings','admin.html',53,49,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (54,'auth_view','',54,53,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (55,'auth_add','',55,53,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (56,'auth_delete','',56,53,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (57,'auth_management','',57,53,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (58,'auth_admin_group','admin_role.html',58,53,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (59,'auth_view','',59,58,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (60,'auth_add','',60,58,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (61,'auth_delete','',61,58,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (62,'auth_management','',62,58,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (63,'auth_security_settings','configuration_security.html',63,49,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (64,'auth_currency_settings','configuration_currency.html',64,49,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (65,'auth_payment_gateway','gateway.html',65,49,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (66,'auth_view','',66,65,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (67,'auth_enable_disable','',67,65,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (68,'auth_install_uninstall_config','',68,65,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (69,'auth_notice','',69,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (70,'auth_sms_notice','notice_sms.html',70,69,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (71,'auth_view','',71,70,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (72,'auth_enable_disable','',72,70,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (73,'auth_install_uninstall_config','',73,70,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (74,'auth_template_management','',74,70,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (75,'auth_email_notice','notice_email.html',75,69,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (76,'auth_view','',76,75,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (77,'auth_enable_disable','',77,75,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (78,'auth_install_uninstall_config','',78,75,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (79,'auth_template_management','',79,75,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (80,'auth_send_settings','notice_send.html',80,69,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (81,'auth_management','',81,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (82,'auth_task','task.html',82,81,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (83,'auth_view','',83,82,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (84,'auth_management','',84,82,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (85,'auth_log','log_system.html',85,81,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (86,'auth_system_log','log_system.html',86,85,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (87,'auth_notice_log','log_notice_sms.html',87,85,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (88,'auth_auto','cron.html',88,81,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (89,'auth_view','',89,88,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (90,'auth_management','',90,88,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (91,'auth_plugin','',91,0,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (92,'auth_plugin_list','plugin.html',92,91,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (93,'auth_view','',93,92,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (94,'auth_enable_disable','',94,92,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (95,'auth_install_uninstall_config','',95,92,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (96,'auth_nav_management','',96,49,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (97,'auth_view','',97,96,'','');
insert  into `idcsmart_auth`(`id`,`title`,`url`,`order`,`parent_id`,`module`,`plugin`) values (98,'auth_view','',98,96,'','');

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
  `title` varchar(1000) NOT NULL DEFAULT '' COMMENT '规则描述',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件',
  `plugin` varchar(100) NOT NULL DEFAULT '' COMMENT '插件',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='权限规则表';

/*Data for the table `idcsmart_auth_rule` */

insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (1,'app\\admin\\controller\\AdminController::adminList','auth_rule_admin_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (2,'app\\admin\\controller\\AdminController::index','auth_rule_admin_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (3,'app\\admin\\controller\\AdminController::create','auth_rule_admin_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (4,'app\\admin\\controller\\AdminController::update','auth_rule_admin_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (5,'app\\admin\\controller\\AdminController::delete','auth_rule_admin_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (6,'app\\admin\\controller\\AdminController::status','auth_rule_admin_status','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (7,'app\\admin\\controller\\AdminRoleController::adminRoleList','auth_rule_admin_role_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (8,'app\\admin\\controller\\AdminRoleController::index','auth_rule_admin_role_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (9,'app\\admin\\controller\\AdminRoleController::create','auth_rule_admin_role_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (10,'app\\admin\\controller\\AdminRoleController::update','auth_rule_admin_role_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (11,'app\\admin\\controller\\AdminRoleController::delete','auth_rule_admin_role_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (12,'app\\admin\\controller\\ClientController::clientList','auth_rule_client_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (13,'app\\admin\\controller\\ClientController::index','auth_rule_client_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (14,'app\\admin\\controller\\ClientController::create','auth_rule_client_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (15,'app\\admin\\controller\\ClientController::update','auth_rule_client_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (16,'app\\admin\\controller\\ClientController::delete','auth_rule_client_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (17,'app\\admin\\controller\\ClientController::search','auth_rule_client_search','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (18,'app\\admin\\controller\\ClientCreditController::clientCreditList','auth_rule_client_credit_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (19,'app\\admin\\controller\\ClientCreditController::update','auth_rule_client_credit_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (20,'app\\admin\\controller\\ConfigurationController::systemList','auth_rule_configuration_system','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (21,'app\\admin\\controller\\ConfigurationController::systemUpdate','auth_rule_configuration_system_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (22,'app\\admin\\controller\\ConfigurationController::loginList','auth_rule_configuration_login','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (23,'app\\admin\\controller\\ConfigurationController::loginUpdate','auth_rule_configuration_login_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (24,'app\\admin\\controller\\ConfigurationController::securityList','auth_rule_configuration_security','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (25,'app\\admin\\controller\\ConfigurationController::securityUpdate','auth_rule_configuration_security_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (26,'app\\admin\\controller\\ConfigurationController::currencyList','auth_rule_configuration_currency','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (27,'app\\admin\\controller\\ConfigurationController::currencyUpdate','auth_rule_configuration_currency_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (28,'app\\admin\\controller\\OrderController::orderList','auth_rule_order_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (29,'app\\admin\\controller\\OrderController::index','auth_rule_order_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (30,'app\\admin\\controller\\OrderController::create','auth_rule_order_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (31,'app\\admin\\controller\\OrderController::updateAmount','auth_rule_order_amount_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (32,'app\\admin\\controller\\OrderController::paid','auth_rule_order_status_paid','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (33,'app\\admin\\controller\\OrderController::delete','auth_rule_order_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (34,'app\\admin\\controller\\TransactionController::transactionList','auth_rule_transaction_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (35,'app\\admin\\controller\\TransactionController::create','auth_rule_transaction_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (36,'app\\admin\\controller\\TransactionController::delete','auth_rule_transaction_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (37,'app\\admin\\controller\\HostController::hostList','auth_rule_host_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (38,'app\\admin\\controller\\HostController::index','auth_rule_host_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (39,'app\\admin\\controller\\HostController::update','auth_rule_host_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (40,'app\\admin\\controller\\HostController::delete','auth_rule_host_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (41,'app\\admin\\controller\\PluginController::pluginList','auth_rule_plugin_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (42,'app\\admin\\controller\\PluginController::setting','auth_rule_plugin_setting','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (43,'app\\admin\\controller\\PluginController::status','auth_rule_plugin_status','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (44,'app\\admin\\controller\\PluginController::install','auth_rule_plugin_install','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (45,'app\\admin\\controller\\PluginController::uninstall','auth_rule_plugin_uninstall','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (46,'app\\admin\\controller\\PluginController::settingPost','auth_rule_plugin_setting_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (47,'app\\admin\\controller\\NoticeEmailController::emailTemplateList','auth_rule_email_template_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (48,'app\\admin\\controller\\NoticeEmailController::create','auth_rule_email_template_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (49,'app\\admin\\controller\\NoticeEmailController::index','auth_rule_email_template_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (50,'app\\admin\\controller\\NoticeEmailController::update','auth_rule_email_template_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (51,'app\\admin\\controller\\NoticeEmailController::delete','auth_rule_email_template_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (52,'app\\admin\\controller\\NoticeEmailController::test','auth_rule_email_template_test','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (53,'app\\admin\\controller\\NoticeSmsController::templateList','auth_rule_sms_template_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (54,'app\\admin\\controller\\NoticeSmsController::create','auth_rule_sms_template_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (55,'app\\admin\\controller\\NoticeSmsController::index','auth_rule_sms_template_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (56,'app\\admin\\controller\\NoticeSmsController::update','auth_rule_sms_template_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (57,'app\\admin\\controller\\NoticeSmsController::delete','auth_rule_sms_template_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (58,'app\\admin\\controller\\NoticeSmsController::test','auth_rule_sms_template_test','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (59,'app\\admin\\controller\\NoticeSettingController::settingList','auth_rule_notice_setting_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (60,'app\\admin\\controller\\NoticeSettingController::update','auth_rule_notice_setting_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (61,'app\\admin\\controller\\TaskController::taskList','auth_rule_task_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (62,'app\\admin\\controller\\TaskController::retry','auth_rule_task_retry','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (63,'app\\admin\\controller\\LogController::systemLogList','auth_rule_system_log_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (64,'app\\admin\\controller\\LogController::emailLogList','auth_rule_email_log_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (65,'app\\admin\\controller\\LogController::smsLogList','auth_rule_sms_log_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (66,'app\\admin\\controller\\ProductController::productList','auth_rule_product_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (67,'app\\admin\\controller\\ProductController::index','auth_rule_product_index','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (68,'app\\admin\\controller\\ProductController::create','auth_rule_product_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (69,'app\\admin\\controller\\ProductController::update','auth_rule_product_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (70,'app\\admin\\controller\\ProductController::order','auth_rule_product_order','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (71,'app\\admin\\controller\\ProductController::delete','auth_rule_product_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (72,'app\\admin\\controller\\ProductController::hidden','auth_rule_product_hidden','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (73,'app\\admin\\controller\\ProductGroupController::create','auth_rule_product_group_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (74,'app\\admin\\controller\\ProductGroupController::moveProduct','auth_rule_product_group_move_product','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (75,'app\\admin\\controller\\ProductGroupController::delete','auth_rule_product_group_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (76,'app\\admin\\controller\\ProductGroupController::productGroupFirstList','auth_rule_product_group_first_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (77,'app\\admin\\controller\\ProductGroupController::productGroupSecondList','auth_rule_product_group_second_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (78,'app\\admin\\controller\\ClientController::login','auth_rule_client_login','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (79,'app\\admin\\controller\\ConfigurationController::cronList','auth_rule_configuration_cron','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (80,'app\\admin\\controller\\ConfigurationController::cronUpdate','auth_rule_configuration_cron_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (81,'app\\admin\\controller\\ProductGroupController::update','auth_rule_product_group_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (82,'app\\admin\\controller\\ProductController::upgrade','auth_rule_product_upgrade','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (83,'app\\admin\\controller\\ServerGroupController::serverGroupList','auth_rule_server_group_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (84,'app\\admin\\controller\\ServerGroupController::create','auth_rule_server_group_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (85,'app\\admin\\controller\\ServerGroupController::update','auth_rule_server_group_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (86,'app\\admin\\controller\\ServerGroupController::delete','auth_rule_server_group_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (87,'app\\admin\\controller\\ServerController::serverList','auth_rule_server_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (88,'app\\admin\\controller\\ServerController::create','auth_rule_server_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (89,'app\\admin\\controller\\ServerController::update','auth_rule_server_update','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (90,'app\\admin\\controller\\ServerController::delete','auth_rule_server_delete','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (91,'app\\admin\\controller\\ServerController::status','auth_rule_server_status','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (92,'app\\admin\\controller\\ModuleController::moduleList','auth_rule_module_list','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (93,'app\\admin\\controller\\HostController::adminArea','auth_rule_host_module','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (94,'app\\admin\\controller\\HostController::changeConfigOption','auth_rule_host_upgrade_config_option','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (95,'app\\admin\\controller\\HostController::changeConfigOptionCalculatePrice','auth_rule_host_upgrade_config_option_price','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (96,'app\\admin\\controller\\HostController::createAccount','auth_rule_host_module_create','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (97,'app\\admin\\controller\\HostController::suspendAccount','auth_rule_host_module_suspend','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (98,'app\\admin\\controller\\HostController::unsuspendAccount','auth_rule_host_module_unsuspend','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (99,'app\\admin\\controller\\HostController::terminateAccount','auth_rule_host_module_terminate','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (100,'app\\admin\\controller\\ProductController::moduleServerConfigOption','auth_rule_product_server_config_option','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (101,'app\\admin\\controller\\ProductController::moduleAdminConfigOption','auth_rule_product_config_option','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (102,'app\\admin\\controller\\ProductController::moduleCalculatePrice','auth_rule_product_config_option_price','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (103,'app\\admin\\controller\\ClientController::status','auth_rule_client_status','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (104,'app\\admin\\controller\\OrderController::getUpgradeAmount','auth_rule_order_upgrade_amount','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (105,'app\\admin\\controller\\MenuController::getAdminMenu','auth_rule_get_admin_menu','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (106,'app\\admin\\controller\\MenuController::getHomeMenu','auth_rule_get_home_menu','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (107,'app\\admin\\controller\\MenuController::saveAdminMenu','auth_rule_save_admin_menu','','');
insert  into `idcsmart_auth_rule`(`id`,`name`,`title`,`module`,`plugin`) values (108,'app\\admin\\controller\\MenuController::saveHomeMenu','auth_rule_save_home_menu','','');

/*Table structure for table `idcsmart_auth_rule_link` */

DROP TABLE IF EXISTS `idcsmart_auth_rule_link`;

CREATE TABLE `idcsmart_auth_rule_link` (
  `auth_rule_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限规则ID',
  `auth_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  KEY `auth_rule_id` (`auth_rule_id`),
  KEY `auth_id` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限规则对应表';

/*Data for the table `idcsmart_auth_rule_link` */

insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (12,3);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (14,4);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (13,6);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (15,7);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (103,7);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (16,8);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (18,10);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (19,11);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (63,12);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (64,12);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (65,12);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (28,15);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (29,15);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (30,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (17,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (82,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (66,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (101,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (102,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (104,16);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (33,17);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (32,18);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (31,19);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (37,21);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (40,22);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (38,23);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (39,23);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (93,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (94,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (95,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (96,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (97,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (98,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (99,24);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (34,26);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (35,27);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (17,27);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (36,28);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (66,31);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (67,31);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (68,32);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (76,32);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (77,32);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (71,33);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (69,34);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (70,34);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (72,34);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (83,34);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (87,34);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (100,34);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (73,36);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (76,36);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (75,37);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (74,38);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (81,38);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (87,40);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (88,41);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (92,41);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (90,42);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (89,43);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (91,43);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (92,43);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (83,45);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (84,46);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (87,46);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (86,47);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (85,48);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (87,48);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (20,51);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (21,51);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (22,52);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (23,52);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (1,54);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (3,55);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (7,55);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (5,56);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (2,57);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (4,57);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (6,57);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (7,57);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (7,59);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (9,60);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (11,61);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (8,62);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (10,62);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (24,63);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (25,63);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (26,64);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (27,64);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (41,66);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (43,67);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (42,68);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (44,68);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (45,68);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (46,68);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (41,71);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (43,72);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (42,73);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (44,73);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (45,73);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (46,73);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (53,74);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (54,74);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (55,74);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (56,74);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (57,74);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (58,74);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (41,76);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (43,77);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (42,78);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (44,78);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (45,78);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (46,78);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (47,79);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (48,79);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (49,79);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (50,79);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (51,79);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (52,79);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (59,80);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (60,80);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (61,83);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (62,84);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (63,86);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (64,87);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (65,87);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (79,89);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (80,90);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (41,93);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (43,94);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (42,95);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (44,95);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (45,95);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (46,95);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (105,97);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (106,97);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (107,98);
insert  into `idcsmart_auth_rule_link`(`auth_rule_id`,`auth_id`) values (108,98);

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
  `client_notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '用户备注',
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

/*Data for the table `idcsmart_configuration` */

insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('lang_admin','zh-cn',0,0,'后台默认语言');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('lang_home','zh-cn',0,0,'前台默认语言');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('lang_home_open','0',0,0,'前台多语言开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('maintenance_mode','0',0,0,'维护模式开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('maintenance_mode_message','维护中...',0,0,'维护模式内容');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('website_name','智简魔方',0,0,'网站名称');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('website_url','http://kfc.idcsmart.com',0,0,'网站域名地址');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('terms_service_url','https://www.baidu.com',0,0,'服务条款地址');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('login_phone_verify','1',0,0,'手机号登录短信验证开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_client_register','1',0,0,'客户注册图形验证码开关  1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_client_login','0',0,0,'客户登录图形验证码开关  1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_admin_login','0',0,0,'管理员登录图形验证码开关  1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_client_login_error','1',0,0,'客户登录失败图形验证码开关  1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_width','200',0,0,'图形验证码宽度');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_height','50',0,0,'图形验证码高度');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('captcha_length','4',0,0,'图形验证码字符长度');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('register_email','1',0,0,'邮箱注册开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('register_phone','1',0,0,'手机号注册开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('currency_code','CNY',0,0,'货币代码');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('currency_prefix','￥',0,0,'货币符号');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('recharge_open','1',0,0,'启用充值');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('recharge_min','0.01',0,0,'单笔最小金额');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('task','0',0,0,'任务队列锁');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('system_version','1.0.0',0,0,'系统版本');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('send_sms','Idcsmart',0,0,'默认短信发送国内接口');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('send_sms_global','Idcsmart',0,0,'默认短信发送国际接口');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('send_email','Smtp',0,0,'默认邮件发送接口');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_lock','0',0,0,'定时任务锁');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_lock_last_time','1661490782',0,0,'定时任务最后执行时间');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_lock_day_last_time','1661485382',0,0,'每天执行一次定时任务最后执行时间');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('task_time','1661490817',0,0,'队列执行时长，然后程序结束');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_lock_five_minute_last_time','1661490782',0,0,'每五分钟执行一次定时任务最后执行时间');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_lock_start_time','1661490842',0,0,'定时任务开始执行时间');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_suspend_swhitch','1',0,0,'产品到期暂停开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_suspend_day','2',0,0,'产品到期暂停X天后暂停');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_unsuspend_swhitch','1',0,0,'财务原因产品暂停后付款自动解封开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_terminate_swhitch','1',0,0,'产品到期删除开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_terminate_day','7',0,0,'产品到期X天后删除');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_renewal_first_swhitch','1',0,0,'续费第一次提醒开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_renewal_first_day','7',0,0,'续费X天后到期第一次提醒');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_renewal_second_swhitch','1',0,0,'续费第二次提醒开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_due_renewal_second_day','3',0,0,'续费X天后到期第二次提醒');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_overdue_first_swhitch','1',0,0,'产品逾期第一次提醒开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_overdue_first_day','3',0,0,'产品逾期X天后第一次提醒');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_overdue_second_swhitch','1',0,0,'产品逾期第二次提醒开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_overdue_second_day','4',0,0,'产品逾期X天后第二次提醒');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_overdue_third_swhitch','1',0,0,'产品逾期第三次提醒开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_overdue_third_day','6',0,0,'产品逾期X天后第三次提醒');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_ticket_close_swhitch','1',0,0,'自动关闭工单开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_ticket_close_day','3',0,0,'已回复状态的工单超过x小时后关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_aff_swhitch','1',0,0,'推介月报开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('currency_suffix','元',0,0,'货币后缀');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('code_client_email_register','1',0,0,'邮箱注册数字验证码开关:1开启0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_order_overdue_day','1',0,0,'订单未付款通知');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_order_overdue_swhitch','1',0,0,'订单未付款通知开关 1开启，0关闭');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('admin_theme','default',0,0,'后台主题');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('clientarea_theme','default',0,0,'会员中心主题');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('recharge_max','100',0,0,'最大充值金额');

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
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

/*Data for the table `idcsmart_country` */

insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (1,'AF','AFG','AFGHANISTAN','阿富汗','Afghanistan',4,93,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (2,'AL','ALB','ALBANIA','阿尔巴尼亚','Albania',8,355,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (3,'DZ','DZA','ALGERIA','阿尔及利亚','Algeria',12,213,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (4,'AS','ASM','AMERICAN SAMOA','美属萨摩亚','American Samoa',16,1684,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (5,'AD','AND','ANDORRA','安道尔','Andorra',20,376,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (6,'AO','AGO','ANGOLA','安哥拉','Angola',24,244,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (7,'AI','AIA','ANGUILLA','安圭拉岛','Anguilla',660,1264,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (8,'AQ','','ANTARCTICA','南极洲','Antarctica',0,0,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (9,'AG','ATG','ANTIGUA AND BARBUDA','安提瓜岛和巴布达','Antigua and Barbuda',28,1268,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (10,'AR','ARG','ARGENTINA','阿根廷','Argentina',32,54,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (11,'AM','ARM','ARMENIA','亚美尼亚','Armenia',51,374,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (12,'AW','ABW','ARUBA','阿鲁巴岛','Aruba',533,297,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (13,'AU','AUS','AUSTRALIA','澳大利亚','Australia',36,61,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (14,'AT','AUT','AUSTRIA','奥地利','Austria',40,43,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (15,'AZ','AZE','AZERBAIJAN','阿塞拜疆','Azerbaijan',31,994,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (16,'BS','BHS','BAHAMAS','巴哈马群岛','Bahamas',44,1242,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (17,'BH','BHR','BAHRAIN','巴林','Bahrain',48,973,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (18,'BD','BGD','BANGLADESH','孟加拉国','Bangladesh',50,880,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (19,'BB','BRB','BARBADOS','巴巴多斯','Barbados',52,1246,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (20,'BY','BLR','BELARUS','白俄罗斯','Belarus',112,375,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (21,'BE','BEL','BELGIUM','比利时','Belgium',56,32,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (22,'BZ','BLZ','BELIZE','伯利兹','Belize',84,501,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (23,'BJ','BEN','BENIN','贝宁','Benin',204,229,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (24,'BM','BMU','BERMUDA','百慕大','Bermuda',60,1441,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (25,'BT','BTN','BHUTAN','不丹','Bhutan',64,975,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (26,'BO','BOL','BOLIVIA','玻利维亚','Bolivia',68,591,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (27,'BA','BIH','BOSNIA AND HERZEGOVINA','波斯尼亚和黑塞哥维那','Bosnia and Herzegovina',70,387,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (28,'BW','BWA','BOTSWANA','博茨瓦纳','Botswana',72,267,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (29,'BV','','BOUVET ISLAND','布维岛','Bouvet Island',0,0,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (30,'BR','BRA','BRAZIL','巴西','Brazil',76,55,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (31,'IO','','BRITISH INDIAN OCEAN TERRITORY','英属印度洋领地','British Indian Ocean Territory',0,246,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (32,'BN','BRN','BRUNEI DARUSSALAM','文莱达鲁萨兰国','Brunei Darussalam',96,673,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (33,'BG','BGR','BULGARIA','保加利亚','Bulgaria',100,359,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (34,'BF','BFA','BURKINA FASO','布吉纳法索','Burkina Faso',854,226,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (35,'BI','BDI','BURUNDI','布隆迪','Burundi',108,257,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (36,'KH','KHM','CAMBODIA','柬埔寨','Cambodia',116,855,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (37,'CM','CMR','CAMEROON','喀麦隆','Cameroon',120,237,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (38,'CA','CAN','CANADA','加拿大','Canada',124,1,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (39,'CV','CPV','CAPE VERDE','佛得角','Cape Verde',132,238,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (40,'KY','CYM','CAYMAN ISLANDS','开曼群岛','Cayman Islands',136,1345,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (41,'CF','CAF','CENTRAL AFRICAN REPUBLIC','中非共和国','Central African Republic',140,236,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (42,'TD','TCD','CHAD','乍得','Chad',148,235,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (43,'CL','CHL','CHILE','智利','Chile',152,56,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (44,'CN','CHN','CHINA','中国','China',156,86,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (45,'CX','','CHRISTMAS ISLAND','圣诞岛','Christmas Island',0,61,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (46,'CC','','COCOS (KEELING) ISLANDS','COCOS(KEELING)岛','Cocos (Keeling) Islands',0,672,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (47,'CO','COL','COLOMBIA','哥伦比亚','Colombia',170,57,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (48,'KM','COM','COMOROS','科摩罗','Comoros',174,269,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (49,'CG','COG','CONGO','刚果','Congo',178,242,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (50,'CD','COD','CONGO, THE DEMOCRATIC REPUBLIC OF THE','刚果民主共和国的','Congo, the Democratic Republic of the',180,242,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (51,'CK','COK','COOK ISLANDS','库克群岛','Cook Islands',184,682,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (52,'CR','CRI','COSTA RICA','哥斯达黎加','Costa Rica',188,506,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (53,'CI','CIV','COTE D\'IVOIRE','科特迪瓦','Cote D\'Ivoire',384,225,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (54,'HR','HRV','CROATIA','克罗地亚','Croatia',191,385,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (55,'CU','CUB','CUBA','古巴','Cuba',192,53,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (56,'CY','CYP','CYPRUS','塞浦路斯','Cyprus',196,357,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (57,'CZ','CZE','CZECH REPUBLIC','捷克共和国','Czech Republic',203,420,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (58,'DK','DNK','DENMARK','丹麦','Denmark',208,45,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (59,'DJ','DJI','DJIBOUTI','吉布提','Djibouti',262,253,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (60,'DM','DMA','DOMINICA','多米尼加','Dominica',212,1767,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (61,'DO','DOM','DOMINICAN REPUBLIC','多米尼加共和国','Dominican Republic',214,1809,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (62,'EC','ECU','ECUADOR','厄瓜多尔','Ecuador',218,593,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (63,'EG','EGY','EGYPT','埃及','Egypt',818,20,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (64,'SV','SLV','EL SALVADOR','萨尔瓦多','El Salvador',222,503,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (65,'GQ','GNQ','EQUATORIAL GUINEA','赤道几内亚','Equatorial Guinea',226,240,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (66,'ER','ERI','ERITREA','厄立特里亚','Eritrea',232,291,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (67,'EE','EST','ESTONIA','爱沙尼亚','Estonia',233,372,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (68,'ET','ETH','ETHIOPIA','埃塞俄比亚','Ethiopia',231,251,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (69,'FK','FLK','FALKLAND ISLANDS (MALVINAS)','福克兰群岛(马尔维纳斯)','Falkland Islands (Malvinas)',238,500,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (70,'FO','FRO','FAROE ISLANDS','法罗群岛','Faroe Islands',234,298,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (71,'FJ','FJI','FIJI','斐济','Fiji',242,679,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (72,'FI','FIN','FINLAND','芬兰','Finland',246,358,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (73,'FR','FRA','FRANCE','法国','France',250,33,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (74,'GF','GUF','FRENCH GUIANA','法属圭亚那','French Guiana',254,594,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (75,'PF','PYF','FRENCH POLYNESIA','法属波利尼西亚','French Polynesia',258,689,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (76,'TF','','FRENCH SOUTHERN TERRITORIES','法国南部地区','French Southern Territories',0,0,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (77,'GA','GAB','GABON','加蓬','Gabon',266,241,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (78,'GM','GMB','GAMBIA','冈比亚','Gambia',270,220,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (79,'GE','GEO','GEORGIA','乔治亚州','Georgia',268,995,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (80,'DE','DEU','GERMANY','德国','Germany',276,49,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (81,'GH','GHA','GHANA','加纳','Ghana',288,233,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (82,'GI','GIB','GIBRALTAR','直布罗陀','Gibraltar',292,350,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (83,'GR','GRC','GREECE','希腊','Greece',300,30,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (84,'GL','GRL','GREENLAND','格陵兰岛','Greenland',304,299,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (85,'GD','GRD','GRENADA','格林纳达','Grenada',308,1473,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (86,'GP','GLP','GUADELOUPE','瓜德罗普岛','Guadeloupe',312,590,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (87,'GU','GUM','GUAM','关岛','Guam',316,1671,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (88,'GT','GTM','GUATEMALA','危地马拉','Guatemala',320,502,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (89,'GN','GIN','GUINEA','几内亚','Guinea',324,224,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (90,'GW','GNB','GUINEA-BISSAU','几内亚比绍','Guinea-Bissau',624,245,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (91,'GY','GUY','GUYANA','圭亚那','Guyana',328,592,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (92,'HT','HTI','HAITI','海地','Haiti',332,509,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (93,'HM','','HEARD ISLAND AND MCDONALD ISLANDS','听到岛和麦当劳的岛屿','Heard Island and Mcdonald Islands',0,0,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (94,'VA','VAT','HOLY SEE (VATICAN CITY STATE)','教廷(梵蒂冈)','Holy See (Vatican City State)',336,39,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (95,'HN','HND','HONDURAS','洪都拉斯','Honduras',340,504,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (96,'HK','HKG','HONG KONG','中国香港','Hong Kong',344,852,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (97,'HU','HUN','HUNGARY','匈牙利','Hungary',348,36,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (98,'IS','ISL','ICELAND','冰岛','Iceland',352,354,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (99,'IN','IND','INDIA','印度','India',356,91,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (100,'ID','IDN','INDONESIA','印尼','Indonesia',360,62,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (101,'IR','IRN','IRAN, ISLAMIC REPUBLIC OF','伊朗伊斯兰共和国','Iran, Islamic Republic of',364,98,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (102,'IQ','IRQ','IRAQ','伊拉克','Iraq',368,964,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (103,'IE','IRL','IRELAND','爱尔兰','Ireland',372,353,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (104,'IL','ISR','ISRAEL','以色列','Israel',376,972,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (105,'IT','ITA','ITALY','意大利','Italy',380,39,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (106,'JM','JAM','JAMAICA','牙买加','Jamaica',388,1876,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (107,'JP','JPN','JAPAN','日本','Japan',392,81,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (108,'JO','JOR','JORDAN','约旦','Jordan',400,962,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (109,'KZ','KAZ','KAZAKHSTAN','哈萨克斯坦','Kazakhstan',398,7,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (110,'KE','KEN','KENYA','肯尼亚','Kenya',404,254,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (111,'KI','KIR','KIRIBATI','基里巴斯','Kiribati',296,686,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (112,'KP','PRK','KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF','朝鲜民主主义人民共和国','Korea, Democratic People\'s Republic of',408,850,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (113,'KR','KOR','KOREA, REPUBLIC OF','朝鲜共和国','Korea, Republic of',410,82,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (114,'KW','KWT','KUWAIT','科威特','Kuwait',414,965,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (115,'KG','KGZ','KYRGYZSTAN','吉尔吉斯斯坦','Kyrgyzstan',417,996,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (116,'LA','LAO','LAO PEOPLE\'S DEMOCRATIC REPUBLIC','老挝人民民主共和国','Lao People\'s Democratic Republic',418,856,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (117,'LV','LVA','LATVIA','拉脱维亚','Latvia',428,371,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (118,'LB','LBN','LEBANON','黎巴嫩','Lebanon',422,961,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (119,'LS','LSO','LESOTHO','莱索托','Lesotho',426,266,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (120,'LR','LBR','LIBERIA','利比里亚','Liberia',430,231,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (121,'LY','LBY','LIBYAN ARAB JAMAHIRIYA','阿拉伯利比亚民众国','Libyan Arab Jamahiriya',434,218,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (122,'LI','LIE','LIECHTENSTEIN','列支敦斯登','Liechtenstein',438,423,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (123,'LT','LTU','LITHUANIA','立陶宛','Lithuania',440,370,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (124,'LU','LUX','LUXEMBOURG','卢森堡','Luxembourg',442,352,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (125,'MO','MAC','MACAO','澳门','Macao',446,853,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (126,'MK','MKD','MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','前南斯拉夫马其顿共和国','Macedonia, the Former Yugoslav Republic of',807,389,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (127,'MG','MDG','MADAGASCAR','马达加斯加','Madagascar',450,261,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (128,'MW','MWI','MALAWI','马拉维','Malawi',454,265,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (129,'MY','MYS','MALAYSIA','马来西亚','Malaysia',458,60,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (130,'MV','MDV','MALDIVES','马尔代夫','Maldives',462,960,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (131,'ML','MLI','MALI','马里','Mali',466,223,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (132,'MT','MLT','MALTA','马耳他','Malta',470,356,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (133,'MH','MHL','MARSHALL ISLANDS','马绍尔群岛','Marshall Islands',584,692,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (134,'MQ','MTQ','MARTINIQUE','马提尼克岛','Martinique',474,596,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (135,'MR','MRT','MAURITANIA','毛利塔尼亚','Mauritania',478,222,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (136,'MU','MUS','MAURITIUS','毛里求斯','Mauritius',480,230,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (137,'YT','','MAYOTTE','马约特岛','Mayotte',0,269,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (138,'MX','MEX','MEXICO','墨西哥','Mexico',484,52,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (139,'FM','FSM','MICRONESIA, FEDERATED STATES OF','密克罗尼西亚联邦','Micronesia, Federated States of',583,691,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (140,'MD','MDA','MOLDOVA, REPUBLIC OF','摩尔多瓦共和国','Moldova, Republic of',498,373,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (141,'MC','MCO','MONACO','摩纳哥','Monaco',492,377,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (142,'MN','MNG','MONGOLIA','蒙古','Mongolia',496,976,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (143,'MS','MSR','MONTSERRAT','蒙特塞拉特','Montserrat',500,1664,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (144,'MA','MAR','MOROCCO','摩洛哥','Morocco',504,212,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (145,'MZ','MOZ','MOZAMBIQUE','MOZAMBIQUE','Mozambique',508,258,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (146,'MM','MMR','MYANMAR','缅甸','Myanmar',104,95,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (147,'NA','NAM','NAMIBIA','纳米比亚','Namibia',516,264,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (148,'NR','NRU','NAURU','瑙鲁','Nauru',520,674,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (149,'NP','NPL','NEPAL','尼泊尔','Nepal',524,977,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (150,'NL','NLD','NETHERLANDS','荷兰','Netherlands',528,31,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (151,'AN','ANT','NETHERLANDS ANTILLES','荷属安的列斯群岛','Netherlands Antilles',530,599,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (152,'NC','NCL','NEW CALEDONIA','新喀里多尼亚','New Caledonia',540,687,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (153,'NZ','NZL','NEW ZEALAND','新西兰','New Zealand',554,64,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (154,'NI','NIC','NICARAGUA','尼加拉瓜','Nicaragua',558,505,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (155,'NE','NER','NIGER','尼日尔','Niger',562,227,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (156,'NG','NGA','NIGERIA','尼日利亚','Nigeria',566,234,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (157,'NU','NIU','NIUE','纽埃岛','Niue',570,683,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (158,'NF','NFK','NORFOLK ISLAND','诺福克岛','Norfolk Island',574,672,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (159,'MP','MNP','NORTHERN MARIANA ISLANDS','北马里亚纳群岛','Northern Mariana Islands',580,1670,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (160,'NO','NOR','NORWAY','挪威','Norway',578,47,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (161,'OM','OMN','OMAN','阿曼','Oman',512,968,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (162,'PK','PAK','PAKISTAN','巴基斯坦','Pakistan',586,92,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (163,'PW','PLW','PALAU','帕劳','Palau',585,680,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (164,'PS','','PALESTINIAN TERRITORY, OCCUPIED','巴勒斯坦的领土,占领','Palestinian Territory, Occupied',0,970,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (165,'PA','PAN','PANAMA','巴拿马','Panama',591,507,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (166,'PG','PNG','PAPUA NEW GUINEA','巴布新几内亚','Papua New Guinea',598,675,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (167,'PY','PRY','PARAGUAY','巴拉圭','Paraguay',600,595,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (168,'PE','PER','PERU','秘鲁','Peru',604,51,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (169,'PH','PHL','PHILIPPINES','菲律宾','Philippines',608,63,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (170,'PN','PCN','PITCAIRN','皮特克恩','Pitcairn',612,0,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (171,'PL','POL','POLAND','波兰','Poland',616,48,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (172,'PT','PRT','PORTUGAL','葡萄牙','Portugal',620,351,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (173,'PR','PRI','PUERTO RICO','波多黎各','Puerto Rico',630,1787,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (174,'QA','QAT','QATAR','卡塔尔','Qatar',634,974,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (175,'RE','REU','REUNION','团聚','Reunion',638,262,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (176,'RO','ROM','ROMANIA','罗马尼亚','Romania',642,40,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (177,'RU','RUS','RUSSIAN FEDERATION','俄罗斯联邦','Russian Federation',643,70,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (178,'RW','RWA','RWANDA','卢旺达','Rwanda',646,250,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (179,'SH','SHN','SAINT HELENA','圣赫勒拿','Saint Helena',654,290,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (180,'KN','KNA','SAINT KITTS AND NEVIS','圣基茨和尼维斯','Saint Kitts and Nevis',659,1869,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (181,'LC','LCA','SAINT LUCIA','圣卢西亚岛','Saint Lucia',662,1758,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (182,'PM','SPM','SAINT PIERRE AND MIQUELON','圣皮埃尔和MIQUELON','Saint Pierre and Miquelon',666,508,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (183,'VC','VCT','SAINT VINCENT AND THE GRENADINES','圣文森特和格林纳丁斯','Saint Vincent and the Grenadines',670,1784,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (184,'WS','WSM','SAMOA','萨摩亚','Samoa',882,684,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (185,'SM','SMR','SAN MARINO','圣马力诺','San Marino',674,378,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (186,'ST','STP','SAO TOME AND PRINCIPE','圣多美和王子','Sao Tome and Principe',678,239,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (187,'SA','SAU','SAUDI ARABIA','沙特阿拉伯','Saudi Arabia',682,966,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (188,'SN','SEN','SENEGAL','塞内加尔','Senegal',686,221,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (189,'CS','','SERBIA AND MONTENEGRO','塞尔维亚和黑山','Serbia and Montenegro',0,381,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (190,'SC','SYC','SEYCHELLES','塞舌尔','Seychelles',690,248,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (191,'SL','SLE','SIERRA LEONE','塞拉利昂','Sierra Leone',694,232,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (192,'SG','SGP','SINGAPORE','新加坡','Singapore',702,65,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (193,'SK','SVK','SLOVAKIA','斯洛伐克','Slovakia',703,421,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (194,'SI','SVN','SLOVENIA','斯洛文尼亚','Slovenia',705,386,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (195,'SB','SLB','SOLOMON ISLANDS','所罗门群岛','Solomon Islands',90,677,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (196,'SO','SOM','SOMALIA','索马里','Somalia',706,252,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (197,'ZA','ZAF','SOUTH AFRICA','南非','South Africa',710,27,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (198,'GS','','SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS','南乔治亚岛和南桑威奇群岛','South Georgia and the South Sandwich Islands',0,0,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (199,'ES','ESP','SPAIN','西班牙','Spain',724,34,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (200,'LK','LKA','SRI LANKA','斯里兰卡','Sri Lanka',144,94,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (201,'SD','SDN','SUDAN','苏丹','Sudan',736,249,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (202,'SR','SUR','SURINAME','苏里南','Suriname',740,597,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (203,'SJ','SJM','SVALBARD AND JAN MAYEN','斯瓦尔巴群岛和扬马延岛','Svalbard and Jan Mayen',744,47,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (204,'SZ','SWZ','SWAZILAND','斯威士兰','Swaziland',748,268,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (205,'SE','SWE','SWEDEN','瑞典','Sweden',752,46,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (206,'CH','CHE','SWITZERLAND','瑞士','Switzerland',756,41,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (207,'SY','SYR','SYRIAN ARAB REPUBLIC','阿拉伯叙利亚共和国','Syrian Arab Republic',760,963,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (208,'TW','TWN','TAIWAN, PROVINCE OF CHINA','中国台湾地区','Taiwan, Province of China',158,886,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (209,'TJ','TJK','TAJIKISTAN','塔吉克斯坦','Tajikistan',762,992,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (210,'TZ','TZA','TANZANIA, UNITED REPUBLIC OF','坦桑尼亚联合共和国','Tanzania, United Republic of',834,255,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (211,'TH','THA','THAILAND','泰国','Thailand',764,66,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (212,'TL','','TIMOR-LESTE','东帝汶','Timor-Leste',0,670,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (213,'TG','TGO','TOGO','多哥','Togo',768,228,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (214,'TK','TKL','TOKELAU','托克劳','Tokelau',772,690,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (215,'TO','TON','TONGA','汤加','Tonga',776,676,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (216,'TT','TTO','TRINIDAD AND TOBAGO','特立尼达和多巴哥','Trinidad and Tobago',780,1868,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (217,'TN','TUN','TUNISIA','突尼斯','Tunisia',788,216,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (218,'TR','TUR','TURKEY','土耳其','Turkey',792,90,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (219,'TM','TKM','TURKMENISTAN','土库曼斯坦','Turkmenistan',795,7370,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (220,'TC','TCA','TURKS AND CAICOS ISLANDS','特克斯和凯科斯群岛','Turks and Caicos Islands',796,1649,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (221,'TV','TUV','TUVALU','图瓦卢','Tuvalu',798,688,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (222,'UG','UGA','UGANDA','乌干达','Uganda',800,256,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (223,'UA','UKR','UKRAINE','乌克兰','Ukraine',804,380,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (224,'AE','ARE','UNITED ARAB EMIRATES','阿拉伯联合酋长国','United Arab Emirates',784,971,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (225,'GB','GBR','UNITED KINGDOM','联合王国','United Kingdom',826,44,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (226,'US','USA','UNITED STATES','美国','United States',840,1,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (227,'UM','','UNITED STATES MINOR OUTLYING ISLANDS','美国小离岛','United States Minor Outlying Islands',0,1,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (228,'UY','URY','URUGUAY','乌拉圭','Uruguay',858,598,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (229,'UZ','UZB','UZBEKISTAN','乌兹别克斯坦','Uzbekistan',860,998,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (230,'VU','VUT','VANUATU','瓦努阿图','Vanuatu',548,678,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (231,'VE','VEN','VENEZUELA','委内瑞拉','Venezuela',862,58,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (232,'VN','VNM','VIET NAM','越南','Viet Nam',704,84,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (233,'VG','VGB','VIRGIN ISLANDS, BRITISH','维尔京群岛,英国','Virgin Islands, British',92,1284,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (234,'VI','VIR','VIRGIN ISLANDS, U.S.','维尔京群岛,美国','Virgin Islands, U.s.',850,1340,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (235,'WF','WLF','WALLIS AND FUTUNA','瓦利斯群岛和富图纳群岛','Wallis and Futuna',876,681,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (236,'EH','ESH','WESTERN SAHARA','西撒哈拉','Western Sahara',732,212,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (237,'YE','YEM','YEMEN','也门','Yemen',887,967,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (238,'ZM','ZMB','ZAMBIA','赞比亚','Zambia',894,260,0);
insert  into `idcsmart_country`(`id`,`iso`,`iso3`,`name`,`name_zh`,`nicename`,`num_code`,`phone_code`,`order`) values (239,'ZW','ZWE','ZIMBABWE','津巴布韦','Zimbabwe',716,263,0);

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
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '模板名称',
  `subject` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
  `message` text NOT NULL COMMENT '模板内容',
  `attachment` text NOT NULL COMMENT '附件',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='邮件模板表';

/*Data for the table `idcsmart_email_template` */

insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (1,'验证码','[{system_website_name}]验证码邮件','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]收到新的验证码</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span><br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您正在申请验证码：</span><br /><span style=\"margin: 0; padding: 0; line-height: 32px;\">为了账号安全，请在指定位置输入下列验证码： <span style=\"margin: 0; padding: 0; color: #007bfc; font-size: 18px; font-weight: bold;\">{code}</span>。 验证码涉及个人账号隐私安全，切勿向他人透漏。</span><br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span>\r\n<div class=\"logo_top\">&nbsp;</div>\r\n</div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',1652863243,1652943008);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (2,'用户登录','[{system_website_name}]用户登录','<!DOCTYPE html>\n<html>\n<head>\n</head>\n<body>\n<p>&nbsp;</p>\n<div class=\"box\">\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\n<div class=\"card\">\n<h2 style=\"text-align: center;\">[{system_website_name}]用户登录</h2>\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\n<div class=\"card\">您的账号{account}成功登录系统，如不是本人操作请及时修改密码<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\n<ul class=\"banquan\">\n<li>{system_website_name}</li>\n</ul>\n</div>\n</body>\n</html>','',0,1660814504);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (3,'用户注册','[{system_website_name}]用户注册','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]用户注册</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">{account}，感谢您支持{system_website_name}<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (4,'客户更改邮箱','[{system_website_name}]客户更改邮箱','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]用户更改邮箱</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的邮箱被改为：{client_email}，请注意账户安全<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (5,'客户更改密码','[{system_website_name}]客户更改密码','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]用户更改密码</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的密码被改为：{client_password}，请注意账户安全<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (6,'订单创建','[{system_website_name}]订单创建','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]订单创建</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您已下单，订单：{order_id}（订单号），请及时支付<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (7,'产品开通中','[{system_website_name}]产品开通中','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]产品开通中</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称）正在开通，请耐心等待<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,1660301886);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (8,'开通成功','[{system_website_name}]开通成功','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]开通成功</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），已开通可使用<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (9,'产品暂停通知','[{system_website_name}]产品暂停通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]产品暂停通知</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），{product_suspend_reason}，已被停用，有疑问请联系客服<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (10,'产品解除暂停通知','[{system_website_name}]产品解除暂停通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]产品解除暂停通知</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），已解除暂停<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (11,'产品删除通知','[{system_website_name}]产品删除通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]产品删除通知</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），由于已经到期续费，已被清除用户数据，有疑问请联系客服<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (12,'产品升降级','[{system_website_name}]产品升降级','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]产品升降级</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您已成功升级产品{product_info}，感谢您的支持<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (13,'超级管理员添加后台管理员','[{system_website_name}]超级管理员添加后台管理员','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]超级管理员添加后台管理员</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您已被设置为后台管理员，登录账户为：{admin_name}，密码为：{admin_password}<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (14,'第一次续费提醒','[{system_website_name}]第一次续费提醒','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]第一次续费提醒</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），还有{renewal_first)天到期，请注意是否续费<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (15,'第二次续费提醒','[{system_website_name}]第二次续费提醒','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]第二次续费提醒</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），还有{renewal_second}天到期，请注意是否续费<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (16,'逾期付款第一次','[{system_website_name}]逾期付款第一次','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]逾期付款第一次</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），将会删除，请及时续费<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (17,'逾期付款第二次','[{system_website_name}]逾期付款第二次','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]逾期付款第二次</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），将会删除，请及时续费<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (18,'逾期付款第三次','[{system_website_name}]逾期付款第三次','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]逾期付款第三次</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的产品：{product_name}（产品名称），将会删除，请及时续费<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (19,'订单未付款通知','[{system_website_name}]订单未付款通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]订单未付款通知</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的订单：{order_id}（订单号）尚未支付，金额{order_amount}，请及时支付<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (20,'订单金额修改','[{system_website_name}]订单金额修改','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]订单金额修改</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的订单：{order_id}（订单号）金额修改为{order_amount}，请及时支付<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (21,'订单支付通知','[{system_website_name}]订单支付通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]订单支付通知</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">您的订单：{order_id}（订单号）支付成功，支付金额为：{order_amount}元<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);
insert  into `idcsmart_email_template`(`id`,`name`,`subject`,`message`,`attachment`,`create_time`,`update_time`) values (22,'充值成功通知','[{system_website_name}]充值成功通知','<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>Document</title> <style> li{list-style: none;} a{text-decoration: none;} body{margin: 0;} .box{ background-color: #EBEBEB; height: 100%; } .logo_top {padding: 20px 0;} .logo_top img{ display: block; width: auto; margin: 0 auto; } .card{ width: 650px; margin: 0 auto; background-color: white; font-size: 0.8rem; line-height: 22px; padding: 40px 50px; box-sizing: border-box; } .contimg{ text-align: center; } button{ background-color: #F75697; padding: 8px 16px; border-radius: 6px; outline: none; color: white; border: 0; } .lvst{ color: #57AC80; } .banquan{ display: flex; justify-content: center; flex-wrap: nowrap; color: #B7B8B9; font-size: 0.4rem; padding: 20px 0; margin: 0; padding-left: 0; } .banquan li span{ display: inline-block; padding: 0 8px; } @media (max-width: 650px){ .card{ padding: 5% 5%; } .logo_top img,.contimg img{width: 280px;} .box{height: auto;} .card{width: auto;} } @media (max-width: 280px){.logo_top img,.contimg img{width: 100%;}} </style> </head> <body>\r\n<div class=\"box\">\r\n<div class=\"logo_top\"><img src=\"{system_logo_url}\" alt=\"\" /></div>\r\n<div class=\"card\">\r\n<h2 style=\"text-align: center;\">[{system_website_name}]充值成功通知</h2>\r\n<br /><strong>尊敬的用户</strong> <br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 55px;\">您好！</span></div>\r\n<div class=\"card\">充值成功，本次充值金额为：{order_amount}元<br /><span style=\"margin: 0; padding: 0; display: inline-block; margin-top: 60px;\">如果本次请求并非由您发起，请务必告知我们, 由此给您带来的不便敬请谅解。</span><br />&nbsp; <span style=\"margin: 0; padding: 0; display: inline-block; width: 100%; text-align: right;\"> <strong>{system_website_name}</strong> </span><br /><span style=\"margin: 0; padding: 0; margin-top: 20px; display: inline-block; width: 100%; text-align: right;\">{send_time}</span></div>\r\n<ul class=\"banquan\">\r\n<li>{system_website_name}</li>\r\n</ul>\r\n</div>\r\n</body> </html>','',0,0);

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
  `suspend_type` varchar(50) NOT NULL DEFAULT '' COMMENT '暂停类型,overdue=到期暂停,overtraffic=超流暂停,certification_not_complete=实名未完成,other=其他',
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
  `client_notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '用户备注',
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
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型admin后台home前台',
  `menu_type` varchar(20) NOT NULL DEFAULT '' COMMENT '菜单类型system系统plugin插件custom自定义',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `language` text NOT NULL COMMENT '多语言',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `nav_id` int(11) NOT NULL DEFAULT '0' COMMENT '系统页面ID',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `nav_id` (`nav_id`),
  KEY `parent_id` (`parent_id`),
  KEY `menu_type` (`menu_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='导航菜单表';

/*Table structure for table `idcsmart_nav` */

DROP TABLE IF EXISTS `idcsmart_nav`;

CREATE TABLE `idcsmart_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '默认导航表',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型admin后台home前台',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '插件模块路径,如:gateway支付接口/sms短信接口/mail邮件接口/addon插件',
  `plugin` varchar(100) NOT NULL DEFAULT '' COMMENT '插件',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='默认导航表';

/*Data for the table `idcsmart_nav` */

insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (1,'admin','nav_user_management','','user',0,2,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (2,'admin','nav_user_list','client.html','',1,3,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (3,'admin','nav_business_management','','view-module',0,4,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (4,'admin','nav_order_management','order.html','',3,5,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (5,'admin','nav_host_management','host.html','',3,6,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (6,'admin','nav_transaction','transaction.html','',3,7,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (7,'admin','nav_product_management','','cart',0,8,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (8,'admin','nav_product_management','product.html','',7,9,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (9,'admin','nav_server_management','server.html','',7,10,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (10,'admin','nav_server_group','server_group.html','',7,11,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (11,'admin','nav_system_settings','','setting',0,12,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (12,'admin','nav_system_settings','configuration_system.html','',11,13,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (13,'admin','nav_admin_settings','admin.html','',11,14,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (14,'admin','nav_security_settings','configuration_security.html','',11,15,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (15,'admin','nav_currency_settings','configuration_currency.html','',11,16,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (16,'admin','nav_payment_gateway','gateway.html','',11,17,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (17,'admin','nav_notice','','folder-open',0,18,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (18,'admin','nav_sms_notice','notice_sms.html','',17,19,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (19,'admin','nav_email_notice','notice_email.html','',17,20,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (20,'admin','nav_send_settings','notice_send.html','',17,21,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (21,'admin','nav_management','','precise-monitor',0,22,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (22,'admin','nav_task','task.html','',21,23,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (23,'admin','nav_log','log_system.html','',21,24,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (24,'admin','nav_auto','cron.html','',21,25,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (25,'admin','nav_plugin','','control-platform',0,43,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (26,'admin','nav_plugin_list','plugin.html','',25,27,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (27,'home','nav_finance_info','finance.html','',0,2,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (28,'home','nav_account_info','account.html','',0,3,'','');

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;

/*Data for the table `idcsmart_notice_setting` */

insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (1,'code','','Idcsmart',22,'Idcsmart',1,1,'Smtp',1,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (2,'client_login_success','','Idcsmart',23,'Idcsmart',2,1,'Smtp',2,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (3,'client_register_success','','Idcsmart',24,'Idcsmart',3,1,'Smtp',3,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (4,'client_change_phone','','Idcsmart',25,'Idcsmart',4,1,'',0,0);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (5,'client_change_email','','',0,'',0,0,'Smtp',4,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (6,'client_change_password','','Idcsmart',26,'Idcsmart',5,1,'Smtp',5,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (7,'order_create','','Idcsmart',27,'Idcsmart',6,1,'Smtp',6,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (8,'host_pending','','Idcsmart',28,'Idcsmart',7,1,'Smtp',7,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (9,'host_active','','Idcsmart',29,'Idcsmart',8,1,'Smtp',8,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (10,'host_suspend','','Idcsmart',30,'Idcsmart',9,1,'Smtp',9,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (11,'host_unsuspend','','Idcsmart',31,'Idcsmart',10,1,'Smtp',10,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (12,'host_terminate','','Idcsmart',32,'Idcsmart',11,1,'Smtp',11,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (13,'host_upgrad','','Idcsmart',33,'Idcsmart',12,1,'Smtp',12,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (14,'admin_create_account','','',0,'',0,0,'Smtp',13,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (15,'host_renewal_first','','Idcsmart',34,'Idcsmart',13,1,'Smtp',14,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (16,'host_renewal_second','','Idcsmart',35,'Idcsmart',14,1,'Smtp',15,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (17,'host_overdue_first','','Idcsmart',36,'Idcsmart',15,1,'Smtp',16,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (18,'host_overdue_second','','Idcsmart',37,'Idcsmart',16,1,'Smtp',17,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (19,'host_overdue_third','','Idcsmart',38,'Idcsmart',17,1,'Smtp',18,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (20,'order_overdue','','Idcsmart',39,'Idcsmart',18,1,'Smtp',19,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (21,'admin_order_amount','','Idcsmart',40,'Idcsmart',19,1,'Smtp',20,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (22,'order_pay','','Idcsmart',41,'Idcsmart',20,1,'Smtp',21,1);
insert  into `idcsmart_notice_setting`(`id`,`name`,`name_lang`,`sms_global_name`,`sms_global_template`,`sms_name`,`sms_template`,`sms_enable`,`email_name`,`email_template`,`email_enable`) values (23,'order_recharge','','Idcsmart',42,'Idcsmart',21,1,'Smtp',22,1);

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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='短信模板';

/*Data for the table `idcsmart_sms_template` */

insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (1,'qyNaA1',0,'验证码','验证码@var(code),5分钟内有效！请勿泄漏于他人','',2,'Idcsmart','',1660877230,1660892753);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (2,'xvZuU2',0,'用户登录','您的账号@var(account)成功登录系统，如不是本人操作请及时修改密码','',2,'Idcsmart','',1660877230,1660892754);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (3,'KZreL2',0,'用户注册','@var(account)，感谢您支持@var(system_website_name)','',2,'Idcsmart','',1660877230,1660892754);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (4,'sM1ok1',0,'客户更改手机','您的手机号被改为：@var(client_phone)，请注意账户安全','',2,'Idcsmart','',1660877230,1660892754);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (5,'F4kXV3',0,'客户更改密码','您的密码被改为：@var(client_password)，请注意账户安全','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (6,'8JGKP3',0,'订单创建','您已下单，订单：@var(order_id)（订单号），请及时支付','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (7,'8cnVI',0,'产品开通中','您的产品：@var(product_name)（产品名称）正在开通，请耐心等待','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (8,'oEoca3',0,'开通成功','您的产品：@var(product_name)（产品名称），已开通可使用','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (9,'CvDQk2',0,'产品暂停通知','您的产品：@var(product_name)（产品名称），由于@var(product_suspend_reason)，已停用','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (10,'5Oy0y3',0,'产品解除暂停通知','您的产品：@var(product_name)（产品名称），已解除暂停','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (11,'MSEt63',0,'产品删除通知','您的产品：@var(product_name)（产品名称），由于到期未续费，已删除','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (12,'pDKUb',0,'产品升降级','您已成功升级产品@var(product_name)，感谢您的支持','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (13,'uQoAL3',0,'第一次续费提醒','您的产品：@var(product_name)（产品名称），还有@var(renewal_first)天到期，请及时续费','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (14,'TwiQv',0,'第二次续费提醒','您的产品：@var(product_name)（产品名称），还有@var(renewal_first)天到期，请及时续费','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (15,'ErfLL1',0,'逾期付款第一次提醒','您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (16,'ZPHjb3',0,'逾期付款第二次提醒','您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (17,'jYqYh',0,'逾期付款第三次提醒','您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (18,'1WcF24',0,'订单未付款通知','您的订单：@var(order_id)（订单号）尚未支付，金额@var(order_amount)，请及时支付','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (19,'ICvxQ2',0,'订单金额修改','您的订单：@var(order_id)（订单号）金额修改为@var(order_amount)，请及时支付','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (20,'MkFSm1',0,'订单支付通知','您的订单：@var(order_id)（订单号）支付成功，支付金额为：@var(order_amount)元','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (21,'JYQXQ4',0,'充值成功通知','充值成功，本次充值金额为：@var(order_amount)元','',2,'Idcsmart','',1660877230,1660892755);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (22,'E5YKY4',1,'验证码','验证码@var(code),5分钟内有效！请勿泄漏于他人','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (23,'IRlJK2',1,'用户登录','您的账号@var(account)成功登录系统，如不是本人操作请及时修改密码','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (24,'d8OG62',1,'用户注册','@var(account)，感谢您支持@var(system_website_name)','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (25,'56pxY',1,'客户更改手机','您的手机号被改为：@var(client_phone)，请注意账户安全','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (26,'xDbEj',1,'客户更改密码','您的密码被改为：@var(client_password)，请注意账户安全','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (27,'gnSV1',1,'订单创建','您已下单，订单：@var(order_id)（订单号），请及时支付','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (28,'ldX5B3',1,'产品开通中','您的产品：@var(product_name)（产品名称）正在开通，请耐心等待','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (29,'TIAmq1',1,'开通成功','您的产品：@var(product_name)（产品名称），已开通可使用','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (30,'FtRVr',1,'产品暂停通知','您的产品：@var(product_name)（产品名称），由于@var(product_suspend_reason)，已停用','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (31,'e5Ine4',1,'产品解除暂停通知','您的产品：@var(product_name)（产品名称），已解除暂停','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (32,'ccIUt2',1,'产品删除通知','您的产品：@var(product_name)（产品名称），由于到期未续费，已删除','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (33,'HEXFw2',1,'产品升降级','您已成功升级产品@var(product_name)，感谢您的支持','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (34,'iSSp3',1,'第一次续费提醒','您的产品：@var(product_name)（产品名称），还有@var(renewal_first)天到期，请及时续费','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (35,'bmSiH3',1,'第二次续费提醒','您的产品：@var(product_name)（产品名称），还有@var(renewal_first)天到期，请及时续费','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (36,'pdWw32',1,'逾期付款第一次提醒','您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (37,'ObO4U',1,'逾期付款第二次提醒','您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (38,'Q2ybf2',1,'逾期付款第三次提醒','您的产品：@var(product_name)（产品名称）已到期，将会删除，请及时续费','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (39,'QMDBn2',1,'订单未付款通知','您的订单：@var(order_id)（订单号）尚未支付，金额@var(order_amount)，请及时支付','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (40,'vpaLJ4',1,'订单金额修改','您的订单：@var(order_id)（订单号）金额修改为@var(order_amount)，请及时支付','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (41,'NSkZL2',1,'订单支付通知','您的订单：@var(order_id)（订单号）支付成功，支付金额为：@var(order_amount)元','',2,'Idcsmart','',1660877230,1660893584);
insert  into `idcsmart_sms_template`(`id`,`template_id`,`type`,`title`,`content`,`notes`,`status`,`sms_name`,`error`,`create_time`,`update_time`) values (42,'gQqtB1',1,'充值成功通知','充值成功，本次充值金额为：@var(order_amount)元','',2,'Idcsmart','',1660877230,1660893584);

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

/*Table structure for table `idcsmart_task_wait` */

DROP TABLE IF EXISTS `idcsmart_task_wait`;

CREATE TABLE `idcsmart_task_wait` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT '关联类型',
  `rel_id` int(11) NOT NULL DEFAULT '0',
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

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
