<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\idcsmart_cloud\logic\ToolLogic;

class PackageModel extends Model
{
	protected $name = 'module_idcsmart_cloud_package';

    // 设置字段信息
    protected $schema = [
        'id'            				=> 'int',
        'name'       					=> 'string',
        'product_id'    				=> 'int',
        'module_idcsmart_cloud_cal_id'  => 'int',
        'module_idcsmart_cloud_bw_id'   => 'int',
        'create_time'   				=> 'int',
        'update_time'   				=> 'int',
    ];

    /**
	 * 时间 2022-06-16
	 * @title 套餐列表
	 * @desc 套餐列表
	 * @author hh
	 * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 套餐ID
     * @return  string data.list[].name - 套餐名称
     * @return  int data.list[].module_idcsmart_cloud_cal_id - 计算型号ID
     * @return  int data.list[].module_idcsmart_cloud_bw_id - 带宽ID
     * @return  string data.list[].cal_name - 计算型号名称
     * @return  string data.list[].cal_price - 计算型号价格
     * @return  int data.list[].bw - 带宽
     * @return  int data.list[].flow - 流量
     * @return  string data.list[].bw_price - 带宽价格
     * @return  string data.list[].description - 描述
     * @return  string data.list[].bw_type_name - 带宽类型名称
     * @return  string data.list[].price - 套餐月付价格
     * @return  int    data.list[].data_center[].id - 数据中心ID
     * @return  string data.list[].data_center[].country - 国家
     * @return  string data.list[].data_center[].city - 城市
     * @return  string data.list[].data_center[].area - 区域
     * @return  int data.count - 总条数
	 */
	public function packageList($param)
	{
		$param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        // $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        // if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','bw','flow','price'])){
        //     $param['orderby'] = 'b.id';
        // }else{
        //     $param['orderby'] = 'b.'.$param['orderby'];
        // }

        $package = [];
        $count = 0;

        if(!empty($param['product_id'])){
        	// 先获取当前月付比例
	        // $durationPrice = DurationPriceModel::where('product_id', $param['product_id'])
	        // 								->where('duration', 30)
	        // 								->find();

			$package = $this
					->alias('p')
					->field('p.id,p.name,p.module_idcsmart_cloud_cal_id,p.module_idcsmart_cloud_bw_id,c.name cal_name,c.price cal_price,b.bw,b.flow,b.price bw_price,bt.name bw_type_name')
					->leftJoin('module_idcsmart_cloud_cal c', 'p.module_idcsmart_cloud_cal_id=c.id')
					->leftJoin('module_idcsmart_cloud_bw b', 'p.module_idcsmart_cloud_bw_id=b.id')
					->leftJoin('module_idcsmart_cloud_bw_type bt', 'b.module_idcsmart_cloud_bw_type_id=bt.id')
					->where('p.product_id', $param['product_id'])
					->group('p.id')
	            	->limit($param['limit'])
	            	->page($param['page'])
	            	// ->order($param['orderby'], $param['sort'])
					->select()
					->toArray();

			if(!empty($package)){
				$link = PackageDataCenterLinkModel::alias('pdcl')
						->field('pdcl.module_idcsmart_cloud_package_id,dc.id,dc.country,dc.city,dc.area')
						->leftJoin('module_idcsmart_cloud_data_center dc', 'pdcl.module_idcsmart_cloud_data_center_id=dc.id')
						->whereIn('pdcl.module_idcsmart_cloud_package_id', array_column($package, 'id'))
						->select()
						->toArray();

				$linkArr = [];
				foreach($link as $v){
					$packageId = $v['module_idcsmart_cloud_package_id'];
					unset($v['module_idcsmart_cloud_package_id']);
					$linkArr[ $packageId ][] = $v;
				}

				bcscale(2);
				foreach($package as $k=>$v){
					$package[$k]['price'] = amount_format(bcadd($v['cal_price'], $v['bw_price']));
					$package[$k]['data_center'] = $linkArr[$v['id']] ?? [];
				}
			}

			$count = $this->where('product_id', $param['product_id'])->count();
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list'=>$package,
				'count'=>$count
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-06-17
	 * @title 创建套餐
	 * @desc 创建套餐
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   string param.name - 套餐名称 require
	 * @param   array param.cal_id - 计算型号ID require
	 * @param   array param.data_center_id - 数据中心ID require
	 * @param   array param.bw_id - 带宽ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.id - 创建的套餐ID
	 */
	public function createPackage($param)
	{
		$ProductModel = ProductModel::find($param['product_id'] ?? '');
		if(empty($ProductModel)){
			return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
		}
		if($ProductModel->getModule() != 'idcsmart_cloud'){
			return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
		}
		$cal = CalModel::whereIn('id', $param['cal_id'])
						->where('product_id', $param['product_id'])
						->select()
						->toArray();
		if(count($cal) != count($param['cal_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('cal_not_found')];
		}
		$dateCenter = DataCenterModel::whereIn('id', $param['data_center_id'])
						->where('product_id', $param['product_id'])
						->select()
						->toArray();
		if(count($dateCenter) != count($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
		}
		$bw = BwModel::whereIn('id', $param['bw_id'])
						->where('product_id', $param['product_id'])
						->select()
						->toArray();
		if(count($bw) != count($param['bw_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('bw_not_found')];
		}
		$bwId = null;
		foreach($param['data_center_id'] as $v){
			if(is_null($bwId)){
				$bwId = BwDataCenterLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $v)->whereIn('module_idcsmart_cloud_bw_id', $param['bw_id'])->column('module_idcsmart_cloud_bw_id');
			}else{
				$bwId = array_intersect($bwId, BwDataCenterLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $v)->whereIn('module_idcsmart_cloud_bw_id', $param['bw_id'])->column('module_idcsmart_cloud_bw_id'));
			}
		}
		if(count($bwId) != count($param['bw_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('bw_data_center_diff_with_select_data_center')];
		}

		$PackageDataCenterLinkModel = new PackageDataCenterLinkModel();

		$id = [];
		$name = [];
		$this->startTrans();
		try{

			$i = 1;
			foreach($cal as $v){
				foreach($bw as $vv){
					// 验证带宽,数据中心
					// $bwDataCenter = BwDataCenterLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $param['data_center_id'])->where('module_idcsmart_cloud_bw_id', $vv['id'])->find();
					// if()

					$data = [
						'name'							=> $param['name'].'-'.$i,
						'product_id'					=> $param['product_id'],
						'module_idcsmart_cloud_cal_id'	=> $v['id'],
						'module_idcsmart_cloud_bw_id'	=> $vv['id'],
						'create_time'					=> time()
					];
					// 名称是否重复
					$name_exist = $this->where('name', $data['name'])->where('product_id', $param['product_id'])->find();
					if(!empty($name_exist)){
						throw new \Exception(lang_plugins('package_name_is_using'));
					}
					$package = $this->create($data);

					$dataCenterLink = [];
					foreach($dateCenter as $val){
						$dataCenterLink[] = [
							'module_idcsmart_cloud_package_id' 		=> $package->id,
							'module_idcsmart_cloud_data_center_id'	=> $val['id'],
						];
					}
					$PackageDataCenterLinkModel->saveAll($dataCenterLink);
					$i++;
					$id[] = $package->id;
					$name[] = $data['name'];
				}
			}
			$this->commit();
		}catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>$e->getMessage()];
		}
        $description = lang_plugins('log_create_package_success', [
            '{name}'=>implode(',', $name),
        ]);
        active_log($description, 'product', $ProductModel['id']);
        
		$result = [
			'status' => 200,
			'msg'    => lang_plugins('create_success'),
			'data'   => [
				'id' => $id,
			],
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改套餐
	 * @desc 修改套餐
	 * @author hh
	 * @version v1
	 * @param   int param.id - 套餐ID require
	 * @param   string param.name - 套餐名称 require
	 * @param   int param.module_idcsmart_cloud_cal_id - 计算型号ID require
	 * @param   array param.data_center_id - 数据中心ID require
	 * @param   int param.module_idcsmart_cloud_bw_id - 带宽ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updatePackage($param)
	{
		$package = $this->find($param['id']);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
		}
		if(empty($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('please_select_data_center')];
		}
		$dateCenter = DataCenterModel::whereIn('id', $param['data_center_id'])
						->select()
						->toArray();
		if(count($dateCenter) != count($param['data_center_id'])){
			return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
		}
		$cal = CalModel::where('id', $param['module_idcsmart_cloud_cal_id'])
						->where('product_id', $package['product_id'])
						->find();
		if(empty($cal)){
			return ['status'=>400, 'msg'=>lang_plugins('cal_not_found')];
		}
		$bw = BwModel::where('id', $param['module_idcsmart_cloud_bw_id'])
						->where('product_id', $package['product_id'])
						->find();
		if(empty($bw)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_not_found')];
		}
		$bwId = BwDataCenterLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $param['data_center_id'])
				->where('module_idcsmart_cloud_bw_id', $param['module_idcsmart_cloud_bw_id'])
				->find();
		if(empty($bwId)){
			return ['status'=>400, 'msg'=>lang_plugins('bw_data_center_diff_with_select_data_center')];
		}
		$nameExist = $this
					->where('name', $param['name'])
					->where('product_id', $package['product_id'])
					->where('id', '<>', $param['id'])
					->find();
		if(!empty($nameExist)){
			return ['status'=>400, 'msg'=>lang_plugins('package_name_is_using')];
		}
		$PackageDataCenterLinkModel = new PackageDataCenterLinkModel();

		$param['update_time'] = time();

		$oldDataCenterArr = PackageDataCenterLinkModel::alias('pdcl')
						->field('dc.*')
						->leftJoin('module_idcsmart_cloud_data_center dc', 'pdcl.module_idcsmart_cloud_data_center_id=dc.id')
						->where('pdcl.module_idcsmart_cloud_package_id', $param['id'])
						->select()
						->toArray();
		$oldDataCenter = [];
		foreach($oldDataCenterArr as $v){
			$oldDataCenter[] = $v['country'].$v['city'].$v['area'];
		}

		$bwType = BwTypeModel::find($bw['module_idcsmart_cloud_bw_type_id']);

		$this->startTrans();
		try{
			$this->update($param, ['id'=>$param['id']], ['name','module_idcsmart_cloud_cal_id','module_idcsmart_cloud_bw_id','update_time']);

			PackageDataCenterLinkModel::where('module_idcsmart_cloud_package_id', $param['id'])->delete();

			$dataCenterLink = [];
			foreach($dateCenter as $val){
				$dataCenterLink[] = [
					'module_idcsmart_cloud_package_id' 		=> $package->id,
					'module_idcsmart_cloud_data_center_id'	=> $val['id'],
				];
			}
			$PackageDataCenterLinkModel->saveAll($dataCenterLink);

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();

			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$oldBw = BwModel::alias('b')
				->field('b.*,bt.name')
				->leftJoin('module_idcsmart_cloud_bw_type bt', 'b.module_idcsmart_cloud_bw_type_id=bt.id')
				->where('b.id', $package['module_idcsmart_cloud_bw_id'])
				->find();

		$newDataCenter = [];
		foreach($dateCenter as $v){
			$newDataCenter[] = $v['country'].$v['city'].$v['area'];
		}

		$desc = [
            'name'=>lang_plugins('name'),
            'module_idcsmart_cloud_cal_id'=>lang_plugins('cal'),
            'data_center_id'=>lang_plugins('data_center'),
            'bw'=>lang_plugins('bw'),
        ];

        $old = $package;
        $old['module_idcsmart_cloud_cal_id'] = CalModel::where('id', $old['module_idcsmart_cloud_cal_id'])->value('name');
        $old['data_center_id'] = implode(',', $oldDataCenter);
        $old['bw'] = isset($oldBw) ? $oldBw['name'].$oldBw['bw'].'Mbps' : '';

        $new = $param;
        $new['module_idcsmart_cloud_cal_id'] = $cal['name'];
        $new['data_center_id'] = implode(',', $newDataCenter);
        $new['bw'] = $bwType['name'].$bw['bw'].'Mbps';

        $description = ToolLogic::createEditLog($old, $new, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_package_success', [
                '{name}'=>$package['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $package['product_id']);
        }

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('update_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-20
	 * @title 删除套餐
	 * @desc 删除套餐
	 * @author hh
	 * @version v1
	 * @param   int id - 套餐ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function deletePackage($id)
	{
		$package = $this->find($id);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_not_found')];
		}
		$use = HostLinkModel::where('module_idcsmart_cloud_package_id', $id)->find();
		if(!empty($use)){
			return ['status'=>400, 'msg'=>lang_plugins('package_is_using')];
		}

		$this->startTrans();
		try{
			$package->delete();

			PackageDataCenterLinkModel::where('module_idcsmart_cloud_package_id', $id)->delete();
			// TODO 删除其他关联
			

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$description = lang_plugins('log_delete_package_success', [
            '{name}'=>$package['name'],
        ]);
        active_log($description, 'product', $package['product_id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('delete_success'),
		];
		return $result;
	}

	/**
	 * 时间 2022-06-22
	 * @title 获取订购页实例配置
	 * @desc 获取订购页实例配置
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   int param.data_center_id - 数据中心ID
	 * @param   int param.bw_type_id - 带宽类型ID
	 * @param   int param.cal_group_id - 计算型号分组ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.cal_group - 计算型号分组数据
	 * @return  int data.cal_group[].id - 计算型号分组ID
	 * @return  string data.cal_group[].id - 计算型号分组名称
	 * @return  string data.cal_group[].description - 计算型号分组描述
	 * @return  array data.package - 套餐数据
	 * @return  int data.package[].id - 套餐ID
	 * @return  string data.package[].price - 套餐价格
	 * @return  string data.package[].description - 套餐描述
	 */
	public function orderConfigShow($param){
		// 获取数据中心
		if(!empty($param['data_center_id'])){
			$dataCenter = DataCenterModel::where('product_id', $param['product_id'])->where('id', $param['data_center_id'])->find();
			// if(empty($dataCenter)){
			// 	return ['status'=>400, 'msg'=>lang_plugins('数据中心不存在')];
			// }
		}else{
			$dataCenter = DataCenterModel::where('product_id', $param['product_id'])->order('order', 'asc')->find();
		}
		// 获取带宽可用带宽
		if(empty($param['bw_type_id'])){
			$param['bw_type_id'] = BwTypeModel::where('product_id', $param['product_id'])->order('order', 'asc')->value('id');
		}
		if(!empty($param['bw_type_id'])){
			$bwId = BwModel::where('module_idcsmart_cloud_bw_type_id', $param['bw_type_id'])->where('product_id', $param['product_id'])->column('id');
		}

		$where = [];
		$where[] = ['pdcl.module_idcsmart_cloud_data_center_id', '=', $dataCenter['id'] ?? 0];
		$where[] = ['p.product_id', '=', $param['product_id']];

		if(!empty($bwId)){
			$where[] = ['p.module_idcsmart_cloud_bw_id', 'IN', $bwId];
		}

		$wherePackage = $where;

		// 获取所有可用的计算型号分组
		$calGroupId = PackageModel::alias('p')
					->join('module_idcsmart_cloud_package_data_center_link pdcl', 'pdcl.module_idcsmart_cloud_package_id=p.id')
					->leftJoin('module_idcsmart_cloud_cal c', 'p.module_idcsmart_cloud_cal_id=c.id')
					->leftJoin('module_idcsmart_cloud_cal_group cg', 'c.module_idcsmart_cloud_cal_group_id=cg.id')
					->where($where)
					->group('c.id')
					->order('cg.order', 'asc')
					->order('cg.id', 'asc')
					->column('cg.id');
		$calGroupId = array_values(array_unique(array_filter($calGroupId)));

		// 获取计算型号
		if(isset($param['cal_group_id']) && in_array($param['cal_group_id'], $calGroupId)){
			$calId = CalModel::where('product_id', $param['product_id'])->where('module_idcsmart_cloud_cal_group_id', $param['cal_group_id'])->column('id');

			if(!empty($calId)){
				$wherePackage[] = ['p.module_idcsmart_cloud_cal_id', 'IN', $calId];
			}
		}else{
			if(!empty($calGroupId)){
				$calId = CalModel::where('product_id', $param['product_id'])->where('module_idcsmart_cloud_cal_group_id', $calGroupId[0])->column('id');

				if(!empty($calId)){
					$wherePackage[] = ['p.module_idcsmart_cloud_cal_id', 'IN', $calId];
				}
			}
		}

		$calGroup = [];
		$package = [];
		if(!empty($calGroupId)){
			$calGroup = CalGroupModel::field('id,name,description')
						->whereIn('id', $calGroupId)
						->order('order', 'asc')
						->order('id', 'asc')
						->select()
						->toArray();

			// 获取可用的套餐
			$package = PackageModel::alias('p')
						->field('p.id,c.description cal_description,c.price cal_price,b.description bw_description,b.price bw_price')
						->leftJoin('module_idcsmart_cloud_package_data_center_link pdcl', 'pdcl.module_idcsmart_cloud_package_id=p.id')
						->leftJoin('module_idcsmart_cloud_cal c', 'p.module_idcsmart_cloud_cal_id=c.id')
						->leftJoin('module_idcsmart_cloud_bw b', 'p.module_idcsmart_cloud_bw_id=b.id')
						->where($wherePackage)
						->order('c.order', 'asc')
						->limit(8)
						->group('p.id')
						->select()
						->toArray();

			foreach($package as $k=>$v){
				$package[$k]['price'] = amount_format(bcadd($v['cal_price'], $v['bw_price']));
				$package[$k]['description'] = $v['cal_description'] . $v['bw_description'];
				unset($package[$k]['cal_description'], $package[$k]['bw_description'], $package[$k]['cal_price'], $package[$k]['bw_price']);
			}
		}
		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>[
				'cal_group'=>$calGroup,
				'package'=>$package,
			]
		];
		return $result;
	}



}