<?php 
namespace server\idcsmart_cloud\model;

use think\Model;

class ImageDataCenterServerLinkModel extends Model
{
	protected $name = 'module_idcsmart_cloud_image_data_center_server_link';

    // 设置字段信息
    protected $schema = [
        'module_idcsmart_cloud_image_id'                => 'int',
        'module_idcsmart_cloud_data_center_id'          => 'int',
        'server_id'                                     => 'int',
    ];

}