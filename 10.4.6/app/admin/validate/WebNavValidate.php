<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 模板控制器-导航验证
 */
class WebNavValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'parent_id'     => 'integer|egt:0',
        'name'          => 'require|max:20',
        'description'   => 'max:1000',
        'file_address'  => 'max:255',
        'icon'          => 'max:255',
        'show'          => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'parent_id.integer' => 'id_error',
        'parent_id.egt'     => 'id_error',
        'name.require'      => 'web_nav_name_require',
        'name.max'          => 'web_nav_name_error',
        'description.max'   => 'web_nav_description_error',
        'file_address.max'  => 'web_nav_file_address_error',
        'icon.max'          => 'web_nav_icon_error',
        'show.require'      => 'param_error',
        'show.in'           => 'param_error',
    ];

    protected $scene = [
        'create' => ['parent_id', 'name', 'description', 'file_address', 'icon', 'show'],
        'update' => ['id', 'parent_id', 'name', 'description', 'file_address', 'icon', 'show'],
        'show' => ['id', 'show'],
        'order' => ['parent_id'],
    ];
}