<?php
namespace reserver\mf_dcim\controller\admin;

use app\admin\model\PluginModel;
use think\facade\Cache;
use think\facade\View;
use reserver\mf_dcim\logic\RouteLogic;
use app\common\model\HostModel;
use app\common\model\SupplierModel;

/**
 * @title 魔方DCIM代理(自定义配置)-后台内页操作
 * @desc  魔方DCIM代理(自定义配置)-后台内页操作
 * @use reserver\mf_dcim\controller\admin\CloudController
 */
class CloudController
{
	/**
     * 时间 2024-05-24
     * @title 后台详情
     * @desc  后台详情,用于提供后台实例操作获取配置
     * @url /admin/v1/remf_dcim/:id
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
     */
	public function adminDetail(){
		$param = request()->param();

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'image' => [],
			],
		];

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json($result);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			// 操作系统
			$res = $RouteLogic->curl( sprintf('console/v1/product/%s/remf_dcim/image', $RouteLogic->upstream_product_id), [], 'GET');
			if($res['status'] == 200){
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
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 开机
	 * @desc  开机
	 * @url /admin/v1/remf_dcim/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function on()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/on', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->on();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 关机
	 * @desc  关机
	 * @url /admin/v1/remf_dcim/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function off()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/off', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->off();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重启
	 * @desc  重启
	 * @url /admin/v1/remf_dcim/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reboot()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reboot', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->reboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取控制台地址
	 * @desc  获取控制台地址
	 * @url /admin/v1/remf_dcim/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 * @return  string url - 控制台地址
	 * @return  string vnc_url - 控制台websocket地址(more=1返回)
	 * @return  string vnc_pass - vnc密码(more=1返回)
	 * @return  string password - 机器密码(more=1返回)
	 * @return  string token - 控制台页面令牌(more=1返回)
	 */
	public function vnc()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/vnc?more=1', $RouteLogic->upstream_host_id), [], 'POST');

			if($result['status'] == 200){
				$cache = $result['data'];
				unset($cache['url']);
 
				if(isset($cache['token'])){
					Cache::set('remf_dcim_vnc_'.$param['id'], $cache, 30*60);
					if(!isset($param['more']) || $param['more'] != 1){
						// 不获取更多信息
						$result['data'] = [];
					}
					// 转到当前res模块
					$result['data']['url'] = request()->domain().'/console/v1/remf_dcim/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
				}
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->vnc();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 控制台页面
	 * @desc  控制台页面
	 * @url /admin/v1/remf_dcim/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string tmp_token - 控制台页面令牌 require
	 */
	public function vncPage()
	{
		$param = request()->param();

		$cache = Cache::get('remf_dcim_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('res_mf_dcim_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/mf_dcim/view/vnc_page.html');
	}

	/**
	 * 时间 2024-05-22
	 * @title 获取实例状态
	 * @desc  获取实例状态
	 * @url /admin/v1/remf_dcim/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/status', $RouteLogic->upstream_host_id), [], 'GET');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->status();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重置密码
	 * @desc  重置密码
	 * @url /admin/v1/remf_dcim/:id/reset_password
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reset_password', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->resetPassword();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 救援模式
	 * @desc  救援模式
	 * @url /admin/v1/remf_dcim/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function rescue()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/rescue', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->rescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2024-05-22
	 * @title 重装系统
 	 * @desc  重装系统
	 * @url /admin/v1/remf_dcim/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   string password - 密码 require
	 * @param   int port - 端口 require
	 * @param   int part_type - 分区类型0全盘格式化1第一分区格式化 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reinstall()
	{
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['is_delete']){
			return json(['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/reinstall', $RouteLogic->upstream_host_id), $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_mf_dcim_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_mf_dcim_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\mf_dcim\controller\home\CloudController')){
					return (new \server\mf_dcim\controller\home\CloudController())->reinstall();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_mf_dcim_act_exception')];
			}
		}
		return json($result);
	}

}
