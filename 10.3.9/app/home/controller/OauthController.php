<?php
namespace app\home\controller;

use app\home\model\OauthModel;

/**
 * @title 三方登录
 * @desc 三方登录
 * @use app\home\controller\OauthController
 */
class OauthController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2023-11-29
     * @title 跳转到登录授权网址
     * @desc 跳转到登录授权网址
     * @url console/v1/oauth/:name
     * @method  GET
     * @author hh
     * @version v1
     * @param   string name - 三方接口标识 require
     * @return  string url - 跳转地址
     */
    public function url()
    {
    	$param = $this->request->param();

		$OauthModel = new OauthModel();
		$result = $OauthModel->oauthUrl($param['name'] ?? '');
        return json($result);
	}

	/**
	 * 时间 2023-11-29
	 * @title 回调地址
	 * @desc 回调地址
	 * @url console/v1/oauth/callback/:name
	 * @method  GET
	 * @author hh
	 * @version v1
	 */
	public function callback()
    {	
        $param = $this->request->param();

        $OauthModel = new OauthModel();
        $result = $OauthModel->callback($param);
        if($result['status'] != 200){
            return json($result);
        }
        // if(isset($result['data']['url'])){
        //     header("Location:{$result['data']['url']}");exit;
        // }
        echo '<script>window.close();</script>';
        exit;
	}

    /**
     * 时间 2023-11-29
     * @title 验证oauthtoken
     * @desc  验证oauthtoken
     * @url console/v1/oauth/token
     * @method GET
     * @author hh
     * @version v1
     * @return  string jwt - 登录成功标识
     * @return  string url - 当返回url时,跳转
     */
    public function checkToken()
    {
        $param = $this->request->param();

        $OauthModel = new OauthModel();
        $result = $OauthModel->checkToken($param);
        return json($result);
    }

    /**
     * 时间 2023-11-29
     * @title 关联账户
     * @desc 关联账户
     * @url  console/v1/oauth/client/bind
     * @method  POST
     * @author hh
     * @version v1
     * @param string type phone 登录类型:phone手机注册,email邮箱注册 required
     * @param string account 18423467948 手机号或邮箱 required
     * @param string phone_code 86 国家区号(登录类型为手机注册时需要传此参数)
     * @param string code 1234 验证码 required
     * @return string data.jwt - jwt:注册后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     */
    public function bind()
    {
        $param = $this->request->param();

        $OauthModel = new OauthModel();
        $result = $OauthModel->bind($param);
        return json($result);
    }

    /**
     * 时间 2023-11-30
     * @title 取消关联
     * @desc 取消关联
     * @url console/v1/oauth/unbind/:name
     * @method  POST
     * @author hh
     * @version v1
     * @param   string name - 三方接口标识 require
     */
    public function unbind()
    {
        $param = $this->request->param();

        $OauthModel = new OauthModel();
        $result = $OauthModel->unbind($param);
        return json($result);
    }

    /**
     * 时间 2023-12-04
     * @title 指令回调地址
     * @desc 指令回调地址,可以用于三方推送相关接口
     * @url oauth/:name/command/receive
     * @method  POST
     * @author hh
     * @version v1
     * @param   string name - 三方接口标识 require
     */
    public function commandReceive()
    {
        $param = $this->request->param();

        $OauthModel = new OauthModel();
        $result = $OauthModel->commandReceive($param);
        return $result;
    }

}