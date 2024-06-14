<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\logic\CloudLogic;
use server\mf_cloud\validate\CloudValidate;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\HostLinkModel;
use think\facade\Cache;
use think\facade\View;

/**
 * @title 魔方云(自定义配置)-后台内页操作
 * @desc  魔方云(自定义配置)-后台内页操作
 * @use server\mf_cloud\controller\admin\CloudController
 */
class CloudController
{
	/**
     * 时间 2024-05-24
     * @title 后台详情
     * @desc  后台详情,用于提供后台实例操作获取配置
     * @url /admin/v1/mf_cloud/:id
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
	public function adminDetail()
	{
		$param = request()->param();
		
		$HostLinkModel = new HostLinkModel();

		$data = $HostLinkModel->adminDetail($param['id'] ?? 0);
		$data['config'] = (object)$data['config'];

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2024-05-16
	 * @title 开机
	 * @desc  开机
	 * @url /admin/v1/mf_cloud/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function on()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->on();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 关机
	 * @desc  关机
	 * @url /admin/v1/mf_cloud/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function off()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->off();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 重启
	 * @desc  重启
	 * @url /admin/v1/mf_cloud/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reboot()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->reboot();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 强制关机
	 * @desc  强制关机
	 * @url /admin/v1/mf_cloud/:id/hard_off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function hardOff()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->hardOff();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 强制重启
	 * @desc  强制重启
	 * @url /admin/v1/mf_cloud/:id/hard_reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function hardReboot()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->hardReboot();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 获取控制台地址
	 * @desc  获取控制台地址
	 * @url /admin/v1/mf_cloud/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
	 * @return  string url - 控制台地址
	 */
	public function vnc()
	{
		$param = request()->param();
        $param['more'] = 0;

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->vnc($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 控制台页面
	 * @desc  控制台页面
	 * @url /admin/v1/mf_cloud/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string temp_token - 临时令牌 require
	 */
	public function vncPage()
	{
		$param = request()->param();

		$cache = Cache::get('mf_cloud_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('mf_cloud_vnc_token_expired');
		}
		return View::fetch(WEB_ROOT . 'plugins/server/mf_cloud/view/vnc_page.html');
	}

	/**
	 * 时间 2024-05-16
	 * @title 获取实例状态
	 * @desc  获取实例状态
	 * @url /admin/v1/mf_cloud/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string status - 实例状态(on=开机,off=关机,suspend=暂停,operating=操作中,fault=故障)
	 * @return  string desc - 实例状态描述
	 */
	public function status()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->status();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 重置密码
	 * @desc  重置密码
	 * @url /admin/v1/mf_cloud/:id/reset_password
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

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('reset_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->resetPassword($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 救援模式
	 * @desc  救援模式
	 * @url /admin/v1/mf_cloud/:id/rescue
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

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('rescue')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->rescue($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 退出救援模式
	 * @desc  退出救援模式
	 * @url /admin/v1/mf_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function exitRescue()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->exitRescue();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 重装系统
 	 * @desc  重装系统
	 * @url /admin/v1/mf_cloud/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   int password - 密码 密码和ssh密钥ID,必须选择一种
	 * @param   int ssh_key_id - ssh密钥ID 密码和ssh密钥ID,必须选择一种
	 * @param   int port - 端口 require
	 * @param   string admin_operate_password - 操作密码,需要验证时传
	 */
	public function reinstall()
	{
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('reinstall')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->reinstall($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2024-05-16
	 * @title 获取魔方云远程信息
	 * @desc  获取魔方云远程信息
	 * @url /admin/v1/mf_cloud/:id/remote_info
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int rescue - 是否正在救援系统(0=不是,1=是)
	 * @return  string username - 远程用户名
	 * @return  string password - 远程密码
	 * @return  int port - 远程端口
	 * @return  int ip_num - IP数量
     * @return  int simulate_physical_machine - 模拟物理机运行(0=关闭,1=开启) 
	 */
	public function remoteInfo()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->detail();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}


}
