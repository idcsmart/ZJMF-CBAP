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
     * @title 获取安全设置
     * @desc 获取安全设置
     * @url /admin/v1/configuration/security
     * @method  GET
     * @author xiong
     * @version v1
     * @return  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @return  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @return  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @return  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @return  int captcha_width - 图形验证码宽度
     * @return  int captcha_height - 图形验证码高度
     * @return  int captcha_length - 图形验证码字符长度
     */
    public function securityList()
    {
		//实例化模型类
		$ConfigurationModel = new ConfigurationModel();
		
		//获取安全设置
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
     * @title 保存安全设置
     * @desc 保存安全设置
     * @url /admin/v1/configuration/security
     * @method  PUT
     * @author xiong
     * @version v1
     * @param  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @param  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @param  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @param  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @param  int captcha_width - 图形验证码宽度
     * @param  int captcha_height - 图形验证码高度
     * @param  int captcha_length - 图形验证码字符长度
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
		
		//保存安全设置
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
		
		//获取安全设置
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
		
		//保存安全设置
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
     * @return int cron_ticket_swhitch - 自动关闭工单开关 1开启，0关闭
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭
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
     * @return int cron_ticket_swhitch - 自动关闭工单开关 1开启，0关闭 required
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭 required
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭 required
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
		
		//保存安全设置
		$result = $ConfigurationModel->cronUpdate($param);   
		
        return json($result);
    }
}

