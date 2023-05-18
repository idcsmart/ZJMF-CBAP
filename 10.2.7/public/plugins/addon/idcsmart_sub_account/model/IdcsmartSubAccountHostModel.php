<?php
namespace addon\idcsmart_sub_account\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 子账户产品关联模型
 * @desc 子账户产品关联模型
 * @use addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel
 */
class IdcsmartSubAccountHostModel extends Model
{
    protected $name = 'addon_idcsmart_sub_account_host';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_sub_account_id' => 'int',
        'host_id'     					=> 'int',
    ];
}