<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\CalGroupModel;
use server\idcsmart_cloud\validate\CalGroupValidate;

/**
 * @title 计算型号分组管理
 * @desc 计算型号分组管理
 * @use server\idcsmart_cloud\controller\admin\CalGroupController
 */
class CalGroupController{

	/**
	 * 时间 2022-06-14
	 * @title 创建计算分组型号
	 * @desc 创建计算分组型号
	 * @url /admin/v1/idcsmart_cloud/cal_group
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   string name - 名称 require
	 * @param   int order 0 排序
	 * @param   string description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的计算型号分组ID
	 */
	public function create(){
		$param = request()->param();

		$CalGroupValidate = new CalGroupValidate();
		if (!$CalGroupValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CalGroupValidate->getError())]);
        }
		$CalGroupModel = new CalGroupModel();

		$result = $CalGroupModel->createCalGroup($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 计算型号分组列表
	 * @desc 计算型号分组列表
	 * @url /admin/v1/idcsmart_cloud/cal_group
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 计算型号分组ID
	 * @return  string data.list[].name - 名称
	 * @return  int data.list[].order - 排序
	 * @return  string data.list[].description - 描述
	 * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$CalGroupModel = new CalGroupModel();

		$result = $CalGroupModel->calGroupList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 修改计算型号分组
	 * @desc 修改计算型号分组
	 * @url /admin/v1/idcsmart_cloud/cal_group/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 计算型号分组ID require
	 * @param   string name - 名称 require
	 * @param   int order 0 排序
	 * @param   string description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$CalGroupValidate = new CalGroupValidate();
		if (!$CalGroupValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CalGroupValidate->getError())]);
        }
		$CalGroupModel = new CalGroupModel();

		$result = $CalGroupModel->updateCalGroup($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 删除计算型号分组
	 * @desc 删除计算型号分组
	 * @url /admin/v1/idcsmart_cloud/cal_group/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 计算型号分组ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$CalGroupModel = new CalGroupModel();

		$result = $CalGroupModel->deleteCalGroup((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-17
	 * @title 修改计算型号分组排序
	 * @desc 修改计算型号分组排序
	 * @url /admin/v1/idcsmart_cloud/cal_group/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 计算型号分组ID require
	 * @param   int order - 排序 require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateOrder(){
		$param = request()->param();

		$CalGroupValidate = new CalGroupValidate();
		if (!$CalGroupValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CalGroupValidate->getError())]);
        }
		$CalGroupModel = new CalGroupModel();

		$result = $CalGroupModel->updateOrder($param);
		return json($result);
	}


}


