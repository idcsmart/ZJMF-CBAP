<?php 
namespace server\idcsmart_cloud_ip;

use server\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use server\idcsmart_cloud_ip\model\DurationPriceModel;
use server\idcsmart_cloud_ip\model\PackageModel;
use server\idcsmart_cloud_ip\model\HostLinkModel;
use think\facade\Db;
use app\common\model\HostModel;
use server\idcsmart_cloud_ip\validate\CartValidate;
use server\idcsmart_cloud\logic\ToolLogic;
use server\idcsmart_cloud\model\HostLinkModel as HL;

/**
 * 魔方云IP模块系统方法
 */
class IdcsmartCloudIp{


	public function metaData(){
		return ['display_name'=>'魔方云IP', 'version'=>'1.0'];
	}

	public function afterCreateFirstServer(){
		// TODO 魔方云IP表添加
		$sql = [
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_ip_duration_price`",
			"CREATE TABLE `idcsmart_module_idcsmart_cloud_ip_duration_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '魔方云IP周期价格表ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '时长',
  `duration_name` varchar(50) NOT NULL DEFAULT '' COMMENT '时长名称',
  `display_name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `ip_ratio` float(4,2) NOT NULL DEFAULT '1.00' COMMENT 'IP比例',
  `bw_ratio` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '带宽比例',
  `pay_type` varchar(255) NOT NULL DEFAULT 'recurring_prepayment' COMMENT '付款类型(周期先付recurring_prepayment,周期后付recurring_postpaid',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云IP周期价格表'",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_ip_host_link`",
			"CREATE TABLE `idcsmart_module_idcsmart_cloud_ip_host_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module_idcsmart_cloud_ip_package_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联云IPID',
  `rel_bw_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联云带宽ID',
  `rel_host_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联实例ID',
  `bw_type` varchar(20) NOT NULL DEFAULT 'default' COMMENT '带宽类型:default默认,independ独立',
  `bw_size` int(11) NOT NULL DEFAULT '0' COMMENT '带宽大小',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0取消挂载 1挂载完成 2挂载中 3创建中 4创建失败 5 回收站',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `module_idcsmart_cloud_ip_package_id` (`module_idcsmart_cloud_ip_package_id`),
  KEY `host_id` (`host_id`),
  KEY `rel_bw_id` (`rel_bw_id`),
  KEY `rel_host_id` (`rel_host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='魔方云IP模块信息表'",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_ip_package`",
			"CREATE TABLE `idcsmart_module_idcsmart_cloud_ip_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '魔方云IP套餐ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `module_idcsmart_cloud_bw_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云带宽ID',
  `ip_enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用附加IP,0否1是',
  `ip_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'IP价格',
  `ip_max` int(11) NOT NULL DEFAULT '0' COMMENT '单个实例上限',
  `bw_enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用独立带宽,0否1是',
  `bw_precision` int(11) NOT NULL DEFAULT '0' COMMENT '带宽最低精度',
  `bw_price` text NOT NULL COMMENT '带宽价格',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_id` (`product_id`),
  KEY `module_idcsmart_cloud_bw_id` (`module_idcsmart_cloud_bw_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='魔方云IP套餐表'",
		];

		foreach($sql as $v){
			Db::execute($v);
		}
	}

	public function afterDeleteLastServer(){
		// TODO 删除魔方云表
		$sql = [
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_ip_duration_price`",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_ip_host_link`",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_ip_package`",
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-22
	 * @title 测试连接
	 * @author theworld
	 * @version v1
	 */
	public function testConnect($params){
		$IC = new IC($params['server']);
		$res = $IC->login(false, true);
		if($res['status'] == 200){
			unset($res['data']);
			$res['msg'] = '连接成功';
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

		// 开通参数
		$post = [];

		$serverHash = ToolLogic::formatParam($params['server']['hash']);

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
		$post['uid'] = $userCheck['data']['id'];

		// 获取当前配置
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($hostLink['rel_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('产品已开通')];
		}

		$host = HL::where('host_id', $hostLink['rel_host_id'])->find();
        $id = $host['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('关联实例未开通')];
        }

        $detail = $IC->cloudDetail($id);
        if($detail['status'] != 200){
			return ['status'=>400, 'msg'=>$detail['msg'] ?: lang_plugins('产品开通失败')];
		}

		// 
		$post['node'] = $detail['data']['node_id'];
		$post['num'] = 1;
		$res = $IC->elasticIpCreate($post);
		if($res['status'] == 200){
			HostLinkModel::where('id', $hostLink['id'])->update(['rel_id'=>$res['data']['ip'][0]['id'], 'ip' => $res['data']['ip'][0]['ip']]);
			$post = ['product_type' => 'host', 'rel_id' => $id];
			$res = $IC->elasticIpDetach($res['data']['ip'][0]['id'], $post);
			if($res['status'] == 200){
				// 处理一些东西
				HostLinkModel::where('id', $hostLink['id'])->update(['status'=>1]);
			}else{
				// 处理一些东西
				HostLinkModel::where('id', $hostLink['id'])->update(['status'=>0, 'rel_host_id'=>0]);
			}
			
		}
		return $res;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块暂停
	 * @author theworld
	 * @version v1
	 */
	public function suspendAccount($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>'未填写魔方云ID'];
		}
		$IC = new IC($params['server']);
		$res = $IC->elasticIpDetach($id);
		return $res;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author theworld
	 * @version v1
	 */
	public function unsuspendAccount($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>'未填写魔方云ID'];
		}
		$IC = new IC($params['server']);
		$res = $IC->elasticIpAttach($id);
		return $res;
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author theworld
	 * @version v1
	 */
	public function terminateAccount($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>'未填写魔方云ID'];
		}
		$IC = new IC($params['server']);
		$res = $IC->elasticIpDelete($id);
		if($res['status'] == 200){
			HostLinkModel::where('host_id', $params['host']['id'])->update(['host_id'=>0]);
		}
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($params){
		// 解除暂停?
		return $this->unsuspendAccount($params);
	}

	/**
	 * 时间 2022-06-28
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 * @param   string x       -             x
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function changePackage($params){
		return ['status'=>200];
	}

	/**
	 * 时间 2022-06-28
	 * @title 变更商品后调用
	 * @author hh
	 * @version v1
	 */
	public function changeProduct($params){
		// TODO 保存新的配置项
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
	 * @param   int params.custom.duration_price_id - 周期价格ID require
	 * @param   int params.custom.package_id - 套餐ID require
	 * @param   int params.custom.size - 带宽大小
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
			'template'=>'template/admin/network_config.html',
		];
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 商品保存时调用
	 * @author hh
	 * @version v1
	 */
	//public function productSave($params){}

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
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($params){
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(empty($hostLink)){
			return ['status'=>400, 'msg'=>lang_plugins('host_not_found')];
		}

		$data = [
			'package_id'=>$hostLink['module_idcsmart_cloud_ip_package_id'],
			'size'=>$hostLink['size'],
		];

		$DurationPriceModel = new DurationPriceModel();
		$result = $DurationPriceModel->configDurationPrice($data);
		return $result;
	}


	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author theworld
	 * @version v1
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function afterSettle($params){
		// 这里不验证了
		$data = [
			'host_id'=>$params['host_id'],
			'module_idcsmart_cloud_ip_package_id'=>$params['custom']['package_id'],
			'rel_bw_id' => $params['custom']['bw_id'],
			'rel_host_id' => $params['custom']['host_id'],
			'bw_type'=>$params['custom']['type'],
			'bw_size'=>$params['custom']['size'],
			'status'=>3
		];
		HostLinkModel::create($data);
		// 覆盖自动生成的name
		if(!empty($params['custom']['hostname'])){
			HostModel::where('id', $params['host_id'])->update(['name'=>$params['custom']['hostname']]);
		}
	}

	


}


