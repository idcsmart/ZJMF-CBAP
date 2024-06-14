<?php
namespace app\http\middleware;

use app\common\model\ClientModel;

/*
 * @title 前台需要操作密码验证的中间件
 * @desc  前台需要操作密码验证的中间件,排除上下游请求
 * @use   app\http\middleware\CheckClientOperatePassword
 * @author hh
 * */
class CheckClientOperatePassword
{
    public function handle($request,\Closure $next)
    {
    	// 下游请求不验证
    	if($request->is_api){
    		return $next($request);
    	}

        $clientId = get_client_id();
        
        $homeEnforceSafeMethod = configuration(['home_enforce_safe_method']);
        $homeEnforceSafeMethod = !empty($homeEnforceSafeMethod) ? explode(',', $homeEnforceSafeMethod) : [];
        if(in_array('operate_password', $homeEnforceSafeMethod)){
            $operatePassword = request()->param('client_operate_password');

            if(idcsmart_password((string)$operatePassword) !== ClientModel::where('id', $clientId)->value('operate_password')){
                return json(['status'=>400, 'msg'=>lang('operate_password_error')]);
            }
        }
        return $next($request);
    }
}