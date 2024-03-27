<?php

use app\common\model\HostModel;
#function nokvm_idcsmartauthorize(){}

// 配置数据
function nokvm_MetaData(){
	return ['DisplayName'=>'NOKVM', 'APIVersion'=>'1.1', 'HelpDoc'=>'https://www.idcsmart.com/wiki_list/339.html#3.3','version'=>'1.0.0'];
}

function nokvm_ConfigOptions(){
	return [
		[
			'type'=>'text', 
			'name'=>'数据中心', 
			'description'=>'所属数据中心位置',
			'key'=>'Location'
		],
		[
			'type'=>'text', 
			'name'=>'节点ID', 
			'description'=>'ID(不填则自动分配)',
			'key'=>'nodes_id',
		],
		[
			'type'=>'text', 
			'name'=>'内存', 
			'description'=>'MB',
			'default'=>'1024',
			'key'=>'Memory',
		],
		[
			'type'=>'text', 
			'name'=>'上行带宽', 
			'description'=>'KB',
			'default'=>'256',
			'key'=>'net_out',
		],
		[
			'type'=>'text', 
			'name'=>'流量', 
			'description'=>'GB(0为不限制)',
			'default'=>'0',
			'key'=>'flow_limit',
		],
		[
			'type'=>'text', 
			'name'=>'备份数量', 
			'description'=>'个',
			'default'=>'2',
			'key'=>'Backups',
		],
		[
			'type'=>'text', 
			'name'=>'端口转发数', 
			'description'=>'条',
			'default'=>'2',
			'key'=>'nat_acl_limit',
		],
		[
			'type'=>'text', 
			'name'=>'IP数量', 
			'description'=>'默认分配公网IP数量',
			'default'=>'1',
			'key'=>'Extra IP Address',
		],
		[
			'type'=>'text', 
			'name'=>'可用镜像ID', 
			'description'=>'ID(用"|"进行分割)',
			'key'=>'',
		],
		[
			'type'=>'text', 
			'name'=>'默认镜像', 
			'description'=>'如果设置可选配置则优先可选配置',
			'key'=>'os',
		],
		[
			'type'=>'text', 
			'name'=>'核心数', 
			'description'=>'核心',
			'default'=>'2',
			'key'=>'CPU',
		],
		[
			'type'=>'text', 
			'name'=>'硬盘大小', 
			'description'=>'GB',
			'default'=>'20',
			'key'=>'Disk Space',
		],
		[
			'type'=>'text', 
			'name'=>'下行带宽', 
			'description'=>'KB',
			'default'=>'256',
			'key'=>'net_in',
		],
		[
			'type'=>'text', 
			'name'=>'快照数量', 
			'description'=>'个',
			'default'=>'2',
			'key'=>'Snapshot',
		],
		[
			'type'=>'text', 
			'name'=>'CPU模式', 
			'description'=>'是否显示CPU型号', 
			// 'options'=>[
			// 	'0'=>'显示CPU型号', 
			// 	'1'=>'隐藏CPU型号'
			// ], 
			'default'=>'1',
			'key'=>'cpu_mode',
		],
		[
			'type'=>'yesno', 
			'name'=>'NAT产品', 
			'description'=>'勾选则表示为NAT产品', 
			'default'=>'1',
			'key'=>'nat',
		],
		[
			'type'=>'text', 
			'name'=>'NAT建站数', 
			'description'=>'条', 
			'default'=>'1',
			'key'=>'nat_web_limit',
		],
	];
}

// 连接测试
function nokvm_TestLink($params){
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/area', $sign);
	
	$res = nokvm_Curl($url, [], 10, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		$result['status'] = 200;
		$result['data']['server_status'] = 1;
	}else{
		$result['status'] = 200;
		$result['data']['server_status'] = 0;
		$result['data']['msg'] = $res['message'];
	}
	return $result;
}

// 图表
function nokvm_Chart(){
	return [
		'cpu'=>[
			'title'=>'CPU',
		],
		'disk'=>[
			'title'=>'磁盘IO',
			'select'=>[
				[
					'name'=>'系统盘',
					'value'=>'vda'
				],
				[
					'name'=>'数据盘',
					'value'=>'vdb'
				],
			]
		],
		'flow'=>[
			'title'=>'流量图'
		],
	];
}

// 图表数据
function nokvm_ChartData($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return ['status'=>'error', 'msg'=>'数据获取失败'];
	}

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_monitoring/'.$vserverid, $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	// var_dump($res);
	if(isset($res['code']) && $res['code'] == 0){

		$start = $params['chart']['start'];
		
		$result['status'] = 'success';
		$result['data'] = [];
		if($params['chart']['type'] == 'cpu'){
			
			$result['data']['unit'] = '%';
			$result['data']['chart_type'] = 'line';
			$result['data']['list'] = [];
			$result['data']['label'] = ['CPU使用率(%)'];

			foreach($res['data'] as $v){
				$v['Cpu'] = json_decode($v['Cpu'], true);
				$time = strtotime($v['CreatedAt']);
				if($time < $start){
					continue;
				}
				$result['data']['list'][0][] = [
					'time'=>date('Y-m-d H:i:s', $time),
					'value'=>$v['Cpu']['value']
				]; 
			}
		}else if($params['chart']['type'] == 'disk'){
			
			$result['data']['unit'] = 'kb/s';
			$result['data']['chart_type'] = 'line';
			$result['data']['list'] = [];
			$result['data']['label'] = ['读取速度(kb/s)','写入速度(kb/s)'];

			foreach($res['data'] as $v){
				$v['Disk'] = json_decode($v['Disk'], true);

				$time = strtotime($v['CreatedAt']);
				if($time < $start){
					continue;
				}
				$date = date('Y-m-d H:i:s', $time);
				$result['data']['list'][0][] = [
					'time'=>$date,
					'value'=>$v['Disk'][$params['chart']['select']][0]
				];
				$result['data']['list'][1][] = [
					'time'=>$date,
					'value'=>$v['Disk'][$params['chart']['select']][1]
				];
			}				
		}else if($params['chart']['type'] == 'flow'){

			$result['data']['unit'] = 'KB/s';
			$result['data']['chart_type'] = 'area';
			$result['data']['list'] = [];
			$result['data']['label'] = ['进(KB/s)','出(KB/s)'];

			foreach($res['data'] as $v){
				$v['Network'] = json_decode($v['Network'], true);
				$time = strtotime($v['CreatedAt']);
				if($time < $start){
					continue;
				}
				$date = date('Y-m-d H:i:s', $time);
				$result['data']['list'][0][] = [
					'time'=>$date,
					'value'=>$v['Network']['in']
				];
				$result['data']['list'][1][] = [
					'time'=>$date,
					'value'=>$v['Network']['out']
				];
			}
		}
		return $result;
	}else{
		return ['status'=>'error', 'msg'=>'数据获取失败'];
	}
}

// 标准输出
function nokvm_ClientArea($params){
	if($params['configoptions']['nat']==1){
		$panel = [
			'snapshot'=>[
				'name'=>'快照',
			],
			'security_group'=>[
				'name'=>'策略',
			],
			'backups'=>[
				'name'=>'备份',
			],
			'cd_rom'=>[
				'name'=>'光驱',
			],
			/*'nat_acl'=>[
				'name'=>'NAT转发',
			],
			'nat_web'=>[
				'name'=>'NAT建站',
			]*/
		];
	}else{
		$panel = [
			'snapshot'=>[
				'name'=>'快照',
			],
			'security_group'=>[
				'name'=>'策略',
			],
			'backups'=>[
				'name'=>'备份',
			],
			'cd_rom'=>[
				'name'=>'光驱',
			]
		];
	}
	if(empty($params['configoptions']['nat_acl_limit'])){
		unset($panel['nat_acl']);
	}
	if(empty($params['configoptions']['nat_web_limit'])){
		unset($panel['nat_web']);
	}
	if(empty($params['configoptions']['Snapshot'])){
		unset($panel['snapshot']);
	}
	if(empty($params['configoptions']['Backups'])){
		unset($panel['backups']);
	}
	return $panel;
}

// 输出内容
function nokvm_ClientAreaOutput($params, $key){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return '';
	}
	if($key == 'snapshot'){
		// 获取快照列表
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/snapshot/'.$vserverid, $sign);
		
		$res = nokvm_Curl($url, [], 30, 'GET');
		foreach($res['data'] AS $k=>$v){
			$res['data'][$k]['created_at'] = date("Y-m-d H:i:s",strtotime($v['created_at']));
		}
		return [
			'template'=>'templates/snapshot.html',
			'vars'=>[
				'list'=>$res['data']
			]
		];
	}else if($key == 'security_group'){
		// 获取安全组列表
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/security_group/'.$vserverid.'/edit', $sign);
		
		$used = nokvm_Curl($url, [], 30, 'GET');

		$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);
		
		$virtual = nokvm_Curl($url, [], 30, 'GET');

		$url = nokvm_GetUrl($params, '/api/security_group/'.$virtual['data']['users_id'], $sign);
		
		$res = nokvm_Curl($url, [], 30, 'GET');
		
		return [
			'template'=>'templates/security_group.html',
			'vars'=>[
				'list'=>$res['data'],
				'used'=>$used['data'][0]['id'],
			]
		];
	}else if($key == 'backups'){
		// 获取安全组列表
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/backups/'.$vserverid, $sign);
		
		$res = nokvm_Curl($url, [], 30, 'GET');
		foreach($res['data'] AS $k=>$v){
			$res['data'][$k]['created_at'] = date("Y-m-d H:i:s",strtotime($v['created_at']));
		}
		return [
			'template'=>'templates/backups.html',
			'vars'=>[
				'list'=>$res['data']
			]
		];
	}else if($key == 'cd_rom'){
		// 获取安全组列表
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);
		
		$host = nokvm_Curl($url, [], 30, 'GET');

		$url = nokvm_GetUrl($params, '/api/cd_rom/', $sign);
		
		$res = nokvm_Curl($url, [], 30, 'GET');

		return [
			'template'=>'templates/cd_rom.html',
			'vars'=>[
				'list'=>$res['data'],
				'host'=>$host['data']
			]
		];
	}else if($key == 'nat_acl'){
		// 获取策略列表
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/nat_acl/'.$vserverid, $sign);
		
		$res = nokvm_Curl($url, [], 30, 'GET');
		return [
			'template'=>'templates/nat_acl.html',
			'vars'=>[
				'list'=>$res['data']
			]
		];
	}else if($key == 'nat_web'){
		// 获取网站列表
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/nat_web/'.$vserverid, $sign);
		
		$res = nokvm_Curl($url, [], 30, 'GET');
		return [
			'template'=>'templates/nat_web.html',
			'vars'=>[
				'list'=>$res['data']
			]
		];
	}
}

// 可以执行自定义方法
function nokvm_AllowFunction(){
	return [
		'client'=>['CreateSnap','DeleteSnap','RestoreSnap','CreateBackup','DeleteBackup','RestoreBackup','CreateSecurityGroup','DeleteSecurityGroup','ApplySecurityGroup','ShowSecurityGroupAcl','CreateSecurityGroupAcl','DeleteSecurityGroupAcl','MountCdRom','UnmountCdRom','addNatAcl','delNatAcl','addNatWeb','delNatWeb'],
	];
}

// 创建转发
function nokvm_addNatAcl($params){
	if($params['configoptions']['nat']!=1){
		return '该产品不是NAT产品,不支持创建转发';
	}
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 创建转发
	$sign = nokvm_CreateSign($params['server_password']);

	$url = nokvm_GetUrl($params, '/api/nat_acl/'.$vserverid, $sign);
	$res = nokvm_Curl($url, [], 30, 'GET');

	if($params['configoptions']['nat_acl_limit']<=count($res['data'])){
		return 'NAT转发数已达最大值';
	}

	$url = nokvm_GetUrl($params, '/api/nat_acl/', $sign);
	
	$res = nokvm_Curl($url, ['name'=>trim($post['name']), 'type'=>trim($post['type']), 'interior_port'=>intval($post['interior_port']), 'virtual'=>$vserverid], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("NAT转发添加成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
        $description = sprintf("NAT转发添加失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: 'NAT转发添加失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 删除转发
function nokvm_delNatAcl($params){
	if($params['configoptions']['nat']!=1){
		return '该产品不是NAT产品,不支持删除转发';
	}
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 删除转发
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/nat_acl/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("NAT转发删除成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("NAT转发删除失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: 'NAT转发删除失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 创建建站
function nokvm_addNatWeb($params){
	if($params['configoptions']['nat']!=1){
		return '该产品不是NAT产品,不支持建站';
	}
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 创建建站
	$sign = nokvm_CreateSign($params['server_password']);

	$url = nokvm_GetUrl($params, '/api/nat_web/'.$vserverid, $sign);
	$res = nokvm_Curl($url, [], 30, 'GET');

	if($params['configoptions']['nat_web_limit']<=count($res['data'])){
		return 'NAT建站数已达最大值';
	}

	$url = nokvm_GetUrl($params, '/api/nat_web/', $sign);
	
	$res = nokvm_Curl($url, ['domain'=>trim($post['domain']), 'exterior_port'=>intval($post['exterior_port']), 'exterior_type'=>'http', 'interior_port'=>intval($post['interior_port']), 'interior_type'=>'http', 'virtual'=>$vserverid], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("NAT建站添加成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
        $description = sprintf("NAT建站添加失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: 'NAT建站添加失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 删除建站
function nokvm_delNatWeb($params){
	if($params['configoptions']['nat']!=1){
		return '该产品不是NAT产品,不支持删除建站';
	}
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 删除建站
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/nat_web/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("NAT建站删除成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("NAT建站删除失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: 'NAT建站删除失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 创建快照
function nokvm_CreateSnap($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 创建快照
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/snapshot/', $sign);
	
	$res = nokvm_Curl($url, ['virtual'=>$vserverid], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("快照创建成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
        $description = sprintf("快照创建失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '快照创建失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 删除快照
function nokvm_DeleteSnap($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 删除快照
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/snapshot/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("快照删除成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("快照删除失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '快照删除失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 恢复快照
function nokvm_RestoreSnap($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 恢复快照
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/snapshot/'.$post['id'].'/edit', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("快照恢复成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("快照恢复失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '快照恢复失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 创建备份
function nokvm_CreateBackup($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 创建备份
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/backups/', $sign);
	
	$res = nokvm_Curl($url, ['virtual'=>$vserverid], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("备份创建成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("备份创建失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '备份创建失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 删除备份
function nokvm_DeleteBackup($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 删除备份
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/backups/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("备份删除成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("备份删除失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '备份删除失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 还原备份
function nokvm_RestoreBackup($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 还原备份
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/backups/'.$post['id'].'/edit', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("备份恢复成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("备份恢复失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '备份恢复失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 创建安全组
function nokvm_CreateSecurityGroup($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 创建安全组
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/security_group/', $sign);
	
	$res = nokvm_Curl($url, ['users_id'=>$params['uid'], 'name'=>$post['name'], 'desc'=>$post['desc']], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("安全组创建成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("安全组创建失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '安全组创建失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 删除安全组
function nokvm_DeleteSecurityGroup($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 删除安全组
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/security_group/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("安全组删除成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("安全组删除失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '安全组删除失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 应用安全组
function nokvm_ApplySecurityGroup($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 应用安全组
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/security_group_apply/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, ['nodes'=>[$vserverid]], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("安全组应用成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("安全组应用失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '安全组应用失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 查看安全组
function nokvm_ShowSecurityGroupAcl($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 查看安全组
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/security_group_acl/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	foreach($res['data'] AS $k=>$v){
		$res['data'][$k]['orientation'] = $v['orientation']==1 ? '入' : '出';
		$res['data'][$k]['tactics'] = $v['tactics']==0 ? '允许' : '拒绝';
		if($v['type']=='all'){
			$res['data'][$k]['type'] = '所有';
		}else if($v['type']=='tcp'){
			$res['data'][$k]['type'] = 'TCP';
		}else if($v['type']=='udp'){
			$res['data'][$k]['type'] = 'UDP';
		}else if($v['type']=='icmp'){
			$res['data'][$k]['type'] = 'ICMP';
		}
		$res['data'][$k]['desc'] = $v['desc']===null ? '' : $v['desc'];
	}
	return ['status'=>'success','msg'=>'策略获取成功','list'=>$res['data']];
}

// 创建安全组策略
function nokvm_CreateSecurityGroupAcl($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 创建安全组策略
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/security_group_acl/', $sign);
	
	$res = nokvm_Curl($url, ['groups_id'=>$post['id'], 'orientation'=>$post['orientation'], 'tactics'=>$post['tactics'], 'type'=>$post['type'], 'port_start'=>$post['port_start'], 'port_end'=>$post['port_end'], 'ip_start'=>$post['ip_start'], 'ip_end'=>$post['ip_end'], 'level'=>$post['level'], 'desc'=>$post['desc']], 30, 'POST');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("安全组策略创建成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("安全组策略创建失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '安全组策略创建失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 删除安全组策略
function nokvm_DeleteSecurityGroupAcl($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 删除安全组策略
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/security_group_acl/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("安全组策略删除成功，{$res['message']} - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("安全组策略删除失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '安全组策略删除失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 挂载光驱
function nokvm_MountCdRom($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 挂载光驱
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_cd_rom/'.$vserverid.'/mount/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("光驱挂载成功 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("光驱挂载失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '光驱挂载失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 卸载光驱
function nokvm_UnmountCdRom($params){
	// 通过post接受自定义参数
	$post = input('post.');
	$vserverid = nokvm_GetServerid($params);
	// 卸载光驱
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_cd_rom/'.$vserverid.'/unmount/'.$post['id'], $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		$description = sprintf("光驱卸载成功 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'success', 'msg'=>$res['message']];
	}else{
		$description = sprintf("光驱卸载失败 - Host ID:%d", $params['hostid']);
		$result = ['status'=>'error', 'msg'=>$res['message'] ?: '光驱卸载失败'];
	}
    active_log($description,'host',$params['hostid']);
    return $result;
}

// 开通
function nokvm_CreateAccount($params){
	// 获取自定义字段
	$vserverid = nokvm_GetServerid($params);
	if(!empty($vserverid)){
		return '已开通,不能重复开通';
	}
	if(empty($params['password'])){
		$sys_pwd = rand_str(8);
	}else{
		$sys_pwd = $params['password'];
	}
	$vnc_pwd = rand_str(8);

	$post_data = [];
	$post_data['users_id'] = $params['uid'];
	$post_data['username'] = ($params['user_info']['phone'] ?: $params['user_info']['email']) ?: $params['user_info']['username'];
	$post_data['areas_id'] = $params['configoptions']['Location'];
	$post_data['core'] = $params['configoptions']['CPU'];
	$post_data['cpu_mode'] = $params['configoptions']['cpu_mode'];
	$post_data['memory'] = $params['configoptions']['Memory'];
	$post_data['data_disk_size'] = $params['configoptions']['Disk Space'];
	$post_data['net_out'] = $params['configoptions']['Network Speed'] ?? $params['configoptions']['net_out'];
	$post_data['net_in'] = $params['configoptions']['Network Speed'] ?? $params['configoptions']['net_in'];
	$post_data['snapshoot'] = $params['configoptions']['Snapshot'];
	$post_data['backups'] = $params['configoptions']['Backups'];
	$post_data['templates_id'] = $params['configoptions']['os'];
	$post_data['sys_pwd'] = $sys_pwd;
	$post_data['vnc_pwd'] = $vnc_pwd;
	$post_data['expire_time'] = '2999-01-01 00:00:00'; // date('Y-m-d H:i:s', $params['nextduedate']);
	$post_data['ip_num'] = $params['configoptions']['Extra IP Address'];
	$post_data['flow_limit'] = $params['configoptions']['flow_limit'];
	$post_data['nat_acl_limit'] = (int)$params['configoptions']['nat_acl_limit'];
	$post_data['nat_web_limit'] = (int)$params['configoptions']['nat_web_limit'];
	if(!empty($params['configoptions']['nodes_id'])){
		$post_data['nodes_id'] = $params['configoptions']['nodes_id'];
	}
	if(strtotime($post_data['expire_time']) < time()){
		return '产品已到期';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual', $sign);
	
	$res = nokvm_Curl($url, $post_data);
	if(isset($res['code']) && $res['code'] == 0){
		// 存入IP
		$mainip = '';
		$ip = [];
		foreach($res['data']['public_ip'] as $v){
			if($res['data']['ip_address_id'] == $v['id']){
				$mainip = $v['ip'];
			}else{
				$ip[] = $v['ip'];
			}
		}
		// 获取当前操作系统
		$IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel();
        $osInfo = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('pcs.option_name')
            ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=hc.configoption_id')
            ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.id=hc.configoption_sub_id')
            ->where('hc.host_id',$params['hostid'])
            ->where('pc.option_type','os')
            ->find();
        if (!empty($osInfo) && stripos(strtolower($osInfo['option_name']), 'windows') !== false){
            $username = "administrator";
        }else{
            $username = 'root';
        }

		$HostModel = new HostModel();
        $HostModel->where('id',$params['hostid'])
            ->update([
                'name' => $res['data']['name'],
                'status' => 'Active'
            ]);
        $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $update['dedicatedip'] = $mainip;
        $update['assignedips'] = implode(',', $ip);
        $update['username'] = $username;
        $update['password'] = password_encrypt($sys_pwd);
        if(empty($osInfo)){
            $update['os'] = $post_data['templates_id'];
        }else{
            $update['os'] = explode('^',$osInfo['option_name'])[1]??$osInfo['option_name'];
        }
        $update['bwlimit'] = (int)$post_data['flow_limit'];
        $update['vserverid'] = $res['data']['id']; // 虚拟机ID
        $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update($update);

		return 'ok';
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '开通失败'];
	}
}

// 暂停
function nokvm_SuspendAccount($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_pause/'.$vserverid, $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '暂停失败'];
	}
}

// 解除暂停
function nokvm_UnsuspendAccount($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_restore_pause/'.$vserverid, $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '解除暂停失败'];
	}
}

// 删除
function nokvm_TerminateAccount($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);
	
	$res = nokvm_Curl($url, [], 30, 'DELETE');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '删除失败'];
	}
}

// 开机
function nokvm_On($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_power/'.$vserverid.'/start', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '开机失败'];
	}
}

// 关机
function nokvm_Off($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_power/'.$vserverid.'/shut', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '关机失败'];
	}
}

// 重启
function nokvm_Reboot($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_power/'.$vserverid.'/restart', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '重启失败'];
	}
}

// 硬关机
function nokvm_HardOff($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_power/'.$vserverid.'/compel_shut', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '强制关机失败'];
	}
}

// 硬重启
function nokvm_HardReboot($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_power/'.$vserverid.'/compel_restart', $sign);
	
	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '强制重启失败'];
	}
}

// Vnc
function nokvm_Vnc($params){
    $vserverid = nokvm_GetServerid($params);
    if(empty($vserverid)){
        return 'nokvmID错误';
    }
    $novnc_type = false;
    if(isset($params['accesshash']) && $params['accesshash'])
    {
        $params['accesshash'] = explode(PHP_EOL, $params['accesshash']);
        foreach($params['accesshash'] as $key => $val)
        {
            if($val == htmlspecialchars('<novnc_type>new</novnc_type>'))
            {
                $novnc_type = true;
            }
        }
    }
    // 先获取vnc密码
    $sign = nokvm_CreateSign($params['server_password']);
    $url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);

    $res = nokvm_Curl($url, [], 15, 'GET');
    if(isset($res['code']) && $res['code'] == 0){

        $sign = nokvm_CreateSign($params['server_password']);
        $url = nokvm_GetUrl($params, '/api/virtual_link_vnc/'.$vserverid, $sign);
        if(!$novnc_type)
        {
            $result['status'] = 'success';
            $result['msg'] = 'vnc获取成功';
            $result['url'] = $url.'&password='.$res['data']['vnc_pwd'];
        }else{
            $token_result = nokvm_Curl($url, [], 15, 'GET');
            /**
             * 改版之后的接口
             */
            if(isset($token_result['code']) && $token_result['code'] == 0)
            {
                $result['status'] = 'success';
                $result['msg'] = 'vnc获取成功';
                $url = nokvm_GetUrl($params, '/api/virtual_link_vnc_view/'.$vserverid.'/' . $token_result['vnc_token'], $sign);
                $result['url'] = $url.'&password='.$res['data']['vnc_pwd'];
            }else{
                $result['status'] = 'error';
                $result['msg'] = 'VNC获取失败';
            }
        }

    }else{
        $result['status'] = 'error';
        $result['msg'] = 'VNC获取失败';
    }
    //$res = nokvm_Curl($url, $post_data, 30, 'GET');
    return $result;
}

// 重装系统
function nokvm_Reinstall($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	//$os = input('post.os', 0, 'intval');
	if(empty($params['reinstall_os'])){
		return '操作系统错误';
	}
	// 判断是否是可配置选项
	// $r = Db::name('product_config_options_sub')
	// 	->alias('a')
	// 	->field('a.*')
	// 	->leftJoin('product_config_options b', 'a.config_id=b.id')
	// 	->leftJoin('product_config_links c', 'b.gid=c.gid')
	// 	->where('c.pid', $params['productid'])
	// 	->where('a.id', $os)
	// 	->find();
	// if(empty($r)){
	// 	return '操作系统错误';
	// }
	// $arr = explode('|', $r['option_name']);

	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_reset_system/'.$vserverid.'/'.$params['reinstall_os'], $sign);

	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		if(stripos(strtolower($params['reinstall_os_name']), 'Windows') !== false){
        	$username = 'administrator';
    	}else{
        	$username = 'root';
    	}
		$IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update([
            'username' => $username,
            'os' => $params['reinstall_os_name']
        ]);

		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '重装失败'];
	}
}

// 破解密码
function nokvm_CrackPassword($params, $new_pass){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_reset_password/'.$vserverid, $sign);

	$post_data['type'] = 'system';
	$post_data['password'] = $new_pass;

	$res = nokvm_Curl($url, $post_data, 30, 'PUT');
	if(isset($res['code']) && $res['code'] == 0){
		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '同步失败'];
	}
}

// 同步
function nokvm_Sync($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);

	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		// 存入IP
		$mainip = '';
		$ip = [];
		foreach($res['data']['ip_list'] as $v){
			if($res['data']['ip_address_id'] == $v['id']){
				$mainip = $v['ip'];
			}else{
				$ip[] = $v['ip'];
			}
		}
		$update['dedicatedip'] = $mainip;
		$update['assignedips'] = implode(',', $ip);
		$update['password'] = password_encrypt($res['data']['sys_pwd']);
		if(is_numeric($res['data']['flow']['code']) && $res['data']['flow']['code'] == 0){
  			$update['bwusage'] = round(($res['data']['flow']['data']['in'] + $res['data']['flow']['data']['out'])/1024/1024/1024, 2);
  			if(is_numeric($res['data']['flow_limit'])){
  				$update['bwlimit'] = (int)$res['data']['flow_limit'];
  			}
  		}
  		/*$os_info = Db::name('host_config_options')
                    ->alias('a')
                    ->field('c.option_name')
                    ->leftJoin('product_config_options b', 'a.configid=b.id')
                    ->leftJoin('product_config_options_sub c', 'a.optionid=c.id')
                    ->where('a.relid', $params['hostid'])
                    ->where('b.option_type', 5)
                    ->find();
        if(stripos($os_info['option_name'], 'win') !== false){
            $update['username'] = 'administrator';
        }else{
            $update['username'] = 'root';
        }*/
        $update['username'] = 'root';

        $HostModel = new HostModel();
        $HostModel->where('id',$params['hostid'])
            ->update([
                'name' => $res['data']['name']
            ]);
        $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update($update);

		return ['status'=>'success', 'msg'=>$res['message']];
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '同步失败'];
	}
}

// 升降级
function nokvm_ChangePackage($params){
	$vserverid = nokvm_GetServerid($params);
	/*if(empty($vserverid)){
	    # wyh 20201109 增
        $vserverid = intval($params['old_configoptions']['customfields']['vserverid']);
        if (empty($vserverid)){
            return 'nokvmID错误';
        }
	}*/

	$post_data = [];
	if(isset($params['configoptions_upgrade']['CPU'])){
		$post_data['core'] = $params['configoptions']['CPU'];
	}
	if(isset($params['configoptions_upgrade']['Memory'])){
		$post_data['memory'] = $params['configoptions']['Memory'];
	}
	if(isset($params['configoptions_upgrade']['Disk Space'])){
		$post_data['data_disk_size'] = $params['configoptions']['Disk Space'];
	}
	if(isset($params['configoptions_upgrade']['Network Speed']) || isset($params['configoptions_upgrade']['net_out'])){
		$post_data['net_out'] = $params['configoptions']['Network Speed'] ?? $params['configoptions']['net_out'];
	}
	if(isset($params['configoptions_upgrade']['Network Speed']) || isset($params['configoptions_upgrade']['net_in'])){
		$post_data['net_in'] = $params['configoptions']['Network Speed'] ?? $params['configoptions']['net_in'];
	}
	if(isset($params['configoptions_upgrade']['flow_limit'])){
        /*if($params['configoptions']['flow_limit'] > 0){
            $IdcsmartCommonDcimBuyRecordkModel = new \server\idcsmart_common\model\IdcsmartCommonDcimBuyRecordkModel();
        	$capacity = $IdcsmartCommonDcimBuyRecordkModel
	            ->where('type', 'flow_packet')
	            ->where('hostid', $params['hostid'])
	            ->where('uid', $params['uid'])
	            ->where('status', 1)
	            ->where('show_status', 0)
	            ->where('pay_time', '>', strtotime(date('Y-m-01 00:00:00')))
	            ->sum('capacity');
        	$post_data['flow_limit'] = $params['configoptions']['flow_limit'] + $capacity;
        }else{
        	$post_data['flow_limit'] = 0;
        }*/
        //$post_data['flow_limit'] = 0;
	}
	if(isset($params['configoptions_upgrade']['Extra IP Address'])){
		$post_data['ip_num'] = $params['configoptions']['Extra IP Address'];
		// $old_ip_num = $params['old_configoptions']['Extra IP Address'];
		// if($ip_num > $old_ip_num){
		// 	$post_data['ip_num'] = intval($ip_num - $old_ip_num);
		// }
	}
	if(!empty($post_data)){
		// 升级配置
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);
		$res = nokvm_Curl($url, $post_data, 20, 'PUT');

		nokvm_Sync($params);
	}
	// 升级快照配置
	if(isset($params['configoptions_upgrade']['Snapshot'])){
		// 获取详情
		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);

		$res1 = nokvm_Curl($url, [], 10, 'GET');
		if(isset($res1['code']) && $res1['code'] == 0){
			$post_data = [];
			$post_data['snapshots_switch'] = $res1['data']['snapshots_switch'];
			$post_data['snapshot'] = $params['configoptions']['Snapshot'];

			$sign = nokvm_CreateSign($params['server_password']);
			$url = nokvm_GetUrl($params, '/api/snapshot/'.$vserverid, $sign);

			nokvm_Curl($url, $post_data, 5, 'PUT');
		}
	}
	// 升级配置
	if(isset($params['configoptions_upgrade']['Backups'])){
		$post_data = [];
		$post_data['backups'] = $params['configoptions']['Backups'];

		$sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/backups/'.$vserverid, $sign);

		// 升级快照配置
		nokvm_Curl($url, $post_data, 5, 'PUT');

	}

	if(isset($res['code']) && $res['code'] == 0){ # 配置改变才变
		$result['status'] = 'success';
		$result['msg'] = $res['message'] ?: '修改配置成功';
	}else{
		$result['status'] = 'error';
		$result['msg'] = $res['message'] ?: '修改配置失败';
	}

    # TODO wyh 20201105 新增 vserverid
    $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
    $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])
        ->update([
            'vserverid' => $res['data']['id']??$vserverid
        ]);

	return $result;
}

// 云主机状态
function nokvm_Status($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return 'nokvmID错误';
	}
	$sign = nokvm_CreateSign($params['server_password']);
	$url = nokvm_GetUrl($params, '/api/virtual_run_status/'.$vserverid, $sign);

	$res = nokvm_Curl($url, [], 30, 'GET');
	if(isset($res['code']) && $res['code'] == 0){
		$status = [
			'1'=>'开机',
			'2'=>'关机',
			'3'=>'挂起',
			'4'=>'关机中',
			'5'=>'创建中',
			'8'=>'重装中',
			'11'=>'迁移中',
			'12'=>'迁移完成',
			'13'=>'暂停',
			'14'=>'超流断网'
		];
		// 定义状态代码
		$result['status'] = 'success';
		if(in_array($res['data']['code'], [1,12])){
			$result['data']['status'] = 'on';
			$result['data']['des'] = '开机';
		}else if(in_array($res['data']['code'], [2,3])){
			$result['data']['status'] = 'off';
			$result['data']['des'] = '关机';
		}else if(in_array($res['data']['code'], [4,5,8,11])){
			$result['data']['status'] = 'process';
			$result['data']['des'] = $status[$res['data']['code']];
		}else if($res['data']['code'] == 13 || $res['data']['code'] == 14){
			$result['data']['status'] = 'suspend';
			$result['data']['des'] = '暂停';
		}else{
			$result['data']['status'] = 'unknown';
            $result['data']['des'] = '未知';
		}
		return $result;
	}else{
		return ['status'=>'error', 'msg'=>$res['message'] ?: '获取失败'];
	}
}


function nokvm_FiveMinuteCron(){
	$time = time();
    $HostModel = new HostModel();
    $host_data = $HostModel->alias('a')
        ->field('a.id,a.status domainstatus,a.suspend_reason,a.suspend_type,a.client_id uid,c.ip_address server_ip,
        c.hostname server_host,c.username server_username,c.password server_password,c.accesshash,c.secure,c.port,shl.vserverid')
        ->leftJoin('module_idcsmart_common_server_host_link shl','shl.host_id=a.id')
        ->leftJoin('module_idcsmart_common_server c','c.id=shl.server_id')
        ->leftJoin('module_idcsmart_common_server_group d','c.gid=d.id')
        ->whereIn('a.status','Active,Suspended')
        ->where('a.due_time=0 OR a.due_time>'.$time)
        ->where('d.system_type', 'normal')
        ->where('c.type', 'nokvm')
        ->where('a.is_delete', 0)
        ->select()
        ->toArray();

    $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();

    foreach($host_data as $v){
    	$v['server_password'] = aes_password_decode($v['server_password']);
  		$sign = nokvm_CreateSign($v['server_password']);
  		$url = nokvm_GetUrl($v, '/api/virtual/'.$v['vserverid'], $sign);
  		$res = nokvm_Curl($url, [], 10, 'GET');
  		if(is_numeric($res['data']['flow']['code']) && $res['data']['flow']['code'] == 0){
  			$update = [];
  			$update['bwusage'] = round(($res['data']['flow']['data']['in'] + $res['data']['flow']['data']['out'])/1024/1024/1024, 2);
  			if(is_numeric($res['data']['flow_limit'])){
  				$update['bwlimit'] = (int)$res['data']['flow_limit'];
  			}
            $IdcsmartCommonServerHostLinkModel->where('id',$v['id'])->update($update);
  			// 流量超额暂停
  			if($v['domainstatus'] == 'Active' && $update['bwlimit'] > 0 && $update['bwusage'] > $update['bwlimit']){
                $HostModel->suspendAccount([
                    'id' => $v['id'],
                    'suspend_reason' => '用量超额',
                    'suspend_type' => 'overtraffic'
                ]);
  			}
  			// 解除暂停
  			if($v['domainstatus'] == 'Suspended' && ($update['bwlimit'] == 0 || $update['bwusage'] < $update['bwlimit']) && ($v['suspend_type']=='overtraffic')){
                $HostModel->unsuspendAccount($v['id']);
  			}
  		}
    }
}

// 每天任务
function nokvm_DailyCron(){
	// 每月开头还原流量上限
	if(date('Y-m-d') == date('Y-m-01')){
		$time = time();
        $HostModel = new HostModel();
		$host_data = $HostModel->alias('a')
            ->field('a.id,a.status domainstatus,a.suspend_reason,a.suspend_type,a.client_id uid,c.ip_address server_ip,
            c.hostname server_host,c.username server_username,c.password server_password,c.accesshash,c.secure,c.port,shl.vserverid')
            ->leftJoin('module_idcsmart_common_server_host_link shl','shl.host_id=a.id')
            ->leftJoin('module_idcsmart_common_server c','c.id=shl.server_id')
            ->leftJoin('module_idcsmart_common_server_group d','c.gid=d.id')
            ->whereIn('a.status','Active,Suspended')
            ->where('a.due_time=0 OR a.due_time>'.$time)
            ->where('d.system_type', 'normal')
            ->where('c.type', 'nokvm')
            ->where('a.is_delete', 0)
            ->select()
            ->toArray();
	    $model = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
	    foreach($host_data as $v){
	    	$params = $model->getProvisionParams($v['id']);
	    	if(isset($params['configoptions']['flow_limit'])){
	    		/*if($params['configoptions']['flow_limit'] > 0){
	    			$capacity = Db::name('dcim_buy_record')
					            ->where('type', 'flow_packet')
					            ->where('hostid', $v['id'])
					            ->where('uid', $v['uid'])
					            ->where('status', 1)
					            ->where('show_status', 0)
					            ->where('pay_time', '>', strtotime(date('Y-m-01 00:00:00')))
					            ->sum('capacity');
	    			$post_data['flow_limit'] = $params['configoptions']['flow_limit'] + $capacity;
	    		}else{
	    			$post_data['flow_limit'] = 0;
	    		}*/
                $post_data['flow_limit'] = 0;
				$sign = nokvm_CreateSign($params['server_password']);
				$url = nokvm_GetUrl($params, '/api/virtual/'.$v['vserverid'], $sign);
				nokvm_Curl($url, $post_data, 20, 'PUT');

				nokvm_Sync($params);
	    	}
	    }
	}
}

// 
function nokvm_FlowPacketPaid($params){
	$vserverid = nokvm_GetServerid($params);
	if(empty($vserverid)){
		return false;
	}
	// 获取本月所有已买流量包
	/*$capacity = Db::name('dcim_buy_record')
            // ->field('capacity')
            ->where('type', 'flow_packet')
            ->where('hostid', $params['hostid'])
            ->where('uid', $params['uid'])
            ->where('status', 1)
            ->where('show_status', 0)
            ->where('pay_time', '>', strtotime(date('Y-m-01 00:00:00')))
            // ->order('pay_time', 'asc')
            ->sum('capacity');*/
	if($params['configoptions']['flow_limit'] > 0){

		// $post_data['flow_limit'] = $params['configoptions']['flow_limit'] + $capacity;
        $post_data['flow_limit'] = 0;

        $sign = nokvm_CreateSign($params['server_password']);
		$url = nokvm_GetUrl($params, '/api/virtual/'.$vserverid, $sign);
		$res = nokvm_Curl($url, $post_data, 20, 'PUT');

		nokvm_Sync($params);
		// 解除暂停
		if($params['domainstatus'] == 'Suspended' && $params['suspend_reason'] == 'overtraffic'){
			// 获取同步后用量
			$IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
            $after = $IdcsmartCommonServerHostLinkModel->field('bwusage,bwlimit')
                ->where('host_id',$params['hostid'])
                ->find();

			if($after['bwlimit'] == 0 || $after['bwlimit'] > $after['bwusage']){
				$HostModel = new HostModel();
                $HostModel->unsuspendAccount($params['hostid']);
			}
		}
	}
    return true;
}

// 后台按钮隐藏
function nokvm_AdminButtonHide($params){
	if(!empty(nokvm_GetServerid($params)) && $params['serverid']>0){
		return ['CreateAccount'];
	}else{
		return ['SuspendAccount','UnsuspendAccount','TerminateAccount','On','Off','Reboot','HardOff','HardReboot','Reinstall','CrackPassword','Vnc','Sync'];
	}
}

// 获取自定义字段value
function nokvm_GetServerid($params){
	return (int)($params['vserverid']??0);
}

// 创建签名
function nokvm_CreateSign($token = ''){
	$data['timeStamp'] = time();
	$data['randomStr'] = rand_str(6);
	$data['token'] = $token;
	$res['time'] = $data['timeStamp'];
	$res['random'] = $data['randomStr'];
	sort($data, SORT_STRING);
	$str = implode($data);
	$signature = md5($str);
	$signature = strtoupper($signature);
	$res['signature'] = $signature;
	return $res;
}

function nokvm_GetUrl($params, $path = '/api/virtual', $query = []){
	$url = '';
	if($params['secure']){
		$url = 'https://';
	}else{
		$url = 'http://';
	}
	$url .= $params['server_ip'] ?: $params['server_host'];
	if(!empty($params['port'])){
		$url .= ':'.$params['port'];
	}
	$url .= $path;
	$q = '';
	foreach($query as $k=>$v){
		$q .= "&{$k}={$v}";
	}
	if(!empty($q)){
		$url = $url.'?'.ltrim($q, '&');
	}
	return $url;
}

function nokvm_Curl($url = '', $data = [], $timeout = 30, $request = 'POST', $header = []){
	$curl = curl_init();
    if($request == 'GET'){
        $s = '';
        if(!empty($data)){
            foreach($data as $k=>$v){
                $s .= $k.'='.urlencode($v).'&';
            }
        }
        if($s){
            $s = '?'.trim($s, '&');
        }
        curl_setopt($curl, CURLOPT_URL, $url.$s);
    }else{
        curl_setopt($curl, CURLOPT_URL, $url);
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_USERAGENT, 'WHMCS');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //curl_setopt($curl, CURLOPT_COOKIESESSION, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if(strtoupper($request) == 'GET'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
    }
    if(strtoupper($request) == 'POST'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if(is_array($data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    if(strtoupper($request) == 'PUT' || strtoupper($request) == 'DELETE'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($request));
        if(is_array($data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    if(!empty($header)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    $res = curl_exec($curl);
    $error = curl_error($curl);
    if(!empty($error)){
    	return ['status'=>500, 'message'=>'CURL ERROR:'.$error];
    }
    $info = curl_getinfo($curl);
    curl_close($curl);
    return json_decode($res, true);
}