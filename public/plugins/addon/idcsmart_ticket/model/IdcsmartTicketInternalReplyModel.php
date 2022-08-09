<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketInternalReplyModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_internal_reply';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_internal_id'               => 'int',
        'admin_id'                         => 'int',
        'content'                          => 'string',
        'attachment'                       => 'string',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

}
