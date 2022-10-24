<?php
namespace server\idcsmart_dcim\controller\home;

use server\idcsmart_dcim\model\PackageModel;
use server\idcsmart_dcim\validate\CloudValidate;

/**
 * @title DCIM套餐
 * @desc DCIM套餐
 * @use server\idcsmart_dcim\controller\home\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-22
	 * @title 获取订购页套餐
	 * @desc 获取订购页实例套餐
	 * @url /console/v1/product/:id/idcsmart_dcim/package
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   int data_center_id - 数据中心ID
	 * @param   int page - 页数
	 * @param   int limit - 每页条数
     * @return  array data.package - 套餐数据
     * @return  int data.package[].id - 套餐ID
     * @return  string data.package[].name - 套餐名称
     * @return  string data.package[].description - 套餐描述
     * @return  string data.package[].onetime_fee - 一次性费用(空不支持,0=免费)
     * @return  string data.package[].month_fee - 月(空不支持,0=免费)
     * @return  string data.package[].quarter_fee - 季度(空不支持,0=免费)
     * @return  string data.package[].year_fee - 年(空不支持,0=免费)
     * @return  string data.package[].two_year - 年(空不支持,0=免费)
     * @return  string data.package[].three_year - 年(空不支持,0=免费)
     * @return  string data.product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];

		$PackageModel = new PackageModel();

		$result = $PackageModel->orderConfigShow($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-29
	 * @title 获取升降级套餐价格
	 * @desc 获取升降级套餐价格
	 * @url /console/v1/idcsmart_dcim/:id/package/upgrade
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int package_id - 产品ID require
	 * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calUpgradePackagePrice(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('upgrade_package')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

		$PackageModel = new PackageModel();
		$result = $PackageModel->calUpgradePackagePrice($param);
		return json($result);
	}


	/**
	 * 时间 2022-07-29
	 * @title 生成升降级套餐订单
	 * @desc 生成升降级套餐订单
	 * @url /console/v1/idcsmart_dcim/:id/package/upgrade/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int package_id - 产品ID require
	 * @return  string data.id - 订单ID
	 */
	public function createUpgradePackageOrder(){
		$param = request()->param();

		$CloudValidate = new CloudValidate();
		if (!$CloudValidate->scene('upgrade_package')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CloudValidate->getError())]);
        }

		$PackageModel = new PackageModel();
		$result = $PackageModel->createUpgradePackageOrder($param);
		return json($result);
	}

	/**
	 * 时间 2022-10-12
	 * @title 获取套餐所有周期价格
	 * @desc 获取套餐所有周期价格
	 * @url /console/v1/product/:id/idcsmart_dcim/duration
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   int package_id - 套餐ID require
     * @param   int image_id 0 镜像ID
     * @return  string [].name - 周期名称
     * @return  string [].duration - 周期标识
     * @return  string [].price.total - 周期总价
     * @return  string [].price.package - 套餐价格
     * @return  string [].price.data_disk - 数据盘价格
     * @return  string [].price.backup - 备份数量价格
     * @return  string [].price.snap - 快照数量价格
     * @return  string [].price.image - 镜像价格
	 */
	public function getAllDurationPrice(){
		$param = request()->param();

		$PackageModel = new PackageModel();
		$result = $PackageModel->allDuration($param);
		return json($result);
	}




}


