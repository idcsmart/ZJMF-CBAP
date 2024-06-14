<?php
namespace reserver\whmcs_cloud\controller\admin;

use think\facade\Cache;
use think\facade\View;
use reserver\whmcs_cloud\validate\HostValidate;
use reserver\whmcs_cloud\logic\RouteLogic;
use app\common\model\HostModel;

/**
 * @title 魔方云代理(WHMCS)-后台内页操作
 * @desc 魔方云代理(WHMCS)-后台内页操作
 * @use reserver\whmcs_cloud\controller\admin\CloudController
 */
class CloudController{

	/**
	 * 时间 2024-05-22
	 * @title 获取操作系统列表
	 * @desc 获取操作系统列表
	 * @url /admin/v1/rewhmcs_cloud/:id/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int list[].id - 操作系统配置ID
	 * @return  string list[].osid - 操作系统ID
	 * @return  string list[].name - 操作系统名称
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
			$result = $RouteLogic->curl('service_clientArea&ac=cloudos_layer', ['hosting_id' => $RouteLogic->upstream_host_id], 'POST');
			if($result['status'] == 200){
				$result['data']['list'] = $result['data']['cloud_os'];
				unset($result['data']['cloud_os'],$result['data']['cloud_os_group']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->imageList();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取实例详情
	 * @desc 获取实例详情
	 * @url /admin/v1/rewhmcs_cloud/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int $id - 产品ID
	 * @return  object host_data - 产品数据
	 * @return  string host_data.domain - 产品标识
	 * @return  string host_data.dedicatedip - 独立IP
	 * @return  string host_data.username - 用户名
	 * @return  string host_data.password - 密码
	 * @return  string host_data.productname - 商品名称
	 * @return  int host_data.bwusage - 已用流量
	 * @return  int host_data.bwlimit - 流量限制
	 * @return  array host_data.assignedips - 附加IP
	 * @return  string host_data.type - 类型
	 * @return  int host_data.reset_flow_day - 流量重置时间
	 * @return  int host_data.port - 端口
	 * @return  int host_data.rescue - 救援系统0否1是
	 * @return  int host_data.image_group_id - 镜像分组ID
	 * @return  string host_data.panel_pass - 面板密码
	 * @return  int host_data.configoptionsupgrade - 支持升降级0否1是
	 * @return  array config_options - 产品配置
	 * @return  int config_options[].option_type - 配置类型
	 * @return  string config_options[].sub_name - 单位名称
	 * @return  string config_options[].name - 配置名称
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
			$RouteLogic->setTimeout(100);
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=product', ['hosting_id' => $RouteLogic->upstream_host_id], 'POST');
			if($result['status']==200){

			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->detail();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 开机
	 * @desc 开机
	 * @url /admin/v1/rewhmcs_cloud/:id/on
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'on'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->on();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 关机
	 * @desc 关机
	 * @url /admin/v1/rewhmcs_cloud/:id/off
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'off'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->off();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重启
	 * @desc 重启
	 * @url /admin/v1/rewhmcs_cloud/:id/reboot
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'reboot'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->reboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 强制关机
	 * @desc 强制关机
	 * @url /admin/v1/rewhmcs_cloud/:id/hard_off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function hardOff(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'hard_off'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->hardOff();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 强制重启
	 * @desc 强制重启
	 * @url /admin/v1/rewhmcs_cloud/:id/hard_reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function hardReboot(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'hard_reboot'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->hardReboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /admin/v1/rewhmcs_cloud/:id/vnc
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'vnc'], 'POST');
			if($result['status'] == 200){
				$cache = $result['data'];
				unset($cache['url']);

				Cache::set('idcsmart_cloud_vnc_'.$param['id'], $cache, 30*60);
				if(!isset($param['more']) || $param['more'] != 1){
					// 不获取更多信息
					$result['data'] = [];
				}
				// 转到当前res模块
				$result['data']['url'] = request()->domain().'/console/v1/rewhmcs_cloud/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
			}

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_vnc_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_vnc_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->vnc();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /admin/v1/rewhmcs_cloud/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function vncPage(){
		$param = request()->param();

		$cache = Cache::get('idcsmart_cloud_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('res_whmcs_cloud_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/whmcs_cloud/view/vnc_page.html');
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取实例状态
	 * @desc 获取实例状态
	 * @url /admin/v1/rewhmcs_cloud/:id/status
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default&requestTime='.time(), ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'status'], 'POST');
			if($result['status'] == 200){
				$result['data']['desc'] = $resultt['data']['des'] ?? '';
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->status();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重置密码
	 * @desc 重置密码
	 * @url /admin/v1/rewhmcs_cloud/:id/reset_password
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['func'] = 'crack_pass';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->resetPassword();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 救援系统
 	 * @desc 救援系统
	 * @url /admin/v1/rewhmcs_cloud/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
     * @param   int temp_pass - 临时密码 require
     * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function rescue(){
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
			$param['func'] = 'rescue';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->rescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 退出救援系统
 	 * @desc 退出救援系统
	 * @url /admin/v1/rewhmcs_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function exitRescue(){
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
			$param['func'] = 'exit_rescue';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_exit_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_exit_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->exitRescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /admin/v1/rewhmcs_cloud/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int os - 镜像ID require
	 * @param   string password - 密码 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reinstall(){
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
			$param['func'] = 'reinstall';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->reinstall();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}


}
