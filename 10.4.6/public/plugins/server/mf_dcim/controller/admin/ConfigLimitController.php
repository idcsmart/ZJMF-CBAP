<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\ConfigLimitModel;
use server\mf_dcim\validate\ConfigLimitValidate;

/**
 * @title DCIM(自定义配置)-配置限制(废弃)
 * @desc DCIM(自定义配置)-配置限制
 * @use server\mf_dcim\controller\admin\ConfigLimitController
 */
class ConfigLimitController
{
	/**
	 * 时间 2023-02-01
	 * @title 添加配置限制
	 * @desc 添加配置限制
	 * @url /admin/v1/mf_dcim/config_limit
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int data_center_id - 数据中心ID require
     * @param   array model_config_id - 型号配置ID require
     * @param   int line_id - 线路ID
     * @param   string min_bw - 带宽最小值 带宽线路可传
     * @param   string max_bw - 带宽最大值 带宽线路可传
     * @param   string min_flow - 流量最小值 流量线路可传
     * @param   string max_flow - 流量最大值 流量线路可传
     * @return  int id - 配置限制ID
	 */
	public function create()
	{
		$param = request()->param();

		$ConfigLimitValidate = new ConfigLimitValidate();
		if (!$ConfigLimitValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigLimitValidate->getError())]);
        }
		$ConfigLimitModel = new ConfigLimitModel();

		$result = $ConfigLimitModel->configLimitCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 配置限制列表
	 * @desc 配置限制列表
	 * @url /admin/v1/mf_dcim/config_limit
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id)
     * @param   string sort - 升降序
     * @param   int product_id - 商品ID
     * @return  int list[].id - 配置限制ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].line_id - 线路ID
     * @return  string list[].min_bw - 带宽最小值
     * @return  string list[].max_bw - 带宽最大值
     * @return  string list[].min_flow - 流量最小值
     * @return  string list[].max_flow - 流量最大值
     * @return  array list[].model_config_id - 型号配置ID
     * @return  string list[].line_name - 线路名称
     * @return  string list[].bill_type - 线路类型(bw=带宽线路,flow=流量线路)
     * @return  int list[].country_id - 国家ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  string list[].iso - 国家图标
     * @return  string list[].country_name - 国家
     * @return  int list[].model_config[].id - 型号配置ID
     * @return  string list[].model_config[].name - 型号配置名称
     * @return  int count - 总条数
	 */
	public function list()
	{
		$param = request()->param();

		$ConfigLimitModel = new ConfigLimitModel();

		$data = $ConfigLimitModel->configLimitList($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 修改配置限制
	 * @desc 修改配置限制
	 * @url /admin/v1/mf_dcim/config_limit/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 配置限制ID require
     * @param   int data_center_id - 数据中心ID require
     * @param   array model_config_id - 型号配置ID require
     * @param   int line_id - 线路ID
     * @param   string min_bw - 带宽最小值 带宽线路可传
     * @param   string max_bw - 带宽最大值 带宽线路可传
     * @param   string min_flow - 流量最小值 流量线路可传
     * @param   string max_flow - 流量最大值 流量线路可传
	 */
	public function update()
	{
		$param = request()->param();

		$ConfigLimitValidate = new ConfigLimitValidate();
		if (!$ConfigLimitValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigLimitValidate->getError())]);
        }

		$ConfigLimitModel = new ConfigLimitModel();

		$result = $ConfigLimitModel->configLimitUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 删除配置限制
	 * @desc 删除配置限制
	 * @url /admin/v1/mf_dcim/config_limit/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 配置限制ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$ConfigLimitModel = new ConfigLimitModel();

		$result = $ConfigLimitModel->configLimitDelete((int)$param['id']);
		return json($result);
	}

}