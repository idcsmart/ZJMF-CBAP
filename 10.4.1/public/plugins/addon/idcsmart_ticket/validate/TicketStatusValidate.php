<?php

namespace addon\idcsmart_ticket\validate;

use think\Validate;

/**
 * @title 工单状态验证
 * @description 接口说明:工单状态验证
 */
class TicketStatusValidate extends Validate
{
    protected $rule = [
        'id'                       => 'require',
        'name'                     => 'require|max:255',
        'color'                    => 'require|max:255',
        'status'                   => 'require|in:0,1',
    ];

    protected $message = [
        'name.require'            => 'ticket_status_name_require',
        'name.max'                => 'ticket_status_name_max',
        'color.require'           => 'ticket_status_color_require',
        'color.max'               => 'ticket_status_color_max',
        'status.require'          => 'ticket_status_status_require',
        'status.in'               => 'ticket_status_status_in',
    ];

    protected $scene = [
        'create'                  => ['name','color','status'],
        'update'                  => ['id','name','color','status'],
    ];

}