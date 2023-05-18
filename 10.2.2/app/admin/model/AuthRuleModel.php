<?php
namespace app\admin\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 管理员权限规则模型
 * @desc 管理员权限规则模型
 * @use app\admin\model\AuthRuleModel
 */
class AuthRuleModel extends Model
{
    protected $name = 'auth_rule';

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
     * @title 获取管理员权限
     * @desc 获取管理员权限
     * @author theworld
     * @version v1
     * @param int adminId - 管理员ID
     * @return array
     */
    public function getAdminAuthRule($adminId)
    {
        $rules = $this->alias('ar')
            ->leftjoin('auth_rule_link arl', 'arl.auth_rule_id=ar.id')
            ->leftjoin('auth au', 'au.id=arl.auth_id')
            ->leftjoin('auth_link al', 'al.auth_id=au.id')
            ->leftjoin('admin_role adr', 'adr.id=al.admin_role_id')
            ->leftjoin('admin_role_link adrl', 'adrl.admin_role_id=adr.id')
            ->where('adrl.admin_id', $adminId)
            ->column('ar.name');
        return $rules;
    }

    /**
     * 时间 2022-5-25
     * @title 获取管理员权限名称
     * @desc 获取管理员权限名称
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