<?php
namespace app\admin\validate;

use think\Validate;

/**
 * API验证
 */
class ApiValidate extends Validate
{
	protected $rule = [
		'client_create_api' 	    => 'in:0,1',
        'client_create_api_type'    => 'in:0,1,2',
    ];

    protected $message  =   [
    	'client_create_api.in'         => 'param_error',
    	'client_create_api_type.in'    => 'param_error',
    ];

    protected $scene = [
        'config' => ['client_create_api', 'client_create_api_type'],
    ];
}