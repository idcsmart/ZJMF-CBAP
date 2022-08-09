<?php
namespace app\admin\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 管理员权限模型
 * @desc 管理员权限模型
 * @use app\admin\model\AuthModel
 */
class AuthModel extends Model
{
    protected $name = 'auth';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'title'         => 'string',
        'url'           => 'string',
        'order'         => 'int',
        'parent_id'     => 'int',
    ];

    /**
     * 时间 2022-5-27
     * @title 权限列表
     * @desc 权限列表
     * @author theworld
     * @version v1
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return array list[].rules - 权限规则标题
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return string list[].child[].rules - 权限规则标题
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return string list[].child[].child[].rules - 权限规则标题
     */
    public function authList()
    {
        $rules = AuthRuleModel::alias('ar')
            ->field('ar.title,ar.name,arl.auth_id')
            ->leftjoin('auth_rule_link arl', 'arl.auth_rule_id=ar.id')
            ->select()
            ->toArray();

        $auths = $this->select()->toArray();
        $ruleList = [];
        foreach ($rules as $key => $value) {
            $ruleList[$value['auth_id']][] = lang($value['title']);
        }

        // 将数组转换成树形结构
        $tree = [];
        if (is_array($auths)) {
            $refer = [];
            foreach ($auths as $key => $data) {
                $auths[$key]['title'] = lang($data['title']);
                $auths[$key]['rules'] = $ruleList[$data['id']] ?? [];
                $refer[$data['id']] = &$auths[$key];
            }
            foreach ($auths as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$auths[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$auths[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        return ['list' => $tree];
    }

    /**
     * 时间 2022-5-27
     * @title 当前管理员权限列表
     * @desc 当前管理员权限列表
     * @author theworld
     * @version v1
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     */
    public function adminAuthList()
    {
        $rules = AuthRuleModel::alias('ar')
            ->field('ar.title,ar.name,arl.auth_id')
            ->leftjoin('auth_rule_link arl', 'arl.auth_rule_id=ar.id')
            ->select()
            ->toArray();

        $auths = $this->select()->toArray();
        $ruleList = [];
        foreach ($rules as $key => $value) {
            $ruleList[$value['auth_id']][] = $value['name'];
        }

        $adminId = get_admin_id();
        if($adminId==1){
            $auths = $this->select()->toArray();
        }else{
            $auths = $this->alias('au')
                ->field('au.id,au.title,au.url,au.order,au.parent_id')
                ->leftjoin('auth_link al', 'al.auth_id=au.id')
                ->leftjoin('admin_role adr', 'adr.id=al.admin_role_id')
                ->leftjoin('admin_role_link adrl', 'adrl.admin_role_id=adr.id')
                ->where('adrl.admin_id', $adminId)
                ->order('au.order', 'asc')
                ->group('au.id')
                ->select()->toArray();
        }
        
        // 将数组转换成树形结构
        $tree = [];
        if (is_array($auths)) {
            $refer = [];
            foreach ($auths as $key => $data) {
                $auths[$key]['title'] = lang($data['title']);
                $auths[$key]['rules'] = $ruleList[$data['id']] ?? [];
                $refer[$data['id']] = &$auths[$key];
            }
            foreach ($auths as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$auths[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$auths[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        return ['list' => $tree];
    }
}