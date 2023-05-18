<?php
namespace server\idcsmart_dcim\controller\home;

use server\idcsmart_dcim\logic\CloudLogic;
use server\idcsmart_dcim\validate\CloudValidate;
use server\idcsmart_dcim\model\HostLinkModel;
use server\idcsmart_dcim\model\SystemLogModel;
use app\common\model\HostModel;
use think\facade\Cache;
use think\facade\View;

/**
 * @title DCIM管理
 * @desc DCIM管理
 * @use server\idcsmart_dcim\controller\home\CloudController
 */
class CloudController{

	/**
	* 时间 2022-06-24
	* @title 产品列表
	* @desc 产品列表
	* @url /console/v1/idcsmart_dcim
	* @method  GET
	* @author hh
	* @version v1
     * @param   int page 1 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,due_time,status)
     * @param   string sort - 升/降序
     * @param   string keywords - 关键字搜索,搜索套餐名称/主机名/IP
     * @param   int data_center_id - 数据中心搜索
     * @param   string status - 产品状态搜索(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @param   int param.m - 菜单ID
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 列表数据
     * @return  string data.list[].name - 产品标识
     * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @return  int data.list[].due_time - 到期时间
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].package_name - 套餐名称
     * @return  string data.list[].ip - IP
     * @return  string data.list[].image_name - 镜像名称
     * @return  string data.list[].image_group_name - 镜像分组名称
     * @return  string data.list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.list[].active_time - 开通时间
     * @return  string data.list[].product_name - 商品名称
	 */
	public function list(){
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();
		$result = $HostLinkModel->dcimList($param);

		return json($result);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取详情
	 * @desc 获取详情
	 * @url /console/v1/idcsmart_dcim/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int $id - 产品ID
     * @return  int rel_id - 魔方云ID
     * @return  string ip - IP地址
     * @return  int port - 端口
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.country_name - 国家
     * @return  string data_center.iso - 图标
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分组
     * @return  int package.id - 套餐ID
     * @return  string package.name - 套餐名称
     * @return  string package.description - 套餐描述
     * @return  string package.ip_num - IP数量
     * @return  string package.in_bw - 进带宽
     * @return  string package.out_bw - 出带宽
	 */
	public function detail(){
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();
		$result = $HostLinkModel->detail((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 开机
	 * @desc 开机
	 * @url /console/v1/idcsmart_dcim/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function on(){
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
	 * 时间 2022-06-22
	 * @title 关机
	 * @desc 关机
	 * @url /console/v1/idcsmart_dcim/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function off(){
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
	 * 时间 2022-06-22
	 * @title 重启
	 * @desc 重启
	 * @url /console/v1/idcsmart_dcim/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function reboot(){
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
	 * 时间 2022-06-29
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /console/v1/idcsmart_dcim/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->vnc();
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-07-01
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /console/v1/idcsmart_dcim/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function vncPage(){
		$param = request()->param();

		$cache = Cache::get('idcsmart_dcim_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('控制台凭证已过期,请重新打开');
		}
		return View::fetch(WEB_ROOT . 'plugins/server/idcsmart_dcim/view/vnc_page.html');
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取电源状态
	 * @desc 获取电源状态
	 * @url /console/v1/idcsmart_dcim/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status(){
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
	 * 时间 2022-06-24
	 * @title 重置密码
	 * @desc 重置密码
	 * @url /console/v1/idcsmart_dcim/:id/reset_password
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string password - 新密码 require
	 */
	public function resetPassword(){
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
	 * 时间 2022-06-24
	 * @title 救援模式
	 * @desc 救援模式
	 * @url /console/v1/idcsmart_dcim/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
	 */
	public function rescue(){
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
	 * 时间 2022-06-30
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /console/v1/idcsmart_dcim/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   int password - 密码 require
	 * @param   int port - 端口 require
	 */
	public function reinstall(){
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
	 * 时间 2022-06-27
	 * @title 获取图表数据
	 * @desc 获取图表数据
	 * @url /console/v1/idcsmart_dcim/:id/chart
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int param.start_time - 开始秒级时间
	 * @return  array list - 图表数据
	 * @return  int list[].time - 时间(秒级时间戳)
	 * @return  float list[].in_bw - 进带宽
	 * @return  float list[].out_bw - 出带宽
	 * @return  string unit - 当前单位
	 */
	public function chart(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->chart($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-30
	 * @title 获取网络流量
	 * @desc 获取网络流量
	 * @url /console/v1/idcsmart_dcim/:id/flow
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string total -总流量
	 * @return  string used -已用流量
	 * @return  string leave - 剩余流量
	 * @return  string reset_flow_date - 流量归零时间
	 */
	public function flowDetail(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->flowDetail($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-07-01
	 * @title 日志
	 * @desc 日志
	 * @url /console/v1/idcsmart_dcim/:id/log
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param int id - 产品ID
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,description,create_time,ip
     * @param string sort - 升/降序 asc,desc
     * @return array list - 系统日志
     * @return int list[].id - 系统日志ID 
     * @return string list[].description - 描述 
     * @return string list[].create_time - 时间 
     * @return int list[].ip - IP 
     * @return int count - 系统日志总数
	 */
	public function log(){
		$param = request()->param();

		$SystemLogModel = new SystemLogModel();
	 	$result = $SystemLogModel->systemLogList($param);
	 	return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 获取IP列表
	 * @desc 获取IP列表
	 * @url /console/v1/idcsmart_dcim/:id/ip
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param int id - 产品ID
     * @param int page 1 页数
     * @param int limit - 每页条数
     * @return array list - 列表数据
     * @return int list[].ip - IP
     * @return string list[].subnet_mask - 掩码
     * @return string list[].gateway - 网关
     * @return int count - 总数
	 */
	public function ipList(){
		$param = array_merge(request()->param(), ['page' => request()->page, 'limit' => request()->limit, 'sort' => request()->sort]);

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$param['host_id'] = $param['id'];
			$data = $CloudLogic->ipList($param);

			$result = [
	            'status' => 200,
	            'msg'    => lang_plugins('success_message'),
	            'data'   => $data
	        ];

			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-10-11
	 * @title 获取DCIM远程信息
	 * @desc 获取DCIM远程信息
	 * @url console/v1/idcsmart_dcim/:id/remote_info
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string username - 远程用户名
	 * @return  string password - 远程密码
	 * @return  int port - 远程端口
	 * @return  int ip_num - IP数量
	 */
	public function remoteInfo(){
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
