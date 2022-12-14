<?php
namespace server\idcsmart_dcim\controller\admin;

use server\idcsmart_dcim\model\PackageModel;
use server\idcsmart_dcim\validate\PackageValidate;

/**
 * @title DCIM套餐管理
 * @desc DCIM套餐管理
 * @use server\idcsmart_dcim\controller\admin\PackageController
 */
class PackageController{

	/**
	 * 时间 2022-06-17
	 * @title 创建套餐
	 * @desc 创建套餐
	 * @url /admin/v1/idcsmart_dcim/package
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   string name - 套餐名称 require
	 * @param   string description - 描述 require
	 * @param   array data_center_id - 数据中心ID require
	 * @param   int dcim_server_group_id - 销售分组ID require
	 * @param   int in_bw - 进带宽 require
	 * @param   int out_bw - 出带宽 require
	 * @param   int ip_num - IP数量 require
	 * @param   int ip_group - IP分组ID
	 * @param   string custom_param - 自定义参数
	 * @param   int traffic_enable - 是否启用流量计费(0=关闭,1=开启)
	 * @param   int flow - 可用流量 开启require
	 * @param   string traffic_bill_type month month=自然月,last_30days=购买日一月
	 * @param   string onetime_fee - 一次性价格
	 * @param   string month_fee - 月价格
	 * @param   string quarter_fee - 季度
	 * @param   string half_year_fee - 半年
	 * @param   string year_fee - 一年
	 * @param   string two_year - 两年
	 * @param   string three_year - 三年
	 * @param   int order - 排序
	 * @return  array data.id - 创建的套餐ID
	 */
	public function create(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->createPackage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 套餐列表
	 * @desc 套餐列表
	 * @url /admin/v1/idcsmart_dcim/package
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,order)
     * @param   string sort - 升降序
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 套餐ID
     * @return  string list[].name - 套餐名称
     * @return  string list[].description - 描述
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].dcim_server_group_id - 销售分组ID
     * @return  int list[].in_bw - 进带宽
     * @return  int list[].out_bw - 出带宽
     * @return  int list[].ip_num - IP数量
     * @return  int list[].ip_group - IP分组
     * @return  string list[].custom_param - 自定义参数
     * @return  int list[].traffic_enable - 是否启用流量计费(0=关闭,1=开启)
     * @return  int list[].flow - 可用流量
     * @return  string list[].traffic_bill_type - 流量计费周期(month=自然月,last_30days=周期)
     * @return  string list[].onetime_fee - 一次性
     * @return  string list[].month_fee - 月
     * @return  string list[].quarter_fee - 季度
     * @return  string list[].half_year_fee - 半年
     * @return  string list[].year_fee - 一年
     * @return  string list[].two_year - 两年
     * @return  string list[].three_year - 三年
     * @return  int list[].order - 排序
     * @return  int list[].create_time - 创建时间
     * @return  int list[].product_id - 商品ID
     * @return  string list[].city - 城市
     * @return  string list[].country_name - 国家
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$result = $PackageModel->packageList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改套餐
	 * @desc 修改套餐
	 * @url /admin/v1/idcsmart_dcim/package/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @param   string name - 套餐名称 require
	 * @param   string description - 描述 require
	 * @param   int data_center_id - 数据中心ID require
	 * @param   int dcim_server_group_id - 销售分组ID require
	 * @param   int in_bw - 进带宽 require
	 * @param   int out_bw - 出带宽 require
	 * @param   int ip_num - IP数量 require
	 * @param   int ip_group - IP分组ID
	 * @param   string custom_param - 自定义参数
	 * @param   int traffic_enable - 是否启用流量计费(0=关闭,1=开启)
	 * @param   int flow - 可用流量 开启require
	 * @param   string traffic_bill_type month month=自然月,last_30days=购买日一月
	 * @param   string onetime_fee - 一次性价格
	 * @param   string month_fee - 月价格
	 * @param   string quarter_fee - 季度
	 * @param   string half_year_fee - 半年
	 * @param   string year_fee - 一年
	 * @param   string two_year - 两年
	 * @param   string three_year - 三年
	 * @param   int order - 排序
	 */
	public function update(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->updatePackage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 删除套餐
	 * @desc 删除套餐
	 * @url /admin/v1/idcsmart_dcim/package/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$result = $PackageModel->deletePackage((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-17
	 * @title 修改套餐排序
	 * @desc 修改套餐排序
	 * @url /admin/v1/idcsmart_dcim/package/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID require
	 * @param   int order - 排序 require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateOrder(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->updateOrder($param);
		return json($result);
	}


}


