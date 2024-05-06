<?php
namespace app\common\model;

use think\Model;

/**
 * @title 配置项模型
 * @desc 配置项模型
 * @use app\common\model\ConfigOptionModel
 */
class ConfigOptionSubModel extends Model
{
    protected $name = 'config_option_sub';

    // 设置字段信息
    protected $schema = [
        'id'      			=> 'int',
        'config_option_id'  => 'int',
        'name'    			=> 'string',
        'field'    			=> 'string',
        'data'    			=> 'string',
        'create_time'   	=> 'int',
        'update_time'   	=> 'int',
    ];

}
