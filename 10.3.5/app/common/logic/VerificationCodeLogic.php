<?php 
namespace app\common\logic;

use think\facade\Cache;
use app\common\model\ClientModel;
use app\common\logic\SmsLogic;
use app\common\logic\EmailLogic;

/**
 * @title 验证码逻辑类
 * @desc 验证码逻辑类
 * @use app\common\logic\DocLogic
 */
class VerificationCodeLogic
{
    /**
     * 时间 2022-5-19
     * @title 发送手机验证码
     * @desc 发送手机验证码
     * @author theworld
     * @version v1
     * @param string param.action - 验证动作login登录register注册verify验证手机update修改手机password_reset重置密码
     * @param int param.phone_code - 国际电话区号 未登录或修改手机时需要
     * @param string param.phone - 手机号 未登录或修改手机时需要
     * @param string param.token - 图形验证码唯一识别码
     * @param string param.captcha - 图形验证码
     */
    public function sendPhoneCode($param)
    {
        $config = $this->getConfig();
        if(isset($config[$param['action']]) && $config[$param['action']]==1){
            if (!isset($param['captcha']) || empty($param['captcha'])){
                return ['status'=>400, 'msg'=>lang('login_captcha')];
            }
            if (!isset($param['token']) || empty($param['token'])){
                return ['status'=>400, 'msg'=>lang('login_captcha_token')];
            }
            $token = $param['token'];
            if (!check_captcha($param['captcha'], $token)){
                return ['status'=>400, 'msg'=>lang('login_captcha_error')];
            }
			Cache::delete("captcha_" . $token);//删除图形验证码
        }
        
        if(in_array($param['action'], ['verify'])){
            $clientId = get_client_id();
            if(empty($clientId)){
                return ['status'=>400, 'msg'=>lang('login_unauthorized')];
            }

            $client = ClientModel::find($clientId);
            if(empty($client)){
                return ['status'=>400, 'msg'=>lang('login_unauthorized')];
            }
            if(empty($client['phone'])){
                return ['status'=>400, 'msg'=>lang('user_not_bind_phone')];
            }
            // 20231019 增加，默认使用接口请求手机号(方便下游调取接口)
            $phone_code = $param['phone_code']??$client['phone_code'];
            $phone = $param['phone']??$client['phone'];
        }else if(in_array($param['action'], ['login'])){
            $client = ClientModel::where('phone', $param['phone'])->where('phone_code', $param['phone_code'])->find();
            if(empty($client)){
                $param['action'] = 'register';
            }
            $phone_code = $param['phone_code'];
            $phone = $param['phone'];
        }else{
            $phone_code = $param['phone_code'];
            $phone = $param['phone'];
        }
		
        if(!empty(Cache::get('verification_code_time_'.$phone_code.'_'.$phone))){
            return ['status' => 400, 'msg' => lang('verification_code_can_only_sent_once_per_minute')]; // 每分钟只能发送一次
        }
        // 生成验证码
        $code = mt_rand(100000, 999999);
        Cache::set('verification_code_'.$param['action'].'_'.$phone_code.'_'.$phone, $code, 300); // 验证码保存5分钟
        Cache::set('verification_code_time_'.$phone_code.'_'.$phone, $code, 60); // 设置每分钟只能发送一次   
		$data = [
			'name'=>'code',
			'phone_code'=>$phone_code,
			'phone'=>$phone,
			'template_param'=>[
				'code'=>$code
			],
		];
		$send_result = (new SmsLogic)->send($data);	
		if($send_result['status'] == 400){
			Cache::delete('verification_code_'.$param['action'].'_'.$phone_code.'_'.$phone); // 删除验证码 
			Cache::delete('verification_code_time_'.$phone_code.'_'.$phone); // 删除1分组缓存判断 
		}
		return 	$send_result;
        //return ['status' => 200, 'msg' => lang('success_message'), 'data' => ['code' => $code]];
    }

    /**
     * 时间 2022-5-19
     * @title 发送邮件验证码
     * @desc 发送邮件验证码
     * @author theworld
     * @version v1
     * @param string param.action - 验证动作login登录register注册verify验证邮箱update修改邮箱password_reset重置密码
     * @param string param.email - 邮箱 未登录或修改邮箱时需要
     * @param string param.token - 图形验证码唯一识别码
     * @param string param.captcha - 图形验证码
     */
    public function sendEmailCode($param)
    {
        $config = $this->getConfig();
        if(isset($config[$param['action']]) && $config[$param['action']]==1){
            if (!isset($param['captcha']) || empty($param['captcha'])){
                return ['status'=>400, 'msg'=>lang('login_captcha')];
            }
            if (!isset($param['token']) || empty($param['token'])){
                return ['status'=>400, 'msg'=>lang('login_captcha_token')];
            }
            $token = $param['token'];
            if (!check_captcha($param['captcha'], $token)){
                return ['status'=>400, 'msg'=>lang('login_captcha_error')];
            }
			Cache::delete("captcha_" . $token);//删除图形验证码
        }
        
        if(in_array($param['action'], ['verify'])){
            $clientId = get_client_id();
            if(empty($clientId)){
                return ['status'=>400, 'msg'=>lang('login_unauthorized')];
            }

            $client = ClientModel::find($clientId);
            if(empty($client)){
                return ['status'=>400, 'msg'=>lang('login_unauthorized')];
            }
            if(empty($client['email'])){
                return ['status'=>400, 'msg'=>lang('user_not_bind_email')];
            }
            $email = $client['email'];
        }else if(in_array($param['action'], ['login'])){
            $client = ClientModel::where('email', $param['email'])->find();
            if(empty($client)){
                $param['action'] = 'register';
            }
            $email = $param['email'];
        }else{
            $email = $param['email'];
        }
        if(!empty(Cache::get('verification_code_time_'.$email))){
            return ['status' => 400, 'msg' => lang('verification_code_can_only_sent_once_per_minute')]; // 每分钟只能发送一次
        }

        // 生成验证码
        $code = mt_rand(100000, 999999);
        Cache::set('verification_code_'.$param['action'].'_'.$email, $code, 300); // 验证码保存5分钟
		Cache::set('verification_code_time_'.$email, $code, 60); // 设置每分钟只能发送一次 
		$data = [
			'name'=>'code',
			'email'=>$email,
			'template_param'=>[
				'code'=>$code
			],
		];
		$send_result = (new EmailLogic)->send($data);	
		if($send_result['status'] == 400){
			Cache::delete('verification_code_'.$param['action'].'_'.$email); // 删除验证码 
			Cache::delete('verification_code_time_'.$email); // 删除1分组缓存判断 
		}
		return 	$send_result;

    }

    private function getConfig()
    {
        //需要验证图形验证码的动作
        $config = [
            'register' => intval(configuration('captcha_client_register')), // 客户注册图形验证码开关  1开启，0关闭
            'login' => intval(configuration('captcha_client_login')), // 客户登录图形验证码开关  1开启，0关闭
        ];

        return $config;
    }
}