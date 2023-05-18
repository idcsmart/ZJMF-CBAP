<?php
namespace addon\idcsmart_sub_account\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 子账户关联模型
 * @desc 子账户关联模型
 * @use addon\idcsmart_sub_account\model\IdcsmartSubAccountProjectModel
 */
class IdcsmartSubAccountProjectModel extends Model
{
    protected $name = 'addon_idcsmart_sub_account_project';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_sub_account_id' => 'int',
        'addon_idcsmart_project_id'     => 'int',
    ];
}