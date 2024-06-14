<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 模板控制器-侧边浮窗验证
 */
class SideFloatingWindowValidate extends Validate
{
    protected $rule = [
        'id'        => 'require|integer|gt:0',
        'name'      => 'require|max:20',
        'icon'      => 'require|max:255',
        'content'   => 'require',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'name.require'      => 'side_floating_window_name_require',
        'name.max'          => 'side_floating_window_name_error',
        'icon.require'      => 'side_floating_window_icon_require',
        'icon.max'          => 'side_floating_window_icon_error',
        'content.require'   => 'side_floating_window_content_require',
    ];

    protected $scene = [
        'create' => ['name', 'icon', 'content'],
        'update' => ['id', 'name', 'icon', 'content'],
    ];
}