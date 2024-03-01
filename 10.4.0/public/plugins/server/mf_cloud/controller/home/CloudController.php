<?php
namespace server\mf_cloud\controller\home;

use server\mf_cloud\logic\CloudLogic;
use server\mf_cloud\validate\CloudValidate;
use server\mf_cloud\validate\BackupConfigValidate;
use server\mf_cloud\validate\VpcNetworkValidate;
use server\mf_cloud\validate\CartValidate;
use server\mf_cloud\validate\NatAclValidate;
use server\mf_cloud\validate\NatWebValidate;
use server\mf_cloud\model\HostLinkModel;
use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\DurationModel;
use server\mf_cloud\model\ConfigLimitModel;
use server\mf_cloud\model\VpcNetworkModel;
use server\mf_cloud\model\LineModel;
use app\common\model\ProductModel;
use app\common\model\SystemLogModel;
use think\facade\Cache;
use think\facade\View;

/**
 * @title 魔方云(自定义配置)-前台
 * @desc 魔方云(自定义配置)-前台
 * @use server\mf_cloud\controller\home\CloudController
 */
class CloudController
{
	/**
	 * 时间 2023-02-06
	 * @title 获取订购页面配置
	 * @desc 获取订购页面配置
	 * @url /console/v1/product/:id/mf_cloud/order_page
	 * @method  GET 
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   string scene custom 场景(recommend=套餐,custom=自定义)
      * @param   int is_downstream 0 是否是下游(0=否,1=是)
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
      * @return  int data_center[].city[].area[].reommend_config[].ip_num - IP数量
      * @return  int data_center[].city[].area[].reommend_config[].upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
      * @return  int data_center[].city[].area[].reommend_config[].gpu_num - GPU数量
      * @return  int data_center[].city[].area[].reommend_config[].gpu_name - GPU型号
      * @return  int data_center[].city[].area[].line[].id - 线路ID
      * @return  string data_center[].city[].area[].line[].name - 线路名称
      * @return  int data_center[].city[].area[].line[].data_center_id - 数据中心ID
      * @return  string data_center[].city[].area[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
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
      * @return  string system_disk[].other_config.store_id - 储存ID
      * @return  string system_disk[].customfield.multi_language.other_config.disk_type - 多语言磁盘类型(有就替换)
      * @return  array data_disk - 数据盘配置
      * @return  int data_disk[].id - 配置ID
      * @return  string data_disk[].type - 配置类型(radio=单选,step=阶梯,total=完整)
      * @return  int data_disk[].value - 配置值
      * @return  int data_disk[].min_value - 最小值
      * @return  int data_disk[].max_value - 最大值
      * @return  int data_disk[].step - 最小变化值
      * @return  string data_disk[].other_config.disk_type - 磁盘类型
      * @return  string data_disk[].other_config.store_id - 储存ID
      * @return  string data_disk[].customfield.multi_language.other_config.disk_type - 多语言磁盘类型(有就替换)
      * @return  string config.type - 实例类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
      * @return  int config.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
      * @return  int config.support_normal_network - 是否支持经典网络(0=不支持,1=支持)
      * @return  int config.support_vpc_network - 是否支持VPC网络(0=不支持,1=支持)
      * @return  int config.support_public_ip - 是否允许公网IP(0=不支持,1=支持)
      * @return  int config.backup_enable - 是否启用备份(0=不支持,1=支持)
      * @return  int config.snap_enable - 是否启用快照(0=不支持,1=支持)
      * @return  string config.memory_unit - 内存单位(GB,MB)
      * @return  int config.disk_limit_num - 数据盘数量限制
      * @return  int config.free_disk_switch - 免费数据盘开关(0=关闭,1=开启)
      * @return  int config.free_disk_size - 免费数据盘大小(GB)
      * @return  int config.only_sale_recommend_config - 仅售卖套餐(0=关闭,1=开启)
      * @return  int config.no_upgrade_tip_show - 不可升降级时订购页提示(0=关闭,1=开启)
      * @return  int config.default_nat_acl - 默认NAT转发(0=关闭,1=开启)
      * @return  int config.default_nat_web - 默认NAT建站(0=关闭,1=开启)
      * @return  int config.ip_mac_bind_enable - 是否启用嵌套虚拟化(0=关闭,1=开启)
      * @return  int config.nat_acl_limit_enable - 是否启用NAT转发(0=关闭,1=开启)
      * @return  int config.nat_web_limit_enable - 是否启用NAT建站(0=关闭,1=开启)
      * @return  int config.ipv6_num_enable - 是否启用IPv6(0=关闭,1=开启)
      * @return  int backup_config[].id - 备份配置ID
      * @return  int backup_config[].num - 备份数量
      * @return  string backup_config[].price - 备份价格
      * @return  int snap_config[].id - 快照ID
      * @return  int snap_config[].num - 快照数量
      * @return  string snap_config[].price - 快照价格
      * @return  string config_limit[].type - 配置限制类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,line=带宽与计算限制)
      * @return  int config_limit[].data_center_id - 数据中心ID
      * @return  int config_limit[].line_id - 线路ID
      * @return  int config_limit[].min_bw - 最小带宽
      * @return  int config_limit[].max_bw - 最大带宽
      * @return  string config_limit[].cpu - cpu(英文逗号分隔)
      * @return  string config_limit[].memory - 内存(英文逗号分隔)
      * @return  int config_limit[].min_memory - 最小内存
      * @return  int config_limit[].max_memory - 最大内存
      * @return  int resource_package[].id - 资源包ID
      * @return  string resource_package[].name - 资源包名称
	 */
	public function orderPage()
	{
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
     * @param   int is_downstream 0 是否下游发起(0=否,1=是)
     * @return  int list[].id - 操作系统分类ID
     * @return  string list[].name - 操作系统分类名称
     * @return  string list[].icon - 操作系统分类图标
     * @return  int list[].image[].id - 操作系统ID
     * @return  int list[].image[].image_group_id - 操作系统分类ID
     * @return  string list[].image[].name - 操作系统名称
     * @return  int list[].image[].charge - 是否收费(0=否,1=是)
     * @return  string list[].image[].price - 价格
	 */
	public function imageList()
	{
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
     * @param   int recommend_config_id - 套餐ID
     * @param   int cpu - CPU
     * @param   int memory - 内存
     * @param   int system_disk.size - 系统盘大小
     * @param   string system_disk.disk_type - 系统盘类型
     * @param   int data_disk[].size - 数据盘大小
     * @param   string data_disk[].disk_type - 系统盘类型
     * @param   int line_id - 线路ID
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值(G)
     * @param   int ip_num - 附加IP数量
     * @param   int gpu_num - 显卡数量
     * @param   int image_id 0 镜像ID
     * @param   int backup_num 0 备份数量
     * @param   int snap_num 0 快照数量
     * @param   int is_downstream - 是否下游发起(0=否,1=是)
     * @return  int [].id - 周期ID
     * @return  string [].name - 周期名称
     * @return  string [].name_show - 周期名称多语言替换
     * @return  string [].price - 周期总价
     * @return  float [].discount - 折扣(0=没有折扣)
     * @return  int [].num - 周期时长
     * @return  string [].unit - 单位(hour=小时,day=天,month=月)
	 */
	public function getAllDurationPrice()
	{
		$param = request()->param();

        $CartValidate = new CartValidate();
        if (!$CartValidate->scene('all_duration_price')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CartValidate->getError())]);
        }

		$DurationModel = new DurationModel();
		$result = $DurationModel->getAllDurationPrice($param);
		if(isset($result['data']) && !empty($result['data'])){
			foreach($result['data'] as $k=>$v){
				$result['data'][$k]['name'] = $v['name_show'] ?? $v['name'];
			}
		}
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
     * @return  string [].type - 类型(cpu=CPU与内存限制,data_center=数据中心与计算限制)
     * @return  int [].data_center_id - 数据中心ID
     * @return  array [].cpu - CPU
     * @return  array [].memory - 内存
     * @return  int [].min_memory - 最小内存
     * @return  int [].max_memory - 最大内存
	 */
	public function getAllConfigLimit()
	{
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
     * @param  int data_center_id - 数据中心ID
     * @param  int downstream_client_id - 下游用户ID(api对接可用)
     * @return int list[].id - VPC网络ID
     * @return string list[].name - VPC网络名称
	 */
	public function vpcNetworkSearch()
	{
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
     * @return  string list[].iso - 图标
     * @return  string list[].country_name - 国家名称
	 */
	public function dataCenterSelect()
	{
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
	* @param   string keywords - 关键字搜索:商品名称/产品名称/IP
	* @param   int data_center_id - 数据中心搜索
	* @param   string status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
	* @return  int list[].id - 产品ID
	* @return  int list[].product_id - 商品ID
	* @return  string list[].name - 产品标识
	* @return  string list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
	* @return  int list[].active_time - 开通时间
	* @return  int list[].due_time - 到期时间
	* @return  string list[].client_notes - 用户备注
	* @return  string list[].product_name - 商品名称
	* @return  string list[].country - 国家
	* @return  string list[].country_code - 国家代码
	* @return  string list[].city - 城市
	* @return  string list[].ip - IP
	* @return  string list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
	* @return  string list[].image_name - 镜像名称
	* @return  string list[].image_group_name - 镜像分组名称
	* @return  array list[].self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容)
	* @return  int count - 总条数
	* @return  int data_center[].id - 数据中心ID
	* @return  string data_center[].area - 区域
	* @return  string data_center[].city - 城市
	* @return  string data_center[].country_name - 国家
	* @return  string data_center[].iso - 图标
	* @return  int self_defined_field[].id - 自定义字段ID
	* @return  string self_defined_field[].field_name - 自定义字段名称
	* @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
	*/
	public function list()
	{
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
	 * @param   int id - 产品ID
     * @return  string type - 类型(host=KVM加强版,lightHost=KVM轻量版,hyperv=Hyper-V)
     * @return  int order_id - 订单ID
     * @return  string ip - IP地址
     * @return  int ip_num - 附加IP数量
     * @return  int backup_num - 允许备份数量
     * @return  int snap_num - 允许快照数量
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  string cpu - CPU
     * @return  int memory - 内存
     * @return  int system_disk.size - 系统盘大小(G)
     * @return  string system_disk.type - 系统盘类型
     * @return  int line.id - 线路ID
     * @return  string line.name - 线路名称
     * @return  string line.bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int bw - 带宽
     * @return  int peak_defence - 防御峰值(G)
     * @return  string network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @return  string gpu - 显卡
     * @return  string username - 用户名
     * @return  string password - 密码
     * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.area - 区域
     * @return  string data_center.country - 国家
     * @return  string data_center.iso - 图标
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分组
     * @return  string image.icon - 图标
     * @return  int ssh_key.id - SSH密钥ID
     * @return  string ssh_key.name - SSH密钥名称
     * @return  int nat_acl_limit - NAT转发数量
     * @return  int nat_web_limit - NAT建站数量
     * @return  int config.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int config.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int security_group.id - 关联的安全组ID(0=没关联)
     * @return  string security_group.name - 关联的安全组名称
     * @return  int recommend_config.id - 套餐ID(有表示是套餐)
     * @return  int recommend_config.product_id - 商品ID
     * @return  string recommend_config.name - 套餐名称
     * @return  string recommend_config.description - 套餐描述
     * @return  int recommend_config.order - 排序
     * @return  int recommend_config.data_center_id - 数据中心ID
     * @return  int recommend_config.cpu - CPU
     * @return  int recommend_config.memory - 内存(GB)
     * @return  int recommend_config.system_disk_size - 系统盘大小(G)
     * @return  int recommend_config.data_disk_size - 数据盘大小(G)
     * @return  int recommend_config.bw - 带宽
     * @return  int recommend_config.peak_defence - 防御峰值(G)
     * @return  string recommend_config.system_disk_type - 系统盘类型
     * @return  string recommend_config.data_disk_type - 数据盘类型
     * @return  int recommend_config.flow - 流量
     * @return  int recommend_config.line_id - 线路ID
     * @return  int recommend_config.create_time - 创建时间
     * @return  int recommend_config.ip_num - IP数量
     * @return  int recommend_config.upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  int recommend_config.hidden - 是否隐藏(0=否,1=是)
     * @return  int recommend_config.gpu_num - 显卡数量
	 */
	public function detail()
	{
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
	public function detailPart()
	{
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
	 * 时间 2022-06-22
	 * @title 关机
	 * @desc 关机
	 * @url /console/v1/mf_cloud/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
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
	 * 时间 2022-06-22
	 * @title 重启
	 * @desc 重启
	 * @url /console/v1/mf_cloud/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
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
	 * 时间 2022-06-22
	 * @title 强制关机
	 * @desc 强制关机
	 * @url /console/v1/mf_cloud/:id/hard_off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
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
	 * 时间 2022-06-22
	 * @title 强制重启
	 * @desc 强制重启
	 * @url /console/v1/mf_cloud/:id/hard_reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
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
	 * 时间 2022-06-29
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /console/v1/mf_cloud/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int id - 产品ID require
	 * @param   int more 0 是否获取更多返回(0=否,1=是)
	 * @return  string url - 控制台地址
	 * @return  string vnc_url - vncwebsocket地址
	 * @return  string vnc_pass - VNC密码
	 * @return  string password - 实例密码
	 * @return  string token - 临时令牌
	 */
	public function vnc()
	{
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
	 * 时间 2022-06-24
	 * @title 获取实例状态
	 * @desc 获取实例状态
	 * @url /console/v1/mf_cloud/:id/status
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
	 * 时间 2022-06-24
	 * @title 重置密码
	 * @desc 重置密码
	 * @url /console/v1/mf_cloud/:id/reset_password
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string password - 新密码 require
	 * @param   string code - 二次验证验证码
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
	 * 时间 2022-06-24
	 * @title 退出救援模式
	 * @desc 退出救援模式
	 * @url /console/v1/mf_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
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
	 * 时间 2022-06-30
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /console/v1/mf_cloud/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @param   int password - 密码 密码和ssh密钥ID,必须选择一种
	 * @param   int ssh_key_id - ssh密钥ID 密码和ssh密钥ID,必须选择一种
	 * @param   int port - 端口 require
	 * @param   string code - 二次验证验证码
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
	 * @return  int list[].time - 时间(秒级时间戳)
	 * @return  float list[].value - CPU使用率
	 * @return  int list[].total - 总内存(单位:B)
	 * @return  int list[].used - 内存使用量(单位:B)
	 * @return  float list[].read_bytes - 读取速度(B/s)
	 * @return  float list[].write_bytes - 写入速度(B/s)
	 * @return  float list[].read_iops - 读取IOPS
	 * @return  float list[].write_iops - 写入IOPS
	 * @return  float list[].in_bw - 进带宽(bps)
	 * @return  float list[].out_bw - 出带宽(bps)
	 */
	public function chart()
	{
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
	public function flowDetail()
	{
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
     * @return  int list[].id - 魔方云磁盘ID
     * @return  string list[].name - 名称
     * @return  int list[].size - 磁盘大小(GB)
     * @return  int list[].create_time - 创建时间
     * @return  string list[].type - 磁盘类型
     * @return  string list[].type2 - 类型(system=系统盘,data=数据盘)
     * @return  int list[].is_free - 是否免费盘(0=否,1=是),免费盘不能扩容
	 */
	public function disk()
	{
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
	 * @return  string name - 磁盘名称
	 */
	public function diskUnmount()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('unmount_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  string name - 磁盘名称
	 */
	public function diskMount()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('mount_disk')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  int count - 总条数
	 */
	public function snapshot()
	{
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
	public function snapshotCreate()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('create_snapshot')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  string name - 快照名称
	 */
	public function snapshotRestore()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('snapshot')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  string name - 快照名称
	 */
	public function snapshotDelete()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('snapshot')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  int list[].status - 状态(0=创建中,1=创建成功)
	 * @return  int count - 总条数
	 */
	public function backup()
	{
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
	public function backupCreate()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('create_snapshot')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  string name - 备份名称
	 */
	public function backupRestore()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('backup')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	 * @return  string name - 备份名称
	 */
	public function backupDelete()
	{
		$param = request()->param();

        $CloudValidate = new CloudValidate();
        if (!$CloudValidate->scene('backup')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

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
	public function log()
	{
		$param = request()->param();
		$param['type'] = 'host';
		$param['rel_id'] = $param['id'];

		$SystemLogModel = new SystemLogModel();
	 	$data = $SystemLogModel->systemLogList($param);

	 	$result = [
	 		'status' => 200,
	 		'msg'	 => lang_plugins('success_message'),
	 		'data'	 => $data,
	 	];
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
     * @return string list[].ip - IP
     * @return string list[].subnet_mask - 掩码
     * @return string list[].gateway - 网关
     * @return int count - 总数
	 */
	public function ipList()
	{
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
	 * @param   array add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
	 * @param   int add_disk[].size - 磁盘大小
	 * @param   string add_disk[].type - 磁盘类型
	 * @param   int is_downstream 0 是否下游发起(0=否,1=是)
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
	 */
	public function calBuyDiskPrice()
	{
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
	 * @param   array add_disk - 新增磁盘大小参数,如:[{"size":1,"type":"SSH"}]
	 * @param   int add_disk[].size - 磁盘大小
	 * @param   string add_disk[].type - 磁盘类型
     * @return  string id - 订单ID
	 */
	public function createBuyDiskOrder()
	{
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
	 * @param   int resize_data_disk[].id - 魔方云磁盘ID
	 * @param   int resize_data_disk[].size - 磁盘大小
	 * @param   int is_downstream 0 是否下游发起(0=否,1=是)
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
	 */
	public function calResizeDiskPrice()
	{
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
	 * @param   int resize_data_disk[].id - 魔方云磁盘ID
	 * @param   int resize_data_disk[].size - 磁盘大小
     * @return  string id - 订单ID
	 */
	public function createResizeDiskOrder()
	{
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
     * @param   int is_downstream 0 是否下游(0=不是,1=是)
     * @return  string price - 需要支付的金额(0.00表示镜像免费或已购买)
     * @return  string description - 描述
	 */
	public function checkHostImage()
	{
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
	 * @return  string id - 订单ID
	 */
	public function createImageOrder()
	{
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
     * @param   string type - 类型(snap=快照,backup=备份) require
     * @param   int num - 数量 require
     * @param   int is_downstream 0 是否下游发起(0=否,1=是)
     * @return  string price - 价格
     * @return  string description - 描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
	 */
	public function calBackupConfigPrice()
	{
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
	 * @param   int num - 备份/快照数量
	 * @return  string id - 订单ID
	 */
	public function createBackupConfigOrder()
	{
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
	 * @param   int is_downstream 0 是否下游发起(0=否,1=是)
     * @return  string price - 价格
     * @return  string description - 生成的订单描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
	 */
	public function calIpNumPrice()
	{
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
	 * @return  string id - 订单ID
	 */
	public function createIpNumOrder()
	{
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
	 * @param   string name - VPC网络名称 require
	 * @param   string ips - IP段(cidr,如10.0.0.0/16,系统分配时不传)
	 * @return  int id - VPC网络ID
	 */
	public function createVpcNetwork()
	{
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
     * @param   string orderby - 排序(id,name)
     * @param   string sort - 升降序(asc,desc)
     * @param   int downstream_client_id - 下游用户ID(api时可用)
     * @return  int list[].id - VPC网络ID
     * @return  string list[].name - VPC网络名称
     * @return  string list[].ips - VPC网络网段
     * @return  int count - 总条数
     * @return  int list[].host[].id - 主机产品ID
     * @return  string list[].host[].name - 主机标识
     * @return  array host - 可用产品ID(api时返回)
	 */
	public function vpcNetworkList()
	{
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
     * @param   string name - VPC网络名称 require
     * @param   int downstream_client_id - 下游用户ID(api时可用)
     * @return  string name - 原VPC名称
	 */
	public function vpcNetworkUpdate()
	{
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
	 * @param   int downstream_client_id - 下游用户ID(api时可用)
	 */
	public function vpcNetworkDelete()
	{
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
	 * @param   int downstream_client_id - 下游用户ID(api时可用)
	 * @return  string name - 变更后VPC网络名称
	 */
	public function changeVpcNetwork()
	{
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
	public function cloudRealData()
	{
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
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int is_downstream 0 是否下游发起(0=否,1=是)
     * @return  string price - 价格
     * @return  string description - 描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
	 */
	public function calCommonConfigPrice()
	{
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
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @return  string id - 订单ID
	 */
	public function createCommonConfigOrder()
	{
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
     * @return  string bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string gpu_name - 显卡名称
     * @return  string bw[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int bw[].value - 带宽
     * @return  int bw[].min_value - 最小值
     * @return  int bw[].max_value - 最大值
     * @return  int bw[].step - 步长
     * @return  int flow[].value - 流量
     * @return  int defence[].value - 防御峰值(G)
     * @return  int ip[].value - IP数量
     * @return  int gpu[].value - 显卡数量
	 */
	public function lineConfig()
	{
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
	 * @param   int id - 商品ID require
	 * @param   int downstream_client_id - 下游用户ID(api时可用)
	 * @param   int custom.duration_id - 周期ID require
     * @param   int custom.recommend_config_id - 套餐ID
     * @param   int custom.data_center_id - 数据中心ID
     * @param   int custom.cpu - CPU
     * @param   int custom.memory - 内存
     * @param   int custom.system_disk.size - 系统盘大小(G)
     * @param   string custom.system_disk.disk_type - 系统盘类型
     * @param   int custom.data_disk[].size - 数据盘大小(G)
     * @param   string custom.data_disk[].disk_type - 数据盘类型
     * @param   int custom.line_id - 线路ID
     * @param   int custom.bw - 带宽(Mbps)
     * @param   int custom.flow - 流量(G)
     * @param   int custom.peak_defence - 防御峰值(G)
     * @param   int custom.gpu_num - 显卡数量
     * @param   int custom.image_id - 镜像ID
     * @param   int custom.ssh_key_id - SSH密钥ID
     * @param   int custom.backup_num 0 备份数量
     * @param   int custom.snap_num 0 快照数量
     * @param   int custom.ip_mac_bind_enable 0 嵌套虚拟化(0=关闭,1=开启)
     * @param   int custom.ipv6_num_enable 0 是否使用IPv6(0=关闭,1=开启)
     * @param   int custom.nat_acl_limit_enable 0 是否启用NAT转发(0=关闭,1=开启)
     * @param   int custom.nat_web_limit_enable 0 是否启用NAT建站(0=关闭,1=开启)
     * @param   int custom.resource_package_id 0 资源包ID
     * @param   string custom.network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @param   int custom.vpc.id - VPC网络ID
     * @param   string custom.vpc.ips - VPCIP段
     * @return  string price - 价格 
     * @return  string renew_price - 续费价格 
     * @return  string billing_cycle - 周期 
     * @return  int duration - 周期时长
     * @return  string description - 订单子项描述
     * @return  string base_price - 基础价格
     * @return  string billing_cycle_name - 周期名称多语言
     * @return  string preview[].name - 配置项名称
     * @return  string preview[].value - 配置项值
     * @return  string preview[].price - 配置项价格
     * @return  string discount - 用户等级折扣
     * @return  string order_item[].type - 订单子项类型(addon_idcsmart_client_level=用户等级)
     * @return  int order_item[].rel_id - 关联ID
     * @return  float order_item[].amount - 子项金额
     * @return  string order_item[].description - 子项描述
	 */
	public function validateSettle()
	{
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
        				return json(['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')]);
        			}
        			if(request()->is_api && isset($param['downstream_client_id']) && $vpcNetwork['downstream_client_id'] != $param['downstream_client_id']){
			            return json(['status'=>400, 'msg'=>lang_plugins('vpc_network_not_found')]);
			        }
        		}else{
        			$VpcNetworkValidate = new VpcNetworkValidate();
        			if(!$VpcNetworkValidate->scene('ips')->check($param['custom']['vpc'])){
			            return json(['status'=>400 , 'msg'=>lang_plugins($VpcNetworkValidate->getError())]);
			        }
        		}
        	}else{
        		return json(['status'=>400, 'msg'=>lang_plugins('support_vpc_network_param_error')]);
        	}
        }
        $param['product'] = ProductModel::find($param['id']);

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($param, false);
		return json($res);
	}

	/**
	 * 时间 2023-09-20
	 * @title NAT转发列表
	 * @desc NAT转发列表
	 * @url console/v1/mf_cloud/:id/nat_acl
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int list[].id - 转发ID
	 * @return  string list[].name - 名称
	 * @return  string list[].ip - IP端口
	 * @return  int list[].int_port - 内部端口
	 * @return  int list[].protocol - 协议(1=tcp,2=udp,3=tcp+udp)
	 * @return  int count - 总条数
	 */
	public function natAclList()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$data = $CloudLogic->natAclList();

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
	 * 时间 2023-09-20
	 * @title 创建NAT转发
	 * @desc 创建NAT转发
	 * @url console/v1/mf_cloud/:id/nat_acl
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   string name - 名称 require
     * @param   int int_port - 内部端口 require
     * @param   int protocol - 协议(1=tcp,2=udp,3=tcp+udp) require
	 */
	public function natAclCreate()
	{
		$param = request()->param();

		$NatAclValidate = new NatAclValidate();
		if (!$NatAclValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($NatAclValidate->getError())]);
        }

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->natAclCreate($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2023-09-20
	 * @title 删除NAT转发
	 * @desc 删除NAT转发
	 * @url console/v1/mf_cloud/:id/nat_acl
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   int nat_acl_id - NAT转发ID require
	 */
	public function natAclDelete()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->natAclDelete($param['nat_acl_id'] ?? 0);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2023-09-20
	 * @title NAT建站列表
	 * @desc NAT建站列表
	 * @url console/v1/mf_cloud/:id/nat_web
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int list[].id - 建站ID
	 * @return  string list[].domain - 域名
	 * @return  int list[].ext_port - 外部端口
	 * @return  int list[].int_port - 内部端口
	 * @return  int count - 总条数
	 */
	public function natWebList()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$data = $CloudLogic->natWebList();

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
	 * 时间 2023-09-20
	 * @title 创建NAT建站
	 * @desc 创建NAT建站
	 * @url console/v1/mf_cloud/:id/nat_web
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   string domain - 域名 require
     * @param   int int_port - 内部端口 require
	 */
	public function natWebCreate()
	{
		$param = request()->param();

		$NatWebValidate = new NatWebValidate();
		if (!$NatWebValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($NatWebValidate->getError())]);
        }

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->natWebCreate($param);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2023-09-20
	 * @title 删除NAT建站
	 * @desc 删除NAT建站
	 * @url console/v1/mf_cloud/:id/nat_web
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   int nat_web_id - NAT建站ID require
     * @return  string domain - 域名
	 */
	public function natWebDelete()
	{
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$result = $CloudLogic->natWebDelete($param['nat_web_id'] ?? 0);
			return json($result);
		}catch(\Exception $e){
			return json(['status'=>400, 'msg'=>$e->getMessage()]);
		}
	}

	/**
	 * 时间 2023-10-24
	 * @title 获取可升降级套餐
	 * @desc 获取可升降级套餐
	 * @url console/v1/mf_cloud/:id/recommend_config
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
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
		$param = request()->param();

		try{
			$CloudLogic = new CloudLogic((int)$param['id']);

			$data = $CloudLogic->getUpgradeRecommendConfig();
		}catch(\Exception $e){
			$data = ['list'=>[], 'count'=>0];
		}
		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-10-25
	 * @title 计算升降级套餐价格
	 * @desc 计算升降级套餐价格
	 * @url console/v1/mf_cloud/:id/recommend_config/price
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int recommend_config_id - 套餐ID require
     * @return  string price - 价格
     * @return  string description - 描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
	 */
	public function calUpgradeRecommendConfig()
	{
		$param = request()->param();

 		try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->calUpgradeRecommendConfig($param);
			if(isset($result['data']['new_config_data'])) unset($result['data']['new_config_data']);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}

	/**
	 * 时间 2023-10-25
	 * @title 生成套餐升级订单
	 * @desc 生成套餐升级订单
	 * @url console/v1/mf_cloud/:id/recommend_config/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   int recommend_config_id - 套餐ID require
	 * @return  string id - 订单ID
	 */
	public function createUpgradeRecommendConfigOrder()
	{
		$param = request()->param();

        try{
        	$CloudLogic = new CloudLogic($param['id']);

			$result = $CloudLogic->createUpgradeRecommendConfigOrder($param);
        }catch(\Exception $e){
			$result = ['status'=>400, 'msg'=>$e->getMessage()];
        }
        return json($result);
	}


}
