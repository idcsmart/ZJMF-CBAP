<?php 
namespace server\idcsmart_dcim;

use app\common\model\HostModel;
use server\idcsmart_dcim\idcsmart_dcim\Dcim;
use server\idcsmart_dcim\logic\ToolLogic;

use server\idcsmart_dcim\model\PackageModel;
use server\idcsmart_dcim\model\ImageModel;
use server\idcsmart_dcim\model\HostLinkModel;
use server\idcsmart_dcim\model\DataCenterModel;
use server\idcsmart_dcim\model\HostImageLinkModel;

use server\idcsmart_dcim\validate\CartValidate;
use think\facade\Db;

/**
 * 魔方DCIM模块
 */
class IdcsmartDcim{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'魔方DCIM', 'version'=>'1.0.0'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表TODO
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer(){
		$sql = [];
		$sql[] = "CREATE TABLE `idcsmart_module_idcsmart_dcim_data_center` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据中心表';";
		$sql[] = "CREATE TABLE `idcsmart_module_idcsmart_dcim_host_image_link` (
  `host_id` int(10) NOT NULL DEFAULT '0',
  `image_id` int(10) NOT NULL DEFAULT '0',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$sql[] = "CREATE TABLE `idcsmart_module_idcsmart_dcim_host_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT 'DCIMID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `package_id` int(11) NOT NULL DEFAULT '0' COMMENT '当前配置ID',
  `package_data` text NOT NULL COMMENT '当前配置时间数据json',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `port` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `power_status` varchar(20) NOT NULL DEFAULT '',
  `traffic_bill_type` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='host产品关联表';";
		$sql[] = "CREATE TABLE `idcsmart_module_idcsmart_dcim_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_group_id` int(10) NOT NULL DEFAULT '0' COMMENT '镜像分组ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '镜像名称',
  `enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否可用(0=禁用,1=可用)',
  `charge` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否收费(0=不收费,1=收费)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_image_id` int(10) NOT NULL DEFAULT '0' COMMENT 'dcim关联ID',
  PRIMARY KEY (`id`),
  KEY `image_group_id` (`image_group_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像表';";
		$sql[] = "CREATE TABLE `idcsmart_module_idcsmart_dcim_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像分组表';";
		$sql[] = "CREATE TABLE `idcsmart_module_idcsmart_dcim_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `description` text NOT NULL COMMENT '描述',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `dcim_server_group_id` int(10) NOT NULL DEFAULT '0' COMMENT '销售分组ID',
  `in_bw` int(10) NOT NULL DEFAULT '0' COMMENT '进带宽',
  `out_bw` int(10) NOT NULL DEFAULT '0' COMMENT '出带宽',
  `ip_num` int(10) NOT NULL DEFAULT '0' COMMENT 'IP数量',
  `ip_group` int(10) NOT NULL DEFAULT '0' COMMENT 'IP分组ID',
  `custom_param` text NOT NULL COMMENT '自定义参数',
  `traffic_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用流量计费(0=关闭,1=开启)',
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
	 * @title 不用之后删除表TODO
	 * @author hh
	 * @version v1
	 */
	public function afterDeleteLastServer(){
		$sql = [];
		$sql[] = "drop table `idcsmart_module_idcsmart_dcim_data_center`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_dcim_host_image_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_dcim_host_link`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_dcim_image`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_dcim_image_group`;";
		$sql[] = "drop table `idcsmart_module_idcsmart_dcim_package`;";

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
		$Dcim = new Dcim($params['server']);
		$res = $Dcim->login();
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
		$Dcim = new Dcim($params['server']);

		$serverHash = ToolLogic::formatParam($params['server']['hash']);
		$prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面

		// 开通参数
		$post = [];
		$post['user_id'] = $prefix . $params['client']['id'];
		
		// 获取当前配置
		$hostLink = HostLinkModel::where('host_id', $params['host']['id'])->find();
		if(!empty($hostLink) && $hostLink['rel_id'] > 0){
			return ['status'=>400, 'msg'=>lang_plugins('host_already_created')];
		}
		$package = PackageModel::find($hostLink['package_id']);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
		}
		
		$post['server_group'] = $package['dcim_server_group_id'];
		$post['in_bw'] = $package['in_bw'];
		$post['out_bw'] = $package['out_bw'];
		$post['ip_num'] = $package['ip_num'];
		$post['ip_group'] = $package['ip_group'];
		if($package['traffic_enable'] == 1){
			$post['limit_traffic'] = $package['flow'];
		}else{
			$post['limit_traffic'] = 0;
		}

		$image = ImageModel::find($hostLink['image_id']);
		if($image['charge'] == 1){
			$HostImageLinkModel = new HostImageLinkModel();
			$HostImageLinkModel->saveLink($params['host']['id'], $image['id']);
		}
		$post['os'] = $image['rel_image_id'];
		$post['hostid'] = $params['host']['id'];
		
		$otherParam = ToolLogic::formatParam($package['custom_param']); // 计算可用参数
		// 暂时只支持一个随机端口
		if(isset($otherParam['port'])){
			if($otherParam['port'] == 'auto'){
				$post['port'] = mt_rand(100, 65535);
			}else{
				$post['port'] = $otherParam['port'];
			}
		}
		
		$res = $Dcim->create($post);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'	=>lang_plugins('host_create_success')
			];

			$update = [];
			$update['rel_id'] = $res['data']['id'];
			$update['password'] = aes_password_encode($res['data']['password']);
			$update['ip'] = $res['data']['zhuip'] ?? '';
			$update['port'] = $res['data']['port'];
			
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
		$Dcim = new Dcim($params['server']);
		$res = $Dcim->suspend(['id'=>$id, 'hostid'=>$params['host']['id']]);
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
		$Dcim = new Dcim($params['server']);
		$res = $Dcim->unsuspend(['id'=>$id, 'hostid'=>$params['host']['id']]);
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
		$Dcim = new Dcim($params['server']);
		$res = $Dcim->delete(['id'=>$id, 'hostid'=>$params['host']['id']]);
		if($res['status'] == 200){
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

			// 变更套餐
			$update = [];
			$update['package_id'] = $params['custom']['package_id'];

			$oldPackage = PackageModel::find($hostLink['package_id']);
			$package = PackageModel::find($params['custom']['package_id']);

			$update['traffic_bill_type'] = $package['traffic_bill_type'];
			
			HostLinkModel::update($update, ['host_id'=>$params['host']['id']]);
			
			$id = $hostLink['rel_id'] ?? 0;
			if(empty($id)){
				$description = '升降级套餐失败,原因:未关联DCIM ID';
				active_log($description, 'host', $params['host']['id']);
				return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
			}

            $Dcim = new Dcim($params['server']);

            $oldFlow = $oldPackage['traffic_enable'] == 1 ? $oldPackage['flow'] : 0;
            $newFlow = $package['traffic_enable'] == 1 ? $package['flow'] : 0;

            $description = [];
            if($oldFlow != $newFlow){
                $post['id'] = $id;
                $post['traffic'] = $newFlow;

                $res = $Dcim->modifyFlowLimit($post);
                if($res['status'] == 200){
                	$description[] = '修改流量设置成功';
                }else{
                	$description[] = '修改流量设置失败,原因:'.$res['msg'];
                }
            }
            // 直接修改带宽
            $res = $Dcim->modifyInBw(['num'=>$package['in_bw'], 'server_id'=>$id]);
            if($res['status'] == 200){
            	$description[] = '修改进带宽成功';
            }else{
            	$description[] = '修改进带宽失败,原因:'.$res['msg'];
            }
            $res = $Dcim->modifyOutBw(['num'=>$package['out_bw'], 'server_id'=>$id]);
            if($res['status'] == 200){
            	$description[] = '修改出带宽成功';
            }else{
            	$description[] = '修改出带宽失败,原因:'.$res['msg'];
            }
            // IP数量/IP分组不一致时修改
            if($oldPackage['ip_num'] != $package['ip_num'] || $oldPackage['ip_group'] != $package['ip_group']){
            	$post = [];
            	$post['id'] = $id;
            	if($package['ip_group'] > 0){
            		$post['ip_num'][ $package['ip_group'] ] = $package['ip_num'];
            	}else{
            		$post['ip_num'] = $package['ip_num'];
            	}
            	$res = $Dcim->modifyIpNum($post);
            	if($res['status'] == 200){
                    $update = [];
                   	$update['ip'] = $res['data'][0]['ipaddress'] ?? '';
                        
                    HostLinkModel::where('host_id', $params['host']['id'])->update($update);

                    $description[] = '修改IP数量成功';
                }else{
                	$description[] = '修改IP数量失败,原因:'.$res['msg'];
                }
            }
            // 检查当前是否还超额
            if(($oldPackage['traffic_bill_type'] != $package['traffic_bill_type'] || $oldFlow != $newFlow) && $params['host']['status'] == 'Suspended' && $params['host']['suspend_type'] == 'overtraffic'){
            	$post = [];
				$post['id'] = $id;
				$post['hostid'] = $params['host']['id'];
				$post['unit'] = 'GB';

				$flow = $Dcim->flow($post);
				if($flow['status'] == 200){
					$data = $flow['data'][ $package['traffic_bill_type'] ];
					$percent = str_replace('%', '', $data['used_percent']);

					$total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
					$used = round($total * $percent / 100, 2);
					if($percent < 100){
						$unsuspendRes = $params['host']->unsuspendAccount($params['host']['id']);
	        			if($unsuspendRes['status'] == 200){
	        				$descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停成功', $total, $used);
	        			}else{
	        				$descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停失败,原因:%s', $total, $used, $unsuspendRes['msg']);
	        			}
					}
				}
            }
            $description = '模块升降级套餐完成,'.implode(',', $description);
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
			'template'=>'template/admin/dcim_cloud.html',
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
     * @param   string param.custom.password - 密码 require
	 */
	public function afterSettle($params){
		// 这里还要查询一次
		$package = PackageModel::find($params['custom']['package_id']);

		// 这里不验证了
		$data = [
			'host_id'=>$params['host_id'],
			'image_id'=>$params['custom']['image_id'],
			'package_id'=>$params['custom']['package_id'],
			'package_data'=>json_encode($package->toArray()),
			'create_time'=>time(),
			'password'=>aes_password_encode($param['password'] ?? ''),
			'traffic_bill_type'=>$package['traffic_bill_type'] ?? 'month',
		];
		$res = HostLinkModel::where('host_id', $params['host_id'])->find();
		if(empty($res)){
			HostLinkModel::create($data);
		}else{
			HostLinkModel::update($data, ['host_id'=>$params['host_id']]);
		}
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格 TODO
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
	 * @title 获取当前配置项
	 * @desc 获取当前配置项
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

		$data = [
			'package_id'	 	=> $hostLink['package_id'],
			'image_id'		 	=> $hostLink['image_id'],
			'duration' 			=> $duration[ $days ] ?? '',
		];

		$result['data'] = $data;
		return $result;
	}



}


