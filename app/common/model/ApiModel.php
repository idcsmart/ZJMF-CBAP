<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Request;

/**
 * @title API密钥模型
 * @desc API密钥模型
 * @use app\common\model\ApiModel
 */
class ApiModel extends Model
{
	protected $name = 'api';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'client_id'     => 'int',
        'name'          => 'string',
        'token'         => 'string',
        'status'        => 'int',
        'ip'            => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

	/**
     * 时间 2022-07-06
     * @title API密钥列表
     * @desc API密钥列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - API密钥
     * @return int list[].name - API密钥名称 
     * @return int list[].id - API密钥ID 
     * @return string list[].token - token 
     * @return int list[].create_time - 创建时间 
     * @return string list[].status - 白名单状态0关闭1开启
     * @return string list[].ip - 白名单IP 
     * @return int count - API日志总数
     */
    public function ApiList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        $param['client_id'] = get_client_id();
        if(empty($param['client_id'])){
            return ['list' => [], 'count' => 0];
        }


        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? $param['orderby'] : 'id';

    	$count = $this->field('id')
    		->where(function ($query) use($param) {
		        if(!empty($param['client_id'])){
                    $query->where('client_id', $param['client_id']);
                }
		    })
		    ->count();
    	$api = $this->field('name,id,token,create_time,status,ip')
    		->where(function ($query) use($param) {
                if(!empty($param['client_id'])){
                    $query->where('client_id', $param['client_id']);
                }
            })
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	foreach ($api as $key => $value) {    
            $api[$key]['token'] = hide_str(aes_password_decode($value['token']), '*', 12, 8); // 隐藏token的部分字符
    	}

    	return ['list' => $api, 'count' => $count];
    }

    /**
     * 时间 2022-07-06
     * @title 创建API密钥
     * @desc 创建API密钥
     * @author theworld
     * @version v1
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return object data.api - API密钥
     * @return int data.api.name - API密钥名称
     * @return int data.api.id - API密钥ID 
     * @return string data.api.token - token 
     * @return int data.api.create_time - 创建时间 
     */
    public function createApi($param)
    {
        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        $count = $this->where('client_id', $clientId)->count();
        if($count>=10){
            return ['status'=>400, 'msg'=>lang('api_key_create_max')];
        }

        $this->startTrans();
        try {
            $token = rand_str(32);
            $api = $this->create([
                'client_id' => $clientId,
                'name' => $param['name'] ?? '',
                'token' => aes_password_encode($token), // token加密
                'ip' => '',
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_client_add_api', ['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{name}'=>$param['name']]), 'api', $api->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success'), 'data' => ['name' => $param['name'] ?? '', 'id' => $api->id, 'token' => $token, 'create_time' => $api->create_time]];
    } 

    /**
     * 时间 2022-07-06
     * @title API白名单设置
     * @desc API白名单设置
     * @author theworld
     * @version v1
     * @param int param.id - API密钥ID required
     * @param int param.status - 白名单状态0关闭1开启 required
     * @param string param.ip - 白名单IP required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function whiteListSetting($param)
    {
        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        // 验证API密钥ID
        $api = $this->find($param['id']);
        if (empty($api)){
            return ['status'=>400, 'msg'=>lang('api_is_not_exist')];
        }
        if ($api['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang('api_is_not_exist')];
        }
        

        $this->startTrans();
        try {
            $this->update([
                'status' => $param['status'],
                'ip' => $param['status']==1 ? str_replace("\r\n", "\n", $param['ip'] ?? '') : '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang('log_client_edit_api', ['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{name}'=>$api['name']]), 'api', $api->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    } 

    /**
     * 时间 2022-07-06
     * @title 删除API密钥
     * @desc 删除API密钥
     * @author theworld
     * @version v1
     * @param int id - API密钥ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteApi($id)
    {
        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        // 验证API密钥ID
        $api = $this->find($id);
        if (empty($api)){
            return ['status'=>400, 'msg'=>lang('api_is_not_exist')];
        }
        if ($api['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang('api_is_not_exist')];
        }
        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_client_delete_api', ['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{name}'=>$api['name']]), 'api', $api->id);
            
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }
}
