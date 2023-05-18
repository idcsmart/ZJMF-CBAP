<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 安全组外部关联表模型
 * @desc 安全组外部关联表模型
 * @use addon\idcsmart_cloud\model\IdcsmartSecurityGroupLinkModel
 */
class IdcsmartSecurityGroupLinkModel extends Model
{
    protected $name = 'addon_idcsmart_security_group_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_security_group_id' 	=> 'int',
        'server_id'         				=> 'int',
        'security_id'     					=> 'int',
    ];

    /**
     * 时间 2022-06-29
     * @title 保存关联关系
     * @desc 保存关联关系
     * @author hh
     * @version v1
     * @param   int param.addon_idcsmart_security_group_id - 安全组ID require
     * @param   int param.server_id - 接口ID require
     * @param   int param.security_id - 魔方云安全组ID require
     */
    public function saveSecurityGroupLink($param){
    	$where = [];
    	$where[] = ['addon_idcsmart_security_group_id', '=', $param['addon_idcsmart_security_group_id']];
    	$where[] = ['server_id', '=', $param['server_id']];

    	$securityGroupLink = $this->where($where)->find();
    	if(!empty($securityGroupLink)){
    		$this->where($where)->update(['security_id'=>$param['security_id']]);
    	}else{
    		$this->create($param, ['addon_idcsmart_security_group_id','server_id','security_id']);
    	}
    	return true;
    }

}