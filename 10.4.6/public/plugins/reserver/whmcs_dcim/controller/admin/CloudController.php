<?php
namespace reserver\whmcs_dcim\controller\admin;

use think\facade\Cache;
use think\facade\View;
use reserver\whmcs_dcim\validate\HostValidate;
use reserver\whmcs_dcim\logic\RouteLogic;
use app\common\model\HostModel;

/**
 * @title DCIM代理(WHMCS)-后台内页操作
 * @desc DCIM代理(WHMCS)-后台内页操作
 * @use reserver\whmcs_dcim\controller\admin\CloudController
 */
class CloudController{

	/**
	 * 时间 2023-02-06
	 * @title 获取操作系统列表
	 * @desc 获取操作系统列表
	 * @url /admin/v1/rewhmcs_dcim/:id/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  array os - 操作系统
	 * @return  string os[].id - 操作系统ID
	 * @return  string os[].name - 操作系统名称
	 * @return  array config - 分区配置
	 * @return  string config[].id - 分区配置ID
	 * @return  string config[].name - 分区配置名称
	 * @return  string config[].osname - 关联操作系统ID
	 * @return  array scripts - 安装脚本
	 * @return  string scripts[].id - 安装脚本ID
	 * @return  string scripts[].name - 安装脚本名称
	 * @return  string scripts[].osname - 关联操作系统ID
	 */
	public function imageList(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'os';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取实例详情
	 * @desc 获取实例详情
	 * @url /admin/v1/rewhmcs_dcim/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID
	 * @return  int serverid - 服务器ID
     * @return  object server - 服务器
     * @return  string server.id - 服务器ID
     * @return  string server.wltag - 物理标签
     * @return  string server.osname - 操作系统
     * @return  string server.power - 电源状态
     * @return  string server.power_msg - 电源状态描述
     * @return  string server.osusername - 操作系统用户名
     * @return  string server.ospassword - 操作系统密码
     * @return  string server.crack_success_info - 破解密码信息
     * @return  string server.crack_user - 破解用户
     * @return  string server.default_user - 默认用户
     * @return  string server.ippassword - 面板密码
     * @return  string server.port - 端口
     * @return  string server.main_ip - 主IP
     * @return  string server.configoptionsupgrade - 支持升降级0否1是
     * @return  array ip - 附加IP
     * @return  string ip[].ipaddress - IP地址
     * @return  string ip[].subnetmask - 子网掩码
     * @return  string ip[].gateway - 网关
     * @return  object configoptions - 当前配置
	 * @return  object configoptions.area.name - 配置area对应的名称
	 * @return  object configoptions.area.value - 配置area对应的值
	 * @return  object oldconfigoptions - 当前配置ID对应的值,例如{"1":1,"2":2}
	 * @return  object customfields - 自定义字段
	 */
	public function detail(){
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'detail';
			$res = $RouteLogic->curl('service_customFunctions', $param, 'POST');
			if($res['status'] == 200){
				unset($res['status']);
				if(isset($res['switch'])){
					unset($res['switch']);
				}
				if(isset($res['ip']['ip'])){
					$res['ip'] = $res['ip']['ip'];
					foreach ($res['ip'] as $key => $value) {
						$res['ip'][$key] = ['ipaddress' => $value['ipaddress'], 'subnetmask' => $value['subnetmask'], 'gateway' => $value['gateway']];
					}
					unset($res['switch']);
				}
				$result = ['status' => 200, 'msg' => lang_plugins('success_message'), 'data' => $res];
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 开机
	 * @desc 开机
	 * @url /admin/v1/rewhmcs_dcim/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function on(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'on';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 关机
	 * @desc  关机
	 * @url /admin/v1/rewhmcs_dcim/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function off(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'off';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 重启
	 * @desc 重启
	 * @url /admin/v1/rewhmcs_dcim/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reboot(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'reboot';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /admin/v1/rewhmcs_dcim/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'vnc';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
			if($result['status'] == 200){
				$result = [
					'status' => 200,
					'msg'    => lang_plugins('success_message'),
					'data'	 => [],
				];

	            if(strpos($res['host'], 'https://') !== false){
	                $link_url = str_replace('https://', 'wss://', $res['host']);
	            }else{
	                $link_url = str_replace('http://', 'ws://', $res['host']);
	            }
	            // vnc不能包含管理员路径
	            // $link_url = rtrim($link_url, '/');
	            // if(substr_count($link_url, '/') > 2){
	            //     $link_url = substr($link_url, 0, strrpos($link_url, '/'));
	            // }
	            $link_url .= '/websockify_'.$res['house'].'?token='.$res['token'];

	            // 获取的东西放入缓存
	            $cache = [
	            	'vnc_url' => $link_url,
	            	'vnc_pass'=> $res['vnc_pass'],
	            	'password'=> aes_password_decode($res['pass']),
	            ];

	            // 生成一个临时token
	            $token = md5(rand_str(16));
	            $cache['token'] = $token;

				Cache::set('rewhmcs_dcim_vnc_'.$param['id'], $cache, 30*60);
				if(!isset($param['more']) || $param['more'] != 1){
					// 不获取更多信息
					$result['data'] = [];
				}
				// 转到当前res模块
				$result['data']['url'] = request()->domain().'/console/v1/rewhmcs_dcim/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
			}
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /admin/v1/rewhmcs_dcim/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function vncPage(){
		$param = request()->param();

		$cache = Cache::get('rewhmcs_dcim_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('res_whmcs_dcim_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/whmcs_dcim/view/vnc_page.html');
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取实例状态
	 * @desc  获取实例状态
	 * @url /admin/v1/rewhmcs_dcim/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'status';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
			if($result['status'] == 200){
				$result['data'] = ['status' => $result['power'], 'desc' => $result['msg']];
				$result['msg'] = lang_plugins('success_message');
				unset($result['power']);
			}
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重置密码
	 * @desc  重置密码
	 * @url /admin/v1/rewhmcs_dcim/:id/reset_password
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string password - 新密码 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function resetPassword(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'crack';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 救援模式
	 * @desc  救援模式
	 * @url /admin/v1/rewhmcs_dcim/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int system - 指定救援系统类型(1=linux,2=windows) require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function rescue(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'rescue';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重装系统
 	 * @desc  重装系统
	 * @url /admin/v1/rewhmcs_dcim/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int mos - 镜像ID require
	 * @param   string password - 密码 require
	 * @param   int port - 端口 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reinstall(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'reinstall';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
		}
		return json($result);
	}


}
