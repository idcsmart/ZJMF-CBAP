<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\idcsmart_cloud\logic\ToolLogic;

class CalGroupModel extends Model
{
	protected $name = 'module_idcsmart_cloud_cal_group';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'name'              => 'string',
        'order'             => 'int',
        'description'       => 'string',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

	/**
	 * 时间 2022-06-10
	 * @title 计算型号分组列表
	 * @desc 计算型号分组列表
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 计算型号分组ID
	 * @return  string data.list[].name - 名称
	 * @return  int data.list[].order - 排序
	 * @return  string data.list[].description - 描述
	 * @return  int data.count - 总条数
	 */
	public function calGroupList($param)
	{
		$calGroup = [];
		$count 	  = 0;

		$ProductModel = ProductModel::find($param['product_id'] ?? 0);
		if($ProductModel && $ProductModel->getModule() == 'idcsmart_cloud'){
			$calGroup = $this
				->field('id,name,order,description')
				->where('product_id', $param['product_id'])
				->select()
				->toArray();

			$count = count($calGroup);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$calGroup,
				'count'=>$count
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-06-10
	 * @title 创建计算型号分组
	 * @desc 创建计算型号分组
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   string param.name - 名称 require
	 * @param   int param.order 0 排序
	 * @param   string param.description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的计算型号分组ID
	 */
	public function createCalGroup($param)
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
		
		$calGroup = $this->create($param, ['product_id','name','order','description','create_time']);

		$description = lang_plugins('log_create_cal_group_success', ['{name}'=>$param['name']]);
		active_log($description, 'product', $ProductModel['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('create_success'),
			'data'   => [
				'id' => (int)$calGroup->id,
			],
		];
		return $result;
	}

	/**
	 * 时间 2022-06-13
	 * @title 修改计算型号分组
	 * @desc 修改计算型号分组
	 * @author hh
	 * @version v1
	 * @param   int param.id - 计算型号分组ID require
	 * @param   string param.name - 名称 require
	 * @param   int param.order 0 排序
	 * @param   string param.description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateCalGroup($param)
	{
		$calGroup = $this->find((int)$param['id']);
		if(empty($calGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('cal_group_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$calGroup['id']], ['name','order','description','update_time']);

		$desc = [
			'name'=>lang_plugins('name'),
			'order'=>lang_plugins('order'),
			'description'=>lang_plugins('description'),
		];

		$description = ToolLogic::createEditLog($calGroup, $param, $desc);
		if(!empty($description)){
			$description = lang_plugins('log_modify_cal_group_success', ['{detail}'=>$description]);
			active_log($description, 'product', $calGroup['product_id']);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-10
	 * @title 删除计算型号分组
	 * @desc 删除计算型号分组
	 * @author hh
	 * @version v1
	 * @param   int id - 计算型号分组ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function deleteCalGroup($id)
	{
		$use = CalModel::where('module_idcsmart_cloud_cal_group_id', $id)->find();
		if(!empty($use)){
			return ['status'=>400, 'msg'=>lang_plugins('cal_group_is_using')];
		}
		$calGroup = $this->find($id);
		if(empty($calGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('cal_group_not_found')];
		}
		$calGroup->delete();

		$description = lang_plugins('log_delete_cal_group_success', ['{name}'=>$calGroup['name']]);
		active_log($description, 'product', $calGroup['product_id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('delete_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-17
	 * @title 修改排序
	 * @desc 修改排序
	 * @author hh
	 * @version v1
	 * @param   int param.id - 计算型号分组ID
	 * @param   int param.order - 排序
	 */
	public function updateOrder($param){
		$calGroup = $this->find((int)$param['id']);
		if(empty($calGroup)){
			return ['status'=>400, 'msg'=>lang_plugins('cal_group_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$calGroup['id']], ['order','update_time']);

		$desc = [
			'order'=>lang_plugins('order'),
		];

		$description = ToolLogic::createEditLog($calGroup, $param, $desc);
		if(!empty($description)){
			$description = lang_plugins('log_modify_cal_group_success', ['{detail}'=>$description]);
			active_log($description, 'product', $calGroup['product_id']);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}


}

