<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use server\idcsmart_cloud\logic\ToolLogic;

class BwTypeModel extends Model
{
	protected $name = 'module_idcsmart_cloud_bw_type';

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
	 * 时间 2022-06-16
	 * @title 带宽类型列表
	 * @desc 带宽类型列表
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID
	 * @param   string param.orderby - 排序(id,name,order)
	 * @param   string param.sort - 升降序(asc=升序,desc=降序)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 带宽类型ID
	 * @return  string data.list[].name - 名称
	 * @return  int data.list[].order - 排序
	 * @return  string data.list[].description - 描述
	 * @return  int data.count - 总条数
	 */
	public function bwTypeList($param)
	{
		$bwType = [];
		$count 	  = 0;

		// $param['page'] = $param['page'] ?? 1;
  //       $param['limit'] = $param['limit'] ?? config('idcsmart.limit');
        $param['sort'] = $param['sort'] ?? config('idcsmart.sort');
        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','name','order'])){
            $param['orderby'] = 'id';
        }

		$where = function(Query $query) use ($param){
			if(!empty($param['product_id'])){
				$query->where('product_id', $param['product_id']);
			}
		};

		$bwType = $this
			->field('id,name,order,description')
			->where($where)
			->order($param['orderby'], $param['sort'])
			->select()
			->toArray();

		$count = count($bwType);
		

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$bwType,
				'count'=>$count
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 创建带宽类型
	 * @desc 创建带宽类型
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   string param.name - 名称 require
	 * @param   int param.order 0 排序
	 * @param   string param.description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的带宽类型ID
	 */
	public function createBwType($param)
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
		
		$bwType = $this->create($param, ['product_id','name','order','description','create_time']);

		$description = lang_plugins('log_create_bw_type_success', ['{name}'=>$param['name']]);
		active_log($description, 'product', $ProductModel['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('create_success'),
			'data'   => [
				'id' => (int)$bwType->id,
			],
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 修改带宽类型
	 * @desc 修改带宽类型
	 * @author hh
	 * @version v1
	 * @param   int param.id - 带宽类型ID require
	 * @param   string param.name - 名称 require
	 * @param   int param.order 0 排序
	 * @param   string param.description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateBwType($param)
	{
		$bwType = $this->find((int)$param['id']);
		if(empty($bwType)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_type_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$bwType['id']], ['name','order','description','update_time']);

		$desc = [
			'name'=>lang_plugins('name'),
			'order'=>lang_plugins('order'),
			'description'=>lang_plugins('description'),
		];

		$old = $bwType;

		$new = $param;
		unset($new['update_time']);

		$description = ToolLogic::createEditLog($old, $new, $desc);
		if(!empty($description)){
			$description = lang_plugins('log_modify_bw_type_success', ['{detail}'=>$description]);
			active_log($description, 'product', $bwType['product_id']);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 删除带宽类型
	 * @desc 删除带宽类型
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽类型ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function deleteBwType($id)
	{
		$bwType = $this->find($id);
		if(empty($bwType)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_type_not_found')];
		}
		if($bwType->isUse()){
			return ['status'=>400, 'msg'=>lang_plugins('bw_type_is_using')];
		}
		$bwType->delete();

		$description = lang_plugins('log_delete_bw_type_success', ['{name}'=>$bwType['name']]);
		active_log($description, 'product', $bwType['product_id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('delete_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 带宽类型是否在使用
	 * @desc 带宽类型是否在使用
	 * @author hh
	 * @version v1
	 * @return  bool
	 */
	public function isUse(){
		$use = BwModel::where('module_idcsmart_cloud_bw_type_id', $this->id)->find();
		if(!empty($use)){
			return true;
		}
		return false;
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
		$bwType = $this->find((int)$param['id']);
		if(empty($bwType)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_type_not_found')];
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$bwType['id']], ['order','update_time']);

		$desc = [
			'order'=>lang_plugins('order'),
		];

		$description = ToolLogic::createEditLog($bwType, $param, $desc);
		if(!empty($description)){
			$description = lang_plugins('log_modify_bw_type_success', ['{detail}'=>$description]);
			active_log($description, 'product', $bwType['product_id']);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}




}

