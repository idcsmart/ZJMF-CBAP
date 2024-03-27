<?php
namespace app\home\validate;

use think\Validate;

/**
 * 意见反馈验证
 */
class FeedbackValidate extends Validate
{
	protected $rule = [
        'type'          => 'require|integer|gt:0',
        'title'         => 'require|max:255',
        'description'   => 'require',
        'attachment'    => 'array',
        'contact'       => 'max:255',
    ];

    protected $message  =   [
        'type.require'          => 'param_error',
        'type.integer'          => 'param_error',
        'type.gt'               => 'param_error',
        'title.require'         => 'please_enter_feedback_title',
        'title.max'             => 'feedback_title_cannot_exceed_255_chars',
        'description.require'   => 'please_enter_feedback_description', 
        'attachment.array'      => 'param_error', 
        'contact.max'           => 'feedback_contact_cannot_exceed_255_chars', 
    ];

    protected $scene = [
        'create' => ['type', 'title', 'description', 'attachment', 'contact'],
    ];
}