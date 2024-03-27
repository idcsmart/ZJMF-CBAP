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
	"ALTER TABLE `idcsmart_module_mf_cloud_line` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';",
	"ALTER TABLE `idcsmart_module_mf_dcim_line` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';",
	"ALTER TABLE `idcsmart_addon_idcsmart_help` ADD COLUMN `cron_release` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否定时发布';",
	"ALTER TABLE `idcsmart_addon_idcsmart_help` ADD COLUMN `cron_release_time` int(11) NOT NULL DEFAULT '0' COMMENT '定时发布时间';",
	"UPDATE `idcsmart_plugin` SET `version`='1.0.1' WHERE `name`='IdcsmartHelp';",
	"ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `cron_release` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否定时发布';",
	"ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `cron_release_time` int(11) NOT NULL DEFAULT '0' COMMENT '定时发布时间';",
	"UPDATE `idcsmart_plugin` SET `version`='1.0.1' WHERE `name`='IdcsmartNews';",
	"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `manual_resource` tinyint(3) NOT NULL DEFAULT '0' COMMENT '手动资源(0=关闭,1=开启)';",
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);