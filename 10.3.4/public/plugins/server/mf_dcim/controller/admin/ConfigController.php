<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\ConfigModel;
use server\mf_dcim\validate\ConfigValidate;

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



}