<?php
namespace addon\idcsmart_withdraw;

use app\common\lib\Plugin;
use think\facade\Db;
use addon\idcsmart_withdraw\model\IdcsmartWithdrawModel;
/*
 * 提现插件
 * @author theworld
 * @time 2022-06-08
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartWithdraw extends Plugin
{
    #public function idcsmartWithdrawidcsmartauthorize(){}

    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartWithdraw', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '提现插件',
        'description' => '提现插件',
        'author'      => 'idcsmart',  //开发者
        'version'     => '1.0',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_withdraw`",
            "CREATE TABLE `idcsmart_addon_idcsmart_withdraw` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '提现ID',
  `source` varchar(100) NOT NULL DEFAULT '' COMMENT '提现来源',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `method` varchar(100) NOT NULL DEFAULT 'bank' COMMENT '提现方式bank银行卡alipay',
  `card_number` varchar(100) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '姓名',
  `account` varchar(100) NOT NULL DEFAULT '' COMMENT '支付宝账号',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态0待审核1审核通过2审核驳回',
  `reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '驳回原因',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_withdraw_rule`",
            "CREATE TABLE `idcsmart_addon_idcsmart_withdraw_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '提现规则ID',
  `source` varchar(100) NOT NULL DEFAULT 'credit' COMMENT '提现来源',
  `method` varchar(100) NOT NULL DEFAULT '' COMMENT '提现方式bank银行卡alipay支付宝',
  `process` varchar(20) NOT NULL DEFAULT 'artificial' COMMENT '提现流程artificial人工auto自动',
  `min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小金额限制',
  `max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最大金额限制',
  `cycle` varchar(20) NOT NULL DEFAULT 'day' COMMENT '提现周期day每天week每周month每月',
  `cycle_limit` int(11) NOT NULL DEFAULT '0' COMMENT '提现周期次数限制,0不限',
  `withdraw_fee_type` varchar(20) NOT NULL DEFAULT 'fixed' COMMENT '手续费类型fixed固定percent百分比',
  `withdraw_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '固定手续费金额',
  `percent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费百分比',
  `percent_min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '百分比最低计算金额',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态0关闭1开启',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现规则表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_withdraw_source`",
            "CREATE TABLE `idcsmart_addon_idcsmart_withdraw_source` (
  `plugin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '提现来源插件标识名',
  `plugin_title` varchar(50) NOT NULL DEFAULT '' COMMENT '提现来源插件名称',
  KEY `plugin_name` (`plugin_name`),
  KEY `plugin_title` (`plugin_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现来源表'",
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
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_withdraw`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_withdraw_rule`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_withdraw_source`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    # 实现提现钩子
    public function clientWithdraw($param)
    {
        $IdcsmartWithdrawModel = new IdcsmartWithdrawModel();

        return $IdcsmartWithdrawModel->idcsmartWithdraw($param);
    }
}