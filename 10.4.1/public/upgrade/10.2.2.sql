CREATE TABLE `idcsmart_upstream_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '上游订单ID',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '供应商ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '利润',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `order_id` (`order_id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='上游订单表';
CREATE TABLE `idcsmart_upstream_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '上游商品ID',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '供应商ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `upstream_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游商品ID',
  `profit_percent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '利润百分比',
  `certification` tinyint(1) NOT NULL DEFAULT '0' COMMENT '本地实名购买0关闭1开启',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `res_module` varchar(255) NOT NULL DEFAULT '' COMMENT '当前resmodule',
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `product_id` (`product_id`),
  KEY `res_module` (`res_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='上游商品表';
CREATE TABLE `idcsmart_upstream_host` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '上游产品ID',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT '供应商ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `upstream_host_id` int(11) NOT NULL DEFAULT '0' COMMENT '上游产品ID',
  `upstream_info` varchar(255) NOT NULL DEFAULT '' COMMENT '上游信息',
  `upstream_configoption` text NOT NULL COMMENT '上游配置项',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='上游产品表';
CREATE TABLE `idcsmart_supplier` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '供应商ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '供应商名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '接口地址',
  `username` varchar(100) NOT NULL DEFAULT '' COMMENT '用户名',
  `token` varchar(200) NOT NULL DEFAULT '' COMMENT 'API密钥',
  `secret` text NOT NULL COMMENT 'API私钥',
  `contact` varchar(1000) NOT NULL DEFAULT '' COMMENT '联系方式',
  `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='供应商表';
ALTER TABLE `idcsmart_api` ADD COLUMN `public_key` text NOT NULL COMMENT '公钥', ADD COLUMN `private_key` text NOT NULL COMMENT '私钥' AFTER `ip`;
ALTER TABLE `idcsmart_host` ADD COLUMN `downstream_info` varchar(255) NOT NULL DEFAULT '' COMMENT '下游token等信息', ADD COLUMN `downstream_host_id` int(11) NOT NULL DEFAULT '0' COMMENT '下游产品ID';
ALTER TABLE `idcsmart_product` ADD COLUMN `agentable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可代理0否1是';
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (32,'admin','nav_upstream_management','','fork',0,32,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (33,'admin','nav_supplier','supplier_list.html','',32,33,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (34,'admin','nav_upstream_order','upstream_order.html','',32,34,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (35,'admin','nav_upstream_product','upstream_product.html','',32,35,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (36,'admin','nav_upstream_goods','upstream_goods.html','',32,36,'','');
insert  into `idcsmart_nav`(`id`,`type`,`name`,`url`,`icon`,`parent_id`,`order`,`module`,`plugin`) values (37,'admin','nav_template','template.html','',11,37,'','');
CREATE TABLE `idcsmart_feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '意见反馈ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `feedback_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '意见反馈类型ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `description` text NOT NULL COMMENT '描述',
  `contact` varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  `attachment` text NOT NULL COMMENT '附件',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `feedback_type_id` (`feedback_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='意见反馈';
CREATE TABLE `idcsmart_feedback_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '意见反馈类型ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `description` text NOT NULL COMMENT '描述',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='意见反馈类型';
CREATE TABLE `idcsmart_consult` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '方案咨询ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `matter` varchar(1000) NOT NULL DEFAULT '' COMMENT '事项',
  `contact` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='方案咨询';
CREATE TABLE `idcsmart_friendly_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '友情链接ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='友情链接';
CREATE TABLE `idcsmart_honor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '荣誉资质ID',
  `name` varchar(100) DEFAULT '' COMMENT '名称',
  `img` varchar(255) DEFAULT '' COMMENT '图片地址',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='荣誉资质';
CREATE TABLE `idcsmart_partner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '合作伙伴ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `description` text NOT NULL COMMENT '描述',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合作伙伴';
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('put_on_record','',0,0,'备案信息');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('enterprise_name','',0,0,'企业名称');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('enterprise_telephone','',0,0,'企业电话');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('enterprise_mailbox','',0,0,'企业邮箱');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('enterprise_qrcode','',0,0,'企业二维码');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('online_customer_service_link','',0,0,'在线客服链接');
