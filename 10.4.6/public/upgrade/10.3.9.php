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

$sql = [];

$sql = [
  "UPDATE `idcsmart_country` SET `name_zh`='中国台湾' WHERE `iso`='TW' AND `iso3`='TWN' LIMIT 1;",
  "UPDATE `idcsmart_country` SET `name_zh`='韩国' WHERE `iso`='KR' AND `iso3`='KOR' LIMIT 1;",
	"CREATE TABLE `idcsmart_self_defined_field` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型(product=商品)',
  `relid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `field_name` varchar(255) NOT NULL DEFAULT '' COMMENT '字段名称',
  `field_type` varchar(50) NOT NULL DEFAULT '' COMMENT '字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,checkbox=勾选框,textarea=文本区)',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '字段描述',
  `regexpr` varchar(255) NOT NULL DEFAULT '' COMMENT '正则验证规则',
  `field_option` varchar(255) NOT NULL DEFAULT '' COMMENT '下拉选项',
  `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '显示排序',
  `is_required` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填',
  `show_order_page` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订购页可见(0=否,1=是)',
  `show_order_detail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订购详情可见(0=否,1=是)',
  `show_client_host_detail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '前台产品内页可见(0=否1,=是)',
  `show_admin_host_detail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '后台产品内页可见(0=否,1=是)',
  `show_client_host_list` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '前台列表可见(0=否,1=是)',
  `upstream_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上游ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_relid` (`relid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义字段表';",
    "CREATE TABLE `idcsmart_self_defined_field_value` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `self_defined_field_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '自定义字段ID',
  `relid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID(产品ID)',
  `value` varchar(2000) NOT NULL DEFAULT '' COMMENT '值',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_self_defined_field_id` (`self_defined_field_id`) USING BTREE,
  KEY `idx_relid` (`relid`) USING BTREE,
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义字段值表';",
    "CREATE TABLE `idcsmart_host_ip` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `dedicate_ip` varchar(255) NOT NULL DEFAULT '' COMMENT '主IP',
  `assign_ip` text NOT NULL COMMENT '附加IP',
  `ip_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IP数量',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_host_id` (`host_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品IP信息表';",
    "ALTER TABLE `idcsmart_menu` ADD COLUMN `second_reminder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '二次提醒0否1是';",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('clientarea_url','',0,0,'会员中心地址');",
    "ALTER TABLE `idcsmart_sms_template` ADD COLUMN `product_url` varchar(255) NOT NULL DEFAULT '' COMMENT '应用场景';",
    "ALTER TABLE `idcsmart_sms_template` ADD COLUMN `remark` varchar(1000) NOT NULL DEFAULT '' COMMENT '场景说明';",
    "INSERT INTO `idcsmart_configuration`(`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('tab_logo','',0,0,'标签页LOGO');",
];

// 是否有DCIM的接口
$dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
if(!empty($dcimServer)){
    $sql[] = "ALTER TABLE `idcsmart_module_mf_dcim_model_config` ADD COLUMN `optional_only_for_upgrade` tinyint(1) NOT NULL DEFAULT '0' COMMENT '增值仅用于升降级(0=关闭,1=开启)';";
}


$plugins = Db::name('plugin')->where('module', 'addon')->select()->toArray();
$tables = Db::query('SHOW TABLES');
$tables = array_column($tables, 'Tables_in_'.DATABASE_NAME);
foreach ($plugins as $key => $value) {
    if($value['name']=='CycleArtificialOrder'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_cycle_artificial_order` ADD COLUMN `renew_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '续费金额';";
            $sql[] = "UPDATE `idcsmart_addon_cycle_artificial_order` SET `renew_amount`=`amount`;";
        }
    }
    if($value['name']=='IdcsmartCertification'){
        $hooks = Db::name('plugin_hook')->where('plugin', $value['name'])->where('module', 'addon')->column('name');
        if(!in_array('check_certification_recharge', $hooks)){
            $sql[] = "insert into `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) values ('check_certification_recharge',1,'IdcsmartCertification','addon',0);";
        }
        $sql[] = "UPDATE `idcsmart_plugin` SET `version`='1.0.7' WHERE `name`='IdcsmartCertification';";
    }
    if($value['name']=='IdcsmartClientLevel'){
        if(version_compare($value['version'], '1.0.3', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_client_level` ADD COLUMN `notes` varchar(1000) NOT NULL DEFAULT '' COMMENT '备注' AFTER `discount_status`;";
        }
    }
    if($value['name']=='IdcsmartCloud'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` MODIFY COLUMN `security_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组ID';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` MODIFY COLUMN `security_rule_id` varchar(255) NOT NULL DEFAULT '' COMMENT '云系统安全组规则ID';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_link` ADD COLUMN `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_security_group_rule_link` ADD COLUMN `type` varchar(50) NOT NULL DEFAULT 'host' COMMENT '类型';";
        }
    }
    if($value['name']=='IdcsmartDomain'){
        if(version_compare($value['version'], '1.0.2', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_domain_info_template` ADD COLUMN `idtype2` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人证件类型' AFTER `img`;";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_domain_info_template` ADD COLUMN `idnum2` varchar(100) NOT NULL DEFAULT '' COMMENT '联系人证件值' AFTER `idtype2`;";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_domain_info_template` ADD COLUMN `img2` text NOT NULL COMMENT '联系人证件图' AFTER `idnum2`;";
        }
    }
    if($value['name']=='IdcsmartFileDownload'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `global_order` int(11) NOT NULL DEFAULT '0' COMMENT '公共排序';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_file` ADD COLUMN `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序';";
        }
    }
    if($value['name']=='IdcsmartHelp'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_help` ADD COLUMN `cron_release` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否定时发布';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_help` ADD COLUMN `cron_release_time` int(11) NOT NULL DEFAULT '0' COMMENT '定时发布时间';";
        }
    }
    if($value['name']=='IdcsmartInvoice'){
        if(version_compare($value['version'], '1.0.5', '>=')){
            if(!in_array('idcsmart_addon_idcsmart_invoice_project', $tables)){
                $config = Db::name('addon_idcsmart_invoice_config')->where("1=1")->select()->toArray();
                $config = array_column($config, 'value', 'name');
                $normalTaxRate = $config['normal_tax_rate_show'] ?? 0;
                $normalTaxPrice = $config['normal_tax_rate'] ?? 0;
                $specialTaxRate = $config['special_tax_rate_show'] ?? 0;
                $specialTaxPrice = $config['special_tax_rate'] ?? 0;
                $specialTaxSwitch = $config['special_tax_rate_switch'] ?? 1;
                $time = time();

                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('before_order_delete', {$value['status']}, 'IdcsmartInvoice', 'addon', 0);";
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('before_delete_host_unpaid_upgrade_order', {$value['status']}, 'IdcsmartInvoice', 'addon', 0);";
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('before_delete_unpaid_renew_order', {$value['status']}, 'IdcsmartInvoice', 'addon', 0);";
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('before_order_pay', {$value['status']}, 'IdcsmartInvoice', 'addon', 0);";
                $sql[] = "INSERT INTO `idcsmart_addon_idcsmart_invoice_config` (`name`,`value`) VALUES ('across_year_invoice','0');";
                $sql[] = "CREATE TABLE `idcsmart_addon_idcsmart_invoice_project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '项目名称',
  `normal_tax_rate` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '普通发票税率',
  `normal_tax_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '普通发票收税金额',
  `special_tax_rate` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '专用发票税率',
  `special_tax_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '专用发票收税金额',
  `special_tax_switch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '增值税专用发票开关(0=关闭,1=开启)',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发票项目表';";
                $sql[] = "INSERT INTO `idcsmart_addon_idcsmart_invoice_project` (`name`,`normal_tax_rate`,`normal_tax_price`,`special_tax_rate`,`special_tax_price`,`special_tax_switch`,`create_time`) VALUES ('信息技术服务费','{$normalTaxRate}','{$normalTaxPrice}','{$specialTaxRate}','{$specialTaxPrice}','{$specialTaxSwitch}',{$time});";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_invoice` ADD COLUMN `invoice_project` varchar(255) NOT NULL DEFAULT '' COMMENT '发票项目';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_invoice` ADD COLUMN `invoice_format` varchar(20) NOT NULL DEFAULT '' COMMENT '发票格式';";
                $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_invoice` ADD COLUMN `invoice_filename` varchar(255) NOT NULL DEFAULT '' COMMENT '发票文件名';";
            }
        }
    }
    if($value['name']=='IdcsmartNews'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `cron_release` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否定时发布';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `cron_release_time` int(11) NOT NULL DEFAULT '0' COMMENT '定时发布时间';";
        }
    }
    if($value['name']=='IdcsmartRecommend'){
        if(version_compare($value['version'], '1.0.6', '>=')){
            if(!in_array('idcsmart_addon_idcsmart_recommend_log', $tables)){
                $sql[] = "CREATE TABLE `idcsmart_addon_idcsmart_recommend_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '推介计划表',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '被推介客户ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `promoter_id` int(11) NOT NULL DEFAULT '0' COMMENT '推广者ID',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `promoter_id` (`promoter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                $sql[] = "insert  into `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) values ('after_order_create',1,'IdcsmartRecommend','addon',0);";
            }
        }
        if(version_compare($value['version'], '1.0.9', '>=')){
            if(!in_array('idcsmart_addon_idcsmart_recommend_product_url', $tables)){
                $sql[] = "CREATE TABLE `idcsmart_addon_idcsmart_recommend_product_url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `identify` varchar(255) NOT NULL DEFAULT '' COMMENT '唯一识别码',
  `url` varchar(2000) NOT NULL DEFAULT '' COMMENT '链接',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            }
        }
    }
    if($value['name']=='IdcsmartSale'){
        if(version_compare($value['version'], '1.0.3', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_sale_product_commission_config` ADD COLUMN `upgrade_mode` varchar(20) NOT NULL DEFAULT '' COMMENT '升级提成模式(fixed=固定,percent=百分比)';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_sale_product_commission_config` ADD COLUMN `upgrade_value` int(11) NOT NULL DEFAULT '0' COMMENT '升级提成值';";
        }
        if(version_compare($value['version'], '1.0.4', '>=')){
            $hooks = Db::name('plugin_hook')->where('plugin', $value['name'])->where('module', 'addon')->column('name');
            if(!in_array('admin_client_index', $hooks)){
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('admin_client_index', {$value['status']}, 'IdcsmartSale', 'addon', 0);";
            }
            if(!in_array('after_client_edit', $hooks)){
                $sql[] = "INSERT INTO `idcsmart_plugin_hook`(`name`, `status`, `plugin`, `module`, `order`) VALUES ('after_client_edit', {$value['status']}, 'IdcsmartSale', 'addon', 0);";
            }
        }
    }
    if($value['name']=='IdcsmartWithdraw'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_withdraw_rule` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态0关闭1开启';";
        }
    }
    if($value['name']=='IdcsmartAnnouncement'){
        $sql[] = "insert into `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) values ('web_seo_custom',1,'IdcsmartAnnouncement','addon',0);";
        $sql[] = "UPDATE `idcsmart_plugin` SET `version`='1.0.1' WHERE `name`='IdcsmartAnnouncement';";
    }
    if($value['name']=='IdcsmartNews'){
        if(version_compare($value['version'], '1.0.1', '>=')){
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `cron_release` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否定时发布';";
            $sql[] = "ALTER TABLE `idcsmart_addon_idcsmart_news` ADD COLUMN `cron_release_time` int(11) NOT NULL DEFAULT '0' COMMENT '定时发布时间';";
        }
        $sql[] = "insert into `idcsmart_plugin_hook`(`name`,`status`,`plugin`,`module`,`order`) values ('web_seo_custom',1,'IdcsmartNews','addon',0);";
        $sql[] = "UPDATE `idcsmart_plugin` SET `version`='1.0.2' WHERE `name`='IdcsmartNews';";
    }
}


foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}
