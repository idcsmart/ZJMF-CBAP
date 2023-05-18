<?php
namespace server\mf_cloud\controller\home;

use server\mf_cloud\logic\CloudLogic;
use server\mf_cloud\validate\CloudValidate;
use server\mf_cloud\validate\BackupConfigValidate;
use server\mf_cloud\validate\VpcNetworkValidate;
use server\mf_cloud\validate\CartValidate;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\SystemLogModel;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\ConfigLimitModel;
use server\mf_cloud\model\VpcNetworkModel;
use server\mf_cloud\model\LineModel;
use app\common\model\ProductModel;
use think\facade\Cache;
use think\facade\View;

/**
 * @title 魔方云(自定义配置)-前台
 * @desc 魔方云(自定义配置)-前台
 * @use server\mf_cloud\controller\home\CloudController
 */
class CloudController{

	/**
	 * 时间 2023-02-06
	 * @title 获取订购页面配置
	 * @desc 获取订购页面配置
	 * @url /console/v1/product/:id/mf_cloud/order_page
	 * @method  GET 
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  int data_center[].id - 国家ID
	 * @return  string data_center[].iso - 图标
	 * @return  string data_center[].name - 名称
	 * @return  string data_center[].city[].name - 城市
	 * @return  int data_center[].city[].area[].id - 数据中心ID
	 * @return  string data_center[].city[].area[].name - 区域
	 * @return  int data_center[].city[].area[].reommend_config[].id - 推荐配置ID
	 * @return  string data_center[].city[].area[].reommend_config[].name - 推荐配置名称
	 * @return  string data_center[].city[].area[].reommend_config[].description - 推荐配置描述
	 * @return  int data_center[].city[].area[].reommend_config[].line_id - 线路ID
	 * @return  int data_center[].city[].area[].reommend_config[].cpu - CPU
	 * @return  int data_center[].city[].area[].reommend_config[].memory - 内存
	 * @return  int data_center[].city[].area[].reommend_config[].system_disk_size - 系统盘
	 * @return  string data_center[].city[].area[].reommend_config[].system_disk_type - 系统盘类型
	 * @return  int data_center[].city[].area[].reommend_config[].data_disk_size - 数据盘
	 * @return  string data_center[].city[].area[].reommend_config[].data_disk_type - 数据盘类型
	 * @return  string data_center[].city[].area[].reommend_config[].network_type - 网络类型(normal=经典网络,vpc=vpc网络)
	 * @return  int data_center[].city[].area[].reommend_config[].bw - 带宽
	 * @return  int data_center[].city[].area[].reommend_config[].flow - 流量
	 * @return  int data_center[].city[].area[].reommend_config[].peak_defence - 防护峰值
	 * @return  int data_center[].city[].area[].line[].id - 线路ID
	 * @return  string data_center[].city[].area[].line[].name - 线路名称
	 * @return  array cpu - CPU配置
	 * @return  int cpu[].id - 配置ID
	 * @return  int cpu[].value - 核心数
	 * @return  int memory[].id - 配置ID
	 * @return  array memory- 内存配置
	 * @return  string memory[].type - 配置类型(radio=单选,step=阶梯,total=完整)
	 * @return  int memory[].value - 配置值
	 * @return  int memory[].min_value - 最小值
	 * @return  int memory[].max_value - 最大值
	 * @return  int memory[].step - 最小变化值
	 * @return  array system_disk - 系统盘配置
	 * @return  int system_disk[].id - 配置ID
	 * @return  string system_disk[].type - 配置类型(radio=单选,step=阶梯,total=完整)
	 * @return  int system_disk[].value - 配置值
	 * @return  int system_disk[].min_value - 最小值
	 * @return  int system_disk[].max_value - 最大值
	 * @return  int system_disk[].step - 最小变化值
	 * @return  string system_disk[].other_config.disk_type - 磁盘类型
	 * @return  array data_disk - 数据盘配置
	 * @return  int data_disk[].id - 配置ID
	 * @return  string data_disk[].type - 配置类型(radio=单选,step=阶梯,total=完整)
	 * @return  int data_disk[].value - 配置值
	 * @return  int data_disk[].min_value - 最小值
	 * @return  int data_disk[].max_value - 最大值
	 * @return  int data_disk[].step - 最小变化值
	 * @return  string data_disk[].other_config.disk_type - 磁盘类型
	 * @return  string data_disk[].other_config.disk_type - 磁盘类型
	 * @return  string config[].host_prefix - 主机名前缀
	 * @return  int config[].host_length - 主机名长度
	 * @return  int config[].support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
	 * @return  int config[].support_normal_network - 是否支持经典网络(0=不支持,1=支持)
	 * @return  int config[].support_vpc_network - 是否支持VPC网络(0=不支持,1=支持)
	 * @return  int config[].support_public_ip - 是否允许公网IP(0=不支持,1=支持)
	 * @return  int config[].backup_enable - 是否启用备份(0=不支持,1=支持)
	 * @return  int config[].snap_enable - 是否启用快照(0=不支持,1=支持)
	 * @return  int backup_config[].id - 备份配置ID
	 * @return  int backup_config[].num - 备份数量
	 * @return  string backup_config[].price - 备份价格
	 * @return  int snap_config[].id - 快照ID
	 * @return  int snap_config[].num - 快照数量
	 * @return  string snap_config[].price - 快照价格
	 */
	public function orderPage(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$DataCenterModel = new DataCenterModel();

		$data = $DataCenterModel->orderPage($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取操作系统列表
	 * @desc 获取操作系统列表
	 * @url /console/v1/product/:id/mf_cloud/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  int list[].id - 操作系统分类ID
	 * @return  string list[].name - 操作系统分类名称
	 * @return  string list[].icon - 操作系统分类图标
	 * @return  int list[].image[].id - 操作系统ID
	 * @return  int list[].image[].image_group_id - 操作系统分类ID
	 * @return  string list[].image[].name - 操作系统名称
	 * @return  int list[].image[].charge - 是否收费(0=否,1=是)
	 * @return  string list[].image[].price - 价格
	 */
	public function imageList(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$ImageModel = new ImageModel();

		$result = $ImageModel->homeImageList($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取商品配置所有周期价格
	 * @desc 获取商品配置所有周期价格
	 * @url /console/v1/product/:id/mf_cloud/duration
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int cpu - cpu核心数
	 * @param   int memory - 内存
     * @param   int image_id 0 镜像ID
     * @param   int system_disk.size - 系统盘大小
     * @param   string system_disk.disk_type - 系统盘类型
     * @param   int data_disk[].size - 数据盘大小
     * @param   string data_disk[].disk_type - 系统盘类型
     * @param   int backup_num 0 备份数量
     * @param   int snap_num 0 备份数量
     * @param   int line_id - 线路ID
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - 附加IP数量
     * @return  int [].id - 周期ID
     * @return  string [].name - 周期名称
     * @return  string [].price - 周期总价
     * @return  float  [].discount - 折扣(0=没有折扣)
	 */
	public function getAllDurationPrice(){
		$param = request()->param();

		$DurationModel = new DurationModel();
		$result = $DurationModel->getAllDurationPrice($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-09
	 * @title 获取配置限制规则
	 * @desc 获取配置限制规则
	 * @url /console/v1/product/:id/mf_cloud/config_limit
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
     * @return  string [].type - 类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,line=带宽与计算限制)
     * @return  int [].data_center_id - 数据中心ID
     * @return  int [].line_id - 线路ID
     * @return  int [].min_bw - 最小带宽
     * @return  int [].max_bw - 最大带宽
     * @return  array [].cpu - CPU
     * @return  array [].memory - 内存
     * @return  int [].min_memory - 最小内存
     * @return  int [].max_memory - 最大内存
	 */
	public function getAllConfigLimit(){
		$param = request()->param();

		$ConfigLimitModel = new ConfigLimitModel();
		$data = $ConfigLimitModel->getAllConfigLimit((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 获取可用VPC网络
	 * @desc 获取可用VPC网络
	 * @url /console/v1/product/:id/mf_cloud/vpc_network/search
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  int list[].id - VPC网络ID
	 * @return  string list[].name - VPC网络名称
	 */
	public function vpcNetworkSearch(){
		$param = request()->param();

		$VpcNetworkModel = new VpcNetworkModel();
		$data = $VpcNetworkModel->vpcNetworkSearch($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-22
	 * @title 数据中心选择
	 * @desc 数据中心选择
	 * @url /console/v1/product/:id/mf_cloud/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID
	 * @return  int list[].id - 数据中心ID
	 * @return  string list[].city - 城市
	 * @return  string list[].area - 区域
	 * @return  string list[].iso - 国家图标
	 * @return  string list[].country_name - 国家名称
	 */
	public function dataCenterSelect(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->formatDisplay($param);
		return json($result);
	}

	/**
	* 时间 2023-02-09
	* @title 产品列表
	* @desc 产品列表
	* @url /console/v1/mf_cloud
	* @method  GET
	* @author hh
	* @version v1
    * @param   int page 1 页数
    * @param   int limit - 每页条数
    * @param   string orderby - 排序(id,due_time,status)
    * @param   string sort - 升/降序
    * @param   string keywords - 关键字搜索,搜索套餐名称/主机名/IP
    * @param   int data_center_id - 数据中心搜索
    * @param   string param.status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
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
    * @return  string data.list[].icon - 镜像图标
	*/
	public function list(){
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();
		$result = $HostLinkModel->idcsmartCloudList($param);

		return json($result);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取实例详情
	 * @desc 获取实例详情
	 * @url /console/v1/mf_cloud/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int $id - 产品ID
     * @return  string ip - IP地址
     * @return  int backup_num - 允许备份数量
     * @return  int snap_num - 允许快照数量
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int cpu - cpu
     * @return  int memory - 内存
     * @return  int system_disk.size - 系统盘大小
     * @return  string system_disk.type - 系统盘类型
     * @return  int line.id - 线路
     * @return  string line.name - 线路名称
     * @return  string line.bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int bw - 带宽(0表示没有)
     * @return  int peak_defence - 防御峰值(0表示没有)
     * @return  string network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @return  string username - 用户名
     * @return  string password - 密码
     * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.area - 区域
     * @return  string data_center.country - 国家
     * @return  string data_center.iso - 图标
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分类
     * @return  string image.icon - 图标
     * @return  int security_group.id - 安全组ID
     * @return  string security_group.name - 安全组名称
     * @return  int vpc_network.id - VPC网络ID
     * @return  string vpc_network.name - VPC网络名称
     * @return  int ssh_key.id - SSH密钥ID(>0就是用了SSH密钥)
     * @return  string ssh_key.name - SSH密钥名称
	 */
	public function detail(){
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();
		$result = $HostLinkModel->detail((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-02-27
	 * @title 获取部分详情
	 * @desc 获取部分详情
	 * @url /console/v1/mf_cloud/:id/part
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.area - 区域
     * @return  string data_center.country - 国家
     * @return  string data_center.iso - 图标
     * @return  string ip - IP地址
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分类
     * @return  string image.icon - 图标
	 */
	public function detailPart(){
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();
		$result = $HostLinkModel->detailPart((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 开机
	 * @desc 开机
	 * @url /console/v1/mf_cloud/:id/on
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
	 * @url /console/v1/mf_cloud/:id/off
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
	 * @url /console/v1/mf_cloud/:id/reboot
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
	 * 时间 2022-06-22
	 * @title 强制关机
	 * @desc 强制关机
	 * @url /console/v1/mf_cloud/:id/hard_off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function hardOff(){
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
	 * 时间 2022-06-22
	 * @title 强制重启
	 * @desc 强制重启
	 * @url /console/v1/mf_cloud/:id/hard_reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function hardReboot(){
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
	 * 时间 2022-06-29
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /console/v1/mf_cloud/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->vnc($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-07-01
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /console/v1/mf_cloud/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function vncPage(){
		$param = request()->param();

		$cache = Cache::get('mf_cloud_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('控制台凭证已过期,请重新打开');
		}
		return View::fetch(WEB_ROOT . 'plugins/server/mf_cloud/view/vnc_page.html');
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取实例状态
	 * @desc 获取实例状态
	 * @url /console/v1/mf_cloud/:id/status
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
	 * @url /console/v1/mf_cloud/:id/reset_password
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
	 * @url /console/v1/mf_cloud/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
	 * @param   string password - 救援系统临时密码 require
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
	 * 时间 2022-06-24
	 * @title 退出救援模式
	 * @desc 退出救援模式
	 * @url /console/v1/mf_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function exitRescue(){
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
	 * 时间 2022-06-30
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /console/v1/mf_cloud/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   string password - 密码和ssh密钥ID,必须选择一种
	 * @param   int ssh_key_id - ssh密钥ID 密码和ssh密钥ID,必须选择一种
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
	 * @url /console/v1/mf_cloud/:id/chart
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int start_time - 开始秒级时间
	 * @param   string type - 图表类型(cpu=CPU,memory=内存,disk_io=硬盘IO,bw=带宽) require
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
	 * @url /console/v1/mf_cloud/:id/flow
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
	 * 时间 2022-07-11
	 * @title 获取实例磁盘
	 * @desc 获取实例磁盘
	 * @url /console/v1/mf_cloud/:id/disk
	 * @method  GET
	 * @author theworld
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  array list -  列表数据 
	 * @return  int list[].id - 磁盘ID
	 * @return  string list[].name - 磁盘名称
	 * @return  int list[].size - 磁盘大小,GB
	 * @return  int list[].create_time - 创建时间
	 * @return  string list[].type - 磁盘类型
	 * @return  string list[].type2 - 磁盘类型(data=数据盘,system=系统盘)
	 * @return  int list[].status - 磁盘状态(0=未挂载,1=挂载中)
	 */
	public function disk(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$data = $CloudLogic->diskList($param);

			$result = [
				'status' => 200,
				'msg'    => lang_plugins('success_message'),
				'data'	 => $data,
			];
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2023-02-10
	 * @title 卸载磁盘
	 * @desc 卸载磁盘
	 * @url /console/v1/mf_cloud/:id/disk/:disk_id/unmount
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int disk_id - 磁盘ID require
	 */
	public function diskUnmount(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->diskUnmount((int)$param['disk_id']);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2023-02-10
	 * @title 挂载磁盘
	 * @desc 挂载磁盘
	 * @url /console/v1/mf_cloud/:id/disk/:disk_id/mount
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int disk_id - 磁盘ID require
	 */
	public function diskMount(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->diskMount((int)$param['disk_id']);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-27
	 * @title 快照列表
	 * @desc 快照列表
	 * @url /console/v1/mf_cloud/:id/snapshot
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int page - 页数
	 * @param   int limit - 每页条数
	 * @return  int list[].id - 快照ID
	 * @return  string list[].name - 快照名称
	 * @return  int list[].create_time - 创建时间
	 * @return  string list[].notes - 备注
	 * @return  int list[].status - 状态(0=创建中,1=创建完成)
	 * @return  int data.count - 总条数
	 */
	public function snapshot(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->snapshotList($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-07-11
	 * @title 创建快照
	 * @desc 创建快照
	 * @url /console/v1/mf_cloud/:id/snapshot
	 * @method  POST
	 * @author theworld
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int name - 快照名称 require
	 * @param   int disk_id - 磁盘ID require
	 */
	public function snapshotCreate(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->snapshotCreate($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-27
	 * @title 快照还原
	 * @desc 快照还原
	 * @url /console/v1/mf_cloud/:id/snapshot/restore
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int snapshot_id - 快照ID require
	 */
	public function snapshotRestore(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->snapshotRestore($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除快照
	 * @desc 删除快照
	 * @url /console/v1/mf_cloud/:id/snapshot/:snapshot_id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int snapshot_id - 快照ID require
	 */
	public function snapshotDelete(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->snapshotDelete((int)$param['snapshot_id']);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-27
	 * @title 备份列表
	 * @desc 备份列表
	 * @url /console/v1/mf_cloud/:id/backup
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int page - 页数
	 * @param   int limit - 每页条数
	 * @return  int list[].id - 备份ID
	 * @return  string list[].name - 备份名称
	 * @return  int list[].create_time - 创建时间
	 * @return  string list[].notes - 备注
	 * @return  int list[].status - 状态(0=创建中,1=创建完成)
	 * @return  int data.count - 总条数
	 */
	public function backup(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->backupList($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-07-11
	 * @title 创建备份
	 * @desc 创建备份
	 * @url /console/v1/mf_cloud/:id/backup
	 * @method  POST
	 * @author theworld
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int name - 备份名称 require
	 * @param   int disk_id - 磁盘ID require
	 */
	public function backupCreate(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->backupCreate($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-27
	 * @title 备份还原
	 * @desc 备份还原
	 * @url /console/v1/mf_cloud/:id/backup/restore
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int backup_id - 备份ID require
	 */
	public function backupRestore(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->backupRestore($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除备份
	 * @desc 删除备份
	 * @url /console/v1/mf_cloud/:id/backup/:backup_id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int backup_id - 备份ID require
	 */
	public function backupDelete(){
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->backupDelete((int)$param['backup_id']);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	
	/**
	 * 时间 2022-07-01
	 * @title 日志
	 * @desc 日志
	 * @url /console/v1/mf_cloud/:id/log
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
	 * 时间 2022-09-14
	 * @title 获取魔方云远程信息
	 * @desc 获取魔方云远程信息
	 * @url console/v1/mf_cloud/:id/remote_info
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

	/**
	 * 时间 2022-07-01
	 * @title 获取IP列表
	 * @desc 获取IP列表
	 * @url /console/v1/mf_cloud/:id/ip
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
	 * 时间 2022-07-29
	 * @title 获取订购磁盘价格
	 * @desc 获取订购磁盘价格
	 * @url /console/v1/mf_cloud/:id/disk/price
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   array remove_disk_id - 要取消订购的磁盘ID
	 * @param   array add_disk - 新增磁盘大小,如:[{"size":1,"type":"SSH"}]
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calBuyDiskPrice(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('buy_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

 		try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->calDiskPrice($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}


	/**
	 * 时间 2022-07-29
	 * @title 生成购买磁盘订单
	 * @desc 生成购买磁盘订单
	 * @url /console/v1/mf_cloud/:id/disk/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   array remove_disk_id - 要取消订购的磁盘ID
	 * @param   array add_disk - 新增磁盘大小,如:[{"size":1,"type":"SSH"}]
	 * @return  string data.id - 订单ID
	 */
	public function createBuyDiskOrder(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('buy_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

        try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->createBuyDiskOrder($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 获取磁盘扩容价格
	 * @desc 获取磁盘扩容价格
	 * @url /console/v1/mf_cloud/:id/disk/resize
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   array resize_data_disk - 要扩容的磁盘数据,如[{"id":1,"size":50}],id=磁盘ID,size=扩容后的容量
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calResizeDiskPrice(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('resize_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

 		try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->calResizeDiskPrice($param);
			if(isset($result['data']['resize_disk'])) unset($result['data']['resize_disk']);

        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}


	/**
	 * 时间 2022-07-29
	 * @title 生成磁盘扩容订单
	 * @desc 生成磁盘扩容订单
	 * @url /console/v1/mf_cloud/:id/disk/resize/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   array resize_data_disk - 要扩容的磁盘数据,如[{"id":1,"size":50}],id=磁盘ID,size=扩容后的容量 require
	 * @return  string data.id - 订单ID
	 */
	public function createResizeDiskOrder(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('resize_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

        try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->createResizeDiskOrder($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 检查产品是够购买过镜像
	 * @desc 检查产品是够购买过镜像
	 * @url /console/v1/mf_cloud/:id/image/check
	 * @method GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @return  string data.price - 需要支付的金额(0.00表示镜像免费或已购买)
	 */
	public function checkHostImage(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->checkHostImage($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 生成购买镜像订单
	 * @desc 生成购买镜像订单
	 * @url /console/v1/mf_cloud/:id/image/order
	 * @method POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @return  string data.id - 订单ID
	 */
	public function createImageOrder(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->createImageOrder($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 获取快照/备份数量升降级价格
	 * @desc 获取快照/备份数量升降级价格
	 * @url /console/v1/mf_cloud/:id/backup_config
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string type - 类型(snap=快照,backup=备份)
	 * @param   string num - 备份/快照数量
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calBackupConfigPrice(){
		$param = request()->param();

		$BackupConfigValidate = new BackupConfigValidate();
		if (!$BackupConfigValidate->scene('buy')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
        }
    	$ConfigModel = new ConfigModel();

		$result = $ConfigModel->calConfigPrice($param);
		if(isset($result['data']['backup_config'])) unset($result['data']['backup_config']);
        return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 生成快照/备份数量升降级订单
	 * @desc 生成快照/备份数量升降级订单
	 * @url /console/v1/mf_cloud/:id/backup_config/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string type - 类型(snap=快照,backup=备份)
	 * @param   string num - 备份/快照数量
	 * @return  string data.id - 订单ID
	 */
	public function createBackupConfigOrder(){
		$param = request()->param();

		$BackupConfigValidate = new BackupConfigValidate();
		if (!$BackupConfigValidate->scene('buy')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
        }

        $ConfigModel = new ConfigModel();

		$result = $ConfigModel->createBackupConfigOrder($param);
        return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 获取附加IP价格
	 * @desc 获取附加IP价格
	 * @url /console/v1/mf_cloud/:id/ip_num
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int ip_num - 附加IP数量 require
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calIpNumPrice(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('upgrade_ip_num')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

 		try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->calIpNumPrice($param);
			if(isset($result['data']['ip_data'])) unset($result['data']['ip_data']);

        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}


	/**
	 * 时间 2023-02-13
	 * @title 生成附加IP订单
	 * @desc 生成附加IP订单
	 * @url /console/v1/mf_cloud/:id/ip_num/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int ip_num - 附加IP数量 require
	 * @return  string data.id - 订单ID
	 */
	public function createIpNumOrder(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('upgrade_ip_num')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

        try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->createIpNumOrder($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 创建VPC网络
	 * @desc 创建VPC网络
	 * @url /console/v1/mf_cloud/:id/vpc_network
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string name - 网络名称 require
	 * @param   string ips - IP网段(系统分配时不传)
	 * @return  int id - VPC网络ID
	 */
	public function createVpcNetwork(){
		$param = request()->param();

		$VpcNetworkValidate = new VpcNetworkValidate();
		if (!$VpcNetworkValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($VpcNetworkValidate->getError())]);
        }

         try{
        	$CloudLogic = new CloudLogic($param['id']);

        	unset($param['id']);
			$result = $CloudLogic->vpcNetworkCreate($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title VPC网络列表
	 * @desc VPC网络列表
	 * @url /console/v1/mf_cloud/:id/vpc_network
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int page - 页数
	 * @param   int limit - 每页条数
	 * @return  int list[].id - VPC网络ID
	 * @return  string list[].name - VPC网络名称
	 * @return  string list[].ips - VPC网络网段
	 * @return  int list[].host[].id - 主机产品ID
	 * @return  string list[].host[].name - 主机标识
	 */
	public function vpcNetworkList(){
		$param = request()->param();

		$VpcNetworkModel = new VpcNetworkModel();

		$data = $VpcNetworkModel->vpcNetworkList($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 修改VPC网络
	 * @desc 修改VPC网络
	 * @url /console/v1/mf_cloud/:id/vpc_network/:vpc_network_id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int vpc_network_id - VPC网络ID require
	 * @param   string name - 网络名称 require
	 */
	public function vpcNetworkUpdate(){
		$param = request()->param();

		$VpcNetworkValidate = new VpcNetworkValidate();
		if (!$VpcNetworkValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($VpcNetworkValidate->getError())]);
        }

        $VpcNetworkModel = new VpcNetworkModel();

		$result = $VpcNetworkModel->vpcNetworkUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-14
	 * @title 删除VPC网络
	 * @desc 删除VPC网络
	 * @url /console/v1/mf_cloud/:id/vpc_network/:vpc_network_id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int vpc_network_id - VPC网络ID require
	 */
	public function vpcNetworkDelete(){
		$param = request()->param();

        $VpcNetworkModel = new VpcNetworkModel();

		$result = $VpcNetworkModel->vpcNetworkDelete($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-14
	 * @title 切换实例VPC网络
	 * @desc 切换实例VPC网络
	 * @url /console/v1/mf_cloud/:id/vpc_network
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int vpc_network_id - 新VPCID require
	 */
	public function changeVpcNetwork(){
		$param = request()->param();

        try{
        	$CloudLogic = new CloudLogic($param['id']);

        	unset($param['id']);
			$result = $CloudLogic->changeVpcNetwork($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2023-02-14
	 * @title 获取cpu/内存使用信息
	 * @desc 获取cpu/内存使用信息
	 * @url /console/v1/mf_cloud/:id/real_data
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string cpu_usage - CPU使用率
	 * @return  string memory_total - 内存总量(‘-’代表获取不到)
	 * @return  string memory_usable - 已用内存(‘-’代表获取不到)
	 * @return  string memory_usage - 内存使用百分比(‘-1’代表获取不到)
	 */
	public function cloudRealData(){
		$param = request()->param();

        try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->cloudRealData();

			return json($result);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
	}

	/**
	 * 时间 2023-02-13
	 * @title 计算产品配置升级价格
	 * @desc 计算产品配置升级价格
	 * @url /console/v1/mf_cloud/:id/common_config
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存 require
     * @param   int bw - 带宽
     * @param   int flow - 流量包
     * @param   int peak_defence - 防御峰值
     * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calCommonConfigPrice(){
		$param = request()->param();

		$CartValidate = new CartValidate();
		if (!$CartValidate->scene('upgrade_config')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CartValidate->getError())]);
        }

 		try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->calCommonConfigPrice($param);
			if(isset($result['data']['new_config_data'])) unset($result['data']['new_config_data']);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}


	/**
	 * 时间 2023-02-13
	 * @title 生成产品配置升级订单
	 * @desc 生成产品配置升级订单
	 * @url /console/v1/mf_cloud/:id/common_config/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存 require
     * @param   int bw - 带宽
     * @param   int flow - 流量包
     * @param   int peak_defence - 防御峰值
	 * @return  string data.id - 订单ID
	 */
	public function createCommonConfigOrder(){
		$param = request()->param();

		$CartValidate = new CartValidate();
		if (!$CartValidate->scene('upgrade_config')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CartValidate->getError())]);
        }

        try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->createCommonConfigOrder($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2023-02-14
	 * @title 获取线路配置
	 * @desc 获取线路配置
	 * @url /console/v1/product/:id/mf_cloud/line/:line_id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int line_id - 线路ID require
	 * @return  string bill_type - 线路类型(bw=带宽,flow=流量)
	 * @return  string bw[].type - 计费类型
	 * @return  int bw[].value - 带宽
	 * @return  int bw[].min_value - 最小值
	 * @return  int bw[].max_value - 最大值
	 * @return  int bw[].step - 最小变化值
	 * @return  int flow[].value - 流量
	 * @return  int defence[].value - 防御
	 * @return  int ip[].value - 附加IP
	 */
	public function lineConfig(){
		$param = request()->param();

		$LineModel = new LineModel();

		$data = $LineModel->homeLineConfig((int)$param['line_id']);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-03-01
	 * @title 验证下单
	 * @desc 验证下单
	 * @url /console/v1/product/:id/mf_cloud/validate_settle
	 * @method  POST
	 * @author hh
	 * @version v1
	 */
	public function validateSettle(){
		$param = request()->param();

		$CartValidate = new CartValidate();
		if(!$CartValidate->scene('cal')->check($param['custom'] ?? [])){
            return json(['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())]);
        }
        // 当是VPC时,验证VPC
        if($param['custom']['network_type'] == 'vpc'){
        	if(isset($param['custom']['vpc'])){
        		if(isset($param['custom']['vpc']['id']) && $param['custom']['vpc']['id']>0){
        			$vpcNetwork = VpcNetworkModel::find($param['custom']['vpc']['id']);
        			if(empty($vpcNetwork) || $vpcNetwork['client_id'] != get_client_id() || $vpcNetwork['data_center_id'] != $param['custom']['data_center_id']){
        				return json(['status'=>400, 'msg'=>lang_plugins('VPC网络不存在')]);
        			}
        			if(request()->is_api && isset($param['downstream_client_id']) && $vpcNetwork['downstream_client_id'] != $param['downstream_client_id']){
			            return json(['status'=>400, 'msg'=>lang_plugins('VPC网络不存在')]);
			        }
        		}else{
        			$VpcNetworkValidate = new VpcNetworkValidate();
        			if(!$VpcNetworkValidate->scene('ips')->check($param['custom']['vpc'])){
			            return json(['status'=>400 , 'msg'=>lang_plugins($VpcNetworkValidate->getError())]);
			        }
        		}
        	}else{
        		return json(['status'=>400, 'msg'=>lang_plugins('VPC网络参数错误')]);
        	}
        }
        $param['product'] = ProductModel::find($param['id']);

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($param, false);
		return json($res);
	}


}
