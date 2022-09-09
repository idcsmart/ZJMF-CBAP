<?php 
namespace server\idcsmart_cloud_disk;

use server\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;
use server\idcsmart_cloud_disk\model\DurationPriceModel;
use server\idcsmart_cloud_disk\model\PackageModel;
use server\idcsmart_cloud_disk\model\HostLinkModel;
use think\facade\Db;
use app\common\model\HostModel;
use server\idcsmart_cloud_disk\validate\CartValidate;
use server\idcsmart_cloud\logic\ToolLogic;
use server\idcsmart_cloud\model\HostLinkModel as HL;

/**
 * 魔方云磁盘模块系统方法
 */
class IdcsmartCloudDisk{


	public function metaData(){
		return ['display_name'=>'魔方云磁盘', 'version'=>'1.0'];
	}

	public function afterCreateFirstServer(){
		// TODO 魔方云磁盘表添加
		$sql = [
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_disk_duration_price`",
			"CREATE TABLE `idcsmart_module_idcsmart_cloud_disk_duration_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '魔方云磁盘周期价格表ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '时长',
  `duration_name` varchar(50) NOT NULL DEFAULT '' COMMENT '时长名称',
  `display_name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `disk_ratio` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '磁盘比例',
  `pay_type` varchar(255) NOT NULL DEFAULT 'recurring_prepayment' COMMENT '付款类型(周期先付recurring_prepayment,周期后付recurring_postpaid',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='魔方云磁盘周期价格表'",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_disk_host_link`",
			"CREATE TABLE `idcsmart_module_idcsmart_cloud_disk_host_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module_idcsmart_cloud_disk_package_id` int(11) NOT NULL DEFAULT '0' COMMENT '套餐ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联云磁盘ID',
  `rel_host_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联实例ID',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '容量,GB',
  `mount_enable` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用自动挂载并格式化',
  `file_system` varchar(20) NOT NULL DEFAULT 'xfs' COMMENT '文件系统,xfs,ext3,ext4',
  `mount_path` varchar(200) NOT NULL DEFAULT '' COMMENT '挂载路径',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0取消挂载 1挂载完成 2挂载中 3创建中 4创建失败 5 回收站',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `module_idcsmart_cloud_disk_package_id` (`module_idcsmart_cloud_disk_package_id`),
  KEY `host_id` (`host_id`),
  KEY `rel_host_id` (`rel_host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='魔方云磁盘模块信息表'",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_disk_package`",
			"CREATE TABLE `idcsmart_module_idcsmart_cloud_disk_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '魔方云磁盘套餐ID',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `module_idcsmart_cloud_data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `size_min` int(11) NOT NULL DEFAULT '0' COMMENT '容量范围最小值,GB',
  `size_max` int(11) NOT NULL DEFAULT '0' COMMENT '容量范围最大值,GB',
  `precision` int(11) NOT NULL DEFAULT '0' COMMENT '最低精度',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='魔方云磁盘套餐表'",
		];

		foreach($sql as $v){
			Db::execute($v);
		}
	}

	public function afterDeleteLastServer(){
		// TODO 删除魔方云表
		$sql = [
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_disk_duration_price`",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_disk_host_link`",
			"DROP TABLE IF EXISTS `idcsmart_module_idcsmart_cloud_disk_package`",
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
		$post['remarks'] = $params['host']['name'];

		// 获取当前配置
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($hostLink['rel_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('产品已开通')];
		}

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

		// 
		$post['size'] = $hostLink['size'];
		$res = $IC->diskCreate($post);
		if($res['status'] == 200){
			$host = HL::where('host_id', $hostLink['rel_host_id'])->find();
	        $post['cloud'] = $host['rel_id'] ?? 0;
	        if(empty($post['cloud'])){
	            // 处理一些东西
				HostLinkModel::where('id', $hostLink->id)->update(['rel_id'=>$res['data']['diskid'], 'status'=>0, 'rel_host_id' => 0]);
	        }

			$res = $IC->diskMount($res['data']['diskid'], $post);
			if($res['status'] == 200){
				// 处理一些东西
				HostLinkModel::where('id', $hostLink->id)->update(['rel_id'=>$res['data']['diskid'], 'status'=>1]);
			}else{
				// 处理一些东西
				HostLinkModel::where('id', $hostLink->id)->update(['rel_id'=>$res['data']['diskid'], 'status'=>0, 'rel_host_id' => 0]);
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
		$res = $IC->cloudUmountDisk($id);
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
		$res = $IC->diskMount($id);
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
		$res = $IC->diskDelete($id);
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
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		$id = $hostLink['rel_id'] ?? 0;
		if(empty($id)){
			return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
		}
		$IC = new IC($params['server']);

		$res = $IC->diskModify($id, ['size' => $params['custom']['size']]);
		if($res['status'] == 200){
			HostLinkModel::where('host_id', $params['host']['id'])->update(['size'=>$params['custom']['size']]);
		}
		return $res;
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
	 * @param   int params.custom.size - 磁盘容量
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
			'template'=>'template/admin/disk_config.html',
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
			'package_id'=>$hostLink['module_idcsmart_cloud_disk_package_id'],
			'size'=>$hostLink['size'],
			'mount_enable'=>$hostLink['mount_enable'],
			'file_system'=>$hostLink['file_system'],
			'mount_path'=>$hostLink['mount_path'],
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
			'module_idcsmart_cloud_disk_package_id'=>$params['custom']['package_id'],
			'rel_host_id' => $params['custom']['host_id'],
			'size'=>$params['custom']['size'],
			'mount_enable'=>$params['custom']['mount_enable'],
			'file_system'=>$params['custom']['file_system'],
			'mount_path'=>$params['custom']['mount_path'],
			'status'=>3
		];
		HostLinkModel::create($data);
		// 覆盖自动生成的name
		if(!empty($params['custom']['hostname'])){
			HostModel::where('id', $params['host_id'])->update(['name'=>$params['custom']['hostname']]);
		}
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

		$durationPrice = DurationPriceModel::where('product_id', $params['host']['product_id'])->where('duration', $params['host']['billing_cycle_time']/3600/24)->find();

		$data = [
			'hostname'			=> $params['host']['name'] ?? '',
			'size'				=> $hostLink['size'],
			'mount_enable'		=> $hostLink['mount_enable'],
			'file_system'		=> $hostLink['file_system'],
			'mount_path'		=> $hostLink['mount_path'],
			'duration_price_id' => $durationPrice['id'] ?? 0,
		];

		$result['data'] = $data;
		return $result;
	}

}


