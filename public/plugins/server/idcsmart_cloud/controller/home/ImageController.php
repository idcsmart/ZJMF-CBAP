<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\ImageModel;

/**
 * @title 镜像
 * @desc 镜像
 * @use server\idcsmart_cloud\controller\home\ImageController
 */
class ImageController{

	/**
	 * 时间 2022-06-22
	 * @title 获取可用官方镜像
	 * @desc 获取可用官方镜像
	 * @url /console/v1/product/:id/idcsmart_cloud/system_image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   int data_center_id - 数据中心ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 镜像数据
	 * @return  int  data.list[].id - 镜像组ID
	 * @return  string data.list[].name - 镜像组名称
	 * @return  string data.list[].icon - 图标路径
	 * @return  int data.list[].image[].id - 镜像ID
	 * @return  string data.list[].image[].name - 镜像名称
	 * @return  int data.list[].image[].charge - 是否付费(0=免费,1=付费)
	 * @return  string data.list[].image[].price - 价格
	 */
	public function systemImage(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$ImageModel = new ImageModel();

		$result = $ImageModel->getEnableSystemImage($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 检查产品是够购买过镜像
	 * @desc 检查产品是够购买过镜像
	 * @url /console/v1/idcsmart_cloud/:id/image/check
	 * @method GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @return  string data.price - 需要支付的金额(0.00表示镜像免费或已购买)
	 */
	public function checkHostImage(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->checkHostImage($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 生成购买镜像订单
	 * @desc 生成购买镜像订单
	 * @url /console/v1/idcsmart_cloud/:id/image/order
	 * @method POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int image_id - 镜像ID require
	 * @return  string data.id - 订单ID
	 */
	public function createImageOrder(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->createImageOrder($param);
		return json($result);
	}




}


