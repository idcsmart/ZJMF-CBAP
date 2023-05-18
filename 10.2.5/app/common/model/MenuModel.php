<?php
namespace app\common\model;

use think\Model;
use think\Db;
use app\admin\model\PluginModel;
use app\admin\model\AuthModel;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;

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
        'module'        => 'string',
        'product_id'    => 'string',
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
        $navs = NavModel::field('id,name,url')
            ->where('type', 'admin')
            ->where('plugin', '')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = lang($value['name']);
        }
        $plugins = PluginModel::field('name,title,module')
            ->where('status', 1)
            ->order('order','asc')
            ->select()
            ->toArray();

        $where = [];
        $where[] = ['type', '=', 'admin'];
        $where[] = ['plugin', '<>', ''];

        $pluginNavs = NavModel::field('id,name,url,module,plugin')
            ->where($where)
            ->select()
            ->toArray();
        foreach ($pluginNavs as $key => $value) {
            foreach ($plugins as $k => $v) {
                $plugins[$k]['navs'] = $plugins[$k]['navs'] ?? [];
                if($value['plugin']==$v['name'] && $value['module']==$v['module']){
                    $plugins[$k]['navs'][] = ['id' => $value['id'], 'name' => lang_plugins($value['name']), 'url' => $value['url']];
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

        $where = [];
        $where[] = ['type', '=', 'admin'];

        $menus = $this->field('id,menu_type type,name,language,url,icon,nav_id,parent_id')
            ->where($where)
            ->order('order','asc')
            ->select()
            ->toArray();
        if(empty($menus)){
            $menus = NavModel::field('id,name,url,icon,id nav_id,parent_id,module')
                ->where($where)
                ->order('order','asc')
                ->select()
                ->toArray();
            foreach ($menus as $key => $value) {
                $menus[$key]['type'] = !empty($value['module']) ? 'plugin' : 'system';
                $menus[$key]['language'] = '{}';
                $menus[$key]['name'] = !empty($value['module']) ? lang_plugins($value['name']) : lang($value['name']);
                unset($menus[$key]['module']);
            }
        }

        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                $menus[$key]['language'] = json_decode($data['language'], true);
                $menus[$key]['language'] = !empty($menus[$key]['language']) ? $menus[$key]['language'] : (object)[];
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
        $this->createHomeMenu();

        $navs = NavModel::field('id,name,url')
            ->where('type', 'home')
            ->where('plugin', '')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = lang($value['name']);
        }
        $plugins = PluginModel::field('name,title,module')
            ->where('status', 1)
            ->order('order','asc')
            ->select()
            ->toArray();
        $pluginNavs = NavModel::field('id,name,url,module,plugin')
            ->where('type', 'home')
            ->where('plugin', '<>', '')
            ->select()
            ->toArray();
        foreach ($pluginNavs as $key => $value) {
            foreach ($plugins as $k => $v) {
                $plugins[$k]['navs'] = $plugins[$k]['navs'] ?? [];
                if($value['plugin']==$v['name'] && $value['module']==$v['module']){
                    $plugins[$k]['navs'][] = ['id' => $value['id'], 'name' => lang_plugins($value['name']), 'url' => $value['url']];
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

        $ModuleLogic = new ModuleLogic();

        $module = $ModuleLogic->getModuleList();

        $ResModuleLogic = new ResModuleLogic();

        $resModule = $ResModuleLogic->getModuleList();

        $menus = $this->field('id,menu_type type,name,language,url,icon,nav_id,parent_id,module,product_id')
            ->where('type', 'home')
            ->order('order','asc')
            ->select()
            ->toArray();
        if(empty($menus)){
            $menus = NavModel::field('id,name,url,icon,id nav_id,parent_id,module')
                ->where('type', 'home')
                ->order('order','asc')
                ->select()
                ->toArray();
            foreach ($menus as $key => $value) {
                $menus[$key]['type'] = !empty($value['module']) ? 'plugin' : 'system';
                $menus[$key]['language'] = '{}';
                $menus[$key]['name'] = !empty($value['module']) ? lang_plugins($value['name']) : lang($value['name']);
                $menus[$key]['module'] = '';
                $menus[$key]['product_id'] = '[]';
            }
        }
        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                $menus[$key]['language'] = json_decode($data['language'], true);
                $menus[$key]['language'] = !empty($menus[$key]['language']) ? $menus[$key]['language'] : (object)[];
                $menus[$key]['product_id'] = json_decode($data['product_id'], true);
                $menus[$key]['product_id'] = !empty($menus[$key]['product_id']) ? $menus[$key]['product_id'] : (object)[];
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
        return ['menu' => $tree, 'language' => lang_list('home'), 'system_nav' => $navs, 'plugin_nav' => $plugins, 'module' => $module, 'res_module' => $resModule];
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
                    'product_id'    => '',
                    'order'         => $order,
                    'create_time'   => time(),
                ]);
                $order++;
                if(isset($value['child'])){
                    $order = $this->saveChild('admin', $menu->id, $value['child'], $order);
                }
                /*foreach ($value['child'] as $k => $v) {
                    $list[] = [
                        'type'          => 'admin',
                        'menu_type'     => $v['type'],
                        'name'          => $v['name'] ?? '',
                        'language'      => isset($v['language']) ? json_encode($v['language']) : '',
                        'url'           => $v['url'] ?? '',
                        'icon'          => $v['icon'] ?? '',
                        'nav_id'        => $v['nav_id'] ?? 0,
                        'parent_id'     => $menu->id,
                        'product_id'    => '',
                        'order'         => $order,
                        'create_time'   => time(),
                    ];
                    $order++;
                }*/
            }

            //$this->insertAll($list);

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
        $ModuleLogic = new ModuleLogic();
        $module = $ModuleLogic->getModuleList();
        $module = array_column($module, 'name');

        $ResModuleLogic = new ResModuleLogic();
        $resModule = $ResModuleLogic->getModuleList();
        $resModule = array_column($resModule, 'name');

        foreach ($param['menu'] as $key => $value) {
            if(in_array($value['type'], ['system', 'plugin'])){
                $nav = NavModel::find($value['nav_id']);
                if(empty($nav)){
                    return ['status' => 400, 'msg' => lang('nav_is_not_exist')];
                }
                if($nav['type']=='admin'){
                    return ['status' => 400, 'msg' => lang('param_error')];
                }
            }else if($value['type']=='module'){
                if(!in_array($value['module'], $module)){
                    return ['status' => 400, 'msg' => lang('module_error')];
                }
                $products = ProductModel::alias('p')
                    ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
                    ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
                    ->leftjoin('server ss','ss.server_group_id=sg.id')
                    ->where('p.hidden', 0)
                    ->where('s.module|ss.module', $value['module'])
                    ->column('p.id');
                if(count(array_diff($value['product_id'], $products))>0){
                    return ['status' => 400, 'msg' => lang('product_error')];
                }
            }else if($value['type']=='res_module'){
                if(!in_array($value['module'], $resModule)){
                    return ['status' => 400, 'msg' => lang('module_error')];
                }
                $products = ProductModel::alias('p')
                    ->leftjoin('upstream_product up','up.product_id=p.id')
                    ->where('p.hidden', 0)
                    ->where('up.res_module', $value['module'])
                    ->column('p.id');
                if(count(array_diff($value['product_id'], $products))>0){
                    return ['status' => 400, 'msg' => lang('product_error')];
                }
            }
            if(isset($value['child'])){
                foreach ($value['child'] as $k => $v) {
                    if(in_array($v['type'], ['system', 'plugin'])){
                        $nav = NavModel::find($v['nav_id']);
                        if(empty($nav)){
                            return ['status' => 400, 'msg' => lang('nav_is_not_exist')];
                        }
                        if($nav['type']=='admin'){
                            return ['status' => 400, 'msg' => lang('param_error')];
                        }
                    }else if($v['type']=='module'){
                        if(!in_array($v['module'], $module)){
                            return ['status' => 400, 'msg' => lang('module_error')];
                        }
                        $products = ProductModel::alias('p')
                            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
                            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
                            ->leftjoin('server ss','ss.server_group_id=sg.id')
                            ->where('p.hidden', 0)
                            ->whereIn('s.module|ss.module', $v['module'])
                            ->column('p.id');
                        if(count(array_diff($v['product_id'], $products))>0){
                            return ['status' => 400, 'msg' => lang('product_error')];
                        }
                    }else if($v['type']=='res_module'){
                        if(!in_array($v['module'], $resModule)){
                            return ['status' => 400, 'msg' => lang('module_error')];
                        }
                        $products = ProductModel::alias('p')
                            ->leftjoin('upstream_product up','up.product_id=p.id')
                            ->where('p.hidden', 0)
                            ->where('up.res_module', $v['module'])
                            ->column('p.id');
                        if(count(array_diff($v['product_id'], $products))>0){
                            return ['status' => 400, 'msg' => lang('product_error')];
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
                    'module'        => $value['module'] ?? '',
                    'product_id'    => isset($value['product_id']) ? json_encode($value['product_id']) : '',
                    'order'         => $order,
                    'create_time'   => time(),
                ]);
                $order++;
                if(isset($value['child'])){
                    $order = $this->saveChild('home', $menu->id, $value['child'], $order);
                }
                /*foreach ($value['child'] as $k => $v) {
                    $list[] = [
                        'type'          => 'home',
                        'menu_type'     => $v['type'],
                        'name'          => $v['name'] ?? '',
                        'language'      => isset($v['language']) ? json_encode($v['language']) : '',
                        'url'           => $v['url'] ?? '',
                        'icon'          => $v['icon'] ?? '',
                        'nav_id'        => $v['nav_id'] ?? 0,
                        'parent_id'     => $menu->id,
                        'module'        => $v['module'] ?? '',
                        'product_id'    => isset($v['product_id']) ? json_encode($v['product_id']) : '',
                        'order'         => $order,
                        'create_time'   => time(),
                    ];
                    $order++;
                }*/
            }

            //$this->insertAll($list);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    public function saveChild($type = '', $parentId = 0, $child = [], $order = 0)
    {
        foreach ($child as $k => $v) {
            $menu = $this->create([
                'type'          => $type,
                'menu_type'     => $v['type'],
                'name'          => $v['name'] ?? '',
                'language'      => isset($v['language']) ? json_encode($v['language']) : '',
                'url'           => $v['url'] ?? '',
                'icon'          => $v['icon'] ?? '',
                'nav_id'        => $v['nav_id'] ?? 0,
                'parent_id'     => $parentId,
                'module'        => $v['module'] ?? '',
                'product_id'    => isset($v['product_id']) ? json_encode($v['product_id']) : '',
                'order'         => $order,
                'create_time'   => time(),
            ]);
            $order++;
            if(isset($v['child'])){
                $order = $this->saveChild($type, $menu->id, $v['child'], $order);
            }
        }
        return $order;
    }

    public function adminMenu(){

        $where = [];
        $where[] = ['type', '=', 'admin'];

        $hookRes = hook('hide_admin_memu_nav');
        $hideId = [];
        foreach($hookRes as $v){
            if(!empty($v)){
                $hideId = array_merge($hideId, $v);
            }
        }
        if(!empty($hideId)){
            $where[] = ['id', 'NOT IN', $hideId];
        }

        $navs = NavModel::field('id,name,url,icon,parent_id,plugin')
            ->where($where)
            ->order('order','asc')
            ->select()
            ->toArray();
        foreach ($navs as $key => $value) {
            $navs[$key]['name'] = !empty($value['plugin']) ? lang_plugins($value['name']) : lang($value['name']);
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
        
        $where = [];
        $where[] = ['m.type', '=', 'admin'];
        if(!empty($hideId)){
            $where[] = ['m.nav_id', 'NOT IN', $hideId];
        }

        $menus = $this->alias('m')
            ->field('m.id,m.name,m.language,m.url,m.icon,m.parent_id,n.url nav_url,n.plugin')
            ->leftjoin('nav n', 'n.id=m.nav_id')
            ->where($where)
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

        $plugins = PluginModel::where('status', 1)->column('name');
        foreach ($menus as $key => $value) {
            if(!empty($value['url']) && !in_array($value['url'], $auths) && in_array($value['url'], $urls)){
                unset($menus[$key]);
                continue;
            }
            if(!empty($value['plugin'])){
                if(!in_array($value['plugin'], $plugins)){
                    unset($menus[$key]);
                    continue;
                }
            }
            unset($menus[$key]['plugin']);
        }
        $menus = array_values($menus);
        
        $url = '';
        $result = hook('admin_menu_get_url', []); //获取跳转地址

        foreach ($result as $value){
            if ($value){
                $url = $value;
            }
        }

        $menuId = 0;
        // 将数组转换成树形结构
        $tree = [];
        if (is_array($menus)) {
            $refer = [];
            foreach ($menus as $key => $data) {
                if(!empty($url) && $data['url']==$url){
                    $menuId = $data['id'];
                }
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
        return ['menu' => $tree, 'menu_id' => $menuId, 'url' => $url];
    }

    public function homeMenu(){
        $clientId = get_client_id();
        if(!empty($clientId)){
            $navs = NavModel::field('id,name,url,icon,parent_id,plugin')
                ->where('type', 'home')
                ->order('order','asc')
                ->select()
                ->toArray();
            foreach ($navs as $key => $value) {
                $navs[$key]['name'] = !empty($value['plugin']) ? lang_plugins($value['name']) : lang($value['name']);
            }
            $client = ClientModel::find($clientId);
            $language = $client['language'] ?? get_system_lang(false); 

            $this->createHomeMenu();

            $menus = $this->alias('m')
                ->field('m.id,m.name,m.language,m.url,m.icon,m.parent_id,n.url nav_url,m.menu_type,n.plugin')
                ->leftjoin('nav n', 'n.id=m.nav_id')
                ->where('m.type', 'home')
                ->order('m.order','asc')
                ->select()
                ->toArray();
            foreach ($menus as $key => $data) {
                $data['language'] = json_decode($data['language'], true);
                $menus[$key]['name'] = $data['language'][$language] ?? $data['name'];
                $menus[$key]['url'] = $data['nav_url'] ?? $data['url'];
                if($data['menu_type']=='module' || $data['menu_type']=='res_module'){
                    $menus[$key]['url'] = 'product.html?m='.$data['id'];
                }

                unset($menus[$key]['language'], $menus[$key]['nav_url'], $menus[$key]['menu_type']);
            }
            if(empty($menus)){
                $menus = $navs;
            }
            $plugins = PluginModel::where('status', 1)->column('name');
            foreach ($menus as $key => $value) {
                if(!empty($value['plugin'])){
                    if(!in_array($value['plugin'], $plugins)){
                        unset($menus[$key]);
                        continue;
                    }
                }
                unset($menus[$key]['plugin']);
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
            return ['menu' => $tree];
        }else{
            $goods = NavModel::field('id,name,url,icon,parent_id,plugin')->where('type', 'home')->where('name','nav_goods_list')->find();
            $source1 = NavModel::field('id,name,url,icon,parent_id,plugin')->where('type', 'home')->where('name','nav_plugin_addon_idcsmart_news_source')->find();
            if(!empty($source1)){
                $source = $source1;
            }else{
                $source2 = NavModel::field('id,name,url,icon,parent_id,plugin')->where('type', 'home')->where('name','nav_plugin_addon_idcsmart_help_source')->find();
                if(!empty($source2)){
                    $source = $source2;
                }else{
                    $source3 = NavModel::field('id,name,url,icon,parent_id,plugin')->where('type', 'home')->where('name','nav_plugin_addon_idcsmart_news_source')->find();
                    if(!empty($source3)){
                        $source = $source3;
                    }else{
                        $source = [];
                    }
                }
            }
            
            
            $tree = [
                [
                    'name' => lang($goods['name']),
                    'url' => $goods['url'],
                    'icon' => $goods['icon'],
                    'parent_id' => 0,
                    'child' => [],
                ]
            ];
            if(!empty($source)){
                /*$tree[] = [
                    'name' => '分隔符',
                    'url' => '',
                    'icon' => '',
                    'parent_id' => 0,
                    'child' => [],
                ];*/
                $tree[] = [
                    'name' => lang_plugins($source['name']),
                    'url' => $source['url'],
                    'icon' => 'icon-a-15',
                    'parent_id' => 0,
                    'child' => [],
                ];
            }
            return ['menu' => $tree];
        }
        
    }

    # 创建默认导航
    public function createHomeMenu()
    {
        $count = $this->where('type', 'home')->count();
        if($count>0){
            return true;
        }

        $navs = NavModel::where('parent_id', 0)->where('module', '')->where('type', 'home')->select()->toArray();
        $navs = array_column($navs, 'id', 'name');

        $idcsmartTicketPlugins = NavModel::where('plugin', 'IdcsmartTicket')->where('type', 'home')->select()->toArray();
        $idcsmartTicketPlugins = array_column($idcsmartTicketPlugins, 'id', 'name');

        
        $idcsmartNewsPlugins = NavModel::where('plugin', 'IdcsmartNews')->where('type', 'home')->select()->toArray();
        $idcsmartNewsPlugins = array_column($idcsmartNewsPlugins, 'id', 'name');
        if(isset($idcsmartNewsPlugins['nav_plugin_addon_idcsmart_news_source'])){
            $source = $idcsmartNewsPlugins['nav_plugin_addon_idcsmart_news_source'];
        }else{
            $idcsmartHelpPlugins = NavModel::where('plugin', 'IdcsmartHelp')->where('type', 'home')->select()->toArray();
            $idcsmartHelpPlugins = array_column($idcsmartHelpPlugins, 'id', 'name');
            if(isset($idcsmartHelpPlugins['nav_plugin_addon_idcsmart_help_source'])){
                $source = $idcsmartHelpPlugins['nav_plugin_addon_idcsmart_help_source'];
            }else{
                $idcsmartFileDownloadPlugins = NavModel::where('plugin', 'IdcsmartFileDownload')->where('type', 'home')->select()->toArray();
                $idcsmartFileDownloadPlugins = array_column($idcsmartFileDownloadPlugins, 'id', 'name');
                if(isset($idcsmartFileDownloadPlugins['nav_plugin_addon_idcsmart_file_download_source'])){
                    $source = $idcsmartFileDownloadPlugins['nav_plugin_addon_idcsmart_file_download_source'];
                }else{
                    $source = 0;
                }
            }
        }

        $param = [
            'menu' => [
                [
                    'type' => 'system',
                    'name' => '订购产品',
                    'url' => '',
                    'icon' => 'icon-a-7',
                    'nav_id' => $navs['nav_goods_list'] ?? 0,
                    'child' => [
                    ]
                ],
                [
                    'type' => 'module',
                    'name' => '云服务器',
                    'url' => '',
                    'icon' => 'icon-a-6',
                    'nav_id' => 0,
                    'module' => 'common_cloud',
                    'product_id' => [],
                    'child' => [
                    ]
                ],
                [
                    'type' => 'module',
                    'name' => '独立服务器',
                    'url' => '',
                    'icon' => 'icon-a-6',
                    'nav_id' => 0,
                    'module' => 'idcsmart_dcim',
                    'product_id' => [],
                    'child' => [
                    ]
                ],
                [
                    'type' => 'module',
                    'name' => '其他',
                    'url' => '',
                    'icon' => 'icon-a-6',
                    'nav_id' => 0,
                    'module' => 'idcsmart_common',
                    'product_id' => [],
                    'child' => [
                    ]
                ],
                [
                    'type' => 'custom',
                    'name' => '分隔符',
                    'url' => '',
                    'icon' => '',
                    'nav_id' => 0,
                    'child' => [
                    ]
                ],
                [
                    'type' => 'system',
                    'name' => '账户信息',
                    'url' => '',
                    'icon' => 'icon-a-20',
                    'nav_id' => $navs['nav_account_info'] ?? 0,
                    'child' => [
                    ]
                ],
                [
                    'type' => 'system',
                    'name' => '财务信息',
                    'url' => '',
                    'icon' => 'icon-a-4',
                    'nav_id' => $navs['nav_finance_info'] ?? 0,
                    'child' => [
                    ]
                ],
                [
                    'type' => 'system',
                    'name' => '安全中心',
                    'url' => '',
                    'icon' => 'icon-a-19',
                    'nav_id' => $navs['nav_security'] ?? 0,
                    'child' => [
                    ]
                ],
            ]   
        ];
        if(isset($idcsmartTicketPlugins['nav_plugin_addon_ticket_list']) && !empty($idcsmartTicketPlugins['nav_plugin_addon_ticket_list'])){
            $param['menu'][] = [
                'type' => 'plugin',
                'name' => '工单中心',
                'url' => '',
                'icon' => 'icon-a-17',
                'nav_id' => $idcsmartTicketPlugins['nav_plugin_addon_ticket_list'] ?? 0,
                'child' => [
                ]
            ];
        }
        if(isset($source) && !empty($source)){
            $param['menu'][] = [
                'type' => 'plugin',
                'name' => '资源中心',
                'url' => '',
                'icon' => 'icon-a-15',
                'nav_id' => $source ?? 0,
                'child' => [
                ]
            ];
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
                    'module'        => $value['module'] ?? '',
                    'product_id'    => isset($value['product_id']) ? json_encode($value['product_id']) : '',
                    'order'         => $order,
                    'create_time'   => time(),
                ]);
                $order++;
                if(isset($value['child'])){
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
                            'module'        => $v['module'] ?? '',
                            'product_id'    => isset($v['product_id']) ? json_encode($v['product_id']) : '',
                            'order'         => $order,
                            'create_time'   => time(),
                        ];
                        $order++;
                    }
                }
                
            }

            $this->insertAll($list);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }
        return true;
    }

    public function createHomeModuleMenu($productId = 0)
    {   

        $product = ProductModel::alias('p')
            ->field('p.id,p.name,p.product_group_id,s.module,ss.module module2')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where('p.id', $productId)
            ->find();
        if(empty($product)){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        $module = !empty($product['module']) ? $product['module'] : $product['module2'];
        if(empty($module)){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        $menu = $this->where('type', 'home')->where('menu_type', 'module')->where('module', $module)->find();
        if(!empty($menu)){
            return ['status' => 200, 'msg' => lang('success_message')];
        }
        $menus = $this->alias('m')
            ->field('m.id,m.name,m.language,m.url,m.icon,m.parent_id,n.url nav_url,m.menu_type,m.nav_id,m.module,m.product_id')
            ->leftjoin('nav n', 'n.id=m.nav_id')
            ->where('m.type', 'home')
            ->order('m.order','asc')
            ->select()
            ->toArray();
        foreach ($menus as $key => $data) {
            $menus[$key]['language'] = json_decode($data['language'], true);
            $menus[$key]['product_id'] = json_decode($data['product_id'], true);
        }
        if(empty($menus)){
            $navs = NavModel::field('id,name,url,icon,parent_id,module')
                ->where('type', 'home')
                ->order('order','asc')
                ->select()
                ->toArray();
            foreach ($navs as $key => $value) {
                $navs[$key]['name'] = !empty($value['module']) ? lang_plugins($value['name']) : lang($value['name']);
                $navs[$key]['menu_type'] = !empty($value['module']) ? 'plugin' : 'system';
                $navs[$key]['nav_id'] = $value['id'];
                unset($navs[$key]['module']);
            }
            $menus = $navs;
        }

        $nav = NavModel::where('name', 'nav_goods_list')->find();

        $i = 0;
        $parentId = 0;
        foreach ($menus as $key => $value) {
            if($value['menu_type']=='module'){
               $i = $key+1;
               $parentId =  $value['parent_id'];
            }else if(empty($i) && !empty($nav) && $value['nav_id']==$nav['id']){
                $i = $key+1;
                $parentId =  $value['parent_id'];
            }
        }

        $ModuleLogic = new ModuleLogic();

        $moduleList = $ModuleLogic->getModuleList();
        $moduleList = array_column($moduleList, 'display_name', 'name');

        $arr = [
            'id' => (int)$this->max('id')+1,
            'menu_type' => 'module',
            'name' => $moduleList[$module] ?? $module,
            'module' => $module,
            'product_id' => [$productId],
            'parent_id' => $parentId,
        ];

        $list = [];
        foreach ($menus as $key => $value) {
            if($key>$i){
                $list[$key+1] = $value;
            }else if($key==$i){
                $list[$i] = $arr;
                $list[$key+1] = $value;
            }else{
                $list[$key] = $value;
            }
        }
        $menus = $list;

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

        $this->where('type', 'home')->delete();
        $list = [];
        $order = 0;
        foreach ($tree as $key => $value) {
            $menu = $this->create([
                'type'          => 'home',
                'menu_type'     => $value['menu_type'],
                'name'          => $value['name'] ?? '',
                'language'      => isset($value['language']) ? json_encode($value['language']) : '',
                'url'           => $value['url'] ?? '',
                'icon'          => $value['icon'] ?? '',
                'nav_id'        => $value['nav_id'] ?? 0,
                'parent_id'     => 0,
                'module'        => $value['module'] ?? '',
                'product_id'    => isset($value['product_id']) ? json_encode($value['product_id']) : '',
                'order'         => $order,
                'create_time'   => time(),
            ]);
            $order++;
            if(isset($value['child'])){
                foreach ($value['child'] as $k => $v) {
                    $list[] = [
                        'type'          => 'home',
                        'menu_type'     => $v['menu_type'],
                        'name'          => $v['name'] ?? '',
                        'language'      => isset($v['language']) ? json_encode($v['language']) : '',
                        'url'           => $v['url'] ?? '',
                        'icon'          => $v['icon'] ?? '',
                        'nav_id'        => $v['nav_id'] ?? 0,
                        'parent_id'     => $menu->id,
                        'module'        => $v['module'] ?? '',
                        'product_id'    => isset($v['product_id']) ? json_encode($v['product_id']) : '',
                        'order'         => $order,
                        'create_time'   => time(),
                    ];
                    $order++;
                }
            }
            
        }

        $this->insertAll($list);

        return ['status' => 200, 'msg' => lang('success_message')];
    }

    public function deleteHomeModuleMenu($productId = 0)
    {
        $menus = $this->where('type', 'home')->where('menu_type', 'module')->select()->toArray();

        $menuId = 0;
        foreach ($menus as $key => $value) {
            $value['product_id'] = json_decode($value['product_id'], true);
            if(in_array($productId, $value['product_id'])){
                $k = array_search($productId, $value['product_id']);
                if($k!==false){
                    unset($value['product_id'][$k]);
                    if(empty($value['product_id'])){
                        $menuId = $value['id'];
                        break;
                    }
                }
            }
        }

        if(!empty($menuId)){
            $this->destroy($menuId);
        }

        return ['status' => 200, 'msg' => lang('success_message')];
    }
}
