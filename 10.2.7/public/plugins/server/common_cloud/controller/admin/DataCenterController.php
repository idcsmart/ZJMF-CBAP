<?php
namespace server\common_cloud\controller\admin;

use server\common_cloud\model\DataCenterModel;
use server\common_cloud\validate\DataCenterValidate;

/**
 * @title 通用云数据中心管理
 * @desc 通用云数据中心管理
 * @use server\common_cloud\controller\admin\DataCenterController
 */
class DataCenterController{

	/**
	 * 时间 2022-06-15
	 * @title 创建数据中心
	 * @desc 创建数据中心
	 * @url /admin/v1/common_cloud/data_center
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int country_id - 国家ID require
     * @param   string city - 城市 require
     * @param   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int cloud_config_id - 魔方云配置关联ID require
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
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->createDataCenter($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-17
	 * @title 数据中心列表
	 * @desc 数据中心列表
	 * @url /admin/v1/common_cloud/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,order)
     * @param   string sort - 升降序
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 数据中心ID
     * @return  int list[].country_id - 国家ID
     * @return  string list[].city - 城市
     * @return  int list[].order - 排序
     * @return  string list[].cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return  int list[].cloud_config_id - 魔方云配置关联ID
     * @return  int list[].create_time - 创建时间
     * @return  int list[].product_id - 商品ID
     * @return  string list[].country_name - 国家名称
     * @return  int count - 总条数
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
	 * @url /admin/v1/common_cloud/data_center/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 数据中心ID require
     * @param   int country_id - 国家 require
     * @param   string city - 城市 require
     * @param   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int config_rel_id - 魔方云配置关联ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
        }        
		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->updateDataCenter($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-15
	 * @title 删除数据中心
	 * @desc 删除数据中心
	 * @url /admin/v1/common_cloud/data_center/:id
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
	 * @url /admin/v1/common_cloud/data_center/:id/order
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


