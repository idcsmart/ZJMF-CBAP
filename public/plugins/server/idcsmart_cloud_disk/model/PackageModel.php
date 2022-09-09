<?php 
namespace server\idcsmart_cloud_disk\model;

use think\Model;
use app\common\model\ProductModel;
use app\common\model\HostModel;
use server\idcsmart_cloud\model\HostLinkModel as HL;
use server\idcsmart_cloud\model\DataCenterModel;

class PackageModel extends Model
{
	protected $name = 'module_idcsmart_cloud_disk_package';

    // 设置字段信息
    protected $schema = [
        'id'            						=> 'int',
        'product_id'    						=> 'int',
        'name'       							=> 'string',
        'description'    						=> 'string',
        'module_idcsmart_cloud_data_center_id'	=> 'int',
        'size_min'   							=> 'int',
        'size_max'   							=> 'int',
        'precision'   							=> 'int',
        'price'   								=> 'float',
        'order'  		 						=> 'int',
        'create_time'   						=> 'int',
        'update_time'   						=> 'int',
    ];

    # 套餐列表
	public function packageList($param)
	{
		$param['page'] = $param['page'] ?? 1;
        $param['limit'] = $param['limit'] ?? config('idcsmart.limit');
        // $param['sort'] = $param['sort'] ?? config('idcsmart.sort');

        // if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','bw','flow','price'])){
        //     $param['orderby'] = 'b.id';
        // }else{
        //     $param['orderby'] = 'b.'.$param['orderby'];
        // }

        $package = [];
        $count = 0;

        if(!empty($param['product_id'])){
        	// 先获取当前月付比例
	        $durationPrice = DurationPriceModel::where('product_id', $param['product_id'])
	        								->where('duration', 30)
	        								->find();

			$package = $this
					->alias('p')
					->field('p.id,p.name,p.description,p.module_idcsmart_cloud_data_center_id,c.country,c.country_code,c.city,c.area,p.size_min,p.size_max,p.precision,p.price,p.order')
					->leftJoin('module_idcsmart_cloud_data_center c', 'p.module_idcsmart_cloud_data_center_id=c.id')
					->where('p.product_id', $param['product_id'])
	            	->limit($param['limit'])
	            	->page($param['page'])
	            	// ->order($param['orderby'], $param['sort'])
					->select()
					->toArray();

			bcscale(2);
			foreach($package as $k=>$v){
				$package[$k]['price'] = amount_format(bcmul($durationPrice['disk_ratio'], $v['price']));
			}

			$count = $this->where('product_id', $param['product_id'])->count();
        }else{
        	return ['list' => [], 'count' => 0];
        }

		return ['list' => $package, 'count' => $count];
	}

	# 创建套餐
	public function createPackage($param)
	{
		$product = ProductModel::find($param['product_id']);
		if(empty($product)){
			return ['status'=>400, 'msg'=>lang_plugins('package_product_is_not_exist')];
		}
		if($product->getModule() != 'idcsmart_cloud_disk'){
			return ['status'=>400, 'msg'=>lang_plugins('package_product_is_not_associated_module_disk')];
		}

		$dateCenter = DataCenterModel::where('id', $param['module_idcsmart_cloud_data_center_id'])
						->where('product_id', $product['product_id'])
						->find();
		if(empty($dateCenter)){
			return ['status'=>400, 'msg'=>lang_plugins('package_data_center_id_error')];
		}

		$this->startTrans();
		try {
	    	$package = $this->create([
	    		'product_id' => $param['product_id'],
	    		'name' => $param['name'] ?? '',
	    		'description' => $param['description'] ?? '',
	    		'module_idcsmart_cloud_data_center_id' => $param['module_idcsmart_cloud_data_center_id'],
	    		'size_min' => $param['size_min'] ?? 0,
	    		'size_max' => $param['size_max'] ?? 0,
	    		'precision' => $param['precision'] ?? 0,
	    		'price' => $param['price'] ?? 0,
	    		'order' => $param['order'] ?? 0,
                'create_time' => time()
	    	]);

	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang_plugins('create_fail')];
		}
    	return ['status' => 200, 'msg' => lang_plugins('create_success'), 'data' => ['id' => $package->id]];
	}

	# 修改套餐
	public function updatePackage($param)
	{
		$package = $this->find($param['id']);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_is_not_exist')];
		}
		$product = ProductModel::find($package['product_id']);
		if(empty($product)){
			return ['status'=>400, 'msg'=>lang_plugins('package_product_is_not_exist')];
		}
		$dateCenter = DataCenterModel::where('id', $param['module_idcsmart_cloud_data_center_id'])
						->where('product_id', $product['product_id'])
						->find();
		if(empty($dateCenter)){
			return ['status'=>400, 'msg'=>lang_plugins('package_data_center_id_error')];
		}

		$this->startTrans();
		try {
            $this->update([
                'name' => $param['name'] ?? '',
	    		'description' => $param['description'] ?? '',
	    		'module_idcsmart_cloud_data_center_id' => $param['module_idcsmart_cloud_data_center_id'],
	    		'size_min' => $param['size_min'] ?? 0,
	    		'size_max' => $param['size_max'] ?? 0,
	    		'precision' => $param['precision'] ?? 0,
	    		'price' => $param['price'] ?? 0,
	    		'order' => $param['order'] ?? 0,
                'update_time' => time()
            ], ['id' => $param['id']]);

		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang_plugins('update_fail')];
		}
    	return ['status' => 200, 'msg' => lang_plugins('update_success')];
	}

	# 删除套餐
	public function deletePackage($id)
	{
		$package = $this->find($id);
		if(empty($package)){
			return ['status'=>400, 'msg'=>lang_plugins('package_is_not_exist')];
		}

		$this->startTrans();
		try{
			$package->delete();

			$this->commit();
		}catch(\Exception $e){
			$this->rollback();
			return ['status'=>400, 'msg'=>$e->getMessage()];
		}

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('delete_success'),
		];
		return $result;
	}

	# 获取订购页实例配置
	public function orderConfigShow($param){
		// 获取数据中心
		if(!empty($param['host_id'])){
			$hostLink = HL::where('host_id', $param['host_id'])->find();
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

		// 获取可用的套餐
		$package = PackageModel::alias('p')
					->field('p.id,p.name,p.description,p.price,p.size_min,p.size_max,p.precision')
					->where('p.product_id', $param['product_id'])
					->where('p.module_idcsmart_cloud_data_center_id', $hostLink['module_idcsmart_cloud_data_center_id'])
					->order('p.order', 'asc')
					->select()
					->toArray();

		return $package;
	}

}