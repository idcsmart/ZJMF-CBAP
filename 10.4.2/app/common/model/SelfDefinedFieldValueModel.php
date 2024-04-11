<?php
namespace app\common\model;

use think\Model;

/**
 * @title 自定义字段值模型
 * @desc 自定义字段值模型
 * @use app\common\model\SelfDefinedFieldValueModel
 */
class SelfDefinedFieldValueModel extends Model
{
    protected $name = 'self_defined_field_value';

    // 设置字段信息
    protected $schema = [
        'id'                        => 'int',
        'self_defined_field_id'     => 'int',
        'relid'                     => 'int',
        'value'                     => 'string',
        'order_id'                  => 'int',
        'create_time'               => 'int',
        'update_time'               => 'int',
    ];

    /**
     * 时间 2024-01-03
     * @title 删除自定义字段值
     * @desc  删除自定义字段值
     * @author hh
     * @version v1
     * @param   string param.type product 自定义字段类型(product=商品)
     * @param   int param.relid - 关联ID(商品ID) require
     */
    public function withDelete($param)
    {
        $id = $this
                ->alias('sdfv')
                ->join('self_defined_field sdf', 'sdfv.self_defined_field_id=sdf.id')
                ->where('sdfv.relid', $param['relid'])
                ->where('sdf.type', $param['type'] ?? 'product')
                ->column('sdfv.id');
        if(!empty($id)){
            $this->whereIn('id', $id)->delete();
        }
    }




}