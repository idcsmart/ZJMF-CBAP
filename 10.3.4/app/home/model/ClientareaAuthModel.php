<?php
namespace app\home\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 会员中心权限模型
 * @desc 会员中心权限模型
 * @use app\home\model\ClientareaAuthModel
 */
class ClientareaAuthModel extends Model
{
    protected $name = 'clientarea_auth';

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
        $rules = ClientareaAuthRuleModel::alias('ar')
            ->field('ar.title,ar.name,arl.clientarea_auth_id,ar.module,ar.plugin')
            ->leftjoin('clientarea_auth_rule_link arl', 'arl.clientarea_auth_rule_id=ar.id')
            ->select()
            ->toArray();

        $auths = $this->select()->toArray();
        $ruleList = [];
        foreach ($rules as $key => $value) {
            if ($value['module'] && $value['plugin']){
                $ruleList[$value['clientarea_auth_id']][] = lang_plugins($value['title']);
            }else{
                $ruleList[$value['clientarea_auth_id']][] = lang($value['title']);
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

        $ClientareaAuthRuleModel = new ClientareaAuthRuleModel();
        $ClientareaAuthRuleLinkModel = new ClientareaAuthRuleLinkModel();

        # 插入auth_rule
        if (isset($auth['auth_rule']) && !empty($auth['auth_rule']) && is_string($auth['auth_rule'])){
            $authRule = $ClientareaAuthRuleModel->create([
                'name' => "{$module}\\{$name}\\controller\\clientarea\\".$auth['auth_rule'],
                'title' => $auth['auth_rule_title']??'',
                'module' => $module,
                'plugin' => parse_name($name,1)
            ]);

            $ClientareaAuthRuleLinkModel->create([
                'clientarea_auth_rule_id' => $authRule->id,
                'clientarea_auth_id' => $object->id,
            ]);
        }else if (isset($auth['auth_rule']) && !empty($auth['auth_rule']) && is_array($auth['auth_rule'])){
            foreach ($auth['auth_rule'] as $key => $value) {
                if(isset($auth['auth_rule_title'][$key])){
                    $authRule = $ClientareaAuthRuleModel->create([
                        'name' => "{$module}\\{$name}\\controller\\clientarea\\".$value,
                        'title' => $auth['auth_rule_title'][$key],
                        'module' => $module,
                        'plugin' => parse_name($name,1)
                    ]);

                    $ClientareaAuthRuleLinkModel->create([
                        'clientarea_auth_rule_id' => $authRule->id,
                        'clientarea_auth_id' => $object->id,
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

        $ClientareaAuthRuleModel = new ClientareaAuthRuleModel();
        $ClientareaAuthRuleModel->where('module',$module)
            ->where('plugin',$name)
            ->delete();

        $this->where('module',$module)->where('plugin',$name)->delete();

        $ClientareaAuthRuleLinkModel = new ClientareaAuthRuleLinkModel();
        $ClientareaAuthRuleLinkModel->whereIn('clientarea_auth_id',$authIds)->delete();

        return ['status' => 200, 'msg' => lang('create_success')];
    }
}