<?php 
namespace server\idcsmart_dcim\model;

use think\Model;

class HostImageLinkModel extends Model{

	protected $name = 'module_idcsmart_dcim_host_image_link';

    // protected $pk = 'host_id';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'image_id'  => 'int',
    ];

    public function saveLink($hostId, $imageId){
        $where = [];
        $where[] = ['host_id', '=', $hostId];
        $where[] = ['image_id', '=', $imageId];

        $res = $this->where($where)->find();
        if(empty($res)){
            $this->insert(['host_id'=>$hostId, 'image_id'=>$imageId]);
        }
        return true;
    }




}