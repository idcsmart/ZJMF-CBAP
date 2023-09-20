<?php
namespace reserver\mf_finance_dcim\middleware;

use think\Request;

/**
 * @title hook验证
 * @desc hook验证
 * @use reserver\mf_finance_dcim\middleware\CheckAuthMiddleware
 */
class CheckAuthMiddleware
{
    public function handle($request,\Closure $next)
    {
        $result = $this->checkAuth($request);

        if ($result['status'] == 400){
            return json($result);
        }
        
        return $next($request);
    }

    
    public function checkAuth(Request $request){
        $route = $request->rule()->getName();
        $route = explode('@', $route);
        $action = $route[1] ?? '';

        // 需要验证的方法对应权限关系
        $auth = [
            'on'                        => 'on',
            'off'                       => 'off',
            'reboot'                    => 'reboot',
            'vnc'                       => 'vnc',
            'reinstall'                 => 'reinstall',
            'rescue'                    => 'rescue',
            'resetPassword'             => 'reset_password',
        ];

        if(isset($auth[$action])){
            $client_id = get_client_id(false);

            $result = hook('home_check_access', ['rule' => $auth[$action], 'client_id' => $client_id]);
            $result = array_values(array_filter($result ?? []));
            $res = true;
            foreach ($result as $key => $value) {
                if(isset($value['status'])){
                    if($value['status']==200){
                        // $res = true;
                    }else{
                        $res = false;
                    }
                }
            }

            if(!$res){
                return ['status'=>400, 'msg'=>'没有权限'];
            }
        }
        return ['status'=>200];
    }
    
        
    

}