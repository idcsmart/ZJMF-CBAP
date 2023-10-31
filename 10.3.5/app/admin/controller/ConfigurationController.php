<?php
namespace app\admin\controller;
use app\common\model\ConfigurationModel;
use app\admin\validate\ConfigurationValidate;
use think\captcha\Captcha;
/**
 * @title 系统设置
 * @desc 系统设置
 * @use app\admin\controller\ConfigurationController
 */
class ConfigurationController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new ConfigurationValidate();
    }
    /**
     * 时间 2022-5-10
     * @title 获取系统设置
     * @desc 获取系统设置
     * @url /admin/v1/configuration/system
     * @method  GET
     * @author xiong
     * @version v1
     * @return  string lang_admin - 后台默认语言
     * @return  int lang_home_open - 前台多语言开关:1开启0关闭
     * @return  string lang_home - 前台默认语言
     * @return  int maintenance_mode - 维护模式开关:1开启0关闭
     * @return  string maintenance_mode_message - 维护模式内容
     * @return  string website_name - 网站名称
     * @return  string website_url - 网站域名地址
     * @return  string terms_service_url - 服务条款地址
     * @return  string terms_privacy_url - 隐私条款地址
     * @return  string system_logo - 系统LOGO
     */
    public function systemList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取系统设置
		$data=$ConfigurationModel->systemList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存系统设置
     * @desc 保存系统设置
     * @url /admin/v1/configuration/system
     * @method  PUT
     * @author xiong
     * @version v1
     * @param  string lang_admin - 后台默认语言
     * @param  int lang_home_open - 前台多语言开关:1开启0关闭
     * @param  string lang_home - 前台默认语言
     * @param  int maintenance_mode - 维护模式开关:1开启0关闭
     * @param  string maintenance_mode_message - 维护模式内容
     * @param  string website_name - 网站名称
     * @param  string website_url - 网站域名地址
     * @param  string terms_service_url - 服务条款地址
     * @param  string terms_privacy_url - 隐私条款地址
     * @param  string system_logo - 系统LOGO
     */
    public function systemUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('system_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存系统设置
		$result = $ConfigurationModel->systemUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取登录设置
     * @desc 获取登录设置
     * @url /admin/v1/configuration/login
     * @method  GET
     * @author xiong
     * @version v1
     * @return  int register_email - 邮箱注册开关:1开启0关闭
     * @return  int register_phone - 手机号注册开关:1开启0关闭
     * @return  int login_phone_verify - 手机号登录短信验证开关:1开启0关闭
     * @return  int home_login_check_ip - 前台登录检查IP:1开启0关闭
     * @return  int admin_login_check_ip - 后台登录检查IP:1开启0关闭
     */
    public function loginList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取登录设置
		$data=$ConfigurationModel->loginList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存登录设置
     * @desc 保存登录设置
     * @url /admin/v1/configuration/login
     * @method  PUT
     * @author xiong
     * @version v1
     * @param  int register_email - 邮箱注册开关:1开启0关闭
     * @param  int register_phone - 手机号注册开关:1开启0关闭
     * @param  int login_phone_verify - 手机号登录短信验证开关:1开启0关闭
     * @param  int home_login_check_ip - 前台登录检查IP:1开启0关闭
     * @param  int admin_login_check_ip - 后台登录检查IP:1开启0关闭
     */
    public function loginUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('login_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存系统设置
		$result = $ConfigurationModel->loginUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取图形验证码预览
     * @desc 获取图形验证码预览
     * @url /admin/v1/configuration/security/captcha
     * @method  GET
     * @author xiong
     * @version v1
     * @param  int captcha_width - 图形验证码宽度 required
     * @param  int captcha_height - 图形验证码高度 required
     * @param  int captcha_length - 图形验证码字符长度 required
     * @return  string captcha - 图形验证码图片
     */
    public function securityCaptcha()
    {
		//接收参数
		$param = $this->request->param();
		$config = [
            'imageW' => $param['captcha_width'],
            'imageH' => $param['captcha_height'],
            'length' => $param['captcha_length'],
            'codeSet' => '1234567890',
        ];
        $Captcha = new Captcha(app('config'),app('session'));
        $response = $Captcha->create($config);
		$result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
				'captcha' => 'data:png;base64,' . base64_encode($response->getData()),
			]
        ];
        return json($result);
    }  
	/**
     * 时间 2022-5-10
     * @title 获取验证码设置
     * @desc 获取验证码设置
     * @url /admin/v1/configuration/security
     * @method  GET
     * @author xiong
     * @version v1
     * @return  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @return  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @return  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @return  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @return  string captcha_plugin - 验证码插件(从/admin/v1/captcha_list接口获取)
     * @return  int code_client_email_register - 邮箱注册数字验证码开关:1开启0关闭
     */
    public function securityList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取验证码设置
		$data=$ConfigurationModel->securityList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存验证码设置
     * @desc 保存验证码设置
     * @url /admin/v1/configuration/security
     * @method  PUT
     * @author xiong
     * @version v1
     * @param  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @param  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @param  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @param  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @param  string captcha_plugin - 验证码插件(从/admin/v1/captcha_list接口获取)
     * @param  int code_client_email_register - 邮箱注册数字验证码开关:1开启0关闭
     */
    public function securityUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('security_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存验证码设置
		$result = $ConfigurationModel->securityUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取货币设置
     * @desc 获取货币设置
     * @url /admin/v1/configuration/currency
     * @method  GET
     * @author xiong
     * @version v1
     * @return  string currency_code - 货币代码
     * @return  string currency_prefix - 货币符号
     * @return  string currency_suffix - 货币后缀
     * @return  int recharge_open - 启用充值:1开启0关闭
     * @return  int recharge_min - 单笔最小金额
     */
    public function currencyList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取验证码设置
		$data=$ConfigurationModel->currencyList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存货币设置
     * @desc 保存货币设置
     * @url /admin/v1/configuration/currency
     * @method  PUT
     * @author xiong
     * @version v1
     * @param  string currency_code - 货币代码
     * @param  string currency_prefix - 货币符号
     * @param  string currency_suffix - 货币后缀
     * @param  int recharge_open - 启用充值:1开启0关闭
     * @param  int recharge_min - 单笔最小金额
     */
    public function currencyUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('currency_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存验证码设置
		$result = $ConfigurationModel->currencyUpdate($param);   
		
        return json($result);
    }
    /**
     * 时间 2022-5-10
     * @title 获取自动化设置
     * @desc 获取自动化设置
     * @url /admin/v1/configuration/cron
     * @method  GET
     * @author xiong
     * @version v1
     * @return int cron_shell - 自动化脚本
     * @return int cron_status - 自动化状态,正常返回success,不正常返回error
     * @return int cron_due_suspend_swhitch - 产品到期暂停开关 1开启，0关闭
     * @return int cron_due_suspend_day - 产品到期暂停X天后暂停
     * @return int cron_due_unsuspend_swhitch - 财务原因产品暂停后付款自动解封开关 1开启，0关闭
     * @return int cron_due_terminate_swhitch - 产品到期删除开关 1开启，0关闭
     * @return int cron_due_terminate_day - 产品到期X天后删除
     * @return int cron_due_renewal_first_swhitch - 续费第一次提醒开关 1开启，0关闭
     * @return int cron_due_renewal_first_day - 续费X天后到期第一次提醒
     * @return int cron_due_renewal_second_swhitch - 续费第二次提醒开关 1开启，0关闭
     * @return int cron_due_renewal_second_day - 续费X天后到期第二次提醒
     * @return int cron_overdue_first_swhitch - 产品逾期第一次提醒开关 1开启，0关闭
     * @return int cron_overdue_first_day - 产品逾期X天后第一次提醒
     * @return int cron_overdue_second_swhitch - 产品逾期第二次提醒开关 1开启，0关闭
     * @return int cron_overdue_second_day - 产品逾期X天后第二次提醒
     * @return int cron_overdue_third_swhitch - 产品逾期第三次提醒开关 1开启，0关闭
     * @return int cron_overdue_third_day - 产品逾期X天后第三次提醒
     * @return int cron_ticket_close_swhitch - 自动关闭工单开关 1开启，0关闭
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭
     * @return int cron_order_overdue_swhitch - 订单未付款通知开关 1开启，0关闭 required
     * @return int cron_order_overdue_day - 订单未付款X天后通知 required
     * @return int cron_task_shell - 任务队列命令 required
     * @return int cron_task_status - 任务队列最新状态:success成功，error失败 required
     * @return int cron_order_unpaid_delete_swhitch - 订单自动删除开关 1开启，0关闭 required
     * @return int cron_order_unpaid_delete_day - 订单未付款X天后自动删除 required
     * @return int cron_day_start_time - 定时任务开始时间 required
     */
    public function cronList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取自动化设置
		$data=$ConfigurationModel->cronList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 保存自动化设置
     * @desc 保存自动化设置
     * @url /admin/v1/configuration/cron
     * @method  PUT
     * @author xiong
     * @version v1
     * @return int cron_due_suspend_swhitch - 产品到期暂停开关1开启，0关闭 required
     * @return int cron_due_suspend_day - 产品到期暂停X天后暂停 required
     * @return int cron_due_unsuspend_swhitch - 财务原因产品暂停后付款自动解封开关1开启，0关闭 required
     * @return int cron_due_terminate_swhitch - 产品到期删除开关1开启，0关闭 required
     * @return int cron_due_terminate_day - 产品到期X天后删除 required
     * @return int cron_due_renewal_first_swhitch - 续费第一次提醒开关1开启，0关闭 required
     * @return int cron_due_renewal_first_day - 续费X天后到期第一次提醒 required
     * @return int cron_due_renewal_second_swhitch - 续费第二次提醒开关1开启，0关闭 required
     * @return int cron_due_renewal_second_day - 续费X天后到期第二次提醒 required
     * @return int cron_overdue_first_swhitch - 产品逾期第一次提醒开关1开启，0关闭 required
     * @return int cron_overdue_first_day - 产品逾期X天后第一次提醒 required
     * @return int cron_overdue_second_swhitch - 产品逾期第二次提醒开关1开启，0关闭 required
     * @return int cron_overdue_second_day - 产品逾期X天后第二次提醒 required
     * @return int cron_overdue_third_swhitch - 产品逾期第三次提醒开关1开启，0关闭 required
     * @return int cron_overdue_third_day - 产品逾期X天后第三次提醒 required
     * @return int cron_ticket_close_swhitch - 自动关闭工单开关 1开启，0关闭 required
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭 required
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭 required
     * @return int cron_order_overdue_swhitch - 订单未付款通知开关 1开启，0关闭 required
     * @return int cron_order_overdue_day - 订单未付款X天后通知 required
     * @return int cron_order_unpaid_delete_swhitch - 订单自动删除开关 1开启，0关闭 required
     * @return int cron_order_unpaid_delete_day - 订单未付款X天后自动删除 required
     * @return int cron_day_start_time - 定时任务开始时间 required
     */
    public function cronUpdate()
    {
		//接收参数
		$param = $this->request->param();
		
        //参数验证
        if (!$this->validate->scene('cron_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//保存验证码设置
		$result = $ConfigurationModel->cronUpdate($param);   
		
        return json($result);
    }

    /**
     * 时间 2022-08-12
     * @title 获取主题设置
     * @desc 获取主题设置
     * @url /admin/v1/configuration/theme
     * @method  GET
     * @author theworld
     * @version v1
     * @return string admin_theme - 后台主题
     * @return string clientarea_theme - 会员中心主题
     * @return int web_switch - 官网开关0关闭1开启
     * @return string web_theme - 官网主题
     * @return array admin_theme_list - 后台主题列表
     * @return string admin_theme_list[].name - 名称
     * @return string admin_theme_list[].img - 图片
     * @return array clientarea_theme_list - 会员中心主题列表
     * @return string clientarea_theme_list[].name - 名称
     * @return string clientarea_theme_list[].img - 图片
     * @return array web_theme_list - 官网主题列表
     * @return string web_theme_list[].name - 名称
     * @return string web_theme_list[].img - 图片
     */
    public function themeList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();
        
        //获取主题设置
        $data = $ConfigurationModel->themeList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2022-08-12
     * @title 保存主题设置
     * @desc 保存主题设置
     * @url /admin/v1/configuration/theme
     * @method  PUT
     * @author theworld
     * @version v1
     * @param string admin_theme - 后台主题 required
     * @param string clientarea_theme - 会员中心主题 required
     * @param int web_switch - 官网开关0关闭1开启 required
     * @param string web_theme - 官网主题 required
     */
    public function themeUpdate()
    {
        //接收参数
        $param = $this->request->param();
        
        //参数验证
        if (!$this->validate->scene('theme_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();
        
        //保存主题设置
        $result = $ConfigurationModel->themeUpdate($param);   
        
        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 获取实名设置
     * @desc 获取实名设置
     * @url /admin/v1/configuration/certification
     * @method  GET
     * @author wyh
     * @version v1
     * @return int certification_open - 实名认证是否开启:1开启默认,0关
     * @return int certification_approval - 是否人工复审:1开启默认，0关
     * @return int certification_notice - 审批通过后,是否通知客户:1通知默认,0否
     * @return int certification_update_client_name - 是否自动更新姓名:1是,0否默认
     * @return int certification_upload - 是否需要上传证件照:1是,0否默认
     * @return int certification_update_client_phone - 手机一致性:1是,0否默认
     * @return int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认
     */
    public function certificationList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取主题设置
        $data = $ConfigurationModel->certificationList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-12
     * @title 保存实名设置
     * @desc 保存实名设置
     * @url /admin/v1/configuration/certification
     * @method  PUT
     * @author theworld
     * @version v1
     * @param int certification_open - 实名认证是否开启:1开启默认,0关 required
     * @param int certification_approval - 是否人工复审:1开启默认，0关 required
     * @param int certification_notice - 审批通过后,是否通知客户:1通知默认,0否 required
     * @param int certification_update_client_name - 是否自动更新姓名:1是,0否默认 required
     * @param int certification_upload - 是否需要上传证件照:1是,0否默认 required
     * @param int certification_update_client_phone - 手机一致性:1是,0否默认 required
     * @param int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认 required
     */
    public function certificationUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('certification_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存主题设置
        $result = $ConfigurationModel->certificationUpdate($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 获取信息配置
     * @desc 获取信息配置
     * @url /admin/v1/configuration/info
     * @method  GET
     * @author theworld
     * @version v1
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
     */
    public function infoList()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取信息配置
        $data = $ConfigurationModel->infoList();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 保存信息配置
     * @desc 保存信息配置
     * @url /admin/v1/configuration/info
     * @method  PUT
     * @author theworld
     * @version v1
     * @param string enterprise_name - 企业名称 required
     * @param string enterprise_telephone - 企业电话 required
     * @param string enterprise_mailbox - 企业邮箱 required
     * @param string enterprise_qrcode - 企业二维码 required
     * @param string online_customer_service_link - 在线客服链接 required
     * @param string icp_info - ICP信息 required
     * @param string icp_info_link - ICP信息信息链接 required
     * @param string public_security_network_preparation - 公安网备 required
     * @param string public_security_network_preparation_link - 公安网备链接 required
     * @param string telecom_appreciation - 电信增值 required
     * @param string copyright_info - 版权信息 required
     * @param string official_website_logo - 官网LOGO required
     * @param string cloud_product_link - 云产品跳转链接 required
     * @param string dcim_product_link - DCIM产品跳转链接 required
     */
    public function infoUpdate()
    {
        //接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('info_update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存信息配置
        $result = $ConfigurationModel->infoUpdate($param);

        return json($result);
    }

    /**
     * 时间 2023-09-07
     * @title debug页面
     * @desc debug页面
     * @url /admin/v1/configuration/debug
     * @method  GET
     * @author wyh
     * @version v1
     * @return string debug_model - 1开启debug模式
     * @return string debug_model_auth - debug模式授权码
     * @return string debug_model_expire_time - 到期时间
     */
    public function debugInfo()
    {
        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //获取信息配置
        $data = $ConfigurationModel->debugInfo();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];

        return json($result);
    }

    /**
     * 时间 2023-09-07
     * @title 保存debug页面
     * @desc 保存debug页面
     * @url /admin/v1/configuration/debug
     * @method  PUT
     * @author wyh
     * @version v1
     * @param string debug_model - 1开启debug模式 required
     */
    public function debug()
    {
        //接收参数
        $param = $this->request->param();

        //实例化模型类
        $ConfigurationModel = new ConfigurationModel();

        //保存信息配置
        $result = $ConfigurationModel->debug($param);

        return json($result);
    }

}

