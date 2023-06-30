<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\ConfigModel;

/**
 * @title 保存参数验证
 * @use  server\mf_cloud\validate\HostUpdateValidate
 */
class HostUpdateValidate extends Validate{

	protected $rule = [
        'cpu'                   => 'integer|between:1,240',
        'memory'                => 'integer|checkValue:thinkphp',
        'bw'                    => 'integer|between:0,30000',
        'in_bw'                 => 'integer|between:0,30000',
        'out_bw'                => 'integer|between:0,30000',
        'flow'                  => 'integer|between:0,999999',
        'defence'               => 'integer|between:0,999999',
        'ip_num'                => 'integer|between:0,10000',
    ];

    protected $message = [
        'cpu.integer'           => 'CPU只能是1-240的整数',
        'cpu.between'           => 'CPU只能是1-240的整数',
        'memory.integer'        => '内存只能是1-512的整数',
        'memory.between'        => '内存只能是1-512的整数',
        'bw.integer'            => '带宽只能是0-30000的整数',
        'bw.between'            => '带宽只能是0-30000的整数',
        'in_bw.integer'         => '进带宽只能是0-30000的整数',
        'in_bw.between'         => '进带宽只能是0-30000的整数',
        'out_bw.integer'        => '出带宽只能是0-30000的整数',
        'out_bw.between'        => '出带宽只能是0-30000的整数',
        'flow.integer'          => '流量只能是0-999999的整数',
        'flow.between'          => '流量只能是0-999999的整数',
        'defence.integer'       => '防御只能是0-999999的整数',
        'defence.between'       => '防御只能是0-999999的整数',
        'ip_num.integer'        => '附加IP数量只能是0-999999的整数',
        'ip_num.between'        => '附加IP数量只能是0-999999的整数',
    ];

    protected $scene = [
        'update' => ['cpu','memory','bw','in_bw','out_bw','flow','defence','ip_num'],
    ];

     public function checkValue($value, $type, $param){
        // $memoryUnit = '';
        // $id = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::MEMORY)->value('id');
        // if(empty($id)){
        //     if(isset($param['memory_unit']) && !empty($param['memory_unit'])){
        //         $memoryUnit = $param['memory_unit'];
        //     }
        // }
        // if(empty($memoryUnit)){
        $memoryUnit = ConfigModel::where('product_id', $param['product_id'])->value('memory_unit') ?? 'GB';
        // }

        if($memoryUnit == 'MB'){
            if($value < 128 || $value > 524288){
                return '内存只能是128-524288的整数';
            }
        }else{
            if($value < 1 || $value > 512){
                return 'memory_value_format_error';
            }
        }
        return true;
    }

}