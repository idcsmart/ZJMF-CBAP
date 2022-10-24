<?php 
namespace server\idcsmart_cloud;

use server\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use server\idcsmart_cloud\model\DurationPriceModel;
use server\idcsmart_cloud\model\HostLinkModel;
use server\idcsmart_cloud\model\PackageModel;
use server\idcsmart_cloud\model\BwModel;
use server\idcsmart_cloud\model\CalModel;
use server\idcsmart_cloud\model\ImageModel;
use server\idcsmart_cloud\model\DataCenterServerLinkModel;
use server\idcsmart_cloud\model\ConfigModel;
use server\idcsmart_cloud\model\HostImageLinkModel;
use server\idcsmart_cloud\logic\ToolLogic;
use app\common\model\HostModel;
use addon\idcsmart_cloud\model\IdcsmartVpcHostLinkModel;
use addon\idcsmart_cloud\model\IdcsmartVpcLinkModel;
use addon\idcsmart_cloud\model\IdcsmartVpcModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;
use server\idcsmart_cloud\validate\CartValidate;
use think\facade\Db;

/**
 * 魔方云模块系统方法
 */
class IdcsmartCloud{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'魔方云', 'version'=>'1.0'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表TODO
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer(){
		$sql = [];
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_bw` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module_idcsmart_cloud_bw_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '带宽类型ID',
  `bw` int(11) NOT NULL DEFAULT '0' COMMENT '带宽',
  `flow` int(11) NOT NULL DEFAULT '0' COMMENT '流量',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `description` text CHARACTER SET utf8mb4 NOT NULL COMMENT '显示描述',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `flow_type` varchar(10) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'all' COMMENT '流量方向(in=进,out=出,all=进+出)',
  `in_bw_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否独立进带宽(0=关闭,1=开启)',
  `in_bw` int(11) NOT NULL DEFAULT '0' COMMENT '进带宽',
  PRIMARY KEY (`id`),
  KEY `module_idcsmart_cloud_bw_type_id` (`module_idcsmart_cloud_bw_type_id`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云模块带宽表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_bw_data_center_link` (
  `module_idcsmart_cloud_bw_id` int(11) NOT NULL DEFAULT '0' COMMENT '带宽ID',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  KEY `module_idcsmart_cloud_bw_id` (`module_idcsmart_cloud_bw_id`),
  KEY `module_idcsmart_cloud_data_center_id` (`module_idcsmart_cloud_data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云模块带宽数据中心关联表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_bw_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '名称',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `description` text CHARACTER SET utf8mb4 NOT NULL COMMENT '描述',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='云模块带宽类型表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_cal` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '显示名称',
  `module_idcsmart_cloud_cal_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组ID',
  `cpu` int(11) NOT NULL DEFAULT '0' COMMENT 'CPU',
  `memory` int(11) NOT NULL DEFAULT '0' COMMENT '内存(MB)',
  `disk_size` int(11) NOT NULL DEFAULT '0' COMMENT '硬盘(GB)',
  `description` text NOT NULL COMMENT '描述',
  `other_param` text CHARACTER SET utf8mb4 NOT NULL COMMENT '其他参数',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module_idcsmart_cloud_cal_group_id` (`module_idcsmart_cloud_cal_group_id`) USING BTREE,
  KEY `product_id` (`module_idcsmart_cloud_cal_group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云计算型号表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_cal_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '分组名称',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 NOT NULL COMMENT '描述',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='云模块计算型号分组表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `backup_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '启用备份价格(0=不启用,1=启用)',
  `backup_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '备份价格',
  `backup_param` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '备份参数',
  `panel_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '启用独立面板(0=不启用,1=启用)',
  `panel_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '独立面板价格',
  `panel_param` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '独立面板参数',
  `snap_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '启用快照(0=不启用,1=启用)',
  `snap_free_num` int(11) NOT NULL DEFAULT '0' COMMENT '快照免费数量',
  `snap_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '快照价格',
  `hostname_rule` tinyint(7) NOT NULL DEFAULT '1' COMMENT '主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机)',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='其他设置表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_data_center` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '国家',
  `country_code` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '国家代码',
  `city` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '区域',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云数据中心';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_data_center_server_link` (
  `server_id` int(11) NOT NULL DEFAULT '0' COMMENT '接口ID',
  `server_param` text CHARACTER SET utf8mb4 NOT NULL COMMENT '接口参数',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据中心接口关联表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_duration_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '时长',
  `duration_name` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '时长名称',
  `display_name` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '显示名称',
  `cal_ratio` float(10,2) NOT NULL DEFAULT '1.00' COMMENT '计算型号比例',
  `bw_ratio` float(10,2) NOT NULL DEFAULT '1.00' COMMENT '带宽比例',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `pay_type` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'recurring_prepayment' COMMENT '付款类型(周期先付recurring_prepayment,周期后付recurring_postpaid',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云周期价格表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_host_image_link` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `module_idcsmart_cloud_image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_host_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联云ID',
  `ip` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '主IP',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `module_idcsmart_cloud_image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `password` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '密码',
  `backup_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用自动备份',
  `snap_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用自动快照',
  `panel_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用独立面板',
  `module_idcsmart_cloud_package_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `power_status` varchar(255) NOT NULL DEFAULT '' COMMENT '电源状态',
  `vpc_ip` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT 'VPC内网IP',
  `addon_idcsmart_cloud_ssh_key_id` int(11) NOT NULL DEFAULT '0' COMMENT 'sshkeyID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云模块信息表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '系统名称',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `enable` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否启用(0=禁用,1=启用)',
  `charge` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否付费',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `filename` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '文件名/镜像标识',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `image_type` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '镜像类型(system=官方镜像,app=应用镜像)',
  `module_idcsmart_cloud_image_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像分组ID',
  `icon` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '图标',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `image_type` (`image_type`) USING BTREE,
  KEY `module_idcsmart_cloud_image_group_id` (`module_idcsmart_cloud_image_group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云操作系统表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_image_data_center_link` (
  `module_idcsmart_cloud_image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `enable` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否启用(0=禁用,1=启用)',
  `is_exist` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否存在(0=不存在,1=存在)',
  KEY `module_idcsmart_cloud_image_id` (`module_idcsmart_cloud_image_id`) USING BTREE,
  KEY `module_idcsmart_cloud_data_center_id` (`module_idcsmart_cloud_data_center_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像数据中心关联表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_image_data_center_server_link` (
  `server_id` int(11) NOT NULL DEFAULT '0',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0',
  `module_idcsmart_cloud_image_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_image_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '分组名称',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `description` text CHARACTER SET utf8mb4 NOT NULL COMMENT '描述',
  `enable` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `enable` (`enable`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云操作系统分组表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '套餐名称',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `module_idcsmart_cloud_cal_id` int(11) NOT NULL DEFAULT '0' COMMENT '计算型号ID',
  `module_idcsmart_cloud_bw_id` int(11) NOT NULL DEFAULT '0' COMMENT '带宽ID',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='魔方云模块套餐表';";
		$sql[] = "CREATE TABLE IF NOT EXISTS `idcsmart_module_idcsmart_cloud_package_data_center_link` (
  `module_idcsmart_cloud_package_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  KEY `module_idcsmart_cloud_package_id` (`module_idcsmart_cloud_package_id`) USING BTREE,
  KEY `module_idcsmart_cloud_data_center_id` (`module_idcsmart_cloud_data_center_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='套餐数据中心关联表';";

		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-28
	 * @title 不用之后删除表TODO
	 * @author hh
	 * @version v1
	 */
	public function afterDeleteLastServer(){
		$sql = [];
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_bw`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_bw_data_center_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_bw_type`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_cal`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_config`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_data_center`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_data_center_server_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_duration_price`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_host_image_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_host_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_image`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_image_data_center_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_image_data_center_server_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_image_group`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_package`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_cloud_package_data_center_link`;";

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
		$IC = new IC($params['server']);
		$res = $IC->login(false, true);
		if($res['status'] == 200){
			unset($res['data']);
			$res['msg'] = lang_plugins('link_success');
		}
		return $res;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块开通
	 * @author hh
	 * @version v1
	 */
	public function createAccount($params){
		$IC = new IC($params['server']);

		$serverHash = ToolLogic::formatParam($params['server']['hash']);

		// 开通参数
		$post = [];
		$post['hostname'] = $params['host']['name'];

		// 定义用户参数
		$prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面
		$username = $prefix.$params['client']['id'];

		$userData = [
            'username'=>$username,
            'email'=>$params['client']['email'] ?: '',
            'status'=>1,
            'real_name'=>$params['client']['username'] ?: '',
            'password'=>rand_str()
        ];
        $IC->userCreate($userData);
        $userCheck = $IC->userCheck($username);
		if($userCheck['status'] != 200){
			return $userCheck;
		}
		$post['client'] = $userCheck['data']['id'];

		// 获取当前配置
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($hostLink) && $hostLink['rel_id'] > 0){
			return ['status'=>400, 'msg'=>lang_plugins('host_already_created')];
		}
		$package = PackageModel::find($hostLink['module_idcsmart_cloud_package_id']);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
		}
		$cal = CalModel::find($package['module_idcsmart_cloud_cal_id']);
		if(empty($cal)){
			return ['status'=>400, 'msg'=>lang_plugins('package_cal_not_found')];
		}
		$bw = BwModel::find($package['module_idcsmart_cloud_bw_id']);
		if(empty($bw)){
			return ['status'=>400, 'msg'=>lang_plugins('package_bw_not_found')];
		}

		// 没有vpc默认经典网络
		$post['network_type'] = 'normal';
		// 是否有VPC
		$vpcHostLink = IdcsmartVpcHostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($vpcHostLink)){
			$post['network_type'] = 'vpc';
			// 是否有当前接口对应vpc_id
			$vpcLink = IdcsmartVpcLinkModel::where('addon_idcsmart_vpc_id', $vpcHostLink['addon_idcsmart_vpc_id'])
						->where('server_id', $params['server']['id'])
						->find();
			if(!empty($vpcLink) && !empty($vpcLink['vpc_network_id'])){
				// TODO 是否先验证一下该ID
				$post['vpc'] = $vpcLink['vpc_network_id'];
			}else{
				// 新建vpc
				$vpc = IdcsmartVpcModel::find($vpcHostLink['addon_idcsmart_vpc_id']);
				if(empty($vpc)){
					return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
				}
				$post['vpc_name'] = $vpc['name'];
				$post['vpc_ips'] = $vpc['ip'];
			}
		}
		// 是否有安全组
		$securityGroupHostLink = IdcsmartSecurityGroupHostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($securityGroupHostLink)){
			$securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $securityGroupHostLink['addon_idcsmart_security_group_id'])
								->where('server_id', $params['server']['id'])
								->find();
			if(!empty($securityGroupLink)){
				$post['security_group'] = $securityGroupLink['security_id'];
			}else{
				// 获取安全组数据
				$securityGroup = IdcsmartSecurityGroupModel::find($securityGroupHostLink['addon_idcsmart_security_group_id']);
				if(empty($securityGroup)){
					return ['status'=>400, 'msg'=>lang_plugins('security_group_not_found')];
				}
				$post['type'] = $securityGroup['type'];
				// 自动创建安全组
				$securityGroupData = [
					'name'=>$securityGroup['name'],
					'description'=>$securityGroup['description'],
					'uid'=>$post['client'],
					'type'=>$securityGroup['type'],
					'create_default_rule'=>0,   // 不创建默认规则
				];
				$securityGroupCreateRes = $IC->securityGroupCreate($securityGroupData);
				if($securityGroupCreateRes['status'] != 200){
					return $securityGroupCreateRes;
				}
				$post['security_group'] = $securityGroupCreateRes['data']['id'];
				// 保存关联
				$IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
				$IdcsmartSecurityGroupLinkModel->saveSecurityGroupLink([
					'addon_idcsmart_security_group_id'=>$securityGroupHostLink['addon_idcsmart_security_group_id'],
					'server_id'=>$params['server']['id'],
					'security_id'=>$securityGroupCreateRes['data']['id'],
				]);
				// 创建规则
				$IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
				$securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $securityGroupHostLink['addon_idcsmart_security_group_id'])->select()->toArray();
				foreach($securityGroupRule as $v){
					$ruleId = $v['id'];
					unset($v['id'], $v['lock']);
					$securityGroupRuleCreateRes = $IC->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], $v);
					if($securityGroupRuleCreateRes['status'] == 200){
						$IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
							'addon_idcsmart_security_group_rule_id'=>$ruleId,
							'server_id'=>$params['server']['id'],
							'security_rule_id'=>$securityGroupRuleCreateRes['data']['id'] ?? 0
						]);
					}
				}
			}
		}
		// 以镜像方式创建暂时,以后加入其他方式
		$image = ImageModel::find($hostLink['module_idcsmart_cloud_image_id']);
		if($image['charge'] == 1){
			$HostImageLinkModel = new HostImageLinkModel();
			$HostImageLinkModel->saveLink($params['host']['id'], $image['id']);
		}
		$imageCheck = $IC->getImageId($image['filename']);
		if(!isset($imageCheck['data']['id']) || empty($imageCheck['data']['id'])){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_in_zjmf_cloud')];
		}
		// 是否使用了SSH key
		if(!empty($hostLink['addon_idcsmart_cloud_ssh_key_id'])){
			$sshKey = IdcsmartSshKeyModel::find($hostLink['addon_idcsmart_cloud_ssh_key_id']);
			if(empty($sshKey)){
				return ['status'=>400, 'msg'=>lang_plugins('ssh_key_not_found')];
			}
			$sshKeyRes = $IC->sshKeyCreate([
				'type' => 1,
				'uid'  => $post['client'],
				'name' => $sshKey['name'],
				'public_key'=>$sshKey['public_key'],
			]);
			if($sshKeyRes['status'] != 200){
				return ['status'=>400, 'msg'=>$sshKeyRes['msg'] ?? lang_plugins('ssh_key_create_failed')];
			}
			$post['ssh_key'] = $sshKeyRes['data']['id'];
			$post['password_type'] = 1;
		}else{
			$post['password_type'] = 0;
		}

		$post['os'] = $imageCheck['data']['id'];

		$dataCenterServerLink = DataCenterServerLinkModel::where('server_id', $params['server']['id'])->where('module_idcsmart_cloud_data_center_id', $hostLink['module_idcsmart_cloud_data_center_id'])->find();
		$config = ConfigModel::where('product_id', $params['product']['id'])->find();

		// 合并所有可用参数
		$otherParam = ToolLogic::formatParam($cal['other_param']); // 计算可用参数
		$otherParam = array_merge($otherParam, ToolLogic::formatParam($dataCenterServerLink['server_param'] ?? ''));
		if(!empty($hostLink['backup_enable'])){
			$otherParam = array_merge($otherParam, ToolLogic::formatParam($config['backup_enable']));
			$post['backup_num'] = 999;
			// 默认给一个
		}else{
			$post['backup_num'] = -1;
		}
		if(!empty($hostLink['panel_enable'])){
			$otherParam = array_merge($otherParam, ToolLogic::formatParam($config['panel_param']));
		}
		// if(!empty($config['snap_enable'])){
		$post['snap_num'] = 999;
		// }

		// 一些默认值
		$otherParam['type'] = $otherParam['type'] ?? 'host';
		$otherParam['flow_way'] = $otherParam['flow_way'] ?? 'all';

		$post['cpu'] = $cal['cpu'];
		$post['memory'] = $cal['memory'];
		$post['system_disk_size'] = $cal['disk_size'];
		$post['in_bw'] = $bw['bw'];
		$post['out_bw'] = $bw['bw'];
		if($bw['in_bw_enable'] == 1){
			$post['in_bw'] = $bw['in_bw'];
		}
		$flow_type = [
			'in'  => 1,
			'out' => 2,
			'all' => 3
		];
		$post['traffic_type'] = $flow_type[ $bw['flow_type'] ] ?? 3;
		$post['traffic_quota'] = $bw['flow'] ?: 0;

		if($post['password_type']){
			$post['rootpass'] = aes_password_decode($hostLink['password']);
		}
		// 支持的参数,不在里面的全都排除
		$supportParam = [
			'area',			  // 区域ID
			'node',			  // 节点ID
			'node_group',	  // 节点分组ID
			'ip_group',		  // IP分组ID
			'node_priority',  // 节点优先级,1,2,3
			'nat_acl_limit',  // NAT转发数量
			'nat_web_limit',  // NAT建站数量
			'advanced_cpu',   // 智能CPU规则ID
			'advanced_bw',	  // 智能带宽规则ID
			'network_type',   // 网络类型,normal,vpc
			'ip_num',		  // ip数量
			'backup_num',	  // 备份数量
			'snap_num',		  // 快照数量
			'bind_mac',		  // IPMAC绑定
			'cpu_limit',	  // CPU限制
			'port',			  // 随机端口
			'cpu_model',	  // CPU模式
			'ipv6_num',			// IPv6数量
			'type',				  // 节点类型
			'store',				  // 存储ID
			'traffic_bill_type',  // 计费周期,特殊处理
			'system_disk_io_limit', // 系统盘IO限制, 特殊处理
		];
		foreach($supportParam as $v){
			if(isset($otherParam[$v])){
				if($v == 'traffic_bill_type' && $otherParam[$v] == 'last_30days'){
					$post['reset_flow_day'] = date('j');
					continue;
				}
				if($v == 'system_disk_io_limit'){
					$arr = explode(',', $otherParam[$v]);
		            $post['system_read_bytes_sec'] = $arr[0] > 0 ? (int)$arr[0] : 0;
		            $post['system_write_bytes_sec'] = $arr[1] > 0 ? (int)$arr[1] : 0;
		            $post['system_read_iops_sec'] = $arr[2] > 0 ? (int)$arr[2] : 0;
		            $post['system_write_iops_sec'] = $arr[3] > 0 ? (int)$arr[3] : 0;
				}
				$post[$v] = $otherParam[$v];
			}
		}
		$post['num'] = 1;
		$res = $IC->cloudCreate($post);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'	=>lang_plugins('host_create_success')
			];

			$update = [];
			$update['rel_id'] = $res['data']['id'];
			// 获取详情同步信息
			$detail = $IC->cloudDetail($res['data']['id']);
			if($detail['status'] == 200){
				// if($hostLink['backup_enable']){
				// 	$IC->cloudCreateCronSnap($res['data']['id'], [
				// 		'type'=>'backup',
				// 		'frequency'=>1,
				// 		'cycle'=>'day',
				// 		'disk'=>[
				// 			$detail['data']['disk'][0]['id']
				// 		]
				// 	]);
				// }
				// if($hostLink['snap_enable']){
				// 	$IC->cloudCreateCronSnap($res['data']['id'], [
				// 		'type'=>'snap',
				// 		'frequency'=>1,
				// 		'cycle'=>'day',
				// 		'disk'=>[
				// 			$detail['data']['disk'][0]['id']
				// 		]
				// 	]);
				// }
				$update['password'] = aes_password_encode($detail['data']['rootpassword']);
				$update['ip'] = $detail['data']['mainip'] ?? '';

				// 如果有vpc
				if(!empty($vpcHostLink) && $detail['data']['network_type'] == 'vpc'){
					$update['vpc_ip'] = $detail['data']['network'][0]['ipaddress'][0]['ipaddress'] ?? '';

					$IdcsmartVpcLinkModel = new IdcsmartVpcLinkModel();
					$IdcsmartVpcLinkModel->saveVpcLink(['addon_idcsmart_vpc_id'=>$vpcHostLink['addon_idcsmart_vpc_id'], 'server_id'=>$params['server']['id'], 'vpc_network_id'=>$detail['data']['network'][0]['vpc']]);
				}
			}
			HostLinkModel::where('id', $hostLink['id'])->update($update);
		}else{
			$result = [
				'status'=>400,
				'msg'=>$res['msg'] ?: lang_plugins('host_create_failed'),
			];
			HostLinkModel::where('id', $hostLink['id'])->update(['power_status'=>'fault']);
		}
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块暂停
	 * @author hh
	 * @version v1
	 */
	public function suspendAccount($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
		}
		$IC = new IC($params['server']);
		$res = $IC->cloudSuspend($id);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'=>lang_plugins('suspend_success'),
			];
		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('suspend_failed'),
			];

		}
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author hh
	 * @version v1
	 */
	public function unsuspendAccount($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
		}
		$IC = new IC($params['server']);
		$res = $IC->cloudUnsuspend($id);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'=>lang_plugins('unsuspend_success'),
			];
		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('unsuspend_failed'),
			];
		}
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author hh
	 * @version v1
	 */
	public function terminateAccount($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
		}
		$IC = new IC($params['server']);
		$res = $IC->cloudDelete($id);
		if($res['status'] == 200 || $res['http_code'] == 404){
			HostLinkModel::where('host_id', $params['host']['id'])->update(['rel_id'=>0]);

			$result = [
				'status'=>200,
				'msg'=>lang_plugins('delete_success'),
			];

		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('delete_failed'),
			];


		}
		return $result;
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($params){
		// 直接解除暂停
		return $this->unsuspendAccount($params);
	}

	/**
	 * 时间 2022-06-28
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params){
		// 判断是什么类型
		if(!isset($params['custom']['type'])){
			return ['status'=>400, 'msg'=>lang_plugins('参数错误')];
		}
		if($params['custom']['type'] == 'buy_image'){
			// 购买镜像
			$HostImageLinkModel = new HostImageLinkModel();
			$HostImageLinkModel->saveLink($params['host']['id'], $params['custom']['image_id']);
		}else if($params['custom']['type'] == 'change_package'){
			// 变更套餐
			$update = [];
			$update['module_idcsmart_cloud_package_id'] = $params['custom']['package_id'];

			HostLinkModel::update($update, ['host_id'=>$params['host']['id']]);

			$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
			$id = $hostLink['rel_id'] ?? 0;
			if(empty($id)){
				return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
			}

			$package = PackageModel::alias('p')
					->field('c.cpu,c.memory,c.other_param,b.bw,b.flow,b.flow_type,b.in_bw_enable,b.in_bw')
					->leftJoin('module_idcsmart_cloud_cal c', 'p.module_idcsmart_cloud_cal_id=c.id')
					->leftJoin('module_idcsmart_cloud_bw b', 'p.module_idcsmart_cloud_bw_id=b.id')
					->where('p.id', $params['custom']['package_id'])
					->find();

			// TODO其他参数也需要升降级?
			if(!empty($package)){
                $IC = new IC($params['server']);

				$post['cpu'] = $package['cpu'];
				$post['memory'] = $package['memory'];
				$post['traffic_quota'] = $package['flow'];

				$flow_type = [
					'in'  => 1,
					'out' => 2,
					'all' => 3
				];
				$post['traffic_type'] = $flow_type[ $package['flow_type'] ] ?? 3;

				$res = $IC->cloudModifyBw($id, ['in_bw'=>$package['in_bw_enable'] ? $package['in_bw'] : $package['bw'], 'out_bw'=>$package['bw']]);
				if($res['status'] != 200){
					return ['status'=>400, 'msg'=>lang_plugins('修改带宽失败,原因:'.$res['msg'])];
				}
				$res = $IC->cloudModify($id, $post);
				if($res['status'] != 200){
					return ['status'=>400, 'msg'=>lang_plugins('修改配置失败,原因:'.$res['msg'])];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('套餐不存在')];
			}
		}else{
			// 暂时不处理


		}

		// 先修改当前配置
		// $update = [];
		// if(isset($params['custom']['package_id']) && !empty($params['custom']['package_id'])){
		// 	$update['module_idcsmart_cloud_package_id'] = $params['custom']['package_id'];
		// }
		// if(!isset($params['custom']['backup_enable'])){
		// 	$update['backup_enable'] = (int)$params['custom']['backup_enable'];
		// }
		// if(isset($params['custom']['snap_enable'])){
		// 	$update['snap_enable'] = (int)$params['custom']['snap_enable'];
		// }
		// if(!empty($update)){
		// 	HostLinkModel::update($update, ['host_id'=>$params['host']['id']]);
		// }
		// $hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		// $id = $hostLink['rel_id'] ?? 0;
		// if(empty($id)){
		// 	return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
		// }

		// $IC = new IC($params['server']);
		// $detail = $IC->cloudDetail($id);
		// if($detail['status'] == 400){
		// 	return $detail;
		// }
		// $post = [];
		// // 升降级套餐
		// if(isset($params['custom']['package_id'])){
		// 	$package = PackageModel::alias('p')
		// 			->field('c.cpu,c.memory,c.other_param,b.bw,b.flow,b.flow_type,b.in_bw_enable,b.in_bw')
		// 			->leftJoin('module_idcsmart_cloud_cal c', 'p.module_idcsmart_cloud_cal_id=c.id')
		// 			->leftJoin('module_idcsmart_cloud_bw b', 'p.module_idcsmart_cloud_bw_id=b.id')
		// 			->where('p.id', $params['custom']['package_id'])
		// 			->find();

		// 	// TODO其他参数也需要升降级?
		// 	if(!empty($package)){
		// 		$post['cpu'] = $package['cpu'];
		// 		$post['memory'] = $package['memory'];
		// 		$post['traffic_quota'] = $package['flow'];

		// 		$flow_type = [
		// 			'in'  => 1,
		// 			'out' => 2,
		// 			'all' => 3
		// 		];
		// 		$post['traffic_type'] = $flow_type[ $bw['flow_type'] ] ?? 3;

		// 		$IC->cloudModifyBw($id, ['in_bw'=>$package['in_bw_enable'] ? $package['in_bw'] : $package['bw'], 'out_bw'=>$package['bw']]);
		// 	}
		// }
		// // 自动备份快照列表
		// $cron = [];
		// // 是否启用自动备份
		// if(isset($params['custom']['backup_enable'])){
		// 	$res = $IC->cloudCronSnap($id);
		// 	if($res['status'] == 200){
		// 		$cron = $res['data'];
		// 	}
		// 	$backupCron = [];
		// 	foreach($cron as $v){
		// 		if($v['type'] == 'backup'){
		// 			$backupCron = $v;
		// 			break;
		// 		}
		// 	}
		// 	if($params['custom']['backup_enable'] > 0){
		// 		if(empty($backupCron)){
		// 			$IC->cloudCreateCronSnap($id, [
		// 				'type'=>'backup',
		// 				'frequency'=>1,
		// 				'cycle'=>'day',
		// 				'disk'=>[
		// 					$detail['data']['disk'][0]['id']
		// 				]
		// 			]);
		// 		}
		// 	}else{
		// 		if(!empty($backupCron)){
		// 			$IC->cloudDeleteCronSnap($id, $backupCron['id']);
		// 		}
		// 	}
		// }
		// // 是否启用自动快照
		// if(isset($params['custom']['snap_enable'])){
		// 	if(empty($cron)){
		// 		$res = $IC->cloudCronSnap($id);
		// 		if($res['status'] == 200){
		// 			$cron = $res['data'];
		// 		}
		// 	}
		// 	$snapCron = [];
		// 	foreach($cron as $v){
		// 		if($v['type'] == 'snap'){
		// 			$snapCron = $v;
		// 			break;
		// 		}
		// 	}
		// 	if($params['custom']['snap_enable'] > 0){
		// 		if(empty($snapCron)){
		// 			$IC->cloudCreateCronSnap($id, [
		// 				'type'=>'snap',
		// 				'frequency'=>1,
		// 				'cycle'=>'day',
		// 				'disk'=>[
		// 					$detail['data']['disk'][0]['id']
		// 				]
		// 			]);
		// 		}
		// 	}else{
		// 		if(!empty($snapCron)){
		// 			$IC->cloudDeleteCronSnap($id, $snapCron['id']);
		// 		}
		// 	}
		// }
		// // 升级快照数量
		// if(isset($params['custom']['snap_num'])){
		// 	$post['snap_num'] = (int)$params['custom']['snap_num'];
		// }
		// if(!empty($post)){
		// 	$IC->cloudModify($id, $post);
		// }
		return ['status'=>200];
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
		if(!$CartValidate->scene('cal')->check($params['custom'])){
            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
        }

		$DurationPriceModel = new DurationPriceModel();

		$res = $DurationPriceModel->cartCalculatePrice($params['custom']);
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
			'template'=>'template/admin/cloud_config.html',
		];
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 商品保存时调用
	 * @author hh
	 * @version v1
	 */
	// public function productSave($params){}

	/**
	 * 时间 2022-06-29
	 * @title 前台产品内页输出,TODO
	 * @author hh
	 * @version v1
	 */
	public function clientArea(){
		return '';
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

	// 前台商品配置项输出,好像不需要
	// public function clientProductConfigOption(){}

	// 后台商品配置项输出,好像不需要
	// public function adminProductConfigOption(){}

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author hh
	 * @version v1
     * @param   int param.custom.data_center_id - 数据中心ID require
     * @param   int param.custom.package_id - 套餐ID require
     * @param   int param.custom.image_id - 镜像ID require
     * @param   int param.custom.vpc_id 0 VPCID
     * @param   int param.custom.security_group_id 0 安全组ID
     * @param   string param.custom.hostname - 主机名 require
     * @param   string param.custom.password - 密码 和SSHKEYID一起2个之中必须传一个
     * @param   string param.custom.ssh_key_id - SSHKEYID 和密码一起2个之中必须传一个
     * @param   int param.custom.backup_enable - 启用备份
     * @param   int param.custom.panel_enable - 启用独立面板
     * @param   int param.custom.duration_price_id - 周期价格ID require
	 */
	public function afterSettle($params){
		// 这里不验证了
		$data = [
			'host_id'=>$params['host_id'],
			'module_idcsmart_cloud_data_center_id'=>$params['custom']['data_center_id'],
			'module_idcsmart_cloud_image_id'=>$params['custom']['image_id'],
			'password'=>aes_password_encode($params['custom']['password'] ?? ''),
			'backup_enable'=>$params['custom']['backup_enable'] ?? 0,
			'panel_enable'=>$params['custom']['panel_enable'] ?? 0,
			'module_idcsmart_cloud_package_id'=>$params['custom']['package_id'],
			'power_status'=>'off',
		];
		if(isset($param['ssh_key_id']) && !empty($param['ssh_key_id'])){
			$data['ssh_key_id'] = $params['ssh_key_id'];
			$data['password'] = aes_password_encode('');
		}
		$res = HostLinkModel::where('host_id', $params['host_id'])->find();
		if(empty($res)){
			HostLinkModel::create($data);
		}else{
			HostLinkModel::update($data, ['host_id'=>$params['host_id']]);
		}
		// 覆盖自动生成的name
		if(isset($params['custom']['hostname']) && !empty($params['custom']['hostname'])){
			HostModel::update(['name'=>$params['custom']['hostname']], ['id'=>$params['host_id']]);
		}
		// 选择了vpc
		if(isset($params['custom']['vpc_id']) && !empty($params['custom']['vpc_id'])){
			$res = IdcsmartVpcHostLinkModel::where('host_id', $params['host_id'])->find();
			if(empty($res)){
				IdcsmartVpcHostLinkModel::create(['addon_idcsmart_vpc_id'=>$params['custom']['vpc_id'], 'host_id'=>$params['host_id']]);
			}else{
				IdcsmartVpcHostLinkModel::update(['addon_idcsmart_vpc_id'=>$params['custom']['vpc_id']], ['host_id'=>$params['host_id']]);
			}
		}
		if(isset($params['custom']['security_group_id']) && !empty($params['custom']['security_group_id'])){
			$res = IdcsmartSecurityGroupHostLinkModel::where('host_id', $params['host_id'])->find();
			if(empty($res)){
				IdcsmartSecurityGroupHostLinkModel::create(['addon_idcsmart_security_group_id'=>$params['custom']['security_group_id'], 'host_id'=>$params['host_id']]);
			}else{
				IdcsmartSecurityGroupHostLinkModel::update(['addon_idcsmart_security_group_id'=>$params['custom']['security_group_id']], ['host_id'=>$params['host_id']]);
			}
		}
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($params){
		$DurationPriceModel = new DurationPriceModel();
		$result = $DurationPriceModel->currentDurationPrice($params['host']['id']);
		return $result;
	}


	public function allConfigOption($params){
		$package = PackageModel::where('product_id', $params['product']['id'])
				->field('name,id value')
				->select()
				->toArray();

		$data = [];
		if(!empty($package)){
			$data = [
				[
					'name'=>lang_plugins('package'),
					'field'=>'package_id',
					'type'=>'dropdown',
					'option'=>$package
				]
			];
		}

		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>$data,
		];
		return $result;
	}

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
	public function currentConfigOptioin($params){
		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>[],
		];

		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(empty($hostLink)){
			return $result;
		}

		$vpcLink = IdcsmartVpcHostLinkModel::where('host_id', $params['host']['id'])->find();
		$securityGroupLink = IdcsmartSecurityGroupHostLinkModel::where('host_id', $params['host']['id'])->find();

		$durationPrice = DurationPriceModel::where('product_id', $params['host']['product_id'])->where('duration', $params['host']['billing_cycle_time']/3600/24)->find();

		$data = [
			'data_center_id' 	=> $hostLink['module_idcsmart_cloud_data_center_id'],
			'package_id'	 	=> $hostLink['module_idcsmart_cloud_package_id'],
			'image_id'		 	=> $hostLink['module_idcsmart_cloud_image_id'],
			'vpc_id'		  	=> $vpcLink['addon_idcsmart_vpc_id'] ?? 0,
			'security_group_id'	=> $securityGroupLink['addon_idcsmart_security_group_id'] ?? 0,
			'hostname'			=> $params['host']['name'] ?? '',
			'password'			=> aes_password_decode($hostLink['password']) ?: '',
			// 'ssh_key_id'		=>'',
			'backup_enable'		=> $hostLink['backup_enable'],
			'panel_enable'		=> $hostLink['panel_enable'],
			'duration_price_id' => $durationPrice['id'] ?? 0,
		];

		$result['data'] = $data;
		return $result;
	}














}


