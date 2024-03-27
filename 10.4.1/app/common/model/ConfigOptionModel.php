<?php
namespace app\common\model;

use think\Model;

/**
 * @title 配置项模型
 * @desc 配置项模型
 * @use app\common\model\ConfigOptionModel
 */
class ConfigOptionModel extends Model
{
    protected $name = 'config_option';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'module'        => 'string',
        'product_id'    => 'int',
        'name'    		=> 'string',
        'field'    		=> 'string',
        'data'    		=> 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];




}