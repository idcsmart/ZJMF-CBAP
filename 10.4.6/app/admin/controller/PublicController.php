<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;
use think\facade\Db;

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
     * @return int captcha_admin_login - 管理员登录图形验证码开关:1开启,0关闭
     * @return string website_name 智简魔方 网站名称
     * @return string lang_admin - 语言
     * @return int admin_allow_remember_account - 后台是否允许记住账号:1开启0关闭
     * @author wyh
     * @version v1
     */
    public function loginInfo()
    {
        $setting = [
            'captcha_admin_login',
            'website_name',
            'lang_admin',
            'admin_allow_remember_account',
        ];
        $data = configuration($setting);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'captcha_admin_login' => $data['captcha_admin_login'] ?? '',
                'website_name' => $data['website_name'] ?? '',
                'lang_admin' => $data['lang_admin'] ?? '',
                'admin_allow_remember_account' => isset($data['admin_allow_remember_account']) ? (int)$data['admin_allow_remember_account'] : 1,
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
     * @param string name 测试员 用户名 required
     * @param string password 123456 密码 required
     * @param string remember_password 1 是否记住密码(1是,0否) required
     * @param string token d7e57706218451cbb23c19cfce583fef 图形验证码唯一识别码
     * @param string captcha 12345 图形验证码
     * @return object data - 返回数据
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     * @version v1
     * @author wyh
     */
    public function login()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('login')->check($param)) {
            return json(['status' => 400, 'msg' => lang($this->validate->getError())]);
        }
        // 是否允许记住密码
        $adminAllowRememberAccount = configuration('admin_allow_remember_account') ?: 1;
        if($adminAllowRememberAccount != 1){
            $param['remember_password'] = 0;
        }

        hook_one('before_admin_login', ['name' => $param['name'] ?? '', 'password' => $param['password'] ?? '', 'remember_password' => $param['remember_password'] ?? '',
            'token' => $param['token'] ?? '', 'captcha' => $param['captcha'] ?? '', 'customfield' => $param['customfield'] ?? []]);

        $result = (new AdminModel())->login($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 图形验证码
     * @desc 图形验证码
     * @url /admin/v1/captcha
     * @method  get
     * @return string html - html文档
     * @version v1
     * @author wyh
     */
    public function captcha()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'html' => get_captcha(true)
            ]
        ];

        return json($result);
    }
}