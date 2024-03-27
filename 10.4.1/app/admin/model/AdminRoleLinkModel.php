<?php
namespace app\admin\model;

use think\Model;

class AdminRoleLinkModel extends Model
{
    protected $name = 'admin_role_link';

    // 设置字段信息
    protected $schema = [
        'admin_role_id'   => 'int',
        'admin_id'        => 'int',
    ];

}