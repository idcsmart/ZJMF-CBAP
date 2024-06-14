<?php 
namespace app\common\logic;

use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use app\admin\model\PluginModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamProductModel;
use think\facade\View;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;
use app\common\model\SelfDefinedFieldModel;

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

	private $localCycle = [
        'ontrial' =>'试用',
        'onetime' => '一次性',
        'hour' => '小时',
        'day' => '天',
        'monthly' => '月付',
        'quarterly' => '季付',
        'semiannually' => '半年付',
        'annually' => '年付',
        'biennially' => '两年付',
        'triennially' => '三年付',
        'fourly' => '四年付',
        'fively' => '五年付',
        'sixly' => '六年付',
        'sevenly' => '七年付',
        'eightly' => '八年付',
        'ninely' => '九年付',
        'tenly' => '十年付',
    ];

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
            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $selfDefinedField = $SelfDefinedFieldModel->getUpstreamSelfDefinedFieldValue([
                'host_id'   => $id,
                'type'      => $res['data']['supplier']['type'],
            ]);

        	if($res['data']['supplier']['type']=='whmcs'){
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
                $upstream_configoption = json_decode($upstreamHost['upstream_configoption'],true);
        		$param = [
        			'hostname' => $HostModel['name'],
                    'productid' => $this->upstreamProduct['upstream_product_id'],
                    'billingcycle' => $upstream_configoption['billingcycle'] ?? '',
                    'configoptions' => $upstream_configoption['configoption'] ?? [],
                    'customfields' => $selfDefinedField,  //$upstream_configoption['customfield'] ?? [],
                    'downstream_url' => $url,
                    'downstream_token' => $token,
                    'downstream_id' => $id,
                ];

                $res = idcsmart_api_curl($apiId,'host_addOrder',$param,30,'POST');
                if ($res['status']==200){
        	        if (!empty($res['data']['hosting_id'])){
                        $upstreamHost->save([
        	                'upstream_host_id' => $res['data']['hosting_id']
                        ]);
                    }
                }
        	}
        	elseif($res['data']['supplier']['type']=='finance'){ // 对接老财务

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
                    'downstream_id' => $id,
                ];

        	    $res = idcsmart_api_curl($apiId,'/cart/clear',$clearCartData);

        	    if ($res['status']==200){
        	        if (!empty($res['hostid'])){
                        $upstreamHost->save([
        	                'upstream_host_id' => $res['hostid']
                        ]);
                    }

        	        if (!empty($res['invoiceid'])){
                        $payData = [
                            'invoiceid' => $res['invoiceid'],
                            'use_credit' => 1,
                            'enough' => 1,
                            'downstream_url' => $url,
                            'downstream_token' => $token,
                            'downstream_id' => $id
                        ];

                        $res = idcsmart_api_curl($apiId,'/apply_credit',$payData);

                        if ($res['status']==1001){
                            $res['status'] = 200;
                            $hostId = $res['data']['hostid'][0]??0;
                            if ($hostId){
                                $upstreamHost->save([
                                    'upstream_host_id' => $hostId
                                ]);
                            }
                        }elseif ($res['status']==200){
                            $res['status']=400;
                        }
                    }else{

                        $upstreamHost['upstream_configoption'] = json_decode($upstreamHost['upstream_configoption'],true);
                        $cartData = [
        	                'pid' => $this->upstreamProduct['upstream_product_id'],
                            'billingcycle' => $upstreamHost['upstream_configoption']['cycle'] ?? '', // 周期
                            'host' => $upstreamHost['upstream_configoption']['host'] ?? '',
                            'password' => $upstreamHost['upstream_configoption']['password'] ?? '',
                            'currencyid' => 1,
                            'qty' => 1,
                            'configoption' => $upstreamHost['upstream_configoption']['configoption'] ?? [],
                            'customfield' => $selfDefinedField, //$upstreamHost['upstream_configoption']['customfield'] ?? [],
                        ];
                        $res = idcsmart_api_curl($apiId,'/cart/add_to_shop',$cartData);
                        if ($res['status']==200){
                            $settleCartData = [
                                'downstream_url' => $url,
                                'downstream_token' => $token,
                                'downstream_id' => $id,
                                /*'cart_data' => [
                                    'configoptions' => $cartData['configoption']
                                ]*/
                            ];
                            $res = idcsmart_api_curl($apiId,'/cart/settle',$settleCartData);

                            if ($res['status']==200){
                                if (isset($res['data']['hostid'][0]) && !empty($res['data']['hostid'][0])){

                                    $upstreamHost->update([
                                        'upstream_host_id' => intval($res['data']['hostid'][0]??0)
                                    ],['host_id'=>$id]);
                                    // 用这个会报错！！
                                    /*$upstreamHost->save([
                                        'upstream_host_id' => intval($res['data']['hostid'][0]??0)
                                    ]);*/
                                }

                                $invoiceId = intval($res['data']['invoiceid']??0);

                                $applyCreditData = [
                                    'invoiceid' => $invoiceId,
                                    'use_credit' => 1,
                                    'enough' => 0,
                                    'downstream_url' => $url,
                                    'downstream_token' => $token,
                                    'downstream_id' => $id
                                ];
                                $res = idcsmart_api_curl($apiId,'/apply_credit',$applyCreditData);

                                if ($res['status']==1001){
                                    $res['status']=200;
                                    if (isset($res['data']['hostid'][0]) && !empty($res['data']['hostid'][0])){
                                        $upstreamHost->update([
                                            'upstream_host_id' => intval($res['data']['hostid'][0]??0)
                                        ],['host_id'=>$id]);
                                    }
                                }else{
                                    $creditLimitData = [
                                        'invoiceid' => $invoiceId,
                                        'use_credit_limit' => 1,
                                        'enough' => 0,
                                        'downstream_url' => $url,
                                        'downstream_token' => $token,
                                        'downstream_id' => $id,
                                    ];
                                    $res = idcsmart_api_curl($apiId,'/apply_credit_limit',$creditLimitData);
                                    if ($res['status']==1001){
                                        $res['status']=200;
                                        if (isset($res['data']['hostid'][0]) && !empty($res['data']['hostid'][0])){
                                            $upstreamHost->update([
                                                'upstream_host_id' => intval($res['data']['hostid'][0]??0)
                                            ],['host_id'=>$id]);
                                        }
                                    }else{
                                        $creditNotEnough = [
                                            'invoiceid' => $invoiceId,
                                            'use_credit' => 0
                                        ];
                                        $msg = $res['msg'];
                                        $res = idcsmart_api_curl($apiId,'/apply_credit',$creditNotEnough);
                                        if ($res['status']!=200){
                                            $res = idcsmart_api_curl($apiId,'/apply_credit',$creditNotEnough);
                                        }
                                        $res['status'] = 400;
                                        $res['msg'] = $msg;

                                    }
                                }
                            }elseif ($res['status']==1001){
                                $res['status'] = 200;
                                if (isset($res['data']['hostid'][0]) && !empty($res['data']['hostid'][0])){
                                    $upstreamHost->update([
                                        'upstream_host_id' => intval($res['data']['hostid'][0]??0)
                                    ],['host_id'=>$id]);
                                }
                            }
                        }
                    }
                }
        	    elseif ($res['status']==400){
        	        if (!empty($res['hostid']) && $res['domainstatus']=='Active'){
        	            $upstreamHost->save([
                            'upstream_host_id' => $res['hostid'],
                        ]);
        	            $HostModel->save([
        	                'status' => 'Active'
                        ]);
                    }
                }
        	    if ($res['status']==400){
        	        $res['msg'] = '上游：' . $res['msg'];
                }

            }
        	else{
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
	                    $configoptions = json_decode($upstreamHost['upstream_configoption'],true);
                        if (isset($configoptions['configoption']['host'])){
                            unset($configoptions['configoption']['host']);
                        }
	                    $cartData = [
	                        'product_id'           => $this->upstreamProduct['upstream_product_id'],
	                        'qty'                  => 1,
	                        'config_options'       => $configoptions,
                            'self_defined_field'   => $selfDefinedField,
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
        $supplier = SupplierModel::find($apiId);
        if($supplier['type']=='whmcs'){
        	$suspendData = [
        		'serviceid' => $upstreamHost['upstream_host_id'],
	            'suspendreason' => $param['suspend_reason']??'代理商暂停'
	        ];

	        $res = idcsmart_api_curl($apiId,"service_suspend",$suspendData,30,'POST');
        }elseif ($supplier['type']=='finance'){
            $suspendData = [
                'id' => $upstreamHost['upstream_host_id'],
                'func' => 'suspend',
                'reason' => $param['suspend_reason']??'代理商暂停',
                'is_api' => 1
            ];
            $res = idcsmart_api_curl($apiId,"/provision/default",$suspendData,30,'POST');
        }
        else{
        	$suspendData = [
	            'suspend_type' => $param['suspend_type']??'downstream',
	            'suspend_reason' => $param['suspend_reason']??'代理商暂停'
	        ];

	        $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/module/suspend",$suspendData,30,'POST');
        }


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
        $supplier = SupplierModel::find($apiId);
        if($supplier['type']=='whmcs'){
        	$data = [
	            'serviceid' => $upstreamHost['upstream_host_id'],
	        ];

	        $res = idcsmart_api_curl($apiId,"service_unsuspend",$data,30,'POST');
        }elseif ($supplier['type']=='finance'){
            $unsuspendData = [
                'id' => $upstreamHost['upstream_host_id'],
                'func' => 'unsuspend',
                'is_api' => 1
            ];

            $res = idcsmart_api_curl($apiId,"/provision/default",$unsuspendData,30,'POST');
        }
        else{
        	$res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/module/unsuspend",[],30,'POST');
        }

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
        $supplier = SupplierModel::find($apiId);
        if($supplier['type']=='whmcs'){
        	$data = [
	            'serviceid' => $upstreamHost['upstream_host_id'],
	        ];

	        $res = idcsmart_api_curl($apiId,"service_terminate",$data,30,'POST');
        }elseif ($supplier['type']=='finance'){
            $terminateData = [
                'id' => $upstreamHost['upstream_host_id'],
                'type' => 'Immediate',
                'reason' => '代理商删除'
            ];

            $res = idcsmart_api_curl($apiId,"/host/cancel",$terminateData,30,'POST');
        }else{
	        // TODO
	        $terminateData = [
	            'host_id' => $upstreamHost['upstream_host_id'],
	            'suspend_reason' => '代理商删除',
	            'type' => 'Immediate'
	        ];

	        $res = idcsmart_api_curl($apiId,"/console/v1/refund",$terminateData,30,'POST');
	    }

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

        $supplier = SupplierModel::find($apiId);
        if($supplier['type']=='whmcs'){
            foreach ($this->localCycle as $k=>$v){
                if ($v==($HostModel['billing_cycle_name']??"")){
                    $cycle = $k;
                    break;
                }
            }
        	$data = [
	            'hosting_id' => $upstreamHost['upstream_host_id'],
	            'billingcycle' => $cycle??'',
	        ];

	        $res = idcsmart_api_curl($apiId,"host_renew",$data,30,'POST');
            if ($res['status']==200){
                HostModel::update([
                    'due_time' => strtotime($res['data']['nextduedate'])
                ], ['id' => $id]);
            }
        }
        elseif ($supplier['type']=='finance'){
            foreach ($this->localCycle as $k=>$v){
                if ($v==($HostModel['billing_cycle_name']??"")){
                    $cycle = $k;
                    break;
                }
            }
            $renewData = [
                'billingcycles' => $cycle??'',
                'hostid' => $upstreamHost['upstream_host_id']
            ];

            $res = idcsmart_api_curl($apiId,"/host/renew",$renewData,30,'POST');

            if ($res['status']==200){
                $applyCreditData = [
                    'invoiceid' => $res['data']['invoiceid'],
                    'use_credit' => 1
                ];

                $res = idcsmart_api_curl($apiId,"/apply_credit",$applyCreditData,30,'POST');
                if ($res['status']==1001){
                    $res['status'] = 200;
                }elseif ($res['status']==200){
                    $res['status'] = 400;
                }

            }

        }
        else{

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
	 public function changeProduct(HostModel $HostModel, $params)
	 {
	 	if($ImportModule = $this->importModule()){
	 		if(method_exists($ImportModule, 'changeProduct')){
	 			// 获取模块通用参数
	 			$moduleParams = $HostModel->getModuleParams();
	 			$moduleParams['custom'] = $params;
	 			$res = call_user_func([$ImportModule, 'changeProduct'], $moduleParams);
	 		}
	 	}
	 	// 不需要返回
	 }

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
	public function cartCalculatePrice($ProductModel, $params = [], $qty=1, $scene="cal_price",$create=false)
	{
        $apiId = $this->upstreamProduct['supplier_id'];

        if (isset($params['promo_code'])){
            unset($params['promo_code']);
        }
        if (isset($params['event_promotion'])){
            unset($params['event_promotion']);
        }

       	$supplier = SupplierModel::find($apiId);
        if($supplier['type']=='whmcs'){
        	if(in_array($ProductModel['pay_type'], ['onetime', 'free'])){
        		$params['cycle'] = 'monthly';
        	}
        	$data = [
	            'productid' => $this->upstreamProduct['upstream_product_id'],
	            'billingcycle' => $params['cycle']??'monthly',
	            'configoptions' => $params['configoption']??'',
	        ];

	        $res = idcsmart_api_curl($apiId,"product_price",$data,30,'POST');

	        if ($res['status']==200){
                // 计算汇率
                $res['data']['price'] = ($res['data']['price'] ?? 0) * $supplier['rate'];
                $res['data']['price_total'] = ($res['data']['price'] ?? 0) * $supplier['rate'];
                $res['data']['renew_price'] = ($res['data']['renew_price'] ?? 0) * $supplier['rate'];
                $res['data']['base_price'] = ($res['data']['base_price'] ?? 0) * $supplier['rate'];

                // 根据利润计算本地价格(到本地的基础价格，需要考虑利润为负的情况！)
                if ($this->upstreamProduct['profit_type']==1){
                    $profit = $res['data']['profit'] = bcadd(0, $this->upstreamProduct['profit_percent'], 2);
                    $res['data']['price'] = bcadd($res['data']['price'], $profit, 2)>0?bcadd($res['data']['price'], $profit, 2):0;
                    $res['data']['base_price'] = bcadd($res['data']['base_price'],$profit,2)>0?bcadd($res['data']['base_price'],$profit,2):0;
                    $res['data']['price_total'] = bcadd($res['data']['price_total'],$profit,2)>0?bcadd($res['data']['price_total'],$profit,2):0;
                }else{
                    $profitAndBasePercent = $res['data']['profit'] = bcadd(1,$this->upstreamProduct['profit_percent']/100,2);
                    $res['data']['price'] = bcmul($res['data']['price'],$profitAndBasePercent,2)>0?bcmul($res['data']['price'],$profitAndBasePercent,2):0;
                    $res['data']['base_price'] = bcmul($res['data']['base_price'],$profitAndBasePercent,2)>0?bcmul($res['data']['base_price'],$profitAndBasePercent,2):0;
                    $res['data']['price_total'] = bcmul($res['data']['price_total'],$profitAndBasePercent,2)>0?bcmul($res['data']['price_total'],$profitAndBasePercent,2):0;
                }
                // 续费利润(需要考虑利润为负的情况！)
                if ($this->upstreamProduct['renew_profit_type']==1){
                    $renewProfit = bcadd(0, $this->upstreamProduct['renew_profit_percent'], 2);
                    $res['data']['renew_price'] = bcadd($res['data']['renew_price'],$renewProfit,2)>0?bcadd($res['data']['renew_price'],$renewProfit,2):0;
                }else{
                    $renewProfitAndBasePercent = bcadd(1,$this->upstreamProduct['renew_profit_percent']/100,2);
                    $res['data']['renew_price'] = bcmul($res['data']['renew_price'],$renewProfitAndBasePercent,2)>0?bcmul($res['data']['renew_price'],$renewProfitAndBasePercent,2):0;
                }
	            // wyh 20240226 后端计算价格返回
	            $res['data']['price'] = bcmul($res['data']['price'],$qty,2);
	            $billingCycleArr = [
	            	'monthly' => '月付',
	            	'quarterly' => '季付',
	            	'semiannually' => '半年付',
	            	'annually' => '年付',
	            	'biennially' => '两年付',
	            	'triennially' => '三年付',
	            ];
	            $durationArr = [
	            	'monthly' => 30*24*3600,
	            	'quarterly' => 90*24*3600,
	            	'semiannually' => 180*24*3600,
	            	'annually' => 365*24*3600,
	            	'biennially' => 2*365*24*3600,
	            	'triennially' => 3*365*24*3600,
	            ];
	            $res['data']['billing_cycle'] = $billingCycleArr[$data['billingcycle']] ?? '';
                $res['data']['duration'] = $durationArr[$data['billingcycle']] ?? 0;
                if(in_array($ProductModel['pay_type'], ['onetime', 'free'])){
                    $res['data']['renew_price'] = 0;
                    $res['data']['billing_cycle'] = $ProductModel['pay_type'] == 'onetime' ? '一次性' : '免费';
                    $res['data']['duration'] = 0;
                }
                $fixedProfit = $this->upstreamProduct['profit_percent'];
                $description = '';
                foreach($res['data']['preview'] as $k=>&$item){
                    // 计算汇率
                    $price =  ($item['price'] ?? 0) * $supplier['rate'];
                    // 计算利润
                    $price = bcmul($price,$this->upstreamProduct['profit_type']==1?1:(1+$this->upstreamProduct['profit_percent']/100),2);
                    // 固定利润，且为负数，且配置项金额大于0
                    if ($this->upstreamProduct['profit_type']==1 && floatval($fixedProfit)<0 && floatval($price)>0){
                        if (abs(floatval($fixedProfit))>floatval($price)){
                            $fixedProfit = -bcsub(abs($fixedProfit),$price,2);
                            $price = 0; // 配置项为0
                        }else{
                            $price = bcsub($price,abs($fixedProfit),2);
                            $fixedProfit = 0;
                        }
                    }elseif ($this->upstreamProduct['profit_type']==1 && floatval($fixedProfit)>0){
                        // 固定利润为正，将所有金额叠加至周期金额
                        if ($k==count($res['data']['preview'])-1){
                            $price = bcadd($price,$fixedProfit,2);
                        }
                    }
                    $item['price'] = $price>0?$price:0;
                    $description .= $item['name'].': '.$item['value'].','.lang('price').':'.$item['price']."\r\n";
                }
	            $res['data']['description'] = $description;
	            // $res['data']['content'] = $description;
	        }
        }
        elseif ($supplier['type']=='finance'){

            // 调上游加入购物车 进行数据判断(注意，调开通需要清空购物车)
            if ($create){
                $addToShopData = [
                    'pid' => $this->upstreamProduct['upstream_product_id'],
                    'billingcycle' => $params['cycle']??'',
                    'qty' => $qty,
                    'configoption' => $params['configoption']??'',
                    'customfield' => $params['self_defined_field']??[],
                    'host' => $params['host']??'',
                    'password' => $params['password']??'',
                    'currencyid' => 1,
                    'is_api' => true
                ];

                $res = idcsmart_api_curl($apiId,"/cart/add_to_shop", $addToShopData,30,'POST');
                if ($res['status']!=200){
                    $res['status']=400;
                    return $res;
                }else{
                    idcsmart_api_curl($apiId,'/cart/clear',[]);//清空数据
                }
            }

            $getTotalData = [
                'pid' => $this->upstreamProduct['upstream_product_id'],
                'billingcycle' => $params['cycle']??'',
                'qty' => $qty,
                'configoption' => $params['configoption']??'',
                'customfield' => $params['self_defined_field']??[]
            ];

            $res = idcsmart_api_curl($apiId,"/cart/get_total", $getTotalData,30,'POST');

            if ($res['status']==200){
                // 计算汇率
                $res['products']['sale_total'] = ($res['products']['sale_total'] ?? 0) * $supplier['rate'];
                $res['products']['sale_signal_price'] = ($res['products']['sale_signal_price'] ?? 0) * $supplier['rate'];
                $res['products']['signal_price'] = ($res['products']['signal_price'] ?? 0) * $supplier['rate'];
                $res['products']['product_sale_setup_fee'] = ($res['products']['product_sale_setup_fee'] ?? 0) * $supplier['rate'];
                $res['products']['product_sale_price'] = ($res['products']['product_sale_price'] ?? 0) * $supplier['rate'];

                if (isset($res['products']['type']['type']) && !empty($res['products']['type']['type'])){
                    $renewPrice = $res['products']['sale_signal_price'];
                }else{
                    $renewPrice = $res['products']['signal_price'];
                }
                $basePrice = bcadd($res['products']['product_sale_setup_fee'],$res['products']['product_sale_price'],2);
                # 固定利润(可为负)
                if ($this->upstreamProduct['profit_type']==1){
                    $res['data']['profit'] = bcadd( 0, $this->upstreamProduct['profit_percent'], 2);
                    $res['data']['price'] = bcadd($res['products']['sale_total'], $res['data']['profit'], 2)>0?bcadd($res['products']['sale_total'], $res['data']['profit'], 2):0;
                    // wyh 20240202 金额显示问题
                    $res['data']['base_price'] = $basePrice;
                    $res['data']['price_total'] = $res['data']['price'];
                }else{
                    $res['data']['profit'] = bcmul($res['products']['sale_total'], ($this->upstreamProduct['profit_percent']/100), 2);
                    $res['data']['price'] = bcadd($res['products']['sale_total'], $res['data']['profit'], 2)>0?bcadd($res['products']['sale_total'], $res['data']['profit'], 2):0;
                    $res['data']['base_price'] = bcmul($basePrice,(1+$this->upstreamProduct['profit_percent']/100),2)>0?bcmul($basePrice,(1+$this->upstreamProduct['profit_percent']/100),2):0;
                    $res['data']['price_total'] = $res['data']['price'];
                }

                // 新增续费利润(利润可为负)
                if ($this->upstreamProduct['renew_profit_type']==1){
                    $res['data']['renew_price'] = bcadd($renewPrice,$this->upstreamProduct['renew_profit_percent'],2)>0?bcadd($renewPrice,$this->upstreamProduct['renew_profit_percent'],2):0;
                }else{
                    $res['data']['renew_price'] = bcmul($renewPrice,(1+$this->upstreamProduct['renew_profit_percent']/100),2)>0?bcmul($renewPrice,(1+$this->upstreamProduct['renew_profit_percent']/100),2):0;
                }

                $res['data']['billing_cycle'] = $res['products']['billingcycle_zh'];
                $res['data']['duration'] = $res['products']['duration']??0;
                $description = '';
                $alloption = $res['products']['child']??[];
                $preview = [];
                $fixedProfit = $this->upstreamProduct['profit_percent'];
                foreach ($alloption as $k=>$v){
                    // 计算汇率
                    $v['suboption_sale_price_total'] = ($v['suboption_sale_price_total'] ?? 0) * $supplier['rate'];
                    $price = bcmul($v['suboption_sale_price_total'],$this->upstreamProduct['profit_type']==1?1:(1+$this->upstreamProduct['profit_percent']/100),2);
                    // 固定利润，且为负数，且配置项金额大于0
                    if ($this->upstreamProduct['profit_type']==1 && floatval($fixedProfit)<0 && floatval($price)>0){
                        if (abs(floatval($fixedProfit))>floatval($price)){
                            $fixedProfit = -bcsub(abs($fixedProfit),$price,2);
                            $price = 0; // 配置项为0
                        }else{
                            $price = bcsub($price,abs($fixedProfit),2);
                            $fixedProfit = 0;
                        }
                    }
                    $price = $price>0?$price:0;
                    $preview[] = [
                        'name' => $v['option_name'],
                        'value' => $v['qty']??$v['suboption_name'],
                        'price' => $price,
                    ];
                    $description .= $v['option_name'].': '.($v['qty']??$v['suboption_name']).','.lang('price').':'.$price."\r\n";
                }
                // 代理老财务特有的，商品价格
                if ($this->upstreamProduct['profit_type']==1){
                    $res['data']['base_price'] = bcadd($basePrice,$fixedProfit,2)>0?bcadd($basePrice,$fixedProfit,2):0;
                }
                $preview[] = [
                    'name' => lang("cloud_server_product_duration"),
                    'value' => $res['data']['billing_cycle'],
                    "price" => $res['data']['base_price']
                ];
                $res['data']['preview'] = $preview;
                $res['data']['description'] = $description;
                // $res['data']['content'] = $description;
                // 删除不必要返回
                unset($res['currency'],$res['products'],$res['is_aff']);
            }
        }
        else{
	        $upstreamProductId = $this->upstreamProduct['upstream_product_id'];
	        $res = idcsmart_api_curl($apiId,"/console/v1/product/{$upstreamProductId}/config_option?is_downstream=1", ['config_options'=>$params, 'qty'=>$qty],30,'POST');
	        if ($res['status']==200){
	            // 去掉不需要的返回
                if(isset($res['data']['discount'])){
                    unset($res['data']['discount']);
                }
                if(isset($res['data']['order_item'])){
                    unset($res['data']['order_item']);
                }
                // 计算汇率(到本地的基础价格)
                $res['data']['price'] = ($res['data']['price'] ?? 0) * $supplier['rate'];
                $res['data']['price_total'] = ($res['data']['price_total'] ?? 0) * $supplier['rate'];
                $res['data']['renew_price'] = ($res['data']['renew_price'] ?? 0) * $supplier['rate'];
                $res['data']['base_price'] = ($res['data']['base_price'] ?? 0) * $supplier['rate'];
                // 根据利润计算本地价格(到本地的基础价格，需要考虑利润为负的情况！)
                if ($this->upstreamProduct['profit_type']==1){
                    // 利润数据，返回给购物车使用
                    $profit = $res['data']['profit'] = bcadd(0, $this->upstreamProduct['profit_percent'], 2);
                    $res['data']['price'] = bcadd($res['data']['price'], $profit, 2)>0?bcadd($res['data']['price'], $profit, 2):0;
                    $res['data']['base_price'] = bcadd($res['data']['base_price'],$profit,2)>0?bcadd($res['data']['base_price'],$profit,2):0;
                    $res['data']['price_total'] = bcadd($res['data']['price_total'],$profit,2)>0?bcadd($res['data']['price_total'],$profit,2):0;
                }else{
                    $profitAndBasePercent = $res['data']['profit'] = bcadd(1,$this->upstreamProduct['profit_percent']/100,2);
                    $res['data']['price'] = bcmul($res['data']['price'],$profitAndBasePercent,2)>0?bcmul($res['data']['price'],$profitAndBasePercent,2):0;
                    $res['data']['base_price'] = bcmul($res['data']['base_price'],$profitAndBasePercent,2)>0?bcmul($res['data']['base_price'],$profitAndBasePercent,2):0;
                    $res['data']['price_total'] = bcmul($res['data']['price_total'],$profitAndBasePercent,2)>0?bcmul($res['data']['price_total'],$profitAndBasePercent,2):0;
                }
                // 续费利润(需要考虑利润为负的情况！)
                if ($this->upstreamProduct['renew_profit_type']==1){
                    $renewProfit = bcadd(0, $this->upstreamProduct['renew_profit_percent'], 2);
                    $res['data']['renew_price'] = bcadd($res['data']['renew_price'],$renewProfit,2)>0?bcadd($res['data']['renew_price'],$renewProfit,2):0;
                }else{
                    $renewProfitAndBasePercent = bcadd(1,$this->upstreamProduct['renew_profit_percent']/100,2);
                    $res['data']['renew_price'] = bcmul($res['data']['renew_price'],$renewProfitAndBasePercent,2)>0?bcmul($res['data']['renew_price'],$renewProfitAndBasePercent,2):0;
                }
                $fixedProfit = $this->upstreamProduct['profit_percent'];
                $description = '';
                foreach($res['data']['preview'] as $k=>&$item){
                    // 计算汇率
                    $price =  ($item['price'] ?? 0) * $supplier['rate'];
                    // 计算利润
                    $price = bcmul($price,$this->upstreamProduct['profit_type']==1?1:(1+$this->upstreamProduct['profit_percent']/100),2);
                    // 固定利润，且为负数，且配置项金额大于0
                    if ($this->upstreamProduct['profit_type']==1 && floatval($fixedProfit)<0 && floatval($price)>0){
                        if (abs(floatval($fixedProfit))>floatval($price)){
                            $fixedProfit = -bcsub(abs($fixedProfit),$price,2);
                            $price = 0; // 配置项为0
                        }else{
                            $price = bcsub($price,abs($fixedProfit),2);
                            $fixedProfit = 0;
                        }
                    }elseif ($this->upstreamProduct['profit_type']==1 && floatval($fixedProfit)>0){
                        // 固定利润为正，将所有金额叠加至周期金额
                        if ($k==count($res['data']['preview'])-1){
                            $price = bcadd($price,$fixedProfit,2);
                        }
                    }

                    $item['price'] = $price>0?$price:0;
                    $description .= $item['name'].': '.$item['value'].','.lang('price').':'.$item['price']."\r\n";

                }
                // 处理描述信息
                $res['data']['description'] = $description;
                // 处理内容(不确定是否有用)
                // $res['data']['content'] = $description;
	        }
	    }
        // WYH 20240521 多级代理情况(当请求来自下游时，返回折扣!)，所有代理统一处理客户等级折扣，需提供的数据保持一致
        $param = request()->param();
        if ($res['status']==200 && isset($param['is_downstream']) && $param['is_downstream']==1){
            $PluginModel = new PluginModel();
            $plugin = $PluginModel->where('status',1)->where('name','IdcsmartClientLevel')->find();
            if (!empty($plugin)){
                $IdcsmartClientLevelModel = new \addon\idcsmart_client_level\model\IdcsmartClientLevelModel();
                // 获取商品折扣金额
                $priceDiscount = $IdcsmartClientLevelModel->productDiscount([
                    'id' => $ProductModel['id'],
                    'amount' => $res['data']['price']
                ]);
                $priceTotalDiscount = $IdcsmartClientLevelModel->productDiscount([
                    'id' => $ProductModel['id'],
                    'amount' => $res['data']['price_total']
                ]);
                $basePriceDiscount = $IdcsmartClientLevelModel->productDiscount([
                    'id' => $ProductModel['id'],
                    'amount' => $res['data']['base_price']
                ]);
                $renewPriceDiscount = $IdcsmartClientLevelModel->productDiscount([
                    'id' => $ProductModel['id'],
                    'amount' => $res['data']['renew_price']
                ]);
                // 处理基础数据，二级代理及以下给下游的客户等级折扣数据(要么在这里减(这种方式更加合理)，要么下游去减)
                $res['data']['price'] = bcsub($res['data']['price'],$priceDiscount,2)>0?bcsub($res['data']['price'],$priceDiscount,2):0;
                $res['data']['base_price'] = bcsub($res['data']['base_price'],$basePriceDiscount,2)>0?bcsub($res['data']['base_price'],$basePriceDiscount,2):0;
                $res['data']['price_total'] = bcsub($res['data']['price_total'],$priceTotalDiscount,2)>0?bcsub($res['data']['price_total'],$priceTotalDiscount,2):0;
                $res['data']['renew_price'] = bcsub($res['data']['renew_price'],$renewPriceDiscount,2)>0?bcsub($res['data']['renew_price'],$renewPriceDiscount,2):0;
                // 处理预览数据(展示使用)，同样返回折扣后的数据到下游
                $description = '';
                foreach($res['data']['preview'] as &$item){
                    $previewPriceDiscount = $IdcsmartClientLevelModel->productDiscount([
                        'id' => $ProductModel['id'],
                        'amount' => $item['price']
                    ]);
                    $item['price'] = bcsub($item['price'],$previewPriceDiscount,2)>0?bcsub($item['price'],$previewPriceDiscount,2):0;
                    $description .= $item['name'].': '.$item['value'].','.lang('price').':'.$item['price']."\r\n";
                }
                // 处理描述信息
                $res['data']['description'] = $description;
                // 处理内容(不确定是否有用)
                // $res['data']['content'] = $description;
            }
        }

        return $res;
	}

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
	 * 时间 2022-05-30
	 * @title 在结算之后调用
	 * @desc 在结算之后调用,通常是验证参数,并保存参数
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 商品模型
	 * @param   int hostId - 产品ID
	 * @param   array params - 自定义参数
     * @param   array customfields - 其他自定义参数
     * @param   int position - 结算位置标识
	 */
	public function afterSettle($ProductModel, $hostId, $params,$customfields=[],$position=0): void
	{
		if($ImportModule = $this->importModule()){
			if(method_exists($ImportModule, 'afterSettle')){
				call_user_func([$ImportModule, 'afterSettle'], ['product'=>$ProductModel, 'host_id'=>$hostId, 'custom'=>$params,'customfields'=>$customfields,'position'=>$position]);
			}
		}
	}

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

        $supplier = SupplierModel::find($apiId);

        $formatZero = bcsub(0,0,2);

        if ($supplier['type']=='whmcs'){

            $data = [
                'hosting_id' => $upstreamHost['upstream_host_id'],
            ];

            $res = idcsmart_api_curl($apiId,"host_billingCycle",$data,30,'POST');
 
            if ($res['status']==200){
                $hosts = [];
                $cycles = $res['data']['cycle']??[];

                foreach ($cycles as $cycle){
                    // 计算汇率
                    $cycle['amount'] = $cycle['amount'] * $supplier['rate'];

                    if ($this->upstreamProduct['renew_profit_type']==1){
                        $price = bcadd($cycle['amount'], $this->upstreamProduct['renew_profit_percent'], 2)>0?bcadd($cycle['amount'], $this->upstreamProduct['renew_profit_percent'], 2):$formatZero;
                    }else{
                        $price = bcmul($cycle['amount'], bcadd(1,$this->upstreamProduct['renew_profit_percent']/100,2), 2)>0?bcmul($cycle['amount'], bcadd(1,$this->upstreamProduct['renew_profit_percent']/100,2), 2):$formatZero;
                    }
                    $hosts[] = [
                        'duration' => $cycle['duration']??0,
                        'price' => $price,
                        'billing_cycle' => $this->localCycle[$cycle['billingcycle']],
                        'base_price' => $price
                    ];
                }

                return [
                    'status' => 200,
                    'msg' => lang('success_message'),
                    'data' => $hosts
                ];

            }else{
                return $res;
            }

        }
        else if ($supplier['type']=='finance'){

            $renewData = [
                'hostid' => $upstreamHost['upstream_host_id'],
            ];

            $res = idcsmart_api_curl($apiId,"/host/renewpage",$renewData,30,'GET');
            if ($res['status']==200){
                $hosts = [];
                $cycles = $res['data']['cycle']??[];

                foreach ($cycles as $cycle){
                    // 计算汇率
                    $cycle['amount'] = $cycle['amount'] * $supplier['rate'];

                    if ($this->upstreamProduct['renew_profit_type']==1){
                        $price = bcadd($cycle['amount'], $this->upstreamProduct['renew_profit_percent'], 2)>0?bcadd($cycle['amount'], $this->upstreamProduct['renew_profit_percent'], 2):$formatZero;
                    }else{
                        $price = bcmul($cycle['amount'], bcadd(1,$this->upstreamProduct['renew_profit_percent']/100,2), 2)>0?bcmul($cycle['amount'], bcadd(1,$this->upstreamProduct['renew_profit_percent']/100,2), 2):$formatZero;
                    }
                    $hosts[] = [
                        'duration' => $cycle['duration']??0,
                        'price' => $price,
                        'billing_cycle' => $this->localCycle[$cycle['billingcycle']],
                        'base_price' => $price
                    ];
                }

                return [
                    'status' => 200,
                    'msg' => lang('success_message'),
                    'data' => $hosts
                ];

            }else{
                return $res;
            }

        }else{
            $res = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/renew",[],30,'GET');

            if ($res['status']==200){
                $result = [
                    'status' => 200,
                    'msg' => $res['msg'],
                    'data' => [
                    ]
                ];
                foreach ($res['data']['host'] as $item){
                    if(isset($item['client_level_discount'])){
                        unset($item['client_level_discount']);
                    }
                    // 计算汇率
                    $item['price'] = $item['price'] * $supplier['rate'];
                    $item['price_save'] = $item['price_save'] * $supplier['rate'];
                    
                    if ($this->upstreamProduct['renew_profit_type']==1){
                        $item['profit'] = bcadd(0, $this->upstreamProduct['renew_profit_percent'], 2);
                        $item['price'] = bcadd($item['price'], $item['profit'], 2)>0?bcadd($item['price'], $item['profit'], 2):$formatZero;
                        $item['base_price'] = bcadd($item['price_save'], $item['profit'], 2)>0?bcadd($item['price_save'], $item['profit'], 2):$formatZero;
                    }else{
                        $item['profit'] = bcmul($item['price'], bcadd($this->upstreamProduct['renew_profit_percent']/100,0,2), 2);
                        $item['price'] = bcadd($item['price'], $item['profit'], 2)>0?bcadd($item['price'], $item['profit'], 2):$formatZero;
                        $item['base_price'] = bcmul($item['price_save'], bcadd($this->upstreamProduct['renew_profit_percent']/100,1,2), 2)>0?bcmul($item['price_save'], bcadd($this->upstreamProduct['renew_profit_percent']/100,1,2), 2):$formatZero;

                    }
                    $result['data'][] = $item;
                }
                return $result;
            }

            return $res;
        }

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
	public function allConfigOption(ProductModel $ProductModel,$hostId=0)
    {
        $apiId = $this->upstreamProduct['supplier_id'];

        $supplier = SupplierModel::find($apiId);

        $upstreamProductId = $this->upstreamProduct['upstream_product_id'];

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $hostId)->find();
        $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
        if (!empty($supplier) && $supplier['type']=='default'){
            $res = idcsmart_api_curl($apiId,"/console/v1/refund",['host_id'=>$upstreamHost['upstream_host_id']],30,'GET');
            return  $res['data']['config_option']??[];
        }else{
            if ($supplier['type']=='finance'){
                $postData = [
                    'pid' => $upstreamProductId,
                    'billingcycle' => ''
                ];
                $res = idcsmart_api_curl($apiId,"/cart/set_config",$postData,30,'GET');
                if ($res['status']==200){
                    $options = $res['option']??[];
                    $data = [];
                    foreach ($options as $option){
                        if ($option['option_type']==1){
                            $suboptions = $option['sub']??[];
                            $subArr = [];
                            foreach ($suboptions as $suboption){
                                $subArr[] = [
                                    'name' => $suboption['option_name'],
                                    'value' => $suboption['id'],
                                ];
                            }
                            $data[] = [
                                'name' => $option['option_name'],
                                'field' => "configoption[{$option['id']}]",
                                'type' => 'dropdown',
                                'option' => $subArr
                            ];
                        }
                    }
                    $res = [
                        'status' => 200,
                        'msg' => $res['msg'],
                        'data' => $data
                    ];
                }
            }
        }

        return $res??[];
	}

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

	/**
     * 时间 2024-05-20
     * @title 后台产品内页实例操作输出
     * @desc  后台产品内页实例操作输出
     * @author hh
     * @version v1
     * @param   HostModel $HostModel - HostModel实例
     * @return  string
     */
    public function adminAreaModuleOperate(HostModel $HostModel): string
    {
        $res = '';
        if($ImportModule = $this->importModule()){
            if(method_exists($ImportModule, 'adminAreaModuleOperate')){
                // 获取模块通用参数
                $params = $HostModel->getModuleParams();
                $res = call_user_func([$ImportModule, 'adminAreaModuleOperate'], $params);
                $res = $this->formatTemplate($res);
            }
        }
        return $res;
    }

	public function downloadResource($ProductModel){
		$apiId = $this->upstreamProduct['supplier_id'];
        $supplier = SupplierModel::find($apiId);
        if ($supplier['type']=='whmcs'){
            $res = idcsmart_api_curl($apiId, 'product_detail', [] ,30, 'GET');
        } else if ($supplier['type']=='finance'){
            $res = idcsmart_api_curl($apiId, sprintf('api/product/%d/resource', $this->upstreamProduct['upstream_product_id']), [] ,30, 'GET');
        } else {
            $res = idcsmart_api_curl($apiId, sprintf('api/v1/product/%d/resource', $this->upstreamProduct['upstream_product_id']), [] ,30, 'GET');
        }

        return $res;
	}

    /**
     * 时间 2023-04-14
     * @title 产品内页模块输入框输出
     * @desc 产品内页模块输入框输出
     * @author hh
     * @version v1
     * @param   HostModel $HostModel - HostModel实例
     * @return  string data[].name - 名称
     * @return  string data[].key -  标识
     * @return  string data[].value - 当前值
     */
    public function adminField(HostModel $HostModel)
    {
        $data = [];
        if($ImportModule = $this->importModule()){
            if(method_exists($ImportModule, 'adminField')){
                // 获取模块通用参数
                $params = $HostModel->getModuleParams();
                $data = call_user_func([$ImportModule, 'adminField'], $params);
                // 格式化
                array_walk($data, function(&$value){
                    $value['value'] = (string)$value['value'];
                });
            }
        }
        $res = [
            'status' => 200,
            'msg'    => lang('message_success'),
            'data'   => $data,
        ];
        return $res;
    }

    public function syncAccount(HostModel $HostModel)
    {
        $id = $HostModel->id;

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
        if (empty($upstreamHost)) {
            return ['status' => 400, 'msg' => lang('upstream_host_is_not_exist')];
        }

        $apiId = $this->upstreamProduct['supplier_id'];
        $supplier = SupplierModel::find($apiId);
        $result = [];
        if($supplier['type']=='whmcs'){
            $res = idcsmart_api_curl($apiId,"/v1/hosts/{$upstreamHost['upstream_host_id']}",[],30,'GET');
            if ($res['status']==200){
                if ($res['data']['host_data']['domainstatus']=='Pending'){
                    $status = 'Pending';
                }elseif ($res['data']['host_data']['domainstatus']=='Active'){
                    $status = 'Active';
                }elseif ($res['data']['host_data']['domainstatus']=='Deleted'){
                    $status = 'Deleted';
                }elseif ($res['data']['host_data']['domainstatus']=='Failed'){
                    $status = 'Failed';
                }elseif ($res['data']['host_data']['domainstatus']=='Cancelled'){
                    $status = 'Cancelled';
                }elseif ($res['data']['host_data']['domainstatus']=='Suspended'){
                    $status = 'Suspended';
                }
                $result['status'] = 200;
                $result['msg'] = $res['msg']??lang('success_message');
                $result['data'] = [
                    'dedicate_ip' => $res['data']['dedicatedip']??$HostModel['dedicatedip'],
                    'assign_ip' => $res['data']['assignedips']??$HostModel['assignedips'],
                    'status' => $status??$HostModel['status'],
                    'name' => $res['data']['domain']??$HostModel['name'],
                ];
            }else{
                $result['status'] = 400;
                $result['msg'] = $res['msg']??lang('fail_message');
            }
        }elseif ($supplier['type']=='finance'){
            $data = [
                'host_id' => $upstreamHost['upstream_host_id'],
            ];
            $res = idcsmart_api_curl($apiId,"/host/header",$data,30,'GET');
            if ($res['status']==200){
                if ($res['data']['host_data']['domainstatus']=='Pending'){
                    $status = 'Pending';
                }elseif ($res['data']['host_data']['domainstatus']=='Active'){
                    $status = 'Active';
                }elseif ($res['data']['host_data']['domainstatus']=='Deleted'){
                    $status = 'Deleted';
                }elseif ($res['data']['host_data']['domainstatus']=='Failed'){
                    $status = 'Failed';
                }elseif ($res['data']['host_data']['domainstatus']=='Cancelled'){
                    $status = 'Cancelled';
                }elseif ($res['data']['host_data']['domainstatus']=='Suspended'){
                    $status = 'Suspended';
                }
                $result['status'] = 200;
                $result['msg'] = $res['msg']??lang('success_message');
                $result['data'] = [
                    'dedicate_ip' => $res['data']['host_data']['dedicatedip']??$HostModel['dedicatedip'],
                    'assign_ip' => isset($res['data']['host_data']['assignedips'])?implode(',',$res['data']['host_data']['assignedips']):$HostModel['assignedips'],
                    'status' => $status??$HostModel['status'],
                    'name' => $res['data']['host_data']['domain']??$HostModel['name'],
                ];
            }else{
                $result['status'] = 400;
                $result['msg'] = $res['msg']??lang('fail_message');
            }
        }else{
            $res1 = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}/ip",[],30,'GET');
            $res2 = idcsmart_api_curl($apiId,"/console/v1/host/{$upstreamHost['upstream_host_id']}",[],30,'GET');
            if ($res1['status']==200 && $res2['status']==200){
                $result['status'] = 200;
                $result['msg'] = lang('success_message');
                $result['data'] = [
                    'dedicate_ip' => $res1['data']['dedicate_ip']??$HostModel['dedicate_ip'],
                    'assign_ip' => $res1['data']['assign_ip']??$HostModel['assign_ip'],
                    'status' => $res2['data']['host']['status']??$HostModel['status'],
                    'name' => $res2['data']['host']['name']??$HostModel['name'],
                ];
            }else{
                $result['status'] = 400;
                $result['msg'] = lang('fail_message');
            }
        }

        return $result;
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



