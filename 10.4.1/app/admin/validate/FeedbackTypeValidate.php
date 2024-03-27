<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 意见反馈类型验证
 */
class FeedbackTypeValidate extends Validate
{
	protected $rule = [
		'id'            => 'require|integer|gt:0',
        'name'          => 'require|max:255',
        'description'   => 'require',
    ];

    protected $message = [
    	'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'name.require'          => 'please_enter_feedback_type_name',
        'name.max'              => 'feedback_type_name_cannot_exceed_255_chars',
        'description.require'   => 'please_enter_feedback_type_description',
    ];

    protected $scene = [
        'create' => ['name', 'description'],
        'update' => ['id', 'name', 'description'],
    ];

}