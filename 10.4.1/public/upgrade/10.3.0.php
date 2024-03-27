<?php

require dirname(dirname(__DIR__ )) . '/config.php';
require dirname(dirname(__DIR__ )) .'/vendor/autoload.php';

define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ) . '/'); # 网站入口目录

$App=new \think\App();
$App->debug(APP_DEBUG);
$http = $App->http;
$response = $http->run();

use think\facade\Db;

$sql = [
    "CREATE TABLE `idcsmart_module_idcsmart_common_server_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '服务器组ID',
  `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '组名称',
  `type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '服务器模块类型',
  `system_type` VARCHAR(255) NOT NULL DEFAULT 'normal' COMMENT '组类型',
  `mode` INT(1) NOT NULL DEFAULT '1' COMMENT '分配方式（1：平均分配  2 满一个算一个）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;",
    "CREATE TABLE `idcsmart_module_idcsmart_common_server` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '服务器配置ID',
  `gid` INT(11) NOT NULL DEFAULT '0' COMMENT '服务器组ID',
  `name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '名称',
  `ip_address` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `assigned_ips` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '其他IP地址',
  `hostname` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '主机名',
  `monthly_cost` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '每月成本',
  `noc` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '数据中心',
  `status_address` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '服务器状态地址',
  `name_server1` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '主域名服务器',
  `name_server1_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server2` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '次域名服务器',
  `name_server2_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server3` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '第三域名服务器',
  `name_server3_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server4` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '第四域名服务器',
  `name_server4_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server5` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '第五域名服务器',
  `name_server5_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `max_accounts` INT(11) NOT NULL DEFAULT '0' COMMENT '最大账号数量（默认为0）',
  `username` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '密码',
  `accesshash` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '访问散列值',
  `secure` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '安全，1:选中复选框使用 SSL 连接模式,0不选(默认)',
  `port` varchar(25) NOT NULL DEFAULT '' COMMENT '访问端口',
  `active` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1.当前模块类型激活的服务器(或默认服务器),0非默认',
  `disabled` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1勾选禁用，0使用(默认)(单选框)',
  `server_type` VARCHAR(255) NOT NULL DEFAULT 'normal' COMMENT '服务器类型',
  `link_status` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '服务器连接状态 0失败 1成功',
  `type` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '服务器模块类型',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `gid` (`gid`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;",
    "CREATE TABLE `idcsmart_module_idcsmart_common_server_host_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0',
  `server_id` int(11) NOT NULL DEFAULT '0',
  `dedicatedip` varchar(255) NOT NULL DEFAULT '' COMMENT 'ip',
  `assignedips` varchar(255) NOT NULL DEFAULT '' COMMENT '分配IP',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `bwlimit` int(11) NOT NULL DEFAULT '0' COMMENT '流量限制',
  `os` varchar(255) NOT NULL DEFAULT '' COMMENT '操作系统',
  `bwusage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vserverid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

try{

}catch(\think\db\exception\PDOException $e){

}



set_time_limit(0);
ini_set('max_execution_time', 3600);
