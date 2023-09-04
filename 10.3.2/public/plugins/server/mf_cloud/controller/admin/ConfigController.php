<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\ConfigModel;
use server\mf_cloud\validate\ConfigValidate;
use server\mf_cloud\validate\BackupConfigValidate;
use server\mf_cloud\validate\ResourcePackageValidate;

/**
 * @title 魔方云(自定义配置)-其他设置
 * @desc 魔方云(自定义配置)-其他设置
 * @use server\mf_cloud\controller\admin\ConfigController
 */
class ConfigController{

	/**
	 * 时间 2022-06-20
	 * @title 获取设置
	 * @desc 获取设置
	 * @url /admin/v1/mf_cloud/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低)
     * @return  int ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @return  int support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @return  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int support_normal_network - 经典网络(0=不支持,1=支持)
     * @return  int support_vpc_network - VPC网络(0=不支持,1=支持)
     * @return  int support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @return  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int disk_limit_enable - 性能限制(0=不启用,1=启用)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @return  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @return  string ipv6_num - IPv6数量
     * @return  string nat_acl_limit - NAT转发限制
     * @return  string nat_web_limit - NAT建站限制
     * @return  bool is_agent - 是否是代理商(是的时候才能添加资源包)
     * @return  int backup_data[].num - 备份数量
     * @return  float backup_data[].price - 备份价格
     * @return  int snap_data[].num - 快照数量
     * @return  float snap_data[].price - 快照价格
     * @return  int resource_package[].id - 
     * @return  int resource_package[].rid - 资源包ID
     * @return  string resource_package[].name - 资源包名称
	 */
	public function index(){
		$param = request()->param();

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->indexConfig($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存设置
	 * @desc 保存设置
	 * @url /admin/v1/mf_cloud/config
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低) require
     * @param  int ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启) require
     * @param  int support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启) require
     * @param  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启) require
     * @param  int support_normal_network - 经典网络(0=不支持,1=支持) require
     * @param  int support_vpc_network - VPC网络(0=不支持,1=支持) require
     * @param  int support_public_ip - 是否允许公网IP(0=不支持,1=支持) require
     * @param  int backup_enable - 是否启用备份(0=不启用,1=启用) require
     * @param  int snap_enable - 是否启用快照(0=不启用,1=启用) require
     * @param  int disk_limit_enable - 性能限制(0=不启用,1=启用) require
     * @param  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用) require
     * @param  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用) require
     * @param  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @param  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @param  string ipv6_num - IPv6数量
     * @param  string nat_acl_limit - NAT转发限制
     * @param  string nat_web_limit - NAT建站限制
     * @param  int backup_data[].num - 备份数量
     * @param  float backup_data[].price - 备份价格
     * @param  int snap_data[].num - 快照数量
     * @param  float snap_data[].price - 快照价格
     * @param  int resource_package[].rid - 资源包ID
     * @param  string resource_package[].name - 资源包名称
	 */
	public function save(){
		$param = request()->param();

		$ConfigValidate = new ConfigValidate();
		if (!$ConfigValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $BackupConfigValidate = new BackupConfigValidate();
        if(isset($param['backup_data']) && is_array($param['backup_data'])){
        	foreach($param['backup_data'] as $v){
        		if (!$BackupConfigValidate->scene('save')->check($v)){
		            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
		        }
        	}
        }else{
        	$param['backup_data'] = null;
        }
        if(isset($param['snap_data']) && is_array($param['snap_data'])){
        	foreach($param['snap_data'] as $v){
        		if (!$BackupConfigValidate->scene('save')->check($v)){
		            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
		        }
        	}
        }else{
        	$param['snap_data'] = null;
        }
        if(isset($param['resource_package']) && is_array($param['resource_package'])){
            $ResourcePackageValidate = new ResourcePackageValidate();

            foreach($param['resource_package'] as $v){
                if (!$ResourcePackageValidate->scene('save')->check($v)){
                    return json(['status' => 400 , 'msg' => lang_plugins($ResourcePackageValidate->getError())]);
                }
            }
        }else{
            $param['resource_package'] = null;
        }

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->saveConfig($param);
		return json($result);
	}

    /**
     * 时间 2023-02-02
     * @title 切换性能限制开关
     * @desc 切换性能限制开关
     * @url  /admin/v1/mf_cloud/config/disk_limit_enable
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int status - 状态(0=关闭,1=开启) require
     */
    public function toggleDiskLimitEnable(){
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('toggle')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $param['field'] = 'disk_limit_enable';

        $ConfigModel = new ConfigModel();

        $result = $ConfigModel->toggleSwitch($param);
        return json($result);
    }

    /**
     * 时间 2023-08-22
     * @title 检查切换类型后是否清空冲突数据
     * @desc 检查切换类型后是否清空冲突数据
     * @url /admin/v1/mf_cloud/config/check_clear
     * @method  POST
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int type - 类型(host=加强版,lightHost=轻量版,hyperv=Hyper-V) require
     * @return  bool clear - 是否会清除数据
     * @return  string desc - 清楚数据描述
     */
    public function checkClear(){
        $param = request()->param();

        $ConfigValidate = new ConfigValidate();
        if (!$ConfigValidate->scene('check_clear')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $ConfigModel = new ConfigModel();

        $data = $ConfigModel->isClear($param['product_id'], $param['type']);

        $result = [
            'status' => 200,
            'data'   => $data,
        ];
        return json($result);
    }




}