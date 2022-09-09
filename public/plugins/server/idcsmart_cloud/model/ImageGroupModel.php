<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\idcsmart_cloud\logic\ToolLogic;

class ImageGroupModel extends Model
{
	protected $name = 'module_idcsmart_cloud_image_group';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'name'              => 'string',
        'order'             => 'int',
        'description'       => 'string',
        'enable'       		=> 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

	/**
	 * 时间 2022-06-20
	 * @title 镜像分组列表
	 * @desc 镜像分组列表
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 镜像分组ID
	 * @return  string data.list[].name - 名称
	 * @return  int data.list[].order - 排序
	 * @return  string data.list[].description - 描述
	 * @return  int data.list[].enable - 是否启用(0=禁用,1=启用)
	 * @return  int data.count - 总条数
	 */
	public function imageGroupList($param)
	{
		$imageGroup = [];
		$count 	  = 0;

		$ProductModel = ProductModel::find($param['product_id'] ?? 0);
		if($ProductModel && $ProductModel->getModule() == 'idcsmart_cloud'){
			$imageGroup = $this
				->field('id,name,order,description,enable')
				->where('product_id', $param['product_id'])
				->select()
				->toArray();

			$count = count($imageGroup);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$imageGroup,
				'count'=>$count
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 创建镜像分组
	 * @desc 创建镜像分组
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   string param.name - 名称 require
	 * @param   int param.enable - 是否启用(0=不启用,1=启用) require
	 * @param   int param.order 0 排序
	 * @param   string param.description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的镜像分组ID
	 */
	public function createImageGroup($param)
	{
		$ProductModel = ProductModel::find((int)$param['product_id']);
		if(empty($ProductModel)){
			return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
		}
		if($ProductModel->getModule() != 'idcsmart_cloud'){
			return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
		}
		$param['create_time'] = time();
		$param['description'] = $param['description'] ?? '';
		
		$ImageGroup = $this->create($param, ['product_id','name','order','description','create_time','enable']);

		$description = lang_plugins('log_create_image_group_success', ['{name}'=>$param['name']]);
        active_log($description, 'product', $ProductModel['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('create_success'),
			'data'   => [
				'id' => (int)$ImageGroup->id,
			],
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改镜像分组
	 * @desc 修改镜像分组
	 * @author hh
	 * @version v1
	 * @param   int param.id - 镜像分组ID require
	 * @param   string param.name - 名称 require
	 * @param   int param.order - 排序
	 * @param   string param.description - 描述
	 * @param   int param.enable - 是否启用
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateImageGroup($param)
	{
		$ImageGroup = $this->find((int)$param['id']);
		if(empty($ImageGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('image_group_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$ImageGroup['id']], ['name','order','description','update_time','enable']);

		$desc = [
            'name'=>lang_plugins('name'),
            'order'=>lang_plugins('order'),
            'description'=>lang_plugins('description'),
            'enable'=>lang_plugins('is_enable'),
        ];

        $description = ToolLogic::createEditLog($ImageGroup, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_image_group_success', [
                '{name}'=>$ImageGroup['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $ImageGroup['product_id']);
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 删除镜像分组
	 * @desc 删除镜像分组
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像分组ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function deleteImageGroup($id)
	{
		// $use = CalModel::where('module_idcsmart_cloud_cal_group_id', $id)->find();
		// if(!empty($use)){
		// 	return ['status'=>400, 'msg'=>lang_plugins('计算型号分组正在使用')];
		// }
		$imageGroup = $this->find($id);
		if(empty($imageGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('image_group_not_found')];
		}
		$image = ImageModel::where('module_idcsmart_cloud_image_group_id', $id)->find();
		if(!empty($image)){
			return ['status'=>400, 'msg'=>lang_plugins('cannot_delete_image_group')];
		}

		$this->startTrans();
		try{
			$imageGroup->delete();

			ImageModel::where('module_idcsmart_cloud_image_group_id', $id)->update(['module_idcsmart_cloud_image_group_id'=>0]);

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$description = lang_plugins('log_delete_image_group_success', ['{name}'=>$imageGroup['name']]);
        active_log($description, 'product', $imageGroup['product_id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('delete_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改排序
	 * @desc 修改排序
	 * @author hh
	 * @version v1
	 * @param   int param.id - 镜像分组ID
	 * @param   int param.order - 排序
	 */
	public function updateOrder($param){
		$imageGroup = $this->find((int)$param['id']);
		if(empty($imageGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('image_group_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$imageGroup['id']], ['order','update_time']);

		$desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($imageGroup, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_image_group_success', [
                '{name}'=>$imageGroup['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $imageGroup['product_id']);
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改是否启用
	 * @desc 修改是否启用
	 * @author hh
	 * @version v1
	 * @param   int param.id - 镜像分组ID
	 * @param   int param.enable - 是否启用(0=不启用,1=启用)
	 */
	public function enable($param){
		$imageGroup = $this->find((int)$param['id']);
		if(empty($imageGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('image_group_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$imageGroup['id']], ['enable','update_time']);

		$desc = [
            'enable'=>lang_plugins('is_enable'),
        ];

        $description = ToolLogic::createEditLog($imageGroup, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_image_group_success', [
                '{name}'=>$imageGroup['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $imageGroup['product_id']);
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}


}

