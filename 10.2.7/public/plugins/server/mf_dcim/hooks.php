<?php

use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\ServerModel;
use server\mf_dcim\model\DurationModel;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\idcsmart_dcim\Dcim;

// 商品保存后预设商品默认值
add_hook('after_product_edit', function($param){
	$ProductModel = ProductModel::find($param['id']);
	if($ProductModel->getModule() != 'mf_dcim'){
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


add_hook('daily_cron', function($param){
	try{
		// 处理清零,超额暂停
		$host = HostLinkModel::alias('hl')
			->field('h.id,h.name,h.active_time,h.create_time,h.server_id,h.status,h.suspend_type,hl.rel_id,hl.config_data')
			->join('host h', 'hl.host_id=h.id')
			->whereIn('h.status', 'Active,Suspended')
			->where('hl.rel_id', '>', 0)
			->select()
			->toArray();
	}catch(\Exception $e){
		// 异常可能是表不存在
		return false;
	}

	$HostModel = new HostModel();
	$dcim = [];
	$date = date('d');
	foreach($host as $v){
		$configData = json_decode($v['config_data'], true);
		if($configData['line']['bill_type'] != 'flow'){
			continue;
		}
		// 流量周期
		$billCycle = $configData['flow']['other_config']['bill_cycle'] ?? 'month';

		if(!isset($dcim[$v['server_id']])){
			$ServerModel = ServerModel::find($v['server_id']);
			$ServerModel['password'] = aes_password_decode($ServerModel['password']);

			$Dcim = new Dcim($ServerModel);
			$dcim[$v['server_id']] = $Dcim;
		}else{
			$Dcim = $dcim[ $v['server_id'] ];
		}
		if($billCycle == 'last_30days'){
			// 计算开通的日期
			$date = date('d', $v['active_time'] ?: $v['create_time']);
        }else{
        	$date = '01';
        }
        // 流量清零
        if($date == date('d')){
        	$res = $Dcim->resetFlow(['id'=>$v['rel_id'], 'hostid'=>$v['id']]);

        	if($res['status'] == 200){
        		if($v['status'] == 'Suspended' && $v['suspend_type'] == 'overtraffic'){
        			$unsuspendRes = $HostModel->unsuspendAccount($v['id']);
        			if($unsuspendRes['status'] == 200){
        				$descrition = sprintf('产品%s流量清零成功,解除因流量超额的暂停成功', $v['name']);
        			}else{
        				$descrition = sprintf('产品%s流量清零成功,解除因流量超额的暂停失败,原因:%s', $v['name'], $unsuspendRes['msg']);
        			}
        		}else{
        			$descrition = sprintf('产品%s流量清零成功', $v['name']);
        		}
        	}else{
        		// 流量清零失败
        		$descrition = sprintf('产品%s流量清零失败,原因:%s', $v['name'], $res['msg']);
        	}
        	active_log($description, 'host', $v['id']);
        }else{
        	// 只有已激活的才检查
        	if($v['status'] == 'Suspended'){
        		continue;
        	}

        	// 检查是否超额
        	$post = [];
			$post['id'] = $v['rel_id'];
			$post['hostid'] = $v['id'];
			$post['unit'] = 'GB';

			$flow = $Dcim->flow($post);
			if($flow['status'] == 200){
				$data = $flow['data'][ $billCycle ];
				$percent = str_replace('%', '', $data['used_percent']);

				$total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
				$used = round($total * $percent / 100, 2);
				if($percent >= 100){
					// 执行超额
					$post = [];
					$post['id'] = $v['rel_id'];
			        $post['type'] = $billCycle;
			        $post['hostid'] = $v['id'];

			        $overFlow = $Dcim->overFlow($post);
			        if($overFlow['status'] == 200){
			        	// 超额后执行超额暂停
			        	if($overFlow['act'] == 1){
			                //执行暂停
			                $suspendRes = $HostModel->suspendAccount([
			                	'id'=>$v['id'],
			                	'suspend_type'=>'overtraffic',
			                	'suspend_reason'=>sprintf('流量限制:%dGB,已用:%sGB', $total, $used),
			                ]);
			                if($suspendRes['status'] == 200){
			                	$descrition = sprintf('产品%s流量使用超额,暂停成功,流量限制:%dGB,已用:%sGB', $v['name'], $total, $used);
			                }else{
			                	$descrition = sprintf('产品%s流量使用超额,暂停失败,流量限制:%dGB,已用:%sGB, 原因:%s', $v['name'], $total, $used, $suspendRes['msg']);
			                }
			            }else if($overFlow['act'] == 2){
			            	$descrition = sprintf('产品%s流量使用超额,限速成功,流量限制:%dGB,已用:%sGB', $v['name'], $total, $used);
			            }else if($overFlow['act'] == 3){
			            	$descrition = sprintf('产品%s流量使用超额,关闭端口成功,流量限制:%dGB,已用:%sGB', $v['name'], $total, $used);
			            }else{
			            	$description = '';
			            }
			        }else{
			        	$descrition = sprintf('产品%s流量使用超额,执行超额动作失败,流量限制:%dGB,已用:%sGB,原因:%s', $v['name'], $total, $used, $overFlow['msg']);
			        }
			        if(!empty($description)){
		            	active_log($description, 'host', $v['id']);
		            }
				}
			}
        }
        // 防止请求过快
        sleep(1);
	}
});