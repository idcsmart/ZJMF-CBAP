<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\ModelConfigModel;
use server\mf_dcim\validate\ModelConfigValidate;

/**
 * @title DCIM(自定义配置)-型号配置
 * @desc DCIM(自定义配置)-型号配置
 * @use server\mf_dcim\controller\admin\ModelConfigController
 */
class ModelConfigController{

	/**
	 * 时间 2023-01-31
	 * @title 添加型号配置
	 * @desc 添加型号配置
	 * @url /admin/v1/mf_dcim/model_config
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 配置名称 require
     * @param   int group_id - 分组ID require
     * @param   string cpu - 处理器 require
     * @param   string cpu_param - 处理器参数 require
     * @param   string memory - 内存 require
     * @param   string disk - 硬盘 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @return  int id - 型号配置ID
	 */
	public function create(){
		$param = request()->param();

		$ModelConfigValidate = new ModelConfigValidate();
		if (!$ModelConfigValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ModelConfigValidate->getError())]);
        }
		$ModelConfigModel = new ModelConfigModel();

		$result = $ModelConfigModel->modelConfigCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 型号配置列表
	 * @desc 型号配置列表
	 * @url /admin/v1/mf_dcim/model_config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 型号配置ID
     * @return  string list[].name - 配置名称
     * @return  int list[].group_id - 分组ID
     * @return  string list[].cpu - 处理器
     * @return  string list[].cpu_param - 处理器参数
     * @return  string list[].memory - 内存
     * @return  string list[].disk - 硬盘
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$ModelConfigModel = new ModelConfigModel();

		$data = $ModelConfigModel->modelConfigList($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 修改型号配置
	 * @desc 修改型号配置
	 * @url /admin/v1/mf_dcim/model_config/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @param   string name - 配置名称 require
     * @param   int group_id - 分组ID require
     * @param   string cpu - 处理器 require
     * @param   string cpu_param - 处理器参数 require
     * @param   string memory - 内存 require
     * @param   string disk - 硬盘 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
	 */
	public function update(){
		$param = request()->param();

		$ModelConfigValidate = new ModelConfigValidate();
		if (!$ModelConfigValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ModelConfigValidate->getError())]);
        }        
		$ModelConfigModel = new ModelConfigModel();

		$result = $ModelConfigModel->modelConfigUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 删除型号配置
	 * @desc 删除型号配置
	 * @url /admin/v1/mf_dcim/model_config/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 型号配置ID require
	 */
	public function delete(){
		$param = request()->param();

		$ModelConfigModel = new ModelConfigModel();

		$result = $ModelConfigModel->modelConfigDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 型号配置详情
	 * @desc 型号配置详情
	 * @url /admin/v1/mf_dcim/model_config/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 型号配置ID require
     * @return  int id - 型号配置ID
     * @return   string name - 配置名称
     * @return   int group_id - 分组ID
     * @return   string cpu - 处理器
     * @return   string cpu_param - 处理器参数
     * @return   string memory - 内存
     * @return   string disk - 硬盘
     * @return   int duration[].id - 周期ID
     * @return   string duration[].name - 周期名称
     * @return   string duration[].price - 周期价格
	 */
	public function index(){
		$param = request()->param();

		$ModelConfigModel = new ModelConfigModel();

		$data = $ModelConfigModel->modelConfigIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}



}