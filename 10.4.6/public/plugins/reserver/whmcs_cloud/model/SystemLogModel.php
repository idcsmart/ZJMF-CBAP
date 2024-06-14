<?php
namespace reserver\whmcs_cloud\model;

use app\common\model\SystemLogModel AS SLM;
use app\common\model\HostModel;

/**
 * @title 系统日志模型
 * @desc 系统日志模型
 * @use reserver\whmcs_cloud\model\SystemLogModel
 */
class SystemLogModel extends SLM
{

	/**
     * 时间 2022-05-16
     * @title 系统日志列表
     * @desc 系统日志列表
     * @author theworld
     * @version v1
     * @param int param.id - 产品ID
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,description,create_time,ip
     * @param string param.sort - 升/降序 asc,desc
     * @return int status - 状态码(200=成功,400=失败)
     * @return string msg - 提示信息
     * @return array data.list - 系统日志
     * @return int data.list[].id - 系统日志ID 
     * @return string data.list[].description - 描述 
     * @return string data.list[].create_time - 时间 
     * @return int data.list[].ip - IP 
     * @return string data.list[].user_type - 操作人类型client用户admin管理员system系统cron定时任务
     * @return string data.list[].user_id - 操作人ID
     * @return string data.list[].user_name - 操作人名称
     * @return int data.count - 系统日志总数
     */
    public function systemLogList($param)
    {
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'list'=>[],
                'count'=>0,
            ]
        ];

        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['client_id'] = get_client_id();
            if(empty($param['client_id'])){
                return $result;
            }else{
                $host = HostModel::find($param['id']);
                if($host['client_id'] != $param['client_id'] || $host['is_delete']){
                    return $result;
                }
            }
        }
        
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'description', 'create_time', 'ip']) ? $param['orderby'] : 'id';

    	$count = $this->field('id')
    		->where(function ($query) use($param, $app) {
    			if(!empty($param['keywords'])){
    				$query->where('description', 'like', "%{$param['keywords']}%");
    			}

		        if(!empty($param['client_id'])){
                    if($app=='home'){
                        $query->where('user_type', 'client')->where('user_id', $param['client_id']);
                    }
                }
		    })
            ->where('type', 'host')
            ->where('rel_id', $param['id'])
		    ->count();
    	$logs = $this->field('id,description,create_time,ip,user_type,user_id,user_name')
    		->where(function ($query) use($param, $app) {
    			if(!empty($param['keywords'])){
    				$query->where('description', 'like', "%{$param['keywords']}%");
    			}
		        if(!empty($param['client_id'])){
                    if($app=='home'){
                        $query->where('user_type', 'client')->where('user_id', $param['client_id']);
                    }
                }
		    })
            ->where('type', 'host')
            ->where('rel_id', $param['id'])
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	foreach ($logs as $key => $log) {    
            // 前台接口去除字段
            if($app=='home'){
                unset($logs[$key]['user_type'], $logs[$key]['user_id'], $logs[$key]['user_name']);
            }
    	}
        $result['data']['list'] = $logs;
        $result['data']['count'] = $count;
    	return $result;
    }
}
