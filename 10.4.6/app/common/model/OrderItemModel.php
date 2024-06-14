<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 订单子项模型
 * @desc 订单子项模型
 * @use app\common\model\OrderItemModel
 */
class OrderItemModel extends Model
{
	protected $name = 'order_item';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'order_id'      => 'int',
        'client_id'     => 'int',
        'host_id'       => 'int',
        'product_id'    => 'int',
        'type'      	=> 'string',
        'rel_id'        => 'int',
        'description'   => 'string',
        'amount'        => 'float',
        'gateway'       => 'string',
        'gateway_name'  => 'string',
        'notes'         => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

}
