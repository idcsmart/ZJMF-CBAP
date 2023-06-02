<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\OptionModel;
use server\mf_cloud\validate\CpuValidate;

/**
 * @title 魔方云(自定义配置)-CPU配置
 * @desc 魔方云(自定义配置)-CPU配置
 * @use server\mf_cloud\controller\admin\CpuController
 */
class CpuController{

	/**
	 * 时间 2023-01-31
	 * @title 添加CPU配置
	 * @desc 添加CPU配置
	 * @url /admin/v1/mf_cloud/cpu
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int value - 核心数 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   string other_config.advanced_cpu - 智能CPU配置规则ID
     * @param   string other_config.cpu_limit - CPU限制
     * @return  int id - 通用配置ID
	 */
	public function create(){
		$param = request()->param();

		$CpuValidate = new CpuValidate();
		if (!$CpuValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CpuValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::CPU;

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title CPU配置列表
	 * @desc CPU配置列表
	 * @url /admin/v1/mf_cloud/cpu
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 通用配置ID
     * @return  int list[].value - 核心数
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$param['rel_type'] = OptionModel::CPU;
        $param['rel_id'] = 0;
        $param['orderby'] = 'value';
        $param['sort'] = 'asc';

        $field = 'id,value';
		$OptionModel = new OptionModel();

		$data = $OptionModel->optionList($param, $field);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 修改CPU配置
	 * @desc 修改CPU配置
	 * @url /admin/v1/mf_cloud/cpu/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - CPU配置ID require
     * @param   int value - 核心数 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   string other_config.advanced_cpu - 智能CPU配置规则ID
     * @param   string other_config.cpu_limit - CPU限制
	 */
	public function update(){
		$param = request()->param();

		$CpuValidate = new CpuValidate();
		if (!$CpuValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CpuValidate->getError())]);
        }
		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 删除CPU配置
	 * @desc 删除CPU配置
	 * @url /admin/v1/mf_cloud/cpu/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - CPU配置ID require
	 */
	public function delete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::CPU);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title CPU配置详情
	 * @desc CPU配置详情
	 * @url /admin/v1/mf_cloud/cpu/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 核心数
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.advanced_cpu - 智能CPU配置规则
     * @return  string other_config.cpu_limit - CPU限制
	 */
	public function index(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->cpuIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}



}