<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;

/**
 * @title 镜像分类模型
 * @use   server\mf_dcim\model\ImageGroupModel
 */
class ImageGroupModel extends Model
{
	protected $name = 'module_mf_dcim_image_group';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
        'icon'          => 'string',
        'order'         => 'int',
        'create_time'   => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 添加操作系统分类
     * @desc 添加操作系统分类
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.name - 分类名称 require
     * @param   string param.icon - 系统图标 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 操作系统分类ID
     */
    public function imageGroupCreate($param)
    {
    	$ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }
        $exist = $this->where('product_id', $param['product_id'])->where('name', $param['name'])->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_name_already_add')];
        }

        $imageGroup = $this->create($param, ['product_id','name','icon']);

        $description = lang_plugins('mf_dcim_log_add_image_group_success', ['{name}'=>$param['name']]);
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
     * @param   int param.product_id - 商品ID
     * @return  int list[].id - 操作系统分类ID
     * @return  string list[].name - 操作系统分类名称
     * @return  string list[].icon - 图标
     * @return  int count - 总条数
     */
    public function imageGroupList($param)
    {
    	$where = [];

    	if(isset($param['product_id']) && is_numeric($param['product_id'])){
    		$where[] = ['product_id', '=', $param['product_id']];
    	}
    	$list = $this
    		->field('id,name,icon')
			->where($where)
            ->order('order', 'asc')
            ->order('id', 'desc')
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
     * @param   int param.id - 操作系统分类ID require
     * @param   string param.name - 分类名称 require
     * @param   string param.icon - 系统图标 require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function imageGroupUpdate($param)
    {
    	$imageGroup = $this->find($param['id']);
        if(empty($imageGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_not_found')];
        }
        $exist = $this->where('product_id', $imageGroup['product_id'])->where('name', $param['name'])->where('id', '<>', $param['id'])->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_name_already_add')];
        }

        $this->update($param, ['id'=>$imageGroup->id], ['name','icon']);

        if($imageGroup['name'] != $param['name']){
            $description = lang_plugins('mf_dcim_log_modify_image_group_success', [
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
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function imageGroupDelete($id)
    {
        $imageGroup = $this->find($id);
        if(empty($imageGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_not_found')];
        }
        $image = ImageModel::where('image_group_id', $id)->find();
        if(!empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_cannot_delete')];
        }
        $this->startTrans();
        try{
        	$this->where('id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('mf_dcim_log_delete_image_group_success', ['{name}'=>$imageGroup['name']]);
        active_log($description, 'product', $imageGroup['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-05-06
     * @title 镜像分组排序
     * @desc 镜像分组排序
     * @author hh
     * @version v1
     * @param   array image_group_order - 镜像分组ID(排好序的ID) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function imageGroupOrder($param)
    {
        $imageGroup = $this->whereIn('id', $param['image_group_order'])->select()->toArray();
        if(count($imageGroup) != count($param['image_group_order'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_not_found')];
        }
        $order = 0;
        foreach($param['image_group_order'] as $v){
            $this->update(['order'=>$order], ['id'=>$v]);
            $order++;
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

}