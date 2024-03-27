<?php
namespace app\admin\controller;

use app\common\model\TaskModel;

/**
 * @title 任务管理
 * @desc 任务管理
 * @use app\admin\controller\TaskController
 */
class TaskController extends AdminBaseController
{
    /**
     * 时间 2022-05-16
     * @title 任务列表
     * @desc 任务列表
     * @author theworld
     * @version v1
     * @url /admin/v1/task
     * @method  GET
     * @param string keywords - 关键字,搜索范围:任务ID,描述
     * @param string status - 状态Wait未开始Exec执行中Finish完成Failed失败
     * @param int start_time - 开始时间
     * @param int end_time - 结束时间
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,description,status,start_time,finish_time
     * @param string sort - 升/降序 asc,desc
     * @return array list - 任务
     * @return int list[].id - 任务ID 
     * @return string list[].description - 描述 
     * @return string list[].status - 状态Wait未开始Exec执行中Finish完成Failed失败 
     * @return string list[].retry - 是否已重试0否1是
     * @return int list[].start_time - 开始时间 
     * @return int list[].finish_time - 完成时间
     * @return int list[].fail_reason - 失败原因
     * @return int count - 任务总数
     */
	public function taskList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $TaskModel = new TaskModel();

        // 获取任务列表
        $data = $TaskModel->taskList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-16
     * @title 任务重试
     * @desc 任务重试
     * @author theworld
     * @version v1
     * @url /admin/v1/task/:id/retry
     * @method  PUT
     * @param int id - 任务ID required
     */
	public function retry()
    {
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $TaskModel = new TaskModel();
        
        // 任务重试
        $result = $TaskModel->retryTask($param['id']);

        return json($result);
	}
}