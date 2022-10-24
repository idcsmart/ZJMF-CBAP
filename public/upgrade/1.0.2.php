<?php 

require dirname(dirname(__DIR__ )) . '/config.php';
require dirname(dirname(__DIR__ )) .'/vendor/autoload.php';

define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ) . '/'); # 网站入口目录

set_time_limit(0);
ini_set('max_execution_time', 3600);

$upgradePHP = new upgradePHP();
$upgradePHP->run();

class upgradePHP{

	public function run(){
		$db = $this->dbConnect();

		$sql = [];
		$sql[] = "ALTER TABLE `idcsmart_module_common_cloud_package` ADD COLUMN `order` int(10) NOT NULL DEFAULT '0' COMMENT '排序';";
		$sql[] = "ALTER TABLE `idcsmart_module_idcsmart_dcim_package` ADD COLUMN `order` int(10) NOT NULL DEFAULT '0' COMMENT '排序';";

		try {

			foreach($sql as $v){
				$db->exec($v);
			}
        } catch (\Exception $e) {
            
        }
	}

	public function dbConnect()
    {
        $database = include IDCSMART_ROOT."/config/database.php";
        $database = $database['connections']['mysql'];
        $db = new PDO("mysql:host={$database['hostname']};port={$database['hostport']};dbname={$database['database']}",$database['username'],$database['password']);
        return $db;
    }

}