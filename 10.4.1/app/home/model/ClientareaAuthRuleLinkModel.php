<?php
namespace app\home\model;

use think\Model;

/**
 * @title 会员中心权限规则关联模型
 * @desc 会员中心权限规则关联模型
 * @use app\home\model\ClientareaAuthRuleLinkModel
 */
class ClientareaAuthRuleLinkModel extends Model
{
    protected $name = 'clientarea_auth_rule_link';

    // 设置字段信息
    protected $schema = [
        'clientarea_auth_rule_id'     => 'int',
        'clientarea_auth_id'          => 'int',
    ];
}