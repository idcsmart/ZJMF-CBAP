<?php
namespace addon\idcsmart_ticket;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\common\lib\Plugin;
use app\common\model\SystemLogModel;
use think\facade\Db;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;

/*
 * 智简魔方工单插件
 * @author wyh
 * @time 2022-06-20
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartTicket extends Plugin
{
    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartTicket', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '用户工单', //插件名称
        'description' => '用户工单', //插件描述
        'author'      => '智简魔方',  //开发者
        'version'     => '2.1.0',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_num` varchar(128) NOT NULL DEFAULT '' COMMENT '工单号',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '工单号后四位数字num',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `ticket_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单类型ID',
  `content` text COMMENT '正文',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '工单状态ID',
  `attachment` text COMMENT '附件',
  `last_reply_time` int(11) NOT NULL DEFAULT '0' COMMENT '上次回复时间',
  `post_time` int(11) NOT NULL DEFAULT '0' COMMENT '工单提交时间,催单会更新此值,防止判断时间生成工单号出现冲突',
  `notes` text COMMENT '备注',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `last_reply_admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '最近一次回复的管理员ID',
  `post_admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '后台创建工单的管理员ID',
  PRIMARY KEY (`id`),
  KEY `tn` (`ticket_num`) USING BTREE,
  KEY `ci` (`client_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_host_link`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_host_link` (
  `ticket_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  KEY `ticket_id` (`ticket_id`) USING BTREE,
  KEY `host_id` (`host_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单产品关联表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_prereply`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_prereply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '预设回复',
  `content` text CHARACTER SET utf8 COMMENT '内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单回复';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_status`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '工单状态',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `color` varchar(255) NOT NULL DEFAULT '' COMMENT '颜色码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '完结状态:1完结,0未完结',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认状态:1是,0否',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;",
            "INSERT INTO `idcsmart_addon_idcsmart_ticket_status`(`id`,`name`,`color`,`status`,`default`,`create_time`,`update_time`) VALUES(1,'待接单','#779500',0,1,0,0);",
            "INSERT INTO `idcsmart_addon_idcsmart_ticket_status`(`id`,`name`,`color`,`status`,`default`,`create_time`,`update_time`) VALUES(2,'用户已回复','#ff6600',0,1,0,0);",
            "INSERT INTO `idcsmart_addon_idcsmart_ticket_status`(`id`,`name`,`color`,`status`,`default`,`create_time`,`update_time`) VALUES(3,'已回复','#000000',1,1,0,0);",
            "INSERT INTO `idcsmart_addon_idcsmart_ticket_status`(`id`,`name`,`color`,`status`,`default`,`create_time`,`update_time`) VALUES(4,'已关闭','#888888',1,1,0,0);",
            "INSERT INTO `idcsmart_addon_idcsmart_ticket_status`(`id`,`name`,`color`,`status`,`default`,`create_time`,`update_time`) VALUES(5,'处理中','#cc0000',0,1,0,0);",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_type`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '工单类型',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='工单类型';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_notes`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '工单备注',
  `ticket_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单ID',
  `content` text COMMENT '内容',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_type_admin_link`;",
            "CREATE TABLE `idcsmart_addon_idcsmart_ticket_type_admin_link` (
  `ticket_type_id` int(11) NOT NULL DEFAULT '0',
  `admin_id` int(11) NOT NULL DEFAULT '0',
  KEY `ticket_type_id` (`ticket_type_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工单类型管理员管理员关联表';",
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
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_prereply`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_reply`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_status`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_notes`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ticket_type_admin_link`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        $SystemLogModel = new SystemLogModel();
        $SystemLogModel ->where('type','addon_idcsmart_ticket')->delete();

        # 删除插入的邮件短信模板
        $templates = IdcsmartTicketLogic::getDefaultConfig('ticket_notice_template');
        foreach ($templates as $key=>$template){
            notice_action_delete($key);
        }

        return true;
    }

    public function afterAdminDelete($param){
        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $IdcsmartTicketModel->afterAdminDelete($param);
    }


}