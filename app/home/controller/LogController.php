<?php
namespace app\home\controller;

use app\common\model\SystemLogModel;

/**
 * @title 日志管理
 * @desc 日志管理
 * @use app\home\controller\LogController
 */
class LogController extends HomeBaseController
{   
    /**
     * 时间 2022-05-19
     * @title 操作日志
     * @desc 操作日志
     * @author theworld
     * @version v1
     * @url /console/v1/log
     * @method  GET
     * @param string type - 类型system:系统日志api:API日志
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,description,create_time,ip
     * @param string sort - 升/降序 asc,desc
     * @return array list - 操作日志
     * @return int list[].id - 操作日志ID 
     * @return string list[].description - 描述 
     * @return int list[].create_time - 时间 
     * @return string list[].ip - IP 
     * @return int count - 操作日志总数
     */
	public function logList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $SystemLogModel = new SystemLogModel();

        // 获取系统日志列表
        $data = $SystemLogModel->systemLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}
}