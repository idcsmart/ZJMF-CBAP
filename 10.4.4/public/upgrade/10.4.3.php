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
	"INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('home_show_deleted_host', '1', 0, 0, '前台是否展示已删除产品(0=否,1=是)');",
	"UPDATE `idcsmart_auth` SET `module`='addon',`plugin`='HostTransfer' WHERE `title`='auth_business_host_detail_host_transfer';",
	"INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('limit_email_suffix', '0', 0, 0, '是否限制邮箱后缀(0=否,1=是)');",
	"INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('email_suffix', '', 0, 0, '邮箱后缀');",
	"INSERT INTO `idcsmart_configuration`(`setting`, `value`, `create_time`, `update_time`, `description`) VALUES ('account_info_change', '1', 0, 0, '账户信息变更(0禁止1允许)');",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('clientarea_theme_mobile','default','手机主题')",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('clientarea_theme_mobile_switch',0,'是否开启手机主题：1是，0否默认');",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('first_navigation','一级导航','一级导航名称')",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`description`) VALUES ('second_navigation','二级导航','二级导航名称');",
    "ALTER TABLE `idcsmart_order` ADD COLUMN `return_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '同步回调跳转地址';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.3' WHERE `name`='PromoCode';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.3' WHERE `name`='IdcsmartRenew';",
    "UPDATE `idcsmart_plugin` SET `version`='2.0.3' WHERE `name`='IdcsmartRefund';",
);

$install_plugin = Db::name('plugin')->column('name');
if(!in_array('HostTransfer', $install_plugin)){
	$auth = Db::name('auth')->where('title', 'auth_business_host_detail_host_transfer')->find();
	if(!empty($auth)){
		$sql[] = "DELETE FROM `idcsmart_auth_link` WHERE `auth_id`={$auth['id']};";
		$sql[] = "DELETE FROM `idcsmart_auth_rule_link` WHERE `auth_id`={$auth['id']};";
	}
	$sql[] = "DELETE FROM `idcsmart_auth` WHERE `module`='addon' AND `plugin`='HostTransfer';";
	$sql[] = "DELETE FROM `idcsmart_auth_rule` WHERE `module`='addon' AND `plugin`='HostTransfer';";
}

// 是否有云的接口
$cloudServer = Db::name('server')->where('module', 'mf_cloud')->find();
if(!empty($cloudServer)){
	$sql[] = "ALTER TABLE `idcsmart_module_mf_cloud_config_limit` ADD COLUMN `image_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '镜像ID';";
}

// 是否有dcim的接口
$dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
if(!empty($dcimServer)){
	$sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_option` ADD COLUMN `value_show` varchar(255) NOT NULL DEFAULT '' COMMENT '实际显示';";
}

// 添加原有模块版本号
$ModuleLogic = new \app\common\logic\ModuleLogic();
$module = $ModuleLogic->getModuleList();
foreach($module as $v){
	$moduleName = parse_name($v['name'], 1);
	$server = Db::name('server')->where('module', $v['name'])->find();
	if(!empty($server) && !in_array($moduleName, $install_plugin)){
		$sql[] = "INSERT INTO `idcsmart_plugin`(`status`, `name`, `title`, `url`, `author`, `author_url`, `version`, `description`, `config`, `module`) VALUES (1, '{$moduleName}', '{$v['display_name']}', '{$v['display_name']}', '', '', '{$v['version']}', '', '', 'server');";
	}
}


foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}