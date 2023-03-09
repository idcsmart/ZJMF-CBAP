<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 镜像分类模型
 * @use   server\mf_cloud\model\ImageGroupModel
 */
class ImageGroupModel extends Model{

	protected $name = 'module_mf_cloud_image_group';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
        'icon'          => 'string',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 添加操作系统分类
     * @desc 添加操作系统分类
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 分类名称 require
     * @param   string icon - 系统图标 require
     * @return  int id - 操作系统分类ID
     */
    public function imageGroupCreate($param){
    	$ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $exist = $this->where('product_id', $param['product_id'])->where('name', $param['name'])->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_image_group_name_already_add')];
        }

        $imageGroup = $this->create($param, ['product_id','name','icon']);

        $description = lang_plugins('log_mf_cloud_add_image_group_success', ['{name}'=>$param['name']]);
        active_log($description, 'product', $param['product_id']);

        $result = [
        	'status' => 200,
        	'msg'	 => lang_plugins('create_success'),
        	'data'   => [
        		'id' => (int)$imageGroup->id,
        	],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 操作系统分类列表
     * @desc 操作系统分类列表
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 操作系统分类ID
     * @return  string list[].name - 操作系统分类名称
     * @return  string list[].icon - 图标
     * @return  int count - 总条数
     */
    public function imageGroupList($param){
    	$where = [];

    	if(isset($param['product_id']) && is_numeric($param['product_id'])){
    		$where[] = ['product_id', '=', $param['product_id']];
    	}
    	$list = $this
    		->field('id,name,icon')
			->where($where)
			->select()
			->toArray();

		$count = $this
				->where($where)
				->count();

		return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-01
     * @title 修改操作系统分类
     * @desc 修改操作系统分类
     * @author hh
     * @version v1
     * @param   int id - 操作系统分类ID require
     * @param   string name - 分类名称 require
     * @param   string icon - 系统图标 require
     * @return  int id - 操作系统分类ID
     */
    public function imageGroupUpdate($param){
    	$imageGroup = $this->find($param['id']);
        if(empty($imageGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_image_group_not_found')];
        }
        $exist = $this->where('product_id', $imageGroup['product_id'])->where('name', $param['name'])->where('id', '<>', $param['id'])->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_image_group_name_already_add')];
        }

        $this->update($param, ['id'=>$imageGroup->id], ['name','icon']);

        if($imageGroup['name'] != $param['name']){
            $description = lang_plugins('log_mf_cloud_modify_image_group_success', [
                '{name}'=>$imageGroup['name'],
                '{new_name}'=>$param['name'],
            ]);
            active_log($description, 'product', $imageGroup['product_id']);
        }

        $result = [
        	'status' => 200,
        	'msg'	 => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 删除操作系统分类
     * @desc 删除操作系统分类
     * @author hh
     * @version v1
     * @param   int id - 操作系统分类ID require
     */
    public function imageGroupDelete($id){
        $imageGroup = $this->find($id);
        if(empty($imageGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_image_group_not_found')];
        }
        $image = ImageModel::where('image_group_id', $id)->find();
        if(!empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_image_group_cannot_delete')];
        }
        $this->startTrans();
        try{
        	$this->where('id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('log_mf_cloud_delete_image_group_success', ['{name}'=>$imageGroup['name']]);
        active_log($description, 'product', $imageGroup['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }



}