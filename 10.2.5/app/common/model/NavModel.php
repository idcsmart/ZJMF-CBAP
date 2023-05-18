<?php
namespace app\common\model;

use app\admin\model\PluginModel;
use think\Model;
use think\Db;

/**
 * @title 默认导航模型
 * @desc 默认导航模型
 * @use app\common\model\NavModel
 */
class NavModel extends Model
{
	protected $name = 'nav';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'type'          => 'string',
        'name'          => 'string',
        'url'           => 'string',
        'icon'          => 'string',
        'parent_id'     => 'int',
        'order'         => 'int',
        'module'        => 'string',
        'plugin'        => 'string',
    ];

    /**
     * 时间 2022-08-10
     * @title 插件增加导航
     * @desc 插件增加导航
     * @author theworld
     * @version v1
     * @param array nav - 菜单 required
     * @param string nav[].name - 名称 required
     * @param string nav[].url - 网址 required
     * @param string nav[].in - 定义插件导航位置:可定义导航在某个一级导航之下,默认会放置在此一级导航最后的位置
     * @param string nav[].icon - 导航图标
     * @param int nav[].parent_id - 父ID
     * @param string module - 模块 required
     * @param string plugin - 插件名 required
     * @param string type - 类型:admin后台,home前台
     * @return string msg - 提示信息
     */
    public function createPluginNav($nav,$module,$name,$type='admin')
    {
        if (isset($nav['in']) && !empty($nav['in']) && isset($nav['url']) && !empty($nav['url']) && (!isset($nav['child']) || empty($nav['child']))){
            $firstNav = $this->where('name',$nav['in'])->where('parent_id',0)->find();
            if (!empty($firstNav)){
                $parentId = $firstNav->id;
            }else{
                $parentId = isset($nav['parent_id'])?intval($nav['parent_id']):0;
            }
        }else{
            $parentId = isset($nav['parent_id'])?intval($nav['parent_id']):0;
        }

        $maxOrder = $this->max('order');

        $PluginModel = new PluginModel();
        $pluginId = $PluginModel->where('name',parse_name($name,1))->value('id');

        $url = $type=='admin'?$name:$pluginId;

        $object = $this->create([
            'type' => $type,
            'name' => $nav['name']??'',
            'url'  => (isset($nav['url']) && !empty($nav['url']) && is_string($nav['url']))? (strpos($nav['url'], '.html') === false ? "plugin/{$url}/".$nav['url'].'.html' : "plugin/{$url}/".$nav['url']):'',
            'icon' => $nav['icon']??'',
            'parent_id' => $parentId,
            'order'  => $maxOrder+1,
            'module' => $module,
            'plugin' => parse_name($name,1)
        ]);

        $child = $nav['child']??[];
        foreach ($child as $item){
            $item['parent_id'] = $object->id;
            $this->createPluginNav($item,$module,$name,$type);
        }

        return ['status' => 200, 'msg' => lang('create_success')];

    }

    /**
     * 时间 2022-08-10
     * @title 插件删除导航
     * @desc 插件删除导航
     * @author theworld
     * @version v1
     * @param string module - 模块 required
     * @param string plugin - 插件名 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deletePluginNav($param)
    {
        $navId = $this->where('module', $param['module'])->where('plugin', $param['plugin'])->column('id');
        if(!empty($navId)){
            $MenuModel = new MenuModel();
            $MenuModel->whereIn('nav_id', $navId)->delete();
        }
        $this->where('module', $param['module'])->where('plugin', $param['plugin'])->delete();
        return ['status' => 200, 'msg' => lang('create_success')];
    }

}
