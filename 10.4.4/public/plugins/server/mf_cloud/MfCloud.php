<?php 
namespace server\mf_cloud;

use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\ResourcePackageModel;
use server\mf_cloud\validate\CartValidate;
use server\mf_cloud\validate\VpcNetworkValidate;
use server\mf_cloud\validate\HostUpdateValidate;
use think\facade\Db;
use server\mf_cloud\logic\ToolLogic;

/**
 * 魔方云模块
 */
class MfCloud
{
	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
     * @return  string display_name - 模块名称
     * @return  string version - 版本号
	 */
	public function metaData()
    {
		return ['display_name'=>'魔方云(自定义配置)', 'version'=>'2.0.3'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer()
    {
		$sql = [
			"CREATE TABLE `idcsmart_module_mf_cloud_backup_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT 'snap=快照,bakcup=备份',
  `num` int(11) NOT NULL DEFAULT '1' COMMENT '允许的数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `pt` (`product_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快照备份价格设置表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `node_priority` tinyint(3) NOT NULL DEFAULT '1' COMMENT '开通平衡规则(1=数量平均,2=负载最低,3=内存最低)',
  `ip_mac_bind` tinyint(3) NOT NULL DEFAULT '0' COMMENT '嵌套虚拟化(0=关闭,1=开启)',
  `support_ssh_key` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否支持SSH密钥(0=关闭,1=开启)',
  `rand_ssh_port` tinyint(3) NOT NULL DEFAULT '0' COMMENT '随机SSH端口(0=关闭,1=开启)',
  `support_normal_network` tinyint(3) NOT NULL DEFAULT '0' COMMENT '经典网络(0=不支持,1=支持)',
  `support_vpc_network` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'VPC网络(0=不支持,1=支持)',
  `support_public_ip` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否允许公网IP(0=不支持,1=支持)',
  `backup_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用备份(0=不启用,1=启用)',
  `snap_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用快照(0=不启用,1=启用)',
  `disk_limit_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '性能限制(0=不启用,1=启用)',
  `reinstall_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重装短信验证(0=关闭,1=开启)',
  `reset_password_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重置密码短信验证(0=关闭,1=开启)',
  `niccard` tinyint(4) NOT NULL DEFAULT '0' COMMENT '网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)',
  `cpu_model` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)',
  `ipv6_num` varchar(10) NOT NULL DEFAULT '' COMMENT 'IPv6数量',
  `nat_acl_limit` varchar(10) NOT NULL DEFAULT '' COMMENT 'NAT转发限制',
  `nat_web_limit` varchar(10) NOT NULL DEFAULT '' COMMENT 'NAT建站限制',
  `memory_unit` varchar(10) NOT NULL DEFAULT 'GB' COMMENT '内存单位(GB,MB)',
  `type` varchar(30) NOT NULL DEFAULT 'host' COMMENT '类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)',
  `disk_limit_switch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '数据盘数量限制开关(0=关闭,1=开启)',
  `disk_limit_num` int(11) NOT NULL DEFAULT '16' COMMENT '数据盘限制数量',
  `free_disk_switch` tinyint(3) NOT NULL DEFAULT '0' COMMENT '免费数据盘开关(0=关闭,1=开启)',
  `free_disk_size` int(11) NOT NULL DEFAULT '1' COMMENT '免费数据盘大小(G)',
  `only_sale_recommend_config` tinyint(3) NOT NULL DEFAULT '0' COMMENT '仅售卖套餐',
  `no_upgrade_tip_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '不可升降级时,订购页提示',
  `default_nat_acl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认NAT转发(0=关闭,1=开启)',
  `default_nat_web` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认NAT建站(0=关闭,1=开启)',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			"CREATE TABLE `idcsmart_module_mf_cloud_config_limit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT 'cpu=CPU与内存限制,data_center=数据中心,line=带宽',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '线路ID',
  `min_bw` int(11) NOT NULL DEFAULT '0' COMMENT '最小带宽',
  `max_bw` int(11) NOT NULL DEFAULT '0' COMMENT '最大带宽',
  `cpu` text NOT NULL COMMENT 'CPU',
  `memory` text NOT NULL,
  `min_memory` int(11) NOT NULL DEFAULT '0' COMMENT '最小内存',
  `max_memory` int(11) NOT NULL DEFAULT '0' COMMENT '最大内存',
  `image_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置限制表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_data_center` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(255) NOT NULL DEFAULT '' COMMENT '区域',
  `cloud_config` varchar(20) NOT NULL DEFAULT '' COMMENT 'node=节点,area=区域,node_group=节点分组',
  `cloud_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据中心表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_disk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '磁盘名称',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '磁盘大小',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联云磁盘ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '磁盘类型',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '购买时价格',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `is_free` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否免费(0=不是,1=是)',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据盘';",
			"CREATE TABLE `idcsmart_module_mf_cloud_disk_limit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型(system=系统盘,disk=数据盘)',
  `min_value` int(11) NOT NULL DEFAULT '0' COMMENT '容量小值',
  `max_value` int(11) NOT NULL DEFAULT '0' COMMENT '容量大值',
  `read_bytes` int(11) NOT NULL DEFAULT '0',
  `write_bytes` int(11) NOT NULL DEFAULT '0',
  `read_iops` int(11) NOT NULL DEFAULT '0',
  `write_iops` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `range` (`min_value`,`max_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='性能限制表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_duration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '周期名称',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '周期时常',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '周期单位(hour=小时,day=天,month=自然月)',
  `price_factor` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '价格系数',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期价格',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='周期表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_host_image_link` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			"CREATE TABLE `idcsmart_module_mf_cloud_host_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云实例ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `backup_num` int(11) NOT NULL DEFAULT '0' COMMENT '备份数量',
  `snap_num` int(11) NOT NULL DEFAULT '0' COMMENT '快照数量',
  `power_status` varchar(30) NOT NULL DEFAULT '' COMMENT '电源状态',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `vpc_network_id` int(11) NOT NULL DEFAULT '0' COMMENT 'VPC网络ID',
  `config_data` text NOT NULL COMMENT '用于缓存购买时的配置价格,用于升降级',
  `ssh_key_id` int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT 'host' COMMENT '类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)',
  `recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '推荐配置ID',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `image_id` (`image_id`),
  KEY `recommend_config_id` (`recommend_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品实例关联表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `image_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像分组ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `charge` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否收费(0=不收费,1=收费)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否可用(0=禁用,1=可用)',
  `rel_image_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联魔方云ID',
  PRIMARY KEY (`id`),
  KEY `image_group_id` (`image_group_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='镜像表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='镜像分组';",
			"CREATE TABLE `idcsmart_module_mf_cloud_line` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '线路名称',
  `bill_type` varchar(20) NOT NULL DEFAULT '' COMMENT 'bw=带宽计费,flow=流量计费',
  `bw_ip_group` varchar(10) NOT NULL DEFAULT '' COMMENT '带宽IP分组',
  `defence_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用防护',
  `defence_ip_group` varchar(10) NOT NULL DEFAULT '' COMMENT '防护IP分组',
  `ip_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用附加IP价格',
  `link_clone` tinyint(3) NOT NULL DEFAULT '0' COMMENT '链接创建(0=否,1=是)',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `gpu_name` varchar(255) NOT NULL DEFAULT '',
  `gpu_enable` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用GPU',
  PRIMARY KEY (`id`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='线路表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(7) NOT NULL DEFAULT '0' COMMENT '0=CPU配置\r\n1=内存配置\r\n2=线路带宽计费\r\n3=线路流量计费\r\n4=线路防护配置\r\n5=线路附加IP配置\r\n6=系统盘配置\r\n7=数据盘配置\r\n8=线路显卡',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '计费方式(radio=单选，step=阶梯,total=总量)',
  `value` int(10) NOT NULL DEFAULT '0' COMMENT '单选值',
  `min_value` int(10) NOT NULL DEFAULT '0' COMMENT '最小值',
  `max_value` int(10) NOT NULL DEFAULT '0' COMMENT '最大值',
  `step` int(10) NOT NULL DEFAULT '1' COMMENT '步长',
  `other_config` text NOT NULL COMMENT '其他配置,json存储',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `prr` (`product_id`,`rel_type`,`rel_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通用配置表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '关联表:0=option,1=recommend_config',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `duration_id` int(11) NOT NULL DEFAULT '0' COMMENT '周期ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `prr` (`product_id`,`rel_type`,`rel_id`),
  KEY `duration_id` (`duration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置价格表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_recommend_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `description` text NOT NULL COMMENT '描述',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `cpu` int(11) NOT NULL DEFAULT '0' COMMENT 'CPU',
  `memory` int(11) NOT NULL DEFAULT '0' COMMENT '内存',
  `system_disk_size` int(11) NOT NULL DEFAULT '0' COMMENT '系统盘大小',
  `data_disk_size` int(11) NOT NULL DEFAULT '0' COMMENT '数据盘大小',
  `network_type` varchar(10) NOT NULL DEFAULT '' COMMENT 'normal=经典网络,vpc=VPC网络',
  `bw` int(11) NOT NULL DEFAULT '0' COMMENT '带宽',
  `peak_defence` int(11) NOT NULL DEFAULT '0' COMMENT '防御峰值',
  `system_disk_type` varchar(255) NOT NULL DEFAULT '' COMMENT '系统盘类型',
  `data_disk_type` varchar(255) NOT NULL DEFAULT '' COMMENT '数据盘类型',
  `flow` int(11) NOT NULL DEFAULT '0' COMMENT '流量',
  `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '线路ID',
  `ip_num` int(11) NOT NULL DEFAULT '1' COMMENT 'IP数量',
  `upgrade_range` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0=不可升降级,1=全部,2=自定义',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `gpu_num` int(11) NOT NULL DEFAULT '0' COMMENT 'GPU数量',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `order` (`order`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='套餐表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_vpc_network` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ips` varchar(50) NOT NULL DEFAULT '' COMMENT 'VPC网段',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云VPCID',
  `vpc_name` varchar(255) NOT NULL DEFAULT '',
  `downstream_client_id` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `downstream_client_id` (`downstream_client_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_mf_cloud_resource_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '资源包名称',
  `rid` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云资源包ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
          "CREATE TABLE `idcsmart_module_mf_cloud_recommend_config_upgrade_range` (
  `recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `rel_recommend_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '可升降级套餐ID',
  KEY `recommend_config_id` (`recommend_config_id`),
  KEY `rel_recommend_config_id` (`rel_recommend_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-28
	 * @title 不用之后删除表
	 * @author hh
	 * @version v1
	 */
	public function afterDeleteLastServer()
    {
		$sql = [
			'drop table `idcsmart_module_mf_cloud_backup_config`;',
			'drop table `idcsmart_module_mf_cloud_config`;',
			'drop table `idcsmart_module_mf_cloud_config_limit`;',
			'drop table `idcsmart_module_mf_cloud_data_center`;',
			'drop table `idcsmart_module_mf_cloud_disk`;',
			'drop table `idcsmart_module_mf_cloud_disk_limit`;',
			'drop table `idcsmart_module_mf_cloud_duration`;',
			'drop table `idcsmart_module_mf_cloud_host_image_link`;',
			'drop table `idcsmart_module_mf_cloud_host_link`;',
			'drop table `idcsmart_module_mf_cloud_image`;',
			'drop table `idcsmart_module_mf_cloud_image_group`;',
			'drop table `idcsmart_module_mf_cloud_line`;',
			'drop table `idcsmart_module_mf_cloud_option`;',
			'drop table `idcsmart_module_mf_cloud_price`;',
			'drop table `idcsmart_module_mf_cloud_recommend_config`;',
            'drop table `idcsmart_module_mf_cloud_vpc_network`;',
            'drop table `idcsmart_module_mf_cloud_resource_package`;',
			'drop table `idcsmart_module_mf_cloud_recommend_config_upgrade_range`;',
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-22
	 * @title 测试连接
	 * @author hh
	 * @version v1
	 */
	public function testConnect($param)
    {
        $hash = ToolLogic::formatParam($param['server']['hash']);
        
		$IdcsmartCloud = new IdcsmartCloud($param['server']);
        $IdcsmartCloud->setIsAgent(isset($hash['account_type']) && $hash['account_type'] == 'agent');
		$res = $IdcsmartCloud->login(false, true);
		if($res['status'] == 200){
			unset($res['data']);
			$res['msg'] = lang_plugins('link_success');
		}
		return $res;
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块开通
	 * @author hh
	 * @version v1
	 */
	public function createAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->createAccount($param);
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块暂停
	 * @author hh
	 * @version v1
	 */
	public function suspendAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->suspendAccount($param);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author hh
	 * @version v1
	 */
	public function unsuspendAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->unsuspendAccount($param);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author hh
	 * @version v1
	 */
	public function terminateAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->terminateAccount($param);
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->renew($param);
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->changePackage($param);
	}

	/**
	 * 时间 2022-06-28
	 * @title 变更商品后调用
	 * @author hh
	 * @version v1
	 */
	public function changeProduct($param)
    {
		$param['host_id'] = $param['host']['id'];
		$this->afterSettle($param);
	}

	/**
	 * 时间 2022-06-21
	 * @title 价格计算
	 * @author hh
	 * @version v1
     * @param   ProductModel param.product - 商品模型实例 require
     * @param   int param.custom.duration_id - 周期ID require
     * @param   int param.custom.recommend_config_id - 套餐ID
     * @param   int param.custom.data_center_id - 数据中心ID
     * @param   int param.custom.cpu - CPU
     * @param   int param.custom.memory - 内存
     * @param   int param.custom.system_disk.size - 系统盘大小(G)
     * @param   string param.custom.system_disk.disk_type - 系统盘类型
     * @param   int param.custom.data_disk[].size - 数据盘大小(G)
     * @param   string param.custom.data_disk[].disk_type - 数据盘类型
     * @param   int param.custom.line_id - 线路ID
     * @param   int param.custom.bw - 带宽(Mbps)
     * @param   int param.custom.flow - 流量(G)
     * @param   int param.custom.peak_defence - 防御峰值(G)
     * @param   int param.custom.gpu_num - 显卡数量
     * @param   int param.custom.image_id - 镜像ID
     * @param   int param.custom.ssh_key_id - SSH密钥ID
     * @param   int param.custom.backup_num 0 备份数量
     * @param   int param.custom.snap_num 0 快照数量
     * @param   int param.custom.ip_mac_bind_enable 0 嵌套虚拟化(0=关闭,1=开启)
     * @param   int param.custom.ipv6_num_enable 0 是否使用IPv6(0=关闭,1=开启)
     * @param   int param.custom.nat_acl_limit_enable 0 是否启用NAT转发(0=关闭,1=开启)
     * @param   int param.custom.nat_web_limit_enable 0 是否启用NAT建站(0=关闭,1=开启)
     * @param   int param.custom.resource_package_id 0 资源包ID
     * @param   string param.custom.network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @param   int param.custom.vpc.id - VPC网络ID
     * @param   string param.custom.vpc.ips - VPCIP段
     * @param   bool only_cal - 是否仅计算价格(false=否,true=是)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格 
     * @return  string data.renew_price - 续费价格 
     * @return  string data.billing_cycle - 周期 
     * @return  int data.duration - 周期时长
     * @return  string data.description - 订单子项描述
     * @return  string data.base_price - 基础价格
     * @return  string data.billing_cycle_name - 周期名称多语言
     * @return  string data.preview[].name - 配置项名称
     * @return  string data.preview[].value - 配置项值
     * @return  string data.preview[].price - 配置项价格
     * @return  string data.discount - 用户等级折扣
     * @return  string data.order_item[].type - 订单子项类型(addon_idcsmart_client_level=用户等级)
     * @return  int data.order_item[].rel_id - 关联ID
     * @return  float data.order_item[].amount - 子项金额
     * @return  string data.order_item[].description - 子项描述
	 */
	public function cartCalculatePrice($param)
    {
		$CartValidate = new CartValidate();

		// 仅计算价格验证,不需要验证其他参数
		if($param['scene'] == 'cal_price'){
			if(!$CartValidate->scene('CalPrice')->check($param['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}else{
			// 下单的验证
			if(!$CartValidate->scene('cal')->check($param['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
	        // 当是VPC时,验证VPC
	        if($param['custom']['network_type'] == 'vpc'){
	        	if(isset($param['custom']['vpc'])){
	        		if(isset($param['custom']['vpc']['id']) && $param['custom']['vpc']['id']>0){
	        			
	        		}else{
	        			$VpcNetworkValidate = new VpcNetworkValidate();
	        			if(!$VpcNetworkValidate->scene('ips')->check($param['custom']['vpc'])){
				            return ['status'=>400 , 'msg'=>lang_plugins($VpcNetworkValidate->getError())];
				        }
	        		}
	        	}else{
	        		return ['status'=>400, 'msg'=>lang_plugins('support_vpc_network_param_error')];
	        	}
	        }
            // 验证资源包
            if(isset($param['custom']['resource_package_id']) && !empty($param['custom']['resource_package_id'])){
                $resourcePackage = ResourcePackageModel::where('id', $param['custom']['resource_package_id'])->where('product_id', $param['product']['id'])->find();
                if(empty($resourcePackage)){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_resource_package_not_found')];
                }
            }
		}
        $param['custom']['product_id'] = $param['product']['id'];

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($param, $param['scene'] == 'cal_price');
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 切换商品后的输出
	 * @author hh
	 * @version v1
	 */
	public function serverConfigOption($param)
    {
		$res = [
			'template'=>'template/admin/mf_cloud.html',
		];
		return $res;
	}

	/**
	 * 时间 2022-06-29
	 * @title 前台产品内页输出
	 * @author hh
	 * @version v1
	 */
	public function clientArea()
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('clientarea_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_detail.html"
            ];
        }else{ // pc端
            $clientareaTheme = configuration('clientarea_theme');
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_detail.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_detail.html"
            ];
        }

        return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 产品列表
	 * @author hh
	 * @version v1
	 */
	public function hostList($param)
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('clientarea_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_list.html"
            ];
        }else{ // pc端
            $clientareaTheme = configuration('clientarea_theme');
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_list.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_list.html"
            ];
        }

        return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 前台商品购买页面输出
	 * @author hh
	 * @version v1
	 */
	public function clientProductConfigOption($param)
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('cart_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/cart/mobile/{$mobileTheme}/goods.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/cart/{$type}/{$mobileTheme}/goods.html"
            ];
        }else{ // pc端
            $cartTheme = configuration('cart_theme');
            if (!file_exists(__DIR__."/template/cart/pc/{$cartTheme}/goods.html")){
                $cartTheme = "default";
            }
            $res = [
                'template' => "template/cart/pc/{$cartTheme}/goods.html"
            ];
        }

        return $res;
	}

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author hh
	 * @version v1
     * @param   int param.custom.duration_id - 周期ID require
     * @param   int param.custom.recommend_config_id - 套餐ID
     * @param   int param.custom.data_center_id - 数据中心ID
     * @param   int param.custom.cpu - CPU
     * @param   int param.custom.memory - 内存
     * @param   int param.custom.system_disk.size - 系统盘大小(G)
     * @param   string param.custom.system_disk.disk_type - 系统盘类型
     * @param   int param.custom.data_disk[].size - 数据盘大小(G)
     * @param   string param.custom.data_disk[].disk_type - 数据盘类型
     * @param   int param.custom.line_id - 线路ID
     * @param   int param.custom.bw - 带宽(Mbps)
     * @param   int param.custom.flow - 流量(G)
     * @param   int param.custom.peak_defence - 防御峰值(G)
     * @param   int param.custom.gpu_num - 显卡数量
     * @param   int param.custom.image_id - 镜像ID
     * @param   int param.custom.ssh_key_id - SSH密钥ID
     * @param   int param.custom.backup_num 0 备份数量
     * @param   int param.custom.snap_num 0 快照数量
     * @param   int param.custom.ip_mac_bind_enable 0 嵌套虚拟化(0=关闭,1=开启)
     * @param   int param.custom.ipv6_num_enable 0 是否使用IPv6(0=关闭,1=开启)
     * @param   int param.custom.nat_acl_limit_enable 0 是否启用NAT转发(0=关闭,1=开启)
     * @param   int param.custom.nat_web_limit_enable 0 是否启用NAT建站(0=关闭,1=开启)
     * @param   int param.custom.resource_package_id 0 资源包ID
     * @param   string param.custom.network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @param   int param.custom.vpc.id - VPC网络ID
     * @param   string param.custom.vpc.ips - VPCIP段
     * @param   int param.custom.security_group_id - 安全组ID(ID优先)
     * @param   array param.custom.security_group_protocol - 安全组协议(icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,all)
     * @param   int param.custom.auto_renew 0 自动续费(0=否,1=是)
	 */
	public function afterSettle($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->afterSettle($param);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->durationPrice($param);
	}

    /**
     * 时间 2024-02-19
     * @title 获取商品起售周期价格
     * @desc 获取商品起售周期价格
     * @author hh
     * @version v1
     */
	public function getPriceCycle($param)
	{
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->getPriceCycle($param['product']['id']);
	}

	/**
	 * 时间 2023-02-16
	 * @title 资源下载
	 * @desc 资源下载
	 * @author hh
	 * @version v1
	 */
	public function downloadResource($param)
    {
        $metaData = $this->metaData();

        // 尝试解压到本地目录下
        ToolLogic::unzipToReserver();

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [
				'module' => 'mf_cloud',
				'url' => request()->domain() . '/plugins/server/mf_cloud/data/abc.zip' , // 下载路径
                'version' => $metaData['version'] ?? '1.0.0',
			]
		];
		return $result;
	}

    /**
     * 时间 2023-04-12
     * @title 产品内页模块配置信息输出
     * @desc 产品内页模块配置信息输出
     * @author hh
     * @version v1
     */
    public function adminField($param)
    {
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->adminField($param);
    }

    /**
     * 时间 2024-02-19
     * @title 产品保存后
     * @desc 产品保存后
     * @author hh
     * @version v1
     * @param int param.module_admin_field.cpu - CPU
     * @param int param.module_admin_field.memory - 内存
     * @param int param.module_admin_field.bw - 带宽
     * @param int param.module_admin_field.in_bw - 进带宽
     * @param int param.module_admin_field.out_bw - 出带宽
     * @param int param.module_admin_field.flow - 流量
     * @param int param.module_admin_field.snap_num - 快照数量
     * @param int param.module_admin_field.backup_num - 备份数量
     * @param int param.module_admin_field.defence - 防御峰值
     * @param int param.module_admin_field.ip_num - IP数量
     * @param string param.module_admin_field.ip - 主IP
     * @param int param.module_admin_field.zjmf_cloud_id - 魔方云实例ID
     * @param int param.module_admin_field.disk_[0-9]+ - 对应数据盘大小
     */
    public function hostUpdate($param)
    {
        $HostUpdateValidate = new HostUpdateValidate();
        $param['module_admin_field']['product_id'] = $param['product']['id'];
        if(!$HostUpdateValidate->scene('update')->check($param['module_admin_field'])){
            return ['status'=>400 , 'msg'=>lang_plugins($HostUpdateValidate->getError())];
        }

        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->hostUpdate($param);
    }




}


