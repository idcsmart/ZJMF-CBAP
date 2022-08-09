<?php 
namespace app\common\logic;

use think\captcha\Captcha;
use think\facade\Cache;

/**
 * @title 图形验证码逻辑类
 * @desc 图形验证码逻辑类
 * @use app\common\logic\CaptchaLogic
 */
class CaptchaLogic
{
    /**
     * 时间 2022-05-19
     * @title 图形验证码
     * @desc 图形验证码
     * @author wyh
     * @version v1
     * @return array
     * @return captcha 234 图形验证码base64编码
     * @return token 1234 图形验证码唯一识别码
     */
    public function captcha()
    {
        $config = $this->getConfig();

        $Captcha = new Captcha(app('config'),app('session'));

        $response = $Captcha->create($config);

        $token = md5(microtime().rand(10000000,99999999));
        Cache::set("captcha_" . $token,$GLOBALS['captcha'],1800); # 缓存验证

        $result = [
            'captcha' => 'data:png;base64,' . base64_encode($response->getData()),
            'token' => $token
        ];

        return $result;
    }

    private function getConfig()
    {
        $configuration = configuration(['captcha_width','captcha_height','captcha_length']);
        if (floatval($configuration['captcha_width']) <= 10){
            $configuration['captcha_width'] = 250;
        }
        if (floatval($configuration['captcha_height']) <= 5){
            $configuration['captcha_height'] = 61;
        }
        if (floatval($configuration['captcha_length']) <= 0){
            $configuration['captcha_length'] = 4;
        }

        $config = [
            'imageW' => $configuration['captcha_width'],
            'imageH' => $configuration['captcha_height'],
            'length' => $configuration['captcha_length'],
            'codeSet' => '1234567890',
        ];

        return $config;
    }

    public function checkCaptcha($param)
    {
        if (!isset($param['captcha']) || empty($param['captcha'])){
            return ['status'=>400,'msg'=>lang('login_captcha')];
        }

        if (!isset($param['token']) || empty($param['token'])){
            return ['status'=>400,'msg'=>lang('login_captcha_token')];
        }

        if (Cache::get('captcha_'.$param['token']) != $param['captcha']){
            return ['status'=>400,'msg'=>lang('login_captcha_error')];
        }

        return ['status'=>200,'msg'=>lang('success_message')];
    }
}