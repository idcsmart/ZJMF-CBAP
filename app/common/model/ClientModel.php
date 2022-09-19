<?php
namespace app\common\model;

use think\facade\Cache;
use think\Model;

/**
 * @title 用户模型
 * @desc 用户模型
 * @use app\common\model\ClientModel
 */
class ClientModel extends Model
{
	protected $name = 'client';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'status'          => 'int',
        'email'           => 'string',
        'phone_code'      => 'int',
        'phone'           => 'string',
        'password'        => 'string',
        'credit'          => 'float',
        'company'         => 'string',
        'country'         => 'string',
        'address'         => 'string',
        'language'        => 'string',
        'notes'           => 'string',
        'client_notes'    => 'string',
        'last_login_time' => 'int',
        'last_login_ip'   => 'string',
        'last_action_time'=> 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
    ];

	/**
     * 时间 2022-05-10
     * @title 用户列表
     * @desc 用户列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:用户ID,姓名,邮箱,手机号
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
     * @return int count - 用户总数
     */
    public function clientList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'username', 'phone', 'email']) ? $param['orderby'] : 'id';

    	$count = $this->field('id')
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('id|username|email|phone', 'like', "%{$param['keywords']}%");
    			}
		        
		    })
		    ->count();
    	$clients = $this->field('id,username,email,phone_code,phone,status,company')
    		->where(function ($query) use($param) {
    			if(!empty($param['keywords'])){
    				$query->where('id|username|email|phone', 'like', "%{$param['keywords']}%");
    			}
		        
		    })
    		->limit($param['limit'])
    		->page($param['page'])
    		->order($param['orderby'], $param['sort'])
    		->select()
            ->toArray();
        $clientId = array_column($clients, 'id');
        $hostNum = HostModel::field('COUNT(id) num,client_id')->whereIn('client_id', $clientId)->group('client_id')->select()->toArray();;
        $hostNum = array_column($hostNum, 'num', 'client_id'); 
        $hostActiveNum = HostModel::field('COUNT(id) num,client_id')->where('status', 'Active')->whereIn('client_id', $clientId)->group('client_id')->select()->toArray();;
        $hostActiveNum = array_column($hostActiveNum, 'num', 'client_id'); 

    	foreach ($clients as $key => $client) {
    		$clients[$key]['host_num'] = $hostNum[$client['id']] ?? 0; // 产品数量
    		$clients[$key]['host_active_num'] = $hostActiveNum[$client['id']] ?? 0; // 已激活产品数量
    	}

    	return ['list' => $clients, 'count' => $count];
    }

    /**
     * 时间 2022-05-10
     * @title 用户详情
     * @desc 用户详情
     * @author theworld
     * @version v1
     * @param int id - 用户ID required
     * @return int id - 用户ID 
     * @return string username - 姓名 
     * @return string email - 邮箱 
     * @return int phone_code - 国际电话区号 
     * @return string phone - 手机号 
     * @return string company - 公司 
     * @return string country - 国家 
     * @return string address - 地址 
     * @return string language - 语言 
     * @return string notes - 备注
     * @return int status - 状态;0:禁用,1:正常 
     * @return int register_time - 注册时间 
     * @return int last_login_time - 上次登录时间 
     * @return string last_login_ip - 上次登录IP
     * @return string credit - 余额 
     * @return string consume - 消费 
     * @return string refund - 退款 
     * @return string withdraw - 提现 
     * @return int host_num - 产品数量 
     * @return int host_active_num - 已激活产品数量
     * @return array login_logs - 登录记录
     * @return string login_logs[].ip - IP
     * @return int login_logs[].login_time - 登录时间
     */
    public function indexClient($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $client = $this->field('id,username,email,phone_code,phone,company,country,address,language,notes,client_notes,status,create_time register_time,last_login_time,last_login_ip,credit')->find($id);
        if (empty($client)){
            return (object)[]; // 转换为对象
        }

        $client['credit'] = amount_format($client['credit']); // 余额
        if($app=='admin'){
            $client['consume'] = amount_format(TransactionModel::where('client_id', $id)->where('amount', '>', 0)->sum('amount')); // 消费
            $client['refund'] = amount_format(TransactionModel::where('client_id', $id)->where('amount', '<', 0)->sum('amount')); // 退款
            $client['withdraw'] = amount_format(-ClientCreditModel::where('client_id', $id)->where('type', 'Withdraw')->sum('amount')); // 提现
            $client['host_num'] = HostModel::where('client_id', $id)->count();  // 产品数量
            $client['host_active_num'] = HostModel::where('status', 'Active')->where('client_id', $id)->count(); // 已激活产品数量
            $client['login_logs'] = SystemLogModel::field('ip,create_time login_time')->where('type', 'login')->where('user_type', 'client')->where('user_id', $id)->limit(5)->order('id', 'desc')->select()->toArray(); 
            unset($client['client_notes']);
        }else if($app=='home'){
            $client['notes'] = $client['client_notes'];
            // 前台接口去除字段
            unset($client['id'], $client['client_notes'], $client['register_time'], $client['last_login_time'], $client['last_login_ip']);
        }
        
        
        return $client;
    }

    /**
     * 时间 2022-05-10
     * @title 新建用户
     * @desc 新建用户
     * @author theworld
     * @version v1
     * @param string param.username - 姓名
     * @param string param.email - 邮箱 邮箱手机号两者至少输入一个
     * @param int param.phone_code - 国际电话区号 邮箱手机号两者至少输入一个
     * @param string param.phone - 手机号 邮箱手机号两者至少输入一个
     * @param string param.password - 密码 required
     * @param string param.repassword - 重复密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return object data - 返回数据
     * @return int data.id - 用户ID,成功时返回
     */
    public function createClient($param)
    {
	    $this->startTrans();
		try {
	    	$client = $this->create([
	    		'username' => (isset($param['username']) && !empty($param['username']))?$param['username']:((isset($param['email']) && !empty($param['email']))?explode('@',$param['email'])[0]:((isset($param['phone']) && !empty($param['phone']))?$param['phone']:'')),
	    		'email' => $param['email']  ?? '',
	    		'phone_code' => $param['phone_code'] ?? 44,
	    		'phone' => $param['phone'] ?? '',
	    		'password' => idcsmart_password($param['password']), // 密码加密
                'language' => configuration('lang_home')??'zh-cn',
                'create_time' => time()
	    	]);

            # 记录日志
            active_log(lang('admin_create_new_user', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$param['username'].'#']), 'client', $client->id);

	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('create_fail')];
		}

		hook('after_client_create',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

    	return ['status' => 200, 'msg' => lang('create_success'), 'data' => ['id' => $client->id]];
    }

    /**
     * 时间 2022-05-10
     * @title 修改用户
     * @desc 修改用户
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param string param.username - 姓名
     * @param string param.email - 邮箱 邮箱手机号两者至少输入一个
     * @param int param.phone_code - 国际电话区号 邮箱手机号两者至少输入一个
     * @param string param.phone - 手机号 邮箱手机号两者至少输入一个
     * @param string param.company - 公司
     * @param string param.country - 国家
     * @param string param.address - 地址
     * @param string param.language - 语言
     * @param string param.notes - 备注
     * @param string password - 密码 为空代表不修改
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClient($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['id'] = get_client_id();
        }


        // 验证用户ID
    	$client = $this->find($param['id']);
        if($app=='home'){
            if (empty($client)){
                return ['status'=>400, 'msg'=>lang('fail_message')];
            }
            $param['email'] = $client['email'];
            $param['phone_code'] = $client['phone_code'];
            $param['phone'] = $client['phone'];
            $param['client_notes'] = $param['notes'] ?? '';
            $param['notes'] = $client['notes'];
        }else{
            if (empty($client)){
                return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
            }
            $param['username'] = $param['username'] ?? '';
            $param['email'] = $param['email'] ?? '';
            $param['phone_code'] = $param['phone_code'] ?? 44;
            $param['phone'] = $param['phone'] ?? '';
            $param['company'] = $param['company'] ?? '';
            $param['country'] = $param['country'] ?? '';
            $param['address'] = $param['address'] ?? '';
            $param['language'] = $param['language'] ?? '';
            $param['notes'] = $param['notes'] ?? '';
            $param['client_notes'] = $client['client_notes'];
        }
        $param['password'] = $param['password'] ?? '';

        if($app=='admin'){
            # 日志详情
            $description = [];
            if ($client['username'] != $param['username']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_username').$client['username'], '{new}'=>$param['username']]);
            }
            if ($client['email'] != $param['email']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_email').$client['email'], '{new}'=>$param['email']]);
            }
            if ($client['phone_code'] != $param['phone_code']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_phone_code').$client['phone_code'], '{new}'=>$param['phone_code']]);
            }
            if ($client['phone'] != $param['phone']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_phone').$client['phone'], '{new}'=>$param['phone']]);
            }
            if ($client['company'] != $param['company']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_company').$client['company'], '{new}'=>$param['company']]);
            }
            if ($client['country'] != $param['country']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_country').$client['country'], '{new}'=>$param['country']]);
            }
            if ($client['address'] != $param['address']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_address').$client['address'], '{new}'=>$param['address']]);
            }
            if ($client['language'] != $param['language']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_language').$client['language'], '{new}'=>$param['language']]);
            }
            if ($client['notes'] != $param['notes']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_notes').$client['notes'], '{new}'=>$param['notes']]);
            }
            if(!empty($param['password'])){
                $description[] = lang('log_change_password');
            }
            $description = implode(',', $description);
        }else if($app=='home'){
            # 日志详情
            $description = [];
            if ($client['username'] != $param['username']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_username').$client['username'], '{new}'=>$param['username']]);
            }
            if ($client['company'] != $param['company']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_company').$client['company'], '{new}'=>$param['company']]);
            }
            if ($client['country'] != $param['country']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_country').$client['country'], '{new}'=>$param['country']]);
            }
            if ($client['address'] != $param['address']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_address').$client['address'], '{new}'=>$param['address']]);
            }
            if ($client['language'] != $param['language']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_language').$client['language'], '{new}'=>$param['language']]);
            }
            if ($client['client_notes'] != $param['client_notes']){
                $description[] = lang('old_to_new',['{old}'=>lang('client_notes').$client['client_notes'], '{new}'=>$param['client_notes']]);
            }
            $description = implode(',', $description);
        }
    	

    	$this->startTrans();
		try {
            $this->update([
                'username' => $param['username'] ?? '',
                'email' => $param['email'] ?? '',
                'phone_code' => $param['phone_code'] ?? 44,
                'phone' => $param['phone'] ?? '',
                'password' => !empty($param['password']) ? idcsmart_password($param['password']) : $client['password'], // 密码加密
                'company' => $param['company'] ?? '',
                'country' => $param['country'] ?? '',
                'address' => $param['address'] ?? '',
                'language' => $param['language'] ?? '',
                'notes' => $param['notes'] ?? '',
                'client_notes' => $param['client_notes'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            if($app=='admin' && !empty($description)){
                # 记录日志
                active_log(lang('admin_modify_user_profile', ['{admin}'=>request()->admin_name, '{client}'=>'client#'.$client->id.'#'.$param['username'].'#', '{description}'=>$description]), 'client', $client->id);
            }else if($app=='home' && !empty($description)){
                # 记录日志
                active_log(lang('modify_profile', ['{client}'=>'client#'.$client->id.'#'.request()->client_name.'#', '{description}'=>$description]), 'client', $client->id);
            }

		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}

		hook('after_client_edit',['id'=>$param['id'],'customfield'=>$param['customfield']??[]]);

    	return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-10
     * @title 删除用户
     * @desc 删除用户
     * @author theworld
     * @version v1
     * @param int id - 用户ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteClient($param)
    {
        $id = $param['id']??0;
        // 验证用户ID
    	$client = $this->find($id);
    	if (empty($client)){
            return ['status'=>400, 'msg'=>lang('client_is_not_exist')];
        }
    	$this->startTrans();
		try {
			$this->destroy($id);
            // 删除用户余额记录
            ClientCreditModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户订单
            OrderModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户订单子项
            OrderItemModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户流水
            TransactionModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
            // 删除用户产品
            HostModel::destroy(function($query) use($id){
                $query->where('client_id', $id);
            });
		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('delete_fail')];
		}

        hook('after_client_delete',['id'=>$id]);

    	return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-5-26
     * @title 用户状态切换
     * @desc 用户状态切换
     * @author theworld
     * @version v1
     * @param int param.id - 用户ID required
     * @param int param.status 1 状态:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientStatus($param)
    {
        // 验证用户ID
        $client = $this->find($param['id']);
        if (empty($client)){
            return ['status' => 400, 'msg' => lang('client_is_not_exist')];
        }

        $status = intval($param['status']);

        if ($client['status'] == $status){
            return ['status' => 400, 'msg' => lang('cannot_repeat_opreate')];
        }
        $this->startTrans();
        try{
            $this->update([
                'status' => $status,
                'update_time' => time(),
            ],['id' => $param['id']]);
            $this->commit();
        }catch (\Exception $e){
            // 回滚事务
            $this->rollback();
            if ($status == 0){
                return ['status' => 400, 'msg' => lang('disable_fail')];
            }else{
                return ['status' => 400, 'msg' => lang('enable_fail')];
            }
        }

        if ($status == 0){
            return ['status' => 200, 'msg' => lang('disable_success')];
        }else{
            return ['status' => 200, 'msg' => lang('enable_success')];
        }

    }

    /**
     * 时间 2022-05-16
     * @title 搜索用户
     * @desc 搜索用户
     * @author theworld
     * @version v1
     * @param string keywords - 关键字,搜索范围:用户ID,姓名,邮箱,手机号
     * @param string type - 搜索类型:global全局搜索
     * @return array list - 用户
     * @return int list[].id - 用户ID 
     * @return string list[].username - 姓名
     * @return string list[].company - 公司
     * @return string list[].email - 邮箱
     * @return string list[].phone_code - 国际电话区号
     * @return string list[].phone - 手机号
     */
    public function searchClient($keywords, $type = '')
    {
        if($type=='global'){
            //全局搜索
            $clients = $this->field('id,username,company,email,phone_code,phone')
                ->where(function ($query) use($keywords) {
                    if(!empty($keywords)){
                        $query->where('username|company|email|phone', 'like', "%{$keywords}%");
                    }
                })
                ->select()
                ->toArray();
        }else{
            //搜索20条数据
            $clients = $this->field('id,username')
                ->where(function ($query) use($keywords) {
                    if(!empty($keywords)){
                        $query->where('id|username|email|phone', 'like', "%{$keywords}%");
                    }
                })
                ->limit(20)
                ->select()
                ->toArray();
        }

        return ['list' => $clients];
    }

    /**
     * 时间 2022-05-19
     * @title 验证原手机
     * @desc 验证原手机
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyOldPhone($param)
    {
        // 获取登录用户ID
        $id = get_client_id();
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }
        if(empty($client['phone'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_phone')];
        }

        // 验证码验证
        $code = Cache::get('verification_code_verify_'.$client['phone_code'].'_'.$client['phone']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_verify_'.$client['phone_code'].'_'.$client['phone']); // 验证通过,删除验证码缓存
        Cache::set('verification_code_verify_'.$client['phone_code'].'_'.$client['phone'].'_success', 1, 300); // 验证成功结果保存5分钟

        return ['status' => 200, 'msg' => lang('success_message')];
    }


    /**
     * 时间 2022-05-19
     * @title 修改手机
     * @desc 修改手机
     * @author theworld
     * @version v1
     * @param int param.phone_code - 国际电话区号 required
     * @param string param.phone - 手机号 required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientPhone($param)
    {
        // 获取登录用户ID
        $id = get_client_id();
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        // 如果已有手机则需要验证原手机
        if(!empty($client['phone'])){
            $verifyResult = Cache::get('verification_code_verify_'.$client['phone_code'].'_'.$client['phone'].'_success'); // 获取验证原手机结果
            if(empty($verifyResult)){
                return ['status'=>400, 'msg'=>lang('please_verify_old_phone')];
            }
        }

        // 验证码验证
        $code = Cache::get('verification_code_update_'.$param['phone_code'].'_'.$param['phone']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_update_'.$param['phone_code'].'_'.$param['phone']); // 验证通过,删除验证码缓存

        // 修改手机
        $this->startTrans();
        try {
            $this->update([
                'phone_code' => $param['phone_code'],
                'phone' => $param['phone'],
                'update_time' => time()
            ], ['id' => $id]);
			//客户更改手机发送短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => '客户更改手机成功,发送短信',
				'task_data' => [
					'name'=>'client_change_phone',//发送动作名称
					'phone_code' => $param['phone_code'],
					'phone' => $param['phone'],
					'client_id'=>$client['id'],//客户ID
				],		
			]);

            # 记录日志
            if(!empty($client['phone'])){
                active_log(lang('change_bound_mobile', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{phone}'=>$param['phone'], '{old_phone}'=>$client['phone']]), 'client', $id);
            }else{
                active_log(lang('bound_mobile', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{phone}'=>$param['phone']]), 'client', $id);
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-19
     * @title 验证原邮箱
     * @desc 验证原邮箱
     * @author theworld
     * @version v1
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function verifyOldEmail($param)
    {
        // 获取登录用户ID
        $id = get_client_id();
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }
        if(empty($client['email'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_email')];
        }

        // 验证码验证
        $code = Cache::get('verification_code_verify_'.$client['email']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }

        Cache::delete('verification_code_verify_'.$client['email']); // 验证通过,删除验证码缓存
        Cache::set('verification_code_verify_'.$client['email'].'_success', 1, 300); // 验证成功结果保存5分钟

        return ['status' => 200, 'msg' => lang('success_message')];
    }


    /**
     * 时间 2022-05-19
     * @title 修改邮箱
     * @desc 修改邮箱
     * @author theworld
     * @version v1
     * @param string param.email - 邮箱 required
     * @param string param.code - 验证码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientEmail($param)
    {
        // 获取登录用户ID
        $id = get_client_id();
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        // 如果已有邮箱则需要验证原邮箱
        if(!empty($client['email'])){
            $verifyResult = Cache::get('verification_code_verify_'.$client['email'].'_success'); // 获取验证原邮箱结果
            if(empty($verifyResult)){
                return ['status'=>400, 'msg'=>lang('please_verify_old_email')];
            }
        }

        // 验证码验证
        $code = Cache::get('verification_code_update_'.$param['email']);
        if(empty($code)){
            return ['status' => 400, 'msg' => lang('please_get_verification_code')];
        }

        if($code!=$param['code']){
            return ['status' => 400, 'msg' => lang('verification_code_error')];
        }
        Cache::delete('verification_code_update_'.$param['email']); // 验证通过,删除验证码缓存

        // 修改邮箱
        $this->startTrans();
        try {
            $this->update([
                'email' => $param['email'],
                'update_time' => time()
            ], ['id' => $id]);
			//客户更改邮箱发送邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => '客户更改邮箱成功,发送邮件',
				'task_data' => [
					'name'=>'client_change_email',//发送动作名称
					'email' => $param['email'],
					'client_id'=>$client['id'],//客户ID
				],		
			]);

            # 记录日志
            if(!empty($client['phone'])){
                active_log(lang('change_bound_email', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{email}'=>$param['email'], '{old_email}'=>$client['email']]), 'client', $id);
            }else{
                active_log(lang('bound_email', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#', '{email}'=>$param['email']]), 'client', $id);
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-19
     * @title 修改密码
     * @desc 修改密码
     * @author theworld
     * @version v1
     * @param string param.old_password - 旧密码 required
     * @param string param.new_password - 新密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateClientPassword($param)
    {
        // 获取登录用户ID
        $id = get_client_id();
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }

        // 验证密码
        if(!idcsmart_password_compare($param['old_password'], $client['password'])){
            return ['status'=>400, 'msg'=>lang('old_password_error')];
        }

        // 修改密码
        $this->startTrans();
        try {
            $this->update([
                'password' => idcsmart_password($param['new_password']), // 密码加密
                'update_time' => time()
            ], ['id' => $id]);

            Cache::set('home_update_password_'.$id,time(),3600*24*7); # wyh增 修改密码 退出登录 7天未操作接口,就可以不退出
			//客户更改密码发送邮件添加到任务队列
			if($client['email']){
				add_task([
					'type' => 'email',
					'description' => '客户更改密码成功,发送邮件',
					'task_data' => [
						'name'=>'client_change_password',//发送动作名称
						'email' => $client['email'],
						'client_id'=>$client['id'],//客户ID
						'template_param'=>[
							'client_password' => $param['new_password'],//新密码
						],
					],		
				]);
			}
			//客户更改密码发送短信添加到任务队列
			if($client['phone']){
				add_task([
					'type' => 'sms',
					'description' => '客户更改密码成功,发送短信',
					'task_data' => [
						'name'=>'client_change_password',//发送动作名称
						'phone_code' => $client['phone_code'],
						'phone' => $client['phone'],
						'client_id'=>$client['id'],//客户ID
						'template_param'=>[
							'client_password' => $param['new_password'],//新密码
						],
					],		
				]);
			}

            # 记录日志
            active_log(lang('change_password', ['{client}'=>'client#'.$id.'#'.request()->client_name.'#']), 'client', $id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-08-16
     * @title 验证码修改密码
     * @desc 验证码修改密码
     * @author theworld
     * @version v1
     * @param string type phone 验证类型:phone手机,email邮箱 required
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     */
    public function codeUpdatePassword($param)
    {
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('verify_type_is_required')];
        }

        if (!in_array($param['type'],['phone','email'])){
            return ['status'=>400,'msg'=>lang('verify_type_only_phone_or_email')];
        }

        $param['id'] = get_client_id();
        $type = $param['type'];
        if ($type == 'phone'){
            return $this->phonePasswordUpdate($param);
        }else{
            return $this->emailPasswordUpdate($param);
        }
    }

    /**
     * 时间 2022-05-20
     * @title 登录
     * @desc 登录
     * @author wyh
     * @version v1
     * @param string type code 登录类型:code验证码登录,password密码登录 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(手机号登录时需要传此参数)
     * @param string code 1234 验证码(登录类型为验证码登录code时需要传此参数)
     * @param string password 123456 密码(登录类型为密码登录password时需要传此参数)
     * @param string remember_password 1 记住密码(登录类型为密码登录password时需要传此参数,1是,0否)
     * @param string captcha 1234 图形验证码(开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数)
     * @param string token fd5adaf7267a5b2996cc113e45b38f05 图形验证码唯一识别码(开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数)
     * @return array
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+jwt
     */
    public function login($param)
    {
        # 参数验证
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('login_type_is_required')];
        }

        if (!in_array($param['type'],['code','password'])){
            return ['status'=>400,'msg'=>lang('login_type_only_code_or_password')];
        }

        if (isset($param['password'])){
            $param['password'] = password_decrypt($param['password']);
        }

        $type = $param['type'];
        if ($type == 'code'){
            return $this->codeLogin($param);
        }else{
            return $this->passwordLogin($param);
        }
    }

    /**
     * 时间 2022-05-23
     * @title 注册
     * @desc 注册
     * @author wyh
     * @version v1
     * @param string type phone 登录类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(登录类型为手机注册时需要传此参数)
     * @param string username wyh 姓名
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     * @return string data.jwt - jwt:注册后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function register($param)
    {
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('register_type_is_required')];
        }

        if (!in_array($param['type'],['phone','email'])){
            return ['status'=>400,'msg'=>lang('register_type_only_phone_or_email')];
        }

        # 图形验证码
        /*if (configuration('captcha_client_register')){
            if (!isset($param['captcha']) || empty($param['captcha'])){
                return ['status'=>400,'msg'=>lang('login_captcha')];
            }
            if (!isset($param['token']) || empty($param['token'])){
                return ['status'=>400,'msg'=>lang('login_captcha_token')];
            }
            $token = $param['token'];
            if (!check_captcha($param['captcha'],$token)){
                return ['status'=>400,'msg'=>lang('login_captcha_error')];
            }
        }*/

        $type = $param['type'];
        if ($type == 'phone'){
            return $this->phoneRegister($param);
        }else{
            return $this->emailRegister($param);
        }
    }

    /**
     * 时间 2022-05-23
     * @title 忘记密码
     * @desc 忘记密码
     * @author wyh
     * @version v1
     * @param string type phone 注册类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(注册类型为手机注册时需要传此参数)
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     */
    public function passwordReset($param)
    {
        if (!isset($param['type'])){
            return ['status'=>400,'msg'=>lang('register_type_is_required')];
        }

        if (!in_array($param['type'],['phone','email'])){
            return ['status'=>400,'msg'=>lang('register_type_only_phone_or_email')];
        }

        $type = $param['type'];
        if ($type == 'phone'){
            return $this->phonePasswordReset($param);
        }else{
            return $this->emailPasswordReset($param);
        }
    }

    /**
     * 时间 2022-5-23
     * @title 注销
     * @desc 注销
     * @author wyh
     * @version v1
     */
    public function logout($param)
    {
        $clientId = get_client_id();

        $client = $this->find($clientId);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang('client_is_not_exist')];
        }

        $jwt = get_header_jwt();

        Cache::set('login_token_'.$jwt,null);

        active_log(lang('log_client_logout',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id); # 特殊类型

        hook('after_client_logout',['id'=>$clientId,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('logout_success')];

    }

    # 手机号+验证码登录
    private function codeLogin($param)
    {
        # 是否开启手机验证码登录
        if (!configuration('login_phone_verify')){
            return ['status'=>400,'msg'=>lang('login_phone_verify_is_not_open')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 手机号未填
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_phone_require')];
        }
        # 手机号错误
        if (!check_mobile($param['phone_code']. '-' .$param['account'])){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_right')];
        }
        # 手机号未注册
        if (empty($client = $this->checkPhoneRegister($param['account'],$param['phone_code']))){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_register')];
        }
        # 登录限制

        # 验证码验证
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($param['account'],$param['phone_code'],'login');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($code != $param['code']){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $this->clearPhoneVerificationCode($param['account'],$param['phone_code'],'login');
        # 账号被禁用
        if ($client['status'] != 1){
            return ['status'=>400,'msg'=>lang('login_client_is_disabled')];
        }

        $this->startTrans();

        try{
            $udpate = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip(),
                'last_action_time' => time()
            ];
            $client->save($udpate);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);

            # 登录提醒
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_login',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id); # 特殊类型
			add_task([
				'type' => 'sms',
				'description' => '手机+验证码登录成功,发送短信',
				'task_data' => [
					'name'=>'client_login_success',//发送动作名称
					'client_id'=>$client->id,//客户ID
				],		
			]);		
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('login_fail') . ':' . $e->getMessage()];
        }

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        $expired = 3600*24*1;

        $data = [
            'jwt' => create_jwt($info,$expired)
        ];

        hook('after_client_login',['id'=>$client->id,'customfiled'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 密码登录
    private function passwordLogin($param)
    {
        # 验证账号
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_account_require')];
        }
        # 登录3次失败,开启图形验证码,且2个小时内操作有效
        $ip = get_client_ip();
        $key = "password_login_times_{$param['account']}_{$ip}";
        Cache::set($key,intval(Cache::get($key))+1,3600*2);

        # 图形验证码
        if ((configuration('captcha_client_login') && empty(configuration('captcha_client_login_error'))) || (configuration('captcha_client_login') && configuration('captcha_client_login_error') && Cache::get($key)>3)){
            if (!isset($param['captcha']) || empty($param['captcha'])){
                return ['status'=>400,'msg'=>lang('login_captcha')];
            }
            if (!isset($param['token']) || empty($param['token'])){
                return ['status'=>400,'msg'=>lang('login_captcha_token')];
            }
            $token = $param['token'];
            if (!check_captcha($param['captcha'],$token)){
                return ['status'=>400,'msg'=>lang('login_captcha_error')];
            }
        }
        # 邮箱登录
        if (strpos($param['account'],'@')>0){
            $result = $this->emailLogin($param);
        }else{ # 手机号登录
            $result = $this->phoneLogin($param);
        }
        # 登录成功后操作
        if ($result['status'] == 200){
            Cache::delete($key);
        }

        return $result;
    }

    # 邮箱+密码登录
    private function emailLogin($param)
    {
        # 验证邮箱账号
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_account_require')];
        }
        if (strpos($param['account'],'@')===false){
            return ['status'=>400,'msg'=>lang('login_email_error')];
        }
        # 验证记住密码
        if (!isset($param['remember_password']) || !in_array($param['remember_password'],[0,1])){
            return ['status'=>400,'msg'=>lang('login_remember_password_is_0_or_1')];
        }
        # 验证密码
        if (empty($param['password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        # 验证账号
        $email = $param['account'];
        if (empty($client = $this->checkEmailRegister($email))){
            return ['status'=>400,'msg'=>lang('login_email_is_not_register')];
        }
        # 验证密码是否相等
        if (!idcsmart_password_compare($param['password'],$client->password)){
            return ['status'=>400,'msg'=>lang('login_password_error')];
        }
        # 账号被禁用
        if ($client['status'] != 1){
            return ['status'=>400,'msg'=>lang('login_client_is_disabled')];
        }

        $this->startTrans();

        try{
            $udpate = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip(),
                'last_action_time' => time()
            ];
            $client->save($udpate);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);
            # 登录提醒
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_login',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
			add_task([
				'type' => 'email',
				'description' => '邮箱+密码登录成功,发送邮件',
				'task_data' => [
					'name'=>'client_login_success',//发送动作名称
					'client_id'=>$client->id,//客户ID
				],		
			]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('login_fail') . ':' . $e->getMessage()];
        }
        # 记住密码,保持7天登录状态;否则,2个小时内无操作退出登录
        $expired = $param['remember_password']?3600*24*7:3600*24*1;

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => intval($param['remember_password'])
        ];

        $data = [
            'jwt' => create_jwt($info,$expired)
        ];

        hook('after_client_login',['id'=>$client->id,'customfiled'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 手机+密码登录
    private function phoneLogin($param)
    {
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 验证账号
        if (empty($param['account'])){
            return ['status'=>400,'msg'=>lang('login_account_require')];
        }
        # 验证记住密码
        if (!isset($param['remember_password']) || !in_array($param['remember_password'],[0,1])){
            return ['status'=>400,'msg'=>lang('login_remember_password_is_0_or_1')];
        }
        # 验证密码
        if (empty($param['password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        # 手机号错误
        if (!check_mobile($param['phone_code']. '-' .$param['account'])){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_right')];
        }
        # 手机号未注册
        if (empty($client = $this->checkPhoneRegister($param['account'],$param['phone_code']))){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_register')];
        }
        # 验证密码是否相等
        if (!idcsmart_password_compare($param['password'],$client->password)){
            return ['status'=>400,'msg'=>lang('login_password_error')];
        }
        # 账号被禁用
        if ($client['status'] != 1){
            return ['status'=>400,'msg'=>lang('login_client_is_disabled')];
        }

        $this->startTrans();

        try{
            $udpate = [
                'last_login_time' => time(),
                'last_login_ip' => get_client_ip(),
                'last_action_time' => time()
            ];
            $client->save($udpate);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);
            # 登录提醒
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_login',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'login',$client->id);
			add_task([
				'type' => 'sms',
				'description' => '手机+密码登录成功,发送短信',
				'task_data' => [
					'name'=>'client_login_success',//发送动作名称
					'client_id'=>$client->id,//客户ID
				],		
			]);	
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('login_fail') . ':' . $e->getMessage()];
        }
        # 记住密码,保持7天登录状态;否则,2个小时内无操作退出登录
        $expired = $param['remember_password']?3600*24*7:3600*24*1;

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => intval($param['remember_password'])
        ];

        $data = [
            'jwt' => create_jwt($info,$expired)
        ];

        hook('after_client_login',['id'=>$client->id,'customfiled'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 检查手机注册用户是否存在并返回用户数据
    private function checkPhoneRegister($phone,$phone_code)
    {
        $client = $this->where('phone',$phone)
            ->where('phone_code',$phone_code)
            ->find();
        return $client;
    }

    # 检查邮箱注册用户是否存在并返回用户数据
    private function checkEmailRegister($email)
    {
        $client = $this->where('email',$email)->find();
        return $client;
    }

    # 获取手机验证码
    private function getPhoneVerificationCode($phone,$phone_code,$action='login')
    {
        return Cache::get('verification_code_'.$action . '_' . $phone_code. '_' . $phone);
    }

    # 获取邮箱验证码
    private function getEmailVerificationCode($email,$action='register')
    {
        return Cache::get('verification_code_'.$action.'_'.$email);
    }

    # 清除手机验证码
    private function clearPhoneVerificationCode($phone,$phone_code,$action='login')
    {
        return Cache::delete('verification_code_'.$action . '_' . $phone_code. '_' . $phone);
    }

    # 清除邮箱验证码
    private function clearEmailVerificationCode($email,$action='register')
    {
        return Cache::delete('verification_code_'.$action.'_'.$email);
    }

    # 手机号注册
    private function phoneRegister($param)
    {
        # 未开启手机注册
        if (!configuration('register_phone')){
            return ['status'=>400,'msg'=>lang('register_phone_is_not_open')];
        }
        # 验证手机
        if (!check_mobile($param['account'])){
            return ['status'=>400,'msg'=>lang('please_enter_vaild_phone')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 验证用户名
        if (strlen($param['username'])>20){
            return ['status'=>400,'msg'=>lang('client_name_cannot_exceed_20_chars')];
        }
        # 验证码
        if (!isset($param['code']) || empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($param['account'],$param['phone_code'],'register');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (!empty($this->checkPhoneRegister($param['account'],$param['phone_code']))){
            return ['status'=>400,'msg'=>lang('phone_has_been_registered')];
        }

        $this->startTrans();

        try{
            $time = time();
            $client = $this->create([
                'username' => $param['username']?:$param['account'],
                'phone_code' => $param['phone_code'],
                'phone' => $param['account'],
                'password' => idcsmart_password($param['password']),
                'last_login_time' => $time,
                'last_login_ip' => get_client_ip(),
                'last_action_time' => $time,
                'language' => configuration('lang_home')??'zh-cn',
                'create_time' => $time
            ]);

            $this->clearPhoneVerificationCode($param['account'],$param['phone_code'],'register');

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_register',['{account}'=>$param['account']]),'client',$client->id);
			
			//注册成功发送短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => '短信注册成功,发送短信',
				'task_data' => [
					'name'=>'client_register_success',//发送动作名称
					'phone_code' => $param['phone_code'],
					'phone' => $param['account'],
					'client_id'=>$client->id,//客户ID
				],		
			]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('register_fail') . ':' . $e->getMessage()];
        }

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        $expired = 3600*24*1;

        $data = [
            'jwt' => create_jwt($info,$expired)
        ];

        hook('after_client_register',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('register_success'),'data'=>$data];

    }

    # 邮箱注册
    private function emailRegister($param)
    {
        # 未开启邮箱注册
        if (!configuration('register_email')){
            return ['status'=>400,'msg'=>lang('register_email_is_not_open')];
        }
        # 验证邮箱
        if (strpos($param['account'],'@')===false){
            return ['status'=>400,'msg'=>lang('login_email_error')];
        }
        # 验证用户名
        if (strlen($param['username'])>20){
            return ['status'=>400,'msg'=>lang('client_name_cannot_exceed_20_chars')];
        }
        # 验证码
        if (configuration('code_client_email_register')){
            if (!isset($param['code']) || empty($param['code'])){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            $code = $this->getEmailVerificationCode($param['account']);
            if (empty($code)){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            if ($param['code'] != $code){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (!empty($this->checkEmailRegister($param['account']))){
            return ['status'=>400,'msg'=>lang('email_has_been_registered')];
        }

        $this->startTrans();

        try{
            $time = time();
            $client = $this->create([
                'username' => $param['username']?:(explode('@',$param['account'])[0]?:$param['account']),
                'email' => $param['account'],
                'password' => idcsmart_password($param['password']),
                'last_login_time' => $time,
                'last_login_ip' => get_client_ip(),
                'last_action_time' => $time,
                'language' => configuration('lang_home')??'zh-cn',
                'create_time' => $time
            ]);

            $this->clearEmailVerificationCode($param['account']);

            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($client->id);

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('log_client_register',['{account}'=>$param['account']]),'client',$client->id);
			
			//注册成功发送邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => '邮件注册成功,发送邮件',
				'task_data' => [
					'name'=>'client_register_success',//发送动作名称
					'email' => $param['account'],
					'client_id'=>$client->id,//客户ID
				],		
			]);
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('register_fail') . ':' . $e->getMessage()];
        }

        $info = [
            'id' => $client->id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        $expired = 3600*24*1;

        $data = [
            'jwt' => create_jwt($info,$expired)
        ];

        hook('after_client_register',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('register_success'),'data'=>$data];
    }

    # 手机重置密码
    private function phonePasswordReset($param)
    {
        # 验证手机
        if (!check_mobile($param['account'])){
            return ['status'=>400,'msg'=>lang('please_enter_vaild_phone')];
        }
        # 区号未填
        if (empty($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_require')];
        }
        $CountryModel = new CountryModel();
        # 区号错误
        if (!$CountryModel->checkPhoneCode($param['phone_code'])){
            return ['status'=>400,'msg'=>lang('login_phone_code_error')];
        }
        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($param['account'],$param['phone_code'],'password_reset');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (empty($client = $this->checkPhoneRegister($param['account'],$param['phone_code']))){
            return ['status'=>400,'msg'=>lang('login_phone_is_not_register')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearPhoneVerificationCode($param['account'],$param['phone_code'],'password_reset');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        hook('after_client_password_reset',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 邮箱重置密码
    private function emailPasswordReset($param)
    {
        # 验证邮箱
        if (strpos($param['account'],'@')===false){
            return ['status'=>400,'msg'=>lang('login_email_error')];
        }
        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getEmailVerificationCode($param['account'],'password_reset');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        # 验证密码
        if (empty($param['password']) || empty($param['re_password'])){
            return ['status'=>400,'msg'=>lang('login_password_require')];
        }
        if (strlen($param['password'])<6 || strlen($param['password'])>32){
            return ['status'=>400,'msg'=>lang('login_password_len')];
        }
        if ($param['password'] != $param['re_password']){
            return ['status'=>400,'msg'=>lang('passwords_not_match')];
        }
        # 账号是否已注册
        if (empty($client = $this->checkEmailRegister($param['account']))){
            return ['status'=>400,'msg'=>lang('login_email_is_not_register')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearEmailVerificationCode($param['account'],'password_reset');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 发送邮件短信
            # 记录日志
            # 赋值,方便记日志
            $request = request();
            $request->client_id = $client->id;
            $request->client_name = $client['username'];
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        hook('after_client_password_reset',['id'=>$client->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 手机重置密码
    private function phonePasswordUpdate($param)
    {
        $client = $this->where('status', 1)->find($param['id']);
        if(empty($client)){
            return ['status'=>400,'msg'=>lang('fail_message')];
        }

        # 验证手机
        if(empty($client['phone'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_phone')];
        }

        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getPhoneVerificationCode($client['account'],$client['phone_code'],'verify');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearPhoneVerificationCode($client['phone'],$client['phone_code'],'verify');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 发送邮件短信
            //客户更改密码发送邮件添加到任务队列
            if($client['email']){
                add_task([
                    'type' => 'email',
                    'description' => '客户更改密码成功,发送邮件',
                    'task_data' => [
                        'name'=>'client_change_password',//发送动作名称
                        'email' => $client['email'],
                        'client_id'=>$client['id'],//客户ID
                        'template_param'=>[
                            'client_password' => $param['password'],//新密码
                        ],
                    ],      
                ]);
            }
            //客户更改密码发送短信添加到任务队列
            if($client['phone']){
                add_task([
                    'type' => 'sms',
                    'description' => '客户更改密码成功,发送短信',
                    'task_data' => [
                        'name'=>'client_change_password',//发送动作名称
                        'phone_code' => $client['phone_code'],
                        'phone' => $client['phone'],
                        'client_id'=>$client['id'],//客户ID
                        'template_param'=>[
                            'client_password' => $param['password'],//新密码
                        ],
                    ],      
                ]);
            }

            # 记录日志
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 邮箱重置密码
    private function emailPasswordUpdate($param)
    {
        $client = $this->where('status', 1)->find($param['id']);
        if(empty($client)){
            return ['status'=>400,'msg'=>lang('fail_message')];
        }

        if(empty($client['email'])){
            return ['status'=>400, 'msg'=>lang('user_not_bind_email')];
        }

        # 验证码
        if (empty($param['code'])){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        $code = $this->getEmailVerificationCode($client['email'],'verify');
        if (empty($code)){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }
        if ($param['code'] != $code){
            return ['status'=>400,'msg'=>lang('verification_code_error')];
        }

        $this->startTrans();

        try{
            $client->save([
                'update_time' => time(),
                'password' => idcsmart_password($param['password'])
            ]);

            $this->clearEmailVerificationCode($client['email'],'verify');

            Cache::set('home_update_password_'.$client->id,time(),3600*24*365); # 365天未操作接口,就可以不退出

            # 发送邮件短信
            //客户更改密码发送邮件添加到任务队列
            if($client['email']){
                add_task([
                    'type' => 'email',
                    'description' => '客户更改密码成功,发送邮件',
                    'task_data' => [
                        'name'=>'client_change_password',//发送动作名称
                        'email' => $client['email'],
                        'client_id'=>$client['id'],//客户ID
                        'template_param'=>[
                            'client_password' => $param['password'],//新密码
                        ],
                    ],      
                ]);
            }
            //客户更改密码发送短信添加到任务队列
            if($client['phone']){
                add_task([
                    'type' => 'sms',
                    'description' => '客户更改密码成功,发送短信',
                    'task_data' => [
                        'name'=>'client_change_password',//发送动作名称
                        'phone_code' => $client['phone_code'],
                        'phone' => $client['phone'],
                        'client_id'=>$client['id'],//客户ID
                        'template_param'=>[
                            'client_password' => $param['password'],//新密码
                        ],
                    ],      
                ]);
            }

            # 记录日志
            active_log(lang('change_password',['{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'client',$client->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-30
     * @title 以用户登录
     * @desc 以用户登录
     * @author wyh
     * @version v1
     * @param int id - 用户ID
     * @return string data.jwt - jwt:获取后放在请求头Authorization里,拼接成如下格式:Bearer yJ0eX.test.ste
     */
    public function loginByClient($id)
    {
        $client = $this->find($id);
        if (empty($client)){
            return ['status'=>400,'msg'=>lang('client_is_not_exist')];
        }

        $expired = 3600*24*1;

        $info = [
            'id' => $id,
            'name' => $client->username,
            'remember_password' => 0
        ];

        $data = [
            'jwt' => create_jwt($info,$expired)
        ];

        $ClientLoginModel = new ClientLoginModel();
        $ClientLoginModel->clientLogin($id);

        # 记录日志
        active_log(lang('log_login_by_client',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{client}'=>'client#'.$client->id.'#'.$client->username.'#']),'admin',get_admin_id());

        return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
    }

    # 判断是否新客户
    public function newClient($id)
    {
        if (empty($id)){
            return false;
        }

        $client = $this->find($id);
        if (empty($client)){
            return false;
        }

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->leftJoin('order_item oi','oi.host_id=h.id')
            ->leftJoin('order o','o.id=oi.order_id')
            ->where('h.client_id',$id)
            ->where('oi.type','host')
            ->where('o.status','<>','Unpaid')
            ->find();

        if (!empty($host)){
            return false;
        }

        $OrderModel = new OrderModel();
        $total = $OrderModel->where('client_id',$id)
            ->where('status','Paid')
            ->whereNotIn('type',['recharge','artificial'])
            ->sum('amount');
        if ($total>0){
            return false;
        }

        return true;
    }


    /**
     * 时间 2022-09-16
     * @title 最近访问用户列表
     * @desc 最近访问用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/index/visit_client
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 用户列表
     * @return int list[].id - ID
     * @return int list[].username - 姓名
     * @return int list[].email - 邮箱
     * @return int list[].phone_code - 国际电话区号
     * @return int list[].phone - 手机号
     * @return int list[].company - 公司
     * @return int list[].visit_time - 访问时间
     * @return int count - 用户总数
     */
    public function visitClientList($param)
    {

        $clients = $this->field('id,username,email,phone_code,phone,company,last_action_time')
            ->where('status', 1)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('last_action_time', 'desc')
            ->select()
            ->toArray();

        $count = $this->field('id')
            ->where('status', 1)
            ->count();

        $time = time();
        foreach ($clients as $key => $value) {
            $visitTime = $time - $value['last_action_time'];
            if($visitTime>365*24*3600){
                $clients[$key]['visit_time'] = lang('one_year_ago');
            }else{
                $day = floor($visitTime/(24*3600));
                $visitTime = $visitTime%(24*3600);
                $hour = floor($visitTime/3600);
                $visitTime = $visitTime%60;
                $minute = floor($visitTime/60);
                $clients[$key]['visit_time'] = ($day>0 ? $day.lang('day') : '').($hour>0 ? $hour.lang('hour') : '').($minute>0 ? $minute.lang('minute') : '');
                $clients[$key]['visit_time'] = !empty($clients[$key]['visit_time']) ? $clients[$key]['visit_time'].lang('ago') : $minute.lang('minute').lang('ago');
            }
            unset($clients[$key]['last_action_time']);
        }

        return ['list'=>$clients, 'count'=>$count];
    }
}
