<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 邮件模板验证
 */
class NoticeEmailValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'subject'       => 'require|max:100',
        'message'       => 'require',
        'email'       => 'require|email',
    ];

    protected $message  =   [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'subject.require'           => 'please_enter_email_subject',
        'subject.max'               => 'email_subject_cannot_exceed_100_chars',
        'message.require'           => 'please_enter_email_message',
        'email.require'             => 'email_cannot_be_empty',
        'email.email'               => 'email_format_error',
    ];

    protected $scene = [
        'create' => ['subject', 'message'],
        'update' => ['id', 'subject', 'message'],
        'test' => ['id', 'email'],
    ];
}