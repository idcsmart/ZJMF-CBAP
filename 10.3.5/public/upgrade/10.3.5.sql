CREATE TABLE `idcsmart_product_duration_ratio` (
  `duration_id` int(11) NOT NULL DEFAULT '0' COMMENT '周期ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `ratio` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '比例',
  KEY `product_id` (`product_id`),
  KEY `duration_id` (`duration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('web_switch','1',0,0,'官网开关');