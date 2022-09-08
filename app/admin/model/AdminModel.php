<?php
namespace app\admin\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 管理员模型
 * @desc 管理员模型
 * @use app\admin\model\AdminModel
 */
class AdminModel extends Model
{
    protected $name = 'admin';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'nickname'        => 'string',
        'name'            => 'string',
        'password'        => 'string',
        'email'           => 'string',
        'status'          => 'int',
        'last_login_time' => 'int',
        'last_login_ip'   => 'string',
        'last_action_time'=> 'int',
        'create_time'     => 'int',
        'update_time'     => 'int',
    ];

    /**
     * 时间 2022-5-10
     * @title 管理员列表
     * @desc 管理员列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,nickname,name,email
     * @param string sort - 升/降序 asc,desc
     * @return array list - 管理员列表
     * @return int list[].id - ID
     * @return int list[].nickname - 名称
     * @return int list[].name - 用户名
     * @return int list[].email - 邮箱
     * @return int list[].roles - 分组名称
     * @return int list[].status - 状态;0:禁用,1:正常
     * @return int count - 管理员总数
     */
    public function adminList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','nickname','email'])){
            $param['orderby'] = 'a.id';
        }else{
            $param['orderby'] = 'a.'.$param['orderby'];
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('a.id|a.nickname|a.name|a.email', 'like', "%{$param['keywords']}%");
            }
        };

        $admins = $this->alias('a')
            ->field('a.id,a.nickname,a.name,a.email,a.status,group_concat(ar.name) as roles')
            ->leftjoin('admin_role_link arl','a.id=arl.admin_id')
            ->leftjoin('admin_role ar','arl.admin_role_id=ar.id')
            ->where($where)
            ->group('a.id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        $count = $this->alias('a')
            ->field('a.id,a.nickname,a.name,a.email,a.status,group_concat(ar.name) as roles')
            ->leftjoin('admin_role_link arl','a.id=arl.admin_id')
            ->leftjoin('admin_role ar','arl.admin_role_id=ar.id')
            ->where($where)
            ->count();

        return ['list'=>$admins,'count'=>$count];
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员
     * @desc 获取单个管理员
     * @author wyh
     * @version v1
     * @param int id - 管理员分组ID required
     * @return int id - ID
     * @return string nickname - 名称
     * @return string name - 用户名
     * @return string email - 邮箱
     * @return string role_id - 分组ID
     * @return string roles - 所属分组,逗号分隔
     * @return string status - 状态;0:禁用;1:正常
     */
    public function indexAdmin($id)
    {
        $admin = $this->alias('a')
            ->field('a.id,a.nickname,a.name,a.email,a.status,ar.id as role_id,group_concat(ar.name) as roles')
            ->leftJoin('admin_role_link arl','a.id=arl.admin_id')
            ->leftJoin('admin_role ar','ar.id=arl.admin_role_id')
            ->where('a.id',$id)
            ->group('a.id')
            ->find($id);
        return $admin?:(object)[];
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员
     * @desc 添加管理员
     * @author wyh
     * @version v1
     * @param string param.name 测试员 用户名 required
     * @param string param.password 123456 密码 required
     * @param string param.repassword 123456 重复密码 required
     * @param string param.email 123@qq.com 邮箱 required
     * @param string param.nickname 小华 名称 required
     * @param string param.role_id 1 分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createAdmin($param)
    {
        $adminRole = AdminRoleModel::find(intval($param['role_id']));
        if (empty($adminRole)){
            return ['status'=>400,'msg'=>lang('admin_role_is_not_exist')];
        }

        $this->startTrans();
        try{
            $admin = $this->create([
                'name' => $param['name']?:'',
                'password' => idcsmart_password($param['password']),
                'email' => $param['email']?:'',
                'nickname' => $param['nickname']?:'',
                'status' => isset($param['status'])?intval($param['status']):1,
                'create_time' => time(),
            ]);

            AdminRoleLinkModel::create([
                'admin_role_id' => intval($param['role_id']),
                'admin_id' => $admin->id,
            ]);

            # 记录日志
            active_log(lang('log_create_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name']]),'admin',$admin->id);
			add_task([
				'type' => 'email',
				'description' => '超级管理员添加后台管理员,发送邮件',
				'task_data' => [
					'name'=>'admin_create_account',//发送动作名称
					'email'=>$param['email'],
					'template_param'=>[
						'admin_name'=>$param['name'],
						'admin_password'=>$param['password'],
					]
				],		
			]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        hook('after_admin_create',['name'=>$param['name']??'','password'=>$param['password']??'','email'=>$param['email']??'',
            'nickname'=>$param['nickname']??'','status'=>$param['status']??'','role_id'=>$param['role_id']??'','customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('create_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员
     * @desc 修改管理员
     * @author wyh
     * @version v1
     * @param string param.id 1 管理员ID required
     * @param string param.name 测试员 用户名 required
     * @param string param.password 123456 密码 required
     * @param string param.repassword 123456 重复密码 required
     * @param string param.email 123@qq.com 邮箱 required
     * @param string param.nickname 小华 名称 required
     * @param string param.role_id 1 分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAdmin($param)
    {
        $admin = $this->find(intval($param['id']));
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $adminRole = AdminRoleModel::find(intval($param['role_id']));
        if (empty($adminRole)){
            return ['status'=>400,'msg'=>lang('admin_role_is_not_exist')];
        }
        # 修改密码 强制退出登录
        if(!empty($param['password'])){
            if (!idcsmart_password_compare($param['password'],$admin['password'])){
                Cache::set('admin_update_password_'.$param['id'],time(),3600*24*7); # 7天未操作接口,就可以不退出
            }
        }

        $oldRoleId = AdminRoleLinkModel::where('admin_id',intval($param['id']))->value('admin_role_id');
        if ($oldRoleId!=$param['role_id'] && $param['id']==1){
            return ['status'=>400,'msg'=>lang('supper_admin_cannot_update_role')];
        }

        # 日志详情
        $description = '';
        if ($admin['name'] != $param['name']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_name'),'{content}'=>$param['name']]) .',';
        }
        if(!empty($param['password'])){
            if ($admin['password'] != idcsmart_password($param['password'])){
                $description .= lang('log_update_admin_description',['{field}'=>lang('admin_password'),'{content}'=>$param['password']]) .',';
            }
        }
        if ($admin['email'] != $param['email']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_email'),'{content}'=>$param['email']]) .',';
        }
        if ($admin['nickname'] != $param['nickname']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_nickname'),'{content}'=>$param['nickname']]) .',';
        }
        if ($admin['status'] != $param['status']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_status'),'{content}'=>$param['status']]) .',';
        }
        if ($oldRoleId != $param['role_id']){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_role_id'),'{content}'=>$param['role_id']]);
        }

        $this->startTrans();
        try{
            $update=[
                'name' => $param['name'],
                'email' => $param['email']?:'',
                'nickname' => $param['nickname']?:'',
                'status' => isset($param['status'])?intval($param['status']):1,
                'update_time' => time(),
            ];
            if(!empty($param['password'])){
                $update['password']=idcsmart_password($param['password']);
            }
            $this->update($update,['id'=>intval($param['id'])]);

            # 删除原关联
            AdminRoleLinkModel::where('admin_id',intval($param['id']))->delete();

            AdminRoleLinkModel::create([
                'admin_role_id' => intval($param['role_id']),
                'admin_id' => intval($param['id']),
            ]);

            # 记录日志
            active_log(lang('log_update_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name'],'{description}'=>$description]),'admin',$admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        hook('after_admin_edit',['name'=>$param['name']??'','password'=>$param['password']??'','email'=>$param['email']??'',
            'nickname'=>$param['nickname']??'','status'=>$param['status']??'','role_id'=>$param['role_id']??'','customfield'=>$param['customfield']??[]]);


        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员
     * @desc 删除管理员
     * @author wyh
     * @version v1
     * @param int id 1 管理员ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteAdmin($param)
    {

        $id = $param['id']??0;

        # 超级管理员不可删除
        if ($id == 1){
            return ['status'=>400,'msg'=>lang('super_admin_cannot_delete')];
        }

        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);

            AdminRoleLinkModel::where('admin_id',$id)->delete();
            # 记录日志
            active_log(lang('admin_delete_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',$admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        hook('before_admin_delete',['id'=>$id]);

        return ['status'=>200,'msg'=>lang('delete_success')];
    }

    /**
     * 时间 2022-5-11
     * @title 管理员状态切换
     * @desc 管理员状态切换
     * @author wyh
     * @version v1
     * @param int param.id 1 管理员ID required
     * @param int param.status 1 状态:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function status($param)
    {
        # 超级管理员不可操作
        if (intval($param['id']) == 1){
            return ['status'=>400,'msg'=>lang('super_admin_cannot_opreate')];
        }

        $admin = $this->find(intval($param['id']));
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $status = intval($param['status']);

        if ($admin['status'] == $status){
            return ['status'=>400,'msg'=>lang('cannot_repeat_opreate')];
        }

        try{
            $this->update([
                'status' => $status,
                'update_time' => time(),
            ],['id'=>intval($param['id'])]);
        }catch (\Exception $e){
            if ($status == 0){
                return ['status'=>400,'msg'=>lang('disable_fail')];
            }else{
                return ['status'=>400,'msg'=>lang('enable_fail')];
            }
        }

        if ($status == 0){
            # 记录日志
            active_log(lang('admin_disable_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',$admin->id);
            return ['status'=>200,'msg'=>lang('disable_success')];
        }else{
            # 记录日志
            active_log(lang('admin_enable_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',$admin->id);
            return ['status'=>200,'msg'=>lang('enable_success')];
        }

    }

    /**
     * 时间 2022-5-13
     * @title 后台登录
     * @desc 后台登录
     * @author wyh
     * @version v1
     * @param string param.name 测试员 用户名 required
     * @param string param.password 123456 密码 required
     * @param string param.remember_password 1 是否记住密码(1是,0否) required
     * @param string token d7e57706218451cbb23c19cfce583fef 验证码唯一识别码(开启登录图形验证码开关时传此参数)
     * @param string captcha 12345 验证码(开启登录图形验证码开关时传此参数)
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return object data - 返回数据
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function login($param)
    {
        # 图形验证码
        if (configuration('captcha_admin_login')){
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

        $name = $param['name'];

        $password = $param['password'];

        $rememberPassword = $param['remember_password'];

        if (strpos($name,"@")>0){
            $where['email'] = $name;
        }else{
            $where['name'] = $name;
        }

        $admin = $this->where($where)->find();

        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_name_or_password_error')];
        }

        if ($admin['status'] == 0){
            return ['status'=>400,'msg'=>lang('admin_is_disabled')];
        }

        if (idcsmart_password_compare($password,$admin['password'])){

            $ip = get_client_ip(0,true);

            $this->startTrans();
            try{
                $this->update([
                    'last_login_ip' => $ip,
                    'last_login_time' => time(),
                    'last_action_time' => time()
                ],$where);

                $AdminLoginModel = new AdminLoginModel();
                $AdminLoginModel->adminLogin($admin->id);

                // 获取数据库的权限
                $AuthRuleModel = new AuthRuleModel();
                $auth = $AuthRuleModel->getAdminAuthRule($admin->id);
                Cache::set('admin_auth_rule_'.$admin->id, json_encode($auth),7200);

                # 邮件提醒
                # 记录日志,赋值
                $request = request();
                $request->admin_id = $admin->id;
                $request->admin_name = $admin['name'];
                active_log(lang('log_admin_login',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);

                $this->commit();
            }catch (\Exception $e){
                $this->rollback();
                return ['status'=>400,'msg'=>lang('login_fail') . ":" .  $e->getMessage()];
            }

            # 创建jwt
            $adminInfo = [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'remember_password' => $rememberPassword,
                'is_admin' => true # 后台
            ];

            if ($rememberPassword == 1){
                $expired = 3600*24*7; # 7天退出登录
            }else{
                $expired = 3600*24*1; # 最多1天退出
            }

            $data = [
                'jwt' => create_jwt($adminInfo,$expired,true)
            ];

            hook('after_admin_login',['id'=>$admin->id,'customfield'=>$param['customfield']??[]]);

            return ['status'=>200,'msg'=>lang('login_success'),'data'=>$data];
        }else{
            return ['status'=>400,'msg'=>lang('admin_name_or_password_error')];
        }

    }

    /**
     * 时间 2022-5-13
     * @title 注销
     * @desc 注销
     * @author wyh
     * @version v1
     */
    public function logout($param)
    {
        $adminId = get_admin_id();

        $admin = $this->find($adminId);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        $jwt = get_header_jwt();

        Cache::set('login_token_'.$jwt,null);

        # 记录日志
        active_log(lang('log_admin_logout',['{admin}'=>'admin#'.$admin->id.'#'.$admin['name'].'#']),'admin',$admin->id);

        hook('after_admin_logout',['id'=>$adminId,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('logout_success')];

    }

    /**
     * 时间 2022-9-7
     * @title 修改管理员密码
     * @desc 修改管理员密码
     * @author wyh
     * @version v1
     * @param string param.password 123456 密码 required
     * @param string param.repassword 123456 重复密码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAdminPassword($param)
    {
        $admin = $this->find(get_admin_id());
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_is_not_exist')];
        }

        # 修改密码 强制退出登录
        if (!idcsmart_password_compare($param['password'],$admin['password'])){
            Cache::set('admin_update_password_'.get_admin_id(),time(),3600*24*7); # 7天未操作接口,就可以不退出
        }else{
            return ['status'=>400,'msg'=>lang('admin_password_is_same')];
        }

        # 日志详情
        $description = '';
        if ($admin['password'] != idcsmart_password($param['password'])){
            $description .= lang('log_update_admin_description',['{field}'=>lang('admin_password'),'{content}'=>$param['password']]) .',';
        }

        $this->startTrans();
        try{
            $update['password']=idcsmart_password($param['password']);
            $this->update($update,['id'=>get_admin_id()]);
            # 记录日志
            active_log(lang('log_update_admin',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>request()->admin_name,'{description}'=>$description]),'admin',$admin->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail') . ':' . $e->getMessage()];
        }

        return ['status'=>401,'msg'=>lang('update_success')];
    }
}