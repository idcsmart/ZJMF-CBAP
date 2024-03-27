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
	"ALTER TABLE `idcsmart_module_mf_dcim_option` MODIFY COLUMN `value` varchar(255) NOT NULL DEFAULT '0' COMMENT '单选值';",
	"ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `link_clone` tinyint(3) NOT NULL DEFAULT '0' COMMENT '链接创建(0=否,1=是)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `niccard` tinyint(4) NOT NULL DEFAULT 0 COMMENT '网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `cpu_model` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `ipv6_num` varchar(10) NOT NULL DEFAULT '' COMMENT 'IPv6数量';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `nat_acl_limit` varchar(10) NOT NULL DEFAULT '' COMMENT 'NAT转发限制';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `nat_web_limit` varchar(10) NOT NULL DEFAULT '' COMMENT 'NAT建站限制';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `memory_unit` varchar(10) NOT NULL DEFAULT 'GB' COMMENT '内存单位(GB,MB)';",
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

try{
    Db::execute("SELECT `id` FROM `idcsmart_module_mf_cloud_config` LIMIT 1;");
    Db::execute("CREATE TABLE `idcsmart_module_mf_cloud_resource_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '资源包名称',
  `rid` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云资源包ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}catch(\think\db\exception\PDOException $e){

}



set_time_limit(0);
ini_set('max_execution_time', 3600);
