CREATE TABLE `idcsmart_client_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户信息记录ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `content` text NOT NULL COMMENT '内容',
  `attachment` text NOT NULL COMMENT '附件',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户信息记录';
ALTER TABLE `idcsmart_supplier` ADD COLUMN `type` varchar(100) NOT NULL DEFAULT 'default' COMMENT '类型' AFTER `id`;
ALTER TABLE `idcsmart_order` ADD COLUMN `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID';
ALTER TABLE `idcsmart_order` ADD INDEX admin_id (`admin_id`);
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('icp_info','',0,0,'ICP信息');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('icp_info_link','',0,0,'ICP信息跳转链接');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('public_security_network_preparation','',0,0,'公安网备');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('public_security_network_preparation_link','',0,0,'公安网备跳转链接');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('telecom_appreciation','',0,0,'电信增值');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('copyright_info','',0,0,'版权信息');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('official_website_logo','',0,0,'官网LOGO');