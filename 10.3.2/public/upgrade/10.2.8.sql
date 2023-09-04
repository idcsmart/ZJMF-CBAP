ALTER TABLE `idcsmart_order` CHANGE `type` `type` varchar(100) NOT NULL DEFAULT '' COMMENT '类型new新订单renew续费订单upgrade升降级订单artificial人工订单';
CREATE TABLE `idcsmart_admin_role_widget` (
  `admin_role_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员组ID',
  `widget` text NOT NULL COMMENT '挂件标识,逗号分隔',
  KEY `admin_role_id` (`admin_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员组挂件表';
CREATE TABLE `idcsmart_admin_widget` (
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `widget` text NOT NULL COMMENT '挂件标识,逗号分隔',
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员挂件表';
INSERT INTO `idcsmart_admin_role_widget` (`admin_role_id`,`widget`) SELECT `iar`.`id` `admin_role_id`,'ThisYearSale,ThisMonthSale,TodaySale,ActiveClient,ThisYearSaleDetail,ThisYearClient,OnlineAdmin,LastVisitClient' `widget` FROM `idcsmart_admin_role` `iar`;
DELETE FROM `idcsmart_auth` WHERE `id` IN (100,101,102,103,104,105);
DELETE FROM `idcsmart_auth_link` WHERE `auth_id` IN (100,101,102,103,104,105);
DELETE FROM `idcsmart_auth_rule_link` WHERE `auth_id` IN (100,101,102,103,104,105);
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_order_unpaid_delete_swhitch','',0,0,'订单自动删除开关');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cron_order_unpaid_delete_day','',0,0,'订单未付款X天后自动删除');
INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('renew_host_refund_amount',1,'IdcsmartRenew','addon',0);
