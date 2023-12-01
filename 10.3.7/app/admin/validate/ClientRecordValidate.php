<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户信息记录验证
 */
class ClientRecordValidate extends Validate
{
	protected $rule = [
		'id'            => 'require|integer|gt:0',
        'content'       => 'requireWithout:attachment|max:300',
        'attachment'    => 'requireWithout:content|array',
    ];

    protected $message  =   [
    	'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'content.requireWithout'    => 'client_record_content_cannot_empty',
        'content.max'               => 'client_record_content_cannot_exceed_300_chars',
        'attachment.requireWithout' => 'client_record_attachment_cannot_empty',
        'attachment.array'          => 'param_error',
    ];

    protected $scene = [
        'create' => ['id', 'content', 'attachment'],
        'update' => ['id', 'content', 'attachment'],
    ];
}