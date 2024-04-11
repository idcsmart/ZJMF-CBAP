<?php 
namespace server\mf_cloud\model;

use think\Model;

/**
 * @title 磁盘模型
 * @use server\mf_cloud\model\DiskModel
 */
class DiskModel extends Model
{
	protected $name = 'module_mf_cloud_disk';

    // 设置字段信息
    protected $schema = [
        'id'            	=> 'int',
        'name'            	=> 'string',
        'size'            	=> 'int',
        'rel_id'            => 'int',
        'host_id'           => 'int',
        'create_time'       => 'int',
        'type'            	=> 'string',
        'price'             => 'float',
        'is_free'           => 'int',
    ];

    /**
     * 时间 2024-02-18
     * @title 磁盘列表
     * @desc  磁盘列表
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  int [].id - 魔方云磁盘ID
     * @return  string [].name - 名称
     * @return  int [].size - 磁盘大小(GB)
     * @return  int [].create_time - 创建时间
     * @return  string [].type - 磁盘类型
     * @return  int [].is_free - 是否免费盘(0=否,1=是)
     */
    public function diskList($hostId)
    {
    	$data = $this
    			->field('rel_id id,name,size,create_time,type,is_free')
    			->where('host_id', $hostId)
    			->select()
    			->toArray();
    	return $data;
    }












}