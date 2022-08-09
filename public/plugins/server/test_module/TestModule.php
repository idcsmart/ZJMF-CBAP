<?php 
namespace server\test_module;

use think\facade\Db;

class TestModule
{

	public function metaData(){
		$config = [
			'display_name'=>'测试模块',
			'version'=>'1.0',
		];
		return $config;
	}

	public function testConnect($params){
		return ['status'=>200, 'msg'=>'连接成功'];
	}


	public function createAccount($params){
		return ['status'=>200, 'msg'=>'开通成功'];
	}

	public function suspendAccount($params){
		return ['status'=>200, 'msg'=>'暂停成功'];
	}

	public function unsuspendAccount($params){
		return ['status'=>200, 'msg'=>'解除暂停成功'];
	}

	public function terminateAccount($params){
		return ['status'=>200, 'msg'=>'删除成功'];
	}

	public function clientArea($params){
		$hostConfigOption = Db::name('host_config_option')
						->where('host_id', $params['host']['id'])
						->find();

		$res = 'CPU: '. ($hostConfigOption['data'] ?? 1) . '核';
		return $res;
	}

	public function adminArea($params){
		return $this->clientArea($params);
	}

	public function serverConfigOption($params){
		$list = [
			[
				'name'=>'1核/月',
				'value'=>1,
				'price'=>1,
			],
			[
				'name'=>'2核/月',
				'value'=>2,
				'price'=>2,
			],
			[
				'name'=>'4核/月',
				'value'=>3,
				'price'=>4,
			],
		];
		$res = [
			'template'=>'template/product_config.html',
			'vars'=>[
				'list'=>$list,
			]
		];
		return $res;
	}

	public function clientProductConfigOption($params){
		$list = [
			[
				'name'=>'1核/月',
				'value'=>1,
				'price'=>1,
			],
			[
				'name'=>'2核/月',
				'value'=>2,
				'price'=>2,
			],
			[
				'name'=>'4核/月',
				'value'=>3,
				'price'=>4,
			],
		];

		$res = [
			'template'=>'template/cart.html',
			'vars'=>[
				'list' => $list,
				'id'   => $params['product']['id'],
				'tag'  => $params['tag'] ?? '',
			]
		];
		return $res;
	}

	public function adminProductConfigOption($params){
		return $this->clientProductConfigOption($params);
	}

	public function cartCalculatePrice($params){
		$list = [
			[
				'name'=>'1核/月',
				'value'=>1,
				'price'=>1,
			],
			[
				'name'=>'2核/月',
				'value'=>2,
				'price'=>2,
			],
			[
				'name'=>'4核/月',
				'value'=>3,
				'price'=>4,
			],
		];

		$cpu = $params['custom']['cpu'] ?? 1;

		if(!in_array($cpu, [1,2,3])){
			return ['status'=>400, 'msg'=>'CPU错误,当前只支持:1,2,3'];
		}
		$price = $list[$cpu-1]['price'];

		$cart = '<div style="color:red"><span>CPU: </span>  '. $list[$cpu-1]['name'] .' 月付   '.$price.'   </div>';
		$description = 'CPU:'.pow(2, $cpu-1).'核';

		$result = [
			'status'=>200,
			'msg'=>'获取成功',
			'data'=>[
				'price'=>$price,  				// 配置项订单金额
				'billing_cycle'=>'月付',       	// 周期名称
				'duration'=>30*24*3600,			// 时长秒
				'description'=>$description,		// 描述用于产品描述,纯文本
				'content'=>$cart,		    		// 自用随便定义,用于选择后购物车显示(支持html)
			]
		];
		return $result;
	}

	public function afterSettle($params){
		$cpu = $params['custom']['cpu'] ?? 1;

		Db::name('host_config_option')->insert([
			'host_id'=>$params['host_id'],
			'data'=>$cpu,
		]);
	}

	public function adminChangeConfigOption($params){
		$HostModel = $params['host'];

		$hostConfigOption = Db::name('host_config_option')
							->where('host_id', $HostModel['id'])
							->find() ?? [];
		if(empty($hostConfigOption)){
			$hostConfigOption = [
				'host_id'=>$HostModel['id'],
				'data'=>1,
			];

			Db::name('host_config_option')->insert($hostConfigOption);
		}

		$list = [
			[
				'name'=>'1核/月',
				'value'=>1,
				'price'=>1,
			],
			[
				'name'=>'2核/月',
				'value'=>2,
				'price'=>2,
			],
			[
				'name'=>'4核/月',
				'value'=>3,
				'price'=>4,
			],
		];

		$now_cpu = pow(2, $hostConfigOption['data']-1);

		$res = [
			'template'=>'template/upgrade.html',
			'vars'=>[
				'list'=> $list,
				'now' => $now_cpu,
				'val' => $hostConfigOption['data']
			]
		];
		return $res;
	}

	public function clientChangeConfigOption($params){
		return $this->adminChangeConfigOption($params);
	}

	public function changeConfigOptionCalculatePrice($params){
		$new_cpu = $params['custom']['cpu'] ?? 1;
		if(!in_array($new_cpu, [1,2,3])){
			return ['status'=>400, 'msg'=>'CPU错误,当前只支持:1,2,3'];
		}

		$hostConfigOption = Db::name('host_config_option')
							->where('host_id', $params['host']['id'])
							->find();
		if($hostConfigOption['data'] == $new_cpu){
			return ['status'=>400, 'msg'=>'请选择新的CPU配置项'];
		}

		$list = [
			[
				'name'=>'1核/月',
				'value'=>1,
				'price'=>1,
			],
			[
				'name'=>'2核/月',
				'value'=>2,
				'price'=>2,
			],
			[
				'name'=>'4核/月',
				'value'=>3,
				'price'=>4,
			],
		];

		// 当前CPU核数
		$nowCpu = pow(2, $hostConfigOption['data']-1);
		$newCpu = pow(2, $new_cpu-1); 

		$price = $list[$new_cpu-1]['price'] - $list[$hostConfigOption['data']-1]['price'];

		$description = sprintf('CPU: %d核 => %d核', $nowCpu, $newCpu);

		$result = [
			'status'=>200,
			'msg'=>'获取成功',
			'data'=>[
				'price'=>$price,  				// 配置项订单金额
				'billing_cycle'=>'月付',       	// 周期名称
				'duration'=>30*24*3600,			// 时长秒
				'description'=>$description,		// 描述用于产品描述,纯文本
			]
		];
		return $result;
	}

	public function changePackage($params){
		if(!isset($params['custom']['cpu']) || !in_array($params['custom']['cpu'], [1,2,3])){
			return false;
		}
		$hostConfigOption = Db::name('host_config_option')
							->where('host_id', $params['host']['id'])
							->find();
		if(!empty($hostConfigOption)){
			 Db::name('host_config_option')
			->where('host_id', $params['host']['id'])
			->update(['data'=>$params['custom']['cpu']]);
		}else{
			Db::name('host_config_option')->insert([
				'host_id'=>$params['host']['id'],
				'data'=>$params['custom']['cpu'],
			]);
		}
	}

	public function changeProduct($params){
		return $this->changePackage($params);
	}

	public function durationPrice($params){
		$result = [
			'status'=>200,
			'msg'=>'获取成功',
			'data'=>[
				[
					'price'=>0.02,  				// 配置项订单金额
					'billing_cycle'=>'月付',       	// 周期名称
					'duration'=>30*24*3600,			// 时长秒
				],
				[
					'price'=>0.01,  				// 配置项订单金额
					'billing_cycle'=>'年付',       	// 周期名称
					'duration'=>365*30*24*3600,			// 时长秒
				],
			],
		];
		return $result;
	}

	public function allConfigOption($params){
		$result = [
			[
				'name'=>'CPU',
				'field'=>'cpu',
				'type'=>'dropdown',
				'option'=>[
					[
						'name'=>'1核',
						'value'=>1
					],
					[
						'name'=>'2核',
						'value'=>2
					],
					[
						'name'=>'4核',
						'value'=>4
					],
				]
			],
			[
				'name'=>'内存',
				'field'=>'memory',
				'type'=>'dropdown',
				'option'=>[
					[
						'name'=>'1核',
						'value'=>1
					],
					[
						'name'=>'2核',
						'value'=>2
					],
					[
						'name'=>'4核',
						'value'=>4
					],
				]
			]
		];
		return $result;
	}


}


