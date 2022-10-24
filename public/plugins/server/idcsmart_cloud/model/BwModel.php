<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use server\idcsmart_cloud\logic\ToolLogic;

class BwModel extends Model
{
	protected $name = 'module_idcsmart_cloud_bw';

    // 设置字段信息
    protected $schema = [
        'id'                				=> 'int',
        'product_id'                		=> 'int',
        'module_idcsmart_cloud_bw_type_id'  => 'int',
        'bw'             					=> 'int',
        'flow'             					=> 'int',
        'price'             				=> 'float',
        'description'       				=> 'string',
        'create_time'       				=> 'int',
        'update_time'       				=> 'int',
        'flow_type'							=> 'string',
        'in_bw_enable'						=> 'int',
        'in_bw'								=> 'int',
    ];

	/**
	 * 时间 2022-06-16
	 * @title 带宽列表
	 * @desc 带宽列表
	 * @author hh
	 * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序字段(id,bw,flow,price)
     * @param   string param.sort - 升降序(asc=升序,desc=降序)
     * @param   int param.product_id - 商品ID
     * @param   array param.data_center_id - 搜索数据中心
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 带宽ID
     * @return  int data.list[].module_idcsmart_cloud_bw_type_id - 带宽类型ID
     * @return  int data.list[].bw - 带宽
     * @return  int data.list[].flow - 流量
     * @return  string data.list[].price - 价格
     * @return  string data.list[].description - 描述
     * @return  string data.list[].flow_type - 流量统计方向(in=进,out=出,all=进+出)
     * @return  int data.list[].in_bw_enable - 是否启用独立进带宽(0=不是,1=是)
     * @return  int data.list[].in_bw - 进带宽
     * @return  string data.list[].bw_type_name - 带宽类型名称
     * @return  int data.list[].data_center[].id - 数据中心ID
     * @return  string data.list[].data_center[].country - 国家
     * @return  string data.list[].data_center[].city - 城市
     * @return  string data.list[].data_center[].area - 区域
     * @return  int data.count - 总条数
	 */
	public function bwList($param)
	{
		$param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['data_center_id'] = $param['data_center_id'] ?? [];

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','bw','flow','price'])){
            $param['orderby'] = 'b.id';
        }else{
            $param['orderby'] = 'b.'.$param['orderby'];
        }

        $where = [];
        
    	if(!empty($param['product_id'])){
    		$where[] = ['b.product_id', '=', $param['product_id']];
    	}

        if(!empty($param['data_center_id']) && is_array($param['data_center_id'])){
        	$link = BwDataCenterLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $param['data_center_id'])
        			->group('module_idcsmart_cloud_bw_id,module_idcsmart_cloud_data_center_id')
        			->select();

        	$linkArr = [];
        	foreach($link as $v){
        		$linkArr[ $v['module_idcsmart_cloud_bw_id'] ][] = $v['module_idcsmart_cloud_data_center_id'];
        	}

        	$bwId = [];
        	foreach($linkArr as $k=>$v){
        		if(count($v) == count($param['data_center_id'])){
        			$bwId[] = $k;
        		}
        	}

        	if(!empty($bwId)){
        		$where[] = ['b.id', 'IN', $bwId];
        	}else{
        		$where[] = ['b.id', '=', 0];
        	}
        }

		$bw = $this
			->alias('b')
			->field('b.id,b.module_idcsmart_cloud_bw_type_id,b.bw,b.flow,b.price,b.description,b.flow_type,b.in_bw_enable,b.in_bw,bt.name bw_type_name')
			->leftJoin('module_idcsmart_cloud_bw_type bt', 'b.module_idcsmart_cloud_bw_type_id=bt.id')
			->where($where)
			->group('b.id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
			->select()
			->toArray();

		if(!empty($bw)){
			$bwDataCenterArr = [];

			$bwDataCenterLink = BwDataCenterLinkModel::alias('bdcl')
							->field('bdcl.module_idcsmart_cloud_bw_id,dc.id,dc.id,dc.country,dc.city,dc.area')
							->leftJoin('module_idcsmart_cloud_data_center dc', 'bdcl.module_idcsmart_cloud_data_center_id=dc.id')
							->whereIn('module_idcsmart_cloud_bw_id', array_column($bw, 'id'))
							->select();

			foreach($bwDataCenterLink as $v){
				$bwId = $v['module_idcsmart_cloud_bw_id'];
				unset($v['module_idcsmart_cloud_bw_id']);
				$bwDataCenterArr[$bwId][] = $v;
			}

			foreach($bw as $k=>$v){
				$bw[$k]['data_center'] = $bwDataCenterArr[$v['id']] ?? [];
				$bw[$k]['price'] = amount_format($v['price']);
			}
		}
		$count = $this->alias('b')->where($where)->count();

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$bw,
				'count'=>$count
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 创建带宽
	 * @desc 创建带宽
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   int param.module_idcsmart_cloud_bw_type_id - 带宽类型ID require
	 * @param   array param.data_center_id - 数据中心ID require
	 * @param   int param.bw - 带宽 require
	 * @param   int param.flow - 流量 require
	 * @param   float param.price - 价格 require
	 * @param   string param.description - 描述
	 * @param   string param.flow_type all 流量统计方向(in=进,out=出,all=进+出)
	 * @param   int param.in_bw_enable 0 是否启用独立进带宽(0=不是,1=是)
	 * @param   int param.in_bw 0 进带宽
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的带宽ID
	 */
	public function createBw($param)
	{
		$ProductModel = ProductModel::find($param['product_id']);
		if(empty($ProductModel)){
			return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
		}
		if($ProductModel->getModule() != 'idcsmart_cloud'){
			return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
		}
		if(empty($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('please_select_data_center')];
		}
		$bwType = BwTypeModel::find($param['module_idcsmart_cloud_bw_type_id']);
		if(empty($bwType) || $bwType['product_id'] != $param['product_id']){
			return ['status'=>400, 'msg'=>lang_plugins('bw_type_not_found')];
		}
		$dateCenter = DataCenterModel::whereIn('id', $param['data_center_id'])
						->where('product_id', $param['product_id'])
						->select()
						->toArray();
		if(count($dateCenter) != count($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('data_center_id_error')];
		}

		$param['create_time'] = time();
		$param['description'] = $param['description'] ?? '';
		$param['flow_type']   = $param['flow_type'] ?? 'all';
		$param['in_bw_enable'] = $param['in_bw_enable'] ?? 0;
		if($param['in_bw_enable'] == 1){
			$param['in_bw'] = $param['in_bw'] ?? 0;
		}else{
			$param['in_bw'] = 0;
		}

		$BwDataCenterLinkModel = new BwDataCenterLinkModel();

		$this->startTrans();
		try{
			$bw = $this->create($param, ['module_idcsmart_cloud_bw_type_id','bw','flow','price','description','create_time','product_id','flow_type','in_bw_enable','in_bw']);

			$link = [];
			foreach($dateCenter as $v){
				$link[] = [
					'module_idcsmart_cloud_bw_id'			=> $bw->id,
					'module_idcsmart_cloud_data_center_id'	=> $v['id'],
				];
			}
			$BwDataCenterLinkModel->saveAll($link);

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();

			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$description = lang_plugins('log_add_bw_success', [
			'{bw_type}' => $bwType['name'],
			'{bw}'	 	=> $param['bw'],
		]);
		active_log($description, 'product', $ProductModel['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('create_success'),
			'data'   => [
				'id' => (int)$bw->id,
			],
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 修改带宽
	 * @desc 修改带宽
	 * @author hh
	 * @version v1
	 * @param   int param.id - 带宽ID require
	 * @param   int param.module_idcsmart_cloud_bw_type_id - 带宽类型ID require
	 * @param   array param.data_center_id - 数据中心ID require
	 * @param   int param.bw - 带宽 require
	 * @param   int param.flow - 流量 require
	 * @param   float param.price - 价格 require
	 * @param   string param.description - 描述
	 * @param   string param.flow_type - 流量统计方向(in=进,out=出,all=进+出)
	 * @param   int param.in_bw_enable - 是否启用独立进带宽(0=不是,1=是)
	 * @param   int param.in_bw - 进带宽
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateBw($param)
	{
		$bw = $this->find($param['id']);
		if(empty($bw)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_not_found')];
		}
		if(empty($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('please_select_data_center')];
		}
		$bwType = BwTypeModel::find($param['module_idcsmart_cloud_bw_type_id']);
		if(empty($bwType)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_type_not_found')];
		}
		$dateCenter = DataCenterModel::whereIn('id', $param['data_center_id'])
						->select()
						->toArray();
		if(count($dateCenter) != count($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('data_center_id_error')];
		}
		if($param['in_bw_enable'] == 0){
			$param['in_bw'] = 0;
		}

		$oldDataCenterArr = BwDataCenterLinkModel::alias('bdcl')
						->field('dc.country,dc.city,dc.area')
						->join('module_idcsmart_cloud_data_center dc', 'bdcl.module_idcsmart_cloud_data_center_id=dc.id')
						->where('bdcl.module_idcsmart_cloud_bw_id', $bw['id'])
						->select()
						->toArray();

		$oldDataCenter = [];
		foreach($oldDataCenterArr as $v){
			$oldDataCenter[] = $v['country'].$v['city'].$v['area'];
		}

		$newDateCenter = [];
		foreach($dateCenter as $v){
			$newDateCenter[] = $v['country'].$v['city'].$v['area'];
		}

		$param['update_time'] = time();
		$BwDataCenterLinkModel = new BwDataCenterLinkModel();

		$this->startTrans();
		try{
			$this->allowField(['module_idcsmart_cloud_bw_type_id','bw','flow','price','description','update_time','flow_type','in_bw_enable','in_bw'])->update($param, ['id'=>$param['id']]);

			$link = [];
			foreach($dateCenter as $v){
				$link[] = [
					'module_idcsmart_cloud_bw_id'			=> $bw->id,
					'module_idcsmart_cloud_data_center_id'	=> $v['id'],
				];
			}
			BwDataCenterLinkModel::where('module_idcsmart_cloud_bw_id', $param['id'])->delete();
			$BwDataCenterLinkModel->saveAll($link);

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();

			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$desc = [
			'module_idcsmart_cloud_bw_type_id'=>lang_plugins('bw_type'),
			'data_center_id'=>lang_plugins('data_center'),
			'bw'=>lang_plugins('bw'),
			'flow'=>lang_plugins('flow'),
			'price'=>lang_plugins('price'),
			'description'=>lang_plugins('description'),
			'flow_type'=>lang_plugins('flow_type'),
			'in_bw_enable'=>lang_plugins('in_bw_enable'),
			'in_bw'=>lang('in_bw'),
		];

		$flowType = [
			'in'=>lang_plugins('in'),
			'out'=>lang_plugins('out'),
			'all'=>lang_plugins('in_plus_out')
		];

		$old = $bw;
		$old['module_idcsmart_cloud_bw_type_id'] = BwTypeModel::where('id', $old['module_idcsmart_cloud_bw_type_id'])->value('name');
		$old['data_center_id'] = implode(',', $oldDataCenter);
		$old['flow_type'] = $flowType[$old['flow_type']];

		$new = $param;
		$new['module_idcsmart_cloud_bw_type_id'] = $bwType['name'];
		$new['data_center_id'] = implode(',', $newDateCenter);
		$new['flow_type'] = $flowType[$new['flow_type']];

		$description = ToolLogic::createEditLog($old, $new, $desc);
		if(!empty($description)){
			$description = lang_plugins('log_modify_bw_success', ['{detail}'=>$description]);
			active_log($description, 'product', $bw['product_id']);
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 删除带宽
	 * @desc 删除带宽
	 * @author hh
	 * @version v1
	 * @param   int id - 带宽ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function deleteBw($id)
	{
		$bw = $this->find($id);
		if(empty($bw)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_not_found')];
		}
		if($bw->isUse()){
			return ['status'=>400, 'msg'=>lang_plugins('bw_is_using')];
		}

		$bwType = BwTypeModel::find($bw['module_idcsmart_cloud_bw_type_id']);

		$this->startTrans();
		try{
			$bw->delete();

			BwDataCenterLinkModel::where('module_idcsmart_cloud_bw_id', $id)->delete();
			// PackageModel::where('module_idcsmart_cloud_bw_id', $id)->update(['module_idcsmart_cloud_bw_id'=>0]);

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$description = lang_plugins('log_delete_bw_success', [
			'{bw_type}'=>$bwType['name'],
			'{bw}'=>$bw['bw'],
		]);
		active_log($description, 'product', $bw['product_id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('delete_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-16
	 * @title 带宽是否在使用
	 * @desc 带宽是否在使用
	 * @author hh
	 * @version v1
	 * @return  bool
	 */
	public function isUse(){
		// TODO 
		$use = PackageModel::where('module_idcsmart_cloud_bw_id', $this->id)->find();
		if(!empty($use)){
			return true;
		}
		return false;
	}






}

