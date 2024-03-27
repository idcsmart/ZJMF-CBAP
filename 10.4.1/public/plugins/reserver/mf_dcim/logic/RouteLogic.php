<?php 

namespace reserver\mf_dcim\logic;

use app\common\model\HostModel;
use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;

/**
 * @title 路由类
 * @use   reserver\mf_dcim\logic\RouteLogic
 */
class RouteLogic
{
	// 超时时间
	protected $timeout = 60;

	// 是否是代理商品
	public $isUpstream = true;

	// 当下游用后台下单时
	public $clientId = 0;

	/**
	 * 时间 2023-02-16
	 * @title 获取代理商品信息(利润,代理商)
	 * @desc 获取代理商品信息(利润,代理商)
	 * @author hh
	 * @version v1
	 * @throws \Exception 非代理商品抛出
	 * @param   int id - 商品ID require
	 */
	public function routeByProduct($id)
	{
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
	 * @title 获取产品代理商品信息(利润,代理商)
	 * @desc 获取产品代理商品信息(利润,代理商)
	 * @author hh
	 * @version v1
	 * @throws \Exception 非代理产品抛出
	 * @param   int id - 产品ID require
	 */
	public function routeByHost($id)
	{
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

	/**
	 * 时间 2024-02-20
	 * @title 获取价格倍率
	 * @desc  获取价格倍率
	 * @author hh
	 * @version v1
	 * @throws \Exception 非代理产品抛出
	 * @return  string
	 */
	public function getPriceMultiple()
	{
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

	/**
	 * 时间 2024-02-20
	 * @title 获取利润百分比
	 * @desc  获取利润百分比
	 * @author hh
	 * @version v1
	 * @throws \Exception 非代理产品抛出
	 * @return  string
	 */
	public function getProfitPercent()
	{
		$this->getPriceMultiple();
		return $this->profit_percent;
	}

	/**
	 * 时间 2024-02-20
	 * @title 设置超时时间
	 * @desc 设置超时时间
	 * @author hh
	 * @version v1
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
	}

	/**
	 * 时间 2023-02-16
	 * @title 请求上游curl
	 * @desc 请求上游curl
	 * @author hh
	 * @version v1
	 * @param   string path - 请求地址路由 require
	 * @param   array data - 请求参数
	 * @param   string request POST 请求方式(POST,GET,DELETE,PUT)
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 * @return  array data - 其他数据
	 */
	public function curl($path, $data = [], $request = 'POST')
	{
		return idcsmart_api_curl($this->supplier_id, $path, $data, $this->timeout, $request);
	}


}
