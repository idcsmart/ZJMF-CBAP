<?php 
namespace server\idcsmart_cloud\model;

use think\Model;

class DataCenterServerLinkModel extends Model{

	protected $name = 'module_idcsmart_cloud_data_center_server_link';

    // 设置字段信息
    protected $schema = [
        'server_id'                             => 'int',
        'server_param'                          => 'string',
        'module_idcsmart_cloud_data_center_id'  => 'int',
    ];

}