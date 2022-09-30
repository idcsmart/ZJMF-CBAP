<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonPricingModel extends Model
{
    protected $name = 'module_idcsmart_common_pricing';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'type'                   => 'string',
        'rel_id'                 => 'int',
        'onetime'                => 'float',
        'monthly'                => 'float',
        'quarterly'              => 'float',
        'semaiannually'          => 'float',
        'annually'               => 'float',
        'biennially'             => 'float',
        'triennianlly'           => 'float',
    ];

    # 插入价格
    public function commonInsert($param,$relId,$type="product")
    {
        $pricing = $this->where('type',$type)
            ->where('rel_id',$relId)
            ->find();
        if (!empty($pricing)){
            $pricing->save([
                'onetime' => $param['onetime']??-1,
                'monthly' => $param['monthly']??-1,
                'quarterly' => $param['quarterly']??-1,
                'semaiannually' => $param['semaiannually']??-1,
                'annually' => $param['annually']??-1,
                'biennially' => $param['biennially']??-1,
                'triennianlly' => $param['triennianlly']??-1,
            ]);
        }else{
            $this->insert([
                'type' => $type,
                'rel_id' => $relId,
                'onetime' => $param['onetime']??-1,
                'monthly' => $param['monthly']??-1,
                'quarterly' => $param['quarterly']??-1,
                'semaiannually' => $param['semaiannually']??-1,
                'annually' => $param['annually']??-1,
                'biennially' => $param['biennially']??-1,
                'triennianlly' => $param['triennianlly']??-1,
            ]);
        }

        return true;
    }

}