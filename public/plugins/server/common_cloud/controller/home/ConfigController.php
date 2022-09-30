<?php
namespace server\common_cloud\controller\home;

use server\common_cloud\model\ConfigModel;

/**
 * @title 通用云其他设置
 * @desc 通用云其他设置
 * @use server\common_cloud\controller\home\ConfigController
 */
class ConfigController{

	/**
	 * 时间 2022-06-22
	 * @title 获取其他配置
	 * @desc 获取其他配置
	 * @url /console/v1/product/:id/common_cloud/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int data.product_type - 产品模式(0=固定配置,1=自定义配置)
     * @return  int data.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int data.buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @return  float data.price - 每10G价格
     * @return  string data.disk_min_size - 最小容量
     * @return  string data.disk_max_size - 最大容量
     * @return  int data.disk_max_num - 最大附加数量
     * @return  int data.backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int data.snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int data.backup_option[].num - 备份数量
     * @return  string data.backup_option[].price - 价格
     * @return  int data.snap_option[].num - 快照数量
     * @return  string data.snap_option[].price - 价格
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->homeConfig($param);
		return json($result);
	}


}


