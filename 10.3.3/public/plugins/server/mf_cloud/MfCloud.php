<?php 
namespace server\mf_cloud;

use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\VpcNetworkModel;
use server\mf_cloud\model\ResourcePackageModel;
use server\mf_cloud\validate\CartValidate;
use server\mf_cloud\validate\VpcNetworkValidate;
use server\mf_cloud\validate\HostUpdateValidate;
use think\facade\Db;
use server\mf_cloud\logic\ToolLogic;

/**
 * 魔方云模块
 */
class MfCloud{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'魔方云(自定义配置)', 'version'=>'1.0.1'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer(){
		$sql = [
			"CREATE TABLE `idcsmart_module_mf_cloud_backup_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT 'snap=快照,bakcup=备份',
  `num` int(11) NOT NULL COMMENT '允许的数量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `pt` (`product_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快照备份价格设置表';",
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
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
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
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置限制表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_data_center` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(255) NOT NULL DEFAULT '' COMMENT '区域',
  `cloud_config` varchar(20) NOT NULL COMMENT 'node=节点,area=区域,node_group=节点分组',
  `cloud_config_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应ID',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据中心表';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据盘';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='性能限制表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_duration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '周期名称',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '周期时常',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '周期单位(hour=小时,day=天,month=自然月)',
  `price_factor` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '价格系数',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='周期表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_host_image_link` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
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
  `vpc_network_id` int(11) DEFAULT '0' COMMENT 'VPC网络ID',
  `config_data` text NOT NULL COMMENT '用于缓存购买时的配置价格,用于升降级',
  `ssh_key_id` int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT 'host' COMMENT '类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V)',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品实例关联表';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像分组';",
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
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线路表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(7) NOT NULL DEFAULT '0' COMMENT '0=CPU配置\r\n1=内存配置\r\n2=线路带宽计费\r\n3=线路流量计费\r\n4=线路防护配置\r\n5=线路附加IP配置\r\n6=系统盘配置\r\n7=数据盘配置',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通用配置表';",
			"CREATE TABLE `idcsmart_module_mf_cloud_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `option_id` int(11) NOT NULL DEFAULT '0' COMMENT '配置ID',
  `duration_id` int(11) NOT NULL DEFAULT '0' COMMENT '周期ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `option_id` (`option_id`),
  KEY `duration_id` (`duration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置价格表';",
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
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `order` (`order`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='推荐配置表';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
            "CREATE TABLE `idcsmart_module_mf_cloud_resource_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '资源包名称',
  `rid` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云资源包ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
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
	public function afterDeleteLastServer(){
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
	public function testConnect($params){
        $hash = ToolLogic::formatParam($params['server']['hash']);
        
		$IdcsmartCloud = new IdcsmartCloud($params['server']);
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
	public function createAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->createAccount($params);
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块暂停
	 * @author hh
	 * @version v1
	 */
	public function suspendAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->suspendAccount($params);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author hh
	 * @version v1
	 */
	public function unsuspendAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->unsuspendAccount($params);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author hh
	 * @version v1
	 */
	public function terminateAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->terminateAccount($params);
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->renew($params);
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->changePackage($params);
	}

	/**
	 * 时间 2022-06-28
	 * @title 变更商品后调用
	 * @author hh
	 * @version v1
	 */
	public function changeProduct($params){
		$params['host_id'] = $params['host']['id'];
		$this->afterSettle($params);
	}

	/**
	 * 时间 2022-06-21
	 * @title 价格计算
	 * @author hh
	 * @version v1
	 * @param   ProductModel params.product - 商品模型
	 * @param   array params.custom - 自定义参数
	 * @param   int params.custom.data_center_id - 数据中心ID require
	 * @param   int params.custom.package_id - 套餐ID require
	 * @param   int params.custom.image_id - 镜像ID
	 * @param   string params.custom.hostname - 主机名
	 * @param   string params.custom.password - 密码
	 * @param   int params.custom.backup_enable 0 是否启用备份(0=不启用,1=启用)
	 * @param   int params.custom.panel_enable 0 是否启用面板密码(0=不启用,1=启用)
	 * @param   int params.custom.duration_price_id - 周期价格ID require
	 * @return  [type]         [description]
	 */
	public function cartCalculatePrice($params){
		$CartValidate = new CartValidate();

		// 仅计算价格验证,不需要验证其他参数
		if($params['scene'] == 'cal_price'){
			if(!$CartValidate->scene('CalPrice')->check($params['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}else{
			// 下单的验证
			if(!$CartValidate->scene('cal')->check($params['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
	        // 当是VPC时,验证VPC
	        if($params['custom']['network_type'] == 'vpc'  ){
	        	if(isset($params['custom']['vpc'])){
	        		if(isset($params['custom']['vpc']['id']) && $params['custom']['vpc']['id']>0){
	        			$vpcNetwork = VpcNetworkModel::find($params['custom']['vpc']['id']);
	        			if(empty($vpcNetwork) || $vpcNetwork['client_id'] != get_client_id() || $vpcNetwork['data_center_id'] != $params['custom']['data_center_id']){
	        				return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
	        			}
	        		}else{
	        			$VpcNetworkValidate = new VpcNetworkValidate;
	        			if(!$VpcNetworkValidate->scene('ips')->check($params['custom']['vpc'])){
				            return ['status'=>400 , 'msg'=>lang_plugins($VpcNetworkValidate->getError())];
				        }
	        		}
	        	}else{
	        		return ['status'=>400, 'msg'=>lang_plugins('support_vpc_network_param_error')];
	        	}
	        }
            // 验证资源包
            if(isset($params['custom']['resource_package_id']) && !empty($params['custom']['resource_package_id'])){
                $resourcePackage = ResourcePackageModel::where('id', $params['custom']['resource_package_id'])->where('product_id', $params['product']['id'])->find();
                if(empty($resourcePackage)){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_resource_package_not_found')];
                }
            }
		}
        $params['custom']['product_id'] = $params['product']['id'];

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($params, $params['scene'] == 'cal_price');
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 切换商品后的输出
	 * @author hh
	 * @version v1
	 */
	public function serverConfigOption($params){
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
	public function clientArea(){
		$res = [
			'template'=>'template/clientarea/product_detail.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 产品列表
	 * @author hh
	 * @version v1
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function hostList($params){
		$res = [
			'template'=>'template/clientarea/product_list.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 前台购买
	 * @author hh
	 * @version v1
	 * @param   string x       -             x
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function clientProductConfigOption($params){
		$res = [
			'template'=>'template/clientarea/goods.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-06-29
	 * @title 后台产品内页输出,TODO
	 * @author hh
	 * @version v1
	 */
	public function adminArea(){
		return '';
	}

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author hh
	 * @version v1
     * @param   int param.custom.data_center_id - 数据中心ID require
     * @param   int param.custom.package_id - 套餐ID require
     * @param   int param.custom.image_id - 镜像ID require
     * @param   string param.custom.password - 密码 和SSHKEYID一起2个之中必须传一个
     * @param   string param.custom.ssh_key_id - SSHKEYID 和密码一起2个之中必须传一个
	 */
	public function afterSettle($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->afterSettle($params);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->durationPrice($params);
	}


	// public function allConfigOption($params){
	// 	$package = PackageModel::where('product_id', $params['product']['id'])
	// 			->field('name,id value')
	// 			->select()
	// 			->toArray();

	// 	$data = [];
	// 	if(!empty($package)){
	// 		$data = [
	// 			[
	// 				'name'=>lang_plugins('package'),
	// 				'field'=>'package_id',
	// 				'type'=>'dropdown',
	// 				'option'=>$package
	// 			]
	// 		];
	// 	}

	// 	$result = [
	// 		'status'=>200,
	// 		'msg'=>lang_plugins('success_message'),
	// 		'data'=>$data,
	// 	];
	// 	return $result;
	// }

	/**
	 * 时间 2022-08-04
	 * @title 
	 * @desc 
	 * @url
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   string x             - x
	 * @return  [type] [description]
	 */
	// public function currentConfigOption($params){
	// 	$result = [
	// 		'status'=>200,
	// 		'msg'=>lang_plugins('success_message'),
	// 		'data'=>[],
	// 	];

	// 	$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
	// 	if(empty($hostLink)){
	// 		return $result;
	// 	}
	// 	$package = PackageModel::find($hostLink['package_id']);

	// 	$days = $params['host']['billing_cycle_time']/3600/24;

	// 	$duration = [
	// 		'0'=>'onetime_fee',
	// 		'30'=>'month_fee',
	// 		'90'=>'quarter_fee',
	// 		'180'=>'half_year_fee',
	// 		'365'=>'year_fee',
	// 		'730'=>'two_year',
	// 		'1095'=>'three_year',
	// 	];

	// 	// TODO
	// 	// $securityGroupLink = IdcsmartSecurityGroupHostLinkModel::where('host_id', $params['host']['id'])->find();
	// 	// $durationPrice = DurationPriceModel::where('product_id', $params['host']['product_id'])->where('duration', $params['host']['billing_cycle_time']/3600/24)->find();

	// 	$data = [
	// 		'data_center_id' 	=> $package['data_center_id'],
	// 		'package_id'	 	=> $hostLink['package_id'],
	// 		'image_id'		 	=> $hostLink['image_id'],
	// 		'backup_num'		=> $hostLink['backup_num'],
	// 		'snap_num'			=> $hostLink['snap_num'],
	// 		'duration' 			=> $duration[ $days ] ?? '',
	// 	];

	// 	$result['data'] = $data;
	// 	return $result;
	// }

	public function getPriceCycle($params)
	{
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->getPriceCycle($params['product']['id']);
	}

	/**
	 * 时间 2023-02-16
	 * @title 资源下载
	 * @desc 资源下载
	 * @author hh
	 * @version v1
	 * @param   [type] $param [description]
	 * @return  [type]        [description]
	 */
	public function downloadResource($param){
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
     * @title 要输出的值
     * @desc 要输出的值
     * @author hh
     * @version v1
     */
    public function adminField($param){
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->adminField($param);
    }

    public function hostUpdate($param){
        $HostUpdateValidate = new HostUpdateValidate();
        $param['module_admin_field']['product_id'] = $param['product']['id'];
        if(!$HostUpdateValidate->scene('update')->check($param['module_admin_field'])){
            return ['status'=>400 , 'msg'=>lang_plugins($HostUpdateValidate->getError())];
        }

        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->hostUpdate($param);
    }




}


