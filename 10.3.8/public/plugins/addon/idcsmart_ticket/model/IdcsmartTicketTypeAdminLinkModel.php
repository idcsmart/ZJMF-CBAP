<?php
namespace addon\idcsmart_ticket\model;

use app\admin\model\AdminRoleModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketTypeAdminLinkModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_type_admin_link';

    # 设置字段信息
    protected $schema = [
        'ticket_type_id'     => 'int',
        'admin_id'           => 'int',
    ];

}