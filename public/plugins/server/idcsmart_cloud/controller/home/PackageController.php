<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\PackageModel;

/**
 * @title 套餐
 * @desc 套餐
 * @use server\idcsmart_cloud\controller\home\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-22
	 * @title 获取订购页实例配置
	 * @desc 获取订购页实例配置
	 * @url /console/v1/product/:id/idcsmart_cloud/package
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   int data_center_id - 数据中心ID
	 * @param   int bw_type_id - 带宽类型ID
	 * @param   int cal_group_id - 计算型号分组ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.cal_group - 计算型号分组数据
	 * @return  int data.cal_group[].id - 计算型号分组ID
	 * @return  string data.cal_group[].name - 计算型号分组名称
	 * @return  string data.cal_group[].description - 计算型号分组描述
	 * @return  array data.package - 套餐数据
	 * @return  int data.package[].id - 套餐ID
	 * @return  string data.package[].price - 套餐价格
	 * @return  string data.package[].description - 套餐描述
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$PackageModel = new PackageModel();

		$result = $PackageModel->orderConfigShow($param);
		return json($result);
	}


}


