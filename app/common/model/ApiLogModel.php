<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Request;

/**
 * @title API日志模型
 * @desc API日志模型
 * @use app\common\model\ApiLogModel
 */
class ApiLogModel extends Model
{
	protected $name = 'api_log';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'rel_id'        => 'int',
        'description'   => 'string',
        'user_type'     => 'string',
        'user_id'       => 'int',
        'user_name'     => 'string',
        'client_id'     => 'int',
        'ip'            => 'string',
        'port'          => 'int',
        'create_time'   => 'int',
    ];

	/**
     * 时间 2022-07-06
     * @title API日志
     * @desc API日志
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,description,create_time,ip
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - API日志
     * @return int list[].id - API日志ID 
     * @return string list[].description - 描述 
     * @return int list[].create_time - 时间 
     * @return string list[].ip - IP 
     * @return string list[].api_id - API密钥ID 
     * @return int count - API日志总数
     */
    public function apiLogList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['client_id'] = get_client_id();
            if(empty($param['client_id'])){
                return ['list' => [], 'count' => 0];
            }
        }else{
            $param['client_id'] = isset($param['client_id']) ? intval($param['client_id']) : 0;
        }

        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'description', 'create_time', 'ip']) ? $param['orderby'] : 'id';

    	$count = $this->field('id')
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('ip|description', 'like', "%{$param['keywords']}%");
    			}

		        if(!empty($param['client_id'])){
                    $query->where('client_id', $param['client_id']);
                }
		    })
		    ->count();
    	$logs = $this->field('id,description,create_time,ip,user_type,user_id,user_name')
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('ip|description', 'like', "%{$param['keywords']}%");
    			}
		        if(!empty($param['client_id'])){
                    $query->where('client_id', $param['client_id']);
                }
		    })
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	foreach ($logs as $key => $log) {
            $logs['api_id'] = $logs[$key]['user_id'];
            // 前台接口去除字段
            if($app=='home'){
                unset($logs[$key]['user_type'], $logs[$key]['user_id'], $logs[$key]['user_name']);
            }
    	}

    	return ['list' => $logs, 'count' => $count];
    }
}
