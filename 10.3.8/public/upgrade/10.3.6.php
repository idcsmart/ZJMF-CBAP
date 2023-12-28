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
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `no_upgrade_tip_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '不可升降级时,订购页提示';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `default_nat_acl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认NAT转发(0=关闭,1=开启)';",
	"ALTER TABLE `idcsmart_module_mf_cloud_config` ADD COLUMN `default_nat_web` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认NAT建站(0=关闭,1=开启)';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option1` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option2` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option3` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option4` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option5` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option6` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option7` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option8` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option9` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option10` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option11` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option12` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option13` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option14` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option15` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option16` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option17` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option18` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option19` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option20` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option21` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option22` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option23` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';",
    "ALTER TABLE `idcsmart_module_idcsmart_common_product` ADD COLUMN `config_option24` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '自定义配置';"
];

// 是否有dcim的接口
$dcimServer = Db::name('server')->where('module', 'mf_dcim')->find();
if(!empty($dcimServer)){
	$dcimsql = [
		"ALTER TABLE `idcsmart_module_mf_dcim_option` ADD COLUMN `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序';",
		"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_memory_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存是否用等级优惠订购(0=关闭,1=开启)';",
		"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_memory_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存是否用等级优惠升降级(0=关闭,1=开启)';",
		"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_disk_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '硬盘是否用等级优惠订购(0=关闭,1=开启)';",
		"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_disk_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '硬盘是否用等级优惠升降级(0=关闭,1=开启)';",
		"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_bw_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽是否用等级优惠升降级(0=关闭,1=开启)';",
		"ALTER TABLE `idcsmart_module_mf_dcim_config` ADD COLUMN `level_discount_ip_num_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IP是否用等级优惠升降级(0=关闭,1=开启)';",
		"ALTER TABLE `idcsmart_module_mf_dcim_host_link` ADD COLUMN `additional_ip` text NOT NULL COMMENT '附加IP';",
		"ALTER TABLE `idcsmart_module_mf_dcim_host_link` ADD COLUMN `package_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '灵活机型ID';",
		"CREATE TABLE `idcsmart_module_mf_dcim_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'DCIM分组ID',
  `cpu_option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '处理器关联optionID',
  `cpu_num` int(11) unsigned NOT NULL DEFAULT '1' COMMENT 'CPU数量',
  `mem_option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内存关联optionID',
  `mem_num` tinyint(7) unsigned NOT NULL DEFAULT '1' COMMENT '内存数量',
  `disk_option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '硬盘关联optionID',
  `disk_num` tinyint(7) unsigned NOT NULL DEFAULT '1' COMMENT '硬盘数量',
  `bw` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '带宽',
  `ip_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IP数量',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `mem_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内存最大容量',
  `mem_max_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内存最大数量',
  `disk_max_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '硬盘最大数量',
  `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`) USING BTREE,
  KEY `idx_cpu_option_id` (`cpu_option_id`) USING BTREE,
  KEY `idx_mem_option_id` (`mem_option_id`) USING BTREE,
  KEY `idx_disk_option_id` (`disk_option_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='套餐表';",
		"CREATE TABLE `idcsmart_module_mf_dcim_package_option_link` (
`package_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'dcim套餐id',
`option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '可选配optionID',
`option_rel_type` tinyint(7) unsigned NOT NULL DEFAULT '0' COMMENT 'option表的rel_type',
KEY `idx_package_id` (`package_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='套餐可选配置表';",
		"CREATE TABLE `idcsmart_module_mf_dcim_host_option_link` (
`host_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
`option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '配置ID',
`num` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
KEY `idx_host_id` (`host_id`) USING BTREE,
KEY `idx_option_id` (`host_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品配置关联表';",
	];
	$sql = array_merge($sql, $dcimsql);
}
foreach($sql as $v){
    try{
        Db::execute($v);
    }catch(\think\db\exception\PDOException $e){

    }
}

set_time_limit(0);
ini_set('max_execution_time', 3600);