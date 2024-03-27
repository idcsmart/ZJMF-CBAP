<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户余额管理验证
 */
class ClientCreditValidate extends Validate
{
	protected $rule = [
		'id' 			=> 'require|integer|gt:0',
        'type' 		    => 'require|in:recharge,deduction',
        'amount' 		=> 'require|float|gt:0',
        'notes' 	    => 'max:1000',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
    	'id.integer'     			=> 'id_error',
        'id.gt'                     => 'id_error',
        'type.require' 			    => 'param_error',
        'type.in'        		    => 'param_error',    
        'amount.require'            => 'please_enter_amount', 
        'amount.float'        		=> 'amount_formatted_incorrectly', 
        'amount.gt'                 => 'amount_formatted_incorrectly', 
        'notes.max'                 => 'notes_cannot_exceed_1000_chars',
    ];

    protected $scene = [
        'update' => ['id', 'type', 'amount', 'notes'],
    ];
}