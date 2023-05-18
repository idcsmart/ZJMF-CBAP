<?php
namespace app\home\validate;

use think\Validate;

/**
 * 通用接口验证
 */
class CommonValidate extends Validate
{
	protected $rule = [
        'action'        => 'require|in:login,register,verify,update,password_reset',
        'email'         => 'requireIf:action,login|requireIf:action,register|requireIf:action,update|requireIf:action,password_reset|email',
        'phone_code'    => 'requireIf:action,login|requireIf:action,register|requireIf:action,update|requireIf:action,password_reset',
        'phone'         => 'requireIf:action,login|requireIf:action,register|requireIf:action,update|requireIf:action,password_reset|max:11|number',
    ];

    protected $message  =   [
        'action.require'            => 'param_error', 
        'action.in'                 => 'param_error', 
        'email.requireIf'           => 'please_enter_vaild_email',
        'email.email'               => 'please_enter_vaild_email',   
        'phone_code.requireIf'      => 'please_select_phone_code', 
        'phone.requireIf'           => 'please_enter_vaild_phone', 
        'phone.max'                 => 'please_enter_vaild_phone', 
        'phone.number'              => 'please_enter_vaild_phone',
    ];

    protected $scene = [
        'sened_phone_code' => ['action', 'phone_code', 'phone'],
        'sened_email_code' => ['action', 'email'],
    ];
}