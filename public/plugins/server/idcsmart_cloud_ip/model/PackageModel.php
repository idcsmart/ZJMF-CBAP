<?php 
namespace server\idcsmart_cloud_ip\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use app\common\model\HostModel;
use server\idcsmart_cloud\model\HostLinkModel as HL;
use server\idcsmart_cloud\model\DataCenterModel;
use server\idcsmart_cloud\model\BwModel;
use server\idcsmart_cloud\model\BwDataCenterLinkModel;

class PackageModel extends Model
{
	protected $name = 'module_idcsmart_cloud_ip_package';

    // 设置字段信息
    protected $schema = [
        'id'            				=> 'int',
        'product_id'    				=> 'int',
        'module_idcsmart_cloud_bw_id'	=> 'int',
        'ip_enable'    					=> 'int',
        'ip_price'						=> 'float',
        'ip_max'   						=> 'int',
        'bw_enable'   					=> 'int',
        'bw_precision'   				=> 'int',
        'bw_price'   					=> 'string',
        'create_time'   				=> 'int',
        'update_time'   				=> 'int',
    ];

    # 套餐列表
	public function packageList($param)
	{
		$product = ProductModel::find($param['product_id']);
		if(empty($product)){
			return ['list' => [], 'count' => 0];
		}

		$param['page'] = $param['page'] ?? 1;
        $param['limit'] = $param['limit'] ?? config('idcsmart.limit');
        $param['sort'] = $param['sort'] ?? config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','bw','flow','price'])){
            $param['orderby'] = 'b.id';
        }else{
            $param['orderby'] = 'b.'.$param['orderby'];
        }

        $where = function(Query $query) use ($product){
        	$query->where('b.product_id', $product['product_id']);
        };


		$bw = BwModel::alias('b')
			->field('b.id,b.bw,b.flow,b.price,b.description,bt.name bw_type_name,p.ip_enable,p.ip_price,p.ip_max,p.bw_enable,p.bw_precision,p.bw_price')
			->leftJoin('module_idcsmart_cloud_bw_type bt', 'b.module_idcsmart_cloud_bw_type_id=bt.id')
			->leftJoin('module_idcsmart_cloud_ip_package p', 'b.id=p.module_idcsmart_cloud_bw_id')
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
				$bw[$k]['ip_enable'] = $v['ip_enable'] ?? 0;
				$bw[$k]['ip_price'] = amount_format($v['ip_price'] ?? 0);
				$bw[$k]['ip_max'] = $v['ip_max'] ?? 0;
				$bw[$k]['bw_enable'] = $v['bw_enable'] ?? 0;
				$bw[$k]['bw_precision'] = $v['bw_precision'] ?? 0;
				$bw[$k]['bw_price'] = is_array(json_decode($v['bw_price'], true)) ? json_decode($v['bw_price'], true) : [];
				foreach ($bw[$k]['bw_price'] as $key => $value) {
					$bw[$k]['bw_price'][$key]['price'] = amount_format($value['price']);
				}
				$bw[$k]['data_center'] = $bwDataCenterArr[$v['id']] ?? [];
				$bw[$k]['price'] = amount_format($v['price']);
			}
		}
		$count = BwModel::alias('b')->where($where)->count();

		return ['list' => $bw, 'count' => $count];
	}

	# 保存套餐
	public function savePackage($param)
	{
		$package = $this->where('module_idcsmart_cloud_bw_id', $param['id'])->find();

		if(!empty($package)){
			$product = ProductModel::find($package['product_id']);
			if(empty($product)){
				return ['status'=>400, 'msg'=>lang_plugins('package_product_is_not_exist')];
			}
			$bw = BwModel::where('id', $param['id'])
						->where('product_id', $product['product_id'])
						->find();
			if(empty($bw)){
				return ['status'=>400, 'msg'=>lang_plugins('package_bw_id_error')];
			}
		}else{
			$product = ProductModel::find($param['product_id']);
			if(empty($product)){
				return ['status'=>400, 'msg'=>lang_plugins('package_product_is_not_exist')];
			}
			$bw = BwModel::where('id', $param['id'])
						->where('product_id', $product['product_id'])
						->find();
			if(empty($bw)){
				return ['status'=>400, 'msg'=>lang_plugins('package_bw_id_error')];
			}
		}

		$this->startTrans();
		try {
			if(empty($package)){
				$package = $this->create([
					'module_idcsmart_cloud_bw_id' => $param['id'],
		    		'product_id' => $param['product_id'],
		    		'ip_enable' => $param['ip_enable'] ?? 0,
		    		'ip_max' => $param['ip_max'] ?? 0,
		    		'ip_price' => $param['ip_price'] ?? 0,
		    		'bw_enable' => $param['bw_enable'] ?? 0,
		    		'bw_precision' => $param['bw_precision'] ?? 0,
		    		'bw_price' => json_encode($param['bw_price'] ?? []),
	                'create_time' => time()
		    	]);
			}else{
				$this->update([
	                'ip_enable' => $param['ip_enable'] ?? 0,
		    		'ip_max' => $param['ip_max'] ?? 0,
		    		'ip_price' => $param['ip_price'] ?? 0,
		    		'bw_enable' => $param['bw_enable'] ?? 0,
		    		'bw_precision' => $param['bw_precision'] ?? 0,
		    		'bw_price' => json_encode($param['bw_price'] ?? []),
	                'update_time' => time()
	            ], ['module_idcsmart_cloud_bw_id' => $param['id']]);
			}
            

		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => $e->getMessage()];
		    //return ['status' => 400, 'msg' => lang_plugins('update_fail')];
		}
    	return ['status' => 200, 'msg' => lang_plugins('update_success')];
	}

	# 启用/禁用附加IP
	/*public function ipEnable($param)
	{
		$package = $this->where('module_idcsmart_cloud_bw_id', $param['id'])->find();
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_is_not_exist')];
		}

		$this->startTrans();
		try {
			$this->update([
                'ip_enable' => $param['ip_enable'] ?? 0,
                'update_time' => time()
            ], ['module_idcsmart_cloud_bw_id' => $param['id']]);

		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang_plugins('update_fail')];
		}
    	return ['status' => 200, 'msg' => lang_plugins('update_success')];
	}*/

	# 启用/禁用独立带宽
	/*public function bwEnable($param)
	{
		$package = $this->where('module_idcsmart_cloud_bw_id', $param['id'])->find();
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_is_not_exist')];
		}

		if($package['ip_enable']!=1 && $param['bw_enable']==1){
			return ['status'=>400, 'msg'=>lang_plugins('package_bw_enable_error')];
		}

		$this->startTrans();
		try {
			$this->update([
                'bw_enable' => $param['bw_enable'],
                'update_time' => time()
            ], ['module_idcsmart_cloud_bw_id' => $param['id']]);
            
		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang_plugins('update_fail')];
		}
    	return ['status' => 200, 'msg' => lang_plugins('update_success')];
	}*/

	# 获取订购页实例配置
	public function orderConfigShow($param){
		// 获取数据中心
		if(!empty($param['host_id'])){
			$hostLink = HL::alias('hl')->field('p.module_idcsmart_cloud_bw_id')->leftJoin('module_idcsmart_cloud_package p', 'p.id=hl.module_idcsmart_cloud_package_id')->where('hl.host_id', $param['host_id'])->find();
			
			if(empty($hostLink)){
				return [];
			}
			$host = HostModel::find($param['host_id']);

			if(empty($host)){
				return [];
			}
			$product = ProductModel::find($param['product_id']);

			if(empty($product)){
				return [];
			}
			if($product['product_id']!=$host['product_id']){
				return [];
			}
		}else{
			return [];
		}

		$where = function(Query $query) use ($param, $hostLink){
			if($param['bw_type']=='independ'){
				$query->where('p.bw_enable', 1);
			}else{
				$query->where('p.module_idcsmart_cloud_bw_id', $hostLink['module_idcsmart_cloud_bw_id']);
			}
        	
        };

		// 获取可用的套餐
		$package = PackageModel::alias('p')
					->field('p.id,p.ip_price,p.ip_max,p.bw_precision,p.bw_price,bt.name bw_type_name')
					->leftJoin('module_idcsmart_cloud_bw b', 'b.id=p.module_idcsmart_cloud_bw_id')
					->leftJoin('module_idcsmart_cloud_bw_type bt', 'b.module_idcsmart_cloud_bw_type_id=bt.id')
					->where('p.product_id', $param['product_id'])
					->where('p.ip_enable', 1)
					->where($where)
					->select()
					->toArray();

		foreach ($package as $key => $value) {
			$package[$key]['bw_price'] = !empty($value['bw_price']) ? json_decode($value['bw_price'], true) : [];
		}

		return $package;
	}

}