<?php 

namespace reserver\whmcs_dcim\logic;

use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;

/**
 * @title 路由类
 * @use   reserver\whmcs_dcim\logic\RouteLogic
 */
class RouteLogic{

	protected $timeout = 60;

	// 是否是代理商品
	public $isUpstream = true;

	/**
	 * 时间 2023-02-16
	 * @title 获取登录成功标识
	 * @desc 获取登录成功标识
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 */
	public function routeByProduct($id){
		$upstreamProduct = UpstreamProductModel::where('product_id', $id)->find();
		if(empty($upstreamProduct)){
			$this->isUpstream = false;
			throw new \Exception("not upstream");
		}
		bcscale(2);

		$this->supplier_id = $upstreamProduct['supplier_id'];
		$this->upstream_product_id = $upstreamProduct['upstream_product_id'];
		$this->price_multiple = bcdiv(bcadd(100, $upstreamProduct['profit_percent']), 100);
		$this->profit_percent = bcdiv($upstreamProduct['profit_percent'], 100);
        $this->profit_type = $upstreamProduct['profit_type'];
	}

	/**
	 * 时间 2023-02-16
	 * @title 获取登录成功标识
	 * @desc 获取登录成功标识
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 */
	public function routeByHost($id){
		$upstreamHost = UpstreamHostModel::where('host_id', $id)->find();
		if(empty($upstreamHost)){
			$this->isUpstream = false;
			throw new \Exception("not upstream");
		}
		bcscale(2);
		$this->host_id = $id;
		$this->supplier_id = $upstreamHost['supplier_id'];
		$this->upstream_host_id = $upstreamHost['upstream_host_id'];

        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id']??0)->find();
        $this->profit_type = $upstreamProduct['profit_type']??0;
	}

	public function getPriceMultiple(){
		if(isset($this->price_multiple)){
			return $this->price_multiple;
		}else if(isset($this->host_id)){
			$productId = HostModel::where('id', $this->host_id)->value('product_id');
			// 获取倍率
			$upstreamProduct = UpstreamProductModel::where('product_id', $productId)->find();
			if(empty($upstreamProduct)){
				$this->isUpstream = false;
				throw new \Exception("not upstream");
			}
			$this->upstream_product_id = $upstreamProduct['upstream_product_id'];
			$this->price_multiple = bcdiv(bcadd(100, $upstreamProduct['profit_percent']), 100);
			$this->profit_percent = bcdiv($upstreamProduct['profit_percent'], 100);
		}else{
			$this->isUpstream = false;
			throw new \Exception("not upstream");
		}
		return $this->price_multiple;
	}

	public function getProfitPercent(){
		$this->getPriceMultiple();
		return $this->profit_percent;
	}

	/**
	 * 时间 2023-02-16
	 * @title 
	 * @desc 
	 * @author hh
	 * @version v1
	 * @param   string $path    
	 * @param   array  $data    所有参数
	 * @param   [type] $request [description]
	 * @return  [type]          [description]
	 */
	public function curl($path, $data = [], $request = 'POST'){
		return idcsmart_api_curl($this->supplier_id, $path, $data, $this->timeout, $request);
	}

	
	
	
	












































}




