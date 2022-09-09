<?php 
namespace server\idcsmart_cloud\model;

use think\Model;

class PackageDataCenterLinkModel extends Model
{
	protected $name = 'module_idcsmart_cloud_package_data_center_link';

    // 设置字段信息
    protected $schema = [
        'module_idcsmart_cloud_package_id'       => 'int',
        'module_idcsmart_cloud_data_center_id'   => 'int',
    ];


}