<?php
namespace app\home\validate;

use think\Validate;

/**
 * 产品管理验证
 */
class HostValidate extends Validate
{
	protected $rule = [
		'id' 						=> 'require|integer|gt:0',
        'notes' 					=> 'max:1000',
    ];

    protected $message  =   [
    	'id.require'     				=> 'id_error',
    	'id.integer'     				=> 'id_error',
        'id.gt'                         => 'id_error',
    	'name.max'     					=> 'host_notes_cannot_exceed_1000_chars',
    ];

    protected $scene = [
        'update_notes'  => ['id', 'notes'],
    ];
}