<?php 
namespace server\mf_cloud\logic;

use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\HostImageLinkModel;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\model\DiskModel;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\LineModel;
use server\mf_cloud\model\VpcNetworkModel;
use server\mf_cloud\model\ConfigLimitModel;
use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\model\RecommendConfigUpgradeRangeModel;
use server\mf_cloud\model\PriceModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ClientModel;
use think\facade\Cache;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;

class CloudLogic
{
	protected $id = 0;   				// 魔方云ID
	protected $idcsmartCloud = null;	// 魔方云操作类型
	protected $hostModel = [];			// 产品模型
	protected $isClient = false;        // 是否是客户操作

	public function __construct($hostId){
		$HostLinkModel = HostLinkModel::where('host_id', $hostId)->find();
		$this->id = $HostLinkModel['rel_id'] ?? 0;

		$HostModel = HostModel::find($hostId);
		if(empty($HostModel) || $HostModel['is_delete']){
			throw new \Exception(lang_plugins('host_is_not_exist'));
		}
		// 是否是魔方云模块
		if($HostModel->getModule() != 'mf_cloud'){
			throw new \Exception(lang_plugins('can_not_do_this'));
		}
		// 获取模块通用参数
		$params = $HostModel->getModuleParams();
		if(empty($params['server'])){
			throw new \Exception(lang_plugins('host_not_link_server'));
		}

		$hash = ToolLogic::formatParam($params['server']['hash']);

		$this->idcsmartCloud = new IdcsmartCloud($params['server']);
		$this->idcsmartCloud->setIsAgent(isset($hash['account_type']) && $hash['account_type'] == 'agent');

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
	public function status()
	{
		$res = $this->idcsmartCloud->cloudStatus($this->id);
		if($res['status'] == 200){
			if(in_array($res['data']['status'], ['on','wait_reboot','paused'])){
				$status = [
					'status' => 'on',
					'desc'   => lang_plugins('on'),
				];
			}else if($res['data']['status'] == 'off'){
				$status = [
					'status' => 'off',
					'desc'   => lang_plugins('off')
				];
			}else if($res['data']['status'] == 'suspend'){
				$status = [
					'status' => 'suspend',
					'desc'	 => lang_plugins('suspend'),
				];
			}else if(in_array($res['data']['status'], ['task','cold_migrate','hot_migrate'])){
				$status = [
					'status' => 'operating',
					'desc'   => lang_plugins('operating')
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
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function on()
	{
		$res = $this->idcsmartCloud->cloudOn($this->id);
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
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function off()
	{
		$res = $this->idcsmartCloud->cloudOff($this->id);
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
	 * @title 强制关机
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function hardOff()
	{
		$res = $this->idcsmartCloud->cloudHardOff($this->id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_hard_off_success')
			];

			$description = lang_plugins('log_host_start_hard_off_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_hard_off_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_hard_off_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_hard_off_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function reboot()
	{
		$res = $this->idcsmartCloud->cloudReboot($this->id);
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
	 * 时间 2022-06-22
	 * @title 强制重启
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function hardReboot()
	{
		$res = $this->idcsmartCloud->cloudHardReboot($this->id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_hard_reboot_success')
			];

			$description = lang_plugins('log_host_start_hard_reboot_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_hard_reboot_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_reboot_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_hard_reboot_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @param   int param.more 0 是否获取更多返回(0=否,1=是)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.url - 控制台地址
	 * @return  string data.vnc_url - vncwebsocket地址
	 * @return  string data.vnc_pass - VNC密码
	 * @return  string data.password - 实例密码
	 * @return  string data.token - 临时令牌
	 */
	public function vnc($param)
	{
		$res = $this->idcsmartCloud->cloudVnc($this->id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('success_message'),
				'data'	 => [],
			];

			if(!empty($res['data']['vnc_url_http']) && !empty($res['data']['vnc_url_https'])){
                // 外部vnc地址
                if(request()->scheme() == 'https'){
                    $result['data']['url'] = $res['data']['vnc_url_https'];
                }else{
                    $result['data']['url'] = $res['data']['vnc_url_http'];
                }

                $cache = [];
            }else{
                if(strpos($res['data']['vnc_url'], 'wss://') === 0 || strpos($res['data']['vnc_url'], 'ws://') === 0){
                    $link_url = $res['data']['vnc_url'];
                }else{
                    if(strpos($this->server['url'], 'https://') !== false){
                        $link_url = str_replace('https://', 'wss://', $this->server['url']);
                    }else{
                        $link_url = str_replace('http://', 'ws://', $this->server['url']);
                    }
                    // vnc不能包含管理员路径
                    $link_url = rtrim($link_url, '/');
                    if(substr_count($link_url, '/') > 2){
                        $link_url = substr($link_url, 0, strrpos($link_url, '/'));
                    }
                    $link_url .= '/cloud_ws'.$res['data']['path'].'?token='.$res['data']['token'];
                }
                // 获取的东西放入缓存
                $cache = [
                	'vnc_url' => $link_url,
                	'vnc_pass'=>$res['data']['vnc_pass'],
                	'password'=>$res['data']['password'],
                ];
                if($this->isClient){
                    $result['data']['url'] = request()->domain().'/console/v1/mf_cloud/'.$this->hostModel['id'].'/vnc';
                }else{
                    $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/mf_cloud/'.$this->hostModel['id'].'/vnc';
                }
            }
            // 生成一个临时token
            $token = md5(rand_str(16));
            $cache['token'] = $token;

            Cache::set('mf_cloud_vnc_'.$this->hostModel['id'], $cache, 30*60);
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
				'msg'    => lang_plugins('vnc_start_failed')
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
	 * @param   string param.password - 新密码 require
	 * @param   string param.code - 二次验证验证码
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function resetPassword($param)
	{
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
		$res = $this->idcsmartCloud->cloudResetPassword($this->id, $param['password']);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_reset_password_success'),
			];

			HostLinkModel::update(['ssh_key_id'=>0], ['host_id'=>$this->hostModel['id']]);

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
	 * @param   string param.password - 救援系统临时密码 require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function rescue($param)
	{
		$res = $this->idcsmartCloud->cloudRescue($this->id, ['type'=>$param['type'], 'temp_pass'=>$param['password']]);
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
	 * 时间 2022-06-24
	 * @title 退出救援模式
	 * @desc 退出救援模式
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function exitRescue()
	{
		$res = $this->idcsmartCloud->cloudExitRescue($this->id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_exit_rescue_success')
			];

			$description = lang_plugins('log_host_start_exit_rescue_success', ['{hostname}'=>$this->hostModel['name']]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_exit_rescue_failed')
			];

			if($this->isClient){
				$description = lang_plugins('log_host_start_exit_rescue_failed', ['{hostname}'=>$this->hostModel['name']]);
			}else{
				$description = lang_plugins('log_admin_host_start_exit_rescue_failed', ['{hostname}'=>$this->hostModel['name'], '{reason}'=>$res['msg'] ]);
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
	 * @param   int param.password - 密码 密码和ssh密钥ID,必须选择一种
	 * @param   int param.ssh_key_id - ssh密钥ID 密码和ssh密钥ID,必须选择一种
	 * @param   int param.port - 端口 require
	 * @param   string param.code - 二次验证验证码
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function reinstall($param)
	{
		// 请求数据
		$post = [];
		// 更新数据
		$update = [];
		$image = ImageModel::find($param['image_id']);
		if(empty($image) || $this->hostModel['product_id'] != $image['product_id']){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
		}
		$ConfigModel = new ConfigModel();
		$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);

		// 前台
		if($this->isClient){
			if($image['enable'] == 0){
				return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
			}
			if($image['charge'] == 1 && $image['price']>0){
				$hostImageLink = HostImageLinkModel::where('host_id', $this->hostModel['id'])->where('image_id', $image['id'])->find();
				if(empty($hostImageLink)){
					return ['status'=>400, 'msg'=>lang_plugins('image_is_charge_please_buy')];
				}
			}
			// 非代理时验证手机号
			if(isset($config['data']['reinstall_sms_verify']) && $config['data']['reinstall_sms_verify'] && !request()->is_api){
				$ClientModel = new ClientModel();
				$res = $ClientModel->verifyOldPhone(['code'=>$param['code'] ?? '']);
				if($res['status'] == 400){
					return $res;
				}
			}
		}
		$post['os'] = $image['rel_image_id'];
		$update['image_id'] = $param['image_id'];
		
		if(isset($param['password']) && !empty($param['password'])){
			$post['password'] = $param['password'];
			$post['password_type'] = 0;

			$update['password'] = aes_password_encode($param['password']);
			$update['ssh_key_id'] = 0;
		}else{
			if($config['data']['support_ssh_key'] == 0){
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_ssh_key')];
			}
			if(stripos($image['name'], 'win') !== false){
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_windows_cannot_use_ssh_key')];
			}
			// 先获取当前实例详情
			$detail = $this->idcsmartCloud->cloudDetail($this->id);
			if($detail['status'] != 200){
				return ['status'=>400, 'msg'=>$detail['msg'] ?: lang_plugins('start_reinstall_failed')];
			}
			// 使用密钥
			$sshKey = IdcsmartSshKeyModel::where('id', $param['ssh_key_id'] ?? 0)->where('client_id', $this->hostModel['client_id'])->find();
			if(empty($sshKey)){
				return ['status'=>400, 'msg'=>lang_plugins('ssh_key_not_found')];
			}
			$sshKeyRes = $this->idcsmartCloud->sshKeyCreate([
				'type' 		=> 1,
				'uid'  		=> $detail['data']['user_id'],
				'name'		=> 'skey_'.rand_str(),
				'public_key'=> $sshKey['public_key'],
			]);
			if($sshKeyRes['status'] != 200){
				return ['status'=>400, 'msg'=>lang_plugins('ssh_key_create_failed')];
			}
			$post['ssh_key'] = $sshKeyRes['data']['id'];
			$post['password_type'] = 1;

			$update['password'] = aes_password_encode('');
			$update['ssh_key_id'] = $param['ssh_key_id'];
		}
		$post['port'] = $param['port'];

		$res = $this->idcsmartCloud->cloudReinstall($this->id, $post);
		if($res['status'] == 200){
			$result = [
				'status'=>200,
				'msg'=>lang_plugins('start_reinstall_success'),
			];

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
	 * @param   string param.type - 图表类型(cpu=CPU,memory=内存,disk_io=硬盘IO,bw=带宽)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 图表数据
	 * @return  int data.list[].time - 时间(秒级时间戳)
	 * @return  float data.list[].value - CPU使用率
	 * @return  int data.list[].total - 总内存(单位:B)
	 * @return  int data.list[].used - 内存使用量(单位:B)
	 * @return  float data.list[].read_bytes - 读取速度(B/s)
	 * @return  float data.list[].write_bytes - 写入速度(B/s)
	 * @return  float data.list[].read_iops - 读取IOPS
	 * @return  float data.list[].write_iops - 写入IOPS
	 * @return  float data.list[].in_bw - 进带宽(bps)
	 * @return  float data.list[].out_bw - 出带宽(bps)
	 */
	public function chart($param)
	{
		// 验证type
		if(!isset($param['type']) || !is_string($param['type']) || !in_array($param['type'], ['cpu','memory','disk_io','bw'])){
			return ['status'=>400, 'msg'=>lang_plugins('chart_type_error')];
		}

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [],
		];

		$detail = $this->idcsmartCloud->cloudDetail($this->id);
		if($detail['status'] != 200){
			return $result;
		}
		$data = [];
		$data['node_id'] = $detail['data']['node_id'];
		$data['kvm'] = $detail['data']['kvmid'];

		// 时间选择,起始结束
		$data['st'] = $this->hostModel['active_time'] ?: $this->hostModel['create_time'];
		if(isset($param['start_time']) && !empty($param['start_time'])){
			if($param['start_time'] >= $data['st']){
				$data['st'] = $param['start_time'];
			}
		}
		$data['st'] .= '000';

		// 类型转换
		if($param['type'] == 'cpu'){
			$data['type'] = 'kvm_info';
		}else if($param['type'] == 'memory'){
			$data['type'] = 'kvm_info';
		}else if($param['type'] == 'disk_io'){
			$data['type'] = 'disk_io';
			$data['dev_name'] = $param['dev'] ?? 'vda';  // 选择的磁盘
		}else if($param['type'] == 'bw'){
			$data['type'] = 'net_adapter';
			$data['kvm_ifname'] = $detail['data']['kvmid'] . '.0'; // 第一个网卡
		}else{

		}
		$res = $this->idcsmartCloud->chart($data);

		if(isset($res['data']) && !empty($res['data'])){
			// 转换格式
			if($param['type'] == 'cpu'){
				foreach($res['data'] as $v){
					$result['data']['list'][] = [
						'time'	=> strtotime($v[0]),
						'value'	=> $v[1] ?? 0,
					];
				}
			}else if($param['type'] == 'memory'){
				foreach($res['data'] as $v){
					$result['data']['list'][] = [
						'time'	=> strtotime($v[0]),
						'total'	=> $v[2] ?? 0,
						'used'	=> $v[3] ?? 0,
					];
				}
			}else if($param['type'] == 'disk_io'){
				foreach($res['data'] as $v){
					$result['data']['list'][] = [
						'time'		  => strtotime($v[0]),
						'read_bytes'  => $v[1] ?? 0,
						'write_bytes' => $v[2] ?? 0,
						'read_iops'   => $v[3] ?? 0,
						'write_iops'  => $v[4] ?? 0,
					];
				}
			}else if($param['type'] == 'bw'){
				foreach($res['data'] as $v){
					$result['data']['list'][] = [
						'time'	 => strtotime($v[0]),
						'in_bw'  => $v[1] ?? 0,
						'out_bw' => $v[2] ?? 0,
					];
				}
			}
		}
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
	public function flowDetail()
	{
		$res = $this->idcsmartCloud->netInfo($this->id);

		if($res['status'] == 200 && !empty($res['data'])){
			$total = $res['data']['meta']['traffic_quota'] > 0 ? $res['data']['meta']['traffic_quota'] + $res['data']['meta']['tmp_traffic'] : 0;
			if($res['data']['meta']['traffic_type'] == 1){
				$used = round($res['data']['info']['to_month']['accept']/1024/1024/1024, 2);
			}else if($res['data']['meta']['traffic_type'] == 2){
				$used = round($res['data']['info']['to_month']['send']/1024/1024/1024, 2);
			}else{
				$used = round($res['data']['info']['to_month']['total']/1024/1024/1024, 2);
			}
			$leave = round($total - $used, 2);

			$resetFlowDay = $res['data']['meta']['reset_flow_day'] ?? 1;

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
	 * 时间 2022-07-11
	 * @title 获取实例磁盘
	 * @desc 获取实例磁盘
	 * @author theworld
	 * @version v1
     * @return  int list[].id - 魔方云磁盘ID
     * @return  string list[].name - 名称
     * @return  int list[].size - 磁盘大小(GB)
     * @return  int list[].create_time - 创建时间
     * @return  string list[].type - 磁盘类型
     * @return  string list[].type2 - 类型(system=系统盘,data=数据盘)
     * @return  int list[].is_free - 是否免费盘(0=否,1=是),免费盘不能扩容
	 */
	public function diskList()
	{
		$DiskModel = new DiskModel();
		$diskList = $DiskModel->diskList($this->hostModel['id']);

		if(empty($diskList)){
			$diskList = [];
			// return [];
		}
		// 获取磁盘列表
		$res = $this->idcsmartCloud->cloudDetail($this->id);
		if($res['status'] == 400){
			return ['list' => []];
		}
		$disk = $res['data']['disk'];
		$diskStatus = array_column($disk, 'status', 'id');
		$diskName = array_column($disk, 'name', 'id');

		if(count($disk) == 2 && count($diskList) == 1 && $diskList[0]['id'] == 0){
			DiskModel::where('host_id', $this->hostModel['id'])->where('rel_id', $diskList[0]['id'])->update(['name'=>$disk[1]['name'],'rel_id'=>$disk[1]['id']]);

			$diskList[0]['id'] 		= $disk[1]['id'];
			$diskList[0]['name'] 	= $disk[1]['name'];
		}

		foreach($diskList as $k=>$v){
			$diskList[$k]['status'] = $diskStatus[$v['id']] ?? 1;
			if(empty($v['name'])){
				if(isset($diskName[ $v['id'] ])){
					DiskModel::where('host_id', $this->hostModel['id'])->where('rel_id', $v['id'])->update(['name'=>$diskName[ $v['id'] ] ]);
				}
				$diskList[$k]['name'] = $diskName[ $v['id'] ] ?? lang_plugins('mf_cloud_disk') . $k;
			}
			$diskList[$k]['type2'] = 'data';
		}

		// 把系统盘放在最上面
		array_unshift($diskList, [
			'id' => $disk[0]['id'],
			'name' => $disk[0]['name'],
			'size' => $disk[0]['size'],
			'create_time' => strtotime($disk[0]['create_time']),
			'type' => '',
			'type2' => 'system',
			'is_free'=>1,
		]);
		return ['list' => $diskList];
	}

	/**
	 * 时间 2023-02-10
	 * @title 卸载磁盘
	 * @desc 卸载磁盘
	 * @author hh
	 * @version v1
	 * @param   int disk_id - 魔方云磁盘ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.name - 磁盘名称
	 */
	public function diskUnmount($disk_id)
	{
		$disk = DiskModel::where('host_id', $this->hostModel['id'])->where('rel_id', $disk_id)->find();
		if(empty($disk)){
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
		}

		$res = $this->idcsmartCloud->cloudUmountDisk($this->id, $disk_id);
		if($res['status'] == 200){
			// 创建成功
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_cloud_unmount_disk_success')
			];

			$description = lang_plugins('log_mf_cloud_host_unmount_disk_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$disk['name']
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_cloud_unmount_disk_fail')
			];

			$description = lang_plugins('log_mf_cloud_host_unmount_disk_fail', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$disk['name']
			]);
		}
		$result['data']['name'] = $disk['name'];

		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2023-02-10
	 * @title 挂载磁盘
	 * @desc 挂载磁盘
	 * @author hh
	 * @version v1
	 * @param   int disk_id - 魔方云磁盘ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.name - 磁盘名称
	 */
	public function diskMount($disk_id)
	{
		$disk = DiskModel::where('host_id', $this->hostModel['id'])->where('rel_id', $disk_id)->find();
		if(empty($disk)){
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
		}

		$res = $this->idcsmartCloud->diskMount($disk_id);
		if($res['status'] == 200){
			// 创建成功
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('mf_cloud_mount_disk_success')
			];

			$description = lang_plugins('log_mf_cloud_host_mount_disk_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$disk['name']
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_cloud_mount_disk_fail')
			];

			$description = lang_plugins('log_mf_cloud_host_mount_disk_fail', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$disk['name']
			]);
		}
		$result['data']['name'] = $disk['name'];

		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 快照列表
	 * @desc 快照列表
	 * @author hh
	 * @version v1
	 * @param   int param.page - 页数
	 * @param   int param.limit - 每页条数
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.list[].id - 快照ID
	 * @return  string data.list[].name - 快照名称
	 * @return  int data.list[].create_time - 创建时间
	 * @return  string data.list[].notes - 备注
	 * @return  int data.list[].status - 状态(0=创建中,1=创建完成)
	 * @return  int data.count - 总条数
	 */
	public function snapshotList($param)
	{
		$param['page'] = $param['page'] ?? 1;
        $param['per_page'] = $param['limit'] ?? config('idcsmart.limit');
        // $param['sort'] = $param['sort'] ?? config('idcsmart.sort');
        $param['type'] = 'snap';
        
        $res = $this->idcsmartCloud->cloudSnapshot($this->id, $param);

        $data = [];
        if(isset($res['data']['data'])){
        	foreach($res['data']['data'] as $v){
	        	$data[] = [
	        		'id'=>$v['id'],
	        		'name'=>$v['name'],
	        		'create_time'=>strtotime($v['create_time']),
	        		'notes'=>$v['remarks'],
	        		'status'=>$v['status'],
	        	];
	        }
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$data,
				'count'=>$res['data']['meta']['total'] ?? 0
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-07-11
	 * @title 创建快照
	 * @desc 创建快照
	 * @author theworld
	 * @version v1
	 * @param   int param.name - 快照名称 require
	 * @param   int param.disk_id - 魔方云磁盘ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function snapshotCreate($param)
	{
		// 获取磁盘列表
		$res = $this->idcsmartCloud->cloudDetail($this->id);
		if($res['status'] == 400){
			return $res;
		}
		$diskId = array_column($res['data']['disk'], 'id');
		if(!in_array($param['disk_id'] ?? 0, $diskId)){
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotCreate($param['disk_id'], ['type' => 'snap', 'name' => $param['name']]);
		if($res['status'] == 200){
			// 创建成功
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_create_snapshot_success')
			];

			$description = lang_plugins('log_host_start_create_snap_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$param['name']
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_create_snapshot_failed')
			];

			$description = lang_plugins('log_host_start_create_snap_failed', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$param['name']
			]);
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 快照还原
	 * @desc 快照还原
	 * @author hh
	 * @version v1
	 * @param   int param.snapshot_id - 快照ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.name - 快照名称
	 */
	public function snapshotRestore($param)
	{
		// 获取快照列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'snap']);
		if($res['status'] == 400){
			return $res;
		}
		$res['data']['data'] = $res['data']['data'] ?? [];

		$snapshot = null;
		foreach($res['data']['data'] as $v){
			if($v['id'] == $param['snapshot_id']){
				$snapshot = $v['remarks'];
				if($v['status'] == 0){
					return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_snap_creating_wait_to_retry')];
				}
				break;
			}
		}
		if(is_null($snapshot)){
			return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotRestore($this->id, (int)$param['snapshot_id']);

		if($res['status'] == 200){
			// 还原成功,更新密码,端口信息
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_snapshot_restore_success')
			];

			if(isset($res['data']['os'])){
				$update = [];
				$update['password'] = aes_password_encode($res['data']['os']['password']);

				$image = ImageModel::where('rel_image_id', $res['data']['os']['id'])->where('product_id', $this->hostModel['product_id'])->find();
				if(!empty($image)){
					$update['image_id'] = $image['id'];
				}
				$this->hostLinkModel->update($update, ['id'=>$this->hostLinkModel['id']]);
			}

			$description = lang_plugins('log_host_start_snap_restore_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$snapshot
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_snapshot_restore_failed')
			];

			$description = lang_plugins('log_host_start_snap_restore_failed', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$snapshot
			]);
		}
		$result['data']['name'] = $snapshot;

		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除快照
	 * @author hh
	 * @version v1
	 * @param   int id - 快照ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.name - 快照名称
	 */
	public function snapshotDelete($id)
	{
		// 获取快照列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'snap']);
		if($res['status'] == 400){
			return $res;
		}
		$res['data']['data'] = $res['data']['data'] ?? [];

		$snapshot = null;
		foreach($res['data']['data'] as $v){
			if($v['id'] == $id){
				$snapshot = $v['remarks'];
				if($v['status'] == 0){
					return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_snap_creating_wait_to_retry')];
				}
				break;
			}
		}
		if(is_null($snapshot)){
			return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
		}
		// $snapshot = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		// if(!isset($snapshot[$id])){
		// 	return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
		// }
		$res = $this->idcsmartCloud->snapshotDelete($this->id, $id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('delete_snapshot_success')
			];

			$description = lang_plugins('log_host_delete_snap_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$snapshot
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('delete_snapshot_failed')
			];

			$description = lang_plugins('log_host_delete_snap_failed', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$snapshot
			]);
		}
		$result['data']['name'] = $snapshot;

		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 备份列表
	 * @desc 备份列表
	 * @author hh
	 * @version v1
	 * @param   int param.page - 页数
	 * @param   int param.limit - 每页条数
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.list[].id - 备份ID
	 * @return  string data.list[].name - 备份名称
	 * @return  int data.list[].create_time - 创建时间
	 * @return  string data.list[].notes - 备注
	 * @return  int data.list[].status - 状态(0=创建中,1=创建成功)
	 * @return  int data.count - 总条数
	 */
	public function backupList($param)
	{
		$param['page'] = $param['page'] ?? 1;
        $param['per_page'] = $param['limit'] ?? config('idcsmart.limit');
        $param['type'] = 'backup';
        
        $res = $this->idcsmartCloud->cloudSnapshot($this->id, $param);

        $data = [];
        if(isset($res['data']['data'])){
        	foreach($res['data']['data'] as $v){
	        	$data[] = [
	        		'id'=>$v['id'],
	        		'name'=>$v['name'],
	        		'create_time'=>strtotime($v['create_time']),
	        		'notes'=>$v['remarks'],
	        		'status'=>$v['status'],
	        	];
	        }
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$data,
				'count'=>$res['data']['meta']['total'] ?? 0
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-07-11
	 * @title 创建备份
	 * @desc 创建备份
	 * @author theworld
	 * @version v1
	 * @param   int param.name - 备份名称 require
	 * @param   int param.disk_id - 魔方云磁盘ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function backupCreate($param)
	{
		// 获取磁盘列表
		$res = $this->idcsmartCloud->cloudDetail($this->id);
		if($res['status'] == 400){
			return $res;
		}
		$disk = array_column($res['data']['disk'], 'name', 'id');
		if(!isset($disk[$param['disk_id']])){
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotCreate($param['disk_id'], ['type' => 'backup', 'name' => $param['name']]);
		if($res['status'] == 200){
			// 创建成功
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_create_backup_success')
			];

			$description = lang_plugins('log_host_start_create_backup_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$disk[$param['disk_id']]
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_create_backup_failed')
			];

			$description = lang_plugins('log_host_start_create_backup_failed', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$disk[$param['disk_id']]
			]);
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 备份还原
	 * @desc 备份还原
	 * @author hh
	 * @version v1
	 * @param   int param.backup_id - 备份ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.name - 备份名称
	 */
	public function backupRestore($param)
	{
		// 获取备份列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'backup']);
		if($res['status'] == 400){
			return $res;
		}
		$res['data']['data'] = $res['data']['data'] ?? [];

		$backup = null;
		foreach($res['data']['data'] as $v){
			if($v['id'] == $param['backup_id']){
				$backup = $v['remarks'];
				if($v['status'] == 0){
					return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_backup_creating_wait_to_retry')];
				}
				break;
			}
		}
		if(is_null($backup)){
			return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
		}
		// $backup = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		// if(!isset($backup[$param['backup_id']])){
		// 	return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
		// }
		$res = $this->idcsmartCloud->snapshotRestore($this->id, (int)$param['backup_id']);
		if($res['status'] == 200){
			// 还原成功,更新密码,端口信息
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_backup_restore_success')
			];

			if(isset($res['data']['os'])){
				$update = [];
				$update['password'] = aes_password_encode($res['data']['os']['password']);

				$image = ImageModel::where('rel_image_id', $res['data']['os']['id'])->where('product_id', $this->hostModel['product_id'])->find();
				if(!empty($image)){
					$update['image_id'] = $image['id'];
				}

				$this->hostLinkModel->update($update, ['id'=>$this->hostLinkModel['id']]);
			}

			$description = lang_plugins('log_host_start_backup_restore_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$backup
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_backup_restore_failed')
			];

			$description = lang_plugins('log_host_start_backup_restore_failed', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$backup
			]);
		}
		$result['data']['name'] = $backup;

		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除备份
	 * @author hh
	 * @version v1
	 * @param   int id - 备份ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.name - 备份名称
	 */
	public function backupDelete($id)
	{
		// 获取备份列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'backup']);
		if($res['status'] == 400){
			return $res;
		}
		$res['data']['data'] = $res['data']['data'] ?? [];

		$backup = null;
		foreach($res['data']['data'] as $v){
			if($v['id'] == $id){
				$backup = $v['remarks'];
				if($v['status'] == 0){
					return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_backup_creating_wait_to_retry')];
				}
				break;
			}
		}
		if(is_null($backup)){
			return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
		}
		// $backup = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		// if(!isset($backup[$id])){
		// 	return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
		// }
		$res = $this->idcsmartCloud->snapshotDelete($this->id, $id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('delete_backup_success')
			];

			$description = lang_plugins('log_host_delete_backup_success', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$backup
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('delete_backup_failed')
			];

			$description = lang_plugins('log_host_delete_backup_failed', [
				'{hostname}'=>$this->hostModel['name'],
				'{name}'=>$backup
			]);
		}
		$result['data']['name'] = $backup;

		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取魔方云真实详情
	 * @desc 获取魔方云真实详情
	 * @author hh
	 * @version v1
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.rescue - 是否正在救援系统(0=不是,1=是)
	 * @return  string data.username - 远程用户名
	 * @return  string data.password - 远程密码
	 * @return  int data.port - 远程端口
	 * @return  int data.ip_num - IP数量
	 */
	public function detail()
	{
		$detail = $this->idcsmartCloud->cloudDetail($this->id);
		
		// 获取下当前镜像
		$ImageModel = ImageModel::find($this->hostLinkModel['image_id']);
		$info = $ImageModel->getDefaultUserInfo();

		$data = [
			'rescue'=>0,
			'username'=>$info['username'],
			'password'=>aes_password_decode($this->hostLinkModel['password']),
			'port'=>$info['port'],
			'ip_num'=>1,
		];
		if(isset($detail['data'])){
			$update = [];
			if($data['password'] != $detail['data']['rootpassword']){
				$update['password'] = aes_password_encode($detail['data']['rootpassword']);
			}
			if(!empty($this->hostLinkModel['ssh_key_id']) && (!isset($detail['data']['ssh_key']['id']) || empty($detail['data']['ssh_key']['id']))){
				$update['ssh_key_id'] = 0;
			}

			$data['rescue'] = $detail['data']['rescue'];
			$data['username'] = $detail['data']['osuser'];
			$data['password'] = $detail['data']['rootpassword'];
			$data['port'] = $detail['data']['port'] > 0 ? $detail['data']['port'] : ($detail['data']['image_group_id'] == 1 ? 3306 : 22);
			$data['ip_num'] = $detail['data']['ip_num'];
			
			if(!empty($update)){
				HostLinkModel::update($update, ['host_id'=>$this->hostModel['id']]);
			}
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => $data
		];
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取IP列表
	 * @desc 获取IP列表
	 * @author hh
	 * @version v1
	 * @param int param.page 1 页数
	 * @param int param.limit 20 每页条数
	 * @return string list[].ip - IP地址
	 * @return string list[].subnet_mask - 掩码
	 * @return string list[].gateway - 网关
	 * @return int count - 总条数
	 */
	public function ipList($param)
	{
		$param['page'] = $param['page']>0 ? $param['page'] : 1;
		$param['limit'] = $param['limit']>0 ? $param['limit'] : 20;

		$data = [];
		$count = 0;

		if($this->id > 0){
			// 获取当前所有IP
			$bw = $this->idcsmartCloud->bwList(['per_page'=>999, 'cloud'=>$this->id]);
			if(isset($bw['data']['data'])){
				foreach($bw['data']['data'] as $v){
					foreach($v['ip'] as $vv){
						$data[] = [
							'ip' => $vv['ip'],
							'subnet_mask'=>$vv['subnet_mask'],
							'gateway'=>$vv['gateway'],
						];
					}
				}
			}
			
			$count = count($data);
			$data  = array_slice($data, ($param['page']-1)*$param['limit'], $param['limit']);
		}
		return ['list'=>$data, 'count'=>$count];
	}

	/**
     * 时间 2022-09-25
     * @title 计算磁盘价格
     * @desc 计算磁盘价格
     * @author hh
     * @version v1
	 * @param   array param.remove_disk_id - 要取消订购的磁盘ID
	 * @param   array param.add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
	 * @param   int param.add_disk[].size - 磁盘大小
	 * @param   string param.add_disk[].type - 磁盘类型
	 * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     */
    public function calDiskPrice(&$param)
    {
    	// 套餐不能单独购买磁盘
    	if(!empty($this->hostLinkModel['recommend_config_id'])){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
    	}
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$diffTime = $this->hostModel['due_time'] - time();
        // $ConfigModel = ConfigModel::where('product_id', $this->hostModel['product_id'])->find();
        // 获取之前的周期
        // $duration = HostLinkModel::getDuration($this->hostModel);
    	$configData = json_decode($this->hostLinkModel['config_data'], true);
    	// 匹配下之前的周期
        $price = 0;
        $priceDifference = 0;
        $add_size = [];
        $del_size = [];
        // $diskNum = 0;
        $diskNum = DiskModel::where('host_id', $this->hostModel['id'])->count();

        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;

        // 有要取消的磁盘
        if(isset($param['remove_disk_id']) && !empty(array_filter($param['remove_disk_id']))){
        	$removeDisk = DiskModel::where('host_id', $this->hostModel['id'])->whereIn('rel_id', $param['remove_disk_id'])->select()->toArray();
        	if(count($removeDisk) != count($param['remove_disk_id'])){
        		return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
        	}
        	foreach($removeDisk as $v){
        		$priceDifference = bcsub($priceDifference, $v['price']);
        		$del_size[] = $v['size'];
        	}
        	$diskNum -= count($removeDisk);
        }
        // 新购磁盘
        if(isset($param['add_disk']) && !empty(array_filter($param['add_disk']))){
        	// 看下当前周期还存在不
	    	$duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
	    	if(empty($duration)){
	    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
	    	}
        	$param['add_disk'] = array_filter($param['add_disk']);

        	$ConfigModel = new ConfigModel();
        	$dataDiskLimit = $ConfigModel->getDataDiskLimitNum($productId);

        	if($diskNum + count($param['add_disk']) > $dataDiskLimit){
            	return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_over_max_disk_num', ['{num}'=>$dataDiskLimit])];
        	}
        	$OptionModel = new OptionModel();

            foreach($param['add_disk'] as $k=>$v){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $v['type'] ?? '');
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_data_disk', ['{data_disk}'=>$v['type'].$v['size']])];
                }
                $param['add_disk'][$k]['price'] = $optionDurationPrice['price'] ?? 0;

                $price = bcadd($price, $optionDurationPrice['price'] ?? 0);
               	$add_size[] = $v['size'];
            }

            $price = bcmul($price, $duration['price_factor']);
            $priceDifference = bcadd($priceDifference, $price);
        	
    		// 计算差价
			$price = $price * $diffTime/$this->hostModel['billing_cycle_time'];
        }
        if(!empty($add_size) && !empty($del_size) ){
        	$description = lang_plugins('upgrade_buy_and_cancel_data_disk', [
        		'{del}'=>implode(',', $del_size),
        		'{add}'=>implode(',', $add_size),
        	]);
        }else if(!empty($add_size)){
        	$description = lang_plugins('upgrade_buy_data_disk', [
        		'{add}'=>implode(',', $add_size)
        	]);
        }else if(!empty($del_size)){
        	$description = lang_plugins('upgrade_cancel_data_disk', [
        		'{del}'=>implode(',', $del_size)
        	]);
        }else{
        	return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }

        if($this->hostModel['billing_cycle'] == 'free'){
        	$price = '0.00';
        	$priceDifference = 0;
        }else{
        	$price = max(0, $price);
        	$price = amount_format($price);
        }
        if($isDownstream){
        	$DurationModel = new DurationModel();
        	$price = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $price,
            ]);
            $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $priceDifference,
            ]);
        }
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
                'base_price' => $priceDifference
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成订购磁盘订单
     * @desc 生成订购磁盘订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
	 * @param   array param.remove_disk_id - 要取消订购的磁盘ID
	 * @param   array param.add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
	 * @param   int param.add_disk[].size - 磁盘大小
	 * @param   string param.add_disk[].type - 磁盘类型
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createBuyDiskOrder($param)
    {
    	if(isset($param['is_downstream'])){
    		unset($param['is_downstream']);
    	}
        $res = $this->calDiskPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $host['base_price']+$res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'buy_disk',
                'remove_disk_id' => array_filter($param['remove_disk_id'] ?? []),
                'add_disk' => array_filter($param['add_disk'] ?? []),
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }

	/**
     * 时间 2022-09-25
     * @title 计算磁盘扩容价格
     * @desc 计算磁盘扩容价格
     * @author hh
     * @version v1
	 * @param   int param.resize_data_disk[].id - 魔方云磁盘ID
	 * @param   int param.resize_data_disk[].size - 磁盘大小
	 * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     * @return  int resize_disk[].id - 变更磁盘ID
     * @return  int resize_disk[].size - 磁盘大小
     * @return  string resize_disk[].price - 价格
     */
    public function calResizeDiskPrice($param)
    {
    	// 套餐不能单独购买磁盘
    	if(!empty($this->hostLinkModel['recommend_config_id'])){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
    	}
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime = $this->hostModel['due_time'] - time();
    	$isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;

    	$configData = json_decode($this->hostLinkModel['config_data'], true);

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
    	}

        $price = 0;
        $oldPrice = 0;
		$description = '';

		$resizeDisk = [];
		$OptionModel = new OptionModel();
       	foreach($param['resize_data_disk'] as $k=>$v){
       		if(!isset($v['id']) || !isset($v['size'])){
       			return ['status'=>400, 'msg'=>lang_plugins('param_error')];
       		}
       		$disk = DiskModel::where('host_id', $hostId)->where('rel_id', $v['id'])->find();
       		if(empty($disk)){
       			return ['status'=>400, 'msg'=>lang_plugins('disk_not_found')];
       		}
       		// 免费盘不能扩容
       		if($disk['is_free'] == 1){
       			return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_free_disk_cannot_resize') ];
       		}
       		// 大小没改
       		if($v['size'] == $disk['size']){
       			continue;
       		}
       		if($v['size'] < $disk['size']){
       			return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_data_disk_cannot_down_size')];
       		}

            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $v['size'], $duration['id'], $disk['type']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_data_disk', ['{data_disk}'=>$v['size']])];
            }

            $resizeDisk[] = [
				'id' 	=> $v['id'],
				'size' 	=> $v['size'],
				'price' => $optionDurationPrice['price'] ?? 0
			];
			
			$description .= lang_plugins('upgrade_data_disk_size', [
				'{name}'	=> $disk['name'],
				'{old}'		=> $disk['size'],
				'{new}'		=> $v['size']
			]);

			// 获取当前配置价格
            $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::DATA_DISK, 0, $disk['size'], $duration['id'], $disk['type']);
            if($optionDurationPrice['match']){
                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            }
			$price 	  = bcadd($price, $optionDurationPrice['price'] ?? 0);
       	}
		if(empty($resizeDisk)){
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_resize')];
		}

		$price = bcmul($price, $duration['price_factor']);

		$priceDifference = bcsub($price, $oldPrice);

		$price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
		
        $price = max(0, $price);
        $price = amount_format($price);

        if($isDownstream){
        	$DurationModel = new DurationModel();
        	$price = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $price,
            ]);
            $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $priceDifference,
            ]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
                'resize_disk'=>$resizeDisk,
                'base_price' =>$priceDifference
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
     * @param   int param.id - 产品ID require
	 * @param   int param.resize_data_disk[].id - 魔方云磁盘ID
	 * @param   int param.resize_data_disk[].size - 磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createResizeDiskOrder($param)
    {
    	if(isset($param['is_downstream'])){
    		unset($param['is_downstream']);
    	}
        $res = $this->calResizeDiskPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $host['base_price']+$res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'resize_disk',
                'resize_disk' => $res['data']['resize_disk'],
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2022-09-25
     * @title 计算IP数量价格
     * @desc 计算IP数量价格
     * @author hh
     * @version v1
	 * @param   int param.id - 产品ID require
	 * @param   int param.ip_num - 附加IP数量 require
	 * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
	 * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.price - 价格
     * @return  string data.description - 生成的订单描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     * @return  int data.ip_data.value - 附加IP数量
     * @return  string data.ip_data.price - 价格
     */
    public function calIpNumPrice($param)
    {
    	if(!empty($this->hostLinkModel['recommend_config_id'])){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
    	}
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime  = $this->hostModel['due_time'] - time();

    	$configData = json_decode($this->hostLinkModel['config_data'], true);

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
    	}
    	// 检查之前的线路是否还存在
    	$line = LineModel::where('id', $configData['line']['id'])->find();
    	if(empty($line) || $line['ip_enable'] != 1){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_line_not_found_to_upgrade_ip_num')];
    	}
    	if(isset($configData['ip']['value']) && $configData['ip']['value'] == $param['ip_num']){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ip_num_not_change')];
    	}
    	$OptionModel = new OptionModel();
    	$optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $param['ip_num'], $duration['id']);
        if(!$optionDurationPrice['match']){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_ip_num_error') ];
        }
        $ipData = [
            'value' => $param['ip_num'],
            'price' => $optionDurationPrice['price'] ?? 0
        ];

        $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_IP, $line['id'], $configData['ip']['value'] ?? 0, $duration['id']);
        $oldPrice = $currentOptionDurationPrice['price'] ?? 0;
        $price = $optionDurationPrice['price'] ?? 0;

        $oldPrice = bcmul($oldPrice, $duration['price_factor']);
        $price = bcmul($price, $duration['price_factor']);

        $description = lang_plugins('mf_cloud_upgrade_ip_num', [
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
        
        // 下游
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        if($isDownstream){
            $DurationModel = new DurationModel();
            $price = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $price,
            ]);
            $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $priceDifference,
            ]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
                'ip_data'=>$ipData,
                'base_price' => $priceDifference
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成IP数量订单
     * @desc 生成IP数量订单
     * @author hh
     * @version v1
	 * @param   int param.id - 产品ID require
	 * @param   int param.ip_num - 附加IP数量 require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createIpNumOrder($param)
    {
    	if(isset($param['is_downstream'])){
    		unset($param['is_downstream']);
    	}
        $res = $this->calIpNumPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $host['base_price']+$res['data']['base_price'],
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
     * 时间 2023-02-13
     * @title 创建VPC网络
     * @desc 创建VPC网络
     * @author hh
     * @version v1
     * @param   string param.name - VPC网络名称 require
     * @param   string param.ips - IP段(cidr,如10.0.0.0/16,系统分配时不传)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - VPC网络ID
     */
    public function vpcNetworkCreate($param)
    {
    	// 商品是否支持VPC
    	$ConfigModel = new ConfigModel();
    	$config = $ConfigModel->indexConfig(['product_id'=>$this->hostModel['product_id']]);
        $config = $config['data'];
        if($config['support_vpc_network'] == 0){
        	return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_product_not_support_vpc_network')];
        }

    	$param['product_id'] = $this->hostModel['product_id'];
    	$param['data_center_id'] = $this->hostLinkModel['data_center_id'];
    	$param['client_id'] = get_client_id();

    	$VpcNetworkModel = new VpcNetworkModel();
    	return $VpcNetworkModel->vpcNetworkCreate($param);
    }

    /**
     * 时间 2023-02-14
     * @title 切换VPC网络
     * @desc 切换VPC网络
     * @author hh
     * @version v1
     * @param   int param.vpc_network_id - VPC网络ID require
     * @param   int param.downstream_client_id - 下游用户ID(api时可用)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - 变更后VPC网络名称
     */
    public function changeVpcNetwork($param)
    {
    	$vpcNetwork = VpcNetworkModel::find($param['vpc_network_id'] ?? 0);
    	if(empty($vpcNetwork) || $vpcNetwork['data_center_id'] != $this->hostLinkModel['data_center_id'] || $vpcNetwork['client_id'] != $this->hostModel['client_id']){
    		return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
    	}
    	if($vpcNetwork['id'] == $this->hostLinkModel['vpc_network_id']){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_vpc_network_not_change')];
    	}
    	// 是否需要验证vpc子账户是否一样?

    	if(request()->is_api && isset($param['downstream_client_id']) && $param['downstream_client_id'] != $vpcNetwork['downstream_client_id']){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')];
        }

    	$configData = json_decode($this->hostLinkModel['config_data'], true);
    	if($configData['network_type'] != 'vpc'){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_normal_network_cannot_change_to_vpc')];
    	}
    	if(isset($configData['nat_acl_limit']) || isset($configData['nat_web_limit'])){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_nat_host_cannot_change_vpc')];
    	}

    	$post = [];
        // 检查下VPC在魔方云是否还存在
        if(!empty($vpcNetwork['rel_id'])){
            $remoteVpc = $this->idcsmartCloud->vpcNetworkDetail($vpcNetwork['rel_id']);
            if($remoteVpc['status'] == 200){
                $post['vpc'] = $vpcNetwork['rel_id'];
            }else{
                $post['vpc_ips'] = $vpcNetwork['ips'];
            }
        }else{
            $post['vpc_ips'] = $vpcNetwork['ips'];
        }
        $res = $this->idcsmartCloud->cloudChangeVpcNetwork($this->id, $post);
        if($res['status'] == 200){
        	// 新创建的VPC,等待完成后获取vpc保存
        	if(!isset($post['vpc'])){
        		// 等待10分钟
        		for($i = 0; $i<100; $i++){
        			$taskRes = $this->idcsmartCloud->taskDetail($res['data']['taskid']);
        			if($taskRes['status'] == 200 && !in_array($taskRes['data']['status'], [0,1])){
        				break;
        			}
        			sleep(6);
        		}
        		// 任务成功
        		if($taskRes['status'] == 200 && $taskRes['data']['status'] == 2){
        			$result = [
	        			'status' => 200,
	        			'msg'	 => lang_plugins('mf_cloud_change_vpc_network_success'),
	        		];
	        		hostLinkModel::where('host_id', $this->hostModel['id'])->update(['vpc_network_id'=>$vpcNetwork['id']]);

        			$detail = $this->idcsmartCloud->cloudDetail($this->id);
					if($detail['status'] == 200){
						$result = [
		        			'status' => 200,
		        			'msg'	 => lang_plugins('mf_cloud_change_vpc_network_success'),
		        		];
		        		VpcNetworkModel::where('id', $vpcNetwork['id'])->update(['rel_id'=>$detail['data']['network'][0]['vpc'] ?? 0, 'vpc_name'=>$detail['data']['vpc_name'] ?? 'VPC-'.rand_str(8)]);
					}else{
						// 获取不到详情,没法保存关联ID

					}
					$description = lang_plugins('log_mf_cloud_change_vpc_network_success', [
						'{hostname}' => $this->hostModel['name'],
						'{name}' => $vpcNetwork['name'],
					]);
        		}else{
        			$result = [
		        		'status' => 400,
		        		'msg'	 => lang_plugins('mf_cloud_change_vpc_network_fail'),
		        	];
		        	$description = lang_plugins('log_mf_cloud_change_vpc_network_fail', [
						'{hostname}' => $this->hostModel['name'],
						'{name}' => $vpcNetwork['name'],
					]);
        		}
        	}else{
        		$result = [
        			'status' => 200,
        			'msg'	 => lang_plugins('mf_cloud_start_change_vpc_network_success'),
        		];
        		hostLinkModel::where('host_id', $this->hostModel['id'])->update(['vpc_network_id'=>$vpcNetwork['id']]);

        		$description = lang_plugins('log_mf_cloud_start_change_vpc_network_success', [
					'{hostname}' => $this->hostModel['name'],
					'{name}' => $vpcNetwork['name'],
				]);
        	}
        }else{
        	$result = [
        		'status' => 400,
        		'msg'	 => lang_plugins('mf_cloud_change_vpc_network_fail'),
        	];

        	$description = lang_plugins('log_mf_cloud_start_change_vpc_network_fail', [
				'{hostname}' => $this->hostModel['name'],
				'{name}' => $vpcNetwork['name'],
			]);
        }
        $result['data']['name'] = $vpcNetwork['name'];

        active_log($description, 'host', $this->hostModel['id']);
        return $result;
    }

    /**
	 * 时间 2023-02-14
	 * @title 获取cpu/内存使用信息
	 * @author hh
	 * @version v1
	 * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
	 * @return  string data.cpu_usage - CPU使用率
	 * @return  string data.memory_total - 内存总量(‘-’代表获取不到)
	 * @return  string data.memory_usable - 可用内存(‘-’代表获取不到)
	 * @return  string data.memory_usage - 内存使用率(‘-1’代表获取不到)
	 */
	public function cloudRealData()
	{
		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'cpu_usage' => '0' ,
				'memory_total' => '-',
				'memory_usable' => '-',
				'memory_usage' => '-1',
			]
		];

		$res = $this->idcsmartCloud->cloudList(['id'=>$this->id]);
		if($res['status'] == 200){
			$result['data']['cpu_usage'] = $res['data']['data'][0]['cpu_usage'] ?? '0';
			$result['data']['memory_total'] = $res['data']['data'][0]['memory_total'] ?? '-';
			$result['data']['memory_usable'] = $res['data']['data'][0]['memory_usable'] ?? '-';
			$result['data']['memory_usage'] = $res['data']['data'][0]['memory_usage'] ?? '-1';
		}
		return $result;
	}

	/**
     * 时间 2022-09-25
     * @title 计算产品配置升级价格
     * @desc 计算产品配置升级价格
     * @author hh
     * @version v1
     * @param   int param.cpu - 核心数 require
     * @param   int param.memory - 内存 require
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.peak_defence - 防御峰值
     * @param   int param.is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  array data.new_config_data - 新的配置记录
     * @return  int data.new_config_data.cpu.value - CPU
     * @return  string data.new_config_data.cpu.price - 价格
     * @return  string data.new_config_data.cpu.other_config.advanced_cpu - 智能CPU规则ID
     * @return  string data.new_config_data.cpu.other_config.cpu_limit - CPU限制
     * @return  int data.new_config_data.memory.value - 内存
     * @return  string data.new_config_data.memory.price - 价格
     * @return  int data.new_config_data.bw.value - 带宽
     * @return  string data.new_config_data.bw.price - 价格
     * @return  string data.new_config_data.bw.other_config.in_bw - 流入带宽
     * @return  string data.new_config_data.bw.other_config.advanced_bw - 智能带宽规则ID
     * @return  int data.new_config_data.flow.value - 流量
     * @return  string data.new_config_data.flow.price - 价格
     * @return  int data.new_config_data.flow.other_config.in_bw - 入站带宽
     * @return  int data.new_config_data.flow.other_config.out_bw - 出站带宽
     * @return  int data.new_config_data.flow.other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  string data.new_config_data.flow.other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
     * @return  int data.new_config_data.defence.value - 防御峰值
     * @return  string data.new_config_data.defence.price - 价格
     */
    public function calCommonConfigPrice($param)
    {
    	// 套餐不能单独购买磁盘
    	if(!empty($this->hostLinkModel['recommend_config_id'])){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
    	}
    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime  = $this->hostModel['due_time'] - time();

    	$configData = json_decode($this->hostLinkModel['config_data'], true);

    	$newConfigData = [];

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
    	}
    	$OptionModel = new OptionModel();

    	$oldPrice = 0;  // 老价格
    	$price = 0;     // 新价格
    	$description = []; // 描述

    	if($param['cpu'] != $configData['cpu']['value']){
    		$optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $param['cpu'], $duration['id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
            }
            $preview[] = [
                'name'  =>  'CPU',
                'value' =>  $param['cpu'] . lang_plugins('mf_cloud_core'),
                'price' =>  $optionDurationPrice['price'] ?? 0,
            ];

            $newConfigData['cpu'] = [
                'value' => $param['cpu'],
                'price' => $optionDurationPrice['price'] ?? 0,
                'other_config' => $optionDurationPrice['option']['other_config'],
            ];

            $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::CPU, 0, $configData['cpu']['value'], $duration['id']);

            $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            $price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            $description[] = sprintf("CPU: %d => %d", $configData['cpu']['value'], $param['cpu']);
    	}
        // 获取内存周期价格
        if($param['memory'] != $configData['memory']['value']){
            $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $param['memory'], $duration['id']);
            if(!$optionDurationPrice['match']){
                return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
            }
            // 获取单位
            $memoryUnit = ConfigModel::where('product_id', $productId)->value('memory_unit') ?? 'GB';

            $preview[] = [
                'name'  =>  lang_plugins('memory'),
                'value' =>  $param['memory'].$memoryUnit,
                'price' =>  $optionDurationPrice['price'] ?? 0,
            ];

            $newConfigData['memory'] = [
                'value' => $param['memory'],
                'price' => $optionDurationPrice['price'] ?? 0
            ];
            $newConfigData['memory_unit'] = $memoryUnit;

            $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::MEMORY, 0, $configData['memory']['value'], $duration['id']);

            $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            $price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            $description[] = sprintf("%s: %d => %d", lang_plugins('memory'), $configData['memory']['value'], $param['memory']);
        }
        // 检查之前的线路是否还存在
    	$line = LineModel::where('id', $configData['line']['id'])->find();
    	if(empty($line)){
    		// 不支持bw/flow/peak_defence升降机
    		if($configData['line']['bill_type'] == 'bw' && isset($param['bw']) && is_numeric($param['bw']) && $param['bw'] != $configData['bw']['value']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_bw_upgrade')];
    		}
    		if($configData['line']['bill_type'] == 'flow' && isset($param['flow']) && is_numeric($param['flow']) && $param['flow'] != $configData['flow']['value']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_flow_upgrade')];
    		}
    		if(isset($param['peak_defence']) && isset($configData['defence']['value']) && is_numeric($param['peak_defence']) && $param['peak_defence'] != $configData['defence']['value']){
    			return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_defence_upgrade')];
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
                        return ['status'=>400, 'msg'=>lang_plugins('bw_error') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('bw'),
                        'value' => $param['bw'].'Mbps',
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

            		$description[] = sprintf("%s: %d => %d", lang_plugins('bw'), $configData['bw']['value'], $param['bw']);
                }
            }else if($line['bill_type'] == 'flow'){
            	$param['bw'] = null;
                // 获取流量周期价格
                if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0 && $param['flow'] != $configData['flow']['value']){
                    $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_FLOW, $line['id'], $param['flow'], $duration['id']);
                    if(!$optionDurationPrice['match']){
                        return ['status'=>400, 'msg'=>lang_plugins('line_flow_not_found') ];
                    }
                    $preview[] = [
                        'name'  => lang_plugins('flow'),
                        'value' => $param['flow'] == 0 ? lang_plugins('mf_cloud_unlimited_flow') : $param['flow'].'G',
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

            		$description[] = sprintf("%s: %d => %d", lang_plugins('flow'), $configData['flow']['value'], $param['flow']);
                }
            }
            // 防护
            if($line['defence_enable'] == 1 && isset($param['peak_defence']) && is_numeric($param['peak_defence']) && $param['peak_defence'] >= 0 && $param['peak_defence'] != ($configData['defence']['value'] ?? 0)){
                $optionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $param['peak_defence'], $duration['id']);
                if(!$optionDurationPrice['match']){
                    return ['status'=>400, 'msg'=>lang_plugins('line_defence_not_found') ];
                }
                $preview[] = [
                    'name'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
                    'value' => $param['peak_defence'].'G',
                    'price' => $optionDurationPrice['price'] ?? 0,
                ];

                $newConfigData['defence'] = [
                    'value' => $param['peak_defence'],
                    'price' => $optionDurationPrice['price'] ?? 0
                ];

                $currentOptionDurationPrice = $OptionModel->matchOptionDurationPrice($productId, OptionModel::LINE_DEFENCE, $line['id'], $configData['defence']['value'] ?? 0, $duration['id']);

                $oldPrice = bcadd($oldPrice, $currentOptionDurationPrice['price'] ?? 0);
            	$price    = bcadd($price, $optionDurationPrice['price'] ?? 0);

            	$description[] = sprintf("%s: %d => %d", lang_plugins('mf_cloud_recommend_config_peak_defence'), $configData['defence']['value'] ?? 0, $param['peak_defence']);
            }
    	}
    	if(empty($newConfigData)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_change_config')];
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
        
        // 下游
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        if($isDownstream){
            $DurationModel = new DurationModel();
            $price = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $price,
            ]);
            $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $priceDifference,
            ]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' 					=> $price,
                'description' 				=> $description,
                'price_difference' 			=> $priceDifference,
                'renew_price_difference' 	=> $priceDifference,
                'new_config_data'			=> $newConfigData
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成产品配置升级订单
     * @desc 生成产品配置升级订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.cpu - 核心数 require
     * @param   int param.memory - 内存 require
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.peak_defence - 防御峰值
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createCommonConfigOrder($param)
    {
    	if(isset($param['is_downstream'])){
    		unset($param['is_downstream']);
    	}
        $res = $this->calCommonConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }
        $configData = json_decode($this->hostLinkModel['config_data'], true);

        $param['data_center_id'] = $this->hostLinkModel['data_center_id'];
        $param['line_id'] = $configData['line']['id'] ?? 0;

        $ConfigLimitModel = new ConfigLimitModel();
        $checkConfigLimit  = $ConfigLimitModel->checkConfigLimit($this->hostModel['product_id'], $param);
        if($checkConfigLimit['status'] == 400){
        	return $checkConfigLimit;
        }

        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $host['base_price']+$res['data']['price_difference'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'upgrade_common_config',
                'new_config_data'    => $res['data']['new_config_data'],
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2023-09-20
     * @title NAT转发列表
     * @desc  NAT转发列表
     * @author hh
     * @version v1
	 * @return  int list[].id - 转发ID
	 * @return  string list[].name - 名称
	 * @return  string list[].ip - IP端口
	 * @return  int list[].int_port - 内部端口
	 * @return  int list[].protocol - 协议(1=tcp,2=udp,3=tcp+udp)
	 * @return  int count - 总条数
     */
    public function natAclList()
    {
		$data = [];
		$count = 0;

		// 获取当前所有IP
		$list = $this->idcsmartCloud->natAclList($this->id, ['page'=>1, 'per_page'=>999]);
		if(isset($list['data']['data'])){
			foreach($list['data']['data'] as $v){
				$data[] = [
					'id' 		=> $v['id'],
					'name' 		=> $v['name'],
					'ip'		=> $list['data']['nat_host_ip'].':'.$v['ext_port'],
					'int_port' 	=> $v['int_port'],
					'protocol'	=> $v['protocol'],
				];
			}
		}
		
		$count = count($data);
		return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2023-09-20
     * @title 创建NAT转发
     * @desc 创建NAT转发
     * @author hh
     * @version v1
     * @param   string param.name - 名称 require
     * @param   int param.int_port - 内部端口 require
     * @param   int param.protocol - 协议(1=tcp,2=udp,3=tcp+udp) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function natAclCreate($param)
    {
    	$list = $this->idcsmartCloud->natAclList($this->id, ['page'=>1, 'per_page'=>999]);
    	$total = $list['data']['meta']['total'] ?? 0;

    	$configData = json_decode($this->hostLinkModel['config_data'], true);
    	if($total >= $configData['nat_acl_limit']){
    		$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_cloud_nat_acl_be_limited'),
			];
			return $result;
    	}

    	$res = $this->idcsmartCloud->natAclCreate($this->id, [
    		'name' 		=> $param['name'],
    		'int_port'	=> $param['int_port'],
    		'protocol'	=> $param['protocol'],
    	]);

		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('create_success')
			];

			$description = lang_plugins('log_mf_cloud_nat_acl_create_success', [
				'{hostname}' => $this->hostModel['name'],
				'{name}' 	 => $param['name'],
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('create_failed')
			];

			$description = lang_plugins('log_mf_cloud_nat_acl_create_fail', [
				'{hostname}' => $this->hostModel['name'],
				'{name}' 	 => $param['name'],
			]);			
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
    }

    /**
     * 时间 2023-09-20
     * @title 删除NAT转发
     * @desc 删除NAT转发
     * @author hh
     * @version v1
     * @param   int nat_acl_id - NAT转发ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.name - NAT转发名称
     */
    public function natAclDelete($nat_acl_id)
    {
    	$data = [];

    	$list = $this->idcsmartCloud->natAclList($this->id, ['page'=>1, 'per_page'=>999, 'order'=>'id', 'sort'=>'asc']);
		if(isset($list['data']['data'])){
			foreach($list['data']['data'] as $v){
				if($v['id'] == $nat_acl_id){
					$data = $v;
					break;
				}
			}
			if(!empty($data) && $data['id'] == $list['data']['data'][0]['id']){
				$result = [
					'status' => 400,
					'msg'	 => lang_plugins('mf_cloud_default_nat_acl_cannot_delete'),
				];
				return $result;
			}
		}
		if(empty($data)){
			$result = [
				'status' => 400,
				'msg'	 => lang_plugins('mf_cloud_nat_acl_not_found'),
			];
			return $result;
		}

    	$res = $this->idcsmartCloud->natAclDelete($this->id, $nat_acl_id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('delete_success'),
				'data'	 => [
					'name' => $data['name'],
				]
			];

			$description = lang_plugins('log_mf_cloud_nat_acl_delete_success', [
				'{hostname}' => $this->hostModel['name'],
				'{name}' 	 => $data['name'],
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('delete_failed'),
				'data'	 => [
					'name' => $data['name'],
				],
			];

			$description = lang_plugins('log_mf_cloud_nat_acl_delete_fail', [
				'{hostname}' => $this->hostModel['name'],
				'{name}' 	 => $data['name'],
			]);			
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
    }

    /**
     * 时间 2023-09-20
     * @title NAT建站列表
     * @desc  NAT建站列表
     * @author hh
     * @version v1
	 * @return  int list[].id - 建站ID
	 * @return  string list[].domain - 域名
	 * @return  int list[].ext_port - 外部端口
	 * @return  int list[].int_port - 内部端口
	 * @return  int count - 总条数
     */
    public function natWebList()
    {
		$data = [];
		$count = 0;

		// 获取当前所有IP
		$list = $this->idcsmartCloud->natWebList($this->id, ['page'=>1, 'per_page'=>999]);
		$data = $list['data']['data'] ?? [];
		
		$count = count($data);
		return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2023-09-20
     * @title 创建NAT建站
     * @desc 创建NAT建站
     * @author hh
     * @version v1
     * @param   string param.domain - 域名 require
     * @param   int param.int_port - 内部端口 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function natWebCreate($param)
    {
    	$list = $this->idcsmartCloud->natWebList($this->id, ['page'=>1, 'per_page'=>999]);
    	$total = $list['data']['meta']['total'] ?? 0;

    	$configData = json_decode($this->hostLinkModel['config_data'], true);
    	if($total >= $configData['nat_web_limit']){
    		$result = [
				'status' => 400,
				'msg'    => lang_plugins('mf_cloud_nat_web_be_limited'),
			];
			return $result;
    	}

    	$res = $this->idcsmartCloud->natWebCreate($this->id, [
    		'domain' 	=> $param['domain'],
    		'ext_port' 	=> 80,
    		'int_port'	=> $param['int_port'],
    	]);

		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('create_success')
			];

			$description = lang_plugins('log_mf_cloud_nat_web_create_success', [
				'{hostname}' => $this->hostModel['name'],
				'{domain}' 	 => $param['domain'],
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('create_failed')
			];

			$description = lang_plugins('log_mf_cloud_nat_web_create_fail', [
				'{hostname}' => $this->hostModel['name'],
				'{domain}' 	 => $param['domain'],
			]);			
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
    }

    /**
     * 时间 2023-09-20
     * @title 删除NAT建站
     * @desc 删除NAT建站
     * @author hh
     * @version v1
     * @param   int nat_web_id - NAT建站ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.domain - 域名
     */
    public function natWebDelete($nat_web_id)
    {
    	$data = [];

    	$list = $this->idcsmartCloud->natWebList($this->id, ['page'=>1, 'per_page'=>999]);
		if(isset($list['data']['data'])){
			foreach($list['data']['data'] as $v){
				if($v['id'] == $nat_web_id){
					$data = $v;
					break;
				}
			}
		}
		if(empty($data)){
			$result = [
				'status' => 400,
				'msg'	 => lang_plugins('mf_cloud_nat_web_not_found'),
			];
			return $result;
		}

    	$res = $this->idcsmartCloud->natWebDelete($this->id, $nat_web_id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('delete_success'),
				'data'	 => [
					'domain' => $data['domain'],
				],
			];

			$description = lang_plugins('log_mf_cloud_nat_web_delete_success', [
				'{hostname}' => $this->hostModel['name'],
				'{domain}' 	 => $data['domain'],
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('delete_failed'),
				'data'	 => [
					'domain' => $data['domain'],
				],
			];

			$description = lang_plugins('log_mf_cloud_nat_web_delete_fail', [
				'{hostname}' => $this->hostModel['name'],
				'{domain}' 	 => $data['domain'],
			]);			
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
    }

    /**
     * 时间 2023-10-24
     * @title 获取可升降级套餐
     * @desc 获取可升降级套餐
     * @author hh
     * @version v1
     * @return  int list[].id - 套餐ID
     * @return  int list[].product_id - 商品ID
     * @return  string list[].name - 名称
     * @return  string list[].description - 描述
     * @return  int list[].order - 排序ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].cpu - CPU
     * @return  int list[].memory - 内存(GB)
     * @return  int list[].system_disk_size - 系统盘大小(GB)
     * @return  int list[].data_disk_size - 数据盘大小(GB)
     * @return  int list[].bw - 带宽(Mbps)
     * @return  int list[].peak_defence - 防御峰值(G)
     * @return  string list[].system_disk_type - 系统盘类型
     * @return  string list[].data_disk_type - 数据盘类型
     * @return  int list[].flow - 流量
     * @return  int list[].line_id - 线路ID
     * @return  int list[].create_time - 创建时间
     * @return  int list[].ip_num - IP数量
     * @return  int list[].upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int list[].hidden - 是否隐藏(0=否,1=是)
     * @return  int list[].gpu_num - 显卡数量
     * @return  string list[].gpu_name - 显卡名称
     * @return  int count - 总条数
     */
   	public function getUpgradeRecommendConfig()
   	{
   		if(empty($this->hostLinkModel['recommend_config_id'])){
   			return ['list'=> [], 'count'=>0];
   		}

   		$recommendConfig = RecommendConfigModel::find($this->hostLinkModel['recommend_config_id']);
   		if(empty($recommendConfig)){
   			$configData = json_decode($recommendConfig['config_data'], true);
   			$recommendConfig = $configData['recommend_config'];
   		}
   		$RecommendConfigModel = new RecommendConfigModel();
   		return $RecommendConfigModel->getUpgradeRecommendConfig($recommendConfig);
   	}

   	/**
   	 * 时间 2023-10-25
   	 * @title 计算升降级套餐价格
   	 * @desc 计算升降级套餐价格
   	 * @author hh
   	 * @version v1
   	 * @param   int param.recommend_config_id - 套餐ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格
     * @return  string data.description - 描述
     * @return  string data.price_difference - 差价
     * @return  string data.renew_price_difference - 续费差价
     * @return  string data.base_price - 基础价格
     * @return  array data.new_config_data.recommend_config - 新套餐数据
     * @return  array data.new_config_data.line - 新套餐线路数据
   	 */
   	public function calUpgradeRecommendConfig($param)
   	{
    	if(empty($this->hostLinkModel['recommend_config_id'])){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
    	}
    	$targetRecommendConfig = RecommendConfigModel::find($param['recommend_config_id'] ?? 0);
    	if(empty($targetRecommendConfig) || $this->hostLinkModel['recommend_config_id'] == $targetRecommendConfig['id'] || $targetRecommendConfig['hidden'] == 1){
    		return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
    	}

    	bcscale(2);
    	$productId = $this->hostModel['product_id'];
    	$hostId    = $this->hostModel['id'];
    	$diffTime  = $this->hostModel['due_time'] - time();

    	$configData = json_decode($this->hostLinkModel['config_data'], true);
    	$recommendConfig = RecommendConfigModel::find($this->hostLinkModel['recommend_config_id']) ?? $configData['recommend_config'];
    	if($recommendConfig['upgrade_range'] == RecommendConfigModel::UPGRADE_DISABLE){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_no_auth')];
        }else if($recommendConfig['upgrade_range'] == RecommendConfigModel::UPGRADE_ALL){
            
        }else if($recommendConfig['upgrade_range'] == RecommendConfigModel::UPGRADE_CUSTOM){
            $recommendConfigUpgradeRange = RecommendConfigUpgradeRangeModel::where('recommend_config_id', $recommendConfig['id'])->where('rel_recommend_config_id', $param['recommend_config_id'])->find();
           	if(empty($recommendConfigUpgradeRange)){
           		return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
           	}
        }
        if($targetRecommendConfig['product_id'] != $productId || $targetRecommendConfig['data_center_id'] != $recommendConfig['data_center_id']){
        	return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }

    	$newConfigData = [];

        // 获取之前的周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
    	if(empty($duration)){
    		return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_not_support_this_duration_to_upgrade')];
    	}

    	$oldPrice = PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $recommendConfig['id'])->where('duration_id', $duration['id'])->value('price');
    	$price = PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $targetRecommendConfig['id'])->where('duration_id', $duration['id'])->value('price');
    	$description = sprintf("%s: %s => %s", lang_plugins('mf_cloud_recommend_config'), $recommendConfig['name'], $targetRecommendConfig['name']);

        $newConfigData['recommend_config'] = $targetRecommendConfig;
        $newConfigData['line'] = LineModel::find($targetRecommendConfig['line_id']);

    	// 计算价格系数
    	if($duration['price_factor'] != 1){
    		$oldPrice = bcmul($oldPrice, $duration['price_factor']);
	    	$price = bcmul($price, $duration['price_factor']);
    	}

    	$basePrice = $price;

        $priceDifference = bcsub($price, $oldPrice);
        if($this->hostModel['billing_cycle_time']>0){
        	$price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
        }else{
        	$price = $priceDifference;
        }
		
        $price = max(0, $price);
        $price = amount_format($price);
        
        // 下游
        $isDownstream = isset($param['is_downstream']) && $param['is_downstream'] == 1;
        if($isDownstream){
            $DurationModel = new DurationModel();
            $price = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $price,
            ]);
            $priceDifference = $DurationModel->downstreamSubClientLevelPrice([
                'product_id' => $productId,
                'client_id'  => $this->hostModel['client_id'],
                'price'      => $priceDifference,
            ]);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' 					=> $price,
                'description' 				=> $description,
                'price_difference' 			=> $priceDifference,
                'renew_price_difference' 	=> $priceDifference,
                'new_config_data'			=> $newConfigData,
                'base_price' => $priceDifference
            ]
        ];
        return $result;
   	}

   	/**
     * 时间 2023-10-25
     * @title 生成升降级套餐订单
     * @desc 生成升降级套餐订单
     * @author hh
     * @version v1
	 * @param   int param.id - 产品ID require
     * @param   int param.recommend_config_id - 套餐ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
	 * @return  string data.id - 订单ID
     */
    public function createUpgradeRecommendConfigOrder($param)
    {
    	if(isset($param['is_downstream'])){
    		unset($param['is_downstream']);
    	}
        $res = $this->calUpgradeRecommendConfig($param);
        if($res['status'] == 400){
            return $res;
        }

        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'base_price' => $host['base_price']+$res['data']['base_price'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       			=> 'upgrade_recommend_config',
                'new_config_data'   	=> $res['data']['new_config_data'],
                'recommend_config_id' 	=> $param['recommend_config_id'],
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }


}
