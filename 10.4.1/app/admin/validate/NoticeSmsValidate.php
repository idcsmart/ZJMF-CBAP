<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 短信模板管理验证
 */
class NoticeSmsValidate extends Validate
{
	protected $rule = [
		'id' 						=> 'require|integer|gt:0',
		'type' 						=> 'require|in:0,1',
		'title' 					=> 'require|max:50',
		'content' 					=> 'require|max:255',
		'notes' 					=> 'max:1000',
		'status' 					=> 'require|in:0,2,3',
		'phone_code' 				=> 'integer',
		'phone' 					=> 'require|integer',
    ];

    protected $message  =   [
    	'id.require'     					=> 'sms_template_is_not_exist',
    	'id.integer'     					=> 'id_error',
    	'id.gt'     					=> 'id_error',
    	'type.require'     					=> 'sms_please_enter_sms_type',
    	'type.in'     						=> 'sms_type_must',
    	'title.require'     				=> 'sms_title_cannot_empty',
    	'title.max'     					=> 'sms_title_cannot_exceed_50_chars',
    	'content.require'     				=> 'sms_please_enter_content',
    	'content.max'     					=> 'sms_content_cannot_exceed_255_chars',
    	'notes.max'     					=> 'sms_notes_cannot_exceed_1000_chars',
    	'status.require'     				=> 'sms_please_enter_sms_status',
    	'status.in'     					=> 'sms_status_error',
    	'phone_code.integer'     			=> 'sms_area_code_must_be_integer',
		'phone.require'     				=> 'sms_phone_number_cannot_be_empty',
    	'phone.integer'     				=> 'sms_phone_number_must_be_integer',
    ];

    protected $scene = [
        'create' => ['type', 'title', 'content', 'status','notes'],
        'update' => ['id', 'type', 'title', 'content', 'status','notes'],
        'test' => ['id', 'phone_code', 'phone']
    ];
}