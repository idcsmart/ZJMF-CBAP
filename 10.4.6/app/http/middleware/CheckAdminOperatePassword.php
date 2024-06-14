<?php
namespace app\http\middleware;

use app\admin\model\AdminModel;

/*
 * @title 后台需要操作密码验证的中间件
 * @desc  后台需要操作密码验证的中间件
 * @use   app\http\middleware\CheckAdminOperatePassword
 * @author hh
 * */
class CheckAdminOperatePassword
{
    public function handle($request,\Closure $next)
    {
        $adminId = get_admin_id();
        
        $adminEnforceSafeMethod = configuration(['admin_enforce_safe_method']);
        $adminEnforceSafeMethod = !empty($adminEnforceSafeMethod) ? explode(',', $adminEnforceSafeMethod) : [];
        if(in_array('operate_password', $adminEnforceSafeMethod)){
            $operatePassword = request()->param('admin_operate_password');

            if(idcsmart_password((string)$operatePassword) !== AdminModel::where('id', $adminId)->value('operate_password')){
                return json(['status'=>400, 'msg'=>lang('operate_password_error')]);
            }
        }
        return $next($request);
    }
}