<?php
namespace app\common\model;

use think\db\Query;
use think\Model;
use think\facade\Db;

/**
 * @title 任务模型
 * @desc 任务模型
 * @use app\common\model\TaskModel
 */
class TaskModel extends Model
{
	protected $name = 'task';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'rel_id'        => 'int',
        'status'        => 'string',
        'retry'         => 'int',
        'description'   => 'string',
        'task_data'     => 'string',
        'start_time'    => 'int',
        'finish_time'   => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'fail_reason'   => 'string',
    ];

	/**
     * 时间 2022-05-16
     * @title 任务列表
     * @desc 任务列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:任务ID,描述
     * @param string param.status - 状态Wait未开始Exec执行中Finish完成Failed失败
     * @param int param.start_time - 开始时间
     * @param int param.end_time - 结束时间
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,description,status,start_time,finish_time
     * @param string param.sort - 升/降序 asc,desc
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
    public function taskList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'description', 'status', 'start_time', 'finish_time']) ? $param['orderby'] : 'id';
        $param['status'] = $param['status'] ?? '';
        $param['start_time'] = intval($param['start_time'] ?? 0);
        $param['end_time'] = intval($param['end_time'] ?? 0);

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('id|description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['status'])){
                $query->where('status', $param['status']);
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('start_time', '>=', $param['start_time'])->where('start_time', '<=', $param['end_time']);
            } 
        };

    	$count = $this->field('id')
    		->where($where)
		    ->count();
    	$tasks = $this->field('id,description,status,retry,start_time,finish_time,fail_reason')
    		->where($where)
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	return ['list' => $tasks, 'count' => $count];
    }

    /**
     * 时间 2022-05-16
     * @title 任务重试
     * @desc 任务重试
     * @author theworld
     * @version v1
     * @param int id - 任务ID required
     */
    public function retryTask($id)
    {
        // 验证任务ID
    	$task = $this->find($id);
        if (empty($task)){
            return ['status'=>400, 'msg'=>lang('task_is_not_exist')];
        }
        // 任务已重试不能再次发起重试
        if($task['retry']==1){
        	return ['status'=>400, 'msg'=>lang('task_has_been_retried')];
        }
        // 失败的任务才能发起重试
        if($task['status']!='Failed'){
        	return ['status'=>400, 'msg'=>lang('only_failed_task_can_retry')];
        }
        $this->startTrans();
		try {
			$time = time();
            // 标记任务为已重试
			$this->update([
				'retry' => 1,
                'update_time' => $time
            ], ['id' => $id]);
            // 创建重试任务
			$wait=[
	    		'type' => $task['type'],
	    		'rel_id' => $task['rel_id'],
	    		'status' => 'Wait',
	    		'retry' => 0,
	    		'description' => $task['description'],
	    		'task_data' => $task['task_data'],
	    		'start_time' => $time,
	    		'finish_time' => 0,
                'create_time' => $time,
                'update_time' => $time
	    	];
	    	$task = $this->create($wait);
			$wait['task_id']=$task->id;
			Db::name('task_wait')->insert($wait);
            # 记录日志
            active_log(lang('admin_retry_task', ['{admin}'=>request()->admin_name, '{task}'=>'#'.$task->id, '{description}'=>$task->description]), 'task', $task->id);

	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('fail_message')];
		}

		hook('after_task_retry',['id'=>$id]);

    	return ['status' => 200, 'msg' => lang('success_message')];
    }
    // 添加到任务队列(后台读取的就是这里添加的数据，执行任务队列是TaskWaitModel添加的数据)
    public function createTask($param)
    {
        $this->startTrans();
		try {
			$time = time();
            // 创建
	    	$task_id=$this->create([
	    		'type' => $param['type'],
	    		'rel_id' => $param['rel_id'],
	    		'status' => 'Exec',
	    		'retry' => 0,
	    		'description' => $param['description'],
	    		'task_data' => $param['task_data'],
	    		'start_time' => $time,
	    		'finish_time' => 0,
                'create_time' => $time,
				'update_time' => $time
	    	]);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('fail_message')];
		}
    	return ['status' => 200, 'msg' => lang('success_message'), 'task_id' => $task_id->id];
    }

    
}
