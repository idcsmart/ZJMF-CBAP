<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\BwTypeModel;

/**
 * @title 带宽类型
 * @desc 带宽类型
 * @use server\idcsmart_cloud\controller\home\BwTypeController
 */
class BwTypeController{

	/**
	 * 时间 2022-06-21
	 * @title 带宽类型
	 * @desc 带宽类型
	 * @url /console/v1/product/:id/idcsmart_cloud/bw_type
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 带宽类型ID
	 * @return  string data.list[].name - 名称
	 * @return  int data.list[].order - 排序
	 * @return  string data.list[].description - 描述
	 * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();
		$param['product_id'] = $param['id'];
		$param['order'] = 'order';
		$param['sort'] = 'asc';

		$BwTypeModel = new BwTypeModel();

		$result = $BwTypeModel->bwTypeList($param);
		return json($result);
	}


}


