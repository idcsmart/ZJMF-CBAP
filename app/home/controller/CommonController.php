<?php

namespace app\home\controller;

use app\common\logic\CaptchaLogic;
use app\common\logic\UploadLogic;
use app\common\model\CountryModel;
use app\common\logic\VerificationCodeLogic;
use app\home\validate\CommonValidate;
use think\facade\Cache;
use app\common\model\HostModel;
use app\common\model\MenuModel;

/**
 * @title 公共接口(前台,无需登录)
 * @desc 公共接口(前台,无需登录)
 * @use app\home\controller\CommonController
 */
class CommonController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CommonValidate();
    }

    /**
     * 时间 2022-5-16
     * @title 获取国家列表
     * @desc 获取国家列表,包括国家名，中文名，区号
     * @author theworld
     * @version v1
     * @url /console/v1/country
     * @method  GET
     * @param string keywords - 关键字,搜索范围:国家名,中文名,区号
     * @return array list - 国家列表
     * @return string list[].name - 国家名
     * @return string list[].name_zh - 中文名
     * @return int list[].phone_code - 区号
     * @return int count - 国家总数
     */
    public function countryList()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $CountryModel = new CountryModel();

        //获取国家列表
        $data = $CountryModel->countryList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 发送手机验证码
     * @desc 发送手机验证码
     * @author theworld
     * @version v1
     * @url /console/v1/phone/code
     * @method  POST
     * @param string action - 验证动作login登录register注册verify验证手机update修改手机password_reset重置密码
     * @param int phone_code - 国际电话区号 未登录或修改手机时需要
     * @param string phone - 手机号 未登录或修改手机时需要
     * @param string token - 图形验证码唯一识别码
     * @param string captcha - 图形验证码
     */
    public function sendPhoneCode()
    {
        //接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('sened_phone_code')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new VerificationCodeLogic())->sendPhoneCode($param);

        return json($result);
        
    }

    /**
     * 时间 2022-5-19
     * @title 发送邮件验证码
     * @desc 发送邮件验证码
     * @author theworld
     * @version v1
     * @url /console/v1/email/code
     * @method  POST
     * @param string action - 验证动作login登录register注册verify验证邮箱update修改邮箱password_reset重置密码
     * @param string email - 邮箱 未登录或修改邮箱时需要
     * @param string token - 图形验证码唯一识别码
     * @param string captcha - 图形验证码
     */
    public function sendEmailCode()
    {
        //接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('sened_email_code')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new VerificationCodeLogic())->sendEmailCode($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 图形验证码
     * @desc 图形验证码
     * @url /console/v1/captcha
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
            'msg' => lang('success_message'),
            'data' => (new CaptchaLogic())->captcha()
        ];

        return json($result);
    }

    /**
     * 时间 2022-07-08
     * @title 验证图形验证码
     * @desc 验证图形验证码
     * @url /console/v1/captcha
     * @method  POST
     * @author wyh
     * @version v1
     * @param string captcha - 图形验证码, required
     * @param string token - 图形验证码唯一识别码 required
     */
    public function checkCaptcha()
    {
        $param = $this->request->param();

        $CaptchaLogic = new CaptchaLogic();

        $result = $CaptchaLogic->checkCaptcha($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 支付接口
     * @desc 支付接口
     * @url /console/v1/gateway
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 支付接口
     * @return int list[].id - ID
     * @return int list[].title - 名称
     * @return int list[].name - 标识
     * @return int list[].url - 图片:base64格式
     * @return int count - 总数
     */
    public function gateway()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => gateway_list()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 公共配置
     * @desc 公共配置
     * @url /console/v1/common
     * @method  get
     * @author wyh
     * @version v1
     * @param string account - 账户
     * @return array lang_list - 语言列表
     * @return string lang_home zh-cn 前台默认语言
     * @return string lang_home_open 1 前台多语言开关:1开启，0关闭
     * @return string maintenance_mode 1 维护模式开关:1开启，0关闭
     * @return string maintenance_mode_message - 维护模式内容
     * @return string website_name - 网站名称
     * @return string website_url - 网站域名地址
     * @return string terms_service_url - 服务条款地址
     * @return string login_phone_verify 1 手机号登录短信验证开关 1开启，0关闭
     * @return string captcha_client_register 1 客户注册图形验证码开关  1开启，0关闭
     * @return string captcha_client_login 1 客户登录图形验证码开关  1开启，0关闭
     * @return string captcha_client_login_error 1 客户登录失败图形验证码开关  1开启，0关闭
     * @return string captcha_client_login_error_3_times 1 客户登录失败3次
     * @return string register_email 1 邮箱注册开关 1开启，0关闭
     * @return string register_phone 1 手机号注册开关 1开启，0关闭
     * @return string recharge_open 1 启用充值:1启用,0否
     * @return string recharge_min 1 单笔最小金额
     * @return string currency_code CNY 货币代码
     * @return string currency_prefix ￥ 货币符号
     * @return string currency_suffix 元 货币后缀
     * @return string code_client_email_register 0 邮箱注册数字验证码开关:1开启0关闭
     */
    public function common()
    {
        $param = $this->request->param();
		$lang = [ 
			'lang_list'=> lang_list('home') ,
		];
        $setting = [
            'lang_home',
            'lang_home_open',
            'maintenance_mode',
            'maintenance_mode_message',
            'website_name',
            'website_url',
            'terms_service_url',
            'login_phone_verify',
            'captcha_client_register',
            'captcha_client_login',
            'captcha_client_login_error',
            'register_email',
            'register_phone',
            'recharge_open',
            'recharge_min',
            'recharge_min',
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'code_client_email_register',
        ];

        //$data = configuration($setting);
		$data = array_merge($lang,configuration($setting));
        $account = $param['account']??'';

        # 登录3次失败
        if ($account){
            $ip = get_client_ip();
            $key = "password_login_times_{$account}_{$ip}";
            if (Cache::get($key)>3){
                $data = array_merge($data,['captcha_client_login_error_3_times'=>1]);
            }else{
                $data = array_merge($data,['captcha_client_login_error_3_times'=>0]);
            }
        }else{
            $data = array_merge($data,['captcha_client_login_error_3_times'=>0]);
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-6-20
     * @title 文件上传
     * @desc 文件上传
     * @url /console/v1/upload
     * @method POST
     * @author wyh
     * @version v1
     * @param resource file - 文件资源 required
     * @return string save_name - 文件名
     * @return string data.image_base64 - 图片base64,文件为图片才返回
     */
    public function upload()
    {
        $filename = $this->request->file('file');

        if (!isset($filename)){
            return json(['status'=>400,'msg'=>lang('param_error')]);
        }

        $str=explode($filename->getOriginalExtension(),$filename->getOriginalName())[0];
        if(preg_match("/['!@^&]|\/|\\\|\"/",substr($str,0,strlen($str)-1))){
            return json(['status'=>400,'msg'=>lang('file_name_error')]);
        }

        $UploadLogic = new UploadLogic();

        $result = $UploadLogic->uploadHandle($filename);

        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 全局搜索
     * @desc 全局搜索
     * @url /console/v1/global_search
     * @method GET
     * @author theworld
     * @version v1
     * @param keywords string - 关键字,搜索范围:用户姓名,公司,邮箱,手机号,商品名称,商品一级分组名称,商品二级分组名称,产品ID,标识,商品名称 required
     * @return array hosts - 产品
     * @return int hosts[].id - 产品ID 
     * @return string hosts[].name - 标识
     * @return string hosts[].product_name - 商品名称
     */
    public function globalSearch()
    {
        // 接收参数
        $param = $this->request->param();
        $keywords = $param['keywords'] ?? '';
        if(!empty($keywords)){
            $hosts = (new HostModel())->searchHost($keywords);
            $data = [
                'hosts' => $hosts['list'],
            ];
        }else{
            $data = [
                'hosts' => [],
            ];
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-10
     * @title 获取前台导航
     * @desc 获取前台导航
     * @author theworld
     * @version v1
     * @url /console/v1/menu
     * @method  GET
     * @return array menu - 菜单
     * @return int menu[].id - 菜单ID
     * @return string menu[].name - 名称
     * @return string menu[].url - 网址
     * @return string menu[].icon - 图标
     * @return int menu[].parent_id - 父ID
     * @return array menu[].child - 子菜单
     * @return int menu[].child[].id - 菜单ID
     * @return string menu[].child[].name - 名称
     * @return string menu[].child[].url - 网址
     * @return string menu[].child[].icon - 图标
     * @return int menu[].child[].parent_id - 父ID
     */
    public function homeMenu(){
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new MenuModel())->homeMenu()
        ];
        return json($result);
    }

}