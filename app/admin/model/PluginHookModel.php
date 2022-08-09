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
}