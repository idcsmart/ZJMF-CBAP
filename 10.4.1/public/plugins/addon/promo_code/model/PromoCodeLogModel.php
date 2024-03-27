<?php
namespace addon\promo_code\model;

use think\Model;
use think\db\Query;

/**
 * @title 优惠码使用记录模型
 * @desc 优惠码使用记录模型
 * @use addon\promo_code\model\PromoCodeLogModel
 */
class PromoCodeLogModel extends Model
{
    protected $name = 'addon_promo_code_log';

    // 设置字段信息
    protected $schema = [
        'id'               		=> 'int',
        'addon_promo_code_id'	=> 'int',
        'host_id'               => 'int',
        'product_id'          	=> 'int',
        'order_id'          	=> 'int',
        'client_id'          	=> 'int',
        'scene'                 => 'string',
        'amount'          		=> 'float',
        'discount'          	=> 'float',
        'create_time'          	=> 'int',
    ];

    public function logList($param)
    {
        $where = function (Query $query) use ($param){
            if (isset($param['id']) && !empty($param['id'])){
                $query->where('addon_promo_code_id',$param['id']);
            }
        };

        $logs = $this->alias('apcl')
            ->field('apcl.id,apcl.client_id,c.username,apcl.order_id,apcl.amount,apcl.discount,apcl.create_time')
            ->leftjoin('client c', 'c.id=apcl.client_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('create_time', 'desc')
            ->select()
            ->toArray();

        $count = $this->where($where)->count();

        return ['list'=>$logs, 'count'=>$count];
    }

    public function hostPromoCodeLog($param)
    {
        $where = function (Query $query) use ($param){
            $query->where('o.status', 'Paid');
            if (isset($param['id']) && !empty($param['id'])){
                $query->where('apcl.host_id',$param['id']);
            }
        };

        $logs = $this->alias('apcl')
            ->field('apcl.id,apcl.order_id,apcl.scene,apc.code,apcl.discount,apcl.create_time')
            ->leftjoin('addon_promo_code apc', 'apc.id=apcl.addon_promo_code_id')
            ->leftjoin('order o', 'o.id=apcl.order_id')
            ->where($where)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();

        $count = $this->alias('apcl')->leftjoin('order o', 'o.id=apcl.order_id')->where($where)->count();

        return ['list'=>$logs, 'count'=>$count];
    }
}
