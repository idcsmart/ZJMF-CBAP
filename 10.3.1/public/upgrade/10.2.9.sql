insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('cloud_product_link','',0,0,'云产品跳转链接');
insert  into `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) values ('dcim_product_link','',0,0,'DCIM产品跳转链接');
ALTER TABLE `idcsmart_upstream_product` ADD COLUMN `sync` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否同步 商品的可升降级商品';
INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('client_discount_by_amount',1,'IdcsmartClientLevel','addon',0);
