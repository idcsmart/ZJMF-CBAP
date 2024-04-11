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
	"ALTER TABLE `idcsmart_module_mf_cloud_config` DROP COLUMN `host_prefix`;",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` DROP COLUMN `host_length`;",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `type` varchar(30) NOT NULL DEFAULT 'host' COMMENT '类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_host_link` ADD COLUMN `type` varchar(30) NOT NULL DEFAULT 'host' COMMENT '类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)';",
	"ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` MODIFY COLUMN `security_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组ID';",
    "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` MODIFY COLUMN `security_rule_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组规则ID';",
    "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` ADD COLUMN `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型';",
    "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` ADD COLUMN `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型';",
    "UPDATE `idcsmart_plugin` SET `version`='1.0.1' WHERE `name`='IdcsmartCloud';",
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);