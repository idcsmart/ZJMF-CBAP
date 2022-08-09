<?php
namespace app\http\middleware;

use app\admin\model\AdminLoginModel;
use app\admin\model\AdminModel;

/**
 * @title 后台授权检查
 * @desc 后台授权检查
 * @use app\http\middleware\CheckAdmin
 */
class CheckAdmin extends Check
{
    public function handle($request, \Closure $next)
    { 
        $result = parent::checkToken($request,true);
        if ($result['status'] != 200){
            return json($result);
        }

        $jwtToken = $result['data']['jwt_token'];

        $request->admin_id = $jwtToken['id'];
        $request->admin_name = $jwtToken['name'];
        $request->admin_remember_password = $jwtToken['remember_password'];
        # $request->admin_jwt = $jwtToken['jwt'];

        $time = time();
        $AdminModel = new AdminModel();

        $admin = $AdminModel->find($request->admin_id);
        # 用户不存在
        if (empty($admin)){
            return json(['status'=>401,'msg'=>lang('login_unauthorized')]);
        }
        # 管理员被禁用
        if ($request->admin_id && $admin->status != 1){
            return json(['status'=>401,'msg'=>lang('login_unauthorized') . ':' . lang('admin_is_disabled')]);
        }
        # 不记住密码 2个小时未操作自动退出登录
        $AdminLoginModel = new AdminLoginModel();
        $adminLogin = $AdminLoginModel->where('admin_id',$request->admin_id)
            ->where('last_login_ip',get_client_ip())
            ->find();

        if ($request->admin_remember_password == 0 && $adminLogin && ($adminLogin->last_action_time+config('idcsmart.auto_logout'))<$time){
            return json(['status'=>401,'msg'=>lang('login_unauthorized') . ':' . lang('log_out_automatically_after_2_hours_without_operation')]);
        }
        # 记录操作时间(仅记录登录后的接口操作的时间)
        $admin->last_action_time = $time;
        $admin->save();

        $adminLogin->last_action_time = $time;
        $adminLogin->save();

        return $next($request);
    }
}