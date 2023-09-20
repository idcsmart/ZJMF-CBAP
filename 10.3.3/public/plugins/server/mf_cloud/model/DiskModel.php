<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 磁盘模型
 * @use server\mf_cloud\model\DiskModel
 */
class DiskModel extends Model{

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

    public function diskList($hostId){
    	$data = $this
    			->field('rel_id id,name,size,create_time,type,is_free')
    			->where('host_id', $hostId)
    			->select()
    			->toArray();
    	return $data;
    }












}