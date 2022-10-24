<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\BwTypeModel;
use server\idcsmart_cloud\validate\BwTypeValidate;

/**
 * @title 带宽类型管理
 * @desc 带宽类型管理
 * @use server\idcsmart_cloud\controller\admin\BwTypeController
 */
class BwTypeController{

	/**
	 * 时间 2022-06-16
	 * @title 创建带宽类型
	 * @desc 创建带宽类型
	 * @url /admin/v1/idcsmart_cloud/bw_type
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   string name - 名称 require
	 * @param   int order 0 排序
	 * @param   string description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的带宽类型ID
	 */
	public function create(){
		$param = request()->param();

		$BwTypeValidate = new BwTypeValidate();
		if (!$BwTypeValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BwTypeValidate->getError())]);
        }
		$BwTypeModel = new BwTypeModel();

		$result = $BwTypeModel->createBwType($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 带宽类型列表
	 * @desc 带宽类型列表
	 * @url /admin/v1/idcsmart_cloud/bw_type
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID
	 * @param   string orderby id 排序(id,name,order)
	 * @param   string sort desc 升降序(asc=升序,desc=降序)
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

		$BwTypeModel = new BwTypeModel();

		$result = $BwTypeModel->bwTypeList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 修改带宽类型
	 * @desc 修改带宽类型
	 * @url /admin/v1/idcsmart_cloud/bw_type/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽类型ID require
	 * @param   string name - 名称 require
	 * @param   int order 0 排序
	 * @param   string description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$BwTypeValidate = new BwTypeValidate();
		if (!$BwTypeValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BwTypeValidate->getError())]);
        }
		$BwTypeModel = new BwTypeModel();

		$result = $BwTypeModel->updateBwType($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 删除带宽类型
	 * @desc 删除带宽类型
	 * @url /admin/v1/idcsmart_cloud/bw_type/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽类型ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$BwTypeModel = new BwTypeModel();

		$result = $BwTypeModel->deleteBwType((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 修改带宽类型排序
	 * @desc 修改带宽类型排序
	 * @url /admin/v1/idcsmart_cloud/bw_type/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽类型ID require
	 * @param   int order 0 排序 require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateOrder(){
		$param = request()->param();
		
		$BwTypeValidate = new BwTypeValidate();
		if (!$BwTypeValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BwTypeValidate->getError())]);
        }
		$BwTypeModel = new BwTypeModel();

		$result = $BwTypeModel->updateOrder($param);
		return json($result);
	}


}


