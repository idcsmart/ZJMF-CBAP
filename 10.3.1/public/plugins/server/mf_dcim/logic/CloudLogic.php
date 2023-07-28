<?php 
namespace server\mf_dcim\logic;

use server\mf_dcim\idcsmart_dcim\Dcim;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\model\ImageModel;
use server\mf_dcim\model\HostImageLinkModel;
use server\mf_dcim\model\DurationModel;
use server\mf_dcim\model\LineModel;
use server\mf_dcim\model\OptionModel;
use server\mf_dcim\model\ConfigLimitModel;
use server\mf_dcim\model\ConfigModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ClientModel;
use think\facade\Cache;

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
			throw new \Exception(lang_plugins('mf_dcim_host_not_found'));
		}
		// 是否是魔方云模块
		if($HostModel->getModule() != 'mf_dcim'){
			throw new \Exception(lang_plugins('mf_dcim_can_not_do_this'));
		}
		// 获取模块通用参数
		$params = $HostModel->getModuleParams();
		if(empty($params['server'])){
			throw new \Exception(lang_plugins('mf_dcim_host_not_link_server'));
		}
		$this->idcsmartCloud = new Dcim($params['server']);
		$this->hostModel = $params['host'];
		$this->server = $params['server'];
		$this->hostLinkModel = $HostLinkModel;

		// 前台用户验证
		$app = app('http')->getName();
        if($app == 'home'){
        	if($HostModel['client_id'] != get_client_id()){
        		throw new \Exception(lang_plugins('mf_dcim_host_not_found'));
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
					'desc'   => lang_plugins('mf_dcim_operating')
				];
			}else{
				$res = $this->idcsmartCloud->powerStatus(['id'=>$this->id, 'hostid'=>$this->hostModel['id']]);
				if($res['status'] == 200){
					if($res['msg'] == 'nonsupport'){
						$status = [
							'status' => 'fault',
							'desc'   => lang_plugins('mf_dcim_fault'),
						];
					}else if($res['msg'] == 'on'){
		                $status = [
							'status' => 'on',
							'desc'   => lang_plugins('mf_dcim_on'),
						];
		            }else if($res['msg'] == 'off'){
		                $status = [
							'status' => 'off',
							'desc'   => lang_plugins('mf_dcim_off')
						];
		            }else{
		                $status = [
							'status' => 'fault',
							'desc'   => lang_plugins('mf_dcim_fault'),
						];
		            }
				}else{
					$status = [
						'status' => 'fault',
						'desc'   => lang_plugins('mf_dcim_fault'),
					];
				}
			}
		}else{
			$status = [
				'status' => 'fault',
				'desc'   => lang_plugins('mf_dcim_fault'),
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
				'msg'    => lang_plugins('mf_dcim_start_boot_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_boot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_dcim_start_boot_fail')
			];
			
			if($this->isClient){
				$description = lang_plugins('mf_dcim_log_host_start_boot_fail', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('mf_dcim_log_admin_host_start_boot_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
				'msg'    => lang_plugins('mf_dcim_start_off_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_off_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_dcim_start_off_fail')
			];

			if($this->isClient){
				$description = lang_plugins('mf_dcim_log_host_start_off_fail', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('mf_dcim_log_admin_host_start_off_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
				'msg'    => lang_plugins('mf_dcim_start_reboot_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_reboot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_dcim_start_reboot_fail')
			];

			if($this->isClient){
				$description = lang_plugins('mf_dcim_log_host_start_reboot_fail', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('mf_dcim_log_admin_host_start_reboot_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
                $result['data']['url'] = request()->domain().'/console/v1/mf_dcim/'.$this->hostModel['id'].'/vnc';
            }else{
                $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/mf_dcim/'.$this->hostModel['id'].'/vnc';
            }
            
            // 生成一个临时token
            $token = md5(rand_str(16));
            $cache['token'] = $token;

            Cache::set('mf_dcim_vnc_'.$this->hostModel['id'], $cache, 30*60);
        	if(strpos($result['data']['url'], '?') !== false){
        		$result['data']['url'] .= '&tmp_token='.$token;
        	}else{
        		$result['data']['url'] .= '?tmp_token='.$token;
        	}
        	if(isset($param['more']) && $param['more'] == 1){
                $result['data'] = array_merge($result['data'], $cache);
            }
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_dcim_vnc_start_fail'),
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
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
		// 非代理时验证手机号
		if($this->isClient && isset($config['data']['reset_password_sms_verify']) && $config['data']['reset_password_sms_verify'] && !request()->is_api){
			$ClientModel = new ClientModel();
			$res = $ClientModel->verifyOldPhone(['code'=>$param['code'] ?? '']);
			if($res['status'] == 400){
				return $res;
			}
		}
		$res = $this->idcsmartCloud->resetPassword(['id'=>$this->id, 'hostid'=>$this->hostModel['id'], 'crack_password'=>$param['password'] ]);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_dcim_start_reset_password_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_reset_password_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_dcim_start_reset_password_fail')
			];

			if($this->isClient){
				$description = lang_plugins('mf_dcim_log_host_start_reset_password_fail', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('mf_dcim_log_admin_host_start_reset_password_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
				'msg'    => lang_plugins('mf_dcim_start_rescue_success')
			];

			$description = lang_plugins('mf_dcim_log_host_start_rescue_success', ['{hostname}'=>$this->hostModel['name']]);

		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_dcim_start_rescue_fail')
			];

			if($this->isClient){
				$description = lang_plugins('mf_dcim_log_host_start_rescue_fail', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('mf_dcim_log_admin_host_start_rescue_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
		}
		// 前台
		if($this->isClient){
			if($image['enable'] == 0){
				return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
			}
			if($image['charge'] == 1){
				$hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
				if(empty($hostImageLink)){
					return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_is_charge_please_buy')];
				}
			}
			$ConfigModel = new ConfigModel();
			$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
			// 非代理时验证手机号
			if(isset($config['data']['reinstall_sms_verify']) && $config['data']['reinstall_sms_verify'] && !request()->is_api){
				$ClientModel = new ClientModel();
				$res = $ClientModel->verifyOldPhone(['code'=>$param['code'] ?? '']);
				if($res['status'] == 400){
					return $res;
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
				'msg'=>lang_plugins('mf_dcim_start_reinstall_success'),
			];

			$update['image_id'] = $param['image_id'];
			$update['password'] = aes_password_encode($res['ospassword'] ?? $post['rootpass']);
			$update['port'] = $res['port'] ?? $post['port'];

			$this->hostLinkModel->update($update, ['host_id'=>$this->hostModel['id']]);

			$description = lang_plugins('mf_dcim_log_host_start_reinstall_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status'=>400,
				'msg'=>lang_plugins('mf_dcim_start_reinstall_fail'),
			];

			if($this->isClient){
				$description = lang_plugins('mf_dcim_log_host_start_reinstall_fail', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('mf_dcim_log_admin_host_start_reinstall_fail', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
			if($param['start_time'] >= $this->hostModel['create_time']){
				$post['start_time'] = $param['start_time'];
			}else{
				$post['start_time'] = $this->hostModel['create_time'];
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
			$configData = json_decode($this->hostLinkModel['config_data'], true);

			$data = $res['data'][ $configData['flow']['other_config']['bill_cycle'] ?? 'month'];
			$percent = str_replace('%', '', $data['used_percent']);
			
			$total = $res['limit'] > 0 ? $res['limit'] + $res['temp_traffic'] : 0;
			$used =  round($total * $percent / 100, 2);
			$leave = round($total - $used, 2);

			if(isset($configData['flow']['other_config']['bill_cycle']) && $configData['flow']['other_config']['bill_cycle'] == 'last_30days'){
				$resetFlowDay = date('d', $this->hostModel['active_time']) ?: 1;
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
				'msg'=>lang_plugins('mf_dcim_flow_info_get_failed')
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

			HostLinkModel::where('host_id', $this->hostModel['id'])->update(['password'=>aes_password_encode($data['password']) ]);
		}
		
		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return $result;
	}
	
	 /**
     * 时间 2022-09-25
     * @title 
     * @desc 
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function calIpNumPrice($param){
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime  = $this->hostModel['due_time'] - time();

    	$configData = json_decode($this->hostLinkModel['config_data'], true);

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_this_duration_to_upgrade')];
    	}
    	// 检查之前的线路是否还存在
    	$line = LineModel::where('id', $configData['line']['id'])->find();
    	if(empty($line)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found_to_upgrade_ip_num')];
    	}
    	if(isset($configData['ip']['value']) && $configData['ip']['value'] == $param['ip_num']){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_ip_num_not_change')];
    	}
    	$OptionModel = new OptionModel();
    	$optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $duration['id']);
        if(!$optionDurationPrice['match']){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_ip_num_error') ];
        }
        $ipData = [
            'value' => $param['ip_num'],
            'price' => $optionDurationPrice['price'] ?? 0
        ];

        $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $configData['ip']['value'] ?? -1, $duration['id']);
        $oldPrice = $currentOptionDurationPrice['price'] ?? 0;
        $price = $optionDurationPrice['price'] ?? 0;

        $oldPrice = bcmul($oldPrice, $duration['price_factor']);
        $price = bcmul($price, $duration['price_factor']);

        $description = lang_plugins('mf_dcim_upgrade_ip_num', [
        	'{old}' => $configData['ip']['value'] ?? 0,
        	'{new}' => $param['ip_num'],
        ]);
        $priceDifference = bcsub($price, $oldPrice);
        if($this->hostModel['billing_cycle_time']>0){
        	$price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
        }else{
        	$price = $priceDifference;
        }
		
        $price = max(0, $price);
        $price = amount_format($price);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
                'ip_data'=>$ipData
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成公网IP订单
     * @desc 生成公网IP订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
	 * @param   array remove_disk_id - 要取消订购的磁盘ID
	 * @param   array add_disk - 新增磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createIpNumOrder($param){
        $res = $this->calIpNumPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'upgrade_ip_num',
                'ip_data'    => $res['data']['ip_data'],
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2022-09-25
     * @title 计算产品配置升级价格
     * @desc 计算产品配置升级价格
     * @author hh
     * @version v1
     * @param   int bw - 带宽
     * @param   int flow - 流量包
     * @param   int peak_defence - 防御峰值
     */
    public function calCommonConfigPrice($param){
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime  = $this->hostModel['due_time'] - time();

    	$configData = json_decode($this->hostLinkModel['config_data'], true);

    	$newConfigData = [];

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_this_duration_to_upgrade')];
    	}
    	$OptionModel = new OptionModel();

    	$oldPrice = 0;  // 老价格
    	$price = 0;     // 新价格
    	$description = []; // 描述

        // 检查之前的线路是否还存在
    	$line = LineModel::where('id', $configData['line']['id'])->find();
    	if(empty($line)){
    		// 不支持bw/flow/peak_defence升降机
    		if($configData['line']['bill_type'] == 'bw' && isset($param['bw']) && is_numeric($param['bw']) && $param['bw'] != $configData['bw']['value']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_bw_upgrade')];
    		}
    		if($configData['line']['bill_type'] == 'flow' && isset($param['flow']) && is_numeric($param['flow']) && $param['flow'] != $configData['flow']['value']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_flow_upgrade')];
    		}
    		if(isset($param['peak_defence']) && isset($configData['defence']['value']) && is_numeric($param['peak_defence']) && $param['peak_defence'] != $configData['defence']['value']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_support_defence_upgrade')];
    		}

    		// 线路的都不能升级
    		$param['bw'] = null;
    		$param['flow'] = null;
    		$param['peak_defence'] = null;
    	}else{
    		// 线路存在的情况
    		if($line['bill_type'] == 'bw'){
    			$param['flow'] = null;
                // 获取带宽周期价格
                if(isset($param['bw']) && !empty($param['bw']) && $param['bw'] != $configData['bw']['value']){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $param['bw'], $duration['id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_bw_error') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('mf_dcim_bw'),
                        'value' => $param['bw'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $newConfigData['bw'] = [
                        'value' => $param['bw'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                        'other_config' => $optionDurationPrice['option']['other_config'],
                    ];

                    $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_BW, $line['id'], $configData['bw']['value'] ?? -1, $duration['id']);

                    $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            		$price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            		$description[] = sprintf("带宽: %d => %d", $configData['bw']['value'], $param['bw']);
                }
            }else if($line['bill_type'] == 'flow'){
            	$param['bw'] = null;
                // 获取流量周期价格
                if(isset($param['flow']) && !empty($param['flow']) && $param['flow'] != $configData['flow']['value']){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $duration['id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_flow_not_found') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('mf_dcim_flow'),
                        'value' => $param['flow'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                    ];

                    $newConfigData['flow'] = [
                        'value' => $param['flow'],
                        'price' => $optionDurationPrice['price'] ?? 0,
                        'other_config' => $optionDurationPrice['option']['other_config'],
                    ];

                    $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $configData['flow']['value'] ?? -1, $duration['id']);

                    $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            		$price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            		$description[] = sprintf("流量: %d => %d", $configData['flow']['value'], $param['flow']);
                }
            }
            // 防护
            if($line['defence_enable'] == 1 && isset($param['peak_defence']) && !empty($param['peak_defence']) && $param['peak_defence'] != ($configData['defence']['value'] ?? 0)){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
                }
                $preview[] = [
                    'name'  => lang_plugins('mf_dcim_peak_defence'),
                    'value' => $param['peak_defence'],
                    'price' => $optionDurationPrice['price'] ?? 0,
                ];

                $newConfigData['defence'] = [
                    'value' => $param['peak_defence'],
                    'price' => $optionDurationPrice['price'] ?? 0
                ];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $configData['defence']['value'] ?? 0, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            	$price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            	$description[] = sprintf("防御峰值: %d => %d", $configData['defence']['value'] ?? 0, $param['peak_defence']);
            }
            // 公网IP
            if(isset($param['ip_num']) && !empty($param['ip_num']) && $param['ip_num'] != ($configData['ip']['value'] ?? 0)){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_peak_defence_not_found') ];
                }
                $preview[] = [
                    'name'  => '公网IP',
                    'value' => $param['ip_num'].'个',
                    'price' => $optionDurationPrice['price'] ?? 0,
                ];

                $newConfigData['ip'] = [
                    'value' => $param['ip_num'],
                    'price' => $optionDurationPrice['price'] ?? 0
                ];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $configData['ip']['value'] ?? 0, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            	$price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            	$description[] = sprintf("公网IP数量: %d => %d", $configData['ip']['value'] ?? 0, $param['ip_num']);
            }
    	}
    	if(empty($newConfigData)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_change_config')];
    	}
    	// 计算价格系数
    	if($duration['price_factor'] != 1){
    		$oldPrice = bcmul($oldPrice, $duration['price_factor']);
	    	$price = bcmul($price, $duration['price_factor']);

	    	foreach($newConfigData as $k=>$v){
	    		if(isset($v['price'])){
	    			$newConfigData[$k]['price'] = bcmul($v['price'], $duration['price_factor']);
	    		}
	    	}
    	}

        $description = implode("\r\n", $description);
        $priceDifference = bcsub($price, $oldPrice);
        if($this->hostModel['billing_cycle_time']>0){
        	$price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
        }else{
        	$price = $priceDifference;
        }
		
        $price = max(0, $price);
        $price = amount_format($price);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
                'new_config_data'=>$newConfigData
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成磁盘扩容订单
     * @desc 生成磁盘扩容订单
     * @author hh
     * @version v1
	 * @param   int id - 产品ID require
     * @param   int bw - 带宽
     * @param   int flow - 流量包
     * @param   int peak_defence - 防御峰值
	 * @return  string data.id - 订单ID
     */
    public function createCommonConfigOrder($param){
        $res = $this->calCommonConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }
        $configData = json_decode($this->hostLinkModel['config_data'], true);

        $param['data_center_id'] = $this->hostLinkModel['data_center_id'];
        $param['line_id'] = $configData['line']['id'] ?? 0;
        $param['model_config_id'] = $configData['model_config']['id'];
        if($configData['line']['bill_type'] == 'bw'){
        	if(isset($param['flow'])) unset($param['flow']);
        }else{
        	if(isset($param['bw'])) unset($param['bw']);
        }

        $ConfigLimitModel = new ConfigLimitModel();
        $checkConfigLimit  = $ConfigLimitModel->checkConfigLimit($this->hostModel['product_id'], $param);
        if($checkConfigLimit['status'] == 400){
        	return $checkConfigLimit;
        }

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'upgrade_common_config',
                'new_config_data'    => $res['data']['new_config_data'],
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }


}
