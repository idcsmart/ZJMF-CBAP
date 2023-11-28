<?php

use app\common\model\ProductModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\HostLinkModel;
use think\db\exception\PDOException;
use server\mf_cloud\model\BackupConfigModel;
use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\model\DiskLimitModel;
use server\mf_cloud\model\ImageGroupModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\LineModel;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\PriceModel;
use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\model\RecommendConfigUpgradeRangeModel;
use server\mf_cloud\model\ResourcePackageModel;
use server\mf_cloud\model\ConfigLimitModel;
use server\mf_cloud\model\DurationRatioModel;
use server\mf_cloud\model\VpcNetworkModel;

add_hook('after_product_delete', function($param){
	if(!isset($param['module']) || $param['module'] != 'mf_cloud'){
		return false;
	}
	try{
		$dataCenterId = DataCenterModel::where('product_id', $param['id'])->column('id');
		$recommendConfigId = RecommendConfigModel::where('product_id', $param['id'])->column('id');

		BackupConfigModel::where('product_id', $param['id'])->delete();
		ConfigModel::where('product_id', $param['id'])->delete();
		ConfigLimitModel::where('product_id', $param['id'])->delete();
		DataCenterModel::where('product_id', $param['id'])->delete();
		DiskLimitModel::where('product_id', $param['id'])->delete();
		DurationModel::where('product_id', $param['id'])->delete();
		ImageModel::where('product_id', $param['id'])->delete();
		ImageGroupModel::where('product_id', $param['id'])->delete();
		if(!empty($dataCenterId)){
			LineModel::whereIn('data_center_id', $dataCenterId)->delete();
		}
		OptionModel::where('product_id', $param['id'])->delete();
		PriceModel::where('product_id', $param['id'])->delete();
		RecommendConfigModel::where('product_id', $param['id'])->delete();
		VpcNetworkModel::where('product_id', $param['id'])->delete();
		ResourcePackageModel::where('product_id', $param['id'])->delete();
		if(!empty($recommendConfigId)){
			RecommendConfigUpgradeRangeModel::whereIn('recommend_config_id', $recommendConfigId)->delete();
			RecommendConfigUpgradeRangeModel::whereIn('rel_recommend_config_id', $recommendConfigId)->delete();
		}
		DurationRatioModel::where('product_id', $param['id'])->delete();
	}catch(\PDOException $e){
		
	}catch(\Exception $e){

	}
});

add_hook('after_host_delete', function($param){
	try{
		HostLinkModel::where('host_id', $param['id'])->delete();
	}catch(\Exception $e){
		
	}
});

//商品复制后
add_hook('after_product_copy', function($param){
	try{
		$DurationModel = new DurationModel();
		$duration = $DurationModel->where('product_id', $param['product_id'])->select()->toArray();
		if(!empty($duration)){
			$durationIdArr = [];
			foreach ($duration as $key => $value) {
				$id = $value['id'];
				$durationIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $DurationModel->create($value);
				$durationIdArr[$id] = $r->id;
			}

			$DataCenterModel = new DataCenterModel();
			$dataCenter = $DataCenterModel->where('product_id', $param['product_id'])->select()->toArray();
			$dataCenterIdArr = [];
			foreach ($dataCenter as $key => $value) {
				$id = $value['id'];
				$dataCenterIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $DataCenterModel->create($value);
				$dataCenterIdArr[$id] = $r->id;
			}

			$ConfigModel = new ConfigModel();
			$config = $ConfigModel->where('product_id', $param['product_id'])->select()->toArray();
			$configIdArr = [];
			foreach ($config as $key => $value) {
				$id = $value['id'];
				$configIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ConfigModel->create($value);
				$configIdArr[$id] = $r->id;
			}

			$DiskLimitModel = new DiskLimitModel();
			$diskLimit = $DiskLimitModel->where('product_id', $param['product_id'])->select()->toArray();
			$diskLimitIdArr = [];
			foreach ($diskLimit as $key => $value) {
				$id = $value['id'];
				$diskLimitIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $DiskLimitModel->create($value);
				$diskLimitIdArr[$id] = $r->id;
			}

			$BackupConfigModel = new BackupConfigModel();
			$backupConfig = $BackupConfigModel->where('product_id', $param['product_id'])->select()->toArray();
			$backupConfigIdArr = [];
			foreach ($backupConfig as $key => $value) {
				$id = $value['id'];
				$backupConfigIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $BackupConfigModel->create($value);
				$backupConfigIdArr[$id] = $r->id;
			}

			$ImageGroupModel = new ImageGroupModel();
			$imageGroup = $ImageGroupModel->where('product_id', $param['product_id'])->select()->toArray();
			$imageGroupIdArr = [];
			foreach ($imageGroup as $key => $value) {
				$id = $value['id'];
				$imageGroupIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ImageGroupModel->create($value);
				$imageGroupIdArr[$id] = $r->id;
			}

			$ImageModel = new ImageModel();
			$image = $ImageModel->where('product_id', $param['product_id'])->select()->toArray();
			$imageIdArr = [];
			foreach ($image as $key => $value) {
				$id = $value['id'];
				$imageIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$value['image_group_id'] = $imageGroupIdArr[$value['image_group_id']] ?? 0;
				$r = $ImageModel->create($value);
				$imageIdArr[$id] = $r->id;
			}

			$LineModel = new LineModel();
			$line = $LineModel->whereIn('data_center_id', array_keys($dataCenterIdArr))->select()->toArray();
			$lineIdArr = [];
			foreach ($line as $key => $value) {
				$id = $value['id'];
				$lineIdArr[$id] = 0;
				unset($value['id']);
				$value['data_center_id'] = $dataCenterIdArr[$value['data_center_id']] ?? 0;
				$r = $LineModel->create($value);
				$lineIdArr[$id] = $r->id;
			}

			$OptionModel = new OptionModel();
			$option = $OptionModel->where('product_id', $param['product_id'])->select()->toArray();
			$optionIdArr = [];
			foreach ($option as $key => $value) {
				$id = $value['id'];
				$optionIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				if(in_array($value['rel_type'], [2, 3, 4, 5])){
					$value['rel_id'] = $lineIdArr[$value['rel_id']] ?? 0;
				}
				$r = $OptionModel->create($value);
				$optionIdArr[$id] = $r->id;
			}

			$RecommendConfigModel = new RecommendConfigModel();
			$recommendConfig = $RecommendConfigModel->where('product_id', $param['product_id'])->select()->toArray();
			$recommendConfigIdArr = [];
			foreach ($recommendConfig as $key => $value) {
				$id = $value['id'];
				$recommendConfigIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$value['data_center_id'] = $dataCenterIdArr[$value['data_center_id']] ?? 0;
				$value['line_id'] = $lineIdArr[$value['line_id']] ?? 0;
				$r = $RecommendConfigModel->create($value);
				$recommendConfigIdArr[$id] = $r->id;
			}

			$RecommendConfigUpgradeRangeModel = new RecommendConfigUpgradeRangeModel();
			$recommendConfigUpgrade = $RecommendConfigUpgradeRangeModel->whereIn('recommend_config_id', array_keys($recommendConfigIdArr))->select()->toArray();
			$recommendConfigUpgradeIdArr = [];
			foreach ($recommendConfigUpgrade as $key => $value) {
				$value['recommend_config_id'] = $recommendConfigIdArr[$value['recommend_config_id']] ?? 0;
				$value['rel_recommend_config_id'] = $recommendConfigIdArr[$value['rel_recommend_config_id']] ?? 0;
				$RecommendConfigUpgradeRangeModel->insert($value);
			}

			$PriceModel = new PriceModel();
			$price = $PriceModel->where('product_id', $param['product_id'])->select()->toArray();
			$priceIdArr = [];
			foreach ($price as $key => $value) {
				$id = $value['id'];
				$priceIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				if($value['rel_type'] == 0){
					$value['rel_id'] = $optionIdArr[$value['rel_id']] ?? 0;
				}else if($value['rel_type'] == 1){
					$value['rel_id'] = $recommendConfigIdArr[$value['rel_id']] ?? 0;
				}else{
					continue;
				}
				$value['duration_id'] = $durationIdArr[$value['duration_id']] ?? 0;
				$r = $PriceModel->create($value);
				$priceIdArr[$id] = $r->id;
			}

			$ResourcePackageModel = new ResourcePackageModel();
			$resourcePackage = $ResourcePackageModel->where('product_id', $param['product_id'])->select()->toArray();
			$resourcePackageIdArr = [];
			foreach ($resourcePackage as $key => $value) {
				$id = $value['id'];
				$resourcePackageIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ResourcePackageModel->create($value);
				$resourcePackageIdArr[$id] = $r->id;
			}
			
			$ConfigLimitModel = new ConfigLimitModel();
			$configLimit = $ConfigLimitModel->where('product_id', $param['product_id'])->select()->toArray();
			$configLimitIdArr = [];
			foreach ($configLimit as $key => $value) {
				$id = $value['id'];
				$configLimitIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$value['data_center_id'] = $dataCenterIdArr[$value['data_center_id']] ?? 0;
				$value['line_id'] = $lineIdArr[$value['line_id']] ?? 0;
				$r = $ConfigLimitModel->create($value);
				$configLimitIdArr[$id] = $r->id;
			}

			// 获取周期比例
			$DurationRatioModel = new DurationRatioModel();
			$durationRatio = $DurationRatioModel->where('product_id', $param['product_id'])->select()->toArray();
			foreach ($durationRatio as $key => $value) {
				if(isset($durationIdArr[$value['duration_id']])){
					$value['product_id'] = $param['id'];
					$value['duration_id'] = $durationIdArr[$value['duration_id']];
					$DurationRatioModel->create($value);
				}
			}
		}
	}catch(\Exception $e){
		return $e->getMessage();
	}
});

// 购买流量包后
add_hook('flow_packet_order_paid', function($param){
	$hostId = $param['host_id'];
	$flow = $param['flow_packet']['capacity'];
	$moduleParam = $param['module_param'];

	if(!empty($moduleParam['server']) && $moduleParam['server']['module'] == 'mf_cloud'){
		$hash = \server\mf_cloud\logic\ToolLogic::formatParam($moduleParam['server']['hash']);

		$idcsmartCloud = new \server\mf_cloud\idcsmart_cloud\IdcsmartCloud($moduleParam['server']);
		$idcsmartCloud->setIsAgent(isset($hash['account_type']) && $hash['account_type'] == 'agent');

		$hostLink = HostLinkModel::where('host_id', $hostId)->find();
		$res = $idcsmartCloud->cloudIncTempTraffic($hostLink['rel_id'] ?? 0, (int)$flow);
		if($res['status'] == 200){
			$description = lang_plugins('log_mf_cloud_buy_flow_packet_success', [
				'{host}'	=> 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#',
				'{order}' 	=> '#'.$param['order_id'],
				'{flow}' 	=> $flow.'G',
			]);

	        // 如果是流量暂停在检查流量
	        if($moduleParam['host']['status'] == 'Suspended' && $moduleParam['host']['suspend_type'] == 'overtraffic'){
		        if($moduleParam['host']['due_time'] == 0 || time() < $moduleParam['host']['due_time']){
		        	$res = $idcsmartCloud->netInfo($hostLink['rel_id']);
		            if($res['status'] == 'success' && $res['data']['info']['30_day']['float'] < 100){
		                //执行解除暂停
		                $result = $moduleParam['host']->unsuspendAccount($hostId);
		                if ($result['status'] == 200){
	                        $description .= lang_plugins('log_mf_cloud_buy_flow_packet_and_unsuspend_success');
	                    }else{
	                        $description .= lang_plugins('log_mf_cloud_buy_flow_packet_but_unsuspend_fail', ['{reason}'=>$result['msg']]);
	                    }
		            }
		        }
	        }
	    }else{
	    	$description = lang_plugins('log_mf_cloud_buy_flow_packet_remote_add_fail', [
	    		'{host}'	=> 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#',
	    		'{order}'	=> '#'.$param['order_id'],
	    		'{flow}'	=> $flow.'G',
	    	]);
	    }
	    // 记录日志
	    active_log($description, 'host', $hostId);
	}
});

// 在购买流量包之前
add_hook('flow_packet_before_order', function($param){
	try{
		$hostLink = hostLinkModel::where('host_id', $param['host']['id'])->find();
		if(!empty($hostLink)){
			$configData = json_decode($hostLink['config_data'], true);
			if(isset($configData['line']['bill_type']) && $configData['line']['bill_type'] !== 'flow'){
				// 不是流量线路,不能购买
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_buy_flow_packet')];
			}
		}
	}catch(PDOException $e){

	}
});

// 获取产品转移信息
add_hook('host_transfer_info', function($param){
	if($param['module'] == 'mf_cloud'){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->hostTransferInfo($param);
	}
});

// 产品转移
add_hook('host_transfer', function($param){
	if($param['module'] == 'mf_cloud'){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->hostTransfer($param);
	}
});

// 在产品转移之前
add_hook('before_host_transfer', function($param){
	if($param['module'] == 'mf_cloud'){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->beforeHostTransfer($param);
	}
});
