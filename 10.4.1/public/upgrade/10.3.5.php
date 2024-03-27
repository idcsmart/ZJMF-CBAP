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
	"ALTER TABLE `idcsmart_module_mf_cloud_duration` ADD COLUMN `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期价格';",
	"ALTER TABLE `idcsmart_module_mf_dcim_duration` ADD COLUMN `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期价格';",
	"ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '推荐配置ID';",
	"ALTER TABLE `idcsmart_module_mf_cloud_price` ADD COLUMN `rel_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '关联表:0=option,1=recommend_config';",
	"ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `ip_num` int(11) NOT NULL DEFAULT '1' COMMENT 'IP数量';",
	"ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `upgrade_range` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0=不可升降级,1=全部,2=自定义';",
	"ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` ADD COLUMN `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `only_sale_recommend_config` tinyint(3) NOT NULL DEFAULT '0' COMMENT '仅售卖套餐';",
	"ALTER TABLE `idcsmart_module_mf_cloud_price` CHANGE COLUMN `option_id` `rel_id` int(11) NOT NULL DEFAULT 0 COMMENT '配置ID';",
	"ALTER TABLE `idcsmart_module_mf_cloud_recommend_config` DROP COLUMN `network_type`;",
	"ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD INDEX `recommend_config_id`(`recommend_config_id`);",
	"ALTER TABLE `idcsmart_module_mf_cloud_price` DROP INDEX `product_id`;",
	"ALTER TABLE `idcsmart_module_mf_cloud_price` DROP INDEX `option_id`;",
	"ALTER TABLE `idcsmart_module_mf_cloud_price` ADD INDEX `prr`(`product_id`, `rel_id`, `rel_type`);",
	"CREATE TABLE `idcsmart_module_mf_cloud_recommend_config_upgrade_range` (
  `recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `rel_recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '可升降级套餐ID',
  KEY `recommend_config_id` (`recommend_config_id`),
  KEY `rel_recommend_config_id` (`rel_recommend_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	"ALTER TABLE `idcsmart_addon_idcsmart_withdraw_rule` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态0关闭1开启';",
	"UPDATE `idcsmart_plugin` SET `version`='1.0.1' WHERE `name`='IdcsmartWithdraw';",
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);