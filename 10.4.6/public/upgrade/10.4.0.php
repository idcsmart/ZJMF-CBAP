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

$sql = [
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='TpCaptcha';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='Idcsmart';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='Smtp';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='AliPayDmf';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='Idcsmartmail';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='UserCustom';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='WxPay';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartRenew';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartRefund';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartFileDownload';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='PromoCode';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartNews';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartCertification';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='Idcsmartali';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartSshKey';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartWithdraw';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartHelp';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartTicket';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartSubAccount';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartCloud';",
	"UPDATE `idcsmart_plugin` SET `version`='2.0.0' WHERE `name`='IdcsmartAnnouncement';",
];

foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}