<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 交易流水管理验证
 */
class TransactionValidate extends Validate
{
	protected $rule = [
        'id'                            => 'require|integer|gt:0',
		'amount' 						=> 'require|float',
		'gateway' 						=> 'require',
        'transaction_number' 			=> 'alphaNum',
        'client_id'     				=> 'require|integer|gt:0',
    ];

    protected $message  =   [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'id.gt'                         => 'id_error',
    	'amount.require'    			=> 'please_enter_amount',
        'amount.float'    				=> 'amount_formatted_incorrectly',
        'gateway.require'        		=> 'please_select_gateway',
        'transaction_number.alphaNum'   => 'transaction_number_formatted_incorrectly', 
        'client_id.require'     		=> 'please_select_client',
    	'client_id.integer'     		=> 'client_id_error',
        'client_id.gt'                  => 'client_id_error',
    ];

    protected $scene = [
        'create' => ['amount', 'gateway', 'transaction_number', 'client_id'],
        'update' => ['id', 'amount', 'gateway', 'transaction_number', 'client_id']
    ];
}