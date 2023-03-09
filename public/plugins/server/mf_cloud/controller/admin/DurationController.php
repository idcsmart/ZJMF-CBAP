<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\DurationModel;
use server\mf_cloud\validate\DurationValidate;

/**
 * @title 魔方云(自定义配置)-周期
 * @desc 魔方云(自定义配置)-周期
 * @use server\mf_cloud\controller\admin\DurationController
 */
class DurationController{

	/**
	 * 时间 2023-01-31
	 * @title 添加周期
	 * @desc 添加周期
	 * @url /admin/v1/mf_cloud/duration
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 周期名称 require
     * @param   int num - 周期时长 require
     * @param   string unit - 单位(hour=小时,day=天,month=月) require
     * @return  int id - 添加成功的周期ID
	 */
	public function create(){
		$param = request()->param();

		$DurationValidate = new DurationValidate();
		if (!$DurationValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DurationValidate->getError())]);
        }
		$DurationModel = new DurationModel();

		$result = $DurationModel->durationCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 周期列表
	 * @desc 周期列表
	 * @url /admin/v1/mf_cloud/duration
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,num)
     * @param   string sort - 升降序
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 周期ID
     * @return  string list[].name - 周期名称
     * @return  int list[].num - 周期时长
     * @return  string list[].unit - 单位(hour=小时,day=天,month=月)
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$DurationModel = new DurationModel();

		$result = $DurationModel->durationList($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 修改周期
	 * @desc 修改周期
	 * @url /admin/v1/mf_cloud/duration/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 数据中心ID require
     * @param   string name - 周期名称 require
     * @param   int num - 周期时长 require
     * @param   string unit - 单位(hour=小时,day=天,month=月) require
	 */
	public function update(){
		$param = request()->param();

		$DurationValidate = new DurationValidate();
		if (!$DurationValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DurationValidate->getError())]);
        }        
		$DurationModel = new DurationModel();

		$result = $DurationModel->durationUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 删除周期
	 * @desc 删除周期
	 * @url /admin/v1/mf_cloud/duration/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 周期ID require
	 */
	public function delete(){
		$param = request()->param();

		$DurationModel = new DurationModel();

		$result = $DurationModel->durationDelete($param);
		return json($result);
	}

}