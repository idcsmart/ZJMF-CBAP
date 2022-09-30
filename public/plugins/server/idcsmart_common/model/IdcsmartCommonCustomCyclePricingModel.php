<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonCustomCyclePricingModel extends Model
{
    protected $name = 'module_idcsmart_common_custom_cycle_pricing';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'custom_cycle_id'        => 'int',
        'rel_id'                 => 'int',
        'type'                   => 'string',
        'amount'                 => 'float',
    ];

}