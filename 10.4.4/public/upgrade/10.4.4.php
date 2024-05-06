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

$sql = array(
	"INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('cart_theme_mobile','default','手机购物车主题');",
);

foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}