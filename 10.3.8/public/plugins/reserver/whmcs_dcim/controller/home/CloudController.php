<?php
namespace reserver\whmcs_dcim\controller\home;

use think\facade\Cache;
use think\facade\View;
use reserver\whmcs_dcim\validate\HostValidate;
use reserver\whmcs_dcim\logic\RouteLogic;
use reserver\whmcs_dcim\model\SystemLogModel;
use app\common\model\OrderModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\HostModel;
use app\common\model\MenuModel;
use app\common\model\ProductModel;

/**
 * @title DCIM代理(WHMCS)-前台
 * @desc DCIM代理(WHMCS)-前台
 * @use reserver\whmcs_dcim\controller\home\CloudController
 */
class CloudController{

	/**
	 * 时间 2023-02-06
	 * @title 获取订购页面配置
	 * @desc 获取订购页面配置
	 * @url /console/v1/product/:id/rewhmcs_dcim/order_page
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  array cycle - 周期
	 * @return  object pricing - 商品价格
	 * @return  float pricing.msetupfee - 月初装费
	 * @return  float pricing.qsetupfee - 季初装费
	 * @return  float pricing.ssetupfee - 半年初装费
	 * @return  float pricing.asetupfee - 年初装费
	 * @return  float pricing.bsetupfee - 两年初装费
	 * @return  float pricing.tsetupfee - 三年初装费
	 * @return  float pricing.monthly - 月付
	 * @return  float pricing.quarterly - 季付
	 * @return  float pricing.semiannually - 半年付
	 * @return  float pricing.annually - 年付
	 * @return  float pricing.biennially - 两年付
	 * @return  float pricing.triennially - 三年付
	 * @return  array configoption - 配置
	 * @return  int configoption[].id - 配置项ID
	 * @return  string configoption[].optionname - 配置项名称
	 * @return  string configoption[].firstname - 配置项真实值
	 * @return  string configoption[].lastname - 配置项描述
	 * @return  int configoption[].optiontype - 配置项类型1下拉2单选3是否4数量
	 * @return  int configoption[].qtyminimum - 配置项最小值,类型为数量时才有
	 * @return  int configoption[].qtymaximum - 配置项最大值,类型为数量时才有
	 * @return  array configoption[].options - 配置子项
	 * @return  int configoption[].options[].id - 配置子项ID
	 * @return  string configoption[].options[].optionname - 配置子项名称
	 * @return  string configoption[].options[].firstname - 配置子项真实值
	 * @return  string configoption[].options[].lastname - 配置子项描述
	 * @return  int configoption[].options[].currency - 货币
	 * @return  float configoption[].options[].msetupfee - 月初装费
	 * @return  float configoption[].options[].qsetupfee - 季初装费
	 * @return  float configoption[].options[].ssetupfee - 半年初装费
	 * @return  float configoption[].options[].asetupfee - 年初装费
	 * @return  float configoption[].options[].bsetupfee - 两年初装费
	 * @return  float configoption[].options[].tsetupfee - 三年初装费
	 * @return  float configoption[].options[].monthly - 月付
	 * @return  float configoption[].options[].quarterly - 季付
	 * @return  float configoption[].options[].semiannually - 半年付
	 * @return  float configoption[].options[].annually - 年付
	 * @return  float configoption[].options[].biennially - 两年付
	 * @return  float configoption[].options[].triennially - 三年付
	 */
	public function orderPage(){
		$param = request()->param();

		try{
			$product = ProductModel::find($param['id']);

			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			unset($param['id']);
			$param['productid'] = $RouteLogic->upstream_product_id;
			$result = $RouteLogic->curl('product_configOptions', $param, 'POST');
			if($result['status'] == 200){
				$result['data']['cycle'] = [];
				foreach ($result['data']['pricing'] as $key => $value) {
					$result['data']['pricing'][$key] = bcmul($value, $RouteLogic->price_multiple);
				}
				foreach ($result['data']['configoption'] as $key => $value) {
					foreach ($value['options'] as $k => $v) {
						foreach ($v as $kk => $vv) {
							if(in_array($kk, ['msetupfee','qsetupfee','ssetupfee','asetupfee','bsetupfee','tsetupfee','monthly','quarterly','semiannually','annually','biennially','triennially'])){
								if($product['pay_type']=='onetime'){
									$result['data']['cycle'] = ['onetime'];
									if(in_array($kk, ['msetupfee'])){
										if(isset($result['data']['pricing'][$kk])){
											$result['data']['pricing']['setupfee'] = $result['data']['pricing'][$kk];
											unset($result['data']['pricing'][$kk]);
										}
										$result['data']['configoption'][$key]['options'][$k]['setupfee'] = bcmul($vv, $RouteLogic->price_multiple);
										unset($result['data']['configoption'][$key]['options'][$k][$kk]);
									}else if(in_array($kk, ['monthly'])){
										if(isset($result['data']['pricing'][$kk])){
											$result['data']['pricing']['onetime'] = $result['data']['pricing'][$kk];
											unset($result['data']['pricing'][$kk]);
										}
										$result['data']['configoption'][$key]['options'][$k]['onetime'] = bcmul($vv, $RouteLogic->price_multiple);
										unset($result['data']['configoption'][$key]['options'][$k][$kk]);
									}else{
										if(isset($result['data']['pricing'][$kk]))unset($result['data']['pricing'][$kk]);
										unset($result['data']['configoption'][$key]['options'][$k][$kk]);
									}
								}else{
									if(!isset($result['data']['pricing'][$kk]) || $result['data']['pricing'][$kk]<0){
										if(isset($result['data']['pricing'][$kk]))unset($result['data']['pricing'][$kk]);
										unset($result['data']['configoption'][$key]['options'][$k][$kk]);
									}else{
										if($product['pay_type']=='free'){
											$result['data']['cycle'] = ['free'];
											$result['data']['pricing'][$kk] = 0;
											$result['data']['configoption'][$key]['options'][$k][$kk] = 0;
										}else{
											if(in_array($kk, ['monthly','quarterly','semiannually','annually','biennially','triennially']) && !in_array($kk, $result['data']['cycle'])){
												$result['data']['cycle'][] = $kk;
											}
											$result['data']['configoption'][$key]['options'][$k][$kk] = bcmul($vv, $RouteLogic->price_multiple);
										}
									}
								}
							}
						}
					}
				}
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->orderPage();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取升降级配置
	 * @desc 获取升降级配置
	 * @url /console/v1/product/:id/rewhmcs_dcim/upgrade_page
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  array configoption - 配置
	 * @return  int configoption[].id - 配置项ID
	 * @return  string configoption[].optionname - 配置项名称
	 * @return  string configoption[].firstname - 配置项真实值
	 * @return  string configoption[].lastname - 配置项描述
	 * @return  int configoption[].optiontype - 配置项类型1下拉2单选3是否4数量
	 * @return  int configoption[].qtyminimum - 配置项最小值,类型为数量时才有
	 * @return  int configoption[].qtymaximum - 配置项最大值,类型为数量时才有
	 * @return  array configoption[].options - 配置子项
	 * @return  int configoption[].options[].id - 配置子项ID
	 * @return  string configoption[].options[].optionname - 配置子项名称
	 * @return  string configoption[].options[].firstname - 配置子项真实值
	 * @return  string configoption[].options[].lastname - 配置子项描述
	 */
	public function upgradePage(){
		$param = request()->param();

		try{
			$product = ProductModel::find($param['id']);

			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			unset($param['id']);
			$param['productid'] = $RouteLogic->upstream_product_id;
			$result = $RouteLogic->curl('product_configOptions', $param, 'POST');
			if($result['status'] == 200){
				unset($result['data']['pricing']);
				unset($result['data']['customfield']);
				foreach ($result['data']['configoption'] as $key => $value) {
					if(!in_array($value['firstname'], ['ip_num', 'bw', 'bwt'])){
						unset($result['data']['configoption'][$key]);
						continue;
					}
					foreach ($value['options'] as $k => $v) {
						foreach ($v as $kk => $vv) {
							if(in_array($kk, ['msetupfee','qsetupfee','ssetupfee','asetupfee','bsetupfee','tsetupfee','monthly','quarterly','semiannually','annually','biennially','triennially'])){
								unset($result['data']['configoption'][$key]['options'][$k][$kk]);
							}
						}
					}
				}
				$result['data']['configoption'] = array_values($result['data']['configoption']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->orderPage();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取操作系统列表
	 * @desc 获取操作系统列表
	 * @url /console/v1/rewhmcs_dcim/:id/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int list[].id - 操作系统分类ID
	 * @return  string list[].name - 操作系统分类名称
	 * @return  string list[].icon - 操作系统分类图标
	 * @return  int list[].image[].id - 操作系统ID
	 * @return  int list[].image[].image_group_id - 操作系统分类ID
	 * @return  string list[].image[].name - 操作系统名称
	 * @return  int list[].image[].charge - 是否收费(0=否,1=是)
	 * @return  string list[].image[].price - 价格
	 */
	public function imageList(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}
		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'os';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->imageList();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取商品配置所有周期价格
	 * @desc 获取商品配置所有周期价格
	 * @url /console/v1/product/:id/rewhmcs_dcim/duration
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @param   object configoptions - 配置项
     * @return  string [].name - 周期名称
     * @return  string [].price - 周期总价
     * @return  float  [].discount - 折扣(0=没有折扣)
	 */
	public function getAllDurationPrice(){
		$param = request()->param();

		try{
			$product = ProductModel::find($param['id']);

			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			unset($param['id']);
			$param['productid'] = $RouteLogic->upstream_product_id;
			$result = $RouteLogic->curl('product_duration', $param, 'POST');

			if($result['status'] == 200){
				if($product['pay_type']=='onetime'){
					// 计算价格倍率
					foreach($result['data']['price'] as $k=>$v){
						if($k=='monthly'){
							$result['data']['price']['onetime'] = ['name' => 'onetime', 'price' => $RouteLogic->profit_type==1?bcadd($v,$RouteLogic->getProfitPercent()*100):bcmul($v, $RouteLogic->price_multiple), 'discount' => 0];
						}
						unset($result['data']['price'][$k]);
					}
				}else if($product['pay_type']=='free'){
					$result['data']['price'] = ['free' => ['name' => 'free', 'price' => 0, 'discount' => 0]];
				}else{
					// 计算价格倍率
					foreach($result['data']['price'] as $k=>$v){
						$result['data']['price'][$k] = ['name' => $k, 'price' => $RouteLogic->profit_type==1?bcadd($v,$RouteLogic->getProfitPercent()*100):bcmul($v, $RouteLogic->price_multiple), 'discount' => 0];
					}
				}

				$result['data']['price'] = array_values($result['data']['price']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->getAllDurationPrice();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				return ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	* 时间 2023-02-09
	* @title 产品列表
	* @desc 产品列表
	* @url /console/v1/rewhmcs_dcim
	* @method  GET
	* @author hh
	* @version v1
    * @param   int page 1 页数
    * @param   int limit - 每页条数
    * @param   string orderby - 排序(id,due_time,status)
    * @param   string sort - 升/降序
    * @param   string keywords - 关键字搜索,搜索套餐名称/主机名/IP
    * @param   string param.status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
    * @param   int param.m - 菜单ID
    * @return  array data.list - 列表数据
    * @return  int data.list[].id - 产品ID
    * @return  string data.list[].name - 产品标识
    * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
    * @return  int data.list[].due_time - 到期时间
    * @return  int data.list[].active_time - 开通时间
    * @return  string data.list[].product_name - 商品名称
	*/
	public function list(){
		$param = request()->param();

		$result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => [],
                'count' => [],
            ]
        ];

        $clientId = get_client_id();
        if(empty($clientId)){
            return json($result);
        }

        $where = [];
        if(isset($param['m']) && !empty($param['m'])){
        	// 菜单,菜单里面必须是下游商品
            $MenuModel = MenuModel::where('menu_type', 'res_module')
            			->where('module', 'whmcs_dcim')
                        ->where('id', $param['m'])
                        ->find();
            if(!empty($MenuModel) && !empty($MenuModel['product_id'])){
                $MenuModel['product_id'] = json_decode($MenuModel['product_id'], true);
                if(!empty($MenuModel['product_id'])){
                	$upstreamProduct = UpstreamProductModel::whereIn('product_id', $MenuModel['product_id'])->where('res_module', 'whmcs_dcim')->find();
                	if(!empty($upstreamProduct)){
                		$where[] = ['h.product_id', 'IN', $MenuModel['product_id'] ];
                	}
                }
            }
        }else{
        	//return json($result);
        }

        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','due_time','status']) ? $param['orderby'] : 'id';
        $param['orderby'] = 'h.'.$param['orderby'];

        $where[] = ['h.client_id', '=', $clientId];
        $where[] = ['h.status', '<>', 'Cancelled'];
        if(isset($param['status']) && !empty($param['status'])){
            if($param['status'] == 'Pending'){
                $where[] = ['h.status', 'IN', ['Pending','Failed']];
            }else if(in_array($param['status'], ['Unpaid','Active','Suspended','Deleted'])){
                $where[] = ['h.status', '=', $param['status']];
            }
        }
        if(isset($param['keywords']) && $param['keywords'] !== ''){
        	$where[] = ['h.name', 'LIKE', '%'.$param['keywords'].'%'];
        }

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'IN', $hostId];
        }

        $count = HostModel::alias('h')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="whmcs_dcim"')
            ->where($where)
            ->count();

        $host = HostModel::alias('h')
            ->field('h.id,h.name,h.status,h.active_time,h.due_time,p.name product_name,h.client_notes')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="whmcs_dcim"')
            ->where($where)
            ->withAttr('status', function($val){
                return $val == 'Failed' ? 'Pending' : $val;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();

        $result['data']['list']  = $host;
        $result['data']['count'] = $count;
        return json($result);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取实例详情
	 * @desc 获取实例详情
	 * @url /console/v1/rewhmcs_dcim/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int $id - 产品ID
     * @return  string ip - IP地址
     * @return  string power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int model_config.id - 型号配置ID
     * @return  string model_config.name - 型号配置名称
     * @return  string model_config.cpu - 处理器
     * @return  string model_config.cpu_param - 处理器参数
     * @return  string model_config.memory - 内存
     * @return  string model_config.disk - 硬盘
     * @return  int line.id - 线路
     * @return  string line.name - 线路名称
     * @return  string line.bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int bw - 带宽(0表示没有)
     * @return  int peak_defence - 防御峰值(0表示没有)
     * @return  string username - 用户名
     * @return  string password - 密码
     * @return  int data_center.id - 数据中心ID
     * @return  string data_center.city - 城市
     * @return  string data_center.area - 区域
     * @return  int image.id - 镜像ID
     * @return  string image.name - 镜像名称
     * @return  string image.image_group_name - 镜像分类
     * @return  string image.icon - 图标
	 */
	public function detail(){
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'detail';
			$res = $RouteLogic->curl('service_customFunctions', $param, 'POST');
			if($res['status'] == 200){
				unset($res['status']);
				$result = ['status' => 200, 'msg' => lang_plugins('success_message'), 'data' => $res];
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->detail();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}


	/**
	 * 时间 2022-06-22
	 * @title 开机
	 * @desc 开机
	 * @url /console/v1/rewhmcs_dcim/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function on(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'on';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->on();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 关机
	 * @desc 关机
	 * @url /console/v1/rewhmcs_dcim/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function off(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'off';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->off();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-22
	 * @title 重启
	 * @desc 重启
	 * @url /console/v1/rewhmcs_dcim/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function reboot(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'reboot';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->reboot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /console/v1/rewhmcs_dcim/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'vnc';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
			if($result['status'] == 200){
				$result = [
					'status' => 200,
					'msg'    => lang_plugins('success_message'),
					'data'	 => [],
				];

	            if(strpos($res['host'], 'https://') !== false){
	                $link_url = str_replace('https://', 'wss://', $res['host']);
	            }else{
	                $link_url = str_replace('http://', 'ws://', $res['host']);
	            }
	            // vnc不能包含管理员路径
	            // $link_url = rtrim($link_url, '/');
	            // if(substr_count($link_url, '/') > 2){
	            //     $link_url = substr($link_url, 0, strrpos($link_url, '/'));
	            // }
	            $link_url .= '/websockify_'.$res['house'].'?token='.$res['token'];

	            // 获取的东西放入缓存
	            $cache = [
	            	'vnc_url' => $link_url,
	            	'vnc_pass'=> $res['vnc_pass'],
	            	'password'=> aes_password_decode($res['pass']),
	            ];

	            // 生成一个临时token
	            $token = md5(rand_str(16));
	            $cache['token'] = $token;

				Cache::set('rewhmcs_dcim_vnc_'.$param['id'], $cache, 30*60);
				if(!isset($param['more']) || $param['more'] != 1){
					// 不获取更多信息
					$result['data'] = [];
				}
				// 转到当前res模块
				$result['data']['url'] = request()->domain().'/console/v1/rewhmcs_dcim/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->vnc();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /console/v1/rewhmcs_dcim/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function vncPage(){
		$param = request()->param();

		$cache = Cache::get('rewhmcs_dcim_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('res_whmcs_dcim_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/whmcs_dcim/view/vnc_page.html');
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取实例状态
	 * @desc 获取实例状态
	 * @url /console/v1/rewhmcs_dcim/:id/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
	 * @return  string data.desc - 实例状态描述
	 */
	public function status(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'status';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
			if($result['status'] == 200){
				$result['data'] = ['status' => $result['power'], 'desc' => $result['msg']];
				$result['msg'] = lang_plugins('success_message');
				unset($result['power']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->status();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-24
	 * @title 重置密码
	 * @desc 重置密码
	 * @url /console/v1/rewhmcs_dcim/:id/reset_password
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   string password - 新密码 require
	 */
	public function resetPassword(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'crack';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->resetPassword();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-24
	 * @title 救援模式
	 * @desc 救援模式
	 * @url /console/v1/rewhmcs_dcim/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int system - 指定救援系统类型(1=linux,2=windows) require
	 */
	public function rescue(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'rescue';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->rescue();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-30
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /console/v1/rewhmcs_dcim/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int mos - 镜像ID require
	 * @param   string password - 密码 require
	 * @param   int port - 端口 require
	 */
	public function reinstall(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'reinstall';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_dcim_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->reinstall();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}


	/**
	 * 时间 2022-06-27
	 * @title 获取图表数据
	 * @desc 获取图表数据
	 * @url /console/v1/rewhmcs_dcim/:id/chart
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int start_time - 开始秒级时间
	 * @param   int end_time - 结束秒级时间
	 */
	public function chart(){
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'getTraffic';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');

		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->chart();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-30
	 * @title 获取网络流量
	 * @desc 获取网络流量
	 * @url /console/v1/rewhmcs_dcim/:id/flow
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  string total -总流量
	 * @return  string used -已用流量
	 * @return  string leave - 剩余流量
	 * @return  string reset_flow_date - 流量归零时间
	 */
	public function flowDetail(){
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['method'] = 'trafficUsage';
			$result = $RouteLogic->curl('service_customFunctions', $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->flowDetail();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 日志
	 * @desc 日志
	 * @url /console/v1/rewhmcs_dcim/:id/log
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param int id - 产品ID
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,description,create_time,ip
     * @param string sort - 升/降序 asc,desc
     * @return array list - 系统日志
     * @return int list[].id - 系统日志ID
     * @return string list[].description - 描述
     * @return string list[].create_time - 时间
     * @return int list[].ip - IP
     * @return int count - 系统日志总数
	 */
	public function log(){
		$param = request()->param();

		$SystemLogModel = new SystemLogModel();
	 	$result = $SystemLogModel->systemLogList($param);
	 	return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 计算产品配置升级价格
	 * @desc 计算产品配置升级价格
	 * @url /console/v1/rewhmcs_dcim/:id/common_config
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   object configoptions - 配置 require
     * @return  string price - 价格
     * @return  string description - 生成的订单描述
	 */
	public function calCommonConfigPrice(){
		$param = request()->param();

        $hostId = $param['id'];
 		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['configoptions'] = $param['configoptions'];
			$result = $RouteLogic->curl('host_changePackageAmount', $param, 'POST');
			if($result['status'] == 200){
                if ($RouteLogic->profit_type==1){
                    $profit = bcadd(0, $RouteLogic->getProfitPercent()*100);

                    $result['data']['price'] = bcadd($result['data']['price'], $profit);
                    $result['data']['price_difference'] = bcadd($result['data']['price_difference'], $RouteLogic->getProfitPercent());
                    $result['data']['renew_price_difference'] = bcadd($result['data']['renew_price_difference'], $RouteLogic->getProfitPercent());
                }else{
                    $profit = bcmul($result['data']['price'], $RouteLogic->getProfitPercent());

                    $result['data']['price'] = bcadd($result['data']['price'], $profit);
                    $result['data']['price_difference'] = bcmul($result['data']['price_difference'], $RouteLogic->getPriceMultiple());
                    $result['data']['renew_price_difference'] = bcmul($result['data']['renew_price_difference'], $RouteLogic->getPriceMultiple());
                }
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->calCommonConfigPrice();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 生成产品配置升级订单
	 * @desc 生成产品配置升级订单
	 * @url /console/v1/rewhmcs_dcim/:id/common_config/order
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
     * @param   object configoptions - 配置 require
	 * @return  string data.id - 订单ID
	 */
	public function createCommonConfigOrder(){
		$param = request()->param();

        $hostId = $param['id'];
 		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['configoptions'] = $param['configoptions'];
			$result = $RouteLogic->curl('host_changePackageAmount', $param, 'POST');
			if($result['status'] == 200){
                if ($RouteLogic->profit_type==1){
                    $profit = bcadd(0, $RouteLogic->getProfitPercent()*100);

                    $result['data']['price'] = bcadd($result['data']['price'], $profit);
                    $result['data']['price_difference'] = bcadd($result['data']['price_difference'], $RouteLogic->getProfitPercent());
                    $result['data']['renew_price_difference'] = bcadd($result['data']['renew_price_difference'], $RouteLogic->getProfitPercent());
                }else{
                    $profit = bcmul($result['data']['price'], $RouteLogic->getProfitPercent());

                    $result['data']['price'] = bcadd($result['data']['price'], $profit);
                    $result['data']['price_difference'] = bcmul($result['data']['price_difference'], $RouteLogic->getPriceMultiple());
                    $result['data']['renew_price_difference'] = bcmul($result['data']['renew_price_difference'], $RouteLogic->getPriceMultiple());
                }
				$OrderModel = new OrderModel();

		        $data = [
		            'host_id'     => $hostId,
		            'client_id'   => get_client_id(),
		            'type'        => 'upgrade_config',
		            'amount'      => $result['data']['price'],
		            'description' => $result['data']['description'],
		            'price_difference' => $result['data']['price_difference'],
		            'renew_price_difference' => $result['data']['renew_price_difference'],
		            'upgrade_refund' => 0,
		            'config_options' => [
		                'type'       => 'upgrade_common_config',
		                'param'		 => $param,
		            ],
		            'customfield' => $param['customfield'] ?? [],
		        ];
				$result = $OrderModel->createOrder($data);
				if($result['status'] == 200){
					UpstreamOrderModel::create([
						'supplier_id' 	=> $RouteLogic->supplier_id,
						'order_id' 		=> $result['data']['id'],
						'host_id' 		=> $hostId,
						'amount' 		=> $data['amount'],
						'profit' 		=> $profit,
						'create_time' 	=> time(),
					]);
				}
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->createCommonConfigOrder();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-03-01
	 * @title 验证下单
	 * @desc 验证下单
	 * @url /console/v1/product/:id/rewhmcs_dcim/validate_settle
	 * @method  POST
	 * @author hh
	 * @version v1
	 */
	public function validateSettle(){
		$param = request()->param();

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByProduct($param['id']);

			unset($param['id']);
			$result = $RouteLogic->curl( sprintf('console/v1/product/%d/rewhmcs_dcim/validate_settle', $RouteLogic->upstream_product_id), $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_dcim\controller\home\CloudController')){
					return (new \server\whmcs_dcim\controller\home\CloudController())->validateSettle();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_dcim_act_exception')];
			}
		}
		return json($result);
	}

}
