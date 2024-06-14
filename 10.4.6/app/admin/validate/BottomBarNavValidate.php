<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 模板控制器-底部栏导航验证
 */
class BottomBarNavValidate extends Validate
{
    protected $rule = [
        'id'        => 'require|integer|gt:0',
        'group_id'  => 'integer|egt:0',
        'name'      => 'require|max:20',
        'url'       => 'require|max:255|url',
        'show'      => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'group_id.integer'  => 'id_error',
        'group_id.egt'      => 'id_error',
        'name.require'      => 'bottom_bar_nav_name_require',
        'name.max'          => 'bottom_bar_nav_name_error',
        'url.require'       => 'bottom_bar_nav_url_require',
        'url.max'           => 'bottom_bar_nav_url_error',
        'url.url'           => 'bottom_bar_nav_url_error',
        'show.require'      => 'param_error',
        'show.in'           => 'param_error',
    ];

    protected $scene = [
        'create' => ['group_id', 'name', 'url', 'show'],
        'update' => ['id', 'group_id', 'name', 'url', 'show'],
        'show' => ['id', 'show'],
        'order' => ['group_id'],
    ];
}