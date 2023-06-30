<?php

use app\common\model\ProductModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\HostLinkModel;
use think\db\exception\PDOException;

// 商品保存后预设商品默认值
add_hook('after_product_edit', function($param){
	$ProductModel = ProductModel::find($param['id']);
	if($ProductModel->getModule() != 'mf_cloud'){
		return false;
	}
	$productId = $ProductModel->id;
	$time = time();
	// 预设周期
	$add = DurationModel::value('id');
	if(empty($add)){
		$DurationModel = new DurationModel();
		$DurationModel->insertAll([
			[
				'product_id'    => $productId,
		        'name'          => '月',
		        'num'           => 1,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
			[
				'product_id'    => $productId,
		        'name'          => '季度',
		        'num'           => 3,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
			[
				'product_id'    => $productId,
		        'name'          => '半年',
		        'num'           => 6,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
			[
				'product_id'    => $productId,
		        'name'          => '年',
		        'num'           => 12,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
		]);
	}

});

add_hook('after_product_delete', function($param){
	/*
	try{
		// 加入异常,有可能表不存在
		// $imageId = ImageModel::where('product_id', $param['id'])->column('id');

		// BackupConfigModel::where('product_id', $param['id'])->delete();
		// ConfigModel::where('product_id', $param['id'])->delete();
		// DataCenterModel::where('product_id', $param['id'])->delete();
		// ImageGroupModel::where('product_id', $param['id'])->delete();
		// ImageModel::where('product_id', $param['id'])->delete();
		// PackageModel::where('product_id', $param['id'])->delete();
		
		// if(!empty($imageId)){
		// 	HostImageLinkModel::whereIn('image_id', $imageId)->delete();
		// }
	}catch(\Exception $e){
		
	}
	*/
});

add_hook('after_host_delete', function($param){
	try{
		HostLinkModel::where('host_id', $param['id'])->delete();
	}catch(\Exception $e){
		
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
	        $description = sprintf("流量包购买成功，已成功附加到产品:%s，订单:%s，流量: %d", 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#', '#'.$param['order_id'], $flow.'G');

	        // 如果是流量暂停在检查流量
	        if($moduleParam['host']['status'] == 'Suspended' && $moduleParam['host']['suspend_type'] == 'overtraffic'){
		        if($moduleParam['host']['due_time'] == 0 || time() < $moduleParam['host']['due_time']){
		        	$res = $idcsmartCloud->netInfo($hostLink['rel_id']);
		            if($res['status'] == 'success' && $res['data']['info']['30_day']['float'] < 100){
		                //执行解除暂停
		                $result = $moduleParam['host']->unsuspendAccount($hostId);
		                if ($result['status'] == 200){
	                        $description .= "，购买流量包后 - 解除暂停成功";
	                    }else{
	                        $description .= "，购买流量包后 - 解除暂停失败：{$result['msg']}";
	                    }
		            }
		        }
	        }
	    }else{
	    	$description = sprintf("流量包购买成功，附加到产品失败，请手动添加临时流量:%s，订单:%s，流量: %d", 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#', '#'.$param['order_id'], $flow.'G');
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