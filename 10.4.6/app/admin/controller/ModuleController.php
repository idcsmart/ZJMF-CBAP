<?php
namespace app\admin\controller;

use app\common\logic\ModuleLogic;

/**
 * @title 模块管理
 * @desc 模块管理
 * @use app\admin\controller\ModuleController
 */
class ModuleController extends AdminBaseController
{
	/**
	 * 时间 2022-05-27
	 * @title 模块列表
	 * @desc 模块列表
	 * @url /admin/v1/module
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @return array list - 模块列表
     * @return string list[].name - 模块类型
     * @return string list[].display_name - 模块名称
     * @return string list[].version - 版本号
	 */
	public function moduleList()
	{
		$ModuleLogic = new ModuleLogic();

        $data = $ModuleLogic->getModuleList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
            	'list' => $data
            ]
        ];
        return json($result);
	}

} 


