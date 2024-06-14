<?php 

require dirname(dirname(__DIR__ )) . '/config.php';
require dirname(dirname(__DIR__ )) .'/vendor/autoload.php';

define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ) . '/'); # 网站入口目录

set_time_limit(0);
ini_set('max_execution_time', 3600);

$App=new \think\App();
$App->debug(APP_DEBUG);
$http = $App->http;
$response = $http->run();

use think\facade\Db;

$sql = [];

$sql = [
	"CREATE TABLE `idcsmart_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(32) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '三方插件标识',
  `client_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `openid` varchar(128) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '三方登录标识',
  `oauth` text CHARACTER SET utf8mb4 NOT NULL COMMENT '三方信息',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='三方登录信息表';",
    "INSERT  INTO `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) VALUES ('check_certification_recharge',1,'IdcsmartCertification','addon',0);",
    "ALTER TABLE `idcsmart_host` ADD COLUMN `base_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买周期原价';",
    "ALTER TABLE `idcsmart_host` ADD COLUMN `ratio_renew` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否开启比例续费:0否默认，1是';",
    "ALTER TABLE `idcsmart_upgrade` ADD COLUMN `base_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '升降级后产品原价';",
];
$systemNav = Db::name('nav')->where('type', 'admin')->where('parent_id', 0)->where('name', 'nav_system_settings')->find();
if(!empty($systemNav)){
	$navId = Db::name('nav')->insertGetId([
		'type'		=> 'admin',
		'name'		=> 'nav_oauth',
		'url'		=> 'oauth.htm',
		'icon'		=> '',
		'parent_id'	=> $systemNav['id'],
	]);
	$parentId = Db::name('menu')->where('type', 'admin')->where('nav_id', $systemNav['id'])->value('id');
	$navId = (int)$navId;
	$parentId = (int)$parentId;
	$sql[] = "INSERT INTO `idcsmart_menu` (`type`, `menu_type`, `name`, `language`, `url`, `icon`, `nav_id`, `parent_id`, `module`, `product_id`, `order`, `create_time`) VALUES ('admin', 'system', '三方登录', '[]', 'oauth.htm', '', $navId, $parentId, '', '', 0, 0);";
}

// 是否有云的接口
$cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
if(!empty($cloudServer)){
	$sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `gpu_name` varchar(255) NOT NULL DEFAULT '';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `gpu_enable` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用GPU';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `gpu_num` int(11) NOT NULL DEFAULT '0' COMMENT 'GPU数量';";
}

// 是否有云的接口
$dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
if(!empty($dcimServer)){
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=隐藏,1=显示)';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `support_optional` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持选配(0=不支持,1=支持)';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `leave_memory` int(11) NOT NULL DEFAULT '0' COMMENT '剩余容量';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `max_memory_num` int(11) NOT NULL DEFAULT '0' COMMENT '可增加内存条数';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `max_disk_num` int(11) NOT NULL DEFAULT '0' COMMENT '可增加硬盘数量';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `gpu` varchar(255) NOT NULL DEFAULT '' COMMENT '显卡';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `max_gpu_num` int(11) NOT NULL DEFAULT '0' COMMENT '可增加显卡数量';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `optional_host_auto_create` tinyint(1) NOT NULL DEFAULT '0' COMMENT '选配机器是否自动开通(0=关闭,1=开启)';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_gpu_order` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显卡是否应用等级优惠订购(0=关闭,1=开启)';";
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_gpu_upgrade` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显卡是否应用等级优惠升降级(0=关闭,1=开启)';";
	$sql[] = "CREATE TABLE `idcsmart_module_mf_dcim_model_config_option_link` (
  `model_config_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'dcim型号配置id',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '可选配optionID',
  `option_rel_type` tinyint(7) unsigned NOT NULL DEFAULT '0' COMMENT 'option表的rel_type',
  KEY `idx_model_config_id` (`model_config_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='型号配置可选配置表';";
}

// 是否有directAdmin的接口
$directAdminServer = Db::name('server')->where('module', 'direct_admin')->find();
if (!empty($directAdminServer)){
    $sql[] = "ALTER TABLE `idcsmart_module_direct_admin_duration` ADD COLUMN `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期价格';";
}

// 是否有文件下载插件
$idcsmartFileDownloadPlugin = Db::name('plugin')->where('name', 'IdcsmartFileDownload')->find();
if (!empty($idcsmartFileDownloadPlugin)){
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `global_order` int(11) NOT NULL DEFAULT '0' COMMENT '公共排序';";
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';";
    $sql[] = "UPDATE `idcsmart_plugin` SET `version`='1.0.1' WHERE `name`='IdcsmartFileDownload';";
}

// 是否有续费插件IdcsmartRenew
$idcsmartRenewPlugin = Db::name('plugin')->where('name', 'IdcsmartRenew')->find();
if (!empty($idcsmartRenewPlugin)){
    $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_renew` ADD COLUMN `base_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费原价';";

    // 删除未支付续费订单
    $orders = Db::name("order")->where('type','renew')
        ->where('status','Unpaid')
        ->select();
    foreach ($orders as $order){
        // 删除订单
        Db::name("order")->where('id',$order['id'])->delete();
        // 删除续费日志
        $orderItems = Db::name("order_item")->where('type','renew')->where('order_id',$order['id'])->select();
        foreach ($orderItems as $orderItem){
            Db::name("addon_idcsmart_renew")->where('id',$orderItem['rel_id'])->delete();
        }
        // 删除订单子项
        Db::name("order_item")->where('order_id',$order['id'])->delete();
    }

}

foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

// 更改host数据
$hosts = Db::name("host")->where('id','>',0)->select();
foreach ($hosts as $host){
    $OrderItemModel = new \app\common\model\OrderItemModel();
    $basePrice = $OrderItemModel->where('host_id',$host['id'])
        ->where('type','host')
        ->where('rel_id',$host['id'])
        ->where('order_id',$host['order_id'])
        ->where('client_id',$host['client_id'])
        ->where('product_id',$host['product_id'])
        ->value('amount');
    // 若产品存在最新续费订单，取续费；若存在最新升降级订单，取续费金额
    $hostOrderItems = Db::name("order_item")->where('host_id',$host['id'])
        ->whereIn('type',['upgrade','renew'])
        ->order('id','desc')
        ->select()->toArray();
    foreach ($hostOrderItems as $hostOrderItem){
        $paidOrder = Db::name("order")->where('status','Paid')->where('id',$hostOrderItem['order_id'])->find();
        if (!empty($paidOrder)){ // 订单已支付
            if ($hostOrderItem['type']=='renew'){ // 且最新的已支付订单类型为续费
                $basePrice = $hostOrderItem['amount'];
                break;
            }
            if ($hostOrderItem['type']=='upgrade'){ // 或者最新的已支付订单类型为升降级
                $basePrice = ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment')?$host['renew_amount']:$host['first_payment_amount']; // 取续费金额
                break;
            }
        }
    }

    Db::name("host")->where('id',$host['id'])->update(['base_price'=>$basePrice??0]);
}

// 删除未支付升降级订单(原升降级订单没有原价，这里删除，客户重新下单即可)
$upgrades = Db::name("upgrade")->where('status','Unpaid')->select();
foreach ($upgrades as $upgrade){
    // 删除升降级订单
    Db::name("order")->where('status','Unpaid')
        ->where('type','upgrade')
        ->where('id',$upgrade['order_id'])
        ->delete();
    // 删除订单子项
    Db::name("order_item")->where('order_id',$upgrade['order_id'])->delete();
    // 删除升降级记录
    Db::name("upgrade")->where('id',$upgrade['id'])->delete();
}