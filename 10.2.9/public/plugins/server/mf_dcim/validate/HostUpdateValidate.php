<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 保存参数验证
 * @use  server\mf_dcim\validate\HostUpdateValidate
 */
class HostUpdateValidate extends Validate{

	protected $rule = [
        // 'cpu'                   => 'integer|between:1,240',
        // 'memory'                => 'integer|between:1,512',
        'bw'                    => 'checkBw:thinkphp',
        'in_bw'                 => 'integer|between:0,30000',
        'out_bw'                => 'checkBw:thinkphp',
        'flow'                  => 'integer|between:0,999999',
        'defence'               => 'integer|between:0,999999',
        'ip_num'                => 'checkIpNum:thinkphp',
    ];

    protected $message = [
        // 'cpu.integer'           => 'CPU只能是1-240的整数',
        // 'cpu.between'           => 'CPU只能是1-240的整数',
        // 'memory.integer'        => '内存只能是1-512的整数',
        // 'memory.between'        => '内存只能是1-512的整数',
        // 'bw.integer'            => '带宽只能是0-30000的整数',
        // 'bw.between'            => '带宽只能是0-30000的整数',
        'in_bw.integer'         => '进带宽只能是0-30000的整数',
        'in_bw.between'         => '进带宽只能是0-30000的整数',
        'out_bw.integer'        => '出带宽只能是0-30000的整数',
        'out_bw.between'        => '出带宽只能是0-30000的整数',
        'flow.integer'          => '流量只能是0-999999的整数',
        'flow.between'          => '流量只能是0-999999的整数',
        'defence.integer'       => '防御只能是0-999999的整数',
        'defence.between'       => '防御只能是0-999999的整数',
        // 'ip_num.integer'        => '附加IP数量只能是0-999999的整数',
        // 'ip_num.between'        => '附加IP数量只能是0-999999的整数',
    ];

    protected $scene = [
        'update' => ['bw','in_bw','out_bw','flow','defence','ip_num'],
    ];

    /**
     * 时间 2023-05-15
     * @title 验证带宽格式
     * @desc  验证带宽格式
     * @author hh
     * @version v1
     * @param   int|string $value - 带宽 require
     */
    public function checkBw($value){
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || $value<1 || $value > 30000){
                return 'mf_dcim_line_bw_format_error';
            }
        }else if($value == 'NC'){

        }else{
            return 'mf_dcim_line_bw_format_error';
        }
        return true;
    }

    /**
     * 时间 2023-05-15
     * @title 验证IP数量格式
     * @desc  验证IP数量格式
     * @author hh
     * @version v1
     * @param   int|string $value - IP数量 require
     */
    public function checkIpNum($value){
        if(is_numeric($value)){
            if(strpos($value, '.') !== false || $value<1 || $value > 10000){
                return 'mf_dcim_line_ip_num_format_error';
            }
        }else if($value == 'NC'){

        }else{
            $value = ToolLogic::formatDcimIpNum($value);
            if($value === false){
                return 'IP数量格式错误';
            }
        }
        return true;
    }

}