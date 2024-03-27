<?php
namespace app\common\model;

use think\db\Query;
use think\Model;
use think\facade\Db;

/**
 * @title 文件表
 * @desc 文件表
 * @use app\common\model\FileLogModel
 */
class FileLogModel extends Model
{
    protected $name = 'file_log';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'uuid'          => 'string',
        'save_name'     => 'string',
        'name'          => 'string',
        'type'          => 'string',
        'oss_method'    => 'string',
        'create_time'   => 'int',
        'client_id'     => 'int',
        'admin_id'      => 'int',
        'source'        => 'string',
    ];
}
