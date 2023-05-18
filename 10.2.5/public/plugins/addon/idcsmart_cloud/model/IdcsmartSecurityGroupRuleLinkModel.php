<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 安全组规则外部关联表模型
 * @desc 安全组规则外部关联表模型
 * @use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleLinkModel
 */
class IdcsmartSecurityGroupRuleLinkModel extends Model
{
    protected $name = 'addon_idcsmart_security_group_rule_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_security_group_rule_id' => 'int',
        'server_id'         					=> 'int',
        'security_rule_id'         				=> 'int',
    ];


    /**
     * 时间 2022-06-29
     * @title 保存关联关系
     * @desc 保存关联关系
     * @author hh
     * @version v1
     * @param   int param.addon_idcsmart_security_group_rule_id - 安全组ID require
     * @param   int param.server_id - 接口ID require
     * @param   int param.security_rule_id - 魔方云安全组规则ID require
     */
    public function saveSecurityGroupRuleLink($param){
    	$where = [];
    	$where[] = ['addon_idcsmart_security_group_rule_id', '=', $param['addon_idcsmart_security_group_rule_id']];
    	$where[] = ['server_id', '=', $param['server_id']];

    	$securityGroupRuleLink = $this->where($where)->find();
    	if(!empty($securityGroupRuleLink)){
    		$this->where($where)->update(['security_rule_id'=>$param['security_rule_id']]);
    	}else{
    		$this->create($param, ['addon_idcsmart_security_group_rule_id','server_id','security_rule_id']);
    	}
    	return true;
    }

}