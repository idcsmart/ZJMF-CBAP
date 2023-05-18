<?php
namespace app\api\controller;

use app\common\model\ClientModel;

/*
 * API鉴权登录
 *
 * */
class AuthController
{
    /**
     * 时间 2023-02-16
     * @title API鉴权登录
     * @desc API鉴权登录
     * @author wyh
     * @version v1
     * @url /api/v1/auth
     * @method  POST
     * @param string username - 用户名(用户注册时的邮箱或手机号)
     * @param string password - 密码(api信息的token)
     */
    public function auth()
    {
        $param = request()->param();

        $validate = new \think\Validate([
            'username' => 'require|length:4,20',
            'password' => 'require'
        ]);
        $validate->message([
            'username.require' => '用户不能为空',
            'username.length' => '用户名4-20位',
            'password.require' => '密码不能为空',
        ]);
        if (!$validate->check($param)) {
            return json(['status' => 400, 'msg' => '鉴权失败']);
        }

        $ClientModel = new ClientModel();

        $result = $ClientModel->apiAuth($param);

        return json($result);
    }

    /**
     * 时间 2023-02-16
     * @title API同步信息
     * @desc API同步信息
     * @author wyh
     * @version v1
     * @url /api/v1/host/sync
     * @method  POST
     */
    public function hostSync()
    {
        $param = request()->param();

        $id = $param['id'];

    }
}