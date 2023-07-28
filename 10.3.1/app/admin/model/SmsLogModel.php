<?php
namespace app\admin\model;

use think\Model;
use think\Db;

/**
 * @title 短信日志模型
 * @desc 短信日志模型
 * @use app\admin\model\SmsLogModel
 */
class SmsLogModel extends Model
{
	protected $name = 'sms_log';
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'phone_code'    => 'int',
        'phone'         => 'string',
        'template_code' => 'string',
        'content'       => 'string',
        'status'        => 'int',
        'fail_reason'   => 'string',
        'create_time'   => 'int',
        'rel_id'        => 'int',
        'type'          => 'string',
        'ip'            => 'string',
        'port'          => 'int',
    ];
	
	public function createSmsLog($param){
		$this->startTrans();
        try {
            $sms_id = $this->create([       
				'phone_code' => $param['phone_code'] ?? '86',
				'phone' => $param['phone'] ?? '',
				'template_code' => $param['template_code'] ?? '',
				'content' => $param['content'] ?? '',
				'status' => $param['status'],
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
     * @title 短信日志列表
     * @desc 短信日志列表
     * @author theworld
     * @version v1
     * @param string param.client_id - 客户ID
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,content,create_time
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 短信日志
     * @return int list[].id - 短信日志ID 
     * @return string list[].content - 内容 
     * @return int list[].create_time - 时间 
     * @return string list[].user_type - 接收人类型client用户admin管理员
     * @return int list[].user_id - 接收人ID
     * @return int list[].phone_code - 国际电话区号
     * @return string list[].phone - 手机号
     * @return string list[].user_name - 接收人名称
     * @return int list[].status - 状态，1成功，0失败
     * @return string list[].fail_reason - 失败原因
     * @return int count - 短信日志总数
     */
    public function smsLogList($param)
    {
        $param['client_id'] = isset($param['client_id']) ? intval($param['client_id']) : 0;
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'content', 'create_time']) ? 'sl.'.$param['orderby'] : 'sl.id';

    	$count = $this->alias('sl')
    		->field('sl.id')
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('sl.content', 'like', "%{$param['keywords']}%");
    			}
		        if(!empty($param['client_id'])){
                    $query->where('sl.type', 'client')->where('sl.rel_id', $param['client_id']);
                }
		    })
		    ->count();
    	$logs = $this->alias('sl')
    		->field('sl.id,sl.content,sl.create_time,sl.status,sl.fail_reason,sl.type user_type,sl.rel_id user_id,c.username,sl.phone_code,sl.phone,a.name')
    		->leftjoin('client c', "c.id=sl.rel_id AND sl.type='client'")
    		->leftjoin('admin a', "a.id=sl.rel_id AND sl.type='admin'")
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('sl.content', 'like', "%{$param['keywords']}%");
    			}
                if(!empty($param['client_id'])){
                    $query->where('sl.type', 'client')->where('sl.rel_id', $param['client_id']);
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
                $logs[$key]['phone_code'] = '';
                $logs[$key]['phone'] = '';
    		}
    		unset($logs[$key]['username'], $logs[$key]['name']);
    	}

    	return ['list' => $logs, 'count' => $count];
    }
}
