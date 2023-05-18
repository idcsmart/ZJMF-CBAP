<?php 
namespace server\common_cloud;

use app\common\model\HostModel;
use server\common_cloud\idcsmart_cloud\IdcsmartCloud;
use server\common_cloud\logic\ToolLogic;

use server\common_cloud\model\PackageModel;
use server\common_cloud\model\ImageModel;
use server\common_cloud\model\ConfigModel;
use server\common_cloud\model\BackupConfigModel;
use server\common_cloud\model\HostLinkModel;
use server\common_cloud\model\DataCenterModel;
use server\common_cloud\model\HostImageLinkModel;

use server\common_cloud\validate\CartValidate;

use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;
use think\facade\Db;

/**
 * 通用魔方云模块
 */
class CommonCloud{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'通用魔方云', 'version'=>'1.0.0'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer(){
		$sql = [];
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_backup_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '允许的数量',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT 'snap=快照,bakcup=备份',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '费用',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `product_id_type` (`product_id`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快照备份价格设置表';";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_type` tinyint(5) NOT NULL DEFAULT '0' COMMENT '产品模式,0=固定配置,1=自定义配置',
  `support_ssh_key` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否支持SSH密钥(0=不支持,1=支持)',
  `buy_data_disk` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否支持独立订购(0=不支持,1=支持)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '每10G价格',
  `disk_min_size` varchar(20) NOT NULL DEFAULT '' COMMENT '最小容量',
  `disk_max_size` varchar(20) NOT NULL DEFAULT '' COMMENT '最大容量',
  `disk_max_num` tinyint(5) NOT NULL DEFAULT '1' COMMENT '最大附加数量',
  `disk_store_id` varchar(20) NOT NULL DEFAULT '' COMMENT '储存ID',
  `backup_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用备份(0=不启用,1=启用)',
  `snap_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用快照(0=不启用,1=启用)',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置表';";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_data_center` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `cloud_config` varchar(20) NOT NULL DEFAULT '' COMMENT 'node=节点,area=区域,node_group=节点分组',
  `cloud_config_id` int(11) NOT NULL DEFAULT '0' COMMENT 'cloud_config对应类型的ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据中心表';";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_host_image_link` (
  `host_id` int(10) NOT NULL DEFAULT '0',
  `image_id` int(10) NOT NULL DEFAULT '0',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_host_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `backup_num` int(11) NOT NULL DEFAULT '0' COMMENT '允许备份的数量',
  `snap_num` int(11) NOT NULL DEFAULT '0' COMMENT '允许快照数量',
  `package_id` int(11) NOT NULL DEFAULT '0' COMMENT '当前配置ID',
  `package_data` text NOT NULL COMMENT '当前配置时间数据json',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `data_disk_size` varchar(255) NOT NULL DEFAULT '',
  `ssh_key_id` int(11) NOT NULL DEFAULT '0',
  `password` varchar(128) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `power_status` varchar(20) NOT NULL DEFAULT '',
  `free_disk_id` int(11) NOT NULL DEFAULT '0' COMMENT '免费磁盘的ID,0=没有',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `image_id` (`image_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='host产品关联表';";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_group_id` int(10) NOT NULL DEFAULT '0' COMMENT '镜像分组ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '镜像名称',
  `enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否可用(0=禁用,1=可用)',
  `charge` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否收费(0=不收费,1=收费)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_image_id` int(10) NOT NULL DEFAULT '0' COMMENT '魔方云关联ID',
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  PRIMARY KEY (`id`),
  KEY `image_group_id` (`image_group_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像表';";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像分组表';";
		$sql[] = "CREATE TABLE `idcsmart_module_common_cloud_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `description` text NOT NULL COMMENT '描述',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `cpu` int(10) NOT NULL DEFAULT '0' COMMENT 'CPU',
  `memory` int(10) NOT NULL DEFAULT '0' COMMENT '内存',
  `system_disk_size` int(10) NOT NULL DEFAULT '0' COMMENT '系统盘大小(GB)',
  `system_disk_store` int(10) NOT NULL DEFAULT '0' COMMENT '系统盘存储ID(0=自动)',
  `free_data_disk_size` int(10) NOT NULL DEFAULT '0' COMMENT '免费数据盘大小',
  `data_disk_store` int(10) NOT NULL DEFAULT '0' COMMENT '数据盘储存ID',
  `in_bw` int(10) NOT NULL DEFAULT '0' COMMENT '进带宽',
  `out_bw` int(10) NOT NULL DEFAULT '0' COMMENT '出带宽',
  `ip_num` int(10) NOT NULL DEFAULT '0' COMMENT 'IP数量',
  `ip_group` int(10) NOT NULL DEFAULT '0' COMMENT 'IP分组ID',
  `custom_param` text NOT NULL COMMENT '自定义参数',
  `traffic_enable` int(255) NOT NULL DEFAULT '0' COMMENT '是否启用流量计费(0=关闭,1=开启)',
  `flow` int(10) NOT NULL DEFAULT '0' COMMENT '可用流量(GB)',
  `traffic_bill_type` varchar(20) NOT NULL DEFAULT 'month' COMMENT 'month=自然月,last_30days=购买日一月',
  `onetime_fee` varchar(20) NOT NULL DEFAULT '' COMMENT '一次性',
  `month_fee` varchar(20) NOT NULL DEFAULT '' COMMENT '月',
  `quarter_fee` varchar(20) NOT NULL DEFAULT '' COMMENT '季度',
  `half_year_fee` varchar(20) NOT NULL DEFAULT '' COMMENT '半年',
  `year_fee` varchar(20) NOT NULL DEFAULT '' COMMENT '一年',
  `two_year` varchar(20) NOT NULL DEFAULT '' COMMENT '两年',
  `three_year` varchar(255) NOT NULL DEFAULT '' COMMENT '三年',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `product_id` int(10) NOT NULL DEFAULT '0',
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='套餐配置表';";
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
		$sql = [];
		$sql[] = "drop table `idcsmart_module_common_cloud_backup_config`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_config`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_data_center`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_host_image_link`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_host_link`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_image`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_image_group`;";
		$sql[] = "drop table `idcsmart_module_common_cloud_package`;";
		
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
		$IdcsmartCloud = new IdcsmartCloud($params['server']);
		$res = $IdcsmartCloud->login(false, true);
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
		$IdcsmartCloud = new IdcsmartCloud($params['server']);

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
        $IdcsmartCloud->userCreate($userData);
        $userCheck = $IdcsmartCloud->userCheck($username);
		if($userCheck['status'] != 200){
			return $userCheck;
		}
		$post['client'] = $userCheck['data']['id'];
		
		// 获取当前配置
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($hostLink) && $hostLink['rel_id'] > 0){
			return ['status'=>400, 'msg'=>lang_plugins('host_already_created')];
		}
		$package = PackageModel::find($hostLink['package_id']);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
		}
		$dataCenter = DataCenterModel::find($hostLink['data_center_id'] ?: $package['data_center_id']);

		if(!empty($dataCenter)){
			$post[ $dataCenter['cloud_config'] ] = $dataCenter['cloud_config_id'];
		}
		
		$post['cpu'] = $package['cpu'];
		$post['memory'] = $package['memory'];
		$post['system_disk_size'] = $package['system_disk_size'];
		$post['store'] = $package['system_disk_store'];

		if(!empty($package['free_data_disk_size'])){
			$post['other_data_disk'][] = [
				'size'=>$package['free_data_disk_size'],
				'store'=>$package['data_disk_store'] ?: null,
			];
		}
		$post['in_bw'] = $package['in_bw'];
		$post['out_bw'] = $package['out_bw'];
		$post['ip_num'] = $package['ip_num'];
		$post['ip_group'] = $package['ip_group'];
		if($package['traffic_enable'] == 1){
			$post['traffic_quota'] = $package['flow'];

			if($package['traffic_bill_type'] == 'last_30days'){
				$post['reset_flow_day'] = date('j');
			}
		}else{
			$post['traffic_quota'] = 0;
		}
		if($hostLink['backup_num']>0){
			$post['backup_num'] = $hostLink['backup_num'];
		}else{
			$post['backup_num'] = -1;
		}
		if($hostLink['snap_num']>0){
			$post['snap_num'] = $hostLink['snap_num'];
		}else{
			$post['snap_num'] = -1;
		}
		$config = ConfigModel::where('product_id', $params['product']['id'])->find();

		if(!empty($hostLink['data_disk_size'])){
			$data_disk = json_decode($hostLink['data_disk_size'], true);
			foreach($data_disk as $v){
				if(empty($v)){
					continue;
				}
				$post['other_data_disk'][] = [
					'size'=>$v,
					'store'=>$config['disk_store_id'] ?: null,
				];
			}
		}
		$post['network_type'] = 'normal';
		
		// 以镜像方式创建暂时,以后加入其他方式
		$image = ImageModel::find($hostLink['image_id']);
		if($image['charge'] == 1){
			$HostImageLinkModel = new HostImageLinkModel();
			$HostImageLinkModel->saveLink($params['host']['id'], $image['id']);
		}
		if($image['rel_image_id']>0){
			$imageCheck['data']['id'] = $image['rel_image_id'];
		}else{
			$imageCheck = $IdcsmartCloud->getImageId($image['filename']);
			if(!isset($imageCheck['data']['id']) || empty($imageCheck['data']['id'])){
				return ['status'=>400, 'msg'=>lang_plugins('image_not_in_zjmf_cloud')];
			}
		}
		// 是否使用了SSH key
		if(!empty($hostLink['ssh_key_id'])){
			$sshKey = IdcsmartSshKeyModel::find($hostLink['ssh_key_id']);
			if(empty($sshKey)){
				return ['status'=>400, 'msg'=>lang_plugins('ssh_key_not_found')];
			}
			$sshKeyRes = $IdcsmartCloud->sshKeyCreate([
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

		$otherParam = ToolLogic::formatParam($package['custom_param']); // 计算可用参数
		
		// 一些默认值
		$otherParam['type'] = $otherParam['type'] ?? 'host';
		$otherParam['flow_way'] = $otherParam['flow_way'] ?? 'all';

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
		$res = $IdcsmartCloud->cloudCreate($post);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'	=>lang_plugins('host_create_success')
			];

			$update = [];
			$update['rel_id'] = $res['data']['id'];
			$update['power_status'] = 'on';

			// 获取详情同步信息
			$detail = $IdcsmartCloud->cloudDetail($res['data']['id']);
			if($detail['status'] == 200){
				$update['password'] = aes_password_encode($detail['data']['rootpassword']);
				$update['ip'] = $detail['data']['mainip'] ?? '';

				// 当有免费数据盘时记录ID
				if($package['free_data_disk_size'] > 0){
					foreach($detail['data']['disk'] as $v){
						if($v['type'] == 'data' && $v['size'] == $package['free_data_disk_size']){
							$update['free_disk_id'] = $v['id'];
							break;
						}
					}
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
		$IdcsmartCloud = new IdcsmartCloud($params['server']);
		$res = $IdcsmartCloud->cloudSuspend($id);
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
		$IdcsmartCloud = new IdcsmartCloud($params['server']);
		$res = $IdcsmartCloud->cloudUnsuspend($id);
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
		$IdcsmartCloud = new IdcsmartCloud($params['server']);
		$res = $IdcsmartCloud->cloudDelete($id);
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
			return ['status'=>400, 'msg'=>lang_plugins('param_error')];
		}
		if($params['custom']['type'] == 'buy_image'){
			// 购买镜像
			$HostImageLinkModel = new HostImageLinkModel();
			$HostImageLinkModel->saveLink($params['host']['id'], $params['custom']['image_id']);
		}else if($params['custom']['type'] == 'change_package'){
			$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();

			$oldPackage = PackageModel::find($hostLink['package_id']);

			// 变更套餐
			$update = [];
			$update['package_id'] = $params['custom']['package_id'];

			$package = PackageModel::find($params['custom']['package_id']);

			$update['data_center_id'] = $package['data_center_id'] ?? 0;
			
			HostLinkModel::update($update, ['host_id'=>$params['host']['id']]);
			
			$id = $hostLink['rel_id'] ?? 0;
			if(empty($id)){
				$description = '升降级套餐失败,原因:未关联魔方云ID';
				active_log($description, 'host', $params['host']['id']);
				return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
			}
            $IdcsmartCloud = new IdcsmartCloud($params['server']);

            // 套餐中要升级的项
			$post['cpu'] = $package['cpu'];
			$post['memory'] = $package['memory'];

			if($package['traffic_enable'] == 1){
				$post['traffic_quota'] = $package['flow'];
			}else{
				$post['traffic_quota'] = 0;
			}

			$description = [];

			$autoBoot = false;
			if($package['cpu'] != $oldPackage['cpu'] || $package['memory'] != $oldPackage['memory'] || ($oldPackage['free_data_disk_size'] > 0 && $package['free_data_disk_size'] == 0 && !empty($hostLink['free_disk_id']) )){
				$status = $IdcsmartCloud->cloudStatus($id);
				if($status['status'] == 200){
					// 关机
					if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
						$res = $IdcsmartCloud->cloudHardOff($id);
						// 检查任务
						for($i = 0; $i<40; $i++){
							$detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
							if(isset($detail['data']['status']) && $detail['data']['status'] > 1){
								break;
							}
							sleep(10);
						}
						$autoBoot = true;
					}
				}
			}
			// 处理免费磁盘
			if($oldPackage['free_data_disk_size'] > 0 && $package['free_data_disk_size'] > 0){
				if($package['free_data_disk_size'] > $oldPackage['free_data_disk_size'] && $hostLink['free_disk_id'] > 0){
					$res = $IdcsmartCloud->diskModify($id, ['size'=>$package['free_data_disk_size']]);
					if($res['status'] == 200){
						$description[] = "免费数据盘扩容成功";
					}else{
						$description[] = "免费数据盘扩容失败,原因:".$res['msg'];
					}
				}
			}else if($oldPackage['free_data_disk_size'] > 0){
				if(!empty($hostLink['free_disk_id'])){
					// 删除
					$res = $IdcsmartCloud->diskDelete($hostLink['free_disk_id']);
					if($res['status'] == 200){
						$description[] = '删除免费数据盘成功';

						HostLinkModel::update(['free_disk_id'=>0], ['host_id'=>$params['host']['id']]);
					}else{
						$description[] = '删除免费数据盘失败:'.$res['msg'];
					}
				}
			}else if($package['free_data_disk_size'] > 0){
				$storeId = $package['data_disk_store'];
				if(empty($storeId)){
					// 和系统盘一致
					$detail = $IdcsmartCloud->cloudDetail($id);

					if($detail['status'] == 200){
						$storeId = $detail['data']['disk'][0]['store_id'] ?? 0;
					}
				}
				// 新增免费磁盘
				$res = $IdcsmartCloud->addAndMountDisk($id, [
					'size'=>$package['free_data_disk_size'],
					'store'=>$storeId,
					'driver'=>'virtio',
					'cache'=>'writeback',
					'io'=>'native'
				]);
				if($res['status'] == 200){
					HostLinkModel::update(['free_disk_id'=>$res['data']['diskid']], ['host_id'=>$params['host']['id']]);

					$description[] = '添加免费数据盘成功';
				}else{
					$description[] = '添加免费数据盘失败,原因:'.$res['msg'];
				}
			}
			// 修改IP数量
			$res = $IdcsmartCloud->cloudModifyIpNum($id, ['num'=>$package['ip_num'], 'ip_group'=>$package['ip_group']]);
			if($res['status'] != 200){
				$description[] = '套餐IP数量修改失败,原因:'.$res['msg'];
			}else{
				$description[] = '套餐IP数量修改成功';
			}
			$res = $IdcsmartCloud->cloudModifyBw($id, ['in_bw'=>$package['in_bw'], 'out_bw'=>$package['out_bw']]);
			if($res['status'] != 200){
				$description[] = '套餐修改带宽失败,原因:'.$res['msg'];
			}else{
				$description[] = '套餐修改带宽成功';
			}
			$res = $IdcsmartCloud->cloudModify($id, $post);
			if($res['status'] != 200){
				$description[] = '修改配置失败,原因:'.$res['msg'];
			}else{
				$description[] = '修改配置成功';
			}
			if($autoBoot){
				$IdcsmartCloud->cloudOn($id);
			}
			$description = '模块升降级套餐完成,'.implode(',', $description);
			active_log($description, 'host', $params['host']['id']);
		}else if($params['custom']['type'] == 'buy_disk'){
			$ConfigModel = ConfigModel::where('product_id', $params['product']['id'])->find();

			$custom = $params['custom'];

			$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
			$id = $hostLink['rel_id'] ?? 0;

			$IdcsmartCloud = new IdcsmartCloud($params['server']);
			// 这里不用验证了
			$autoBoot = false;

			$delSuccess = [];
			$delFail = [];
			$addSuccess = [];
			$addFail = [];

			$description = [];
			if(!empty($custom['remove_disk_id'])){
				$status = $IdcsmartCloud->cloudStatus($id);
				if($status['status'] == 200){
					// 关机
					if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
						$res = $IdcsmartCloud->cloudHardOff($id);
						// 检查任务
						for($i = 0; $i<40; $i++){
							$detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
							if(isset($detail['data']['status']) && $detail['data']['status'] > 1){
								break;
							}
							sleep(10);
						}
						$autoBoot = true;
					}
				}
				foreach($custom['remove_disk_id'] as $v){
					$deleteRes = $IdcsmartCloud->diskDelete($v);
					if($deleteRes['status'] == 200){
						$delSuccess[] = $v;
					}else{
						$delFail[] = $v.',原因:'.$deleteRes['msg'];
					}
				}
				if(!empty($delSuccess)){
					$description[] = '取消订购成功磁盘:'.implode(',', $delSuccess);
				}
				if(!empty($delFail)){
					$description[] = '取消订购失败磁盘:'.implode(',', $delFail);
				}
			}
			if(!empty($custom['add_disk'])){
				// 查找当前可用存储
				$storeId = $ConfigModel['disk_store_id'];
				if(empty($storeId)){
					// 和系统盘一致
					$detail = $IdcsmartCloud->cloudDetail($id);

					if($detail['status'] == 200){
						$storeId = $detail['data']['disk'][0]['store_id'] ?? 0;
					}
				}
				foreach($custom['add_disk'] as $v){
					$addRes = $IdcsmartCloud->addAndMountDisk($id, [
						'size'=>$v,
						'store'=>$storeId,
						'driver'=>'virtio',
						'cache'=>'writeback',
						'io'=>'native'
					]);
					if($addRes['status'] != 200){
						$addFail[] = $v.',原因:'.$addRes['msg'];
					}else{
						$addSuccess[] = $v;
					}
				}
				if(!empty($addSuccess)){
					$description[] = '订购成功磁盘(GB):'.implode(',', $addSuccess);
				}
				if(!empty($addFail)){
					$description[] = '订购失败磁盘(GB):'.implode(',', $addFail);
				}
			}
			if($autoBoot){
				$IdcsmartCloud->cloudOn($id);
			}
			// 重新获取磁盘列表
			$res = $IdcsmartCloud->cloudDetail($id);
			if($res['status'] == 200 && isset($res['data']['disk'])){
				$disk = $res['data']['disk'];

				$dataDisk = [];
				foreach($disk as $v){
					if($v['type'] == 'data' && $v['id'] != $hostLink['free_disk_id']){
						$dataDisk[] = $v['size'];
					}
				}
				HostLinkModel::update(['data_disk_size'=>json_encode($dataDisk)], ['host_id'=>$params['host']['id']]);
			}
			$description = '模块升降级磁盘订购完成,'.implode(',', $description);
			active_log($description, 'host', $params['host']['id']);
		}else if($params['custom']['type'] == 'resize_disk'){
			$custom = $params['custom'];

			$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
			$id = $hostLink['rel_id'] ?? 0;

			$IdcsmartCloud = new IdcsmartCloud($params['server']);

			// 直接关机扩容
			$autoBoot = false;
			$status = $IdcsmartCloud->cloudStatus($id);
			if($status['status'] == 200){
				// 关机
				if($status['data']['status'] == 'on' || $status['data']['status'] == 'task' || $status['data']['status'] == 'paused'){
					$res = $IdcsmartCloud->cloudHardOff($id);
					// 检查任务
					for($i = 0; $i<40; $i++){
						$detail = $IdcsmartCloud->taskDetail($res['data']['taskid']);
						if(isset($detail['data']['status']) && $detail['data']['status'] > 1){
							break;
						}
						sleep(10);
					}
					$autoBoot = true;
				}
			}

			$success = [];
			$fail = [];
			$description = [];

			foreach($custom['resize_disk'] as $v){
				$resizeRes = $IdcsmartCloud->diskModify($v['id'], ['size'=>$v['size']]);
				if($resizeRes['status'] == 200){
					$success[] = $v['id'];
				}else{
					$fail[] = '磁盘ID:'.$v['id'].',原因:'.$resizeRes['msg'];
				}
			}
			if($autoBoot){
				$IdcsmartCloud->cloudOn($id);
			}
			// 重新获取磁盘列表
			$res = $IdcsmartCloud->cloudDetail($this->id);
			if($res['status'] == 200 && isset($res['data']['disk'])){
				$disk = $res['data']['disk'];

				$dataDisk = [];
				foreach($disk as $v){
					if($v['type'] == 'data' && $v['id'] != $hostLink['free_disk_id']){
						$dataDisk[] = $v['size'];
					}
				}
				HostLinkModel::update(['data_disk_size'=>json_encode($dataDisk)], ['host_id'=>$params['host']['id']]);
			}

			if(!empty($success)){
				$description[] = '磁盘扩容成功ID:'.implode(',', $success);
			}
			if(!empty($fail)){
				$description[] = '扩容失败:'.implode(',', $fail);
			}
			$description = '模块扩容磁盘完成,'.implode(',', $description);
			active_log($description, 'host', $params['host']['id']);
		}else if($params['custom']['type'] == 'modify_backup'){

			$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
			$id = $hostLink['rel_id'] ?? 0;
			$IdcsmartCloud = new IdcsmartCloud($params['server']);

			$update = [ $params['custom']['backup_type'].'_num'=>$params['custom']['num'] ];

			$type = ['backup'=>'备份', 'snap'=>'快照'];

			HostLinkModel::update($update, ['host_id'=>$params['host']['id']]);
			$res = $IdcsmartCloud->cloudModify($hostLink['rel_id'], $update);
			if($res['status'] == 200){
				$description = '模块升降级'.$type[$params['custom']['backup_type']].'数量成功,新数量:'.$params['custom']['num'];
			}else{
				$description = '模块升降级'.$type[$params['custom']['backup_type']].'数量失败,原因:'.$res['msg'];
			}
			active_log($description, 'host', $params['host']['id']);
		}
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

		if($params['scene'] == 'cal_price'){
			if(!$CartValidate->scene('CalPrice')->check($params['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}else{
			if(!$CartValidate->scene('cal')->check($params['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}
        $params['custom']['product_id'] = $params['product']['id'];

		$PackageModel = new PackageModel();

		$res = $PackageModel->cartCalculatePrice($params);
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
			'template'=>'template/admin/common_cloud.html',
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
     * @param   string param.custom.password - 密码 和SSHKEYID一起2个之中必须传一个
     * @param   string param.custom.ssh_key_id - SSHKEYID 和密码一起2个之中必须传一个
	 */
	public function afterSettle($params){
		// 这里还要查询一次
		$package = PackageModel::find($params['custom']['package_id']);
		$config = ConfigModel::where('product_id', $params['product']['id'])->find();

		$data_disk_size = [];
		$backup_num = 0;
		$snap_num = 0;
		if($config['backup_enable'] == 1 && isset($params['custom']['backup_num_id']) && !empty($params['custom']['backup_num_id'])){
			$BackupConfigModel = BackupConfigModel::find($params['custom']['backup_num_id']);
			$backup_num = $BackupConfigModel['num'] ?? 0;
		}
		if($config['snap_enable'] == 1 && isset($params['custom']['snap_num_id']) && !empty($params['custom']['snap_num_id'])){
			$BackupConfigModel = BackupConfigModel::find($params['custom']['snap_num_id']);
			$snap_num = $BackupConfigModel['num'] ?? 0;
		}
		if($config['buy_data_disk'] == 1 && isset($params['custom']['data_disk']) && is_array($params['custom']['data_disk'])){
			$data_disk_size = $params['custom']['data_disk'];
		}
		// 这里不验证了
		$data = [
			'host_id'=>$params['host_id'],
			'data_center_id'=>$params['custom']['data_center_id'] ?? 0,
			'image_id'=>$params['custom']['image_id'],
			'backup_num'=>$backup_num,
			'snap_num'=>$snap_num,
			'package_id'=>$params['custom']['package_id'],
			'package_data'=>json_encode($package->toArray()),
			'create_time'=>time(),
			'data_disk_size'=>json_encode($data_disk_size),
			'password'=>aes_password_encode($param['password'] ?? ''),
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
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($params){
		$PackageModel = new PackageModel();
		$result = $PackageModel->currentDurationPrice($params);
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
	public function currentConfigOption($params){
		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>[],
		];

		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(empty($hostLink)){
			return $result;
		}
		$package = PackageModel::find($hostLink['package_id']);

		$days = $params['host']['billing_cycle_time']/3600/24;

		$duration = [
			'0'=>'onetime_fee',
			'30'=>'month_fee',
			'90'=>'quarter_fee',
			'180'=>'half_year_fee',
			'365'=>'year_fee',
			'730'=>'two_year',
			'1095'=>'three_year',
		];

		// TODO
		// $securityGroupLink = IdcsmartSecurityGroupHostLinkModel::where('host_id', $params['host']['id'])->find();
		// $durationPrice = DurationPriceModel::where('product_id', $params['host']['product_id'])->where('duration', $params['host']['billing_cycle_time']/3600/24)->find();

		$data = [
			'data_center_id' 	=> $package['data_center_id'],
			'package_id'	 	=> $hostLink['package_id'],
			'image_id'		 	=> $hostLink['image_id'],
			'backup_num'		=> $hostLink['backup_num'],
			'snap_num'			=> $hostLink['snap_num'],
			'duration' 			=> $duration[ $days ] ?? '',
		];

		$result['data'] = $data;
		return $result;
	}

	public function getPriceCycle($params)
	{
		$PackageModel = new PackageModel();
		return $PackageModel->getMinPrice($params['product']['id']);
	}

}


