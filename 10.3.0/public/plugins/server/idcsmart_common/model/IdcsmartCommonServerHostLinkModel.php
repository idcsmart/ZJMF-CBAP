<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonServerHostLinkModel extends Model
{
    protected $name = 'module_idcsmart_common_server_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'host_id'                => 'int',
        'server_id'              => 'int',
    ];
}