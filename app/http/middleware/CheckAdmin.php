<?php
namespace app\http\middleware;

use app\admin\model\AdminLoginModel;
use app\admin\model\AdminModel;
use think\db\Query;

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

        # 登录ip不一致 wyh 之后去掉后面的说明,目前方便测试找原因
        $checkIp = configuration('admin_login_check_ip')??0;
        if($checkIp && get_client_ip() !== $jwtToken['ip']){
            return json(['status' => 401,'msg' => lang('login_unauthorized') . ':' . lang('inconsistent_login_ip')]);
        }

        # 不记住密码 2个小时未操作自动退出登录(未开启检查ip,此功能无效)
        $AdminLoginModel = new AdminLoginModel();
        $where = function (Query $query)use($checkIp){
            if ($checkIp){ # 使用CDN时,客户端ip一直变化的问题,检查ip,才判断
                $query->where('last_login_ip',get_client_ip());
            }
        };
        $adminLogin = $AdminLoginModel->where('admin_id',$request->admin_id)
            ->where($where)
            ->order('create_time','desc') # 获取最新一条数据
            ->find();
        if (empty($adminLogin)){
            return json(['status'=>401,'msg'=>lang('login_unauthorized')]);
        }

        if ($checkIp && $request->admin_remember_password == 0 && $adminLogin && ($adminLogin->last_action_time+config('idcsmart.auto_logout'))<$time){
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