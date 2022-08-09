<?php
namespace addon\idcsmart_file_download\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 文件关联产品模型
 * @desc 文件关联产品模型
 * @use addon\idcsmart_file_download\model\IdcsmartFileLinkModel
 */
class IdcsmartFileLinkModel extends Model
{
    protected $name = 'addon_idcsmart_file_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_file_id'    => 'int',
        'product_id'                => 'int',
    ];
}