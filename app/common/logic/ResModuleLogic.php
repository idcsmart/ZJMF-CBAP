<?php 
namespace app\common\logic;

use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\admin\model\PluginModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamHostModel;
use think\facade\View;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;

/**
 * @title RES模块逻辑
 * @desc RES模块逻辑
 * @use  app\common\logic\ResModuleLogic
 */
class ResModuleLogic
{
	// 模块目录
	protected $path = WEB_ROOT . 'plugins/reserver/';

	protected $upstreamProduct = [];

	public function __construct($upstreamProduct = []){
		// upstreamProduct数据
		$this->upstreamProduct = $upstreamProduct;
	}

	/**
	 * 时间 2022-05-27
	 * @title 获取模块列表
	 * @desc 获取模块列表
	 * @author hh
	 * @version v1
	 * @return  string [].name - 模块名称
	 * @return  string [].display_name - 模块显示名称
	 */
	public function getModuleList(): array
	{
		$modules = [];
		if(is_dir($this->path)){
		    if($handle = opendir($this->path)){
		        while(($file = readdir($handle)) !== false){
		        	if($file != '.' && $file != '..' && is_dir($this->path . $file) && preg_match('/^[a-z][a-z0-9_]{0,99}$/', $file)){
		        	    if($ImportModule = $this->importModule($file)){
		        			if(method_exists($ImportModule, 'metaData')){
		        				$metaData = call_user_func([$ImportModule, 'metaData']);
		        				$modules[] = [
		        					'name'=>$file,
		        					'display_name'=>$metaData['display_name'] ?: $file,
		        				];
		        			}else{
		        				$modules[] = [
		        					'name'=>$file,
		        					'display_name'=>$file,
		        				];
		        			}
		        		}
		        	}
		        }
		        closedir($handle);
		    }
		}
		return $modules;
	}

	/**
	 * 时间 2022-05-27
	 * @title 测试连接
	 * @desc 测试连接
	 * @author hh
	 * @version v1
	 * @param   ServerModel ServerModel - 接口模型
	 * @return  int status - 200=连接成功,400=连接失败
	 * @return  string msg - 信息
	 */
	// public function testConnect(ServerModel $ServerModel): array
	// {
	// 	$module = $ServerModel['module'];
	// 	if($ImportModule = $this->importModule($module)){
	// 		if(method_exists($ImportModule, 'testConnect')){
	// 			// 获取模块通用参数
	// 			$res = call_user_func([$ImportModule, 'testConnect'], ['server'=>$ServerModel]);
	// 			$res = $this->formatResult($res, lang('module_test_connect_success'), lang('module_test_connect_fail'));
	// 		}else{
	// 			$res['status'] = 400;
	// 			$res['msg'] = lang('undefined_test_connect_function');
	// 		}
	// 	}else{
	// 		$res['status'] = 400;
	// 		$res['msg'] = lang('module_file_is_not_exist');
	// 	}
	// 	return $res;
	// }

	/**
	 * 时间 2022-05-16
	 * @title 产品开通
	 * @desc 产品开通
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function createAccount(HostModel $HostModel): array
	{
	    $id = $HostModel->id;

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        if (empty($upstreamHost)) {
            return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        }
        $apiId = $this->upstreamProduct['supplier_id'];
        # 检查是否可以登录
        $res = idcsmart_api_login($apiId,true);
        if ($res['status']==200){
            $upstreamInfo = json_decode($upstreamHost['upstream_info'],true)??[];
            if (!empty($upstreamInfo['token'])){
                $token = $upstreamInfo['token'];
                $url = $upstreamInfo['url'];
            }else{
                $token = md5(rand_str(16).time().$id);
                $url = configuration('website_url'); //request()->domain() . request()->rootUrl(); 服务器定时任务走cli模式,获取的是本地localhost
                $upstreamHost->save([
                    'upstream_info' => json_encode(['token'=>$token,'url'=>$url])
                ]);
            }

            $clearCartData = [
                'downstream_url' => $url,
                'downstream_token' => $token,
                'downstream_host_id' => $id,
                'downstream_client_id' => $HostModel['client_id'],
            ];

            $enable = PluginModel::where('name', 'IdcsmartSubAccount')->where('module', 'addon')->where('status',1)->find();
			if(!empty($enable) && class_exists('addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel')){
				// 是否是子账户
				$IdcsmartSubAccountHostModel = IdcsmartSubAccountHostModel::where('host_id', $id)->find();
				if(!empty($IdcsmartSubAccountHostModel)){
					$IdcsmartSubAccountModel = IdcsmartSubAccountModel::find($IdcsmartSubAccountHostModel['addon_idcsmart_sub_account_id']);
					if(!empty($IdcsmartSubAccountModel)){
						$clearCartData['downstream_client_id'] = $IdcsmartSubAccountModel['client_id'];
					}
				}
			}
            # 清空购物车(检查是否已下单)
            $res = idcsmart_api_curl($apiId,'/console/v1/cart',$clearCartData,30,'DELETE');
            if ($res['status']==200){
                if (isset($res['data']['order_id']) && $res['data']['order_id']){ # 已在上游下单,但未支付
                    /*$creditData = [
                        'id' => $res['data']['order_id']??0,
                        'use' => 1
                    ];*/
                    # 使用余额
                    //$res = idcsmart_api_curl($apiId,'/console/v1/credit',$creditData,30,'POST');
                    //if ($res['status']==200){
                        $payData = [
                            'id' => $res['data']['order_id']??0,
                            'gateway' => 'credit'
                        ];
                        # 支付
                        $res = idcsmart_api_curl($apiId,'/console/v1/pay',$payData,30,'POST');
                    //}
                }else{
                    $cartData = [
                        'product_id' => $this->upstreamProduct['upstream_product_id'],
                        'qty' => 1,
                        'config_options' => json_decode($upstreamHost['upstream_configoption'],true)
                    ];
                    # 加入购物车
                    $res = idcsmart_api_curl($apiId,'/console/v1/cart',$cartData,30,'POST');
                    if ($res['status']==200){
                        $settleCartData = $clearCartData;
                        $settleCartData['positions'] = [0]; # 取第一个,只有一个
                        $settleCartData['downstream_client_id'] = $clearCartData['downstream_client_id'];
                        # 结算
                        $res = idcsmart_api_curl($apiId,'/console/v1/cart/settle',$settleCartData,30,'POST');
                        if ($res['status']==200){
                            $upstreamHost->save([
                                'upstream_host_id' => $res['data']['host_ids'][0]??0,
                            ]);
                            if ($res['data']['amount']>0){ // 处理需要支付的
                                /*$creditData = [
                                    'id' => $res['data']['order_id']??0,
                                    'use' => 1
                                ];*/
                                # 使用余额
                                //$res = idcsmart_api_curl($apiId,'/console/v1/credit',$creditData,30,'POST');
                                //if ($res['status']==200){
                                    $payData = [
                                        'id' => $res['data']['order_id']??'',
                                        'gateway' => 'credit'
                                    ];
                                    # 支付
                                    $res = idcsmart_api_curl($apiId,'/console/v1/pay',$payData,30,'POST');
                                //}
                            }
                        }
                    }
                }
            }
        }

        return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品暂停
	 * @desc 产品暂停
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function suspendAccount(HostModel $HostModel, $param = []): array
	{
	    $id = $HostModel->id;

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        if (empty($upstreamHost)) {
            return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        }

        $apiId = $this->upstreamProduct['supplier_id'];

        $suspendData = [
            'suspend_type' => $param['suspend_type']??'downstream',
            'suspend_reason' => $param['suspend_reason']??'代理商暂停'
        ];

        $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/module/suspend",$suspendData,30,'POST');

		return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品解除暂停
	 * @desc 产品解除暂停
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function unsuspendAccount(HostModel $HostModel): array
	{
        $id = $HostModel->id;

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        if (empty($upstreamHost)) {
            return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        }

        $apiId = $this->upstreamProduct['supplier_id'];

        $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/module/unsuspend",[],30,'POST');

        return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品删除
	 * @desc 产品删除
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function terminateAccount(HostModel $HostModel): array
	{
        $id = $HostModel->id;

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        if (empty($upstreamHost)) {
            return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        }

        $apiId = $this->upstreamProduct['supplier_id'];

        // TODO
        $terminateData = [
            'host_id' => $upstreamHost['upstream_host_id'],
            'suspend_reason' => '代理商删除',
            'type' => 'Immediate'
        ];

        $res = idcsmart_api_curl($apiId,"/console/v1/refund",$terminateData,30,'POST');

        return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 续费订单支付后调用
	 * @desc 续费订单支付后调用
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 */
	 public function renew(HostModel $HostModel)
	 {
         $id = $HostModel['id'];

         $UpstreamHostModel = new UpstreamHostModel();
         $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
         if (empty($upstreamHost)) {
             return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
         }

         $apiId = $this->upstreamProduct['supplier_id'];

         $renewData = [
             'billing_cycle' => $HostModel['billing_cycle_name']??''
         ];

         $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/renew",$renewData,30,'POST');
         if ($res['status']==200){
             if ($res['code']=='Unpaid'){ # 未支付
                 $creditData = [
                     'id' => $res['data']['id']??0,
                     'use' => 1
                 ];
                 # 使用余额
                 $res = idcsmart_api_curl($apiId,'/console/v1/credit',$creditData,30,'POST');
                 if ($res['status']==200){
                     $payData = [
                         'id' => $res['data']['id'],
                         'gateway' => 'credit'
                     ];
                     # 支付
                     $res = idcsmart_api_curl($apiId,'/console/v1/pay',$payData,30,'POST');
                 }
             }
             unset($res['code']);
         }

         return $res;
	 }

	/**
	 * 时间 2022-05-26
	 * @title 升降级配置项完成后调用
	 * @desc 升降级配置项完成后调用
	 * @author hh
	 * @version v1
	 * @param HostModel HostModel - 产品模型
	 * @param mixed params - 自定义参数
	 */
	public function changePackage(HostModel $HostModel, $params)
	{
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'changePackage')){
				// 获取模块通用参数
				$moduleParams = $HostModel->getModuleParams();
				$moduleParams['custom'] = $params;
				$res = call_user_func([$ImportModule, 'changePackage'], $moduleParams);
			}
		}
		// 不需要返回
	}

	/**
	 * 时间 2022-06-01
	 * @title 升降级商品完成后调用
	 * @desc 升降级商品完成后调用
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 已经关联新商品的产品模型
	 * @param   mixed params - 自定义参数
	 */
	// public function changeProduct(HostModel $HostModel, $params)
	// {
	// 	if($ImportModule = $this->importModule()){
	// 		if(method_exists($ImportModule, 'changeProduct')){
	// 			// 获取模块通用参数
	// 			$moduleParams = $HostModel->getModuleParams();
	// 			$moduleParams['custom'] = $params;
	// 			$res = call_user_func([$ImportModule, 'changeProduct'], $moduleParams);
	// 		}
	// 	}
	// 	// 不需要返回
	// }

	/**
	 * 时间 2022-05-26
	 * @title 购物车价格计算
	 * @desc 购物车价格计算
	 * @author hh
	 * @version v1
	 * @param   ProductModel $ProductModel - 产品模型
	 * @param   mixed  $params   []  自己定义的参数
	 * @param   string  scene - 场景(buy=验证所有参数,cal_price=价格计算)
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  array data - 购物车数据
	 * @return  float data.price - 配置项金额
	 * @return  string data.billing_cycle - 周期名称
	 * @return  int data.duration - 周期时长
	 * @return  string data.description - 订单子项描述
	 * @return  string data.content - 购物车配置显示,支持模板
	 * @return  string data.preview[].name - 名称
	 * @return  string data.preview[].value - 值
	 * @return  string data.preview[].price - 价格
	 */
	public function cartCalculatePrice($ProductModel, $params = [], $qty=1)
	{
        $apiId = $this->upstreamProduct['supplier_id'];

        $upstreamProductId = $this->upstreamProduct['upstream_product_id'];

        $res = idcsmart_api_curl($apiId,"/console/v1/product/{$upstreamProductId}/config_option", ['config_options'=>$params],30,'POST');
        if ($res['status']==200){
        	$res['data']['profit'] = bcmul($res['data']['price'] ?? 0, ($this->upstreamProduct['profit_percent']/100), 2);
            $res['data']['price'] = bcadd($res['data']['price'] ?? 0, $res['data']['profit'], 2);
            $res['data']['renew_price'] = bcmul($res['data']['renew_price']??0,(1+$this->upstreamProduct['profit_percent']/100),2);
            $res['data']['base_price'] = bcmul($res['data']['base_price']??0,(1+$this->upstreamProduct['profit_percent']/100),2);

            $description = '';
            foreach($res['data']['preview'] as $k=>$v){
            	if($v['price']>0){
            		$v['price'] = bcmul($v['price'],(1+$this->upstreamProduct['profit_percent']/100),2);
            		$res['data']['preview'][$k]['price'] = $v['price'];
            	}
            	$description .= $v['name'].': '.$v['value'].',价格:'.$v['price']."\r\n";
            }
            $res['data']['description'] = $description;
            $res['data']['content'] = $description;
        }
        return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 后台商品接口配置输出
	 * @desc 后台商品接口配置输出
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  string
	 */
	/*public function serverConfigOption($module, ProductModel $ProductModel)
	{
		$res = '';
		// 模块调用
		// if($ImportModule = $this->importModule()){
		// 	if(method_exists($ImportModule, 'serverConfigOption')){
		// 		// 获取模块通用参数
		// 		$res = call_user_func([$ImportModule, 'serverConfigOption'], ['product'=>$ProductModel]);
		// 		$res = $this->formatTemplate($res);
		// 	}
		// }
		return $res;
	}*/

	/**
	 * 时间 2022-05-16
	 * @title 产品列表页内容
	 * @desc 产品列表页内容
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function hostList($module, $params): string
	{
        $res = '';
        // 模块调用
        if($ImportModule = $this->importModule($module)){
            if(method_exists($ImportModule, 'hostList')){
                // 获取模块通用参数
                $this->upstreamProduct['res_module'] = $module;
                $res = call_user_func([$ImportModule, 'hostList'], $params);
                $res = $this->formatTemplate($res);
            }
        }
        return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品前台内页输出
	 * @desc 产品前台内页输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function clientArea(HostModel $HostModel): string
	{
		$res = '';
		// 模块调用
		// $module = $HostModel->getModule();
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'clientArea')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'clientArea'], $params);
				$res = $this->formatTemplate($res);
			}
		}
		return $res;
        // $id = $HostModel['id'];

        // $UpstreamHostModel = new UpstreamHostModel();
        // $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        // if (empty($upstreamHost)) {
        //     return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        // }

        // $apiId = $this->upstreamProduct['supplier_id'];

        // $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/view",[],30,'GET');

        // return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品后台内页输出
	 * @desc 产品后台内页输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function adminArea(HostModel $HostModel): string
	{
        // $id = $HostModel['id'];

        // $UpstreamHostModel = new UpstreamHostModel();
        // $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        // if (empty($upstreamHost)) {
        //     return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        // }

        // $apiId = $this->upstreamProduct['supplier_id'];

        // $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/view",[],30,'GET');
		$res = '';
        return $res;
	}

	/**
	 * 时间 2022-05-30
	 * @title 前台商品配置页面
	 * @desc 前台商品配置输出,购物车,单独订购,升降级商品
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 产品模型
	 * @return  string
	 */
	public function clientProductConfigOption(ProductModel $ProductModel, $tag = ''): string
	{
		$res = '';
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'clientProductConfigOption')){
				// 获取模块通用参数
				$res = call_user_func([$ImportModule, 'clientProductConfigOption'], ['product'=>$ProductModel, 'tag'=>$tag]);
				$res = $this->formatTemplate($res);
			}
		}
		return $res;
        // $apiId = $this->upstreamProduct['supplier_id'];

        // $upstreamProductId = $this->upstreamProduct['upstream_product_id'];

        // $res = idcsmart_api_curl($apiId,"/console/v1/product/{$upstreamProductId}/config_option",['tag'=>$tag],30,'GET');
        // if ($res['status']==200){
        //     return $res['data']['content']??'';
        // }

        // return '';
	}

	/**
	 * 时间 2022-05-31
	 * @title 后台商品配置页面
	 * @desc 后台商品配置输出,新建订单,升降级商品
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 产品模型
	 * @return  string
	 */
	/*public function adminProductConfigOption(ProductModel $ProductModel, $tag = ''): string
	{
        $apiId = $this->upstreamProduct['supplier_id'];

        $upstreamProductId = $this->upstreamProduct['upstream_product_id'];

        $res = idcsmart_api_curl($apiId,"/console/v1/product/{$upstreamProductId}/config_option",['tag'=>$tag],30,'GET');

        return $res;
	}*/

	/**
	 * 时间 2022-05-31
	 * @title 前台产品升降级配置输出
	 * @desc 前台产品升降级配置输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	/*public function clientChangeConfigOption(HostModel $HostModel): string
	{
		$res = '';
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'clientChangeConfigOption')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'clientChangeConfigOption'], $params);
				$res = $this->formatTemplate($res);
			}
		}
		return $res;
	}*/

	/**
	 * 时间 2022-05-31
	 * @title 后台产品升降级配置输出(暂时未用)
	 * @desc 后台产品升降级配置输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	// public function adminChangeConfigOption(HostModel $HostModel): string
	// {
	// 	$res = '';
	// 	if($ImportModule = $this->importModule()){
	// 		if(method_exists($ImportModule, 'adminChangeConfigOption')){
	// 			// 获取模块通用参数
	// 			$params = $HostModel->getModuleParams();
	// 			$res = call_user_func([$ImportModule, 'adminChangeConfigOption'], $params);
	// 			$res = $this->formatTemplate($res);
	// 		}
	// 	}
	// 	return $res;
	// }

	/**
	 * 时间 2022-05-31
	 * @title 升降级配置项计算价格(暂时未用)
	 * @desc 升降级配置项计算价格
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @param   array params - 产品模型
	 * @return  array
	 */
	// public function changeConfigOptionCalculatePrice(HostModel $HostModel, $params): array
	// {
	// 	$result = [];
	// 	if($ImportModule = $this->importModule()){
	// 		if(method_exists($ImportModule, 'changeConfigOptionCalculatePrice')){
	// 			// 获取模块通用参数
	// 			$result = call_user_func([$ImportModule, 'changeConfigOptionCalculatePrice'], ['host'=>$HostModel, 'product'=>ProductModel::find($HostModel['product_id']), 'custom'=>$params]);
	// 			// TODO 是否判断返回/格式化

	// 		}
	// 	}
	// 	if(empty($result)){
	// 		$result = [
	// 			'status'=>400,
	// 			'msg'=>lang('module_file_is_not_exist'),
	// 		];
	// 	}
	// 	return $result;
	// }

	/**
	 * 时间 2022-05-30
	 * @title 在结算之后调用
	 * @desc 在结算之后调用,通常是验证参数,并保存参数
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 商品模型
	 * @param   int hostId - 产品ID
	 * @param   array params - 自定义参数
	 */
	public function afterSettle($ProductModel, $hostId, $params): void
	{
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'afterSettle')){
				call_user_func([$ImportModule, 'afterSettle'], ['product'=>$ProductModel, 'host_id'=>$hostId, 'custom'=>$params]);
			}
		}
	}

	/**
	 * 时间 2022-05-16
	 * @title 自定义后台方法
	 * @desc 自定义后台方法
	 * @author hh
	 * @version v1
	 * @param   string module - 模块名称
	 * @return  mixed params - 自定义参数
	 */
	// public function customAdminFunction($module, $params)
	// {
	// 	$res = [];
	// 	// 验证模块格式是否正确
	// 	if(!$this->checkModule($module)){
	// 		$res['status'] = 400;
	// 		$res['msg'] = '模块格式错误';
	// 		return json($res);
	// 	}
	// 	$controller = $params['controller'] ?? '';
	// 	$method = $params['method'] ?? '';
	// 	if(empty($controller) || empty($method)){
	// 		$res['status'] = 400;
	// 		$res['msg'] = '模块格式错误';
	// 		return json($res);
	// 	}
	// 	$controller = parse_name($controller.'_controller', 1);
	// 	$method = parse_name($method, 1, false);

	// 	$class = '\reserver\\'.$module.'\\controller\\admin\\'.$controller;
	// 	if(class_exists($class)){
	// 		$class = new $class();

	// 		if(method_exists($class, $method)){
	// 			$res = call_user_func([$class, $method], $params);
	// 		}else{
	// 			$res['status'] = 400;
	// 			$res['msg'] = '模块或方法不存在';
	// 			$res = json($res);
	// 		}
	// 	}else{
	// 		$res['status'] = 400;
	// 		$res['msg'] = '模块或方法不存在';
	// 		$res = json($res);
	// 	}
	// 	// if($this->importModule($module)){
	// 	// 	// 执行模块操作
	// 	// 	$func = $module . '_CustomAdminFunction';
	// 	// 	if(function_exists($func)){
	// 	// 		$res = call_user_func($func, $params);
	// 	// 		$res = $this->formatResult($res);
	// 	// 	}
	// 	// }
	// 	// if(empty($res)){
	// 	// 	$res['status'] = 400;
	// 	// 	$res['msg'] = '模块或方法不存在';
	// 	// }
	// 	return $res;
	// }

	/**
	 * 时间 2022-06-08
	 * @title 自定义前台方法
	 * @desc 自定义前台方法
	 * @author hh
	 * @version v1
	 * @param   string $module - 模块名称
	 * @param   mixed $params - POST的其他参数
	 * @return  array
	 */
	// public function customClientFunction($module, $params)
	// {
	// 	$res = [];
	// 	// 验证模块格式是否正确
	// 	if(!$this->checkModule($module)){
	// 		$res['status'] = 400;
	// 		$res['msg'] = '模块格式错误';
	// 		return json($res);
	// 	}
	// 	$controller = $params['controller'] ?? '';
	// 	$method = $params['method'] ?? '';
	// 	if(empty($controller) || empty($method)){
	// 		$res['status'] = 400;
	// 		$res['msg'] = '模块格式错误';
	// 		return json($res);
	// 	}
	// 	$controller = parse_name($controller.'_controller', 1);
	// 	$method = parse_name($method, 1, false);

	// 	$class = '\reserver\\'.$module.'\\controller\\home\\'.$controller;
	// 	if(class_exists($class)){
	// 		$class = new $class();

	// 		if(method_exists($class, $method)){
	// 			$res = call_user_func([$class, $method], $params);
	// 		}else{
	// 			$res['status'] = 400;
	// 			$res['msg'] = '模块或方法不存在';
	// 			$res = json($res);
	// 		}
	// 	}else{
	// 		$res['status'] = 400;
	// 		$res['msg'] = '模块或方法不存在';
	// 		$res = json($res);
	// 	}
	// 	return $res;
	// }

	/**
	 * 时间 2022-06-02
	 * @title 获取当前产品所有周期价格
	 * @desc 获取当前产品所有周期价格
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data - 数据
	 * @return  float data[].price - 金额
	 * @return  string data[].billing_cycle - 周期名称
	 * @return  int data[].duration - 周期时长(秒)
	 */
	public function durationPrice(HostModel $HostModel)
	{
	    $id = $HostModel['id'];

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        if (empty($upstreamHost)) {
            return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        }

        $apiId = $this->upstreamProduct['supplier_id'];

        $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/renew",[],30,'GET');

        if ($res['status']==200){
            $result = [
                'status' => 200,
                'msg' => $res['msg'],
                'data' => [
                ]
            ];
            foreach ($res['data']['host'] as $item){
            	$item['profit'] = bcmul($item['price'], ($this->upstreamProduct['profit_percent']/100), 2);
                $item['price'] = bcadd($item['price'], $item['profit'], 2);
                $result['data'][] = $item;
            }
            return $result;
        }

        return $res;
	}

	/**
	 * 时间 2022-06-16
	 * @title 获取商品所有配置项
	 * @desc 获取商品所有配置项
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 商品模型
	 * @return  array
	 */
	/*public function allConfigOption(ProductModel $ProductModel)
    {
        $apiId = $this->upstreamProduct['supplier_id'];

        $upstreamProductId = $this->upstreamProduct['upstream_product_id'];

        $res = idcsmart_api_curl($apiId,"/api/v1/product/{$upstreamProductId}/all_config_option",[],30,'GET');

        return $res;
	}*/

	/**
	 * 时间 2022-08-04
	 * @title 获取当前产品配置项
	 * @desc 获取当前产品配置项
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data - 数据
	 */
	/*public function currentConfigOption(HostModel $HostModel)
	{
		$res = [];
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'currentConfigOption')){
				// 获取模块通用参数
				$moduleParams = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'currentConfigOption'], $moduleParams);
				// TODO 验证返回
			}
		}
		// if(empty($res)){
		// 	$res = ['status'=>400, 'msg'=>'module_file_is_not_exist'];
		// }
		return $res;
	}*/

	/**
	 * 时间 2023-01-30
	 * @title 获取商品最低周期价格
	 * @desc 获取商品最低周期价格
	 * @author hh
	 * @version v1
	 * @param   int productId - 商品ID
	 * @return  float price - 价格
	 * @return  string cycle - 周期
	 * @return  ProductModel product - ProductModel实例
	 */
	// public function getPriceCycle($productId)
	// {
	// 	$res = [
	// 		'price' => null,
	// 		'cycle' => null
	// 	];
	// 	$ProductModel = ProductModel::findOrEmpty($productId);

	// 	$module = $ProductModel->getModule();
	// 	if($ImportModule = $this->importModule($module)){
	// 		if(method_exists($ImportModule, 'getPriceCycle')){
	// 			$moduleRes = call_user_func([$ImportModule, 'getPriceCycle'], ['product'=>$ProductModel]);
	// 			if(isset($moduleRes['price']) && is_numeric($moduleRes['price'])){
	// 				$res['price'] = $moduleRes['price'];
	// 			}
	// 			if(isset($moduleRes['cycle'])){
	// 				$res['cycle'] = $moduleRes['cycle'];
	// 			}
	// 		}
	// 	}
	// 	$res['product'] = $ProductModel;
	// 	return $res;
	// }

	
	public function downloadResource($ProductModel){
		$apiId = $this->upstreamProduct['supplier_id'];

        $res = idcsmart_api_curl($apiId, sprintf('api/v1/product/%d/resource', $this->upstreamProduct['upstream_product_id']), [] ,30, 'GET');
        return $res;
	}




	/**
	 * 时间 2022-06-08
	 * @title 验证模块名称是否正确
	 * @desc 验证模块名称是否正确
	 * @author hh
	 * @version v1
	 * @param   string $module - 模块名称
	 * @return  bool
	 */
	protected function checkModule($module){
		return (bool)preg_match('/^[a-z][a-z0-9_]{0,99}$/', $module);
	}

	/**
	 * 时间 2022-05-16
	 * @title 引入商品模块文件
	 * @desc 引入商品模块文件
	 * @author hh
	 * @version v1
	 * @param   string module - 模块类型
	 * @return  bool|object - - false=没有对应类,object=成功实例化模块类
	 */
	protected function importModule($module = null)
	{
		$module = $module ?? $this->upstreamProduct['res_module'];
		if(!empty($module)){
			$className = parse_name($module, 1);

			$class = '\reserver\\'.$module.'\\'.$className;

			if(class_exists($class)){
				return new $class();
			}
		}
		return false;
	}

	/**
	 * 时间 2022-05-26
	 * @title 格式化文本返回
	 * @desc 格式化文本返回
	 * @author hh
	 * @version v1
	 * @param   string $module 模块名称
	 * @param   mixed  $res    模块返回
	 * @return  string
	 */
	private function formatTemplate($res = null): string
	{
		$html = '';
		$module = $module ?? $this->upstreamProduct['res_module'];
		if(is_array($res)){
			// 认为是使用模板的方式来输出内容,格式大概如下
			// [
			// 	   'template'=>'abc.html',
			// 	   'vars'=>[
			// 	  		'aaaa'=>'bbb'
			// 	   ]
			// ]
			$template_file = $this->path . $module . '/' . $res['template'];
			if(file_exists($template_file)){
				$PluginModel=new PluginModel();
              	$addons = $PluginModel->plugins('addon')['list'];

              	$vars = isset($res['vars']) && !empty($res['vars']) && is_array($res['vars']) ? $res['vars'] : [];
				$vars['addons'] = $addons;

				View::assign($vars);
				// 调用方法变量
				$html = View::fetch($template_file);
			}else{
				$html = lang('module_cannot_find_template_file');
			}
		}else if(is_string($res)){
			$html = $res;
		}else{
			$html = (string)$res;
		}
		return $html;
	}

	/**
	 * 时间 2022-05-13
	 * @title 格式化系统操作返回
	 * @desc 格式化系统操作返回
	 * @author hh
	 * @version v1
	 * @param  mixed res - 操作返回 required
	 * @param  string successMsg - 成功返回没有提示信息时,会用该信息提示
	 * @param  string failMsg - 失败返回没有提示信息时,会用该信息提示
	 * @return  array
	 */
	private function formatResult($res, $successMsg = '', $failMsg = ''): array
	{
		$result = [];
		// 不兼容原来的老模块写法,都必须按标准返回
		if(is_array($res)){
			$result = $res;
			
			if($result['status'] === 400){
				$result['msg'] = $result['msg'] ?? ($failMsg ?: lang('module_operate_fail'));
			}else if($result['status'] === 200){
				$result['msg'] = $result['msg'] ?? ($successMsg ?: lang('module_operate_success'));
			}else{
				$result = [];
				$result['status'] = 400;
				$result['msg'] = lang('module_res_format_error');
			}
		}else{
			$result = [];
			$result['status'] = 400;
			$result['msg'] = lang('module_res_format_error');
			// 原模块返回判断(废弃)
			// if($res === null || $res == 'success' || $res == 'ok'){
			// 	$result['status'] = 200;
			// 	$result['msg'] = '操作成功';
			// }else{
			// 	$result['status'] = 400;
			// 	$result['msg'] = (string)$res;
			// }
		}
		return $result;
	}


}



