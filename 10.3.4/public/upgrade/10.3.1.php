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
	"ALTER TABLE `idcsmart_module_mf_cloud_duration` ADD COLUMN `price_factor` float(4, 2) NOT NULL DEFAULT 1 COMMENT '价格系数';",
	"ALTER TABLE `idcsmart_module_mf_dcim_duration` ADD COLUMN `price_factor` float(4, 2) NOT NULL DEFAULT 1 COMMENT '价格系数';",
	"UPDATE `idcsmart_plugin` SET `version`='1.0.4' WHERE `name`='IdcsmartCertification';",
	"UPDATE `idcsmart_plugin` SET `version`='1.0.2' WHERE `name`='IdcsmartTicket';",
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);