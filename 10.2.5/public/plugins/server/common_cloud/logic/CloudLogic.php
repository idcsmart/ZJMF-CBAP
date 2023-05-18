<?php 
namespace server\common_cloud\logic;

use server\common_cloud\idcsmart_cloud\IdcsmartCloud;
use server\common_cloud\model\HostLinkModel;
use server\common_cloud\model\ImageModel;
use server\common_cloud\model\HostImageLinkModel;
use server\common_cloud\model\ConfigModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
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
		if(empty($HostModel)){
			throw new \Exception(lang_plugins('host_is_not_exist'));
		}
		// 是否是魔方云模块
		if($HostModel->getModule() != 'common_cloud'){
			throw new \Exception(lang_plugins('can_not_do_this'));
		}
		// 获取模块通用参数
		$params = $HostModel->getModuleParams();
		if(empty($params['server'])){
			throw new \Exception(lang_plugins('host_not_link_server'));
		}
		$this->idcsmartCloud = new IdcsmartCloud($params['server']);
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
	 */
	public function on(){
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
	 */
	public function off(){
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
	 */
	public function hardOff(){
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
	 */
	public function reboot(){
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
	 */
	public function hardReboot(){
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
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
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
                    $result['data']['url'] = request()->domain().'/console/v1/common_cloud/'.$this->hostModel['id'].'/vnc';
                }else{
                    $result['data']['url'] = request()->domain().'/'.DIR_ADMIN.'/v1/common_cloud/'.$this->hostModel['id'].'/vnc';
                }
            }

            // 生成一个临时token
            $token = md5(rand_str(16));
            $cache['token'] = $token;

            Cache::set('idcsmart_cloud_vnc_'.$this->hostModel['id'], $cache, 30*60);
        	if(strpos($result['data']['url'], '?') !== false){
        		$result['data']['url'] .= '&tmp_token='.$token;
        	}else{
        		$result['data']['url'] .= '?tmp_token='.$token;
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
	 * @param   string $param.password - 新密码
	 */
	public function resetPassword($param){
		$res = $this->idcsmartCloud->cloudResetPassword($this->id, $param['password']);
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
	 * @param   string param.password - 救援系统临时密码 require
	 */
	public function rescue($param){
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
	 */
	public function exitRescue(){
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
	 * @param   int param.image_id - 镜像ID 镜像ID
	 * @param   int param.password - 密码 密码和ssh密钥ID,必须选择一种
	 * @param   int param.ssh_key_id - ssh密钥ID 密码和ssh密钥ID,必须选择一种
	 * @param   int param.port - 端口 require
	 */
	public function reinstall($param){
		// 请求数据
		$post = [];
		// 更新数据
		$update = [];
		// 有镜像优先使用镜像
		// if(isset($param['image_id']) && !empty($param['image_id'])){
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
			if(!empty($image['rel_image_id'])){
				$imageCheck['data']['id'] = $image['rel_image_id'];
			}else{
				$imageCheck = $this->idcsmartCloud->getImageId($image['filename']);
				if(!isset($imageCheck['data']['id']) || empty($imageCheck['data']['id'])){
					return ['status'=>400, 'msg'=>lang_plugins('image_not_in_zjmf_cloud')];
				}
			}
			$post['os'] = $imageCheck['data']['id'];
			$update['image_id'] = $param['image_id'];
		// }else{
		// 	// 验证当前模板是否可用
		// 	$list = $this->idcsmartCloud->cloudTemplate($this->id);
		// 	$list = $list['data']['data'] ?? [];
		// 	if(!in_array($param['template_id'] ?? 0, array_column($list, 'id') ?? [])){
		// 		return ['status'=>400, 'msg'=>lang_plugins('template_not_found')];
		// 	}
		// 	$post['template'] = $param['template_id'];
		// 	// 模板是什么镜像呢?
		// 	// $update['module_idcsmart_cloud_image_id'] = 0;
		// }
		if(isset($param['password']) && !empty($param['password'])){
			$post['password'] = $param['password'];
			$post['password_type'] = 0;

			$update['password'] = aes_password_encode($param['password']);
			$update['assh_key_id'] = 0;
		}else{
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
				'type' => 1,
				'uid'  => $detail['data']['user_id'],
				'name' => $sshKey['name'].'_'.time(),
				'public_key'=>$sshKey['public_key'],
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
	public function chart($param){
		if(!isset($param['type']) || !in_array($param['type'], ['cpu','memory','disk_io','bw'])){
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
	public function flowDetail(){
		$res = $this->idcsmartCloud->netInfo($this->id);

		if($res['status'] == 200 && !empty($res['data'])){
			$total = $res['data']['meta']['traffic_quota'] + $res['data']['meta']['tmp_traffic'];
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
	 * @return  array data.list -  列表数据 
	 * @return  int data.list[].id - 磁盘ID
	 * @return  string data.list[].name - 磁盘名称
	 * @return  int data.list[].size - 磁盘大小,GB
	 * @return  bool data.list[].resize - 是否支持扩容(false=不能扩容,true=可以扩容)
	 */
	public function diskList()
	{
		// 获取磁盘列表
		$res = $this->idcsmartCloud->cloudDetail($this->id);
		if($res['status'] == 400){
			return ['list' => []];
		}
		$disk = $res['data']['disk'];
		$list = [];
		foreach ($disk as $k => $v) {
			$one = [
				'id' => $v['id'],
				'name' => $v['name'],
				'size' => $v['size'],
				'create_time' => $v['create_time'],
				'resize' => true
			];
			if($v['type'] == 'system' || $v['id'] == $this->hostLinkModel['free_disk_id']){
				$one['resize'] = false;
			}
			$list[] = $one;
		}

		return ['list' => $list];

	}


	/**
	 * 时间 2022-06-27
	 * @title 快照列表
	 * @desc 快照列表
	 * @author hh
	 * @version v1
	 * @param   int param.page - 页数
	 * @param   int param.limit - 每页条数
	 * @return  array data.list -  列表数据 
	 * @return  int data.list[].id - 快照ID
	 * @return  string data.list[].name - 快照名称
	 * @return  int data.list[].create_time - 创建时间
	 * @return  string data.list[].notes - 备注
	 * @return  int data.count - 总条数
	 */
	public function snapshotList($param){
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
	        		'notes'=>$v['remarks']
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
	 * @param   int param.disk_id - 磁盘ID require
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
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_fount')];
		}
		$res = $this->idcsmartCloud->snapshotCreate($param['disk_id'], ['type' => 'snap', 'name' => $param['name']]);
		if($res['status'] == 200){
			// 创建成功
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_create_snapshot_success')
			];

			$description = lang_plugins('log_host_start_create_snap_success', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$param['name']
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_create_snapshot_failed')
			];

			$description = lang_plugins('log_host_start_create_snap_failed', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$param['name']
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
	 */
	public function snapshotRestore($param){
		// 获取快照列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'snap']);
		if($res['status'] == 400){
			return $res;
		}
		$snapshot = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		if(!isset($snapshot[ $param['snapshot_id'] ])){
			return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotRestore($this->id, (int)$param['snapshot_id']);

		if($res['status'] == 200){
			// 还原成功,更新密码,端口信息
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_snapshot_restore_success')
			];

			$update = [];
			$update['password'] = aes_password_encode($res['data']['os']['password']);

			$image = ImageModel::where('rel_image_id', $res['data']['os']['id'])->where('product_id', $this->hostModel['product_id'])->find();
			if(!empty($image)){
				$update['image_id'] = $image['id'];
			}

			$this->hostLinkModel->update($update, ['id'=>$this->hostLinkModel['id']]);

			$description = lang_plugins('log_host_start_snap_restore_success', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$snapshot[ $param['snapshot_id'] ]
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_snapshot_restore_failed')
			];

			$description = lang_plugins('log_host_start_snap_restore_failed', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$snapshot[ $param['snapshot_id'] ]
			]);
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除快照
	 * @author hh
	 * @version v1
	 * @param   int id - 快照ID
	 * @return  [type] [description]
	 */
	public function snapshotDelete($id){
		// 获取快照列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'snap']);
		if($res['status'] == 400){
			return $res;
		}
		$snapshot = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		if(!isset($snapshot[$id])){
			return ['status'=>400, 'msg'=>lang_plugins('snapshot_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotDelete($this->id, $id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('delete_snapshot_success')
			];

			$description = lang_plugins('log_host_delete_snap_success', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$snapshot[ $id ]
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('delete_snapshot_failed')
			];

			$description = lang_plugins('log_host_delete_snap_failed', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$snapshot[ $id ]
			]);
		}
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
	 * @return  array data.list -  列表数据 
	 * @return  int data.list[].id - 备份ID
	 * @return  string data.list[].name - 备份名称
	 * @return  int data.list[].create_time - 创建时间
	 * @return  string data.list[].notes - 备注
	 * @return  int data.count - 总条数
	 */
	public function backupList($param){
		$param['page'] = $param['page'] ?? 1;
        $param['per_page'] = $param['limit'] ?? config('idcsmart.limit');
        // $param['sort'] = $param['sort'] ?? config('idcsmart.sort');
        $param['type'] = 'backup';
        
        $res = $this->idcsmartCloud->cloudSnapshot($this->id, $param);

        $data = [];
        if(isset($res['data']['data'])){
        	foreach($res['data']['data'] as $v){
	        	$data[] = [
	        		'id'=>$v['id'],
	        		'name'=>$v['name'],
	        		'create_time'=>strtotime($v['create_time']),
	        		'notes'=>$v['remarks']
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
	 * @param   int param.disk_id - 磁盘ID require
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
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_fount')];
		}
		$res = $this->idcsmartCloud->snapshotCreate($param['disk_id'], ['type' => 'backup', 'name' => $param['name']]);
		if($res['status'] == 200){
			// 创建成功
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_create_backup_success')
			];

			$description = lang_plugins('log_host_start_create_backup_success', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$disk[$param['disk_id']]
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_create_backup_failed')
			];

			$description = lang_plugins('log_host_start_create_backup_failed', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$disk[$param['disk_id']]
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
	 */
	public function backupRestore($param){
		// 获取备份列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'backup']);
		if($res['status'] == 400){
			return $res;
		}
		$backup = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		if(!isset($backup[$param['backup_id']])){
			return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotRestore($this->id, (int)$param['backup_id']);
		if($res['status'] == 200){
			// 还原成功,更新密码,端口信息
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('start_backup_restore_success')
			];

			$update = [];
			$update['password'] = aes_password_encode($res['data']['os']['password']);

			$image = ImageModel::where('rel_image_id', $res['data']['os']['id'])->where('product_id', $this->hostModel['product_id'])->find();
			if(!empty($image)){
				$update['image_id'] = $image['id'];
			}

			$this->hostLinkModel->update($update, ['id'=>$this->hostLinkModel['id']]);

			$result['data'] = $update;

			$description = lang_plugins('log_host_start_backup_restore_success', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$backup[$param['backup_id']]
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('start_backup_restore_failed')
			];

			$description = lang_plugins('log_host_start_backup_restore_failed', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$backup[$param['backup_id']]
			]);
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除备份
	 * @author hh
	 * @version v1
	 * @param   int id - 备份ID require
	 */
	public function backupDelete($id){
		// 获取备份列表
		$res = $this->idcsmartCloud->cloudSnapshot($this->id, ['per_page'=>999, 'type'=>'backup']);
		if($res['status'] == 400){
			return $res;
		}
		$backup = array_column($res['data']['data'] ?? [], 'remarks', 'id');
		if(!isset($backup[$id])){
			return ['status'=>400, 'msg'=>lang_plugins('backup_not_found')];
		}
		$res = $this->idcsmartCloud->snapshotDelete($this->id, $id);
		if($res['status'] == 200){
			$result = [
				'status' => 200,
				'msg'    => lang_plugins('delete_backup_success')
			];

			$description = lang_plugins('log_host_delete_backup_success', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$backup[$id]
			]);
		}else{
			$result = [
				'status' => 400,
				'msg'    => lang_plugins('delete_backup_failed')
			];

			$description = lang_plugins('log_host_delete_backup_failed', [
				'hostname'=>$this->hostModel['name'],
				'name'=>$backup[$id]
			]);
		}
		active_log($description, 'host', $this->hostModel['id']);
		return $result;
	}

	/**
	 * 时间 2022-06-27
	 * @title 获取魔方云真实详情
	 * @desc 获取魔方云真实详情
	 * @author hh
	 * @version v1
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
			$data['rescue'] = $detail['data']['rescue'];
			$data['username'] = $detail['data']['osuser'];
			$data['password'] = $detail['data']['rootpassword'];
			$data['port'] = $detail['data']['port'] > 0 ? $detail['data']['port'] : ($detail['data']['image_group_id'] == 1 ? 3306 : 22);
			$data['ip_num'] = $detail['data']['ip_num'];
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
	 */
	public function ipList($param)
	{
		$param['page'] = $param['page']>0 ? $param['page'] : 1;
		$param['limit'] = $param['limit']>0 ? $param['limit'] : 20;

		$data = [];
		$count = 0;

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

		return ['list'=>$data, 'count'=>$count];
	}

	
	/**
     * 时间 2022-09-25
     * @title 计算磁盘价格
     * @desc 计算磁盘价格
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function calDiskPrice($param){
        $ConfigModel = ConfigModel::where('product_id', $this->hostModel['product_id'])->find();

        $diffTime = $this->hostModel['due_time'] - time();
        
        // 获取之前的周期
        $duration = HostLinkModel::getDuration($this->hostModel);

        $price = 0;
        $priceDifference = 0;
        $add_size = [];
        $del_size = [];
        $diskNum = 0;

        $res = $this->idcsmartCloud->cloudDetail($this->id);
		if($res['status'] == 400){
			return ['status'=>400, 'msg'=>lang_plugins('host_status_except_please_wait_and_retry')];
		}
		$disk = $res['data']['disk'];

		$dataDisk = [];
		foreach($disk as $v){
			if($v['type'] == 'data'){
				$dataDisk[$v['id']] = $v['size'];
				if($v['id'] != $this->hostLinkModel['free_disk_id']){
					$diskNum++;
				}
			}
		}

        // 有要取消的磁盘
        if(isset($param['remove_disk_id']) && !empty(array_filter($param['remove_disk_id']))){
        	if(!empty($this->hostLinkModel['free_disk_id']) && in_array($this->hostLinkModel['free_disk_id'], $param['remove_disk_id'])){
        		return ['status'=>400, 'msg'=>lang_plugins('free_disk_cannot_cancel')];
        	}
			$size = 0;
			foreach($param['remove_disk_id'] as $v){
				if( !isset($dataDisk[$v]) ){
					return ['status'=>400, 'msg'=>lang_plugins('disk_error')];
				}
				$size += $dataDisk[$v];
				$del_size[] = $dataDisk[$v];
				$diskNum--;
			}
			// 取消的价格
			// if($this->hostModel['billing_cycle'] == 'onetime' || $diffTime<=0){
			// 	// 不允许白嫖
			// 	$price = 0;
			// }else{
			// 	$price = 0;// - ( $size/10*$ConfigModel['price'] ) * $diffTime/$this->hostModel['billing_cycle_time'];
			// 	// $priceDifference -= $size/10*$ConfigModel['price'];
			// }
			$priceDifference -= $size/10*$ConfigModel['price'];
        }
        $diskNum = max(0, $diskNum);
        // 新购磁盘
        if(isset($param['add_disk']) && !empty(array_filter($param['add_disk']))){
        	$param['add_disk'] = array_filter($param['add_disk']);

        	if($diskNum + count($param['add_disk']) > $ConfigModel['disk_max_num']){
            	return ['status'=>400, 'msg'=>lang_plugins('over_max_disk_num', ['{num}'=>$ConfigModel['disk_max_num']])];
        	}
        	// 验证磁盘
        	if($ConfigModel['buy_data_disk'] != 1){
        		return ['status'=>400, 'msg'=>lang_plugins('now_cannot_buy_disk')];
        	}
        	$check = $ConfigModel->checkDiskArr($param['add_disk']);
        	if($check['status'] != 200){
        		return $check;
        	}
        	$size = $check['data']['size'];

        	if($this->hostModel['billing_cycle'] == 'free'){
        		$price = 0;
        	}else if($this->hostModel['billing_cycle'] == 'onetime' || $diffTime<=0 || $this->hostModel['billing_cycle_time'] == 0){
				// 不允许白嫖
				$price += $size/10*$ConfigModel['price'];
			}else{
				$price += ( $size/10*$ConfigModel['price'] ) * $diffTime/$this->hostModel['billing_cycle_time'];
			}
			$priceDifference += $size/10*$ConfigModel['price'];
			$add_size = $param['add_disk'];
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
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
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
	 * @param   array remove_disk_id - 要取消订购的磁盘ID
	 * @param   array add_disk - 新增磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createBuyDiskOrder($param){
        $res = $this->calDiskPrice($param);
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
                'type'       => 'buy_disk',
                'remove_disk_id' => array_filter($param['remove_disk_id'] ?? []),
                'add_disk' => array_filter($param['add_disk'] ?? []),
            ]
        ];
        return $OrderModel->createOrder($data);
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
    public function calResizeDiskPrice($param){
        $ConfigModel = ConfigModel::where('product_id', $this->hostModel['product_id'])->find();

        $diffTime = $this->hostModel['due_time'] - time();
        // 获取之前的周期
        $duration = HostLinkModel::getDuration($this->hostModel);

        $price = 0;
        $priceDifference = 0;
        
       	// 检查参数
       	$allArr = [];
       	foreach($param['resize_data_disk'] as $v){
       		if(!isset($v['id']) || !isset($v['size'])){
       			return ['status'=>400, 'msg'=>lang_plugins('param_error')];
       		}
       		$check = $ConfigModel->checkDisk($v['size']);
       		if($check['status'] != 200){
       			return $check;
       		}
       		$allArr[ $v['id'] ] = $v['size'];
       	}
    	$res = $this->idcsmartCloud->cloudDetail($this->id);
		if($res['status'] == 400){
			return ['status'=>400, 'msg'=>lang_plugins('host_status_except_please_wait_and_retry')];
		}
		$disk = $res['data']['disk'];

		$inc = 0;
		$description = '';

		$resizeDisk = [];
		foreach($disk as $v){
			if(isset($allArr[ $v['id'] ]) && $v['size'] != $allArr[ $v['id'] ]){
				if($v['type'] == 'system'){
					return ['status'=>400, 'msg'=>lang_plugins('system_disk_not_support_resize')];
				}
				if($v['id'] == $this->hostLinkModel['free_disk_id']){
					return ['status'=>400, 'msg'=>lang_plugins('free_disk_not_support_resize')];
				}
				if($allArr[ $v['id'] ] < $v['size']){
					return ['status'=>400, 'msg'=>lang_plugins('disk_cannot_downgrade')];
				}
				$resizeDisk[] = [
					'id'=>$v['id'],
					'size'=>$allArr[$v['id']]
				];
				$inc += $allArr[ $v['id'] ] - $v['size'];
				$description .= lang_plugins('upgrade_data_disk_size', [
					'{name}'=>$v['name'],
					'{old}'=>$v['size'],
					'{new}'=>$allArr[ $v['id'] ]
				]);
			}
		}
		if(empty($inc)){
			return ['status'=>400, 'msg'=>lang_plugins('disk_not_resize')];
		}
		
		if($this->hostModel['billing_cycle'] == 'free'){
			$price = 0;
			$priceDifference = 0;
		}else if($this->hostModel['billing_cycle'] == 'onetime' || $diffTime<=0){
			// 不允许白嫖
			$priceDifference = $inc/10*$ConfigModel['price'];

			$price = $priceDifference;
		}else{
			$priceDifference = $inc/10*$ConfigModel['price'];

			$price = $priceDifference * $diffTime/$this->hostModel['billing_cycle_time'];
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
                'resize_disk'=>$resizeDisk
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
	 * @param   array remove_disk_id - 要取消订购的磁盘ID
	 * @param   array add_disk - 新增磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createResizeDiskOrder($param){
        $res = $this->calResizeDiskPrice($param);
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
                'type'       => 'resize_disk',
                'resize_disk' => $res['data']['resize_disk'],
            ]
        ];
        return $OrderModel->createOrder($data);
    }






}
