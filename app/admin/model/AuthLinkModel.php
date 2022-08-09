<?php
namespace app\admin\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 管理员权限对应模型
 * @desc 管理员权限对应模型
 * @use app\admin\model\AuthLinkModel
 */
class AuthLinkModel extends Model
{
    protected $name = 'auth_link';

    // 设置字段信息
    protected $schema = [
        'auth_id'      		=> 'int',
        'admin_role_id'     => 'int',
    ];
}