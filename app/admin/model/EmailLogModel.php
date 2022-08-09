<?php
namespace app\admin\model;

use think\Model;
use think\Db;

/**
 * @title 邮件日志模型
 * @desc 邮件日志模型
 * @use app\admin\model\EmailLogModel
 */
class EmailLogModel extends Model
{
	protected $name = 'email_log';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'subject'       => 'string',
        'message'       => 'string',
        'create_time'   => 'int',
        'status'        => 'int',
        'to'        	=> 'string',
        'fail_reason'   => 'string',
        'rel_id'        => 'int',
        'type'          => 'string',
        'ip'            => 'string',
        'port'          => 'string',
    ];
	
	public function createEmailLog($param){
		$this->startTrans();
        try {
            $sms_id = $this->create([       
				'subject' => $param['subject'] ?? '',
				'message' => $param['message'] ?? '',
				'status' => $param['status'],
				'to' => $param['to'],
				'fail_reason' => $param['fail_reason'] ?? '',			
				'create_time' => time(),
				'rel_id' => $param['rel_id'] ?? '0',
				'type' => $param['type'] ?? 'client',
				'ip' =>  empty($param['ip'])?request()->ip():$param['ip'],
				'port' =>  empty($param['port'])?request()->remotePort():$param['port'],
			]);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
        }

	}
	/**
     * 时间 2022-05-17
     * @title 邮件日志列表
     * @desc 邮件日志列表
     * @author theworld
     * @version v1
     * @param string param.client_id - 客户ID
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,subject,create_time
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 邮件日志
     * @return int list[].id - 邮件日志ID 
     * @return string list[].subject - 标题 
     * @return string list[].message - 内容 
     * @return int list[].create_time - 时间 
     * @return int list[].to - 邮箱 
     * @return string list[].user_type - 接收人类型client用户admin管理员
     * @return int list[].user_id - 接收人ID
     * @return string list[].user_name - 接收人名称
     * @return int list[].status - 状态，1成功，0失败
     * @return string list[].fail_reason - 失败原因
     * @return int count - 邮件日志总数
     */
    public function emailLogList($param)
    {
        $param['client_id'] = isset($param['client_id']) ? intval($param['client_id']) : 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'subject', 'create_time']) ? 'el.'.$param['orderby'] : 'el.id';

    	$count = $this->alias('el')
    		->field('el.id')
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('el.subject', 'like', "%{$param['keywords']}%");
    			}
                if(!empty($param['client_id'])){
                    $query->where('el.type', 'client')->where('el.rel_id', $param['client_id']);
                }
		        
		    })
		    ->count();
    	$logs = $this->alias('el')
    		->field('el.id,el.to,el.subject,el.message,el.create_time,el.status,el.fail_reason,el.type user_type,el.rel_id user_id,c.username,a.name')
    		->leftjoin('client c', "c.id=el.rel_id AND el.type='client'")
    		->leftjoin('admin a', "a.id=el.rel_id AND el.type='admin'")
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('el.subject', 'like', "%{$param['keywords']}%");
    			}
                if(!empty($param['client_id'])){
                    $query->where('el.type', 'client')->where('el.rel_id', $param['client_id']);
                }
		        
		    })
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	foreach ($logs as $key => $log) {
            // 根据用户类型生成名称
    		if($log['user_type']=='client'){
    			$logs[$key]['user_name'] = $log['username'];
    		}else if($log['user_type']=='admin'){
    			$logs[$key]['user_name'] = $log['name'];
    		}
    		unset($logs[$key]['username'], $logs[$key]['name']);
    	}

    	return ['list' => $logs, 'count' => $count];
    }
}
