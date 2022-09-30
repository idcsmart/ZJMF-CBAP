<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonCustomCycleModel extends Model
{
    protected $name = 'module_idcsmart_common_custom_cycle';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'name'                   => 'string',
        'cycle_time'             => 'int',
        'cycle_unit'             => 'string',
        'create_time'            => 'int',
        'update_time'            => 'int',
    ];

}