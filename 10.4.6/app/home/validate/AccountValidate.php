<?php
namespace app\home\validate;

use think\Validate;

/**
 * 账户管理验证
 */
class AccountValidate extends Validate
{
    protected $regex = ['password' => '/^[^\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}\s]{6,32}$/u'];

	protected $rule = [
        'username'                  => 'max:20',
        'company'                   => 'max:255',
        'country'                   => 'max:100',
        'address'                   => 'max:255',
        'notes'                     => 'max:1000',
        'old_password'              => 'require',
        'new_password'              => 'require|regex:password|different:old_password',
        'repassword'                => 'require|confirm:new_password',
        'email'                     => 'require|email|unique:client',
        'phone_code'                => 'require',
        'phone'                     => 'require|max:11|number|unique:client,phone_code^phone',
        'code'                      => 'require|number',
        'type'                      => 'in:phone,email',
        'account'                   => 'require',
        'password'                  => 'require|regex:password',
        're_password'               => 'require|confirm:password',
        'origin_operate_password'   => 'regex:password',
        'operate_password'          => 'require|regex:password',
        're_operate_password'       => 'require|confirm:operate_password',
        'notice_open'               => 'require|in:0,1',
        'notice_method'             => 'require|in:all,email,sms',
    ];

    protected $message  =   [
        'username.max'                  => 'client_name_cannot_exceed_20_chars',
        'company.max'                   => 'company_cannot_exceed_255_chars',
        'country.max'                   => 'country_cannot_exceed_100_chars',
        'address.max'                   => 'address_cannot_exceed_255_chars',
        'notes.max'                     => 'notes_cannot_exceed_1000_chars',
        'old_password.require'          => 'please_enter_old_password', 
        'new_password.require'          => 'please_enter_new_password', 
        'new_password.regex'            => 'password_formatted_incorrectly',
        'new_password.different'        => 'new_password_cannot_same_old_password',
        'repassword.require'            => 'please_enter_password_again', 
        'repassword.confirm'            => 'passwords_not_match',
        'email.require'                 => 'please_enter_vaild_email',
        'email.email'                   => 'please_enter_vaild_email', 
        'email.unique'                  => 'email_has_been_registered',   
        'phone_code.require'            => 'please_select_phone_code', 
        'phone.require'                 => 'please_enter_vaild_phone', 
        'phone.max'                     => 'please_enter_vaild_phone', 
        'phone.number'                  => 'please_enter_vaild_phone',
        'phone.unique'                  => 'phone_has_been_registered',
        'code.require'                  => 'please_enter_code',
        'code.number'                   => 'verification_code_error',
        'type.in'                       => 'register_type_only_phone_or_email',
        'account.require'               => 'register_account_is_required',
        'password.require'              => 'register_password_is_required',
        'password.regex'                => 'password_formatted_incorrectly',
        're_password.require'           => 'please_enter_password_again',
        're_password.confirm'           => 'passwords_not_match',
        'origin_operate_password.regex' => 'password_formatted_incorrectly',
        'operate_password.require'      => 'please_enter_password',
        'operate_password.regex'        => 'password_formatted_incorrectly',
        're_operate_password.require'   => 'please_enter_password_again',
        're_operate_password.confirm'   => 'passwords_not_match',
        'notice_open.require'           => 'notice_open_require',
        'notice_open.in'                => 'notice_open_in',
        'notice_method.require'         => 'notice_method_require',
        'notice_method.in'              => 'notice_method_in',
    ];

    protected $scene = [
        'update' => ['username', 'company', 'country', 'address', 'notes', 'notice_open', 'notice_method'],
        'update_password' => ['old_password', 'new_password', 'repassword'],
        'verify_old_phone' => ['code'],
        'update_phone' => ['phone_code', 'phone', 'code'],
        'verify_old_email' => ['code'],
        'update_email' => ['email', 'code'],
        'register' => ['type', 'account','username','password','re_password'],
        'password_reset' => ['password','re_password'],
        'code_update_password' => ['code', 'password','re_password'],
        'oauth_bind' => ['type','account','phone_code','code'],
        'operate_password' => ['origin_operate_password','operate_password','re_operate_password'],
    ];
}