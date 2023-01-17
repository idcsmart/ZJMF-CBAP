<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Request;

/**
 * @title 系统日志模型
 * @desc 系统日志模型
 * @use app\common\model\SystemLogModel
 */
class SystemLogModel extends Model
{
	protected $name = 'system_log';

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
     * 时间 2022-05-16
     * @title 系统日志列表
     * @desc 系统日志列表
     * @author theworld
     * @version v1
     * @param string type - 类型system:系统日志api:API日志
     * @param string param.keywords - 关键字
     * @param int param.client_id - 用户ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,description,create_time,ip
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 系统日志
     * @return int list[].id - 系统日志ID 
     * @return string list[].description - 描述 
     * @return string list[].create_time - 时间 
     * @return int list[].ip - IP 
     * @return string list[].user_type - 操作人类型client用户admin管理员system系统cron定时任务
     * @return string list[].user_id - 操作人ID
     * @return string list[].user_name - 操作人名称
     * @return int count - 系统日志总数
     */
    public function systemLogList($param)
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

        $param['type'] = $param['type'] ?? '';
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'description', 'create_time', 'ip']) ? $param['orderby'] : 'id';

    	$count = $this->field('id')
    		->where(function ($query) use($param, $app) {
    			if(!empty($param['keywords'])){
    				$query->where('description', 'like', "%{$param['keywords']}%");
    			}

		        if(!empty($param['client_id'])){
                    if($app=='home'){
                        $query->whereIn('user_type', ['client', 'api'])->where('client_id', $param['client_id']);
                    }else{
                        $query->where('client_id', $param['client_id']);
                    }
                }
                if($param['type']=='system'){
                    $query->where('user_type', '<>', "api");
                }else if($param['type']=='api'){
                    $query->where('user_type', "api");
                }else{
                    if(!empty($param['type']) && isset($param['rel_id'])){
                        $query->where('type', $param['type'])->where('rel_id', $param['rel_id']);
                    }
                }
		    })
		    ->count();
    	$logs = $this->field('id,description,create_time,ip,user_type,user_id,user_name')
    		->where(function ($query) use($param, $app) {
    			if(!empty($param['keywords'])){
    				$query->where('description', 'like', "%{$param['keywords']}%");
    			}
		        if(!empty($param['client_id'])){
                    if($app=='home'){
                        $query->whereIn('user_type', ['client', 'api'])->where('client_id', $param['client_id']);
                    }else{
                        $query->where('client_id', $param['client_id']);
                    }
                }
                if($param['type']=='system'){
                    $query->where('user_type', '<>', "api");
                }else if($param['type']=='api'){
                    $query->where('user_type', "api");
                }else{
                    if(!empty($param['type']) && isset($param['rel_id'])){
                        $query->where('type', $param['type'])->where('rel_id', $param['rel_id']);
                    }
                }
		    })
            ->withAttr('description',function ($value,$data){
                $app = app('http')->getName();
                $pattern = "/(?P<name>\w+)#(?P<digit>\d+)#(?P<desc>[^#]+)#/";
                preg_match_all($pattern,$value,$matches);
                $name = $matches['name'];
                $digit = $matches['digit'];
                $desc = $matches['desc'];
                if (!empty($name)){
                    foreach ($name as $k=>$v){
                        $relid = $digit[$k];
                        $str = $v.'#'.$relid.'#'.$desc[$k].'#';
                        $str1 = '#'.$relid.$desc[$k];

                        if ($v == 'client'){
                            if($app=='home'){
                                $url = $str1;
                            }else{
                                $url = '<a class="el-link el-link--primary is-underline" href="client_detail.html?client_id=' . $relid . '"><span class="el-link--inner">'.$str1.'</span></a>';
                            }
                            
                            $value = str_replace($str,$url,$value);
                        }
                        elseif ($v == 'host'){
                            $host = HostModel::field('client_id')
                                ->find($relid);
                            if($app=='home'){
                                $url = $str1;
                            }else{
                                $url = '<a class="el-link el-link--primary is-underline" href="host_detail.html?client_id='.$host['client_id'].'&id=' . $relid . '"><span class="el-link--inner">'.$str1.'</span></a>';
                            }
                            
                            $value = str_replace($str,$url,$value);
                        }
                        elseif ($v == 'product'){
                            if($app=='home'){
                                $url = $str1;
                            }else{
                                $url = '<a class="el-link el-link--primary is-underline" href="product_detail.html?id=' . $relid . '"><span class="el-link--inner">'.$str1.'</span></a>';
                            }
                            $value = str_replace($str,$url,$value);
                        }
                        elseif ($v == 'ticket'){
                            if($app=='home'){
                                $url = $str1;
                            }else{
                                $url = '<a class="el-link el-link--primary is-underline" href="plugin/idcsmart_ticket/ticket_detail.html?id=' . $relid . '"><span class="el-link--inner">'.$str1.'</span></a>';
                            }
                            
                            $value = str_replace($str,$url,$value);
                        }
                        
                    }
                    return $value;
                }else{
                    return $value;
                }

            })
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	foreach ($logs as $key => $log) {    
            // 前台接口去除字段
            if($app=='home'){
                if($log['user_type']=='api'){
                    $logs[$key]['description'] = '['.$log['user_id'].']'.$log['description'];
                }
                unset($logs[$key]['user_type'], $logs[$key]['user_id'], $logs[$key]['user_name']);
            }else{
                if($log['user_type']=='api'){
                    $logs[$key]['user_name'] = '[API]'.$log['user_id'];
                }
            }
    	}

    	return ['list' => $logs, 'count' => $count];
    }

    /**
     * 时间 2022-05-23
     * @title 添加系统日志
     * @desc 添加系统日志
     * @author theworld
     * @version v1
     * @param string param.description - 描述
     * @param string param.type - 关联类型
     * @param int param.rel_id - 关联ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return object data - 返回数据
     */
    public function createSystemLog($param)
    {
        $clientIp = get_client_ip(); // 获取客户端IP
        $remotePort = Request::remotePort(); // 获取端口

        $description = $param['description'] ?? '';
        $type = $param['type'] ?? '';
        $relId = $param['rel_id'] ?? 0;
        $param['client_id']  = $param['client_id'] ?? 0;
        $adminId = get_admin_id();
        $clientId = get_client_id();
        if(empty($adminId) && empty($clientId)){
            if ($type=='admin'){
                $userType = 'admin';
                $userId = 0;
                $userName = '';
            }elseif($type=='login'){
                $userType = 'client';
                $userId = 0;
                $userName = '';
            }else{
                $userType = 'cron';
                $userId = 0;
                $userName = 'cron';
            }
        }else if(!empty($adminId) && empty($clientId)){
            $userType = 'admin';
            $userId = $adminId;
            $userName = request()->admin_name;
        }else if(empty($adminId) && !empty($clientId) && empty(request()->api_id)){
            $userType = 'client';
            $userId = $clientId;
            $userName = request()->client_name;
            if($clientId!=get_client_id(false)){
                $userName = $userName.lang('sub_account');
                $userId = get_client_id(false);
                if(!empty($description) && $description!=''){
                    $description = '#'.$userId.$userName.$description;
                }
            }
        }else if(empty($adminId) && !empty($clientId) && !empty(request()->api_id)){
            $userType = 'api';
            $userId = request()->api_id;
            $userName = request()->api_name;
        }
        

        // 描述
        if(empty($description) || $description==''){
            $module = Request::module();
            $controller = Request::controller();
            $action = Request::action();
            $rule = 'app\\'.$module .'\\controller\\'. $controller .'controller::'. $action;
            if(empty($module) || empty($controller) || empty($action)) $rule = request()->url();
            $description = $rule;
        }

        // 描述中存在密码
        if(strpos($description, "password")!==false) {
            $description = preg_replace("/(password(?:hash)?`=')(.*)(',|' )/", "\${1}--REDACTED--\${3}", $description);
        }

        // 定时任务的日志前加定时任务标志
        if($userType=='cron'){
            $description = 'Cron_'.$description;
        }

        // API的日志前加API标志
        /*if($userType=='api'){
            $description = 'Api_'.$description;
        }*/

        // 获取关联用户ID
        if(empty($clientId) && !empty($type) && !empty($relId)){
            if($type=='host'){
                $host = HostModel::find($relId);
                $clientId = $host['client_id'];
            }else if($type=='order'){
                $order = OrderModel::find($relId);
                $clientId = $order['client_id'];
            }else if($type=='transaction'){
                $transaction = TransactionModel::find($relId);
                $clientId = $transaction['client_id'];
            }else if($type=='client'){
                $clientId = $relId;
            }else if(!empty($param['client_id'])){
                $clientId = $param['client_id'];
            }
        }

        try {
            /*if($userType=='api'){
                ApiLogModel::create([
                    'type' => $type,
                    'rel_id' => $relId,
                    'description' => $description,
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'client_id' => $clientId,
                    'ip' => $clientIp,
                    'port' => $remotePort,
                    'create_time' => time(),
                ]);
            }else{*/
                $this->create([
                    'type' => $type,
                    'rel_id' => $relId,
                    'description' => $description,
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'client_id' => $clientId,
                    'ip' => $clientIp,
                    'port' => $remotePort,
                    'create_time' => time(),
                ]);
            //}
            
        } catch (\Exception $e) {
            // 回滚事务
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
        
    }
}
