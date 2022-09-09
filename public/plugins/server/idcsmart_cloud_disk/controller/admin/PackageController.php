<?php
namespace server\idcsmart_cloud_disk\controller\admin;

use server\idcsmart_cloud_disk\model\PackageModel;
use server\idcsmart_cloud_disk\validate\PackageValidate;

/**
 * @title 魔方云磁盘套餐
 * @desc 魔方云磁盘套餐
 * @use server\idcsmart_cloud_disk\controller\admin\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-27
	 * @title 套餐列表
	 * @desc 套餐列表
	 * @url /admin/v1/idcsmart_cloud_disk/package
	 * @method  GET
	 * @author theworld
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array list - 列表数据
     * @return  int list[].id - 套餐ID
     * @return  string list[].name - 套餐名称
     * @return  string list[].description - 描述
     * @return  int list[].module_idcsmart_cloud_data_center_id - 数据中心ID
     * @return  string list[].country - 国家
     * @return  string list[].country_code - 国家代码
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  int list[].size_min - 容量范围最小值,GB
     * @return  int list[].size_max - 容量范围最大值,GB
     * @return  int list[].precision - 最低精度
     * @return  int list[].price - 单价
     * @return  int list[].order - 排序
     * @return  int count - 总条数
	 */
	public function list()
	{
		// 合并分页参数
        $param = array_merge(request()->param(), ['page' => request()->page, 'limit' => request()->limit, 'sort' => request()->sort]);

		$PackageModel = new PackageModel();

		$data = $PackageModel->packageList($param);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => $data
		];
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 创建套餐
	 * @desc 创建套餐
	 * @url /admin/v1/idcsmart_cloud_disk/package
	 * @method  POST
	 * @author theworld
	 * @version v1
	 * @param int product_id - 商品ID require
	 * @param string name - 套餐名称 require
	 * @param string description - 描述 require
	 * @param int module_idcsmart_cloud_data_center_id - 数据中心ID require
	 * @param int size_min - 容量范围最小值,GB require
	 * @param int size_max - 容量范围最大值,GB require
	 * @param int precision - 最低精度 require
	 * @param int price - 单价 require
	 * @param int order - 排序 require
	 * @return int id - 创建的套餐ID
	 */
	public function create()
	{
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
	 * 时间 2022-06-27
	 * @title 修改套餐
	 * @desc 修改套餐
	 * @url /admin/v1/idcsmart_cloud_disk/package/:id
	 * @method  PUT
	 * @author theworld
	 * @version v1
	 * @param int id - 套餐ID require
	 * @param int product_id - 商品ID require
	 * @param string name - 套餐名称 require
	 * @param string description - 描述 require
	 * @param int module_idcsmart_cloud_data_center_id - 数据中心ID require
	 * @param int size_min - 容量范围最小值,GB require
	 * @param int size_max - 容量范围最大值,GB require
	 * @param int precision - 最低精度 require
	 * @param int price - 单价 require
	 * @param int order - 排序 require
	 */
	public function update()
	{
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->updatePackage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除套餐
	 * @desc 删除套餐
	 * @url /admin/v1/idcsmart_cloud_disk/package/:id
	 * @method  DELETE
	 * @author theworld
	 * @version v1
	 * @param int id - 套餐ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$PackageModel = new PackageModel();

		$result = $PackageModel->deletePackage((int)$param['id']);

		return json($result);
	}




}


