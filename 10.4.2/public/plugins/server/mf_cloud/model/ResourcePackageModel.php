<?php 
namespace server\mf_cloud\model;

use think\Model;

/**
 * @title 资源包模型
 * @use   server\mf_cloud\model\ResourcePackageModel
 */
class ResourcePackageModel extends Model
{
	protected $name = 'module_mf_cloud_resource_package';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'name'              => 'string',
        'rid'               => 'int',
    ];

    /**
     * 时间 2024-02-18
     * @title 保存资源包
     * @desc  保存资源包
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int data[].rid - 魔方云资源包ID
     * @param   string data[].name - 资源包名称
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function saveResourcePackage($product_id, $data)
    {
        
        $resourcePackage = [];

        foreach($data as $v){
            $resourcePackage[] = [
                'product_id'    => $product_id,
                'rid'           => $v['rid'],
                'name'          => $v['name'],
            ];
        }
        $this->startTrans();
        try{
            $this->where('product_id', $product_id)->delete();

            if(!empty($resourcePackage)){
                $this->insertAll($resourcePackage);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('update_failed')];
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }



}