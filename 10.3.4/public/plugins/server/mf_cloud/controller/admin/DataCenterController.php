<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\validate\DataCenterValidate;

/**
 * @title 魔方云(自定义配置)-数据中心
 * @desc 魔方云(自定义配置)-数据中心
 * @use server\mf_cloud\controller\admin\DataCenterController
 */
class DataCenterController{

	/**
	 * 时间 2022-06-15
	 * @title 创建数据中心
	 * @desc 创建数据中心
	 * @url /admin/v1/mf_cloud/data_center
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int country_id - 国家ID require
     * @param   string city - 城市 require
     * @param   string area - 区域 require
     * @param   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int cloud_config_id - 魔方云配置关联ID require
     * @param   int order 0 排序
     * @return  int id - 数据中心ID
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
	 * 时间 2023-02-02
	 * @title 数据中心列表
	 * @desc 数据中心列表
	 * @url /admin/v1/mf_cloud/data_center
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  int list[].id - 数据中心ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  int list[].country_id - 国家ID
     * @return  string list[].cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID)
     * @return  int list[].cloud_config_id - 魔方云配置关联ID
     * @return  int list[].order - 排序
     * @return  string list[].country_name - 国家
     * @return  int list[].line[].id - 线路ID
     * @return  int list[].line[].data_center_id - 数据中心ID
     * @return  string list[].line[].name - 线路名称
     * @return  string list[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string list[].line[].price - 价格
     * @return  string list[].line[].duration - 周期
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
	 * @url /admin/v1/mf_cloud/data_center/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 数据中心ID require
     * @param   int country_id - 国家 require
     * @param   string city - 城市 require
     * @param   string area - 区域 require
     * @param   string cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int cloud_config_id - 魔方云配置关联ID require
     * @param   int order - 排序
	 */
	public function update(){
		$param = request()->param();

		$DataCenterValidate = new DataCenterValidate();
		if (!$DataCenterValidate->scene('update')->check($param)){
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
	 * @url /admin/v1/mf_cloud/data_center/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 数据中心ID require
	 */
	public function delete(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->deleteDataCenter((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 数据中心选择
	 * @desc 数据中心选择
	 * @url /admin/v1/mf_cloud/data_center/select
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 国家ID
     * @return  string list[].iso - 国家图标
     * @return  string list[].name - 国家名称
     * @return  string list[].city[].name - 城市名称
     * @return  int list[].area[].id - 数据中心ID
     * @return  string list[].area[].name - 区域名称
     * @return  int list[].area[].line[].id - 线路ID
     * @return  string list[].area[].line[].name - 线路名称
     * @return  string list[].area[].line[].bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int list[].area[].line[].defence_enable - 是否启用防护(0=未启用,1=启用)
     * @return  int count - 总条数
	 */
	public function dataCenterSelect(){
		$param = request()->param();

		$DataCenterModel = new DataCenterModel();

		$result = $DataCenterModel->dataCenterSelect($param);
		return json($result);
	}








	/**
	 * 时间 2022-06-17
	 * @title 修改数据中心排序
	 * @desc 修改数据中心排序
	 * @url /admin/v1/mf_cloud/data_center/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   int param.order - 排序 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	// public function updateOrder(){
	// 	$param = request()->param();

	// 	$DataCenterValidate = new DataCenterValidate();
	// 	if (!$DataCenterValidate->scene('order')->check($param)){
 //            return json(['status' => 400 , 'msg' => lang_plugins($DataCenterValidate->getError())]);
 //        }
	// 	$DataCenterModel = new DataCenterModel();

	// 	$result = $DataCenterModel->updateOrder($param);
	// 	return json($result);
	// }





}


