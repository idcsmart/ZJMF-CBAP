<?php
namespace app\common\model;

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
        'parent_id'     => 'int',
        'order'         => 'int',
        'module'        => 'string',
        'plugin'        => 'string',
    ];

}
