<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\ConfigModel;
use server\idcsmart_cloud\validate\ConfigValidate;

/**
 * @title 其他设置
 * @desc 其他设置
 * @use server\idcsmart_cloud\controller\admin\ConfigController
 */
class ConfigController{

	/**
	 * 时间 2022-06-20
	 * @title 获取其他设置
	 * @desc 获取其他设置
	 * @url /admin/v1/idcsmart_cloud/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  object data - 设置数据
     * @return  int data.backup_enable - 是否启用备份价格(0=不启用,1=启用)
     * @return  string data.backup_price - 备份价格
     * @return  string data.backup_param - 备份参数
     * @return  int data.panel_enable - 是否启用独立面板价格(0=不启用,1=启用)
     * @return  string data.panel_price - 独立面板价格
     * @return  string data.panel_param - 独立面板参数
     * @return  int data.snap_enable - 是否启用快照价格(0=不启用,1=启用)
     * @return  int data.snap_free_num - 免费快照数量
     * @return  string data.snap_price - 快照价格
     * @return  int data.hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机)
     * @return  int data.product_id - 商品ID
	 */
	public function index(){
		$param = request()->param();

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->indexConfig($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存其他设置
	 * @desc 保存其他设置
	 * @url /admin/v1/idcsmart_cloud/config
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param  int product_id - 商品ID require
     * @param  int backup_enable - 是否启用备份价格(0=不启用,1=启用) require
     * @param  string backup_price - 备份价格 require
     * @param  string backup_param - 备份参数 require
     * @param  int panel_enable - 是否启用独立面板价格(0=不启用,1=启用) require
     * @param  string panel_price - 独立面板价格 require
     * @param  string panel_param - 独立面板参数 require
     * @param  int snap_enable - 是否启用快照价格(0=不启用,1=启用) require
     * @param  int snap_free_num - 免费快照数量 require
     * @param  string snap_price - 快照价格 require
     * @param  int hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机) require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
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