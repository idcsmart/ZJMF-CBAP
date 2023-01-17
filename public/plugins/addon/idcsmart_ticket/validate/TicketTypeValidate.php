<?php

namespace addon\idcsmart_ticket\validate;

use app\admin\model\AdminRoleModel;
use app\admin\model\AdminModel;
use think\Validate;

/**
 * @title 工单类型验证
 * @description 接口说明:工单类型验证
 */
class TicketTypeValidate extends Validate
{
    protected $rule = [
        'id'                      => 'require',
        'name'                    => 'require|max:150|unique:addon_idcsmart_ticket_type,name',
        'admin_id'                => 'require|array|checkAdmin:thinkphp',
    ];

    protected $message = [
        'name.require'                => 'ticket_type_name_require',
        'name.max'                    => 'ticket_type_name_require_max',
        'name.unique'                 => 'ticket_type_already_exist',
        'admin_id.require'            => 'ticket_type_admin_require',
        'admin_id.array'              => 'ticket_type_admin_require',
        'admin_id.checkAdmin'         => 'ticket_type_admin_not_found',
    ];

    protected $scene = [
        'create'               => ['name','admin_id'],
        'update'               => ['id','name','admin_id'],
    ];

    protected function checkAdmin($value)
    {
        $admin = AdminModel::whereIn('id', $value)->column('id');
        return count($admin) == count($value);
    }


}