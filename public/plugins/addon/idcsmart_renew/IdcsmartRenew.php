<?php
namespace addon\idcsmart_renew;

use addon\idcsmart_renew\model\IdcsmartRenewModel;
use app\common\lib\Plugin;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use think\facade\Db;
use addon\idcsmart_renew\logic\IdcsmartRenewLogic;

/*
 * 智简魔方续费插件
 * @author wyh
 * @time 2022-06-02
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartRenew extends Plugin
{
    #public function demoStyleidcsmartauthorize(){}

    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartRenew', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '续费',
        'description' => '续费',
        'author'      => '智简魔方',  //开发者
        'version'     => '1.0',      // 版本号
    );

    # 定义此变量,表示不需要默认导航
    public $noNav;

    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew`",
            "CREATE TABLE `idcsmart_addon_idcsmart_renew` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `host_id` INT(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `new_billing_cycle` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '新周期',
  `new_billing_cycle_time` INT(11) NOT NULL DEFAULT '0' COMMENT '新周期时间',
  `new_billing_cycle_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '新周期续费金额',
  `status` ENUM('Completed','Pending') NOT NULL DEFAULT 'Pending' COMMENT '状态:Pending待执行,Completed已完成',
  `create_time` INT(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `client_id` (`client_id`),
  KEY `host_id` (`host_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT '续费表';",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew_auto`",
            "CREATE TABLE `idcsmart_addon_idcsmart_renew_auto` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动续费状态0关闭1开启',
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        # 插入邮件短信模板
        $templates = IdcsmartRenewLogic::getDefaultConfig('renew_notice_template');
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
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_renew_auto`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }

        # 删除插入的邮件短信模板
        $templates = IdcsmartRenewLogic::getDefaultConfig('renew_notice_template');
        foreach ($templates as $key=>$template){
            notice_action_delete($key);
        }
        return true;
    }

    # 实现订单支付后钩子
    public function orderPaid($param)
    {
        if (!isset($param['id'])){
            return false;
        }
        $id = $param['id'];
        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $OrderItemModel = new OrderItemModel();
        $orderItems = $OrderItemModel
            ->where('order_id',$id)
            ->where('type','renew')
            ->select();
        foreach ($orderItems as $orderItem){
            $IdcsmartRenewModel->renewHandle($orderItem->rel_id);
        }

        return true;
    }

    # 实现订单创建后钩子
    public function afterOrderCreate($param)
    {
        if (!isset($param['id'])){
            return false;
        }

        $OrderModel = new OrderModel();
        $order = $OrderModel->find($param['id']);
        if (empty($order)){
            return false;
        }

        # 当为升降级订单时,删除未支付续费订单
        if ($order['type'] == 'upgrade'){
            $renewOrderIds = $OrderModel->where('client_id',$order->client_id)
                ->where('type','renew')
                ->where('status','Unpaid')
                ->column('id');

            $OrderItemModel = new OrderItemModel();
            $OrderItemModel->whereIn('order_id',$renewOrderIds)->delete();

            $OrderModel->where('client_id',$order->client_id)
                ->where('type','renew')
                ->where('status','Unpaid')
                ->delete();
        }

        return true;
    }

    # 实现退款后钩子
    public function afterRefund($param)
    {
        $hostId = $param['host_id']??0;

        $OrderItemModel = new OrderItemModel();

        $renews = $OrderItemModel->alias('oi')
            ->field('oi.order_id,oi.amount')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('oi.host_id',$hostId)
            ->where('oi.type','renew')
            ->where('o.status','Paid')
            ->select()
            ->toArray();

        $amount = 0;

        $manualOrderIds = [];
        foreach ($renews as $renew){
            $manualOrderIds[] = $renew['order_id'];
            $amount = bcadd($amount,$renew['amount'],2);
        }
        # 生成订单,退手动更改的钱
        $manualAmount = $OrderItemModel->whereIn('order_id',$manualOrderIds)
            ->where('type','manual')
            ->sum('amount');

        $amount = bcadd($amount,$manualAmount,2);

        return $amount;
    }

    # 实现产品列表后按钮模板钩子
    public function templateClientAfterHostListButton($param)
    {
        if (!isset($param['id'])){
            return '';
        }
        $id = intval($param['id']);

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        return $IdcsmartRenewModel->templateClientAfterHostListButton($id);
    }

    # 实现产品列表table-header上 按钮 钩子
    public function templateClientHostListOnTableHeader()
    {
        $button = lang_plugins('renew_batch');

        $url = "console/v1/renew/batch";

        return "<a href=\"{$url}\" class=\"btn btn-primary h-100 custom-button text-white\" disabled'>{$button}</a>";
    }

    public function beforeHostRenewalFirst($param)
    {
        if (!isset($param['id'])){
            return false;
        }
        $id = intval($param['id']);

        $IdcsmartRenewModel = new IdcsmartRenewModel();

        $IdcsmartRenewModel->isAdmin = true;

        return $IdcsmartRenewModel->beforeHostRenewalFirst($id);
    }
}