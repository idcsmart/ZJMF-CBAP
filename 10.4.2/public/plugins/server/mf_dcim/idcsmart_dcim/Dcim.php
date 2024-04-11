<?php 
namespace server\mf_dcim\idcsmart_dcim;

/**
 * DCIM操作类
 */
class Dcim{

	protected $username = '';  // 用户名
	protected $password = '';  // 密码
	protected $url 		= '';  // 基础地址,包含二级目录
	protected $timeout  = 30;  // 超时时间

	public function __construct($config){
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->url 		= rtrim($config['url'], '/') . '/index.php?m=api&a=';
	}

	/**
	 * 时间 2022-09-25
	 * @title 开通
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function create($params){
		return $this->request('ipmiCreate', $params, 180);
	}

	/**
	 * 时间 2022-09-26
	 * @title 刷新电源状态
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function powerStatus($params){
		return $this->request('ipmiPowerSync', $params);
	}

	/**
	 * 时间 2022-09-25
	 * @title 开机
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function on($params){
		return $this->request('ipmiOn', $params);
	}

	/**
	 * 时间 2022-09-25
	 * @title 关机
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function off($params){
		return $this->request('ipmiOff', $params);
	}

	/**
	 * 时间 2022-09-25
	 * @title 重启
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function reboot($params){
		return $this->request('ipmiReboot', $params);
	}

	/**
	 * 时间 2022-09-25
	 * @title 救援系统
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function rescue($params){
		return $this->request('ipmiRescueSystem', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 暂停
	 * @author hh
	 * @version v1
	 * @param   array $params 请加参数
	 */
	public function suspend($params){
		return $this->request('ipmiSuspend', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 解除暂停
	 * @author hh
	 * @version v1
	 * @param   array params 请求参数
	 */
	public function unsuspend($params){
		return $this->request('ipmiUnsuspend', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 删除
	 * @author hh
	 * @version v1
	 * @param   array params 请求参数
	 */
	public function delete($params){
		return $this->request('ipmiTerminate', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 重启VNC
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function restartVnc($params){
		return $this->request('ipmiVncRestart', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title vnc
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function vnc($params){
		return $this->request('ipmiVnc', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 获取重装状态
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function getReinstallStatus($params){
		return $this->request('getReinstallStatus', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 重装系统
	 * @author hh
	 * @version v1
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function reinstall($params){
		return $this->request('reinstallSystem', $params);
	}

	/**
	 * 时间 2022-09-25
	 * @title 重置密码
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function resetPassword($params){
		return $this->request('ipmiCrackPwd', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 获取流量图
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function traffic($params){
		return $this->request('traffic', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 获取流量图
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function flow($params){
		return $this->request('serverDetailflowData', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 流量清零
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function resetFlow($params){
		return $this->request('resetServerFlow', $params);
	}

	/**
	 * 时间 2022-09-27
	 * @title 流量超额
	 * @author hh
	 * @version v1
	 * @param   string x             - x
	 * @return  [type] [description]
	 */
	public function overFlow($params){
		return $this->request('overTraffic', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 修改流量限制
	 * @author hh
	 * @version v1
	 * @param   int params.id      服务器ID
	 * @param   int params.traffic 流量限制 
	 */
	public function modifyFlowLimit($params){
		return $this->request('modifyServerTraffic', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 修改进带宽
	 * @author hh
	 * @version v1
	 * @param   int params.num       带宽大小
	 * @param   int params.server_id 服务器ID
	 */
	public function modifyInBw($params){
		return $this->request('inBwSetting', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 修改出带宽
	 * @author hh
	 * @version v1
	 * @param   int params.num       带宽大小
	 * @param   int params.server_id 服务器ID
	 */
	public function modifyOutBw($params){
		return $this->request('outBwSetting', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 服务器IP列表
	 * @author hh
	 * @version v1
	 * @param   string x             - x
	 */
	public function ipList($params){
		return $this->request('serverIp', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 修改IP数量
	 * @author hh
	 * @version v1
	 * @param   int params.ip_num    IP数量
	 * @param   int params.id        服务器ID
	 */
	public function modifyIpNum($params){
		return $this->request('setServerIp', $params);
	}

	/**
	 * 时间 2022-09-26
	 * @title 服务器详情
	 * @author hh
	 * @version v1
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function detail($params){
		return $this->request('serverDetailed', $params);
	}

	/**
	 * 时间 2022-09-25
	 * @title 获取所有可用镜像
	 * @author hh
	 * @version v1
	 */
	public function getAllMirrorOs(){
		return $this->request('getAllMirrorOs');
	}	 

	/**
	 * 时间 2022-06-08
	 * @title 登录
	 * @author hh
	 * @version v1
	 */
	public function login(){
		return $this->request('getHouse');
	}

	/**
	 * 时间 2023-06-08
	 * @title 增加临时流量
	 * @author hh
	 * @version v1
	 */
	public function addTempTraffic($params){
		return $this->request('addTempTraffic', $params);
	}

	/**
	 * 时间 2023-09-06
	 * @title 同步数据
	 * @author hh
	 * @version v1
	 */
	public function ipmiSync($params){
		return $this->request('ipmiSync', $params);
	}

	/**
	 * 时间 2024-01-18
	 * @title 销售列表
	 * @author hh
	 * @version v1
	 */
	public function overview($params){
		return $this->request('overview', $params);
	}

	/**
	 * 时间 2023-09-07
	 * @title 
	 * @author hh
	 * @version v1
	 * @param   string x       -             x
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function editServerSales($params){

	}


	/* 功能方法 */

	/**
	 * 时间 2022-06-08
	 * @title 请求
	 * @author hh
	 * @version v1
	 * @param   string $path    地址
	 * @param   array  $data    数据
	 * @param   string $request 请求方式
	 */
	public function request($path, $data = []){
		$url = $this->url . $path;

		$data['username'] = $this->username;
		$data['password'] = $this->password;

		// 调用公共curl方法
		$res = curl($url, $data, $this->timeout, 'POST');
		if(!empty($res['error'])){
			return ['status'=>400, 'msg'=>'无法连接到服务器管理系统,CURL_ERROR: '.$res['error']];
		}
		if($res['http_code'] != 200){
			return ['status'=>400, 'msg'=>'无法连接到服务器管理系统,HTTP状态码:'.$res['http_code']];
		}
		$content = json_decode($res['content'] ?? '', true) ?: [];
		if(isset($content['status']) && $content['status'] == 'error'){
			$result = ['status'=>400, 'msg'=>$content['msg'] ?? '执行失败'];
		}else{
			$content['status'] = 200;

			$result = $content;
		}
		return $result;
	}
}

