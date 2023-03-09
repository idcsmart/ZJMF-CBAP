<?php
namespace addon\promo_code;

use addon\promo_code\model\PromoCodeModel;
use app\common\lib\Plugin;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use think\facade\Db;

/*
 * 优惠码插件(基础版)
 * @author theworld
 * @time 2022-10-19
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class PromoCode extends Plugin
{
    #public function promoCodeidcsmartauthorize(){}

    # 插件基本信息
    public $info = array(
        'name'        => 'PromoCode', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '优惠码插件(基础版)',
        'description' => '优惠码插件(基础版)',
        'author'      => '智简魔方',  //开发者
        'version'     => '1.0.0',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code`;",
            "CREATE TABLE `idcsmart_addon_promo_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '优惠码',
  `type` varchar(30) NOT NULL DEFAULT 'percent' COMMENT '类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态:0停用,1启用',
  `client_type` varchar(30) NOT NULL DEFAULT 'all' COMMENT '用户类型:all所有客户,new无产品用户,old用户必须存在激活中的产品',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '失效时间',
  `max_times` int(10) NOT NULL DEFAULT '0' COMMENT '最大使用次数',
  `used` int(10) NOT NULL DEFAULT '0' COMMENT '已使用次数',
  `single_user_once` tinyint(1) NOT NULL DEFAULT '0' COMMENT '单用户一次0关闭1开启',
  `upgrade` tinyint(1) NOT NULL DEFAULT '0' COMMENT '升降级0关闭1开启',
  `host_upgrade` tinyint(1) NOT NULL DEFAULT '0' COMMENT '升降级商品配置0关闭1开启',
  `renew` tinyint(1) NOT NULL DEFAULT '0' COMMENT '续费0关闭1开启',
  `loop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '循环优惠0关闭1开启',
  `cycle_limit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '周期限制0关闭1开启',
  `cycle` varchar(255) NOT NULL DEFAULT '' COMMENT '适用场景时长:多选,分隔monthly,quarterly,semiannually,annually,biennially,triennially',
  `notes` varchar (2000) NOT NULL DEFAULT '' COMMENT '备注',
  `delete_time` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间，软删除使用',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='优惠码表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code_product`;",
            "CREATE TABLE `idcsmart_addon_promo_code_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addon_promo_code_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠码ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  PRIMARY KEY (`id`),
  KEY `addon_promo_code_id` (`addon_promo_code_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code_product_need`;",
            "CREATE TABLE `idcsmart_addon_promo_code_product_need` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addon_promo_code_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠码ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  PRIMARY KEY (`id`),
  KEY `addon_promo_code_id` (`addon_promo_code_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code_log`;",
            "CREATE TABLE `idcsmart_addon_promo_code_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addon_promo_code_id` int(11) NOT NULL DEFAULT '0' COMMENT '优惠码ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `scene` varchar(20) NOT NULL DEFAULT '' COMMENT '优惠码应用场景',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠前金额',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `addon_promo_code_id` (`addon_promo_code_id`),
  KEY `host_id` (`host_id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
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
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code`;",
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code_product`;",
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code_product_need`;",
            "DROP TABLE IF EXISTS `idcsmart_addon_promo_code_log`;",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    # 实现优惠码输入框模板钩子
    public function templateClientPromoCode($param)
    {
        return "<a href=\"\" class=\"btn btn-primary h-100 custom-button text-white\" disabled'>输入优惠码</a>";
    }

    # 实现每日定时任务钩子
    public function dailyCron()
    {
        $PromoCodeModel = new PromoCodeModel();

        return $PromoCodeModel->dailyCron();
    }

    # 实现订单创建后钩子
    public function afterOrderCreate($param)
    {
        $PromoCodeModel = new PromoCodeModel();

        return $PromoCodeModel->afterOrderCreate($param);
    }

    # 实现退款后钩子(返回此产品优惠总金额)
    public function afterRefund($param)
    {
        $hostId = $param['host_id']??0;

        $OrderItemModel = new OrderItemModel();

        $renews = $OrderItemModel->alias('oi')
            ->field('oi.order_id,oi.amount')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('oi.host_id',$hostId)
            ->where('oi.type','addon_promo_code')
            ->where('o.status','Paid')
            ->select()
            ->toArray();

        $amount = 0;

        foreach ($renews as $renew){
            $amount = bcadd($amount,$renew['amount'],2);
        }

        return $amount;
    }

    public function applyPromoCode($param)
    {
        $hostId = $param['host_id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->find($hostId);
        if (!empty($host)){
            $OrderItemModel = new OrderItemModel();
            $promoCode = $OrderItemModel->alias('oi')
                ->leftJoin('addon_promo_code pc','pc.id=oi.rel_id')
                ->where('oi.order_id',$host->order_id)
                ->where('oi.host_id',$hostId)
                ->where('oi.type','addon_promo_code')
                ->value('code')??'';
            $postData = [
                'promo_code' => $promoCode,
                'scene' => $param['scene'],
                'host_id' => $hostId,
                'product_id' => $host->product_id,
                'amount' => $param['price'],
                'billing_cycle_time' => $param['duration']
            ];

            $PromoCodeModel = new PromoCodeModel();
            $result = $PromoCodeModel->apply($postData);
            return $result;
        }

        return ['status'=>400];
    }

}