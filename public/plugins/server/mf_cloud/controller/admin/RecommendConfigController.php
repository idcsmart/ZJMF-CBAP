<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\RecommendConfigModel;
use server\mf_cloud\validate\RecommendConfigValidate;

/**
 * @title 魔方云(自定义配置)-推荐配置
 * @desc 魔方云(自定义配置)-推荐配置
 * @use server\mf_cloud\controller\admin\RecommendConfigController
 */
class RecommendConfigController{

	/**
	 * 时间 2023-02-03
	 * @title 添加推荐配置
	 * @desc 添加推荐配置
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
     * @param   string network_type - 网络类型(normal=经典网络,vpc=VPC网络) require
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
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
	 * @title 推荐配置列表
	 * @desc 推荐配置列表
	 * @url /admin/v1/mf_cloud/recommend_config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,order)
     * @param   string sort - 升降序(asc,desc)
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 推荐配置ID
     * @return  int list[].product_id - 商品ID
     * @return  string list[].name - 名称
     * @return  string list[].description - 描述
     * @return  string list[].order - 排序ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].line_id - 线路ID
     * @return  int list[].cpu - CPU
     * @return  int list[].memory - 内存
     * @return  int list[].system_disk_size - 系统盘
     * @return  string list[].system_disk_type - 系统盘类型
     * @return  int list[].data_disk_size - 数据盘
     * @return  string list[].data_disk_type - 数据盘类型
     * @return  string list[].network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @return  int list[].bw - 带宽
     * @return  int list[].flow - 带宽
     * @return  int list[].peak_defence - 防护峰值
     * @return  int list[].create_time - 创建时间
     * @return  int list[].country_id - 国家ID
     * @return  string list[].city - 城市
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$RecommendConfigModel = new RecommendConfigModel();

		$data = $RecommendConfigModel->recommendConfigList($param);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-03
	 * @title 修改推荐配置
	 * @desc 修改推荐配置
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
     * @param   string network_type - 网络类型(normal=经典网络,vpc=VPC网络) require
     * @param   int bw - 带宽
     * @param   int flow - 流量
     * @param   int peak_defence - 防御峰值
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
	 * @title 删除推荐配置
	 * @desc 删除推荐配置
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



}