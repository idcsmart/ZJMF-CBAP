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
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `disk_limit_switch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '数据盘数量限制开关(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `disk_limit_num` int(11) NOT NULL DEFAULT '16' COMMENT '数据盘限制数量';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `free_disk_switch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '免费数据盘开关(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `free_disk_size` int(11) NOT NULL DEFAULT '1' COMMENT '免费数据盘大小(G)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_disk` ADD COLUMN `is_free` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否免费(0=不是,1=是)';",
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);