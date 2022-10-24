<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\PackageModel;
use server\idcsmart_cloud\validate\PackageValidate;

/**
 * @title 套餐管理
 * @desc 套餐管理
 * @use server\idcsmart_cloud\controller\admin\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-17
	 * @title 创建套餐
	 * @desc 创建套餐
	 * @url /admin/v1/idcsmart_cloud/package
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   string name - 套餐名称 require
	 * @param   array cal_id - 计算型号ID require
	 * @param   array data_center_id - 数据中心ID require
	 * @param   array bw_id - 带宽ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.id - 创建的套餐ID
	 */
	public function create(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->createPackage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 套餐列表
	 * @desc 套餐列表
	 * @url /admin/v1/idcsmart_cloud/package
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 套餐ID
     * @return  string data.list[].name - 套餐名称
     * @return  int data.list[].module_idcsmart_cloud_cal_id - 计算型号ID
     * @return  int data.list[].module_idcsmart_cloud_bw_id - 带宽ID
     * @return  string data.list[].cal_name - 计算型号名称
     * @return  string data.list[].cal_price - 计算型号价格
     * @return  int data.list[].bw - 带宽
     * @return  int data.list[].flow - 流量
     * @return  string data.list[].bw_price - 带宽价格
     * @return  string data.list[].description - 描述
     * @return  string data.list[].bw_type_name - 带宽类型名称
     * @return  string data.list[].price - 套餐月付价格
     * @return  int    data.list[].data_center[].id - 数据中心ID
     * @return  string data.list[].data_center[].country - 国家
     * @return  string data.list[].data_center[].city - 城市
     * @return  string data.list[].data_center[].area - 区域
     * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$result = $PackageModel->packageList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改套餐
	 * @desc 修改套餐
	 * @url /admin/v1/idcsmart_cloud/package/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @param   string name - 套餐名称 require
	 * @param   int module_idcsmart_cloud_cal_id - 计算型号ID require
	 * @param   array data_center_id - 数据中心ID require
	 * @param   int module_idcsmart_cloud_bw_id - 带宽ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->updatePackage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 删除套餐
	 * @desc 删除套餐
	 * @url /admin/v1/idcsmart_cloud/package/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$result = $PackageModel->deletePackage((int)$param['id']);
		return json($result);
	}




}


