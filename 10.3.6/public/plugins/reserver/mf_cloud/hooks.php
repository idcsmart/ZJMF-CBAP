<?php 
use reserver\mf_cloud\logic\RouteLogic;

// 产品转移
add_hook('host_transfer', function($param){
	if(isset($param['res_module']) && $param['res_module'] == 'mf_cloud'){
		$RouteLogic = new RouteLogic();
		$RouteLogic->routeByHost($param['host']['id']);
		$RouteLogic->clientId = $param['target_client']['id'];

		$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d', $RouteLogic->upstream_host_id), [], 'GET');
		if($result['status'] != 200){
			return $result;
		}
		if(isset($result['data']['vpc_network']['id'])){
			// 是VPC,新建VPC并切换为新的VPC
			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/vpc_network', $RouteLogic->upstream_host_id), ['name'=>$result['data']['vpc_network']['name'], 'ips'=>$result['data']['vpc_network']['ips'] ?? '10.0.0.0/16'], 'POST');
			if($result['status'] == 200){
				$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/vpc_network', $RouteLogic->upstream_host_id), ['vpc_network_id'=>$result['data']['id'] ], 'PUT');
			}
			return $result;
		}
	}
});

// 产品转移
add_hook('before_host_transfer', function($param){
	if(isset($param['res_module']) && $param['res_module'] == 'mf_cloud'){
		$RouteLogic = new RouteLogic();
		$RouteLogic->routeByHost($param['host']['id']);
		$RouteLogic->clientId = $param['target_client']['id'];

		$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d', $RouteLogic->upstream_host_id), [], 'GET');
		if($result['status'] != 200){
			return $result;
		}
		if(isset($result['data']['vpc_network']['id'])){
			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/status', $RouteLogic->upstream_host_id), [], 'GET');
			if($result['status'] != 200){
				return $result;
			}
			if($result['data']['status'] == 'operating'){
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_operate_cannot_transfer')];
			}

		}
	}
});