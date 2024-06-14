<?php
namespace reserver\mf_cloud\controller\admin;

use app\admin\model\PluginModel;
use think\facade\Cache;
use think\facade\View;
use reserver\mf_cloud\logic\RouteLogic;
use app\common\model\HostModel;
use app\common\model\SupplierModel;

/**
 * @title 魔方云代理(自定义配置)-后台内页操作
 * @desc  魔方云代理(自定义配置)-后台内页操作
 * @use reserver\mf_cloud\controller\admin\CloudController
 */
class CloudController
{
	/**
     * 时间 2024-05-24
     * @title 后台详情
     * @desc  后台详情,用于提供后台实例操作获取配置
     * @url /admin/v1/remf_cloud/:id
	 * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  int image[].id - 操作系统分类ID
     * @return  string image[].name - 操作系统分类名称
     * @return  string image[].icon - 操作系统分类图标
     * @return  int image[].image[].id - 操作系统ID
     * @return  int image[].image[].image_group_id - 操作系统分类ID
     * @return  string image[].image[].name - 操作系统名称
     * @return  int image[].image[].charge - 是否收费(0=否,1=是)
     * @return  string image[].image[].price - 价格
     * @return  string config.type - 实例类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int config.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int config.rand_ssh_port - SSH端口设置(0=默认,1=随机端口,2=指定端口)
     * @return  string config.rand_ssh_port_start - 随机端口开始端口
     * @return  string config.rand_ssh_port_end - 随机端口结束端口
     * @return  string config.rand_ssh_port_windows - 指定端口Windows
     * @return  string config.rand_ssh_port_linux - 指定端口Linux
     */
	public function adminDetail(){
		$param = request()->param();

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'image' => [],
				'config' => [],
			],
		];

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			$result['data']['config'] = (object)$result['data']['config'];
			return json($result);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$res = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_cloud/order_page', $RouteLogic->upstream_product_id), [], 'GET');
			if($res['status'] == 200){
				if(isset($res['data']['config'])){
					$result['data']['config'] = [
						'type' => $res['data']['config']['type'],
						'support_ssh_key' => $res['data']['config']['support_ssh_key'],
						'rand_ssh_port' => $res['data']['config']['rand_ssh_port'],
						'rand_ssh_port_start' => $res['data']['config']['rand_ssh_port_start'],
						'rand_ssh_port_end' => $res['data']['config']['rand_ssh_port_end'],
						'rand_ssh_port_windows' => $res['data']['config']['rand_ssh_port_windows'],
						'rand_ssh_port_linux' => $res['data']['config']['rand_ssh_port_linux'],
					];
				}
			}

			// 操作系统
			$res = $RouteLogic->curl( sprintf('console/v1/product/%s/remf_cloud/image', $RouteLogic->upstream_product_id), [], 'GET');
			if($res['status'] == 200){
                // 处理多级代理问题
                $PluginModel = new PluginModel();
                $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
				// 计算价格倍率
				foreach($res['data']['list'] as $k=>$v){
					foreach($v['image'] as $kk=>$vv){
						// 下游先直接排除收费操作系统
						if($vv['charge'] == 1 && $vv['price'] > 0){
							unset($res['data']['list'][$k]['image'][$kk]);
							continue;
						}
					}
					// 分组下是否还有操作系统
					if(empty($res['data']['list'][$k]['image'])){
						unset($res['data']['list'][$k]);
					}else{
						$res['data']['list'][$k]['image'] = array_values($res['data']['list'][$k]['image']);
					}
				}
				$result['data']['image'] = array_values($res['data']['list']);
			}
		}catch(\Exception $e){

		}
		$result['data']['config'] = (object)$result['data']['config'];
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 开机
	 * @desc  开机
	 * @url /admin/v1/remf_cloud/:id/on
	 * @method  POST
	 * @author  hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function on()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/on', $RouteLogic->upstream_host_id), [], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->on();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 关机
	 * @desc  关机
	 * @url /admin/v1/remf_cloud/:id/off
	 * @method  POST
	 * @author  hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function off()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/off', $RouteLogic->upstream_host_id), [], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->off();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重启
	 * @desc  重启
	 * @url /admin/v1/remf_cloud/:id/reboot
	 * @method  POST
	 * @author  hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reboot()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/reboot', $RouteLogic->upstream_host_id), [], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->reboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 强制关机
	 * @desc  强制关机
	 * @url /admin/v1/remf_cloud/:id/hard_off
	 * @method  POST
	 * @author  hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function hardOff()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/hard_off', $RouteLogic->upstream_host_id), [], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_hard_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_hard_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->hardOff();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 强制重启
	 * @desc  强制重启
	 * @url /admin/v1/remf_cloud/:id/hard_reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function hardReboot()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/hard_reboot', $RouteLogic->upstream_host_id), [], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_hard_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_hard_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->hardReboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取控制台地址
	 * @desc  获取控制台地址
	 * @url /admin/v1/remf_cloud/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 * @return  string url - 控制台地址
	 * @return  string vnc_url - vncwebsocket地址(more=1返回)
	 * @return  string vnc_pass - VNC密码(more=1返回)
	 * @return  string password - 实例密码(more=1返回)
	 * @return  string token - 临时令牌(more=1返回)
	 */
	public function vnc()
	{
		$param = request()->param();
		$param['more'] = 0;

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/vnc?more=1', $RouteLogic->upstream_host_id), [], 'POST');
			if($result['status'] == 200){
				$cache = $result['data'];
				unset($cache['url']);

				Cache::set('idcsmart_cloud_vnc_'.$param['id'], $cache, 30*60);
				if(!isset($param['more']) || $param['more'] != 1){
					// 不获取更多信息
					$result['data'] = [];
				}
				// 转到当前res模块
				$result['data']['url'] = request()->domain().'/console/v1/remf_cloud/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
			}

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_vnc_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_vnc_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->vnc();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 控制台页面
	 * @desc  控制台页面
	 * @url /admin/v1/remf_cloud/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string temp_token - 临时令牌 require
	 */
	public function vncPage()
	{
		$param = request()->param();

		$cache = Cache::get('idcsmart_cloud_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('res_mf_cloud_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/mf_cloud/view/vnc_page.html');
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取实例状态
	 * @desc  获取实例状态
	 * @url /admin/v1/remf_cloud/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string data.status - 实例状态(on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/status', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->status();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重置密码
	 * @desc  重置密码
	 * @url /admin/v1/remf_cloud/:id/reset_password
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string password - 新密码 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function resetPassword()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/reset_password', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->resetPassword();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 救援模式
	 * @desc  救援模式
	 * @url /admin/v1/remf_cloud/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
	 * @param   string password - 救援系统临时密码 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function rescue()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/rescue', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->rescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 退出救援模式
	 * @desc  退出救援模式
	 * @url /admin/v1/remf_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function exitRescue()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/rescue/exit', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_exit_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_exit_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->exitRescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重装系统
 	 * @desc  重装系统
	 * @url /admin/v1/remf_cloud/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   int password - 密码 require
	 * @param   int port - 端口 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reinstall()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/reinstall', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_cloud_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_cloud_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->reinstall();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取魔方云远程信息
	 * @desc  获取魔方云远程信息
	 * @url /admin/v1/remf_cloud/:id/remote_info
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int rescue - 是否正在救援系统(0=不是,1=是)
	 * @return  string username - 远程用户名
	 * @return  string password - 远程密码
	 * @return  int port - 远程端口
	 * @return  int ip_num - IP数量
	 */
	public function remoteInfo()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_cloud/%d/remote_info', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_cloud\controller\home\CloudController')){
					return (new \server\mf_cloud\controller\home\CloudController())->remoteInfo();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_cloud_act_exception')];
			}
		}
		return json($result);
	}

}
