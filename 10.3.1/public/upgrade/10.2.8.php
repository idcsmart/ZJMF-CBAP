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
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `reinstall_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重装短信验证(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `reset_password_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重置密码短信验证(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `niccard` tinyint(4) NOT NULL DEFAULT '0' COMMENT '网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)';",
	"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `reinstall_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重装短信验证(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `reset_password_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重置密码短信验证(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_image_group` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';",
	"ALTER TABLE `idcsmart_module_mf_dcim_image_group` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';",
	"ALTER TABLE `idcsmart_module_mf_dcim_option` MODIFY COLUMN `value` varchar(10) NOT NULL DEFAULT 0 COMMENT '单选值';"
];
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);
