CREATE TABLE `idcsmart_refund_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '退款记录ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `type` varchar(20) NOT NULL DEFAULT 'credit' COMMENT '类型credit余额transaction交易流水',
  `transaction_id` int(11) NOT NULL DEFAULT '0' COMMENT '交易流水ID',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `type` (`type`),
  KEY `client_id` (`client_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='退款记录表';
ALTER TABLE `idcsmart_order` ADD COLUMN `refund_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额';
ALTER TABLE `idcsmart_product` ADD COLUMN `cycle` varchar(50) NOT NULL DEFAULT '' COMMENT '显示周期,模块存进去';