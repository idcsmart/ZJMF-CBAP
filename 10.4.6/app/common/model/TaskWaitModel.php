<?php
namespace app\common\model;

use think\Exception;
use think\Model;
use think\Db;
use app\common\model\TaskModel;
/**
 * @title 添加任务队列模型
 * @desc 添加任务队列模型
 * @use app\common\model\TaskWaitModel
 */
class TaskWaitModel extends Model
{
	protected $name = 'task_wait';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'rel_id'        => 'int',
        'task_id'        => 'int',
        'status'        => 'string',
        'retry'         => 'int',
        'description'   => 'string',
        'task_data'     => 'string',
        'start_time'    => 'int',
        'finish_time'   => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'lock'   => 'int',
    ];
    /**
     * 时间 2022-05-19
     * @title 添加到任务队列
     * @desc 添加到任务队列
     * @author xiong
     * @version v1
	 * @param string param.type - 名称,sms短信发送,email邮件发送,host_create开通主机,host_suspend暂停主机,host_unsuspend解除暂停主机,host_terminate删除主机,执行在插件中的任务 required
	 * @param int param.rel_id - 相关id 
	 * @param int param.client_id - 客户ID(用于判断是否发送)
	 * @param string param.description - 描述 required
	 * @param array param.task_data - 任务要执行的数据 required
     */
    public function createTaskWait($param)
    {
		try {
			hook('before_task_create', $param);
			// wyh 20240606 新增
            $data = [
                'type' => $param['type'],
                'client_id' => $param['client_id']??0
            ];
            if (!client_notice($data)){
                throw new Exception(lang('fail_message'));
            }
			$time = time();
			if($param['type']=='email' || $param['type']=='sms'){
				$ip_port=[
					'ip' => request()->ip(),
					'port' => request()->remotePort()
				];
				$param['task_data']=array_merge($param['task_data'],$ip_port);
			}
			$wait=[
	    		'type' => $param['type'],
	    		'rel_id' => empty($param['rel_id'])?0:$param['rel_id'],
	    		'status' => 'Wait',
	    		'retry' => 0,
	    		'description' => $param['description'],
	    		'task_data' => json_encode($param['task_data']),
	    		'start_time' => $time,
	    		'finish_time' => 0,
                'create_time' => $time,
                'update_time' => $time
	    	];
			$result=(new TaskModel())->createTask($wait);
			if($result['status']==200){
				$wait['task_id']=$result['task_id'];
			}
            // 创建
	    	$this->create($wait);
			
		} catch (\Exception $e) {
		    return ['status' => 400, 'msg' => lang('fail_message')];
		}

        hook('after_task_create', $param);

    	return ['status' => 200, 'msg' => lang('success_message')];
    }
}
