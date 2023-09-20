<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路验证
 * @use  server\mf_cloud\validate\LineValidate
 */
class LineValidate extends Validate{

	protected $rule = [
        'id'                    => 'require|integer',
        'data_center_id'        => 'require|integer',
        'name'                  => 'require|length:1,50',
        'bill_type'             => 'require|in:bw,flow',
        'bw_ip_group'           => 'integer',
        'defence_enable'        => 'require|in:0,1',
        'defence_ip_group'      => 'integer',
        'ip_enable'             => 'require|in:0,1',
        'link_clone'            => 'require|in:0,1',
        'bw_data'               => 'requireIf:bill_type,bw|array|checkBwData:thinkphp',
        'flow_data'             => 'requireIf:bill_type,flow|array|checkFlowData:thinkphp',
        'defence_data'          => 'requireIf:defence_enable,1|array|checkDefenceData:thinkphp',
        'ip_data'               => 'requireIf:ip_enable,1|array|checkIpData:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'data_center_id.require'        => 'please_select_data_center',
        'data_center_id.integer'        => 'please_select_data_center',
        'name.require'                  => 'please_input_line_name',
        'name.length'                   => 'line_name_length_error',
        'bill_type.require'             => 'please_select_line_bill_type',
        'bill_type.in'                  => 'please_select_line_bill_type',
        'bw_ip_group.integer'           => 'line_bw_ip_group_must_int',
        'defence_enable.require'        => 'line_defence_enable_param_error',
        'defence_enable.in'             => 'line_defence_enable_param_error',
        'defence_ip_group.integer'      => 'line_defence_ip_group_must_int',
        'ip_enable.require'             => 'line_ip_enable_param_error',
        'ip_enable.in'                  => 'line_ip_enable_param_error',
        'link_clone.require'            => 'mf_cloud_link_clone_param_error',
        'link_clone.in'                 => 'mf_cloud_link_clone_param_error',
        'bw_data.requireIf'             => 'please_add_at_lease_one_bw_data',
        'bw_data.array'                 => 'please_add_at_lease_one_bw_data',
        'flow_data.requireIf'           => 'please_add_at_lease_one_flow_data',
        'flow_data.array'               => 'please_add_at_lease_one_flow_data',
        'defence_data.requireIf'        => 'please_add_at_lease_one_defence_data',
        'defence_data.array'            => 'please_add_at_lease_one_defence_data',
        'ip_data.requireIf'             => 'please_add_at_lease_one_ip_data',
        'ip_data.array'                 => 'please_add_at_lease_one_ip_data',
    ];

    protected $scene = [
        'create' => ['data_center_id','name','bill_type','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone','bw_data','flow_data','defence_data','ip_data'],
        'update' => ['id','name','bw_ip_group','defence_enable','defence_ip_group','ip_enable','link_clone'],
    ];

    public function checkBwData($value, $t, $param){
        if($param['bill_type'] == 'flow'){
            return true;
        }
        $type = null;
        $LineBwValidate = new LineBwValidate();
        foreach($value as $k=>$v){
            if (!$LineBwValidate->scene('line_create')->check($v)){
                return $LineBwValidate->getError();
            }
            // 验证类型是否一致
            if(!isset($type)){
                $type = $v['type'];
            }else{
                if($type != $v['type']){
                    return 'option_type_must_only_one_type';
                }
            }
            // 验证范围数字是否有交集
            if($type == 'radio'){
                if (!$LineBwValidate->scene('radio')->check($v)){
                    return $LineBwValidate->getError();
                }
            }else{
                if (!$LineBwValidate->scene('step')->check($v)){
                    return $LineBwValidate->getError();
                }
                foreach($value as $kk=>$vv){
                    if($k != $kk){
                        // 有交集
                        if(!($v['max_value']<$vv['min_value'] || $v['min_value']>$vv['max_value'])){
                            return 'line_bw_range_intersect';
                        }
                    }
                }
            }
        }
        if($type == 'radio'){
            $optionValue = array_column($value, 'value');
            if( count($optionValue) != count( array_unique($optionValue) )){
                return 'line_bw_already_exist';
            }
        }
        return true;
    }
    
    public function checkFlowData($value, $t, $param){
        if($param['bill_type'] == 'bw'){
            return true;
        }
        $LineFlowValidate = new LineFlowValidate();
        foreach($value as $k=>$v){
            if (!$LineFlowValidate->scene('line_create')->check($v)){
                return $LineFlowValidate->getError();
            }
        }
        $optionValue = array_column($value, 'value');
        if( count($optionValue) != count( array_unique($optionValue) )){
            return 'line_flow_already_exist';
        }
        return true;
    }

    public function checkDefenceData($value){
        $LineDefenceValidate = new LineDefenceValidate();
        foreach($value as $k=>$v){
            if (!$LineDefenceValidate->scene('line_create')->check($v)){
                return $LineDefenceValidate->getError();
            }
        }
        $optionValue = array_column($value, 'value');
        if( count($optionValue) != count( array_unique($optionValue) )){
            return 'line_defence_already_exist';
        }
        return true;
    }

    public function checkIpData($value){
        $LineIpValidate = new LineIpValidate();
        foreach($value as $k=>$v){
            if (!$LineIpValidate->scene('line_create')->check($v)){
                return $LineIpValidate->getError();
            }
        }
        $optionValue = array_column($value, 'value');
        if( count($optionValue) != count( array_unique($optionValue) )){
            return 'line_ip_already_exist';
        }
        return true;
    }










}