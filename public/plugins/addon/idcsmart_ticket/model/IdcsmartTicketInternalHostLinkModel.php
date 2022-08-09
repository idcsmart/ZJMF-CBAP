<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketInternalHostLinkModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_internal_host_link';

    # 设置字段信息
    protected $schema = [
        'ticket_internal_id'               => 'int',
        'host_id'                          => 'int',
    ];

}
