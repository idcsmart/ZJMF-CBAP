<?php 
namespace server\idcsmart_cloud\model;

use think\Model;

class HostImageLinkModel extends Model{

	protected $name = 'module_idcsmart_cloud_host_image_link';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'host_id'                         => 'int',
        'module_idcsmart_cloud_image_id'  => 'int',
    ];

    public function saveLink($hostId, $imageId){
        $where = [];
        $where[] = ['host_id', '=', $hostId];
        $where[] = ['module_idcsmart_cloud_image_id', '=', $imageId];

        $res = $this->where($where)->find();
        if(empty($res)){
            $this->insert(['host_id'=>$hostId, 'module_idcsmart_cloud_image_id'=>$imageId]);
        }
        return true;
    }




}