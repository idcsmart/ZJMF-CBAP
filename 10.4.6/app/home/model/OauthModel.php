<?php
namespace app\home\model;

use think\Model;
use app\admin\model\PluginModel;
use app\common\model\ClientModel;
use app\common\model\ClientLoginModel;

/**
 * @title 三方登录信息模型
 * @desc 三方登录信息模型
 * @use app\home\model\OauthModel
 */
class OauthModel extends Model
{
    protected $name = 'oauth';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'type'          => 'string',
        'client_id'     => 'int',
        'openid'        => 'string',
        'oauth'         => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2023-11-29
     * @title 跳转到登录授权网址
     * @desc 跳转到登录授权网址
     * @author hh
     * @version v1
     * @param   string name - 三方登录插件标识 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.url - 回调地址
     */
    public function oauthUrl($name)
    {
        $plugin = PluginModel::where('module', 'oauth')
                ->where('name', $name)
                ->where('status', 1)
                ->find();
        if(empty($plugin)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        $config = json_decode($plugin['config'], true);
        $class = get_plugin_class($plugin['name'], 'oauth');
        if(!class_exists($class)){
            return ['status'=>400, 'msg'=>lang('param_error')];
        }
        $obj = new $class();
        
        $systemOauthState = md5(uniqid(rand(), true));

        $param = [
            'name'              => $name,
            'callback'          => request()->domain() .'/console/v1/oauth/callback/'.$name,
            'system_oauth_state'=> $systemOauthState,
        ];
        $param = array_merge($param, $config);
        $data['url'] = $obj->url($param);

        session_start();
        $_SESSION['oauth']['system_oauth_state'] = $systemOauthState;
        $_SESSION['oauth']['client_id'] = get_client_id();
        $_SESSION['oauth']['login_ip'] = get_client_ip();
        $_SESSION['oauth']['expire_time'] = time() + 10 * 60;  // 10分钟后超时
        $_SESSION['oauth']['info'] = NULL;

        return ['status'=>200, 'msg'=>lang('success_message'), 'data'=>$data];
    }

    /**
     * 时间 2023-11-29
     * @title 回调地址
     * @desc 回调地址
     * @author hh
     * @version v1
     * @param  string param.state - 防止csrf攻击标识 require
     * @param  string param.name - 三方登录标识 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function callback($param)
    {   
        // url回调
        session_start();
        $sessionOauth = $_SESSION['oauth'] ?? [];
        if(empty($sessionOauth) || !isset($param['state']) || $param['state'] != $sessionOauth['system_oauth_state']){
            return ['status'=>400, 'msg'=>lang('oauth_state_param_error')];
        }
        $PluginModel = new PluginModel();
        $plugin = $PluginModel
                ->field('id,title,config')
                ->where('module', 'oauth')
                ->where('name', $param['name'])
                ->where('status', 1)
                ->find();
        if(empty($plugin)){
            return ['status'=>400, 'msg'=>lang('oauth_not_active', ['{name}'=>$param['name']])];
        }
        $config = json_decode($plugin['config'], true);

        $class = get_plugin_class($param['name'], 'oauth');
        if (!class_exists($class)) {
            return ['status'=>400, 'msg'=>lang('oauth_not_active', ['{name}'=>$param['name']])];
        }
        $obj = new $class();

        $param['callback'] = request()->domain() .'/console/v1/oauth/callback/'.$param['name'];
        $param = array_merge($param, $config);
        $userinfo = $obj->callback($param);
        if(!is_array($userinfo)){
            return ['status'=>400, 'msg'=>$userinfo];
        }
        if(empty($userinfo['openid'])){
            return ['status'=>400, 'msg'=>lang('openid not found')];
        }
        $oauth = $this
                ->where('type', $param['name'])
                ->where('openid', $userinfo['openid'])
                ->find();

        $_SESSION['oauth']['info'] = $userinfo;
        $_SESSION['oauth']['oauth_type'] = $param['name'];

        $clientId = $sessionOauth['client_id'] ?? 0;
        // 未绑定过
        if(empty($oauth)){
            // 已经登录了
            if(!empty($clientId)){
                $this->create([
                    'type'          => $param['name'],
                    'client_id'     => $clientId,
                    'openid'        => $userinfo['openid'],
                    'oauth'         => json_encode($userinfo['data'] ?? []),
                    'create_time'   => time(),
                ]);

                $result = [
                    'status'    => 200,
                    'msg'       => lang('success_message'),
                ];

                $_SESSION['oauth'] = NULL;
            }else{
                // 跳转至绑定页面
                $result = [
                    'status'    => 200,
                    'msg'       => lang('success_message'),
                ];
            }
        }else{
            if(!empty($clientId)){
                $_SESSION['oauth'] = NULL;

                // 绑定至该用户
                if($clientId != $oauth['client_id']){
                    $this->where('id', $oauth['id'])->update([
                        'client_id'     => $clientId,
                        'update_time'   => time(),
                    ]);
                    // return ['status'=>400, 'msg'=>lang('oauth_already_bind_other_client')];
                }else{

                }
                $result = [
                    'status'    => 200,
                    'msg'       => lang('success_message'),
                ];
                // return ['status'=>400, 'msg'=>lang('oauth_already_bind')];
            }else{
                $client = ClientModel::find($oauth['client_id']);
                if(!empty($client)){
                    if($client['status'] == 0){
                        return ['status'=>400, 'msg'=>lang('client_is_disabled')];
                    }
                    $info = [
                        'id'                => $client->id,
                        'name'              => $client->username,
                        'remember_password' => 0
                    ];
                    $expired = 3600*24*1;
                    $jwt = create_jwt($info, $expired);

                    $_SESSION['oauth']['jwt'] = $jwt;
                    $_SESSION['oauth']['client_id'] = $client->id;

                    // 跳转至绑定页面
                    $result = [
                        'status'    => 200,
                        'msg'       => lang('success_message'),
                    ];
                }else{
                    $this->where('id', $oauth['id'])->delete();

                    // 跳转至绑定页面
                    $result = [
                        'status'    => 200,
                        'msg'       => lang('success_message'),
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * 时间 2023-11-29
     * @title 验证oauthtoken
     * @desc  验证oauthtoken
     * @author hh
     * @version v1
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.jwt - 登录成功标识
     * @return  string data.url - 当返回url时,跳转
     */
    public function checkToken($param)
    {
        session_start();
        $oauth = $_SESSION['oauth'] ?? [];

        $result = [
            'status' => 200,
            'msg'    => lang('oauth_session_expired'),
            'data'   => [

            ],
        ];
        if(empty($oauth) || !isset($oauth['info']) || time() >= $oauth['expire_time']){
            $_SESSION['oauth'] = NULL;
            return $result;
        }

        $result['msg'] = lang('success_message');
        if(isset($oauth['jwt'])){
            $ClientLoginModel = new ClientLoginModel();
            $ClientLoginModel->clientLogin($oauth['client_id']);

            $result['data']['jwt'] = $oauth['jwt'];
            $result['data']['url'] = request()->domain() . '/home.htm';

            $_SESSION['oauth'] = NULL;
        }else{
            $result['data']['url'] = request()->domain() . '/oauth.htm';
        }
        return $result;
    }

    /**
     * 时间 2023-11-29
     * @title 关联账户
     * @desc 关联账户
     * @author hh
     * @version v1
     * @param string param.type - 登录类型:phone手机注册,email邮箱注册 required
     * @param string param.account - 手机号或邮箱 required
     * @param string param.phone_code 86 国家区号(登录类型为手机注册时需要传此参数)
     * @param string param.code - 验证码 required
     * @return int status - 状态(200=成功,400=失败)
     * @return string msg - 信息
     * @return string data.jwt - jwt:注册后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     * @return string data.url - 当返回url时,跳转
     */
    public function bind($param)
    {
        session_start();

        $oauth = $_SESSION['oauth'] ?? [];
        if(empty($oauth) || time() >= $oauth['expire_time']){
            $result = [
                'status' => 400,
                'msg'    => lang('oauth_session_expired'),
                'data'   => [
                    'url'=> request()->domain() . '/login.htm',
                ],
            ];

            $_SESSION['oauth'] = NULL;
            return $result;
        }

        $ClientModel = new ClientModel();
        $type = $param['type'];
        if ($type == 'phone'){
            $code = $ClientModel->getPhoneVerificationCode($param['account'], $param['phone_code'], 'oauth');
            if (empty($code)){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            if ($param['code'] != $code){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            $client = $ClientModel
                    ->where('phone', $param['account'])
                    ->where('phone_code', $param['phone_code'])
                    ->find();

            if(empty($client)){
                $password = rand_str(12);
                $username = $oauth['info']['data']['username'] ?? '';

                $registerRes = $ClientModel->phoneRegister([
                    'username'      => strlen($username) > 20 ? '' : $username,
                    'phone_code'    => $param['phone_code'],
                    'account'       => $param['account'],
                    'password'      => $password,
                    're_password'   => $password,
                ], true);
                if($registerRes['status'] != 200){
                    return $registerRes;
                }
                $clientId = $registerRes['data']['id'];
                $jwt = $registerRes['data']['jwt'];
            }else{
                $ClientLoginModel = new ClientLoginModel();
                $ClientLoginModel->clientLogin($client->id);

                $clientId = $client->id;
                $info = [
                    'id'                => $client->id,
                    'name'              => $client->username,
                    'remember_password' => 0
                ];
                $expired = 3600*24*1;
                $jwt = create_jwt($info, $expired);
            }
        }else{
            $code = $ClientModel->getEmailVerificationCode($param['account'], 'oauth');
            if (empty($code)){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            if ($param['code'] != $code){
                return ['status'=>400,'msg'=>lang('verification_code_error')];
            }
            $client = $ClientModel->where('email', $param['account'])->find();
            if(empty($client)){
                $password = rand_str(12);
                $username = $oauth['info']['data']['username'] ?? '';

                $registerRes = $ClientModel->emailRegister([
                    'username'      => strlen($username) > 20 ? '' : $username,
                    'account'       => $param['account'],
                    'password'      => $password,
                    're_password'   => $password,
                ], true);
                if($registerRes['status'] != 200){
                    return $registerRes;
                }
                $clientId = $registerRes['data']['id'];
                $jwt = $registerRes['data']['jwt'];
            }else{
                $ClientLoginModel = new ClientLoginModel();
                $ClientLoginModel->clientLogin($client->id);

                $clientId = $client->id;
                $info = [
                    'id'                => $client->id,
                    'name'              => $client->username,
                    'remember_password' => 0
                ];
                $expired = 3600*24*1;
                $jwt = create_jwt($info, $expired);
            }
        }
        $_SESSION['oauth'] = NULL;

        $this->create([
            'type'          => $oauth['oauth_type'],
            'client_id'     => $clientId,
            'openid'        => $oauth['info']['openid'],
            'oauth'         => json_encode($oauth['info']['data'] ?? []),
            'create_time'   => time(),
        ]);
        
        cookie("idcsmart_jwt",$jwt);
        
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'jwt'=> $jwt,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-11-29
     * @title 用户三方登录详情
     * @desc  用户三方登录详情
     * @author hh
     * @version v1
     * @return bool list[].link - 是否绑定(false=未绑定,true=已绑定)
     * @return string list[].name - 三方登录标识
     * @return string list[].title - 三方登录名称
     * @return string list[].url - 请求地址
     */
    public function clientOauth()
    {
        $PluginModel = new PluginModel();
        $oauthList = $PluginModel->oauthList();

        $clientId = get_client_id();

        $oauth = $this->field('id,type')->where('client_id', $clientId)->select()->toArray();
        $oauth = array_column($oauth, 'id', 'type');

        foreach($oauthList['list'] as $k=>$v){
            $oauthList['list'][$k]['link'] = isset($oauth[ $v['name'] ]);
            unset($oauthList['list'][$k]['img']);
        }

        return $oauthList;
    }

    /**
     * 时间 2023-11-30
     * @title 取消关联
     * @desc 取消关联
     * @author hh
     * @version v1
     * @param   string param.name - 三方接口标识 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function unbind($param)
    {
        $clientId = get_client_id();

        $oauth = $this
                ->where('client_id', $clientId)
                ->where('type', $param['name'])
                ->find();
        if(empty($oauth)){
            return ['status'=>200, 'msg'=>lang('success_message') ];
        }
        $this->where('id', $oauth['id'])->delete();

        $plugin = PluginModel::where('name', $param['name'])->where('module', 'oauth')->find();
        $description = lang('log_oauth_unbind_success', [
            '{name}'    => $plugin['title'] ?? $param['name'],
        ]);

        active_log($description, 'client', $clientId);

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
        ];
        return $result;
    }

    /**
     * 时间 2023-12-04
     * @title 指令回调地址
     * @desc 指令回调地址,可以用于三方推送相关接口
     * @author hh
     * @version v1
     * @param   string param.name - 三方接口标识 require
     * @return  array|mixed
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function commandReceive($param)
    {
        $plugin = PluginModel::where('module', 'oauth')
                ->where('name', $param['name'])
                ->where('status', 1)
                ->find();
        if(empty($plugin)){
            return ['status'=>400, 'msg'=>lang('oauth_not_active', ['{name}'=>$param['name']]) ];
        }
        $config = json_decode($plugin['config'], true);

        $class = get_plugin_class($param['name'], 'oauth');
        if (!class_exists($class)) {
            return ['status'=>400, 'msg'=>lang('oauth_not_active', ['{name}'=>$param['name']])];
        }
        $obj = new $class();
        if(!method_exists($obj, 'receive')){
            return ['status'=>400, 'msg'=>lang('oauth_not_active', ['{name}'=>$param['name']])];
        }
        $param = array_merge($param, $config);
        $result = $obj->receive($param);
        return $result;
    }


}