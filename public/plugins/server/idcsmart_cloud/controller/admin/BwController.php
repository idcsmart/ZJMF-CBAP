<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\BwModel;
use server\idcsmart_cloud\validate\BwValidate;

/**
 * @title 带宽管理
 * @desc 带宽管理
 * @use server\idcsmart_cloud\controller\admin\BwController
 */
class BwController{

	/**
	 * 时间 2022-06-16
	 * @title 创建带宽
	 * @desc 创建带宽
	 * @url /admin/v1/idcsmart_cloud/bw
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   int module_idcsmart_cloud_bw_type_id - 带宽类型ID require
	 * @param   array data_center_id - 数据中心ID require
	 * @param   int bw - 带宽 require
	 * @param   int flow - 流量 require
	 * @param   float price - 价格 require
	 * @param   string description - 描述
	 * @param   string flow_type - 流量统计方向(in=进,out=出,all=进+出) reuqire
	 * @param   int in_bw_enable - 是否启用独立进带宽(0=不是,1=是) require
	 * @param   int in_bw - 进带宽 in_bw_enable=1,require
	 * @return  int data.id - 创建的带宽ID
	 */
	public function create(){
		$param = request()->param();

		$BwValidate = new BwValidate();
		if (!$BwValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BwValidate->getError())]);
        }
		$BwModel = new BwModel();

		$result = $BwModel->createBw($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 带宽列表
	 * @desc 带宽列表
	 * @url /admin/v1/idcsmart_cloud/bw
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,bw,flow,price)
     * @param   string sort - 升降序(asc=升序,desc=降序)
     * @param   int product_id - 商品ID
     * @param   array data_center_id - 搜索数据中心
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 带宽ID
     * @return  int data.list[].module_idcsmart_cloud_bw_type_id - 带宽类型ID
     * @return  int data.list[].bw - 带宽
     * @return  int data.list[].flow - 流量
     * @return  string data.list[].price - 价格
     * @return  string data.list[].description - 描述
     * @return  string data.list[].bw_type_name - 带宽类型名称
     * @return  int data.list[].data_center[].id - 数据中心ID
     * @return  string data.list[].data_center[].country - 国家
     * @return  string data.list[].data_center[].city - 城市
     * @return  string data.list[].data_center[].area - 区域
     * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$BwModel = new BwModel();

		$result = $BwModel->bwList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 修改带宽
	 * @desc 修改带宽
	 * @url /admin/v1/idcsmart_cloud/bw/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽ID require
	 * @param   int module_idcsmart_cloud_bw_type_id - 带宽类型ID require
	 * @param   array data_center_id - 数据中心ID require
	 * @param   int bw - 带宽 require
	 * @param   int flow - 流量 require
	 * @param   float price - 价格 require
	 * @param   string description - 描述
	 * @param   string param.flow_type - 流量统计方向(in=进,out=出,all=进+出) reuqire
	 * @param   int param.in_bw_enable - 是否启用独立进带宽(0=不是,1=是) require
	 * @param   int param.in_bw - 进带宽 in_bw_enable=1,require
	 */
	public function update(){
		$param = request()->param();

		$BwValidate = new BwValidate();
		if (!$BwValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BwValidate->getError())]);
        }
		$BwModel = new BwModel();

		$result = $BwModel->updateBw($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 删除带宽
	 * @desc 删除带宽
	 * @url /admin/v1/idcsmart_cloud/bw/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽ID require
	 */
	public function delete(){
		$param = request()->param();
		
		$BwModel = new BwModel();

		$result = $BwModel->deleteBw((int)$param['id']);
		return json($result);
	}




}


