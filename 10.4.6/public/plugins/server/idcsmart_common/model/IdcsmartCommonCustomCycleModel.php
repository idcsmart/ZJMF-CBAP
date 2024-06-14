<?php 
namespace server\idcsmart_common\model;

use think\facade\Db;
use think\Model;

class IdcsmartCommonCustomCycleModel extends Model
{
    protected $name = 'module_idcsmart_common_custom_cycle';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'name'                   => 'string',
        'cycle_time'             => 'int',
        'cycle_unit'             => 'string',
        'create_time'            => 'int',
        'update_time'            => 'int',
    ];

    # 预设周期
    public $preSetCycle = [
        '月' => 1,
        '季' => 3,
        '半年' => 6,
        '一年' => 12,
        '两年' => 24,
        '三年' => 36,
    ];

    # 预设周期
    public function preSetCycle($productId)
    {
        foreach ($this->preSetCycle as $key=>$value){
            $this->insertCyclePrice([
                'product_id' => $productId,
                'name' => $key,
                'cycle_time' => $value,
                'cycle_unit' => 'month',
                'rel_id' => $productId,
                'type' => 'product',
                'amount' => 0,
            ]);
        }
    }

    # 插入周期及价格
    public function insertCyclePrice($param)
    {
        $id = $this->insertGetId([
            'product_id' => $param['product_id'],
            'name' => $param['name'],
            'cycle_time' => $param['cycle_time'],
            'cycle_unit' => $param['cycle_unit'],
            'create_time' => time(),
        ]);

        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();

        $IdcsmartCommonCustomCyclePricingModel->insert([
            'custom_cycle_id' => $id,
            'rel_id' => $param['rel_id'],
            'type' => $param['type']??'product',
            'amount' => $param['amount']
        ]);

        return true;
    }

}