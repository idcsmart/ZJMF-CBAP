<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title VPC模型
 * @desc VPC模型
 * @use addon\idcsmart_cloud\model\IdcsmartVpcHostLinkModel
 */
class IdcsmartVpcHostLinkModel extends Model
{
    protected $name = 'addon_idcsmart_vpc_host_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_vpc_id' => 'int',
        'host_id'               => 'int',
    ];
}