<?php
namespace app\http\middleware;

/*
 * 常用参数过滤器,并设置全局参数limit,page,sort
 * */
class ParamFilter
{
	protected $cookieDomain;

    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];
    
    public function handle($request,\Closure $next)
    {
    	$header = !empty($header) ? array_merge($this->header, $header) : $this->header;

        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request->header('origin');

            if ($origin && ('' == $this->cookieDomain || strpos($origin, $this->cookieDomain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }

        $param = $request->param();

        $request->page = isset($param['page']) ? intval($param['page']):config('idcsmart.page');

        $request->limit  = isset($param['limit']) ?intval($param['limit']) : config('idcsmart.limit');

        $request->sort = isset($param['sort'])?(in_array($param['sort'],['desc','asc'])?$param['sort']:'desc'):'desc';

        return $next($request)->header($header);
    }
}