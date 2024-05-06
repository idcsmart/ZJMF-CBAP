<?php
namespace addon\idcsmart_file_download;

use app\common\lib\Plugin;
use think\facade\Db;

require_once __DIR__ . '/common.php';
/*
 * 文件下载
 * @author theworld
 * @time 2022-06-08
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartFileDownload extends Plugin
{
    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartFileDownload', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '文件下载',
        'description' => '文件下载',
        'author'      => '智简魔方',  //开发者
        'version'     => '2.0.2',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_file`",
            "CREATE TABLE `idcsmart_addon_idcsmart_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `addon_idcsmart_file_folder_id` int(11) NOT NULL DEFAULT '0' COMMENT '文件夹ID',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '名称',
  `filename` varchar(200) NOT NULL DEFAULT '' COMMENT '文件名',
  `filetype` varchar(50) NOT NULL DEFAULT '' COMMENT '文件类型',
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小,单位B',
  `visible_range` varchar(50) NOT NULL DEFAULT 'all' COMMENT '可见范围,all:所有用户,host:有产品的用户,product有指定产品的用户',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:显示1:隐藏',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '上传人',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `global_order` int(11) NOT NULL DEFAULT '0' COMMENT '公共排序',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `addon_idcsmart_file_folder_id` (`addon_idcsmart_file_folder_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_file_folder`",
            "CREATE TABLE `idcsmart_addon_idcsmart_file_folder` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件夹ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '默认文件夹0否1是',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作人',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件夹表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_file_link`",
            "CREATE TABLE `idcsmart_addon_idcsmart_file_link` (
  `addon_idcsmart_file_id` int(11) NOT NULL DEFAULT '0' COMMENT '文件ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  KEY `addon_idcsmart_file_id` (`addon_idcsmart_file_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件关联商品表'"
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
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_file`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_file_folder`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_file_link`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    # 插件升级
    public function upgrade()
    {
        $name = $this->info['name'];
        $version = $this->info['version'];
        $PluginModel = new \app\admin\model\PluginModel();
        $plugin = $PluginModel->where('name', $name)->find();
        $sql = [];
        if(isset($plugin['version'])){
            if(version_compare('1.0.1', $plugin['version'], '>')){
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `global_order` int(11) NOT NULL DEFAULT '0' COMMENT '公共排序';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';";
            }
        }
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }
}