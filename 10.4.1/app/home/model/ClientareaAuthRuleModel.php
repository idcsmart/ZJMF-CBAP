<?php
namespace app\home\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 会员中心权限规则模型
 * @desc 会员中心权限规则模型
 * @use app\home\model\ClientareaAuthRuleModel
 */
class ClientareaAuthRuleModel extends Model
{
    protected $name = 'clientarea_auth_rule';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'title'         => 'string',
        'module'        => 'string',
        'plugin'        => 'string',
    ];

    /**
     * 时间 2022-5-25
     * @title 获取会员中心权限名称
     * @desc 获取会员中心权限名称
     * @author theworld
     * @version v1
     * @param string rule - 权限规则标识
     * @return array
     */
    public function getAuthName($rule)
    {
        $authRule = $this->where('name', $rule)->find();
        return $authRule['title'] ?? '';
    }

}