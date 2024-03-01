<?php

use app\common\model\HostModel;
use app\common\model\ServerModel;
use server\mf_dcim\model\DurationModel;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\idcsmart_dcim\Dcim;
use think\db\exception\PDOException;
use server\mf_dcim\model\DataCenterModel;
use server\mf_dcim\model\ConfigLimitModel;
use server\mf_dcim\model\ConfigModel;
use server\mf_dcim\model\ImageGroupModel;
use server\mf_dcim\model\ImageModel;
use server\mf_dcim\model\LineModel;
use server\mf_dcim\model\ModelConfigModel;
use server\mf_dcim\model\OptionModel;
use server\mf_dcim\model\PriceModel;
use server\mf_dcim\model\DurationRatioModel;
use server\mf_dcim\model\HostImageLinkModel;
use server\mf_dcim\model\ModelConfigOptionLinkModel;

// 商品删除后
add_hook('after_product_delete', function($param){
	if(!isset($param['module']) || $param['module'] != 'mf_dcim'){
		return false;
	}
	try{
		$dataCenterId = DataCenterModel::where('product_id', $param['id'])->column('id');
        $imageId = ImageModel::where('product_id', $param['id'])->column('id');
		$modelConfigId = ModelConfigModel::where('product_id', $param['id'])->column('id');

		ConfigModel::where('product_id', $param['id'])->delete();
		ConfigLimitModel::where('product_id', $param['id'])->delete();
		DataCenterModel::where('product_id', $param['id'])->delete();
		DurationModel::where('product_id', $param['id'])->delete();
		ImageModel::where('product_id', $param['id'])->delete();
		ImageGroupModel::where('product_id', $param['id'])->delete();
		if(!empty($dataCenterId)){
			LineModel::whereIn('data_center_id', $dataCenterId)->delete();
		}
		if(!empty($imageId)){
			HostImageLinkModel::whereIn('image_id', $imageId)->delete();
		}
		ModelConfigModel::where('product_id', $param['id'])->delete();
		OptionModel::where('product_id', $param['id'])->delete();
		PriceModel::where('product_id', $param['id'])->delete();
		DurationRatioModel::where('product_id', $param['id'])->delete();
        if(!empty($modelConfigId)){
        	ModelConfigOptionLinkModel::whereIn('model_config_id', $modelConfigId)->delete();
        }
	}catch(\PDOException $e){
		
	}catch(\Exception $e){

	}
});

// 产品删除后
add_hook('after_host_delete', function($param){
	try{
		HostLinkModel::where('host_id', $param['id'])->delete();
	}catch(\Exception $e){
		
	}
});

// 商品复制后
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
				$value['product_id'] = $param['id'];
				$value['data_center_id'] = $dataCenterIdArr[$value['data_center_id']] ?? 0;
				$r = $LineModel->create($value);
				$lineIdArr[$id] = $r->id;
			}

			$ModelConfigModel = new ModelConfigModel();
			$modelConfig = $ModelConfigModel->where('product_id', $param['product_id'])->select()->toArray();
			$modelConfigIdArr = [];
			foreach ($modelConfig as $key => $value) {
				$id = $value['id'];
				$modelConfigIdArr[$id] = 0;
				unset($value['id']);
				$value['product_id'] = $param['id'];
				$r = $ModelConfigModel->create($value);
				$modelConfigIdArr[$id] = $r->id;
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
				$value['model_config_id'] = $modelConfigIdArr[$value['model_config_id']] ?? 0;
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

			if(!empty($modelConfigIdArr)){
				$ModelConfigOptionLinkModel = new ModelConfigOptionLinkModel();
				$modelConfigOptionLink = $ModelConfigOptionLinkModel->whereIn('model_config_id', array_keys($modelConfigIdArr))->select()->toArray();
				foreach ($modelConfigOptionLink as $key => $value) {
                    $value['model_config_id'] = $modelConfigIdArr[$value['model_config_id']] ?? 0;
					$value['option_id'] = $optionIdArr[$value['option_id']] ?? 0;
					$ModelConfigOptionLinkModel->create($value);
				}
			}

            $PriceModel = new PriceModel();
            $price = $PriceModel->where('product_id', $param['product_id'])->select()->toArray();
            $priceIdArr = [];
            foreach ($price as $key => $value) {
                $id = $value['id'];
                $priceIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                if($value['rel_type']=='option'){
                    $value['rel_id'] = $optionIdArr[$value['rel_id']] ?? 0;
                }else if($value['rel_type']=='model_config'){
                    $value['rel_id'] = $modelConfigIdArr[$value['rel_id']] ?? 0;
                }else if($value['rel_type']=='package'){
                	continue;
                }
                $value['duration_id'] = $durationIdArr[$value['duration_id']] ?? 0;
                $r = $PriceModel->create($value);
                $priceIdArr[$id] = $r->id;
            }
		}
	}catch(\Exception $e){
		return $e->getMessage();
	}
});

// 每天定时任务,用于处理流量暂停/清零
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
		// 是否是开通日,开通日不清零流量
		$isCreateDay = false;
		if($billCycle == 'last_30days'){
			// 计算开通的日期
			$date = date('d', $v['active_time'] ?: $v['create_time']);
			
			if(date('Ymd') == date('Ymd', $v['active_time'] ?: $v['create_time'])){
				$isCreateDay = true;
			}
        }else{
        	$date = '01';
        }
        // 流量清零
        if($date == date('d') && !$isCreateDay){
        	$res = $Dcim->resetFlow(['id'=>$v['rel_id'], 'hostid'=>$v['id']]);

        	if($res['status'] == 200){
        		if($v['status'] == 'Suspended' && $v['suspend_type'] == 'overtraffic'){
        			$unsuspendRes = $HostModel->unsuspendAccount($v['id']);
        			if($unsuspendRes['status'] == 200){
        				$description = lang_plugins('mf_dcim_log_host_flow_clear_and_unsuspend_success', [
        					'{host}' => $v['name'],
        				]);
        			}else{
        				$description = lang_plugins('mf_dcim_log_host_flow_clear_but_unsuspend_fail', [
        					'{host}' 	=> $v['name'],
        					'{reason}' 	=> $unsuspendRes['msg'],
        				]);
        			}
        		}else{
        			$description = lang_plugins('mf_dcim_log_host_flow_clear_success', [
    					'{host}' => $v['name'],
    				]);
        		}
        	}else{
        		$description = lang_plugins('mf_dcim_log_host_flow_clear_fail', [
					'{host}' 	=> $v['name'],
					'{reason}' 	=> $res['msg'],
				]);
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
			                	'id'			=> $v['id'],
			                	'suspend_type'	=> 'overtraffic',
			                	'suspend_reason'=> lang_plugins('mf_dcim_flow_limit_desc', ['{total}'=>$total, '{used}'=>$used]),
			                ]);
			                if($suspendRes['status'] == 200){
			                	$description = lang_plugins('mf_dcim_log_host_over_flow_suspend_success', [
			                		'{host}' 	=> $v['name'],
			                		'{total}' 	=> $total,
			                		'{used}' 	=> $used,
			                	]);
			                }else{
			                	$description = lang_plugins('mf_dcim_log_host_over_flow_success_but_suspend_fail', [
			                		'{host}' 	=> $v['name'],
			                		'{total}' 	=> $total,
			                		'{used}' 	=> $used,
			                		'{reason}' 	=> $suspendRes['msg'],
			                	]);
			                }
			            }else if($overFlow['act'] == 2){
			            	$description = lang_plugins('mf_dcim_log_host_over_flow_limit_bw_success', [
		                		'{host}' 	=> $v['name'],
		                		'{total}' 	=> $total,
		                		'{used}' 	=> $used,
		                	]);
			            }else if($overFlow['act'] == 3){
			            	$description = lang_plugins('mf_dcim_log_host_over_flow_close_port_success', [
		                		'{host}' 	=> $v['name'],
		                		'{total}' 	=> $total,
		                		'{used}' 	=> $used,
		                	]);
			            }else{
			            	$description = '';
			            }
			        }else{
			        	$description = lang_plugins('mf_dcim_log_host_over_flow_fail', [
	                		'{host}' 	=> $v['name'],
	                		'{total}' 	=> $total,
	                		'{used}' 	=> $used,
	                		'{reason}' 	=> $overFlow['msg'],
	                	]);
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

// 购买流量包后
add_hook('flow_packet_order_paid', function($param){
	$hostId = $param['host_id'];
	$flow = $param['flow_packet']['capacity'];
	$moduleParam = $param['module_param'];

	if(!empty($moduleParam['server']) && $moduleParam['server']['module'] == 'mf_dcim'){
		$Dcim = new Dcim($moduleParam['server']);

		$hostLink = HostLinkModel::where('host_id', $hostId)->find();
		$billCycle = 'month';
		if(!empty($hostLink)){
			$configData = json_decode($hostLink['config_data'], true);
			$billCycle = $configData['flow']['other_config']['bill_cycle'] ?? 'month';
		}

		$res = $Dcim->addTempTraffic([
			'id'		=> $hostLink['rel_id'] ?? 0,
			'type'		=> $billCycle,
			'traffic'	=> $flow,
			'hostid'	=> $hostId,
		]);
		if($res['status'] == 200){
			$description = lang_plugins('mf_dcim_log_buy_flow_packet_success', [
				'{host}'	=> 'host#'.$hostId.'#'.$moduleParam['host']['name'].'#',
				'{order}'	=> '#'.$param['order_id'],
				'{flow}'	=> $flow.'G',
			]);

            // 如果是流量暂停在检查流量
	        if(isset($res['act']) && $res['act'] == 1 && $moduleParam['host']['status'] == 'Suspended' && $moduleParam['host']['suspend_type'] == 'overtraffic'){
		        if($moduleParam['host']['due_time'] == 0 || time() < $moduleParam['host']['due_time']){
		        	$result = $moduleParam['host']->unsuspendAccount($hostId);
	                if ($result['status'] == 200){
                        $description .= lang_plugins('mf_dcim_log_buy_flow_packet_and_unsuspend_success');
                    }else{
                        $description .= lang_plugins('mf_dcim_log_buy_flow_packet_and_unsuspend_fail', ['{reason}'=>$result['msg']]);
                    }
		        }
		    }
        }else{
        	$description = lang_plugins('mf_dcim_log_buy_flow_packet_remote_fail', [
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
				return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_buy_flow_packet')];
			}
		}
	}catch(PDOException $e){
		
	}
});

// 获取产品转移信息
add_hook('host_transfer_info', function($param){
	if($param['module'] == 'mf_dcim'){
		
	}
});

// 产品转移
add_hook('host_transfer', function($param){
	if($param['module'] == 'mf_dcim'){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->hostTransfer($param);
	}
});

