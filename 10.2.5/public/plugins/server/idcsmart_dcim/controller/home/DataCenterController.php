<?php
namespace server\idcsmart_dcim\controller\home;

use server\idcsmart_dcim\model\DataCenterModel;

/**
 * @title DCIM数据中心
 * @desc DCIM数据中心
 * @use server\idcsmart_dcim\controller\home\DataCenterController
 */
class DataCenterController{

	/**
	 * 时间 2022-06-21
	 * @title 获取数据中心
	 * @desc 获取数据中心
	 * @url /console/v1/product/:id/idcsmart_dcim/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 商品ID
     * @return  array list - 列表数据
     * @return  string list[].id - 国家ID
     * @return  string list[].iso - 图标
     * @return  string list[].name_zh - 国家名称
     * @return  int list[].city[].id - 数据中心ID
     * @return  string list[].city[].name - 城市
	 */
	public function list(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->formatDisplay($param);
		return json($result);
	}


}


