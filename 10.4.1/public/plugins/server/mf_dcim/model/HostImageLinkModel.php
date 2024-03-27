<?php 
namespace server\mf_dcim\model;

use think\Model;

/**
 * @title 产品镜像关联模型
 * @use server\mf_dcim\model\HostImageLinkModel
 */
class HostImageLinkModel extends Model
{
	protected $name = 'module_mf_dcim_host_image_link';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'image_id'  => 'int',
    ];

    /**
     * 时间 2023-02-06
     * @title 保存关联关系
     * @desc 保存关联关系
     * @author hh
     * @version v1
     * @param   int $hostId - 产品ID require
     * @param   int $imageId - 镜像ID require
     * @return  bool
     */
    public function saveLink($hostId, $imageId)
    {
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