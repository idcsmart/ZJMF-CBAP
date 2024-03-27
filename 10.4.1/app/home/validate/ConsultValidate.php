<?php
namespace app\home\validate;

use think\Validate;

/**
 * 方案咨询验证
 */
class ConsultValidate extends Validate
{
	protected $rule = [
        'contact'   => 'require|max:255',
        'company'   => 'max:255',
        'phone'     => 'requireWithout:email|max:20',
        'email'     => 'requireWithout:phone|email',
        'matter'    => 'require|max:1000',
    ];

    protected $message  =   [
        'contact.require'       => 'please_enter_consult_contact',
        'contact.max'           => 'consult_contact_cannot_exceed_50_chars',
        'company.max'           => 'consult_company_cannot_exceed_255_chars',
        'phone.requireWithout'  => 'please_enter_consult_phone',
        'phone.max'             => 'consult_phone_cannot_exceed_20_chars',
        'email.requireWithout'  => 'please_enter_consult_email', 
        'email.email'           => 'consult_email_error', 
        'matter.require'        => 'please_enter_consult_matter', 
        'matter.max'            => 'consult_matter_cannot_exceed_1000_chars', 
    ];

    protected $scene = [
        'create' => ['contact', 'company', 'phone', 'email', 'matter'],
    ];
}