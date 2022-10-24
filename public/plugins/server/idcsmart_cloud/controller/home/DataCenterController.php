<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\DataCenterModel;

/**
 * @title 数据中心
 * @desc 数据中心
 * @use server\idcsmart_cloud\controller\home\DataCenterController
 */
class DataCenterController{

	/**
	 * 时间 2022-06-21
	 * @title 获取数据中心
	 * @desc 获取数据中心
	 * @url /console/v1/product/:id/idcsmart_cloud/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  int data.list[].area[].id - 数据中心ID
     * @return  string data.list[].area[].area - 区域名称
	 */
	public function list(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->formatDisplay($param);
		return json($result);
	}


}


