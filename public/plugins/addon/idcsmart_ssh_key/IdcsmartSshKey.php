<?php
namespace addon\idcsmart_ssh_key;

use app\common\lib\Plugin;
use think\facade\Db;

require_once __DIR__ . '/common.php';
/*
 * SSH密钥
 * @author theworld
 * @time 2022-07-07
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartSshKey extends Plugin
{
    #public function idcsmartSshKeyidcsmartauthorize(){}

    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartSshKey', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => 'SSH密钥',
        'description' => 'SSH密钥',
        'author'      => 'idcsmart',  //开发者
        'version'     => '1.0',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ssh_key`",
            "CREATE TABLE `idcsmart_addon_idcsmart_ssh_key` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'SSH密钥ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `public_key` text NOT NULL COMMENT '公钥',
  `finger_print` varchar(500) NOT NULL DEFAULT '' COMMENT '指纹',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SSH密钥表'",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        # 安装成功返回true，失败false
        return true;
    }
    # 插件卸载
    public function uninstall()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_ssh_key`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }
}