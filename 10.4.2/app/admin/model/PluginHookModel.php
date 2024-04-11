<?php
namespace app\admin\model;

use think\Model;

/**
 * @title 插件钩子模型
 * @desc 插件钩子模型
 * @use app\admin\model\PluginHookModel
 */
class PluginHookModel extends Model
{
    protected $name = 'plugin_hook';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'name'            => 'string',
        'status'          => 'int',
        'plugin'          => 'string',
        'module'          => 'string',
        'order'           => 'int',
    ];

    // 缓存插件钩子
    public function cacheHook()
    {
        $systemHookPlugins = $this->alias('a')
            ->field('a.name,a.plugin')
            ->leftjoin('plugin b', 'b.name=a.plugin')
            ->where('a.status',1)
            ->where('a.module','addon') # 仅插件
            ->order('b.hook_order', 'asc')
            ->select()->toArray();

        //cache('system_plugin_hooks',$systemHookPlugins);

        return $systemHookPlugins;
    }

    // 获取插件钩子
    public function getCacheHook()
    {
        return $this->cacheHook();
        if (empty(cache('system_plugin_hooks'))){
            $systemHookPlugins = $this->cacheHook();
        }else{
            $systemHookPlugins = cache('system_plugin_hooks');
        }

        return $systemHookPlugins;
    }
}