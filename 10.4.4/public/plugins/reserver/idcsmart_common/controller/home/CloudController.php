<?php
namespace reserver\idcsmart_common\controller\home;

use app\admin\model\PluginModel;
use reserver\idcsmart_common\logic\RouteLogic;
use app\common\model\OrderModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;
use app\common\model\SelfDefinedFieldModel;

/**
 * @title 通用代理(自定义配置)-前台
 * @desc 通用代理(自定义配置)-前台
 * @use reserver\idcsmart_common\controller\home\IdcsmartCommonProductController
 */
class CloudController
{
	/**
	* 时间 2024-04-24
	* @title 产品列表
	* @desc 产品列表
	* @url /console/v1/reidcsmart_common/host
	* @method  GET
	* @author hh
	* @version v1
    * @param   int page 1 页数
    * @param   int limit - 每页条数
    * @param   string orderby - 排序(id,due_time,status)
    * @param   string sort - 升/降序
    * @param   string keywords - 关键字搜索主机名
    * @param   string status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
    * @param   string tab - 状态using使用中expiring即将到期overdue已逾期deleted已删除
    * @param   int m - 菜单ID
    * @return  int list[].id - 产品ID
    * @return  int list[].product_id - 商品ID
    * @return  string list[].name - 产品标识
    * @return  string list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
    * @return  int list[].active_time - 开通时间
    * @return  int list[].due_time - 到期时间
    * @return  string list[].first_payment_amount - 首付金额
    * @return  string list[].renew_amount - 续费金额
    * @return  string list[].billing_cycle - 计费方式
    * @return  string list[].billing_cycle_name - 周期名称
    * @return  string list[].product_name - 商品名称
    * @return  string list[].client_notes - 备注
    * @return  object list[].self_defined_field - 自定义字段值(键是自定义字段ID,值是填的内容)
    * @return  int count - 总条数
    * @return  int expiring_count - 即将到期产品数量
    * @return  int self_defined_field[].id - 自定义字段ID
	* @return  string self_defined_field[].field_name - 自定义字段名称
	* @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
	*/
	public function hostList()
	{
		$param = request()->param();

		$result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => [],
                'count' => 0,
                'self_defined_field' => [],
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
            			->where('module', 'idcsmart_common')
                        ->where('id', $param['m'])
                        ->find();
            if(!empty($MenuModel) && !empty($MenuModel['product_id'])){
                $MenuModel['product_id'] = json_decode($MenuModel['product_id'], true);
                if(!empty($MenuModel['product_id'])){
                	$upstreamProduct = UpstreamProductModel::whereIn('product_id', $MenuModel['product_id'])->where('res_module', 'idcsmart_common')->find();
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

        // 前台是否展示已删除产品
        $homeShowDeletedHost = configuration('home_show_deleted_host');
        if($homeShowDeletedHost!=1){
        	$where[] = ['h.status', '<>', 'Deleted'];
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

        // hh 20240319 先做兼容处理,后续稳定后不用判断
        $supportOrderRecycleBin = is_numeric(configuration('order_recycle_bin'));
        if($supportOrderRecycleBin){
            $where[] = ['h.is_delete', '=', 0];
        }

        // theworld 20240401 获取即将到期数量
        $expiringCount = HostModel::alias('h')
                ->field('h.id')
                ->leftJoin('product p', 'h.product_id=p.id')
                ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="idcsmart_common"')
                ->where($where)
                ->where(function($query){
	                $time = time();
	                $renewalFirstDay = configuration('cron_due_renewal_first_day');
	                $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));
	                $query->whereIn('h.status', ['Pending', 'Active'])->where('h.due_time', '>', $time)->where('h.due_time', '<=', $timeRenewalFirst)->where('billing_cycle', '<>', 'free')->where('billing_cycle', '<>', 'onetime');
	            })
                ->count();

        // theworld 20240401 列表过滤条件移动       
        if(isset($param['status']) && !empty($param['status'])){
            if($param['status'] == 'Pending'){
                $where[] = ['h.status', 'IN', ['Pending','Failed']];
            }else if(in_array($param['status'], ['Unpaid','Active','Suspended','Deleted'])){
                $where[] = ['h.status', '=', $param['status']];
            }
        }
        if(isset($param['tab']) && !empty($param['tab'])){
            if($param['tab']=='using'){
                $where[] = ['h.status', 'IN', ['Pending','Active']];
            }else if($param['tab']=='expiring'){
                $time = time();
                $renewalFirstDay = configuration('cron_due_renewal_first_day');
                $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));

                $where[] = ['h.status', 'IN', ['Pending','Active']];
                $where[] = ['h.due_time', '>', $time];
                $where[] = ['h.due_time', '<=', $timeRenewalFirst];
                $where[] = ['h.billing_cycle', '<>', 'free'];
                $where[] = ['h.billing_cycle', '<>', 'onetime'];
            }else if($param['tab']=='overdue'){
                $time = time();

                $where[] = ['h.status', 'IN', ['Pending', 'Active', 'Suspended', 'Failed']];
                $where[] = ['h.due_time', '<=', $time];
                $where[] = ['h.billing_cycle', '<>', 'free'];
                $where[] = ['h.billing_cycle', '<>', 'onetime'];
            }else if($param['tab']=='deleted'){
                $time = time();
                $where[] = ['h.status', '=', 'Deleted'];
            }
        }
        if(isset($param['keywords']) && $param['keywords'] !== ''){
        	$where[] = ['h.name', 'LIKE', '%'.$param['keywords'].'%'];
        }

        $count = HostModel::alias('h')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="idcsmart_common"')
            ->where($where)
            ->count();

        $host = HostModel::alias('h')
            ->field('h.id,h.product_id,h.name,h.status,h.active_time,h.due_time,h.first_payment_amount,h.renew_amount,h.billing_cycle,h.billing_cycle_name,p.name product_name,h.client_notes')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="idcsmart_common"')
            ->where($where)
            ->withAttr('status', function($val){
                return $val == 'Failed' ? 'Pending' : $val;
            })
            ->withAttr('product_name', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'product_name' => $val,
                    ],
                ]);
                if(isset($multiLanguage['product_name'])){
                    $val = $multiLanguage['product_name'];
                }
                return $val;
            })
            ->withAttr('billing_cycle_name', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'billing_cycle_name' => $val,
                    ],
                ]);
                if(isset($multiLanguage['billing_cycle_name'])){
                    $val = $multiLanguage['billing_cycle_name'];
                }
                return $val;
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
        $result['data']['expiring_count'] = $expiringCount;
        $result['data']['self_defined_field'] = $selfDefinedField['self_defined_field'] ?? [];
        return json($result);
	}

    /**
     * 时间 2024-04-23
     * @title 前台商品配置信息
     * @desc  前台商品配置信息
     * @url /console/v1/idcsmart_common/product/:product_id/configoption
     * @method  GET
     * @author  hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  object common_product - 商品基础信息
     * @return  string common_product.name - 商品名称
     * @return  string common_product.order_page_description - 订购页面html
     * @return  string common_product.allow_qty - 是否允许选择数量:1是，0否默认
     * @return  string common_product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  int common_product.product_id - 
     * @return  array configoptions - 配置项信息
     * @return  int configoptions[].id - 配置项ID
     * @return  int configoptions[].product_id - 商品ID
     * @return  string configoptions[].option_name - 配置项名称
     * @return  string configoptions[].option_type -  配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域，os操作系统
     * @return  int configoptions[].qty_min - 数量时最小值
     * @return  int configoptions[].qty_max - 数量时最大值
     * @return  string configoptions[].unit - 单位
     * @return  int configoptions[].allow_repeat - 数量类型时：是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int configoptions[].max_repeat - 最大允许重复数量
     * @return  string configoptions[].description - 说明
     * @return  int configoptions[].configoption_id - 
     * @return  array configoptions.subs - 子项信息
     * @return  string configoptions.subs[].os - 操作系统分组(option_type=os返回)
     * @return  array configoptions.subs[].version - 操作系统版本(option_type=os返回)
     * @return  int configoptions.subs[].version[].id - 子项ID
     * @return  string configoptions.subs[].version[].option_name - 子项名称
     * @return  int configoptions.subs[].version[].qty_min - 子项最小值
     * @return  int configoptions.subs[].version[].qty_max - 子项最大值
     * @return  string configoptions.subs[].version[].country - 
     * @return  string configoptions.subs[].version[].option_type - 配置项类型
     * @return  string configoptions.subs[].version[].fee_type - 
     * @return  int configoptions.subs[].version[].product_configoption_id - 
     * @return  int configoptions.subs[].version[].qty_change - 
     * @return  int configoptions.subs[].id - 子项ID
     * @return  string configoptions.subs[].option_name - 子项名称
     * @return  int configoptions.subs[].qty_min - 子项最小值
     * @return  int configoptions.subs[].qty_max - 子项最大值
     * @return  string configoptions.subs[].country - 
     * @return  string configoptions.subs[].option_type - 配置项类型
     * @return  string configoptions.subs[].fee_type - 
     * @return  int configoptions.subs[].product_configoption_id - 
     * @return  int configoptions.subs[].qty_change - 数量变化值
     * @return  object cycles - 周期({"onetime":1.00})
     * @return  array custom_cycles - 自定义周期
     * @return  int custom_cycles[].id - 自定义周期ID
     * @return  string custom_cycles[].name - 自定义周期名称
     * @return  int custom_cycles.cycle_time - 自定义周期时长
     * @return  string custom_cycles[].cycle_unit - 自定义周期单位
     * @return  string custom_cycles[].amount - 商品自定义周期金额
     * @return  string custom_cycles[].cycle_amount - (商品+配置项)自定义周期金额
     * @return  string custom_cycles[].amount_client_level_discount - 商品自定义周期金额等级折扣
     * @return  string custom_cycles[].cycle_amount_client_level_discount - (商品+配置项)自定义周期金额等级折扣
     */
    public function cartConfigoption(){
        $param = request()->param();

        $productId = $param['product_id'];
        $produduct = ProductModel::find($productId);

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByProduct($productId);

            // 标志为下游
            $param['is_downstream'] = 1;
            unset($param['product_id']);
            $result = $RouteLogic->curl( sprintf('console/v1/reidcsmart_common/product/%d/configoption', $RouteLogic->upstream_product_id), $param, 'GET');
            if($result['status'] == 200){
                // 替换商品名称和ID
                if(!empty($product)){
                    $result['data']['common_product']['name'] = $product['name'];
                    $result['data']['common_product']['product_id'] = $productId;
                    $result['data']['common_product']['pay_type'] = $produduct['pay_type'];
                }
                // 处理多级代理问题
                $PluginModel = new PluginModel();
                $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
                if(isset($result['data']['custom_cycles'])){
                    foreach($result['data']['custom_cycles'] as $k=>$v){
                        // 计算价格倍率
                        if (isset($v['amount_client_level_discount'])){
                            $v['amount'] = bcsub($v['amount'],$v['amount_client_level_discount'],2);
                        }
                        if (isset($v['cycle_amount_client_level_discount'])){
                            $v['cycle_amount'] = bcsub($v['cycle_amount'],$v['cycle_amount_client_level_discount'],2);
                        }
                        if($v['amount'] > 0){
                            $result['data']['custom_cycles'][$k]['amount'] = $RouteLogic->profit_type==1?bcadd($v['amount'],$RouteLogic->getProfitPercent()*100):bcmul($v['amount'], $RouteLogic->price_multiple);
                        }
                        if($v['cycle_amount'] > 0){
                            $result['data']['custom_cycles'][$k]['cycle_amount'] = $RouteLogic->profit_type==1?bcadd($v['cycle_amount'],$RouteLogic->getProfitPercent()*100):bcmul($v['cycle_amount'], $RouteLogic->price_multiple);
                        }
                        if (!empty($plugin)){
                            $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                            // 获取商品折扣金额
                            $amountClientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                                'id'     => $productId,
                                'amount' => $result['data']['custom_cycles'][$k]['amount'],
                            ]);
                            // 获取商品折扣金额
                            $cycleAmountClientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                                'id'     => $productId,
                                'amount' => $result['data']['custom_cycles'][$k]['cycle_amount'],
                            ]);
                            // 二级代理及以下给下游的客户等级折扣数据
                            $result['data']['custom_cycles'][$k]['amount_client_level_discount'] = $amountClientLevelDiscount ?? '0.00';
                            $result['data']['custom_cycles'][$k]['cycle_amount_client_level_discount'] = $cycleAmountClientLevelDiscount ?? '0.00';
                        }
                    }
                }
            }
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->cartConfigoption();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

	/**
     * 时间 2024-04-23
     * @title 前台商品配置信息计算价格
     * @desc 前台商品配置信息计算价格
     * @url /console/v1/reidcsmart_common/product/:product_id/configoption/calculate
     * @method  POST
     * @author hh
     * @version v1
     * @param  object configoption - 配置信息{168:1,514:53} require
     * @return object cycles - 周期({"onetime":1.00})
     * @return array custom_cycles - 自定义周期
     * @return int custom_cycles[].id - 自定义周期ID
     * @return string custom_cycles[].name - 自定义周期名称
     * @return int custom_cycles[].cycle_time - 自定义周期时长
     * @return string custom_cycles[].cycle_unit - 自定义周期单位
     * @return string custom_cycles[].amount - 商品自定义周期金额
     * @return string custom_cycles[].cycle_amount - (商品+配置项)自定义周期金额
     * @return string custom_cycles[].amount_client_level_discount - 商品自定义周期金额等级折扣
     * @return string custom_cycles[].cycle_amount_client_level_discount - (商品+配置项)自定义周期金额等级折扣
     */
    public function cartConfigoptionCalculate()
    {
        $param = request()->param();

        $productId = $param['product_id'];
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByProduct($productId);

            // 标志为下游
            $param['is_downstream'] = 1;
            unset($param['product_id']);
            $result = $RouteLogic->curl( sprintf('console/v1/reidcsmart_common/product/%d/configoption/calculate', $RouteLogic->upstream_product_id), $param, 'POST');
            if($result['status'] == 200){
                // 处理多级代理问题
                $PluginModel = new PluginModel();
                $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();

                if(isset($result['data']['custom_cycles'])){
                    foreach($result['data']['custom_cycles'] as $k=>$v){
                        if (isset($v['amount_client_level_discount'])){
                            $v['amount'] = bcsub($v['amount'],$v['amount_client_level_discount'],2);
                        }
                        if (isset($v['cycle_amount_client_level_discount'])){
                            $v['cycle_amount'] = bcsub($v['cycle_amount'],$v['cycle_amount_client_level_discount'],2);
                        }
                        if($v['amount']>0){
                            $result['data']['custom_cycles'][$k]['amount'] = $RouteLogic->profit_type==1?bcadd($v['amount'],$RouteLogic->getProfitPercent()*100):bcmul($v['amount'], $RouteLogic->price_multiple);
                        }
                        if($v['cycle_amount']>0){
                            $result['data']['custom_cycles'][$k]['cycle_amount'] = $RouteLogic->profit_type==1?bcadd($v['cycle_amount'],$RouteLogic->getProfitPercent()*100):bcmul($v['cycle_amount'], $RouteLogic->price_multiple);
                        }
                        if (!empty($plugin)){
                            $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                            // 获取商品折扣金额
                            $amountClientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                                'id'     => $productId,
                                'amount' => $result['data']['custom_cycles'][$k]['amount'],
                            ]);
                            $cycleAmountClientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                                'id'     => $productId,
                                'amount' => $result['data']['custom_cycles'][$k]['cycle_amount'],
                            ]);
                            // 二级代理及以下给下游的客户等级折扣数据
                            $result['data']['custom_cycles'][$k]['amount_client_level_discount'] = $amountClientLevelDiscount ?? '0.00';
                            $result['data']['custom_cycles'][$k]['cycle_amount_client_level_discount'] = $cycleAmountClientLevelDiscount ?? '0.00';
                        }
                    }
                }
            }
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->cartConfigoptionCalculate();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2024-04-23
     * @title 前台产品内页
     * @desc  前台产品内页
     * @url /console/v1/reidcsmart_common/host/:host_id/configoption
     * @method GET
     * @author hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host - 财务信息
     * @return  int host.id - 产品ID
     * @return  int host.order_id - 订单ID
     * @return  int host.product_id - 商品ID
     * @return  int host.create_time - 订购时间
     * @return  int host.due_time - 到期时间
     * @return  string host.billing_cycle - 计费方式:计费周期免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  string host.billing_cycle_name - 模块计费周期名称
     * @return  int host.billing_cycle_time - 模块计费周期时间,秒
     * @return  string host.renew_amount - 续费金额
     * @return  string host.first_payment_amount - 首付金额
     * @return  string host.name - 商品名称
     * @return  string host.status - 产品状态
     * @return  string host.host_name - 产品标识
     * @return  string host.client_notes - 用户备注
     * @return  string host.dedicatedip - 独立ip
     * @return  string host.assignedips - 分配ip，逗号分隔
     * @return  string host.username - 用户名
     * @return  string host.password - 密码
     * @return  int host.bwlimit - 流量限制
     * @return  string host.os - 操作系统，后台未配置时显示远程操作系统模板ID
     * @return  string host.bwusage - 流量使用
     * @return  array configoptions - 配置项信息
     * @return  int configoptions[].id - 配置项ID
     * @return  string configoptions[].option_name - 配置项名称
     * @return  string configoptions[].option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域，os操作系统
     * @return  string configoptions[].unit - 单位
     * @return  int configoptions[].qty - 数量(当类型为数量时,显示此值)
     * @return  int configoptions[].repeat - 
     * @return  array configoptions[].subs - 
     * @return  string configoptions[].subs[].option_name - 子项名称
     * @return  string configoptions[].subs[].country - 子项名称
     * @return array chart - 图表tab
     * @return string chart[].type - 类型
     * @return string chart[].title - 标题
     * @return array chart[].select - 下拉选择
     * @return string chart[].select[].name - 名称
     * @return string chart[].select[].value - 值
     * @return array client_area - 客户自定义tab区域
     * @return string client_area[].key - 键
     * @return string client_area[].name - 名称标题
     * @return array client_button - 管理按钮区域(默认模块操作)
     * @return array client_button.console - 控制台
     * @return string client_button.console[].func - 模块(调模块动作传此值)
     * @return string client_button.console[].name - 操作名称
     * @return string client_button.console[].type - 类型
     * @return array client_button.control - 下拉管理
     * @return string client_button.control[].func - 模块(调模块动作传此值)
     * @return string client_button.control[].name - 操作名称
     * @return string client_button.control[].type - 类型
     * @return object os - 操作系统
     * @return int os.id - 配置项ID
     * @return string os.option_name - 配置项名称
     * @return string os.option_type - 配置项类型
     * @return array os.subs - 子项
     * @return string os.subs[].os - 操作系统
     * @return array os.subs[].version - 操作系统详细版本
     * @return int os.subs[].version[].id - 子项ID
     * @return string os.subs[].version[].option_name - 名称
     * @return string os.subs[].version[].option_param - 
     */
    public function hostConfigotpion()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }
        $product = ProductModel::find($host['product_id']);

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/configoption', $RouteLogic->upstream_host_id), $param, 'GET');
            if($result['status'] == 200){
                if(isset($result['data']['host'])){
                    // 替换为下游的数据
                    $result['data']['host']['id'] = $host['id'];
                    $result['data']['host']['order_id'] = $host['order_id'];
                    $result['data']['host']['product_id'] = $host['product_id'];
                    $result['data']['host']['create_time'] = $host['create_time'];
                    $result['data']['host']['due_time'] = $host['due_time'];
                    $result['data']['host']['billing_cycle'] = $host['billing_cycle'];
                    $result['data']['host']['billing_cycle_name'] = $host['billing_cycle_name'];
                    $result['data']['host']['billing_cycle_time'] = $host['billing_cycle_time'];
                    $result['data']['host']['renew_amount'] = $host['renew_amount'];
                    $result['data']['host']['first_payment_amount'] = $host['first_payment_amount'];
                    $result['data']['host']['name'] = $product['name'];
                    $result['data']['host']['host_name'] = $host['name'];
                    $result['data']['host']['status'] = $host['status'];
                    $result['data']['host']['client_notes'] = $host['client_notes'];
                }
            }
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->hostConfigotpion();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2023-11-21
     * @title 前台产品内页自定义页面输出
     * @desc 前台产品内页自定义页面输出
     * @url /console/v1/reidcsmart_common/host/:host_id/configoption/area
     * @method  GET
     * @author hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   string key - snapshot快照等 require
     * @param   string api_url - 替换原来模板内的接口地址
     */
    public function clientAreaOutput()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }
        if(!isset($param['api_url'])){
            $param['api_url'] = request()->domain().request()->rootUrl()."/console/v1/reidcsmart_common/host/{$param['host_id']}/custom/provision";
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/configoption/area', $RouteLogic->upstream_host_id), $param, 'GET');
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->clientAreaOutput();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2024-04-23
     * @title 前台产品内页图表页面
     * @desc  前台产品内页图表页面
     * @url /console/v1/reidcsmart_common/host/:host_id/configoption/chart
     * @method  POST
     * @author  hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   object chart - 图表数据 require
     * @param   int chart.start - 开始时间 require
     * @param   string chart.type - 类型：cpu/disk/flow require
     * @param   string chart.select - select的value值 require
     * @return  string unit - 单位
     * @return  string chart_type - 图表类型(line=折线图)
     * @return  array list - 每条线的数据
     * @return  string list[][].time - 时间(YYYY-MM-DD HH:II:SS格式)
     * @return  string list[][].value - 数值
     * @return  array label - 图表label
     */
    public function chartData()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/configoption/chart', $RouteLogic->upstream_host_id), $param, 'POST');
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->chartData();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2023-11-21
     * @title 执行子模块方法
     * @desc 执行子模块方法
     * @url /console/v1/reidcsmart_common/host/:host_id/provision/:func
     * @method  POST
     * @author  hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   string func - 模块方法:on=开机,off=关机,reboot=重启,hard_off=硬关机,hard_reboot=硬重启,crack_pass=重置密码 require
     * @param   string password - 密码 requireIf,func=crack_pass
     */
    public function provisionFunc()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }
        $func = $param['func'];

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id'],$param['func']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/provision/%s', $RouteLogic->upstream_host_id, $func), $param, 'POST');
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->provisionFunc();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2023-11-21
     * @title 执行子模块自定义方法
     * @desc 执行子模块自定义方法
     * @url /console/v1/reidcsmart_common/host/:host_id/custom/provision
     * @method  POST
     * @author  hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   string func - 自定义方法 require
     * @param   array custom_fields - 自定义字段
     */
    public function provisionFuncCustom()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/custom/provision', $RouteLogic->upstream_host_id), $param, 'POST');


        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->provisionFuncCustom();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2024-04-24
     * @title 产品配置升降级页面
     * @desc 产品配置升降级页面
     * @url /console/v1/reidcsmart_common/host/:host_id/upgrade_config
     * @method  GET
     * @author  hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host - 产品信息
     * @return  int host.id - 产品ID
     * @return  int host.product_id - 商品ID
     * @return  string host.name - 商品名称
     * @return  string host.first_payment_amount - 金额
     * @return  string host.billing_cycle_name - 周期
     * @return  array configoptions - 配置
     * @return  int configoptions[].id - 配置ID
     * @return  string configoptions[].option_type - 配置类型
     * @return  string configoptions[].option_name - 名称
     * @return  string configoptions[].sub_name - 子项名称
     * @return  int configoptions[].qty - 数量(类型为数量时,显示此值)
     * @return  int configoptions[].configoption_sub_id - 子项ID
     * @return  array upgrade_configoptions - 可升降级配置项
     * @return  int upgrade_configoptions[].id - 配置项ID
     * @return  string upgrade_configoptions[].option_name - 配置项名称
     * @return  string upgrade_configoptions[].option_type - 配置类型
     * @return  string upgrade_configoptions[].option_param - 配置参数
     * @return  int upgrade_configoptions[].qty_min - 数量时最小值
     * @return  int upgrade_configoptions[].qty_max - 数量时最大值
     * @return  int upgrade_configoptions[].order - 排序
     * @return  string upgrade_configoptions[].unit - 单位
     * @return  int upgrade_configoptions[].allow_repeat - 数量类型时：是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int upgrade_configoptions[].max_repeat - 最大允许重复数量
     * @return  string upgrade_configoptions[].fee_type - 
     * @return  string upgrade_configoptions[].description - 说明
     * @return  int upgrade_configoptions[].configoption_id - 
     * @return  int upgrade_configoptions[].son_product_id - 
     * @return  int upgrade_configoptions[].free - 
     * @return  array upgrade_configoptions[].subs - 配置子项数据
     * @return  int upgrade_configoptions[].subs[].id - 子项ID
     * @return  int upgrade_configoptions[].subs[].product_configoption_id - 配置项ID
     * @return  string upgrade_configoptions[].subs[].option_name - 子项名称
     * @return  string upgrade_configoptions[].subs[].option_param - 子项参数
     * @return  int upgrade_configoptions[].subs[].qty_min - 数量时最小值
     * @return  int upgrade_configoptions[].subs[].qty_max - 数量时最大值
     * @return  int upgrade_configoptions[].subs[].order - 排序
     * @return  int upgrade_configoptions[].subs[].hidden - 
     * @return  string upgrade_configoptions[].subs[].country - 
     * @return  int upgrade_configoptions[].subs[].qty_change - 数量变化值
     */
    public function upgradeConfigPage()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }
        $product = ProductModel::find($host['product_id']);

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/upgrade_config', $RouteLogic->upstream_host_id), $param, 'GET');
            if($result['status'] == 200){
                // 下游数据覆盖
                $result['data']['host'] = [
                    'id'                    => $host['id'],
                    'product_id'            => $product['id'],
                    'name'                  => $product['name'],
                    'first_payment_amount'  => $host['first_payment_amount'],
                    'billing_cycle_name'    => $host['billing_cycle_name'],
                ];
            }
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->upgradeConfigPage();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception')];
            }
        }
        return json($result);
    }

    /**
     * 时间 2024-04-24
     * @title 产品配置升降级异步获取升降级价格
     * @desc  产品配置升降级异步获取升降级价格
     * @url /console/v1/reidcsmart_common/host/:host_id/sync_upgrade_config_price
     * @method  POST
     * @author  hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   object configoption - "configoption":{"1"：2,"2":3,"4":[1,2,3]}
     * @return  string price - 价格
     * @return  string description - 描述
     * @return  string price_difference - 差价
     * @return  string renew_price_difference - 续费差价
     * @return  string base_price - 基础价格
     * @return  string price_client_level_discount - 价格等级折扣
     * @return  string price_difference_client_level_discount - 差价等级折扣
     * @return  string renew_price_difference_client_level_discount - 续费差价等级折扣
     */
    public function syncUpgradeConfigPrice()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/sync_upgrade_config_price', $RouteLogic->upstream_host_id), $param, 'POST');
            if($result['status'] == 200){
                if (isset($result['data']['downgrade']) && $result['data']['downgrade']){

                }else{
                    if (isset($result['data']['price_client_level_discount'])){
                        $result['data']['price'] = bcsub($result['data']['price'],$result['data']['price_client_level_discount'],2);
                    }
                    if (isset($result['data']['price_difference_client_level_discount'])){
                        $result['data']['price_difference'] = bcsub($result['data']['price_difference'],$result['data']['price_difference_client_level_discount'],2);
                    }
                    if (isset($result['data']['renew_price_difference_client_level_discount'])){
                        $result['data']['renew_price_difference'] = bcsub($result['data']['renew_price_difference'],$result['data']['renew_price_difference_client_level_discount'],2);
                    }
                }

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

                if ($result['data']['price']<0){
                    $result['data']['downgrade'] = true;
                }

                // 处理多级代理问题
                $PluginModel = new PluginModel();
                $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
                if (!empty($plugin)){
                    $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                    // 获取商品折扣金额
                    $clientLevelDiscount = $IdcsmartClientLevelModel->productDiscount([
                        'id'        => $host['product_id'],
                        'amount'    => $result['data']['price'],
                    ]);
                    $clientLevelDiscountPriceDifference = $IdcsmartClientLevelModel->productDiscount([
                        'id'        => $host['product_id'],
                        'amount'    => $result['data']['price_difference'],
                    ]);
                    $clientLevelDiscountRenewPriceDifference = $IdcsmartClientLevelModel->productDiscount([
                        'id'        => $host['product_id'],
                        'amount'    => $result['data']['renew_price_difference'],
                    ]);
                    // 二级代理及以下给下游的客户等级折扣数据
                    $result['data']['price_client_level_discount'] = amount_format($clientLevelDiscount);
                    $result['data']['price_difference_client_level_discount'] = amount_format($clientLevelDiscountPriceDifference);
                    $result['data']['renew_price_difference_client_level_discount'] = amount_format($clientLevelDiscountRenewPriceDifference);
                }
            }
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->syncUpgradeConfigPrice();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception') ];
            }
        }
        return json($result);
    }

    /**
     * 时间 2024-04-23
     * @title 产品配置升降级
     * @desc  产品配置升降级
     * @url /console/v1/reidcsmart_common/host/:host_id/upgrade_config
     * @method  POST
     * @author hh
     * @version v1
     * @param   int host_id - 产品ID require
     * @param   object configoption - "configoption":{"1"：2,"2":3,"4":[1,2,3]} require
     * @param   object customfield - 自定义参数
     * @return  int id - 订单ID
     */
    public function upgradeConfig()
    {
        $param = request()->param();

        $host = HostModel::find($param['host_id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['host_id']);

            $param['is_downstream'] = 1;
            unset($param['host_id']);
            $result = $RouteLogic->curl( sprintf('/console/v1/reidcsmart_common/host/%d/sync_upgrade_config_price', $RouteLogic->upstream_host_id), $param, 'POST');

            if($result['status'] == 200){
                if (isset($result['data']['downgrade']) && $result['data']['downgrade']){

                }else{
                    if (isset($result['data']['price_client_level_discount'])){
                        $result['data']['price'] = bcsub($result['data']['price'],$result['data']['price_client_level_discount'],2);
                    }
                    if (isset($result['data']['price_difference_client_level_discount'])){
                        $result['data']['price_difference'] = bcsub($result['data']['price_difference'],$result['data']['price_difference_client_level_discount'],2);
                    }
                    if (isset($result['data']['renew_price_difference_client_level_discount'])){
                        $result['data']['renew_price_difference'] = bcsub($result['data']['renew_price_difference'],$result['data']['renew_price_difference_client_level_discount'],2);
                    }
                }

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

                if ($result['data']['price']<0){
                    $result['data']['downgrade'] = true;
                }
               
                $OrderModel = new OrderModel();

                $data = [
                    'host_id'     => $host['id'],
                    'client_id'   => get_client_id(),
                    'type'        => 'upgrade_config',
                    'amount'      => $result['data']['price'],
                    'description' => $result['data']['description'],
                    'price_difference' => $result['data']['price_difference'],
                    'renew_price_difference' => $result['data']['renew_price_difference'],
                    'upgrade_refund' => 0,
                    'config_options' => [
                        'type'          => 'reidcsmart_common_upgrade_config',
                        'configoption'  => $param['configoption'],
                    ],
                    'customfield' => $param['customfield'] ?? [],
                ];
                $result = $OrderModel->createOrder($data);
                if($result['status'] == 200){
                    UpstreamOrderModel::create([
                        'supplier_id'   => $RouteLogic->supplier_id,
                        'order_id'      => $result['data']['id'],
                        'host_id'       => $host['id'],
                        'amount'        => $data['amount'],
                        'profit'        => $profit,
                        'create_time'   => time(),
                    ]);
                }
            }
        }catch(\Exception $e){
            if(!$RouteLogic->isUpstream){
                if(class_exists('\server\idcsmart_common\controller\home\IdcsmartCommonProductController')){
                    return (new \server\idcsmart_common\controller\home\IdcsmartCommonProductController(app()))->upgradeConfig();
                }else{
                    $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_error_act')];
                }
            }else{
                $result = ['status'=>400, 'msg'=>lang_plugins('res_idcsmart_common_act_exception').$e->getMessage()];
            }
        }
        return json($result);
    }

}
