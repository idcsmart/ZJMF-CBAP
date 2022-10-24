<?php 
namespace server\idcsmart_dcim\logic;

use server\idcsmart_dcim\idcsmart_dcim\Dcim;
use server\idcsmart_dcim\model\HostLinkModel;
use server\idcsmart_dcim\model\ImageModel;
use server\idcsmart_dcim\model\HostImageLinkModel;
use app\common\model\HostModel;
use think\facade\Cache;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;

class CloudLogic
{
	protected $id = 0;   				// DCIM ID
	protected $idcsmartCloud = null;	// DCIM操作类型
	protected $hostModel = [];			// 产品模型
	protected $isClient = false;        // 是否是客户操作

	public function __construct($hostId){
		$HostLinkModel = HostLinkModel::where('host_id', $hostId)->find();
		$this->id = $HostLinkModel['rel_id'] ?? 0;

		$HostModel = HostModel::find($hostId);
		if(empty($HostModel)){
			throw new \Exception(lang_plugins('host_is_not_exist'));
		}
		// 是否是魔方云模块
		if($HostModel->getModule() != 'idcsmart_dcim'){
			throw new \Exception(lang_plugins('can_not_do_this'));
		}
		// 获取模块通用参数
		$params = $HostModel->getModuleParams();
		if(empty($params['server'])){
			throw new \Exception(lang_plugins('host_not_link_server'));
		}
		$this->idcsmartCloud = new Dcim($params['server']);
		$this->hostModel = $params['host'];
		$this->server = $params['server'];
		$this->hostLinkModel = $HostLinkModel;

		// 前台用户验证
		$app = app('http')->getName();
        if($app == 'home'){
        	if($HostModel['client_id'] != get_client_id()){
        		throw new \Exception(lang_plugins('host_is_not_exist'));
        	}
        }
        $this->isClient = $app == 'home';
	}

	/**
	 * 时间 2022-06-22
	 * @title 获取电源状态
	 * @desc 获取电源状态
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.status - 实例状态(on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status(){
		$res = $this->idcsmartCloud->getReinstallStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
		if($res['status'] == 200){
			if(isset($res['data']['task_type'])){
				$status = [
					'status' => 'operating',
					'desc'   => lang_plugins('operating')
				];
			}else{
				$res = $this->idcsmartCloud->powerStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
				if($res['status'] == 200){
					if($res['msg'] == 'nonsupport'){
						$status = [
							'status' => 'fault',
							'desc'   => lang_plugins('fault'),
						];
					}else if($res['msg'] == 'on'){
		                $status = [
							'status' => 'on',
							'desc'   => lang_plugins('on'),
						];
		            }else if($res['msg'] == 'off'){
		                $status = [
							'status' => 'off',
							'desc'   => lang_plugins('off')
						];
		            }else{
		                $status = [
							'status' => 'fault',
							'desc'   => lang_plugins('fault'),
						];
		            }
				}else{
					$status = [
						'status' => 'fault',
						'desc'   => lang_plugins('fault'),
					];
				}
			}
		}else{
			$status = [
				'status' => 'fault',
				'desc'   => lang_plugins('fault'),
			];
		}
		HostLinkModel::where('host_id', $this->hostModel['id'])->update(['power_status'=>$status['status']]);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => $status,
		];
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 开机
	 * @author hh
	 * @version v1
	 */
	public function on(){
		$res = $this->idcsmartCloud->on(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_boot_success')
			];

			$description = lang_plugins('log_host_start_boot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_boot_failed')
			];
			
			if($this->isClient){
				$description = lang_plugins('log_host_start_boot_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_boot_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
				$result['msg'] = $res['msg'];
			}
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 关机
	 * @author hh
	 * @version v1
	 */
	public function off(){
		$res = $this->idcsmartCloud->off(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_off_success')
			];

			$description = lang_plugins('log_host_start_off_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_off_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_off_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_off_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
				$result['msg'] = $res['msg'];
			}
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 重启
	 * @author hh
	 * @version v1
	 */
	public function reboot(){
		$res = $this->idcsmartCloud->reboot(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_reboot_success')
			];

			$description = lang_plugins('log_host_start_reboot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_reboot_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_reboot_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_reboot_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
				$result['msg'] = $res['msg'];
			}
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-07-01
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @author hh
	 * @version v1
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
		$res = $this->idcsmartCloud->vnc(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('success_message'),
				'data'	 => [],
			];

            if(strpos($this->server['url'], 'https://') !== false){
                $link_url = str_replace('https://', 'wss://', $this->server['url']);
            }else{
                $link_url = str_replace('http://', 'ws://', $this->server['url']);
            }
            // vnc不能包含管理员路径
            // $link_url = rtrim($link_url, '/');
            // if(substr_count($link_url, '/') > 2){
            //     $link_url = substr($link_url, 0, strrpos($link_url, '/'));
            // }
            $link_url .= '/websockify_'.$res['house_id'].'?token='.$res['token'];
            
            // 获取的东西放入缓存
            $cache = [
            	'vnc_url' => $link_url,
            	'vnc_pass'=> $res['pass'],
            	'password'=> aes_password_decode($this->hostLinkModel['password']),
            ];
            if($this->isClient){
                $result['data']['url'] = request()->domain().'/console/v1/idcsmart_dcim/'.$this->hostModel['id'].'/vnc';
            }else{
                $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/idcsmart_dcim/'.$this->hostModel['id'].'/vnc';
            }
            
            // 生成一个临时token
            $token = md5(rand_str(16));
            $cache['token'] = $token;

            Cache::set('idcsmart_dcim_vnc_'.$this->hostModel['id'], $cache, 30*60);
        	if(strpos($result['data']['url'], '?') !== false){
        		$result['data']['url'] .= '&tmp_token='.$token;
        	}else{
        		$result['data']['url'] .= '?tmp_token='.$token;
        	}
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('vnc_start_failed').$res['msg'],
			];
		}
		return $result;
	}

	/**
	 * 时间 2022-06-24
	 * @title 重置密码
	 * @desc 重置密码
	 * @author hh
	 * @version v1
	 * @param   string $param.password - 新密码
	 */
	public function resetPassword($param){
		$res = $this->idcsmartCloud->resetPassword(['id'=>$this->id, 'hostid'=>$this->hostModel['id'], 'crack_password'=>$param['password'] ]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_reset_password_success')
			];

			$description = lang_plugins('log_host_start_reset_password_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_reset_password_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_reset_password_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_reset_password_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
				$result['msg'] = $res['msg'];
			}
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-24
	 * @title 救援模式
	 * @desc 救援模式
	 * @author hh
	 * @version v1
	 * @param   int param.type - 指定救援系统类型(1=windows,2=linux) require
	 */
	public function rescue($param){
		$type = ['',2,1];

		$res = $this->idcsmartCloud->resuce(['id'=>$this->id, 'hostid'=>$this->hostModel['id'], 'type'=>$type[$param['type']] ]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_rescue_success')
			];

			$description = lang_plugins('log_host_start_rescue_success', ['{hostname}'=>$this->hostModel['name']]);

		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_rescue_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_rescue_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_rescue_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
				$result['msg'] = $res['msg'];
			}

		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-30
	 * @title 重装系统
	 * @desc 重装系统
	 * @author hh
	 * @version v1
	 * @param   int param.id - 产品ID require
	 * @param   int param.image_id - 镜像ID require
	 * @param   int param.password - 密码 require
	 * @param   int param.port - 端口 require
	 */
	public function reinstall($param){
		$image = ImageModel::find($param['image_id']);
		if(empty($image)){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
		}
		// 前台
		if($this->isClient){
			if($image['enable'] == 0){
				return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
			}
			if($image['charge'] == 1){
				$hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
				if(empty($hostImageLink)){
					return ['status'=>400, 'msg'=>lang_plugins('image_is_charge_please_buy')];
				}
			}
		}
		
		$post['id'] = $this->id;
		$post['hostid'] = $this->hostModel['id'];
		$post['mos'] = $image['rel_image_id'];
		$post['rootpass'] = $param['password'];
		$post['port'] = $param['port'];
		
		$res = $this->idcsmartCloud->reinstall($post);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'=>lang_plugins('start_reinstall_success'),
			];

			$update['image_id'] = $param['image_id'];
			$update['password'] = aes_password_encode($res['ospassword'] ?? $post['rootpass']);
			$update['port'] = $res['port'] ?? $post['port'];

			$this->hostLinkModel->update($update, ['host_id'=>$this->hostModel['id']]);

			$description = lang_plugins('log_host_start_reinstall_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('start_reinstall_failed'),
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_reinstall_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_reinstall_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
				$result['msg'] = $res['msg'];
			}
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取图表数据
	 * @desc 获取图表数据
	 * @author hh
	 * @version v1
	 * @param   int param.start_time - 开始秒级时间
	 * @return  array data.list - 图表数据
	 * @return  int data.list[].time - 时间(秒级时间戳)
	 * @return  float data.list[].in_bw - 进带宽
	 * @return  float data.list[].out_bw - 出带宽
	 * @return  string data.unit - 当前单位
	 */
	public function chart($param){
		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [
				'list' => [],
				'unit' => 'bps'
			],
		];

		$post = [];
		$post['id'] = $this->id;
		$post['hostid'] = $this->hostModel['id'];
		$post['reverse'] = 1;
		$post['type'] = 'server';
		$post['switch_id'] = $this->id;
		
		// 时间选择,起始结束
		$post['start_time'] = $this->hostModel['active_time'] ?: $this->hostModel['create_time'];
		if(isset($param['start_time']) && !empty($param['start_time'])){
			if($param['start_time'] >= $data['st']){
				$post['start_time'] = $param['start_time'];
			}
		}
		$post['start_time'] .= '000';

		$res = $this->idcsmartCloud->traffic($post);
		if(isset($res['y_unit'])){
			$result['data']['unit'] = $res['y_unit'];
		}
		if(isset($res['in'])){
			foreach($res['in'] as $k=>$v){
				$result['data']['list'][$k] = [
					'time'	 => $k/1000,
					'in_bw'  => round($v, 2),
					'out_bw' => 0,
				];
            }
		}
		if(isset($res['out'])){
			foreach($res['out'] as $k=>$v){
				if(!isset($result['data']['list'][$k])){
					$result['data']['list'][$k] = [
						'time'	 => $k/1000,
						'in_bw'  => 0,
						'out_bw' => round($v, 2),
					];
				}else{
					$result['data']['list'][$k]['out_bw'] = round($v, 2);
				}
            }
		}
		$result['data']['list'] = array_values($result['data']['list']);
		return $result;
	}


	/**
	 * 时间 2022-06-30
	 * @title 网络流量
	 * @desc 网络流量
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.total -总流量
	 * @return  string data.used -已用流量
	 * @return  string data.leave - 剩余流量
	 * @return  string data.reset_flow_date - 流量归零时间
	 */
	public function flowDetail(){
		$post = [];
		$post['id'] = $this->id;
		$post['hostid'] = $this->hostModel['id'];
		$post['unit'] = 'GB';

		$res = $this->idcsmartCloud->flow($post);
		if($res['status'] == 200){
			$data = $res['data'][ $this->hostLinkModel['traffic_bill_type'] ];

			$total = $res['limit'] > 0 ? $res['limit'] + $res['temp_traffic'] : 0;
			$used = round($total * str_replace('%', '', $data['used_percent']) / 100, 2);
			$leave = round($total - $used, 2);

			if($this->hostLinkModel['traffic_bill_type'] == 'last_30days'){
				$resetFlowDay = date('d', strtotime($this->hostModel['active_time'])) ?: 1;
			}else{
				$resetFlowDay = 1;
			}
			$time = strtotime(date('Y-m-'.$resetFlowDay.' 00:00:00'));
			if(time() > $time){
				$time = strtotime(date('Y-m-'.$resetFlowDay.' 00:00:00') .' +1 month');
			}

			$result = [
				'status'=>200,
				'msg'=>lang_plugins('success_message'),
				'data'=>[
					'total'=>$total == 0 ? lang_plugins('not_limited') : $total.'GB',
					'used'=>$used.'GB',
					'leave'=>$total == 0 ? lang_plugins('not_limited') : $leave.'GB',
					'reset_flow_date'=>date('Y-m-d', $time)
				]
			];
		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('flow_info_get_failed')
			];
		}
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取IP列表
	 * @desc 获取IP列表
	 * @author hh
	 * @version v1
	 */
	public function ipList($param)
	{
		$param['page'] = $param['page']>0 ? $param['page'] : 1;
		$param['limit'] = $param['limit']>0 ? $param['limit'] : 20;

		$data = [];
		$count = 0;

		$post = [];
		$post['id'] = $this->id;

		// 获取当前所有IP
		$res = $this->idcsmartCloud->detail($post);

		if($res['status'] == 200 && isset($res['ip']['subnet_ip'])){
			foreach($res['ip']['subnet_ip'] as $v){
				$data[] = [
					'ip' => $v['ipaddress'],
					'subnet_mask'=>$v['subnetmask'] ?? '',
					'gateway'=>$v['gateway'] ?? '',
				];
			}
		}
		
		$count = count($data);
		$data  = array_slice($data, ($param['page']-1)*$param['limit'], $param['limit']);
		return ['list'=>$data, 'count'=>$count];
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取DCIM远程信息
	 * @desc 获取DCIM远程信息
	 * @author hh
	 * @version v1
	 */
	public function detail()
	{
		$data = [
			'username'=>'',
			'password'=>aes_password_decode($this->hostLinkModel['password']),
			'port'=>$this->hostLinkModel['port'],
			'ip_num'=>!empty($this->hostModel['ip']) ? 1 : 0,
		];

		$post = [];
		$post['id'] = $this->id;

		// 获取当前所有IP
		$res = $this->idcsmartCloud->detail($post);

		if($res['status'] == 200){
			$data['username'] = $res['server']['osusername'];
			$data['password'] = $res['server']['ospassword'];
			$data['port'] = $res['server']['port'];
			$data['ip_num'] = $res['ip']['ipcount']+1;
		}
		
		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return $result;
	}
	



}
