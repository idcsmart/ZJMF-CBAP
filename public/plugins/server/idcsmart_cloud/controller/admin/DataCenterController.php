<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\DataCenterModel;
use server\idcsmart_cloud\validate\DataCenterValidate;
use server\idcsmart_cloud\validate\DataCenterServerLinkValidate;

/**
 * @title 数据中心管理
 * @desc 数据中心管理
 * @use server\idcsmart_cloud\controller\admin\DataCenterController
 */
class DataCenterController{

	/**
	 * 时间 2022-06-15
	 * @title 创建数据中心
	 * @desc 创建数据中心
	 * @url /admin/v1/idcsmart_cloud/data_center
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string country - 国家 require
     * @param   string country_code - 国家代码 require
     * @param   string city - 城市 require
     * @param   string area - 区域 require
     * @param   array server - 接口和接口参数的数组 require
     * @param   int server[].server_id - 接口ID require
     * @param   string server[].server_param - 接口参数 require
     * @param   int order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 数据中心ID
	 */
	public function create(){
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }
        $DataCenterServerLinkValidate = new DataCenterServerLinkValidate();
        foreach($param['server'] as $v){
        	if (!$DataCenterServerLinkValidate->check($v)){
	            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterServerLinkValidate->getError())]);
	        }
        }
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->createDataCenter($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-17
	 * @title 数据中心列表
	 * @desc 数据中心列表
	 * @url /admin/v1/idcsmart_cloud/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,country,order)
     * @param   string sort - 升降序
     * @param   int product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 数据中心ID
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  int data.list[].order - 排序
     * @return  int data.list[].server[].server_id - 接口ID
     * @return  string data.list[].server[].server_param - 接口参数
     * @return  string data.list[].server[].server_name - 接口名称
     * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->dataCenterList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 修改数据中心
	 * @desc 修改数据中心
	 * @url /admin/v1/idcsmart_cloud/data_center/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   string param.country - 国家 required
     * @param   string param.country_code - 国家代码 required
     * @param   string param.city - 城市 required
     * @param   string param.area - 区域 required
     * @param   array param.server - 接口和接口参数的数组 required
     * @param   int param.server[].server_id - 接口ID required
     * @param   string param.server[].server_param - 接口参数 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }
        $DataCenterServerLinkValidate = new DataCenterServerLinkValidate();
        foreach($param['server'] as $v){
        	if (!$DataCenterServerLinkValidate->check($v)){
	            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterServerLinkValidate->getError())]);
	        }
        }
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->updateDataCenter($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 删除数据中心
	 * @desc 删除数据中心
	 * @url /admin/v1/idcsmart_cloud/data_center/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 数据中心ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->deleteDataCenter((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-17
	 * @title 修改数据中心排序
	 * @desc 修改数据中心排序
	 * @url /admin/v1/idcsmart_cloud/data_center/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   int param.order - 排序 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	public function updateOrder(){
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->updateOrder($param);
		return json($result);
	}





}


