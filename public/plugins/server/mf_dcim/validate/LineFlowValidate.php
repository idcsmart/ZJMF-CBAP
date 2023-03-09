<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 线路流量验证
 * @use  server\mf_dcim\validate\LineFlowValidate
 */
class LineFlowValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'value'             => 'require|integer|between:0,999999',
        'price'             => 'checkPrice:thinkphp',
        'other_config'      => 'require|array|checkOtherConfig:thinkphp',
        'in_bw'             => 'require|integer|between:0,30000',
        'out_bw'            => 'require|integer|between:0,30000',
        'bill_cycle'        => 'require|in:month,last_30days',
    ];

    protected $message = [
        'id.require'                        => 'id_error',
        'id.integer'                        => 'id_error',
        'product_id.require'                => 'product_id_error',
        'product_id.integer'                => 'product_id_error',
        'value.require'                     => 'mf_dcim_please_input_line_flow',
        'value.integer'                     => 'mf_dcim_line_flow_format_error',
        'value.between'                     => 'mf_dcim_line_flow_format_error',
        'price.checkPrice'                  => 'mf_dcim_price_cannot_lt_zero',
        'other_config.require'              => 'mf_dcim_option_other_config_param_error',
        'other_config.array'                => 'mf_dcim_option_other_config_param_error',
        'other_config.checkOtherConfig'     => 'mf_dcim_option_other_config_param_error',
        'in_bw.require'                     => 'mf_dcim_please_input_flow_in_bw',
        'in_bw.integer'                     => 'mf_dcim_flow_in_bw_format_error',
        'in_bw.between'                     => 'mf_dcim_flow_in_bw_format_error',
        'out_bw.require'                    => 'mf_dcim_please_input_flow_out_bw',
        'out_bw.integer'                    => 'mf_dcim_flow_out_bw_format_error',
        'out_bw.between'                    => 'mf_dcim_flow_out_bw_format_error',
        'bill_cycle.require'                => 'mf_dcim_please_select_flow_bill_cycle',
        'bill_cycle.in'                     => 'mf_dcim_please_select_flow_bill_cycle',
    ];

    protected $scene = [
        'create'        => ['id','price','other_config'],
        'update'        => ['id','price','other_config'],
        'other_config'  => ['in_bw','out_bw','bill_cycle'],
        'line_create'   => ['value','price','other_config'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return 'mf_dcim_price_must_between_0_999999';
            }
        }
        return true;
    }

    public function checkOtherConfig($value){
        $LineFlowValidate = new LineFlowValidate();
        if(!$LineFlowValidate->scene('other_config')->check($value)){
            return $LineFlowValidate->getError();
        }
        return true;
    }


}