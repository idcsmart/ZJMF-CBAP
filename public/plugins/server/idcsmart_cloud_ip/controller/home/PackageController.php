<?php
namespace server\idcsmart_cloud_ip\controller\home;

use server\idcsmart_cloud_ip\model\PackageModel;

/**
 * @title 魔方云IP套餐
 * @desc 魔方云IP套餐
 * @use server\idcsmart_cloud_ip\controller\home\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-28
	 * @title 获取订购页实例配置
	 * @desc 获取订购页实例配置
	 * @url /console/v1/product/:id/idcsmart_cloud_ip/package
	 * @method  GET
	 * @author theworld
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int host_id - 实例ID require
	 * @param   string bw_type - 带宽类型:default默认,independ独立 require
	 * @return  array package - 套餐数据
	 * @return  int package[].id - 套餐ID
	 * @return  string package[].ip_price - IP价格
	 * @return  string package[].ip_max - 单个实例上限
	 * @return  string package[].bw_precision - 带宽最低精度
	 * @return  string package[].bw_price - 带宽价格
	 * @return  string package[].bw_type_name - 带宽类型名称
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$PackageModel = new PackageModel();

		$package = $PackageModel->orderConfigShow($param);

		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>[
				'package' => $package,
			]
		];
		return json($result);
	}


}


