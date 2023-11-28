<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\PackageModel;
use server\mf_dcim\validate\PackageValidate;

/**
 * @title DCIM(自定义配置)-灵活机型
 * @desc DCIM(自定义配置)-灵活机型
 * @use server\mf_dcim\controller\admin\PackageController
 */
class PackageController{

	/**
	 * 时间 2023-11-09
	 * @title 创建机型规格
	 * @desc 创建机型规格
	 * @url /admin/v1/mf_dcim/package
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 型号名称 require
     * @param   int group_id - 分组ID require
     * @param   int cpu_option_id - 处理器配置ID require
     * @param   int cpu_num - 处理器数量 require
     * @param   int mem_option_id - 内存配置ID require
     * @param   int mem_num - 内存数量 require
     * @param   int disk_option_id - 磁盘配置ID require
     * @param   int disk_num - 磁盘数量 require
     * @param   int bw - 带宽 require
     * @param   int ip_num - IP数量 require
     * @param   string description - 简单描述
     * @param   array optional_memory_id - 可选配内存ID
     * @param   int mem_max 0 最高容量
     * @param   int mem_max_num 0 最大槽位
     * @param   array optional_disk_id - 可选配硬盘ID
     * @param   int disk_max_num 0 硬盘最大数量
     * @param   int order 0 排序
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 灵活机型ID
	 */
	public function create(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }
		$PackageModel = new PackageModel();

		$result = $PackageModel->packageCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-09
	 * @title 灵活机型列表
	 * @desc 灵活机型列表
	 * @url /admin/v1/mf_dcim/package
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @return  int list[].id - 灵活机型ID
     * @return  string list[].name - 型号名称
     * @return  string list[].description - 简单描述
     * @return  string list[].cpu - 处理器
     * @return  string list[].memory - 内存
     * @return  string list[].disk - 硬盘
     * @return  int list[].bw - 带宽
     * @return  int list[].ip_num - IP数量
     * @return  int list[].order - 排序
     * @return  int list[].hidden - 是否隐藏(0=否,1=是)
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$data = $PackageModel->packageList($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-09
	 * @title 灵活机型详情
	 * @desc 灵活机型详情
	 * @url /admin/v1/mf_dcim/package/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 灵活机型ID require
     * @return  int id - 灵活机型ID
     * @return  int product_id - 商品ID
     * @return  int group_id - 分组ID
     * @return  int cpu_option_id - 处理器配置ID
     * @return  int cpu_num - 处理器数量
     * @return  int mem_option_id - 内存配置ID
     * @return  int mem_num - 内存数量
     * @return  int disk_option_id - 硬盘配置ID
     * @return  int disk_num - 硬盘数量
     * @return  int bw - 带宽
     * @return  int ip_num - IP数量
     * @return  string description - 简单描述
     * @return  int mem_max - 最高容量
     * @return  int mem_max_num - 最大槽位
     * @return  int disk_max_num - 最大数量
     * @return  int order - 排序
     * @return  int hidden - 是否隐藏(0=否,1=是)
     * @return  int create_time - 创建时间
     * @return  int update_time - 修改时间
     * @return  array optional_memory_id - 可选配内存ID
     * @return  array optional_disk_id - 可选配硬盘ID
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 周期价格
	 */
	public function index(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$data = $PackageModel->packageIndex($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-09
	 * @title 修改机型规格
	 * @desc 修改机型规格
	 * @url /admin/v1/mf_dcim/package/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 灵活机型ID require
     * @param   string name - 型号名称 require
     * @param   int group_id - 分组ID require
     * @param   int cpu_option_id - 处理器配置ID require
     * @param   int cpu_num - 处理器数量 require
     * @param   int mem_option_id - 内存配置ID require
     * @param   int mem_num - 内存数量 require
     * @param   int disk_option_id - 磁盘配置ID require
     * @param   int disk_num - 磁盘数量 require
     * @param   int bw - 带宽 require
     * @param   int ip_num - IP数量 require
     * @param   string description - 简单描述
     * @param   array optional_memory_id - 可选配内存ID
     * @param   int mem_max 0 最高容量
     * @param   int mem_max_num 0 最大槽位
     * @param   int order 0 排序
     * @param   array optional_disk_id - 可选配硬盘ID
     * @param   int disk_max_num 0 硬盘最大数量
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 灵活机型ID
	 */
	public function update(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }        
		$PackageModel = new PackageModel();

		$result = $PackageModel->packageUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-09
	 * @title 删除灵活机型
	 * @desc 删除灵活机型
	 * @url /admin/v1/mf_dcim/package/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
     * @param   int id - 灵活机型ID require
	 */
	public function delete(){
		$param = request()->param();

		$PackageModel = new PackageModel();

		$result = $PackageModel->packageDelete($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-23
	 * @title 切换订购是否显示
	 * @desc 切换订购是否显示
	 * @url admin/v1/mf_dcim/package/:id/hidden
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int hidden - 状态(0=显示,1=隐藏)
	 */
	public function updateHidden(){
		$param = request()->param();

		$PackageValidate = new PackageValidate();
		if (!$PackageValidate->scene('update_hidden')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($PackageValidate->getError())]);
        }        
		$PackageModel = new PackageModel();

		$result = $PackageModel->updateHidden($param);
		return json($result);
	}

}