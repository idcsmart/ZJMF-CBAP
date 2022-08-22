<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;
use app\common\logic\CaptchaLogic;

/**
 * @title 后台开放类
 * @desc 后台开放类,不需要授权
 */
class PublicController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminValidate();
    }

    /**
     * 时间 2022-5-18
     * @title 登录信息
     * @desc 登录信息
     * @url /admin/v1/login
     * @method  get
     * @author wyh
     * @version v1
     * @return int captcha_admin_login - 管理员登录图形验证码开关:1开启,0关闭
     */
    public function loginInfo()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data'=>[
                'captcha_admin_login' => configuration('captcha_admin_login')
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-13
     * @title 后台登录
     * @desc 后台登录
     * @url /admin/v1/login
     * @method  post
     * @author wyh
     * @version v1
     * @param string name 测试员 用户名 required
     * @param string password 123456 密码 required
     * @param string remember_password 1 是否记住密码(1是,0否) required
     * @param string token d7e57706218451cbb23c19cfce583fef 图形验证码唯一识别码
     * @param string captcha 12345 图形验证码
     * @return object data - 返回数据
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function login()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('login')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        hook_one('before_admin_login',['name'=>$param['name']??'','password'=>$param['password']??'', 'remember_password'=>$param['remember_password']??'',
            'token'=>$param['token']??'','captcha'=>$param['captcha']??'','customfield'=>$param['customfield']??[]]);

        $result = (new AdminModel())->login($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 图形验证码
     * @desc 图形验证码
     * @url /admin/v1/captcha
     * @method  get
     * @author wyh
     * @version v1
     * @return string captcha - 图形验证码,base64格式
     * @return string token - 图形验证码唯一识别码
     */
    public function captcha()
    {
        $result = [
            'status' => 200,
            'msg'=> lang('success_message'),
            'data' => (new CaptchaLogic())->captcha()
        ];

        return json($result);
    }

}