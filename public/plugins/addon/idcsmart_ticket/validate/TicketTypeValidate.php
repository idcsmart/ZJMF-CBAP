<?php

namespace addon\idcsmart_ticket\validate;

use app\admin\model\AdminRoleModel;
use think\Validate;

/**
 * @title 工单类型验证
 * @description 接口说明:工单类型验证
 */
class TicketTypeValidate extends Validate
{
    protected $rule = [
        'id'                      => 'require',
        'name'                    => 'require|max:150',
        'admin_role_id'           => 'require|checkAdminRole:thinkphp',
    ];

    protected $message = [
        'name.require'                => 'ticket_type_name_require',
        'name.max'                    => 'ticket_type_name_require_max',
        'admin_role_id.require'       => 'ticket_type_admin_role_id_require',
    ];

    protected $scene = [
        'create'               => ['name','admin_role_id'],
        'update'               => ['id','name','admin_role_id'],
    ];

    protected function checkAdminRole($value)
    {
        $AdminRoleModel = new AdminRoleModel();
        $exist = $AdminRoleModel->find($value);
        if (!empty($exist)){
            return true;
        }

        return lang_plugins('admin_role_is_not_exist');
    }

}