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
        'module'        => 'string',
        'plugin'        => 'string',
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
            ->field('ar.title,ar.name,arl.auth_id,ar.module,ar.plugin')
            ->leftjoin('auth_rule_link arl', 'arl.auth_rule_id=ar.id')
            ->select()
            ->toArray();

        $auths = $this->select()->toArray();
        $ruleList = [];
        foreach ($rules as $key => $value) {
            if ($value['module'] && $value['plugin']){
                $ruleList[$value['auth_id']][] = lang_plugins($value['title']);
            }else{
                $ruleList[$value['auth_id']][] = lang($value['title']);
            }
        }

        // 将数组转换成树形结构
        $tree = [];
        if (is_array($auths)) {
            $refer = [];
            foreach ($auths as $key => $data) {
                if ($data['module'] && $data['plugin']){
                    $auths[$key]['title'] = lang_plugins($data['title']);
                }else{
                    $auths[$key]['title'] = lang($data['title']);
                }

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
     * @return array rules - 权限规则
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
                ->field('au.id,au.title,au.url,au.order,au.parent_id,au.module')
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
        $rules = [];
        if (is_array($auths)) {
            $refer = [];
            foreach ($auths as $key => $data) {
                $auths[$key]['title'] = !empty($data['module']) ? lang_plugins($data['title']) : lang($data['title']);
                $auths[$key]['rules'] = $ruleList[$data['id']] ?? [];
                $rules = array_merge($rules, $auths[$key]['rules']);
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
        return ['list' => $tree, 'rule' => $rules];
    }

    public function createPluginAuth($auth,$module,$name)
    {
        $maxOrder = $this->max('order');

        $object = $this->create([
            'title' => $auth['title']??'',
            'url'  => (isset($auth['url']) && !empty($auth['url']) && is_string($auth['url']))?"plugin/{$name}/".$auth['url'].'.html':'',
            'parent_id' => isset($auth['parent_id'])?intval($auth['parent_id']):0,
            'order'  => $maxOrder+1,
            'module' => $module,
            'plugin' => parse_name($name,1)
        ]);

        $AuthRuleModel = new AuthRuleModel();
        $AuthRuleLinkModel = new AuthRuleLinkModel();

        # 插入auth_rule
        if (isset($auth['auth_rule']) && !empty($auth['auth_rule']) && is_string($auth['auth_rule'])){

            $authRule = $AuthRuleModel->create([
                'name' => "{$module}\\{$name}\\controller\\".$auth['auth_rule'],
                'title' => $auth['auth_rule_title']??'',
                'module' => $module,
                'plugin' => parse_name($name,1)
            ]);

            $AuthRuleLinkModel->create([
                'auth_rule_id' => $authRule->id,
                'auth_id' => $object->id,
            ]);
        }else if (isset($auth['auth_rule']) && !empty($auth['auth_rule']) && is_array($auth['auth_rule'])){
            foreach ($auth['auth_rule'] as $key => $value) {
                if(isset($auth['auth_rule_title'][$key])){
                    $authRule = $AuthRuleModel->create([
                        'name' => "{$module}\\{$name}\\controller\\".$value,
                        'title' => $auth['auth_rule_title'][$key],
                        'module' => $module,
                        'plugin' => parse_name($name,1)
                    ]);

                    $AuthRuleLinkModel->create([
                        'auth_rule_id' => $authRule->id,
                        'auth_id' => $object->id,
                    ]);
                }
            }
        }

        $child = $auth['child']??[];
        foreach ($child as $item){
            $item['parent_id'] = $object->id;
            $this->createPluginAuth($item,$module,$name);
        }

        return ['status' => 200, 'msg' => lang('create_success')];
    }

    # 删除插件关联的所有权限数据
    public function deletePluginAuth($module,$name)
    {
        $authIds = $this->where('module',$module)
            ->where('plugin',$name)
            ->column('id');

        $AuthRuleModel = new AuthRuleModel();
        $AuthRuleModel->where('module',$module)
            ->where('plugin',$name)
            ->delete();

        $AuthRuleLinkModel = new AuthRuleLinkModel();
        $AuthRuleLinkModel->whereIn('auth_id',$authIds)->delete();

        $this->where('module',$module)->where('plugin',$name)->delete();

        $AuthLinkModel = new AuthLinkModel();
        $AuthLinkModel->whereIn('auth_id',$authIds)->delete();

        return ['status' => 200, 'msg' => lang('create_success')];
    }
}