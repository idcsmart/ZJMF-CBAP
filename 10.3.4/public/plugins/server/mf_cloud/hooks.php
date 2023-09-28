<?php

use app\common\model\ProductModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\HostLinkModel;
use think\db\exception\PDOException;

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