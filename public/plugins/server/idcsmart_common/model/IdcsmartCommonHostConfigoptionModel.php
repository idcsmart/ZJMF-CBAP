<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonHostConfigoptionModel extends Model
{
    protected $name = 'module_idcsmart_common_host_configoption';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'host_id'                => 'int',
        'configoption_id'        => 'int',
        'configoption_sub_id'    => 'int',
        'qty'                    => 'int',
        'repeat'                 => 'int',
    ];

}