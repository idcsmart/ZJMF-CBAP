<?php

namespace app\home\controller;

use app\admin\model\PluginModel;
use app\common\logic\UploadLogic;
use app\common\model\CountryModel;
use app\common\logic\VerificationCodeLogic;
use app\home\validate\CommonValidate;
use think\facade\Cache;
use app\common\model\HostModel;
use app\common\model\MenuModel;
use app\home\model\ClientareaAuthModel;
use app\common\model\FeedbackModel;
use app\common\model\FeedbackTypeModel;
use app\common\model\ConsultModel;
use app\common\model\FriendlyLinkModel;
use app\common\model\HonorModel;
use app\common\model\PartnerModel;
use app\home\validate\FeedbackValidate;
use app\home\validate\ConsultValidate;

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
     * @return string html - html文档
     */
    public function captcha()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'html' => get_captcha()
            ]
        ];

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
     * @return string terms_privacy_url - 隐私条款地址
     * @return string login_phone_verify 1 手机号登录短信验证开关 1开启，0关闭
     * @return string captcha_client_register 1 客户注册图形验证码开关  1开启，0关闭
     * @return string captcha_client_login 1 客户登录图形验证码开关  1开启，0关闭
     * @return string captcha_client_login_error 1 客户登录失败图形验证码开关  1开启，0关闭
     * @return string captcha_client_login_error_3_times 1 客户登录失败3次
     * @return string register_email 1 邮箱注册开关 1开启，0关闭
     * @return string register_phone 1 手机号注册开关 1开启，0关闭
     * @return string recharge_open 1 启用充值:1启用,0否
     * @return string recharge_min 1 单笔最小金额
     * @return string recharge_max 1 单笔最大金额
     * @return string currency_code CNY 货币代码
     * @return string currency_prefix ￥ 货币符号
     * @return string currency_suffix 元 货币后缀
     * @return string code_client_email_register 0 邮箱注册数字验证码开关:1开启0关闭
     * @return string system_logo - 系统LOGO
     * @return string put_on_record - 备案信息
     * @return string enterprise_name - 企业名称
     * @return string enterprise_telephone - 企业电话
     * @return string enterprise_mailbox - 企业邮箱
     * @return string enterprise_qrcode - 企业二维码
     * @return string online_customer_service_link - 在线客服链接
     * @return string icp_info - ICP信息
     * @return string icp_info_link - ICP信息信息链接
     * @return string public_security_network_preparation - 公安网备
     * @return string public_security_network_preparation_link - 公安网备链接
     * @return string telecom_appreciation - 电信增值
     * @return string copyright_info - 版权信息
     * @return string official_website_logo - 官网LOGO
     * @return string cloud_product_link - 云产品跳转链接
     * @return string dcim_product_link - DCIM产品跳转链接
     * @return array feedback_type - 意见反馈类型
     * @return int feedback_type[].id - 意见反馈类型ID 
     * @return string feedback_type[].name - 名称 
     * @return string feedback_type[].description - 描述 
     * @return array friendly_link - 友情链接
     * @return int friendly_link[].id - 友情链接ID 
     * @return string friendly_link[].name - 名称 
     * @return string friendly_link[].url - 链接地址 
     * @return array honor - 荣誉资质
     * @return int honor[].id - 荣誉资质ID 
     * @return string honor[].name - 名称 
     * @return string honor[].img - 图片地址 
     * @return array partner - 合作伙伴
     * @return int partner[].id - 合作伙伴ID 
     * @return string partner[].name - 名称 
     * @return string partner[].img - 图片地址 
     * @return string partner[].description - 描述
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
            'terms_privacy_url',
            'login_phone_verify',
            'captcha_client_register',
            'captcha_client_login',
            'captcha_client_login_error',
            'register_email',
            'register_phone',
            'recharge_open',
            'recharge_min',
            'recharge_max',
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'code_client_email_register',
            'system_logo',
            'put_on_record',
            'enterprise_name',
            'enterprise_telephone',
            'enterprise_mailbox',
            'enterprise_qrcode',
            'online_customer_service_link',
            'icp_info',
            'icp_info_link',
            'public_security_network_preparation',
            'public_security_network_preparation_link',
            'telecom_appreciation',
            'copyright_info',
            'official_website_logo',
            'cloud_product_link',
            'dcim_product_link',
        ];

        //$data = configuration($setting);
		$data = array_merge($lang,configuration($setting));
        $data['system_logo'] = config('idcsmart.system_logo_url') . $data['system_logo'];
        $account = $param['account']??'';

        // 获取意见反馈类型
        $FeedbackTypeModel = new FeedbackTypeModel();
        $feedbackType = $FeedbackTypeModel->feedbackTypeList();
        $data['feedback_type'] = $feedbackType['list'];

        // 获取友情链接
        $FriendlyLinkModel = new FriendlyLinkModel();
        $friendlyLink = $FriendlyLinkModel->friendlyLinkList();
        $data['friendly_link'] = $friendlyLink['list'];

        // 获取荣誉资质
        $HonorModel = new HonorModel();
        $honor = $HonorModel->honorList();
        $data['honor'] = $honor['list'];

        // 获取意见反馈类型
        $PartnerModel = new PartnerModel();
        $partner = $PartnerModel->partnerList();
        $data['partner'] = $partner['list'];

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

        if (empty($filename->getOriginalExtension())){
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

    /**
     * 时间 2022-5-27
     * @title 权限列表
     * @desc 权限列表
     * @author theworld
     * @version v1
     * @url /console/v1/auth
     * @method  GET
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return array list[].rules - 权限规则标题
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return string list[].child[].rules - 权限规则标题
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return string list[].child[].child[].rules - 权限规则标题
     */
    public function authList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ClientareaAuthModel())->authList()
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 提交意见反馈
     * @desc 提交意见反馈
     * @url /console/v1/feedback
     * @method POST
     * @author theworld
     * @version v1
     * @param int type - 类型 required
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param array attachment - 附件
     * @param string contact - 联系方式
     */
    public function createFeedback()
    {
        // 接收参数
        $param = $this->request->param();

        $FeedbackValidate = new FeedbackValidate();
        // 参数验证
        if (!$FeedbackValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FeedbackModel = new FeedbackModel();
        
        // 提交意见反馈
        $result = $FeedbackModel->createFeedback($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 提交方案咨询
     * @desc 提交方案咨询
     * @url /console/v1/consult
     * @method POST
     * @author theworld
     * @version v1
     * @param string contact - 联系人 required
     * @param string company - 公司名称
     * @param string phone - 手机号码 手机号码和邮箱二选一必填
     * @param string email - 联系邮箱 手机号码和邮箱二选一必填
     * @param string matter - 咨询产品 required
     */
    public function createConsult()
    {
        // 接收参数
        $param = $this->request->param();

        $ConsultValidate = new ConsultValidate();
        // 参数验证
        if (!$ConsultValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ConsultModel = new ConsultModel();
        
        // 提交方案咨询
        $result = $ConsultModel->createConsult($param);

        return json($result);
    }

}