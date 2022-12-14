<?php

use app\common\model\ServerModel;
use app\common\model\HostModel;
use server\idcsmart_dcim\model\DataCenterModel;
use server\idcsmart_dcim\model\HostImageLinkModel;
use server\idcsmart_dcim\model\HostLinkModel;
use server\idcsmart_dcim\model\ImageGroupModel;
use server\idcsmart_dcim\model\ImageModel;
use server\idcsmart_dcim\model\PackageModel;
use server\idcsmart_dcim\idcsmart_dcim\Dcim;

add_hook('after_product_delete', function($param){
	try{
		// 加入异常,有可能表不存在
		$imageId = ImageModel::where('product_id', $param['id'])->column('id');

		PackageModel::where('product_id', $param['id'])->delete();
		ImageGroupModel::where('product_id', $param['id'])->delete();
		ImageModel::where('product_id', $param['id'])->delete();
		DataCenterModel::where('product_id', $param['id'])->delete();

		if(!empty($imageId)){
			HostImageLinkModel::whereIn('image_id', $imageId)->delete();
		}
	}catch(\Exception $e){
		
	}
});

// TODO 增加日志
add_hook('daily_cron', function($param){
	// 处理清零,超额暂停
	$host = HostLinkModel::alias('hl')
		->field('h.id,h.name,h.active_time,h.create_time,h.server_id,h.status,h.suspend_type,hl.traffic_bill_type,hl.rel_id')
		->join('host h', 'hl.host_id=h.id')
		->whereIn('h.status', 'Active,Suspended')
		->where('hl.rel_id', '>', 0)
		->select()
		->toArray();

	$HostModel = new HostModel();
	$dcim = [];
	$date = date('d');
	foreach($host as $v){
		if(!isset($dcim[$v['server_id']])){
			$ServerModel = ServerModel::find($v['server_id']);
			$ServerModel['password'] = aes_password_decode($ServerModel['password']);

			$Dcim = new Dcim($ServerModel);
			$dcim[$v['server_id']] = $Dcim;
		}else{
			$Dcim = $dcim[ $v['server_id'] ];
		}
		if($v['traffic_bill_type'] == 'last_30days'){
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
				$data = $flow['data'][ $v['traffic_bill_type'] ];
				$percent = str_replace('%', '', $data['used_percent']);

				$total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
				$used = round($total * $percent / 100, 2);
				if($percent >= 100){
					// 执行超额
					$post = [];
					$post['id'] = $v['rel_id'];
			        $post['type'] = $v['traffic_bill_type'];
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
        sleep(2);
	}
});

