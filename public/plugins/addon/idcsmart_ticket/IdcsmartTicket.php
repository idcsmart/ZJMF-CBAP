<?php
namespace addon\idcsmart_ticket;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\common\lib\Plugin;
use think\facade\Db;

/*
 * 智简魔方工单插件
 * @author wyh
 * @time 2022-06-20
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartTicket extends Plugin
{
    #public function demoStyleidcsmartauthorize(){}

    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartTicket', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '智简魔方工单插件',
        'description' => '智简魔方工单插件',
        'author'      => 'idcsmart',  //开发者
        'version'     => '1.0',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_num` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '工单号',
  `num` INT(11) NOT NULL DEFAULT '0' COMMENT '工单号后四位数字num',
  `admin_role_id` INT(11) NOT NULL DEFAULT '0' COMMENT '部门id,这里就是管理员分组ID',
  `client_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标题',
  `ticket_type_id` INT(11) NOT NULL DEFAULT '0' COMMENT '工单类型ID',
  `content` TEXT COMMENT '正文',
  `status` VARCHAR(25) NOT NULL DEFAULT 'Pending' COMMENT '状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭',
  `attachment` TEXT COMMENT '附件',
  `last_reply_time` INT(11) NOT NULL DEFAULT '0' COMMENT '上次回复时间',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `post_time` int(11) NOT NULL DEFAULT '0' COMMENT '工单提交时间,催单会更新此值,防止判断时间生成工单号出现冲突',
  PRIMARY KEY (`id`),
  KEY `tn` (`ticket_num`) USING BTREE,
  KEY `ci` (`client_id`) USING BTREE,
  KEY `ari` (`admin_role_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_type`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_type` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '工单类型',
  `admin_role_id` INT(11) NOT NULL DEFAULT '0' COMMENT '默认接受部门ID',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单类型';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_host_link`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_host_link` (
  `ticket_id` INT(11) NOT NULL DEFAULT '0' COMMENT '工单ID',
  `host_id` INT(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
   KEY `ticket_id` (`ticket_id`) USING BTREE,
   KEY `host_id` (`host_id`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单产品关联表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_reply`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单id',
  `type` varchar(25) NOT NULL DEFAULT 'Client' COMMENT '类型:Client客户回复,Admin管理员回复',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型关联ID',
  `content` text COMMENT '回复信息',
  `attachment` text COMMENT '附件',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '回复时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单回复';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_internal`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_internal` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(11) NOT NULL DEFAULT '0' COMMENT '工单ID',
  `post_admin_id` INT(11) NOT NULL DEFAULT '0' COMMENT '提交工单的管理员ID',
  `ticket_num` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '工单号',
  `num` INT(11) NOT NULL DEFAULT '0' COMMENT '工单号后四位数字num',
  `admin_role_id` INT(11) NOT NULL DEFAULT '0' COMMENT '部门id,这里就是管理员分组ID',
  `admin_id` INT(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `client_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标题',
  `ticket_type_id` INT(11) NOT NULL DEFAULT '0' COMMENT '工单类型ID',
  `priority` VARCHAR(25) NOT NULL DEFAULT 'medium' COMMENT '优先级:medium一般,high紧急',
  `content` TEXT COMMENT '正文',
  `status` VARCHAR(25) NOT NULL DEFAULT 'Pending' COMMENT '状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭',
  `attachment` TEXT COMMENT '附件',
  `last_reply_time` INT(11) NOT NULL DEFAULT '0' COMMENT '上次回复时间',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `post_time` INT(11) NOT NULL DEFAULT '0' COMMENT '工单提交时间,催单会更新此值,防止判断时间生成工单号出现冲突',
  PRIMARY KEY (`id`),
  KEY `ti` (`ticket_id`) USING BTREE,
  KEY `tn` (`ticket_num`) USING BTREE,
  KEY `ci` (`client_id`) USING BTREE,
  KEY `ai` (`admin_id`) USING BTREE,
  KEY `ari` (`admin_role_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='内部工单表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_internal_host_link`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_internal_host_link` (
  `ticket_internal_id` INT(11) NOT NULL DEFAULT '0' COMMENT '内部工单ID',
  `host_id` INT(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
   KEY `tii` (`ticket_internal_id`) USING BTREE,
   KEY `host_id` (`host_id`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='内部工单产品关联表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_internal_reply`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_internal_reply` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ticket_internal_id` INT(11) NOT NULL DEFAULT '0' COMMENT '内部工单id',
  `admin_id` INT(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `content` TEXT COMMENT '回复信息',
  `attachment` TEXT COMMENT '附件',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '回复时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='内部工单回复';",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        # 插入邮件短信模板
        $templates = IdcsmartTicketLogic::getDefaultConfig('ticket_notice_template');
        foreach ($templates as $key=>$template){
            $template['name'] = $key;
            notice_action_create($template);
        }

        # 安装成功返回true，失败false
        return true;
    }
    # 插件卸载
    public function uninstall()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_type`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_host_link`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_reply`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_internal`;",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_internal_host_link`;",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_internal_reply`;",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        # 删除插入的邮件短信模板
        $templates = IdcsmartTicketLogic::getDefaultConfig('ticket_notice_template');
        foreach ($templates as $key=>$template){
            notice_action_delete($key);
        }

        return true;
    }

}