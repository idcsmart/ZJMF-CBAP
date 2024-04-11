<?php
namespace addon\idcsmart_renew\validate;

use think\Validate;

/**
 * 续费验证
 */
class IdcsmartRenewValidate extends Validate
{
    protected $rule = [
        'id'        => 'require|integer|gt:0',
        'status'    => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'status.require'    => 'param_error',
        'status.in'         => 'param_error',
    ];

    protected $scene = [
        'update_status' => ['id', 'status'],
    ];
}