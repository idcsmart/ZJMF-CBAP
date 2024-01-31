<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\ConfigModel;
use server\mf_dcim\validate\ConfigValidate;
use server\mf_dcim\model\HostLinkModel;

/**
 * @title DCIM(自定义配置)-其他设置
 * @desc DCIM(自定义配置)-其他设置
 * @use server\mf_dcim\controller\admin\ConfigController
 */
class ConfigController{

	/**
	 * 时间 2022-06-20
	 * @title 获取设置
	 * @desc 获取设置
	 * @url /admin/v1/mf_dcim/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int manual_resource - 手动资源(0=不启用,1=启用)
     * @return  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用)
     * @return  int optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用)
     * @return  int level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用)
     * @return  int level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用)
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
	 * @url /admin/v1/mf_dcim/config
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启) require
     * @param  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用) require
     * @param  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用) require
     * @param  int manual_resource - 手动资源(0=不启用,1=启用) require
     * @param  int level_discount_memory_order - 内存是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_memory_upgrade - 内存是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_disk_order - 硬盘是否应用等级优惠订购(0=不启用,1=启用) require
     * @param  int level_discount_disk_upgrade - 硬盘是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_bw_upgrade - 带宽是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int level_discount_ip_num_upgrade - IP是否应用等级优惠升降级(0=不启用,1=启用) require
     * @param  int optional_host_auto_create - 选配机器是否自动开通(0=不启用,1=启用) require
     * @param  int level_discount_gpu_order - 显卡是否应用等级优惠订购(0=不启用,1=启用)
     * @param  int level_discount_gpu_upgrade - 显卡是否应用等级优惠升降级(0=不启用,1=启用)
	 */
	public function save(){
		$param = request()->param();

		$ConfigValidate = new ConfigValidate();
		if (!$ConfigValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->saveConfig($param);
		return json($result);
	}

	/**
	 * 时间 2024-01-19
	 * @title 获取DCIM分配服务器列表
	 * @desc  获取DCIM分配服务器列表
	 * @url /admin/v1/mf_dcim/host/:id/sales
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 产品ID require
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int status - 状态(1=空闲,2=到期,3=正常,4=故障,5=预装,6=锁定,7=审核中)
     * @param   int server_group_id - DCIM服务器分组ID
     * @param   string ip - 搜索:IP
     * @return  int list[].id - DCIMID
     * @return  string list[].wltag - 标签
     * @return  string list[].typename - 型号
     * @return  string list[].group_name - 分组名称
     * @return  string list[].mainip - 主IP
     * @return  int list[].ip_num - IP数量
     * @return  int list[].ip[].id - IPID
     * @return  string list[].ip[].ipaddress - IP地址
     * @return  string list[].ip[].server_mainip - 是否主IP(true=是,false=否)
     * @return  string list[].in_bw - 进带宽
     * @return  string list[].out_bw - 出带宽
     * @return  string list[].remarks - 备注
     * @return  int list[].status - 状态(1=空闲,2=到期,3=正常,4=故障,5=预装,6=锁定,7=审核中)
     * @return  int list[].host_id - 产品ID
     * @return  int list[].client_id - 所属用户
     * @return  string list[].type - 类型(rent=租用,trust=托管)
     * @return  string list[].dcim_url - dcim链接
     * @return  int count - 总条数
     * @return  int server_group[].id - 服务器分组ID
     * @return  string server_group[].name - 服务器分组名称
     * @return  string server_group[].config - 服务器分组配置
	 */
	public function dcimSalesList(){
        $param = request()->param();
        $param = array_merge($param, ['page' => $param['page'] ?? 1, 'limit' => $param['limit'] ?? 20]);

        $HostLinkModel = new HostLinkModel();
        $data = $HostLinkModel->dcimSalesList($param);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return json($result);
	}

    /**
     * 时间 2024-01-19
     * @title 分配DCIM服务器
     * @desc  分配DCIM服务器
     * @url /admin/v1/mf_dcim/host/:id/assign
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int dcim_id - DCIMID require
     */
    public function assignDcimServer(){
        $param = request()->param();
        
        $HostLinkModel = new HostLinkModel();
        $result = $HostLinkModel->assignDcimServer($param);
        return json($result);
    }

    /**
     * 时间 2024-01-23
     * @title 空闲DCIM服务器
     * @desc  空闲DCIM服务器
     * @url /admin/v1/mf_dcim/host/:id/free
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function freeDcimServer(){
        $param = request()->param();
        
        $HostLinkModel = new HostLinkModel();
        $result = $HostLinkModel->freeDcimServer($param);
        return json($result);
    }


}