<?php
namespace app\http\middleware;

use app\admin\model\AdminLoginModel;
use app\common\model\ClientLoginModel;
use app\common\model\ClientModel;
use think\Request;
use think\facade\Cache;

/**
 * @title 授权检查cookie前台
 * @desc 授权检查cookie前台,用于模板路由获取用户
 * @use app\http\middleware\CheckClientCookieJwt
 */
class CheckClientCookieJwt
{
    public function handle($request,\Closure $next)
    {
        $result = $this->checkToken($request);

        if ($result['status'] == 200){
            $jwtToken = $result['data']['jwt_token'];
            $request->client_id = $jwtToken['id'];
            $request->client_name = $jwtToken['name'];
            $request->client_remember_password = $jwtToken['remember_password'];

            // if (isset($jwtToken['is_api']) && $jwtToken['is_api']){ // 兼容不需要登录的接口
            //     $request->is_api = $jwtToken['is_api'];
            //     $request->api_id = $jwtToken['api_id']??0;
            //     $request->api_name = $jwtToken['api_name']??'';
            // }
            $time = time();
            $ClientModel = new ClientModel();

            $client = $ClientModel->find($request->client_id);
            # 用户不存在
            if (empty($client)){
                $request->client_id = 0;
                return $next($request);
            }
            # 用户被禁用
            if ($request->client_id && $client->status != 1){
                $request->client_id = 0;
                return $next($request);
            }
            # 维护模式
            if (configuration("maintenance_mode")){
                $request->client_id = 0;
                return $next($request);
            }

            # 登录ip不一致 wyh 之后去掉后面的说明,目前方便测试找原因
            $checkIp = configuration('home_login_check_ip')??0;
            if($checkIp && get_client_ip() !== $jwtToken['ip']){
                $request->client_id = 0;
                return $next($request);
            }

            
            # 不记住密码 2个小时未操作自动退出登录(该账号某个ip登录时的操作时间进行判断)(未开启检查ip,此功能无效)
            $ClientLoginModel = new ClientLoginModel();
            $where = function ($query)use ($checkIp){
                if ($checkIp){ # 使用CDN,不检查IP
                    $query->where('last_login_ip',get_client_ip());
                }
            };
            $clientLogin = $ClientLoginModel->where('client_id',$request->client_id)
                ->where($where)
                ->order('create_time','desc')
                ->find();
            if (empty($clientLogin)){
                $request->client_id = 0;
                return $next($request);
            }

            if ($checkIp && $request->client_remember_password == 0 && ($clientLogin->last_action_time+config('idcsmart.auto_logout'))<$time){
                $request->client_id = 0;
                return $next($request);
            }
        }
        return $next($request);
    }

    # 校验token
    public function checkToken(Request $request, $is_admin=false, $jwt = '')
    {
        // if(!empty($jwt)){
        //     $authorization = $jwt;
        // }else{
            // $header = $request->header();

            // if (!isset($header['authorization'])){
            //     return ['status' => 401,'msg' => lang('login_unauthorized')]; # 未授权
            // }

            $authorization = cookie('idcsmart_jwt'); //get_header_jwt();
        // }

        if( empty($authorization) || $authorization == 'null' ){
            return ['status' => 401,'msg' => lang('login_unauthorized')];
        }

        if (count(explode('.', $authorization)) != 3){
            return ['status' => 401,'msg' => lang('login_unauthorized')];
        }
        # 注销验证
        $id = Cache::get('login_token_'.$authorization);
        if(empty($id)){
            return ['status' => 401,'msg' => lang('login_unauthorized')];
        }

        $checkJWt = $this->verifyJwt($authorization,$id,$is_admin);
        if ($checkJWt['status']!=200){
            return $checkJWt;
        }

        $jwtToken = $checkJWt['data'];

        # $jwtToken['jwt'] = $authorization;

        # 后台修改密码 登录失效(前台一样)
        $key = $jwtToken['is_admin']?'admin_update_password_'.$id:'home_update_password_'.$id;
        $updatePassword = Cache::get($key);
        if($updatePassword && $jwtToken['nbf'] < $updatePassword){
            return ['status' => 401,'msg' => lang('password_is_change_please_login_again')];
        }

        # 登录用户ID不一致
        if ($id != $jwtToken['id']){
            return ['status' => 401,'msg' => lang('login_unauthorized') . ':' . lang('login_user_ID_is_inconsistent')];
        }

        return ['status'=>200,'data'=>['jwt_token'=>$jwtToken]];
    }

    # 参考JWT文档：https://packagist.org/packages/firebase/php-jwt
    protected function verifyJwt($jwt,$id,$is_admin=false)
    {
        if ($is_admin){
            $key = config('idcsmart.jwt_key_admin') . AUTHCODE;
        }else{
            $key = config('idcsmart.jwt_key_client') . AUTHCODE;
        }
        /*if ($is_admin){
            $AdminLoginModel = new AdminLoginModel();
            $key = $AdminLoginModel->getJwtKey($id);
        }else{
            $ClientLoginModel = new ClientLoginModel();
            $key = $ClientLoginModel->getJwtKey($id);
        }*/

        try{
            $jwtAuth = json_decode(json_encode(\Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($key,'HS256'))),true);

            if (empty($jwtAuth['info'])){
                return ['status'=>401,'msg'=>lang('login_unauthorized')];
            }

            $info = $jwtAuth['info'];

            $data = [
                'id'                   =>  $info['id'],
                'name'                 =>  $info['name'],
                'remember_password'    =>  isset($info['remember_password'])?$info['remember_password']:0, # 前台不需要就不传此值
                'nbf'                  =>  $jwtAuth['nbf'],
                'ip'                   =>  $jwtAuth['ip'],
                'is_admin'             =>  isset($info['is_admin'])?$info['is_admin']:false, # 是否后台验证
                'is_api'               =>  isset($info['is_api'])?$info['is_api']:false, #
                'api_id'               =>  isset($info['api_id'])?$info['api_id']:0, #
                'api_name'             =>  isset($info['api_name'])?$info['api_name']:'', #
            ];

            return ['status'=>200,'data'=>$data];

        } catch (\Firebase\JWT\SignatureInvalidException $e) { # token无效
            return ['status'=>401,'msg'=>lang('login_unauthorized') . ':' . $e->getMessage()];
        } catch (\Firebase\JWT\ExpiredException $e) { # token过期
            return ['status'=>401,'msg'=>lang('login_unauthorized') . ':' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['status'=>401,'msg'=>lang('login_unauthorized') . ':' . $e->getMessage()];
        }
    }

}