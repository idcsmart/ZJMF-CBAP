<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\CalModel;
use server\idcsmart_cloud\validate\CalValidate;

/**
 * @title 计算型号管理
 * @desc 计算型号管理
 * @use server\idcsmart_cloud\controller\admin\CalController
 */
class CalController{

	/**
	 * 时间 2022-06-15
	 * @title 创建计算型号
	 * @desc 创建计算型号
	 * @url /admin/v1/idcsmart_cloud/cal
	 * @author hh
	 * @version v1
     * @param   string name - 显示名称 required
     * @param   int module_idcsmart_cloud_cal_group_id - 分组ID required
     * @param   int cpu - CPU required
     * @param   int memory - 内存(MB) required
     * @param   int disk_size - 硬盘(GB) required
     * @param   int price - 单价 required
     * @param   string description - 描述
     * @param   string other_param - 其他参数
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 计算型号ID
	 */
	public function create(){
		$param = request()->param();

		$CalValidate = new CalValidate();
		if (!$CalValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CalValidate->getError())]);
        }
		$CalModel = new CalModel();

		$result = $CalModel->createCal($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 计算型号列表
	 * @desc 计算型号列表
	 * @url /admin/v1/idcsmart_cloud/cal
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,name,cpu,memory,disk_size,order,price)
     * @param   string sort - 升降序(asc=升序,desc=降序)
     * @param   int module_idcsmart_cloud_cal_group_id - 搜索计算型号分组ID
     * @param   int param.product_id - 搜索商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 计算型号ID
     * @return  string data.list[].name - 名称
     * @return  int data.list[].module_idcsmart_cloud_cal_group_id - 计算型号分组ID
     * @return  int data.list[].cpu - CPU
     * @return  int data.list[].memory - 内存(MB)
     * @return  int data.list[].disk_size - 硬盘(GB)
     * @return  int data.list[].order - 排序
     * @return  string data.list[].other_param - 其他参数
     * @return  string data.list[].description - 描述
     * @return  string data.list[].price - 价格
     * @return  int data.list[].create_time - 创建时间
     * @return  int data.list[].update_time - 修改时间
     * @return  string data.list[].group_name - 计算型号分组名称
     * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$CalModel = new CalModel();

		$result = $CalModel->calList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 修改计算型号
	 * @desc 修改计算型号
	 * @url /admin/v1/idcsmart_cloud/cal/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 计算型号ID require
     * @param   string name - 显示名称 require
     * @param   int module_idcsmart_cloud_cal_group_id - 分组ID require
     * @param   int cpu - CPU require
     * @param   int memory - 内存(MB) require
     * @param   int disk_size - 硬盘(GB) require
     * @param   int price - 单价 require
     * @param   string description - 描述
     * @param   string other_param - 其他参数
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$CalValidate = new CalValidate();
		if (!$CalValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CalValidate->getError())]);
        }
		$CalModel = new CalModel();

		$result = $CalModel->updateCal($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 删除计算型号
	 * @desc 删除计算型号
	 * @url /admin/v1/idcsmart_cloud/cal/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 计算型号ID required
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$CalModel = new CalModel();

		$result = $CalModel->deleteCal((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 修改计算型号排序
	 * @desc 修改计算型号排序
	 * @url /admin/v1/idcsmart_cloud/cal/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 计算型号ID required
     * @param   int order - 排序 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	public function updateOrder(){
		$param = request()->param();
		
		$CalValidate = new CalValidate();
		if (!$CalValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CalValidate->getError())]);
        }
		$CalModel = new CalModel();

		$result = $CalModel->updateOrder($param);
		return json($result);
	}


}


