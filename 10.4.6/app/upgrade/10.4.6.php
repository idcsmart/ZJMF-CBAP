<?php
use think\facade\Db;

upgradeData1046();
function upgradeData1046()
{
	$sql = [
		"ALTER TABLE `idcsmart_cloud_server_product` ADD COLUMN `cpu` text NOT NULL COMMENT '处理器' AFTER `description`;",
		"ALTER TABLE `idcsmart_cloud_server_product` ADD COLUMN `memory` text NOT NULL COMMENT '内存' AFTER `cpu`;",
        "CREATE TABLE `idcsmart_file_log` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件表',
      `uuid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件唯一ID',
      `save_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件保存名',
      `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '文件原名',
      `type` VARCHAR(255) NOT NULL DEFAULT 'default' COMMENT '文件类型：defautl系统默认、ticket工单、app应用等',
      `oss_method` VARCHAR(255) NOT NULL DEFAULT 'LocalOss' COMMENT '存储方式：默认LocalOss本地存储',
      `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
      `client_id` INT(11) NOT NULL DEFAULT '0' COMMENT '所属客户ID',
      `admin_id` INT(11) NOT NULL DEFAULT '0' COMMENT '所属管理员ID',
      `source` VARCHAR(25) NOT NULL DEFAULT 'client' COMMENT '来源：admin管理员，client客户默认',
      `url` VARCHAR(1000) NOT NULL DEFAULT '' COMMENT '文件访问地址',
      PRIMARY KEY (`id`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('system_version_type','stable',0,0,'系统升级版本(beta=内测版,stable正式版)');",
        "INSERT  INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('system_version_type_last','stable',0,0,'最后一次系统升级版本(beta=内测版,stable正式版)');",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='PromoCode';",
        "UPDATE `idcsmart_plugin` SET `version`='2.1.1' WHERE `name`='IdcsmartRenew';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `refund_time` INT(11) NOT NULL DEFAULT '0' COMMENT '退款时间';",
        "ALTER TABLE `idcsmart_refund_record` ADD COLUMN `gateway` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '退款方式';",
        "UPDATE `idcsmart_refund_record` SET refund_time=create_time;",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `notice_open` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否接收通知，1是默认0否';",
        "ALTER TABLE `idcsmart_client` ADD COLUMN `notice_method` VARCHAR(25) NOT NULL DEFAULT 'all' COMMENT '通知方式：all所有，email邮件，sms短信';",
	];

    $auth = Db::name('auth')->where('title', 'auth_system_configuration_system_configuration_system_info')->find();
    if (!empty($auth)){
        $maxOrder = Db::name('auth')->max('order');
        $authId = Db::name('auth')->insertGetId([
            'title'     => 'auth_system_configuration_system_configuration_system_info_update_system_version_type',
            'url'       => '',
            'order'     => $maxOrder+1,
            'parent_id' => $auth['id'],
            'module' => '',
            'plugin' => '',
            'description' => '切换系统升级版本',
        ]);
        $rules = [
            'app\admin\controller\UpgradeSystemController::updateSystemVersionType',
        ];

        foreach ($rules as $value) {
            $authRule = Db::name('auth_rule')->where('name', $value)->find();
            if(empty($authRule)){
                $authRuleId = Db::name('auth_rule')->insertGetId([
                    'name' => $value,
                ]);
            }else{
                $authRuleId = $authRule['id'];
            }
            if(!empty($authRuleId)){
                Db::name('auth_rule_link')->insert([
                    'auth_rule_id' => $authRuleId,
                    'auth_id' => $authId,
                ]);
            }
        }

        Db::name('auth_link')->insert([
            'admin_role_id' => 1,
            'auth_id' => $authId,
        ]);
    }

	foreach($sql as $v){
        try{
            Db::execute($v);
        }catch(\think\db\exception\PDOException $e){

        }
    }

    Db::execute("update `idcsmart_configuration` set `value`='10.4.6' where `setting`='system_version';");
}