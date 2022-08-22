<?php
namespace app\common\model;

use think\Model;
use think\Db;
use app\admin\model\PluginModel;
use app\admin\model\AuthModel;

/**
 * @title 导航管理模型
 * @desc 导航管理模型
 * @use app\common\model\MenuModel
 */
class MenuModel extends Model
{
	protected $name = 'menu';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'menu_type'     => 'string',
        'name'          => 'string',
        'language'      => 'string',
        'url'           => 'string',
        'icon'          => 'string',
        'nav_id'        => 'int',
        'parent_id'     => 'int',
        'order'         => 'int',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2022-08-05
     * @title 获取后台导航
     * @desc 获取后台导航
     * @author theworld
     * @version v1
     */
    public function getAdminMenu()
    {
        $navs = NavModel::field('id,name')
            ->where('type', 'admin')
            ->where('plugin', '')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = lang($value['name']);
        }
        $plugins = PluginModel::field('name,title,module')
            ->order('order','asc')
            ->select()
            ->toArray();
        $pluginNavs = NavModel::field('id,name,module,plugin')
            ->where('type', 'admin')
            ->where('plugin', '<>', '')
            ->select()
            ->toArray();
        foreach ($pluginNavs as $key => $value) {
            foreach ($plugins as $k => $v) {
                $plugins[$k]['navs'] = $plugins[$k]['navs'] ?? [];
                if($value['plugin']==$v['name'] && $value['module']==$v['module']){
                    $plugins[$k]['navs'][] = ['id' => $value['id'], 'name' => lang_plugins($value['name'])];
                }
            }
        }
        foreach ($plugins as $key => $value) {
            if(empty($value['navs'])){
                unset($plugins[$key]);
                continue;
            }
            unset($plugins[$key]['name'], $plugins[$key]['module']);
        }
        $plugins = array_values($plugins);
        $menus = $this->field('id,menu_type type,name,language,url,icon,nav_id,parent_id')
            ->where('type', 'admin')
            ->order('order','asc')
            ->select()
            ->toArray();
        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                $menus[$key]['language'] = json_decode($data['language'], true);
                $refer[$data['id']] = &$menus[$key];
            }
            foreach ($menus as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$menus[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$menus[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        return ['menu' => $tree, 'language' => lang_list('admin'), 'system_nav' => $navs, 'plugin_nav' => $plugins];
    }

    /**
     * 时间 2022-08-05
     * @title 获取前台导航
     * @desc 获取前台导航
     * @author theworld
     * @version v1
     * @url /admin/v1/menu/home
     * @method  GET
     */
    public function getHomeMenu()
    {
        $navs = NavModel::field('id,name')
            ->where('type', 'home')
            ->where('plugin', '')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = lang($value['name']);
        }
        $plugins = PluginModel::field('name,title,module')
            ->order('order','asc')
            ->select()
            ->toArray();
        $pluginNavs = NavModel::field('id,name,module,plugin')
            ->where('type', 'home')
            ->where('plugin', '<>', '')
            ->select()
            ->toArray();
        foreach ($pluginNavs as $key => $value) {
            foreach ($plugins as $k => $v) {
                $plugins[$k]['navs'] = $plugins[$k]['navs'] ?? [];
                if($value['plugin']==$v['name'] && $value['module']==$v['module']){
                    $plugins[$k]['navs'][] = ['id' => $value['id'], 'name' => lang($value['name'])];
                }
            }
        }
        foreach ($plugins as $key => $value) {
            if(empty($value['navs'])){
                unset($plugins[$key]);
                continue;
            }
            unset($plugins[$key]['name'], $plugins[$key]['module']);
        }
        $plugins = array_values($plugins);
        $menus = $this->field('id,menu_type type,name,language,url,icon,nav_id,parent_id')
            ->where('type', 'home')
            ->order('order','asc')
            ->select()
            ->toArray();
        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                $menus[$key]['language'] = json_decode($data['language'], true);
                $refer[$data['id']] = &$menus[$key];
            }
            foreach ($menus as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$menus[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$menus[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        return ['menu' => $tree, 'language' => lang_list('home'), 'system_nav' => $navs, 'plugin_nav' => $plugins];
    }

    /**
     * 时间 2022-08-05
     * @title 保存后台导航
     * @desc 保存后台导航
     * @author theworld
     * @version v1
     */
    public function saveAdminMenu($param)
    {
        foreach ($param['menu'] as $key => $value) {
            if($value['type']!='custom'){
                $nav = NavModel::find($value['nav_id']);
                if(empty($nav)){
                    return ['status' => 400, 'msg' => lang('nav_is_not_exist')];
                }
                if($nav['type']=='home'){
                    return ['status' => 400, 'msg' => lang('param_error')];
                }
            }
            if(isset($value['child'])){
                foreach ($value['child'] as $k => $v) {
                    if($v['type']!='custom'){
                        $nav = NavModel::find($v['nav_id']);
                        if(empty($nav)){
                            return ['status' => 400, 'msg' => lang('nav_is_not_exist')];
                        }
                        if($nav['type']=='home'){
                            return ['status' => 400, 'msg' => lang('param_error')];
                        }
                    }
                }
            }
        }
        $this->startTrans();
        try {
            $this->where('type', 'admin')->delete();
            $list = [];
            $order = 0;
            foreach ($param['menu'] as $key => $value) {
                $menu = $this->create([
                    'type'          => 'admin',
                    'menu_type'     => $value['type'],
                    'name'          => $value['name'] ?? '',
                    'language'      => isset($value['language']) ? json_encode($value['language']) : '',
                    'url'           => $value['url'] ?? '',
                    'icon'          => $value['icon'] ?? '',
                    'nav_id'        => $value['nav_id'] ?? 0,
                    'parent_id'     => 0,
                    'order'         => $order,
                    'create_time'   => time(),
                ]);
                $order++;
                foreach ($value['child'] as $k => $v) {
                    $list[] = [
                        'type'          => 'admin',
                        'menu_type'     => $v['type'],
                        'name'          => $v['name'] ?? '',
                        'language'      => isset($v['language']) ? json_encode($v['language']) : '',
                        'url'           => $v['url'] ?? '',
                        'icon'          => $v['icon'] ?? '',
                        'nav_id'        => $v['nav_id'] ?? 0,
                        'parent_id'     => $menu->id,
                        'order'         => $order,
                        'create_time'   => time(),
                    ];
                    $order++;
                }
            }

            $this->insertAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-08-05
     * @title 保存前台导航
     * @desc 保存前台导航
     * @author theworld
     * @version v1
     */
    public function saveHomeMenu($param)
    {
        foreach ($param['menu'] as $key => $value) {
            if($value['type']!='custom'){
                $nav = NavModel::find($value['nav_id']);
                if(empty($nav)){
                    return ['status' => 400, 'msg' => lang('nav_is_not_exist')];
                }
                if($nav['type']=='admin'){
                    return ['status' => 400, 'msg' => lang('param_error')];
                }
            }
            if(isset($value['child'])){
                foreach ($value['child'] as $k => $v) {
                    if($v['type']!='custom'){
                        $nav = NavModel::find($v['nav_id']);
                        if(empty($nav)){
                            return ['status' => 400, 'msg' => lang('nav_is_not_exist')];
                        }
                        if($nav['type']=='admin'){
                            return ['status' => 400, 'msg' => lang('param_error')];
                        }
                    }
                }
            }
        }
        $this->startTrans();
        try {
            $this->where('type', 'home')->delete();
            $list = [];
            $order = 0;
            foreach ($param['menu'] as $key => $value) {
                $menu = $this->create([
                    'type'          => 'home',
                    'menu_type'     => $value['type'],
                    'name'          => $value['name'] ?? '',
                    'language'      => isset($value['language']) ? json_encode($value['language']) : '',
                    'url'           => $value['url'] ?? '',
                    'icon'          => $value['icon'] ?? '',
                    'nav_id'        => $value['nav_id'] ?? 0,
                    'parent_id'     => 0,
                    'order'         => $order,
                    'create_time'   => time(),
                ]);
                $order++;
                foreach ($value['child'] as $k => $v) {
                    $list[] = [
                        'type'          => 'home',
                        'menu_type'     => $v['type'],
                        'name'          => $v['name'] ?? '',
                        'language'      => isset($v['language']) ? json_encode($v['language']) : '',
                        'url'           => $v['url'] ?? '',
                        'icon'          => $v['icon'] ?? '',
                        'nav_id'        => $v['nav_id'] ?? 0,
                        'parent_id'     => $menu->id,
                        'order'         => $order,
                        'create_time'   => time(),
                    ];
                    $order++;
                }
            }

            $this->insertAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    public function adminMenu(){
        $navs = NavModel::field('id,name,url,icon,parent_id,module')
            ->where('type', 'admin')
            ->order('order','asc')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = !empty($value['module']) ? lang_plugins($value['name']) : lang($value['name']);
            unset($navs[$key]['module']);
        }

        $urls = AuthModel::where('url', '<>', '')
            ->column('url');

        $adminId = get_admin_id();
        $auths = AuthModel::alias('au')
            ->leftjoin('auth_link al', 'al.auth_id=au.id')
            ->leftjoin('admin_role adr', 'adr.id=al.admin_role_id')
            ->leftjoin('admin_role_link adrl', 'adrl.admin_role_id=adr.id')
            ->where('adrl.admin_id', $adminId)
            ->where('au.url', '<>', '')
            ->column('au.url');

        $language = get_system_lang(true);  
    
        $menus = $this->alias('m')
            ->field('m.id,m.name,m.language,m.url,m.icon,m.parent_id,n.url nav_url')
            ->leftjoin('nav n', 'n.id=m.nav_id')
            ->where('m.type', 'admin')
            ->order('m.order','asc')
            ->select()
            ->toArray();
        foreach ($menus as $key => $data) {
            $data['language'] = json_decode($data['language'], true);
            $menus[$key]['name'] = $data['language'][$language] ?? $data['name'];
            $menus[$key]['url'] = $data['nav_url'] ?? $data['url'];
            unset($menus[$key]['language'], $menus[$key]['nav_url']);
        }
        if(empty($menus)){
            $menus = $navs;
        }
        foreach ($menus as $key => $value) {
            if(!empty($value['url']) && !in_array($value['url'], $auths) && in_array($value['url'], $urls)){
                unset($menus[$key]);
            }
        }
        $menus = array_values($menus);
        
        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                $refer[$data['id']] = &$menus[$key];
            }
            foreach ($menus as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$menus[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$menus[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        foreach ($tree as $key => $value) {
            if((!isset($value['child']) || empty($value['child'])) && empty($value['url'])){
                unset($tree[$key]);
            }
        }
        $tree = array_values($tree);
        return ['menu' => $tree];
    }

    public function homeMenu(){
        $navs = NavModel::field('id,name,url,icon,parent_id,module')
            ->where('type', 'home')
            ->order('order','asc')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = !empty($value['module']) ? lang_plugins($value['name']) : lang($value['name']);
            unset($navs[$key]['module']);
        }

        $language = get_system_lang(false); 

        $menus = $this->alias('m')
            ->field('m.id,m.name,m.language,m.url,m.icon,m.parent_id,n.url nav_url')
            ->leftjoin('nav n', 'n.id=m.nav_id')
            ->where('m.type', 'home')
            ->order('m.order','asc')
            ->select()
            ->toArray();
        foreach ($menus as $key => $data) {
            $data['language'] = json_decode($data['language'], true);
            $menus[$key]['name'] = $data['language'][$language] ?? $data['name'];
            $menus[$key]['url'] = $data['nav_url'] ?? $data['url'];
            unset($menus[$key]['language'], $menus[$key]['nav_url']);
        }
        if(empty($menus)){
            $menus = $navs;
        }

        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                $refer[$data['id']] = &$menus[$key];
            }
            foreach ($menus as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$menus[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$menus[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        return ['menu' => $tree];
    }

}
