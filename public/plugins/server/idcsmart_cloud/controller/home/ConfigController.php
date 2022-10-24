<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\ConfigModel;

/**
 * @title 其他设置
 * @desc 其他设置
 * @use server\idcsmart_cloud\controller\home\ConfigController
 */
class ConfigController{

	/**
	 * 时间 2022-06-22
	 * @title 获取其他配置
	 * @desc 获取其他配置
	 * @url /console/v1/product/:id/idcsmart_cloud/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  object data - 设置数据
     * @return  int data.backup_enable - 是否启用备份价格(0=不启用,1=启用)
     * @return  string data.backup_price - 备份价格
     * @return  int data.panel_enable - 是否启用独立面板价格(0=不启用,1=启用)
     * @return  string data.panel_price - 独立面板价格
     * @return  int data.snap_enable - 是否启用快照价格(0=不启用,1=启用)
     * @return  int data.snap_free_num - 免费快照数量
     * @return  string data.snap_price - 快照价格
     * @return  int data.hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机)
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->homeConfig($param);
		return json($result);
	}


}


