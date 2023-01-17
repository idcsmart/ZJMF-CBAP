<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 配置项验证
 */
class ConfigurationValidate extends Validate
{
	protected $rule = [
		# 系统设置
        'lang_admin' => 'require',
        'lang_home_open' => 'require|in:0,1',
        'lang_home' => 'require',
        'maintenance_mode' => 'require|in:0,1',
        'website_name' => 'require|max:255',
        'website_url' => 'require|max:255|url',	
        'terms_service_url' => 'max:255|url',
        'terms_privacy_url' => 'require|max:255|url',
        'system_logo' => 'require',	
		
		# 登录设置
		'register_email' => 'require|in:0,1',
		'register_phone' => 'require|in:0,1',
		'login_phone_verify' => 'require|in:0,1',
		'home_login_check_ip' => 'require|in:0,1',
		'admin_login_check_ip' => 'require|in:0,1',

		# 安全设置
		'captcha_client_register' => 'require|in:0,1',
		'captcha_client_login' => 'require|in:0,1',
		'captcha_client_login_error' => 'require|in:0,1',
		'captcha_admin_login' => 'require|in:0,1',
		'captcha_width' => 'require|between:200,400',	
		'captcha_height' => 'require|between:50,100',	
		'captcha_length' => 'require|between:4,6|integer',	
		
		# 货币设置
		'currency_code' => 'require',
		'currency_prefix' => 'require',
		'currency_suffix' => 'require',
		'recharge_open' => 'require|in:0,1',
		'recharge_min' => 'gt:0|float',
		'recharge_max' => 'egt:recharge_min|float',

		# 定时任务
		'cron_due_suspend_day' => 'number',
		'cron_due_terminate_day' => 'number',
		'cron_due_renewal_first_day' => 'number',
		'cron_due_renewal_second_day' => 'number',
		'cron_overdue_first_day' => 'number',
		'cron_overdue_second_day' => 'number',
		'cron_overdue_third_day' => 'number',
		'cron_ticket_close_day' => 'number',
		'cron_order_overdue_day' => 'number',
		'cron_due_suspend_swhitch' => 'require|in:0,1',
		'cron_due_unsuspend_swhitch' => 'require|in:0,1',
		'cron_due_terminate_swhitch' => 'require|in:0,1',
		'cron_due_renewal_first_swhitch' => 'require|in:0,1',
		'cron_due_renewal_second_swhitch' => 'require|in:0,1',
		'cron_overdue_first_swhitch' => 'require|in:0,1',
		'cron_overdue_second_swhitch' => 'require|in:0,1',
		'cron_overdue_third_swhitch' => 'require|in:0,1',
		'cron_ticket_close_swhitch' => 'require|in:0,1',
		'cron_aff_swhitch' => 'require|in:0,1',
		'cron_order_overdue_swhitch' => 'require|in:0,1',

		# 主题设置
		'admin_theme' => 'require',
		'clientarea_theme' => 'require',

        # 实名设置
        'certification_open' => 'require|in:0,1',
        'certification_approval' => 'require|in:0,1',
        'certification_notice' => 'require|in:0,1',
        'certification_update_client_name' => 'require|in:0,1',
        'certification_upload' => 'require|in:0,1',
        'certification_update_client_phone' => 'require|in:0,1',
        'certification_uncertified_suspended_host' => 'require|in:0,1',

    ];

    protected $message  =  [
    	# 系统设置
		'lang_admin.require' => 'configuration_admin_default_language_cannot_empty',
        'lang_home_open.require' => 'configuration_home_default_language_open_cannot_empty',
        'lang_home_open.in' => 'configuration_home_default_language_open',
        'lang_home.require' => 'configuration_home_default_language_cannot_empty',
        'maintenance_mode.require' => 'configuration_maintenance_mode_cannot_empty',
        'maintenance_mode.in' => 'configuration_maintenance_mode',
        'website_name.require' => 'configuration_website_name',
        'website_name.max' => 'configuration_website_name_cannot_exceed_255_chars',
        'website_url.require' => 'configuration_website_url',
        'website_url.max' => 'configuration_website_url_cannot_exceed_255_chars',
        'website_url.url' => 'configuration_website_url_error',
        'terms_service_url.max' => 'configuration_terms_service_url_cannot_exceed_255_chars',
        'terms_service_url.url' => 'configuration_website_url_error',
        'terms_privacy_url.require' => 'configuration_terms_privacy_url',
        'terms_privacy_url.max' => 'configuration_terms_privacy_url_cannot_exceed_255_chars',
        'terms_privacy_url.url' => 'configuration_website_url_error',
        'system_logo.require' => 'configuration_system_logo',
		
		# 登录设置
		'register_email.require' => 'configuration_register_email_cannot_empty',
		'register_email.in' => 'configuration_register_email',
		'register_phone.require' => 'configuration_register_phone_cannot_empty',
		'register_phone.in' => 'configuration_register_phone',
		'login_phone_verify.require' => 'configuration_login_phone_verify_cannot_empty',
		'login_phone_verify.in' => 'configuration_login_phone_verify',
		
		# 安全设置
		'captcha_client_register.require' => 'configuration_captcha_client_register_cannot_empty',
		'captcha_client_register.in' => 'configuration_captcha_client_register',
		'captcha_client_login.require' => 'configuration_captcha_client_login_cannot_empty',
		'captcha_client_login.in' => 'configuration_captcha_client_login',
		'captcha_client_login_error.require' => 'configuration_captcha_client_login_error_cannot_empty',
		'captcha_client_login_error.in' => 'configuration_captcha_client_login_error',
		'captcha_admin_login.require' => 'configuration_captcha_admin_login_cannot_empty',
		'captcha_admin_login.in' => 'configuration_captcha_admin_login',
		'captcha_width.require' => 'configuration_captcha_width_cannot_empty',
		'captcha_width.between' => 'configuration_captcha_width',
		'captcha_height.require' => 'configuration_captcha_height_cannot_empty',
		'captcha_height.between' => 'configuration_captcha_height',
		'captcha_length.require' => 'configuration_captcha_length_cannot_empty',
		'captcha_length.between' => 'configuration_captcha_length',
		'captcha_length.integer' => 'configuration_captcha_length',
		
		# 货币设置
		'currency_code.require' => 'configuration_currency_code_cannot_empty',
		'currency_prefix.require' => 'configuration_currency_prefix_cannot_empty',
		'currency_suffix.require' => 'configuration_currency_suffix_cannot_empty',
		'recharge_open.require' => 'configuration_recharge_open_cannot_empty',
		'recharge_open.in' => 'configuration_recharge_open',
		'recharge_min.gt' => 'configuration_recharge_min_float',
		'recharge_min.float' => 'configuration_recharge_min_float',
		'recharge_max.egt' => 'configuration_recharge_max_egt_recharge_min',

		# 定时任务
		
		'cron_due_suspend_day.number' => 'configuration_cron_due_suspend_day_cannot_empty',		
		'cron_due_terminate_day.number' => 'configuration_cron_due_terminate_day_cannot_empty',		
		'cron_due_renewal_first_day.number' => 'configuration_cron_due_renewal_first_day_cannot_empty',	
		'cron_due_renewal_second_day.number' => 'configuration_cron_due_renewal_second_day_cannot_empty',		
		'cron_overdue_first_day.number' => 'configuration_cron_overdue_first_day_cannot_empty',
		'cron_overdue_second_day.number' => 'configuration_cron_overdue_second_day_cannot_empty',
		'cron_overdue_third_day.number' => 'configuration_cron_overdue_third_day_cannot_empty',
		'cron_ticket_close_day.number' => 'configuration_cron_ticket_close_day_cannot_empty',
		'cron_order_overdue_day.number' => 'configuration_cron_order_overdue_day_cannot_empty',
		
		'cron_due_suspend_swhitch.require' => 'configuration_cron_due_suspend_swhitch',		
		'cron_due_unsuspend_swhitch.require' => 'configuration_cron_due_unsuspend_swhitch',		
		'cron_due_terminate_swhitch.require' => 'configuration_cron_due_terminate_swhitch',	
		'cron_due_renewal_first_swhitch.require' => 'configuration_cron_due_renewal_first_swhitch',		
		'cron_due_renewal_second_swhitch.require' => 'configuration_cron_due_renewal_second_swhitch',
		'cron_overdue_first_swhitch.require' => 'configuration_cron_overdue_first_swhitch',
		'cron_overdue_second_swhitch.require' => 'configuration_cron_overdue_second_swhitch',
		'cron_overdue_third_swhitch.require' => 'configuration_cron_overdue_third_swhitch',
		'cron_ticket_close_swhitch.require' => 'configuration_cron_ticket_close_swhitch',
		'cron_aff_swhitch.require' => 'configuration_cron_aff_swhitch',
		'cron_order_overdue_swhitch.require' => 'configuration_cron_order_overdue_swhitch',
		
		'cron_due_suspend_swhitch.in' => 'configuration_cron_due_suspend_swhitch',		
		'cron_due_unsuspend_swhitch.in' => 'configuration_cron_due_unsuspend_swhitch',		
		'cron_due_terminate_swhitch.in' => 'configuration_cron_due_terminate_swhitch',	
		'cron_due_renewal_first_swhitch.in' => 'configuration_cron_due_renewal_first_swhitch',		
		'cron_due_renewal_second_swhitch.in' => 'configuration_cron_due_renewal_second_swhitch',
		'cron_overdue_first_swhitch.in' => 'configuration_cron_overdue_first_swhitch',
		'cron_overdue_second_swhitch.in' => 'configuration_cron_overdue_second_swhitch',
		'cron_overdue_third_swhitch.in' => 'configuration_cron_overdue_third_swhitch',
		'cron_ticket_close_swhitch.in' => 'configuration_cron_ticket_close_swhitch',
		'cron_aff_swhitch.in' => 'configuration_cron_aff_swhitch',
		'cron_order_overdue_swhitch.in' => 'configuration_cron_order_overdue_swhitch',

		# 主题设置
		'admin_theme.require' => 'configuration_theme_admin_theme_cannot_empty',
		'clientarea_theme.require' => 'configuration_theme_clientarea_theme_cannot_empty',

        # 实名设置
		'certification_open.require' => 'configuration_certification_open_require',
		'certification_approval.require' => 'configuration_certification_approval_require',
		'certification_notice.require' => 'configuration_certification_notice_require',
		'certification_update_client_name.require' => 'configuration_certification_update_client_name_require',
        'certification_upload.require' => 'configuration_certification_upload_require',
		'certification_update_client_phone.require' => 'configuration_certification_update_client_phone_require',
		'certification_uncertified_suspended_host.require' => 'configuration_certification_uncertified_suspended_host_require',
    ];
    protected $scene = [
        'system_update' => ['lang_admin','lang_home_open','lang_home','maintenance_mode','website_name','website_url','terms_service_url'],
        'login_update' => ['register_email','register_phone','login_phone_verify','home_login_check_ip','admin_login_check_ip'],
        'security_update' => ['captcha_client_register','captcha_client_login','captcha_client_login_error','captcha_admin_login'],
        'currency_update' => ['currency_code','currency_prefix','recharge_open','recharge_min','recharge_max'],
        'cron_update' => 
	        [
			 'cron_due_suspend_day',
			'cron_due_terminate_day',
			'cron_due_renewal_first_day',
			'cron_due_renewal_second_day',
			'cron_overdue_first_day',
			'cron_overdue_second_day',
			'cron_overdue_third_day',
			'cron_ticket_close_day',
			'cron_due_suspend_swhitch',
			'cron_due_unsuspend_swhitch',
			'cron_due_terminate_swhitch',
			'cron_due_renewal_first_swhitch',
			'cron_due_renewal_second_swhitch',
			'cron_overdue_first_swhitch',
			'cron_overdue_second_swhitch',
			'cron_overdue_third_close_swhitch',
			'cron_ticket_swhitch',
			'cron_aff_swhitch',
			'cron_order_overdue_swhitch',
			'cron_order_overdue_day',
			],
		'theme_update' => ['admin_theme', 'clientarea_theme'],
		'certification_update' => [
		    'certification_open',
            'certification_approval',
            'certification_notice',
            'certification_update_client_name',
            'certification_update_client_phone',
            'certification_uncertified_suspended_host',
            'certification_upload'
        ],
    ];
}