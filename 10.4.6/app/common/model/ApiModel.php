<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Request;
use think\db\Query;

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
        'public_key'    => 'string',
        'private_key'   => 'string',
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
     * @return int create_api - 是否可创建API:0否1是
     */
    public function apiList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        $param['client_id'] = get_client_id();
        if(empty($param['client_id'])){
            return ['list' => [], 'count' => 0];
        }
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? $param['orderby'] : 'id';

        $where = function (Query $query) use($param) {
            if(!empty($param['client_id'])){
                $query->where('client_id', $param['client_id']);
            }
        };

    	$count = $this->field('id')
    		->where($where)
		    ->count();
    	$api = $this->field('name,id,token,create_time,status,ip')
    		->where($where)
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();

    	foreach ($api as $key => $value) {    
            $api[$key]['token'] = hide_str(aes_password_decode($value['token']), '*', 12, 8); // 隐藏token的部分字符
    	}

        if(!empty($param['client_id'])){
            $createApi = 1;
            if(empty(configuration('client_create_api'))){
                $createApi = 0;
            }else if(configuration('client_create_api_type')==1){
                $clientCreateApiClient = explode(',', configuration('client_create_api_client'));
                if(!in_array($param['client_id'], $clientCreateApiClient)){
                    $createApi = 0;
                }
            }else if(configuration('client_create_api_type')==2){
                $clientCreateApiClient = explode(',', configuration('client_create_api_client'));
                if(in_array($param['client_id'], $clientCreateApiClient)){
                    $createApi = 0;
                }
            }
        }
        

    	return ['list' => $api, 'count' => $count, 'create_api' => $createApi];
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

        if(empty(configuration('client_create_api'))){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        if(configuration('client_create_api_type')==1){
            $clientCreateApiClient = explode(',', configuration('client_create_api_client'));
            if(!in_array($clientId, $clientCreateApiClient)){
                return ['status'=>400, 'msg'=>lang('fail_message')];
            }
        }else if(configuration('client_create_api_type')==2){
            $clientCreateApiClient = explode(',', configuration('client_create_api_client'));
            if(in_array($clientId, $clientCreateApiClient)){
                return ['status'=>400, 'msg'=>lang('fail_message')];
            }
        }

        $count = $this->where('client_id', $clientId)->count();
        if($count>=10){
            return ['status'=>400, 'msg'=>lang('api_key_create_max')];
        }

        $this->startTrans();
        try {
            $token = rand_str(32);

            $res = idcsmart_openssl_rsa_key_create();

            $api = $this->create([
                'client_id' => $clientId,
                'name' => $param['name'] ?? '',
                'token' => aes_password_encode($token), // token加密
                'ip' => '',
                'public_key' => aes_password_encode($res['public_key']),
                'private_key' => aes_password_encode($res['private_key']),
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
        return ['status' => 200, 'msg' => lang('create_success'), 'data' => ['name' => $param['name'] ?? '', 'id' => $api->id, 'token' => $token, 'private_key' => $res['private_key'], 'create_time' => $api->create_time]];
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

    /**
     * 时间 2024-04-28
     * @title 获取API设置
     * @desc 获取API设置
     * @author theworld
     * @version v1
     * @return int client_create_api - 用户API创建权限0关闭1开启
     * @return int client_create_api_type - 用户API创建权限类型0全部用户1指定用户可创建2指定用户不可创建
     */
    public function getConfig()
    {
        $data = [
            'client_create_api' => configuration('client_create_api'),
            'client_create_api_type' => configuration('client_create_api_type'),
        ];

        return $data;
    }

    /**
     * 时间 2024-04-28
     * @title 保存API设置
     * @desc 保存API设置
     * @author theworld
     * @version v1
     * @param int client_create_api - 用户API创建权限0关闭1开启
     * @param int client_create_api_type - 用户API创建权限类型0全部用户1指定用户可创建2指定用户不可创建
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateConfig($param)
    {
        $description = [];
        if(isset($param['client_create_api'])){
            if($param['client_create_api']!=configuration('client_create_api')){
                $lang = '"'.lang("configuration_log_client_create_api").'"';
                $lang_old = lang("configuration_log_switch_".configuration('client_create_api'));
                $lang_new = lang("configuration_log_switch_".$param['client_create_api']);
                $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
            }
            updateConfiguration('client_create_api', $param['client_create_api']);    
        }
        if(isset($param['client_create_api_type'])){
            if($param['client_create_api_type']!=configuration('client_create_api_type')){
                $lang = '"'.lang("configuration_log_client_create_api_type").'"';
                $lang_old = lang("configuration_log_client_create_api_type_".configuration('client_create_api_type'));
                $lang_new = lang("configuration_log_client_create_api_type_".$param['client_create_api_type']);
                $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
            }
            updateConfiguration('client_create_api_type', $param['client_create_api_type']);
        }

        $description = implode(',', $description);
        # 记录日志
        if($description)
            active_log(lang('admin_configuration_api', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * 时间 2024-04-28
     * @title API指定用户列表
     * @desc API指定用户列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 用户
     * @return int list[].id - 用户ID 
     * @return string list[].username - 姓名 
     * @return string list[].email - 邮箱 
     * @return int list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return int list[].status - 状态;0:禁用,1:正常 
     * @return string list[].company - 公司 
     * @return int list[].host_num - 产品数量 
     * @return int list[].host_active_num - 已激活产品数量
     * @return array list[].custom_field - 自定义字段
     * @return string list[].custom_field[].name - 名称
     * @return string list[].custom_field[].value - 值
     * @return bool list[].certification 是否实名认证true是false否
     * @return string list[].certification_type 实名类型person个人company企业
     * @return int count - 用户总数
     */
    public function clientList($param)
    {
        $clientId = array_filter(explode(',', configuration('client_create_api_client')));
        if(empty($clientId)){
            return ['list' => [], 'count' => 0];
        }
        $ClientModel = new ClientModel();
        $param['client_ids'] = $clientId;
        $data = $ClientModel->clientList($param);
        return $data;
    }

    /**
     * 时间 2024-04-28
     * @title 添加API指定用户
     * @desc 添加API指定用户
     * @author theworld
     * @version v1
     * @param int id - 用户ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function addClient($id)
    {
        $ClientModel = new ClientModel();
        $client = $ClientModel->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }

        $clientId = array_filter(explode(',', configuration('client_create_api_client')));
        if(in_array($id, $clientId)){
            return ['status'=>400, 'msg'=>lang('client_already_exists_in_client_create_api_client')];
        }

        array_push($clientId, $id);
        updateConfiguration('client_create_api_client', implode(',', $clientId));

        active_log(lang('admin_configuration_add_api_client', ['{admin}'=>request()->admin_name, '{client}'=>$client['name']]), 'admin', request()->admin_id);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * 时间 2024-04-28
     * @title 移除API指定用户
     * @desc 移除API指定用户
     * @author theworld
     * @version v1
     * @param int id - 用户ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function removeClient($id)
    {
        $clientId = array_filter(explode(',', configuration('client_create_api_client')));
        if(!in_array($id, $clientId)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exists_in_client_create_api_client')];
        }

        $ClientModel = new ClientModel();
        $client = $ClientModel->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }

        $key = array_search($id, $clientId);
        unset($clientId[$key]);
        $clientId = array_values($clientId);
        updateConfiguration('client_create_api_client', implode(',', $clientId));

        active_log(lang('admin_configuration_remove_api_client', ['{admin}'=>request()->admin_name, '{client}'=>$client['name']]), 'admin', request()->admin_id);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }
}
