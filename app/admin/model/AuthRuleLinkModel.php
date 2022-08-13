<?php
namespace app\admin\model;

use think\Model;

/**
 * @title 管理员权限规则关联模型
 * @desc 管理员权限规则关联模型
 * @use app\admin\model\AuthRuleLinkModel
 */
class AuthRuleLinkModel extends Model
{
    protected $name = 'auth_rule_link';

    // 设置字段信息
    protected $schema = [
        'auth_rule_id'     => 'int',
        'auth_id'          => 'int',
    ];
}