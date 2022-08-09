<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 升降级模型
 * @desc 升降级模型
 * @use app\common\model\UpgradeModel
 */
class UpgradeModel extends Model
{
	protected $name = 'upgrade';

	// 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'order_id'              => 'int',
        'client_id'             => 'int',
        'host_id'               => 'int',
        'type'      	        => 'string',
        'rel_id'                => 'int',
        'data'                  => 'string',
        'amount'                => 'float',
        'price'                 => 'float',
        'billing_cycle_name'    => 'string',
        'billing_cycle_time'    => 'int',
        'status'                => 'string',
        'description'           => 'string',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

}
