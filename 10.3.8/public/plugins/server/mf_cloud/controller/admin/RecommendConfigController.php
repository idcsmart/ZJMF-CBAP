<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\validate\RecommendConfigValidate;

/**
 * @title 魔方云(自定义配置)-套餐配置
 * @desc 魔方云(自定义配置)-套餐配置
 * @use server\mf_cloud\controller\admin\RecommendConfigController
 */
class RecommendConfigController{

	/**
	 * 时间 2023-02-03
	 * @title 添加套餐配置
	 * @desc 添加套餐配置
	 * @url /admin/v1/mf_cloud/recommend_config
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   string name - 名称 require
     * @param   string description - 描述 require
     * @param   int order - 排序ID require
     * @param   int data_center_id - 数据中心ID require
     * @param   int line_id - 线路ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小 require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - IP数量 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   int gpu_num 0 GPU数量
     * @return  int id - 推荐配置ID
	 */
	public function create(){
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->recommendConfigCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 套餐配置列表
	 * @desc 套餐配置列表
	 * @url /admin/v1/mf_cloud/recommend_config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,order)
     * @param   string sort - 升降序(asc,desc)
     * @param   int product_id - 商品ID
     * @param   int data_center_id - 数据中心ID
     * @return  array list - 列表数据
     * @return  int list[].id - 套餐配置ID
     * @return  string list[].name - 名称
     * @return  string list[].description - 描述
     * @return  int list[].order - 排序
     * @return  int list[].product_id - 商品ID
     * @return  int list[].data_center_id - 数据中心
     * @return  int list[].upgrade_range - 升降级范围(0=不可升降级,1=全部,2=自选)
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  array list[].rel_id - 升降级范围自选套餐ID
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$data = $RecommendConfigModel->recommendConfigList($param);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 修改套餐配置
	 * @desc 修改套餐配置
	 * @url /admin/v1/mf_cloud/recommend_config/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 推荐配置ID require
     * @param   string name - 名称 require
     * @param   string description - 描述 require
     * @param   int order - 排序ID require
     * @param   int data_center_id - 数据中心ID require
     * @param   int line_id - 线路ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小 require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - IP数量 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @param   int gpu_num 0 GPU数量
	 */
	public function update(){
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }        
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->recommendConfigUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 删除套餐配置
	 * @desc 删除套餐配置
	 * @url /admin/v1/mf_cloud/recommend_config/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 推荐配置ID require
	 */
	public function delete(){
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->recommendConfigDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-10-24
	 * @title 套餐配置详情
	 * @desc 套餐配置详情
	 * @url /admin/v1/mf_cloud/recommend_config/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 套餐ID require
     * @return  int id - 套餐ID
     * @return  int product_id - 商品ID
     * @return  string name - 名称
     * @return  string description - 描述
     * @return  int order - 排序ID
     * @return  int data_center_id - 数据中心ID
     * @return  int line_id - 线路ID
     * @return  int cpu - 核心数
     * @return  int memory - 内存大小
     * @return  int system_disk_size - 系统盘大小
     * @return  string system_disk_type - 系统盘类型
     * @return  int data_disk_size - 数据盘大小
     * @return  string data_disk_type - 数据盘类型
     * @return  int bw - 带宽
     * @return  int flow - 流量
     * @return  int peak_defence - 防御峰值
     * @return  int ip_num - IP数量
     * @return  int gpu_num - GPU数量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 名称
     * @return  string duration[].price - 价格
	 */
	public function index(){
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$data = $RecommendConfigModel->recommendConfigIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-10-24
	 * @title 保存套餐升降级范围
	 * @desc 保存套餐升降级范围
	 * @url /admin/v1/mf_cloud/recommend_config/upgrade_range
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   object recommend_config - 升降级范围(如{"5":{"upgrade_range":0, "rel_id": []}},5是套餐ID,upgrade_range:0=不可升降级,1=所有套餐,2=自选套餐,2的时候需要传入rel_id是所选套餐ID) require
	 */
	public function saveUpgradeRange(){
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->saveUpgradeRange($param);
		return json($result);
	}

	/**
	 * 时间 2023-10-26
	 * @title 切换订购是否显示
	 * @desc 切换订购是否显示
	 * @url admin/v1/mf_cloud/recommend_config/:id/hidden
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int hidden - 状态(0=显示,1=隐藏)
	 */
	public function updateHidden(){
		$param = request()->param();

		$RecommendConfigValidate = new RecommendConfigValidate();
		if (!$RecommendConfigValidate->scene('update_hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($RecommendConfigValidate->getError())]);
        }        
		$RecommendConfigModel = new RecommendConfigModel();

		$result = $RecommendConfigModel->updateHidden($param);
		return json($result);
	}


}