<?php

namespace app\home\controller;

use app\common\model\ClientModel;
use app\home\validate\AccountValidate;

/**
 * @title 登录注册
 * @desc 登录注册
 * @use app\home\controller\LoginController
 */
class LoginController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AccountValidate();
    }

    /**
     * 时间 2022-05-20
     * @title 登录
     * @desc 登录
     * @author wyh
     * @version v1
     * @url /console/v1/login
     * @method  POST
     * @param string type code 登录类型:code验证码登录,password密码登录 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(手机号登录时需要传此参数)
     * @param string code 1234 验证码(登录类型为验证码登录code时需要传此参数)
     * @param string password 123456 密码(登录类型为密码登录password时需要传此参数)
     * @param string remember_password 1 记住密码(登录类型为密码登录password时需要传此参数,1是,0否)
     * @param string captcha 1234 图形验证码(开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数)
     * @param string token fd5adaf7267a5b2996cc113e45b38f05 图形验证码唯一识别码(开启登录图形验证码且为密码登录时或者同一ip地址登录失败3次后需要传此参数)
     * @param object customfield {} 自定义字段,格式:{"field1":'test',"field2":'test2'}
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function login()
    {
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();
        # 客户登录前钩子
        hook_one('before_client_login',['type'=>$param['type']??'','account'=>$param['account']??'','phone_code'=>$param['phone_code']??'',
            'code'=>$param['code']??'','password'=>$param['password']??'','remember_password'=>$param['remember_password']??'',
            'captcha'=>$param['captcha']??'','token'=>$param['token']??'','customfield'=>$param['customfield']??[]]);

        $result = $ClientModel->login($param);

        return json($result);
    }

    /**
     * 时间 2022-05-23
     * @title 注册
     * @desc 注册
     * @author wyh
     * @version v1
     * @url /console/v1/register
     * @method  POST
     * @param string type phone 注册类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(注册类型为手机注册时需要传此参数)
     * @param string username wyh 姓名
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     * @param object customfield {} 自定义字段,格式:{"field1":'test',"field2":'test2'}
     * @return string data.jwt - jwt:注册后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function register()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('register')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 修改用户
        $result = $ClientModel->register($param);

        return json($result);
    }

    /**
     * 时间 2022-05-23
     * @title 忘记密码
     * @desc 忘记密码
     * @author wyh
     * @version v1
     * @url /console/v1/account/password_reset
     * @method  POST
     * @param string type phone 注册类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(注册类型为手机注册时需要传此参数)
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     */
    public function passwordReset()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('password_reset')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 修改用户
        $result = $ClientModel->passwordReset($param);

        return json($result);
    }
}