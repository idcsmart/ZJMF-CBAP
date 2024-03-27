<?php
namespace app\http\middleware;

/*
 * @title 拒绝重复请求中间件
 * @desc 拒绝重复请求中间件，若请求方式为POST或者PUT的接口需要防止重复提交，可以使用此中间件，接口3秒内不可重复提交
 * @use app\http\middleware\RejectRepeatRequest
 * @author wyh
 * */
class RejectRepeatRequest
{
    // 3s内，不可重复提交
    private $timeout = 3;

    public function handle($request,\Closure $next)
    {
        if ($request->isPost() || $request->isPut()){
            $param = $request->param();

            $token=md5(json_encode($param));

            $key = "idcsmart_repeat_request_post_".get_client_ip() . '_' . $request->url();

            if($token==\idcsmart_cache($key)){
                return json(['status'=>400,'msg'=>lang("repeat_message")]);
            }

            \idcsmart_cache($key,$token,$this->timeout);
        }

        return $next($request);
    }
}