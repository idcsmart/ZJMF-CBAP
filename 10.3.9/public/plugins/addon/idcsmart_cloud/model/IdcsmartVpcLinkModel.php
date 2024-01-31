<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title VPC模型
 * @desc VPC模型
 * @use addon\idcsmart_cloud\model\IdcsmartVpcLinkModel
 */
class IdcsmartVpcLinkModel extends Model
{
    protected $name = 'addon_idcsmart_vpc_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_vpc_id' => 'int',
        'server_id'             => 'int',
        'vpc_network_id'     	=> 'int',
    ];

    /**
     * 时间 2022-06-29
     * @title 保存关联关系
     * @desc 保存关联关系
     * @author hh
     * @version v1
     * @param   int param.addon_idcsmart_vpc_id - VPCID require
     * @param   int param.server_id - 接口ID require
     * @param   int param.vpc_network_id - 魔方云VPCID require
     */
    public function saveVpcLink($param){
    	$where = [];
    	$where[] = ['addon_idcsmart_vpc_id', '=', $param['addon_idcsmart_vpc_id']];
    	$where[] = ['server_id', '=', $param['server_id']];

    	$vpcLink = $this->where($where)
    					->find();
    	if(!empty($vpcLink)){
    		$this->where($where)->update(['vpc_network_id'=>$param['vpc_network_id']]);
    	}else{
    		$this->create($param, ['addon_idcsmart_vpc_id','server_id','vpc_network_id']);
    	}
    	return true;
    }
    
}