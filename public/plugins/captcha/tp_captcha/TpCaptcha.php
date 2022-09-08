<?php
namespace captcha\tp_captcha;

use app\common\lib\Plugin;
use captcha\tp_captcha\logic\TpCaptchaLogic;
use think\facade\Db;

/**
 * @desc thinkphp图形验证
 * @author wyh
 * @version 1.0
 * @time 2022-08-18
 */
class TpCaptcha extends Plugin
{
    // 插件基础信息
    public $info = array(
        'name'        => 'TpCaptcha', // 必填 插件标识(唯一)
        'title'       => 'thinkphp图形验证', // 必填 插件显示名称
        'description' => 'thinkphp图形验证', // 必填 插件功能描述
        'author'      => '智简魔方', // 必填 插件作者
        'version'     => '1.0',  // 必填 插件版本
    );

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    # 获取前台验证码
    public function TpCaptchaDescribe()
    {
        $TpCaptchaLogic = new TpCaptchaLogic();
        return $TpCaptchaLogic->describe();
    }

    # 获取后台验证码
    public function TpCaptchaDescribeAdmin()
    {
        $TpCaptchaLogic = new TpCaptchaLogic();
        return $TpCaptchaLogic->describe(true);
    }

    # 验证
    public function TpCaptchaVerify($param)
    {
        $TpCaptchaLogic = new TpCaptchaLogic();

        return $TpCaptchaLogic->verify($param);
    }

    // 获取配置
    public function Config()
    {
        $config = Db::name('plugin')->where('name', $this->info['name'])->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = [];
        }
        $con = require dirname(__DIR__).'/tp_captcha/config/config.php';

        $config = array_merge($con,$config);

        return $config;
    }

}