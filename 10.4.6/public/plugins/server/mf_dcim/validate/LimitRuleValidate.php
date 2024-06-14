<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 限制规则验证
 * @use  server\mf_dcim\validate\LimitRuleValidate
 */
class LimitRuleValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'rule'              => 'require|array|checkLimitRule:thinkphp',
        'result'            => 'require|array|checkLimitResult:thinkphp',
    ];

    protected $message = [
        'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'product_id.require'    => 'product_id_error',
        'product_id.integer'    => 'product_id_error',
        'rule.require'          => 'mf_dcim_limit_rule_rule_require',
        'rule.array'            => 'mf_dcim_limit_rule_rule_require',
        'require.require'       => 'mf_dcim_limit_rule_result_require',
        'require.array'         => 'mf_dcim_limit_rule_result_require',
    ];

    protected $scene = [
        'create'        => ['product_id','rule','result'],
        'update'        => ['id','rule','result'],
    ];

    /**
     * 时间 2024-05-10
     * @title 验证规则字段
     * @desc  验证规则字段
     * @author hh
     * @version v1
     * @param   array $value - 规则字段
     * @return  string|bool
     */
    protected function checkLimitRule($value)
    {
        $ruleType = ['data_center','model_config','bw','flow','image'];

        $typeArr = [];
        $hasBw = false;
        $hasFlow = false;
        foreach($value as $type=>$rule){
            if(!in_array($type, $ruleType)){
                continue;
            }
            if(!isset($rule['opt']) || !in_array($rule['opt'], ['eq','neq'])){
                return 'mf_dcim_limit_rule_opt_error';
            }
            if($type == 'bw') $hasBw = true;
            if($type == 'flow') $hasFlow = true;

            if(in_array($type, ['ipv4_num','bw','flow'])){
                $res = $this->checkRange($rule);
                if($res !== true){
                    return $res;
                }
                $typeArr[] = $type;
            }else if(in_array($type, ['data_center','image','model_config','duration'])){
                if(!isset($rule['id']) || !is_array($rule['id']) || empty($rule['id'])){
                    return 'mf_dcim_limit_rule_select_'.$type;
                }
                $typeArr[] = $type;
            }
        }
        if(empty($typeArr)){
            return 'mf_dcim_limit_rule_at_least_one_rule';
        }
        // if(count($typeArr) < 2){
        //     return 'mf_dcim_limit_rule_at_least_two_type';
        // }
        if($hasBw && $hasFlow){
            return 'mf_dcim_limit_rule_cannot_add_bw_and_flow_in_one_rule';
        }
        return true;
    }

    // 验证范围
    protected function checkRange($value)
    {
        $value['min'] = $value['min'] ?? '';
        $value['max'] = $value['max'] ?? '';
        if($value['min'] === '' && $value['max'] === ''){
            return 'mf_dcim_limit_rule_range_min_and_max_at_least_one';
        }
        if($value['min'] !== ''){
            if(!preg_match('/\d+/', $value['min'])){
                return 'mf_dcim_limit_rule_range_min_format_error';
            }
            if($value['min'] < 0 || $value['min'] > 99999999){
                return 'mf_dcim_limit_rule_range_min_format_error';
            }
        }
        if($value['max'] !== ''){
            if(!preg_match('/\d+/', $value['max'])){
                return 'mf_dcim_limit_rule_range_max_format_error';
            }
            if($value['max'] < 0 || $value['max'] > 99999999){
                return 'mf_dcim_limit_rule_range_max_format_error';
            }
        }
        if($value['min'] !== '' && $value['max'] !== ''  && $value['min'] > $value['max']){
            return 'mf_dcim_limit_rule_range_min_cannot_gt_max';
        }
        return true;
    }

    /**
     * 时间 2024-05-24
     * @title 验证限制规则结果
     * @desc  验证限制规则结果
     * @author hh
     * @version v1
     * @param   array $value - 结果 require
     * @return  string|bool
     */
    protected function checkLimitResult($value, $t, $data)
    {
        $ruleType = ['model_config','bw','flow','image'];

        $typeArr = [];
        $hasBw = false;
        $hasFlow = false;
        foreach($value as $type=>$rule){
            if(!in_array($type, $ruleType)){
                continue;
            }
            if(!isset($rule['opt']) || !in_array($rule['opt'], ['eq','neq'])){
                return 'mf_dcim_limit_rule_opt_error';
            }

            if($type == 'bw') $hasBw = true;
            if($type == 'flow') $hasFlow = true;

            if($type == 'cpu'){
                if(!isset($rule['value']) || !is_array($rule['value']) || empty($rule['value'])){
                    return 'mf_dcim_limit_rule_select_'.$type;
                }
                $typeArr[] = $type;
            }else if(in_array($type, ['memory','ipv4_num','ipv6_num','bw','flow','system_disk','data_disk'])){
                $res = $this->checkRange($rule);
                if($res !== true){
                    return $res;
                }
                $typeArr[] = $type;
            }else if(in_array($type, ['data_center','image','model_config','duration'])){
                if(!isset($rule['id']) || !is_array($rule['id']) || empty($rule['id'])){
                    return 'mf_dcim_limit_rule_select_'.$type;
                }
                $typeArr[] = $type;
            }
        }
        if(empty($typeArr)){
            return 'mf_dcim_limit_rule_at_least_one_result';
        }
        // 条件结果不能相交
        $ruleKey = array_keys($data['rule']);
        $intersect = array_intersect($typeArr, $ruleKey);
        if(!empty($intersect)){
            return 'mf_dcim_limit_rule_and_result_type_must_diff';
        }
        if(($hasBw || $hasFlow) && (in_array('bw', $ruleKey) || in_array('flow', $ruleKey))){
            return 'mf_dcim_limit_rule_cannot_add_bw_and_flow_in_one_rule';
        }
        return true;
    }


}