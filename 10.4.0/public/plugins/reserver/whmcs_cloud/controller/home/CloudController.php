<?php
namespace reserver\whmcs_cloud\controller\home;

use think\facade\Cache;
use think\facade\View;
use reserver\whmcs_cloud\validate\HostValidate;
use reserver\whmcs_cloud\logic\RouteLogic;
use reserver\whmcs_cloud\model\SystemLogModel;
use app\common\model\UpstreamHostModel;
use app\common\model\OrderModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;
use app\common\model\SelfDefinedFieldModel;

/**
 * @title 魔方云代理(WHMCS)-前台
 * @desc 魔方云代理(WHMCS)-前台
 * @use reserver\whmcs_cloud\controller\home\CloudController
 */
class CloudController{

	/**
	 * 时间 2023-02-06
	 * @title 获取订购页面配置
	 * @desc 获取订购页面配置
	 * @url /console/v1/product/:id/rewhmcs_cloud/order_page
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  array cycle - 周期
	 * @return  object pricing - 商品价格
	 * @return  string pricing.setupfee - 一次性初装费
	 * @return  string pricing.msetupfee - 月初装费
	 * @return  string pricing.qsetupfee - 季初装费
	 * @return  string pricing.ssetupfee - 半年初装费
	 * @return  string pricing.asetupfee - 年初装费
	 * @return  string pricing.bsetupfee - 两年初装费
	 * @return  string pricing.tsetupfee - 三年初装费
	 * @return  string pricing.onetime - 一次性
	 * @return  string pricing.monthly - 月付
	 * @return  string pricing.quarterly - 季付
	 * @return  string pricing.semiannually - 半年付
	 * @return  string pricing.annually - 年付
	 * @return  string pricing.biennially - 两年付
	 * @return  string pricing.triennially - 三年付
	 * @return  array configoption - 配置
	 * @return  int configoption[].id - 配置项ID
	 * @return  string configoption[].optionname - 配置项名称
	 * @return  string configoption[].firstname - 配置项真实值
	 * @return  string configoption[].lastname - 配置项描述
	 * @return  string configoption[].optiontype - 配置项类型1下拉2单选3是否4数量
	 * @return  int configoption[].qtyminimum - 配置项最小值,类型为数量时才有
	 * @return  int configoption[].qtymaximum - 配置项最大值,类型为数量时才有
	 * @return  array configoption[].options - 配置子项
	 * @return  int configoption[].options[].id - 配置子项ID
	 * @return  string configoption[].options[].optionname - 配置子项名称
	 * @return  string configoption[].options[].firstname - 配置子项真实值
	 * @return  string configoption[].options[].lastname - 配置子项描述
	 * @return  int configoption[].options[].currency - 货币
	 * @return  string configoption[].options[].setupfee - 一次性初装费
	 * @return  string configoption[].options[].msetupfee - 月初装费
	 * @return  string configoption[].options[].qsetupfee - 季初装费
	 * @return  string configoption[].options[].ssetupfee - 半年初装费
	 * @return  string configoption[].options[].asetupfee - 年初装费
	 * @return  string configoption[].options[].bsetupfee - 两年初装费
	 * @return  string configoption[].options[].tsetupfee - 三年初装费
	 * @return  string configoption[].options[].onetime - 一次性
	 * @return  string configoption[].options[].monthly - 月付
	 * @return  string configoption[].options[].quarterly - 季付
	 * @return  string configoption[].options[].semiannually - 半年付
	 * @return  string configoption[].options[].annually - 年付
	 * @return  string configoption[].options[].biennially - 两年付
	 * @return  string configoption[].options[].triennially - 三年付
	 * @return  array customfield - 自定义字段
	 * @return  int customfield[].id - 自定义字段ID
	 * @return  string customfield[].fieldname - 自定义字段名称
	 * @return  string customfield[].fieldtype - 自定义字段类型text,textarea,tickbox,link,password,dropdown
	 * @return  string customfield[].description - 自定义字段描述
	 * @return  string customfield[].fieldoptions - 自定义字段选项,以,分隔
	 * @return  string customfield[].regexpr - 自定义字段验证规则
	 * @return  string customfield[].required - 是否必填on是
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
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->orderPage();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取升降级配置
	 * @desc 获取升降级配置
	 * @url /console/v1/product/:id/rewhmcs_cloud/upgrade_page
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 商品ID require
	 * @return  array configoption - 配置
	 * @return  int configoption[].id - 配置项ID
	 * @return  string configoption[].optionname - 配置项名称
	 * @return  string configoption[].firstname - 配置项真实值
	 * @return  string configoption[].lastname - 配置项描述
	 * @return  string configoption[].optiontype - 配置项类型1下拉2单选3是否4数量
	 * @return  int configoption[].qtyminimum - 配置项最小值,类型为数量时才有
	 * @return  int configoption[].qtymaximum - 配置项最大值,类型为数量时才有
	 * @return  array configoption[].options - 配置子项
	 * @return  int configoption[].options[].id - 配置子项ID
	 * @return  string configoption[].options[].optionname - 配置子项名称
	 * @return  string configoption[].options[].firstname - 配置子项真实值
	 * @return  string configoption[].options[].lastname - 配置子项描述
	 * @return  int configoption[].options[].currency - 货币
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
					if(!in_array($value['firstname'], ['backup_num', 'snap_num', 'in_bw', 'system_disk_io_limit', 'data_disk_io_limit', 'cpu', 'memory', 'bw', 'data_disk_size', 'ip_num', 'flow_limit', 'ipv6_num', 'data1_disk_size', 'data2_disk_size', 'data3_disk_size', 'data4_disk_size'])){
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
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->orderPage();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 获取操作系统列表
	 * @desc 获取操作系统列表
	 * @url /console/v1/rewhmcs_cloud/:id/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int list[].id - 操作系统配置ID
	 * @return  string list[].osid - 操作系统ID
	 * @return  string list[].name - 操作系统名称
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
			$result = $RouteLogic->curl('service_clientArea&ac=cloudos_layer', ['hosting_id' => $RouteLogic->upstream_host_id], 'POST');
			if($result['status'] == 200){
				$result['data']['list'] = $result['data']['cloud_os'];
				unset($result['data']['cloud_os'],$result['data']['cloud_os_group']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->imageList();
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
	 * 时间 2023-02-06
	 * @title 获取商品配置所有周期价格
	 * @desc 获取商品配置所有周期价格
	 * @url /console/v1/product/:id/rewhmcs_cloud/duration
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
	* @url /console/v1/rewhmcs_cloud
	* @method  GET
	* @author hh
	* @version v1
    * @param   int page 1 页数
    * @param   int limit - 每页条数
    * @param   string orderby - 排序(id,due_time,status)
    * @param   string sort - 升/降序
    * @param   string keywords - 关键字搜索主机名
    * @param   string param.status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
    * @param   int param.m - 菜单ID
    * @return  array list - 列表数据
    * @return  int list[].id - 产品ID
    * @return  int list[].product_id - 商品ID
    * @return  string list[].name - 产品标识
    * @return  string list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
    * @return  int list[].due_time - 到期时间
    * @return  int list[].active_time - 开通时间
    * @return  string list[].product_name - 商品名称
    * @return  string list[].client_notes - 用户备注
    * @return  object list[].self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容)
    * @return  int count - 总条数
    * @return  array self_defined_field - 自定义字段
    * @return  int self_defined_field[].id - 自定义字段ID
	* @return  string self_defined_field[].field_name - 自定义字段名称
	* @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
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
            			->where('module', 'whmcs_cloud')
                        ->where('id', $param['m'])
                        ->find();
            if(!empty($MenuModel) && !empty($MenuModel['product_id'])){
                $MenuModel['product_id'] = json_decode($MenuModel['product_id'], true);
                if(!empty($MenuModel['product_id'])){
                	$upstreamProduct = UpstreamProductModel::whereIn('product_id', $MenuModel['product_id'])->where('res_module', 'whmcs_cloud')->find();
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
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="whmcs_cloud"')
            ->where($where)
            ->count();

        $host = HostModel::alias('h')
            ->field('h.id,h.product_id,h.name,h.status,h.active_time,h.due_time,p.name product_name,h.client_notes')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="whmcs_cloud"')
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
        if(!empty($host) && class_exists('app\common\model\SelfDefinedFieldModel')){
            $hostId = array_column($host, 'id');
            $productId = array_column($host, 'product_id');

            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $selfDefinedField = $SelfDefinedFieldModel->getHostListSelfDefinedFieldValue([
                'product_id' => $productId,
                'host_id'    => $hostId,
            ]);
        }
        foreach($host as $k=>$v){
            $host[$k]['self_defined_field'] = $selfDefinedField['self_defined_field_value'][ $v['id'] ] ?? (object)[];
        }

        $result['data']['list']  = $host;
        $result['data']['count'] = $count;
        $result['data']['self_defined_field'] = $selfDefinedField['self_defined_field'] ?? [];
        return json($result);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取实例详情
	 * @desc 获取实例详情
	 * @url /console/v1/rewhmcs_cloud/:id
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int $id - 产品ID
	 * @return  object host_data - 产品数据
	 * @return  string host_data.domain - 产品标识
	 * @return  string host_data.dedicatedip - 独立IP
	 * @return  string host_data.username - 用户名
	 * @return  string host_data.password - 密码
	 * @return  string host_data.productname - 商品名称
	 * @return  int host_data.bwusage - 已用流量
	 * @return  int host_data.bwlimit - 流量限制
	 * @return  array host_data.assignedips - 附加IP
	 * @return  string host_data.type - 类型
	 * @return  int host_data.reset_flow_day - 流量重置时间
	 * @return  int host_data.port - 端口
	 * @return  int host_data.rescue - 救援系统0否1是
	 * @return  int host_data.image_group_id - 镜像分组ID
	 * @return  string host_data.panel_pass - 面板密码
	 * @return  int host_data.configoptionsupgrade - 支持升降级0否1是
	 * @return  array config_options - 产品配置
	 * @return  int config_options[].option_type - 配置类型
	 * @return  string config_options[].sub_name - 单位名称
	 * @return  string config_options[].name - 配置名称
	 * @return  object configoptions - 当前配置
	 * @return  object configoptions.area.name - 配置area对应的名称
	 * @return  object configoptions.area.value - 配置area对应的值
	 * @return  object oldconfigoptions - 当前配置ID对应的值,例如{"1":1,"2":2}
	 * @return  object customfields - 自定义字段
	 */
	public function detail(){
		$param = request()->param();

		$HostValidate = new HostValidate();
		if (!$HostValidate->scene('auth')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->setTimeout(100);
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=product', ['hosting_id' => $RouteLogic->upstream_host_id], 'POST');
			if($result['status']==200){

			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->detail();
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
	 * 时间 2022-06-22
	 * @title 开机
	 * @desc 开机
	 * @url /console/v1/rewhmcs_cloud/:id/on
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function on(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'on'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_boot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_boot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->on();
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
	 * 时间 2022-06-22
	 * @title 关机
	 * @desc 关机
	 * @url /console/v1/rewhmcs_cloud/:id/off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function off(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'off'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->off();
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
	 * 时间 2022-06-22
	 * @title 重启
	 * @desc 重启
	 * @url /console/v1/rewhmcs_cloud/:id/reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function reboot(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'reboot'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->reboot();
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
	 * 时间 2022-06-22
	 * @title 强制关机
	 * @desc 强制关机
	 * @url /console/v1/rewhmcs_cloud/:id/hard_off
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function hardOff(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'hard_off'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_off_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_off_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->hardOff();
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
	 * 时间 2022-06-22
	 * @title 强制重启
	 * @desc 强制重启
	 * @url /console/v1/rewhmcs_cloud/:id/hard_reboot
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function hardReboot(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'hard_reboot'], 'POST');
			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_reboot_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_hard_reboot_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->hardReboot();
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
	 * 时间 2022-06-29
	 * @title 获取控制台地址
	 * @desc 获取控制台地址
	 * @url /console/v1/rewhmcs_cloud/:id/vnc
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @return  string data.url - 控制台地址
	 */
	public function vnc(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default', ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'vnc'], 'POST');
			if($result['status'] == 200){
				$cache = $result['data'];
				unset($cache['url']);

				Cache::set('idcsmart_cloud_vnc_'.$param['id'], $cache, 30*60);
				if(!isset($param['more']) || $param['more'] != 1){
					// 不获取更多信息
					$result['data'] = [];
				}
				// 转到当前res模块
				$result['data']['url'] = request()->domain().'/console/v1/rewhmcs_cloud/'.$param['id'].'/vnc?tmp_token='.$cache['token'];
			}

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_vnc_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_vnc_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $param['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->vnc();
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
	 * 时间 2022-07-01
	 * @title 控制台页面
	 * @desc 控制台页面
	 * @url /console/v1/rewhmcs_cloud/:id/vnc
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function vncPage(){
		$param = request()->param();

		$cache = Cache::get('idcsmart_cloud_vnc_'.$param['id']);
		if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
			View::assign($cache);
		}else{
			return lang_plugins('res_whmcs_cloud_vnc_token_expired_please_reopen');
		}
		return View::fetch(WEB_ROOT . 'plugins/reserver/whmcs_cloud/view/vnc_page.html');
	}

	/**
	 * 时间 2022-06-24
	 * @title 获取实例状态
	 * @desc 获取实例状态
	 * @url /console/v1/rewhmcs_cloud/:id/status
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			$result = $RouteLogic->curl('service_clientArea&ac=default&requestTime='.time(), ['hosting_id' => $RouteLogic->upstream_host_id, 'func' => 'status'], 'POST');

		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->status();
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
	 * 时间 2022-06-24
	 * @title 重置密码
	 * @desc 重置密码
	 * @url /console/v1/rewhmcs_cloud/:id/reset_password
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
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);

			unset($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['func'] = 'crack_pass';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reset_password_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reset_password_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->resetPassword();
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
	 * 时间 2022-06-30
	 * @title 救援系统
 	 * @desc 救援系统
	 * @url /console/v1/rewhmcs_cloud/:id/rescue
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int type - 指定救援系统类型(1=windows,2=linux) require
     * @param   int temp_pass - 临时密码 require
	 */
	public function rescue(){
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
			$param['func'] = 'rescue';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->rescue();
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
	 * 时间 2022-06-30
	 * @title 退出救援系统
 	 * @desc 退出救援系统
	 * @url /console/v1/rewhmcs_cloud/:id/rescue/exit
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 */
	public function exitRescue(){
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
			$param['func'] = 'exit_rescue';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_exit_rescue_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_exit_rescue_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->exitRescue();
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
	 * 时间 2022-06-30
	 * @title 重装系统
 	 * @desc 重装系统
	 * @url /console/v1/rewhmcs_cloud/:id/reinstall
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int os - 镜像ID require
	 * @param   string password - 密码 require
	 */
	public function reinstall(){
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
			$param['func'] = 'reinstall';
			$result = $RouteLogic->curl('service_clientArea&ac=default', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reinstall_success', [
					'{hostname}' => $HostModel['name'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_reinstall_fail', [
					'{hostname}' => $HostModel['name'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->reinstall();
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
	 * 时间 2022-06-27
	 * @title 获取图表数据
	 * @desc 获取图表数据
	 * @url /console/v1/rewhmcs_cloud/:id/chart
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int start_time - 开始秒级时间
	 * @param   string type - 图表类型(cpu=CPU,memory=内存,disk_io=硬盘IO,bw=带宽) require
	 * @return  array data.list - 图表数据
	 * @return  int data.list[].time - 时间(秒级时间戳)
	 * @return  float data.list[].value - CPU使用率
	 * @return  int data.list[].total - 总内存(单位:B)
	 * @return  int data.list[].used - 内存使用量(单位:B)
	 * @return  float data.list[].read_bytes - 读取速度(B/s)
	 * @return  float data.list[].write_bytes - 写入速度(B/s)
	 * @return  float data.list[].read_iops - 读取IOPS
	 * @return  float data.list[].write_iops - 写入IOPS
	 * @return  float data.list[].in_bw - 进带宽(bps)
	 * @return  float data.list[].out_bw - 出带宽(bps)
	 */
	public function chart(){
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
			if(!empty($param['type'])){
				$result = $RouteLogic->curl('service_clientArea&ac=chart&type='.$param['type'].'&hostId='.$param['hosting_id'], $param, 'POST');
			}else{
				$result = $RouteLogic->curl('service_clientArea&ac=chart', $param, 'POST');
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->chart();
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
	 * 时间 2022-06-30
	 * @title 获取网络流量
	 * @desc 获取网络流量
	 * @url /console/v1/rewhmcs_cloud/:id/flow
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

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$result = $RouteLogic->curl('service_clientArea&ac=trafficusage', $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->flowDetail();
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
	 * 时间 2022-06-30
	 * @title 获取网络流量总计
	 * @desc 获取网络流量总计
	 * @url /console/v1/rewhmcs_cloud/:id/flow_total
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @return  int bwlimit -总流量(MB)
	 * @return  int bwusage -已用流量(MB)
	 */
	public function flowTotal(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$result = $RouteLogic->curl('service_clientArea&ac=traffictotal', $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->flowDetail();
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
	 * 时间 2022-06-27
	 * @title 快照列表
	 * @desc 快照列表
	 * @url /console/v1/rewhmcs_cloud/:id/snapshot
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int page - 页数
	 * @param   int limit - 每页条数
	 * @return  array data.list -  列表数据
	 * @return  int data.list[].id - 快照ID
	 * @return  string data.list[].name - 快照名称
	 * @return  int data.list[].create_time - 创建时间
	 * @return  string data.list[].remarks - 备注
	 * @return  int data.count - 总条数
	 */
	public function snapshot(){
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
			$param['func'] = 'getSnapBackup';
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');
			if($result['status']==200){
				$result['data']['snapshots'] = is_array($result['data']['snapshots'])  ? $result['data']['snapshots'] :  [];
				foreach ($result['data']['snapshots'] as $key => $value) {
					if($value['type']!='snap'){
						unset($result['data']['snapshots'][$key]);
					}
				}
				$result['data']['snapshots'] = array_values($result['data']['snapshots']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->snapshot();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-11
	 * @title 创建快照
	 * @desc 创建快照
	 * @url /console/v1/rewhmcs_cloud/:id/snapshot
	 * @method  POST
	 * @author theworld
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int remarks - 备注 require
	 * @param   int disk_id - 磁盘ID require
	 */
	public function snapshotCreate(){
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
			$param['func'] = 'createSnap';
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_create_snap_success', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $param['remarks'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_create_snap_fail', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $param['remarks'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->snapshotCreate();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 快照还原
	 * @desc 快照还原
	 * @url /console/v1/rewhmcs_cloud/:id/snapshot/restore
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int snapshot_id - 快照ID require
	 */
	public function snapshotRestore(){
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
			$param['func'] = 'restore';
			$param['snapshots_id'] = $param['snapshot_id'];
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_snap_restore_success', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['snapshot_id'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_snap_restore_fail', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['snapshot_id'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->snapshotRestore();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除快照
	 * @desc 删除快照
	 * @url /console/v1/rewhmcs_cloud/:id/snapshot/:snapshot_id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int snapshot_id - 快照ID require
	 */
	public function snapshotDelete(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['func'] = 'deleteSnap';
			$param['snapshots_id'] = $param['snapshot_id'];
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_delete_snap_success', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['snapshot_id'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_delete_snap_fail', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['snapshot_id'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->snapshotDelete();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 备份列表
	 * @desc 备份列表
	 * @url /console/v1/rewhmcs_cloud/:id/backup
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int page - 页数
	 * @param   int limit - 每页条数
	 * @return  array data.snapshots -  列表数据
	 * @return  int data.snapshots[].id - 备份ID
	 * @return  string data.snapshots[].name - 备份名称
	 * @return  int data.snapshots[].create_time - 创建时间
	 * @return  string data.snapshots[].remarks - 备注
	 * @return  int data.count - 总条数
	 */
	public function backup(){
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
			$param['func'] = 'getSnapBackup';
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');
			if($result['status']==200){
				$result['data']['snapshots'] = is_array($result['data']['snapshots'])  ? $result['data']['snapshots'] :  [];
				foreach ($result['data']['snapshots'] as $key => $value) {
					if($value['type']!='backup'){
						unset($result['data']['snapshots'][$key]);
					}
				}
				$result['data']['snapshots'] = array_values($result['data']['snapshots']);
			}
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->backup();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-07-11
	 * @title 创建备份
	 * @desc 创建备份
	 * @url /console/v1/rewhmcs_cloud/:id/backup
	 * @method  POST
	 * @author theworld
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int remarks - 备注 require
	 * @param   int disk_id - 磁盘ID require
	 */
	public function backupCreate(){
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
			$param['func'] = 'createBackup';
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_create_backup_success', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $param['remarks'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_create_backup_fail', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $param['remarks'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->backupCreate();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 备份还原
	 * @desc 备份还原
	 * @url /console/v1/rewhmcs_cloud/:id/backup/restore
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int backup_id - 备份ID require
	 */
	public function backupRestore(){
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
			$param['func'] = 'restoreBackup';
			$param['snapshots_id'] = $param['backup_id'];
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_start_backup_restore_success', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['backup_id'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_start_backup_restore_fail', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['backup_id'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->backupRestore();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2022-06-27
	 * @title 删除备份
	 * @desc 删除备份
	 * @url /console/v1/rewhmcs_cloud/:id/backup/:backup_id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 产品ID require
	 * @param   int backup_id - 备份ID require
	 */
	public function backupDelete(){
		$param = request()->param();

		$HostModel = HostModel::find($param['id']);
		if(empty($HostModel) || $HostModel['client_id'] != get_client_id() ){
			return json(['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_host_not_found')]);
		}

		try{
			$RouteLogic = new RouteLogic();
			$RouteLogic->routeByHost($param['id']);
			$param['hosting_id'] = $RouteLogic->upstream_host_id;
			$param['func'] = 'deleteBackup';
			$param['snapshots_id'] = $param['backup_id'];
			$result = $RouteLogic->curl('service_clientArea&ac=snapshots', $param, 'POST');

			if($result['status'] == 200){
				$description = lang_plugins('res_whmcs_cloud_log_host_delete_backup_success', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['backup_id'],
				]);
			}else{
				$description = lang_plugins('res_whmcs_cloud_log_host_delete_backup_fail', [
					'{hostname}' => $HostModel['name'],
					'{name}'	 => $result['data']['name'] ?? 'ID-'.(int)$param['backup_id'],
				]);
			}
			active_log($description, 'host', $HostModel['id']);
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->backupDelete();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}


	/**
	 * 时间 2022-07-01
	 * @title 日志
	 * @desc 日志
	 * @url /console/v1/rewhmcs_cloud/:id/log
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
	 * @url /console/v1/rewhmcs_cloud/:id/common_config
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
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->calCommonConfigPrice();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-02-13
	 * @title 生成产品配置升级订单
	 * @desc 生成产品配置升级订单
	 * @url /console/v1/rewhmcs_cloud/:id/common_config/order
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
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->createCommonConfigOrder();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}

	/**
	 * 时间 2023-03-01
	 * @title 验证下单
	 * @desc 验证下单
	 * @url /console/v1/product/:id/rewhmcs_cloud/validate_settle
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
			$param['productid'] = $RouteLogic->upstream_product_id;
			$result = $RouteLogic->curl('product_validate', $param, 'POST');
		}catch(\Exception $e){
			if(!$RouteLogic->isUpstream){
				if(class_exists('\server\whmcs_cloud\controller\home\CloudController')){
					return (new \server\whmcs_cloud\controller\home\CloudController())->validateSettle();
				}else{
					$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_error_act')];
				}
			}else{
				$result = ['status'=>400, 'msg'=>lang_plugins('res_whmcs_cloud_act_exception')];
			}
		}
		return json($result);
	}


}
