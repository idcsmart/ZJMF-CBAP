<?php
namespace server\idcsmart_cloud_ip\controller\admin;

use server\idcsmart_cloud_ip\model\PackageModel;
use server\idcsmart_cloud_ip\validate\PackageValidate;

/**
 * @title 魔方云IP套餐
 * @desc 魔方云IP套餐
 * @use server\idcsmart_cloud_ip\controller\admin\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-27
	 * @title 套餐列表
	 * @desc 套餐列表
	 * @url /admin/v1/idcsmart_cloud_ip/package
	 * @method  GET
	 * @author theworld
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array list - 列表数据
     * @return  int list[].id - 带宽ID
     * @return  string list[].bw - 带宽
     * @return  int list[].flow - 流量
     * @return  string list[].price - 价格
     * @return  string list[].description - 描述
     * @return  string list[].bw_type_name - 带宽类型名称
     * @return  int list[].ip_enable - 是否启用附加IP,0否1是
     * @return  string list[].ip_price - IP价格
     * @return  int list[].ip_max - 单个实例上限
     * @return  int list[].bw_enable - 是否启用独立带宽,0否1是
     * @return  int list[].bw_precision - 带宽最低精度
     * @return  array list[].bw_price - 带宽价格
	 * @return  int list[].bw_price[].min - 带宽区间最小值
	 * @return  int list[].bw_price[].max - 带宽区间最大值
	 * @return  string bw_price[].price - 带宽区间价格
     * @return  array list[].data_center - 数据中心
     * @return  int list[].data_center[].id - 数据中心ID
     * @return  string list[].data_center[].country - 国家
     * @return  string list[].data_center[].city - 城市
     * @return  string list[].data_center[].area - 区域
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
	 * @title 编辑套餐
	 * @desc 编辑套餐
	 * @url /admin/v1/idcsmart_cloud_ip/package/:id
	 * @method  PUT
	 * @author theworld
	 * @version v1
	 * @param int id - 带宽ID require
	 * @param int product_id - 商品ID require
	 * @param int ip_enable - 是否启用附加IP,0否1是 require
	 * @param float ip_price - IP价格 启用附加IP时必填
	 * @param int ip_max - 单个实例上限 启用附加IP时必填
	 * @param int bw_enable - 是否启用独立带宽,0否1是 启用附加IP时必填
	 * @param int bw_precision - 带宽最低精度 启用独立带宽时必填
	 * @param array bw_price - 带宽价格 启用独立带宽时必填
	 * @param int bw_price[].min - 带宽区间最小值 第一行为0,和上一行的带宽区间最大值相同
	 * @param int bw_price[].max - 带宽区间最大值 带宽区间最大值需要大于带宽区间最小值
	 * @param float bw_price[].price - 带宽区间价格
	 */
	public function save()
	{
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->savePackage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 启用/禁用附加IP
	 * @desc 启用/禁用附加IP
	 * @url /admin/v1/idcsmart_cloud_ip/package/:id/ip
	 * @method  PUT
	 * @author theworld
	 * @version v1
	 * @param int id - 带宽ID require
	 * @param int ip_enable - 是否启用附加IP,0否1是 require
	 */
	/*public function ipEnable()
	{
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('ip_enable')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->ipEnable($param);
		return json($result);
	}*/

	/**
	 * 时间 2022-06-27
	 * @title 启用/禁用独立带宽
	 * @desc 启用/禁用独立带宽
	 * @url /admin/v1/idcsmart_cloud_ip/package/:id/bw
	 * @method  PUT
	 * @author theworld
	 * @version v1
	 * @param int id - 带宽ID require
	 * @param int bw_enable - 是否启用独立带宽,0否1是 require
	 */
	/*public function bwEnable()
	{
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('bw_enable')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->bwEnable($param);
		return json($result);
	}*/
}


