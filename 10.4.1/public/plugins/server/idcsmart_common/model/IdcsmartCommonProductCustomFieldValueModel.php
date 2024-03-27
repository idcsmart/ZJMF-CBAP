<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonProductCustomFieldValueModel extends Model
{
    protected $name = 'module_idcsmart_common_product_custom_field_value';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'host_id'                => 'int',
        'field_id'               => 'int',
        'value'                  => 'string',
        'create_time'            => 'int',
    ];

}