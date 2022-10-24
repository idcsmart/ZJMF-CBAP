<?php
namespace server\idcsmart_dcim\controller\home;

use server\idcsmart_dcim\model\ImageModel;

/**
 * @title DCIM镜像
 * @desc DCIM镜像
 * @use server\idcsmart_dcim\controller\home\ImageController
 */
class ImageController{

	/**
	 * 时间 2022-06-22
	 * @title 获取可用操作系统
	 * @desc 获取可用操作系统
	 * @url /console/v1/product/:id/idcsmart_dcim/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
     * @return  array list - 镜像数据
     * @return  int list[].id - 镜像分组ID
     * @return  string list[].name - 镜像分组
     * @return  int list[].image[].id - 镜像ID
     * @return  string list[].image[].name - 镜像名称
     * @return  int list[].image[].charge - 是否付费
     * @return  string list[].image[].price - 价格
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$ImageModel = new ImageModel();

		$result = $ImageModel->homeImageList($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 检查产品是够购买过镜像
	 * @desc 检查产品是够购买过镜像
	 * @url /console/v1/idcsmart_dcim/:id/image/check
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
	 * @url /console/v1/idcsmart_dcim/:id/image/order
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


