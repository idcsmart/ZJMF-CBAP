<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 通知发送管理
 */
class NoticeSettingValidate extends Validate
{
	protected $rule = [
		'sms_global_template' 		=> 'integer',
		'sms_template' 				=> 'integer',
		'email_template' 			=> 'integer',
		'sms_enable' 				=> 'in:0,1',
		'email_enable' 				=> 'in:0,1',
    ];

    protected $message  =   [
    	'sms_global_template.integer'   => 'notice_setting_sms_global_template_error',
    	'sms_template.integer'     		=> 'notice_setting_sms_template_error',
    	'email_template.integer'     	=> 'notice_setting_email_template_error',
    	'sms_enable.in'     			=> 'notice_setting_sms_enable_error',
    	'email_enable.in'     			=> 'notice_setting_email_enable_error',
    ];

    protected $scene = [
        //'update' => ['sms_global_template','sms_template', 'email_template', 'sms_enable', 'email_enable']
        'update' => ['sms_enable', 'email_enable']
    ];
}