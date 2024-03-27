<?php
namespace captcha\tp_captcha\controller;

use app\home\controller\BaseController;
use captcha\tp_captcha\logic\TpCaptchaLogic;

/**
 * @desc 验证码控制器
 * @author wyh
 * @version 1.0
 * @time 2022-09-08
 */
class IndexController extends BaseController
{
    # 刷新验证码
    public function refresh()
    {
        $TpCaptchaLogic = new TpCaptchaLogic();

        $result = $TpCaptchaLogic->baseDescribe();

        unset($result['captcha']);

        return json($result);
    }

    # 基础验证,不清缓存
    public function verify()
    {
        $param = $_POST;

        $TpCaptchaLogic = new TpCaptchaLogic();

        $param['base'] = true;

        $result = $TpCaptchaLogic->verify($param);

        return json($result);
    }

}