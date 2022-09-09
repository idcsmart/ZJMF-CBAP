<?php
namespace server\idcsmart_cloud_disk\controller\home;

use server\idcsmart_cloud_disk\model\PackageModel;

/**
 * @title 魔方云磁盘套餐
 * @desc 魔方云磁盘套餐
 * @use server\idcsmart_cloud_disk\controller\home\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-28
	 * @title 获取订购页实例配置
	 * @desc 获取订购页实例配置
	 * @url /console/v1/product/:id/idcsmart_cloud_disk/package
	 * @method  GET
	 * @author theworld
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int host_id - 实例ID require
	 * @return  array package - 套餐数据
	 * @return  int package[].id - 套餐ID
	 * @return  string package[].name - 套餐名称
	 * @return  string package[].description - 套餐描述
	 * @return  string package[].price - 套餐价格
	 * @return  string package[].size_min - 容量范围最小值,GB
	 * @return  string package[].size_max - 容量范围最大值,GB
	 * @return  string package[].precision - 最低精度
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


