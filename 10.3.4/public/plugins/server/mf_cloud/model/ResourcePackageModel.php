<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
// use app\common\model\ProductModel;
// use server\mf_cloud\logic\ToolLogic;

/**
 * @title 资源包模型
 * @use   server\mf_cloud\model\ResourcePackageModel
 */
class ResourcePackageModel extends Model{

	protected $name = 'module_mf_cloud_resource_package';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'name'              => 'string',
        'rid'               => 'int',
    ];


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