<?php
namespace app\http\middleware;

use app\common\model\ClientModel;
use app\common\model\ClientLoginModel;
use think\db\Query;

/**
 * @title 前台授权检查
 * @desc 前台授权检查
 * @use app\http\middleware\CheckHome
 */
class CheckHome extends Check
{
    public function handle($request, \Closure $next)
    {
        $result = parent::checkToken($request);
        if ($result['status'] != 200){
            $param = $request->param();
            if (isset($param['login_token'])){
                $result = parent::checkApi($request);
                if ($result['status'] != 200){
                    return json($result);
                }
            }else{
                return json($result);
            }
            
        }

        if(isset($result['data']['api'])){
            $api = $result['data']['api'];

            $request->client_id = $api['client_id'];
            $request->api_id = $api['id'];
            $request->api_name = $api['name'];
        }else{
            $jwtToken = $result['data']['jwt_token'];

            $request->client_id = $jwtToken['id'];
            $request->client_name = $jwtToken['name'];
            $request->client_remember_password = $jwtToken['remember_password'];
        }
        

        $time = time();
        $ClientModel = new ClientModel();

        $client = $ClientModel->find($request->client_id);
        # 用户不存在
        if (empty($client)){
            return json(['status'=>401,'msg'=>lang('login_unauthorized')]);
        }
        # 用户被禁用
        if ($request->client_id && $client->status != 1){
            return json(['status'=>401,'msg'=>lang('client_is_disabled')]);
        }

        # 登录ip不一致 wyh 之后去掉后面的说明,目前方便测试找原因
        $checkIp = configuration('home_login_check_ip')??0;
        if($checkIp && get_client_ip() !== $jwtToken['ip']){
            return json(['status' => 401,'msg' => lang('login_unauthorized') . ':' . lang('inconsistent_login_ip')]);
        }

        if(isset($result['data']['api'])){
            $request->client_name = $client['username'];
        }else{
            # 不记住密码 2个小时未操作自动退出登录(该账号某个ip登录时的操作时间进行判断)(未开启检查ip,此功能无效)
            $ClientLoginModel = new ClientLoginModel();
            $where = function (Query $query)use ($checkIp){
                if ($checkIp){ # 使用CDN,不检查IP
                    $query->where('last_login_ip',get_client_ip());
                }
            };
            $clientLogin = $ClientLoginModel->where('client_id',$request->client_id)
                ->where($where)
                ->order('create_time','desc')
                ->find();
            if (empty($clientLogin)){
                return json(['status'=>401,'msg'=>lang('login_unauthorized')]);
            }

            if ($checkIp && $request->client_remember_password == 0 && ($clientLogin->last_action_time+config('idcsmart.auto_logout'))<$time){
                return json(['status'=>401,'msg'=>lang('login_unauthorized') . ':' . lang('log_out_automatically_after_2_hours_without_operation')]);
            }
            # 记录操作时间(仅记录登录后的接口操作的时间)
            $client->last_action_time = $time;
            $client->save();

            $clientLogin->last_action_time = $time;
            $clientLogin->save();
        }

        return $next($request);
    }
}