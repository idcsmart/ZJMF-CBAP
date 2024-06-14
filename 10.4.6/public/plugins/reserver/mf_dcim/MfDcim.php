<?php 
namespace reserver\mf_dcim;

use reserver\mf_dcim\logic\RouteLogic;
use app\admin\model\PluginModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

/**
 * 魔方DCIM reserver模块
 */
class MfDcim
{
	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData()
	{
		return ['display_name'=>'DCIM代理(自定义配置)', 'version'=>'2.1.0'];
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params)
	{
		$hostId = $params['host']['id'];
		$custom = $params['custom'];

		// 去掉代金券/优惠码参数
		if(isset($custom['param']['customfield'])){
			unset($custom['param']['customfield']);
		}

		if($custom['type'] == 'buy_image'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/image/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result['status'] == 200){
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付
                        $result = $RouteLogic->curl('/console/v1/pay', $payData, 'POST');
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'buy_ip'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/ip_num/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result['status'] == 200){
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付
                        $result = $RouteLogic->curl('/console/v1/pay', $payData, 'POST');
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else if($custom['type'] == 'upgrade_common_config'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl( sprintf('console/v1/remf_dcim/%d/common_config/order', $RouteLogic->upstream_host_id), $custom['param'], 'POST');
				if($result['status'] == 200){
					$creditData = [
                        'id' => $result['data']['id'] ,
                        'use' => 1
                    ];
                    # 使用余额
                    $result = $RouteLogic->curl('/console/v1/credit', $creditData, 'POST');
                    if ($result['status'] == 200){
                        $payData = [
                            'id' => $result['data']['id'],
                            'gateway' => 'credit'
                        ];
                        # 支付
                        $result = $RouteLogic->curl('/console/v1/pay', $payData, 'POST');
                        return $result;
                    }
                    return $result;
				}else{
					// 记录失败日志
					return $result;
				}
			}catch(\Exception $e){
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}
		return ['status'=>200];

	}

    /**
    * 时间 2024-05-20
    * @title 后台产品内页实例操作输出
    * @author hh
    * @version v1
    */
    public function adminAreaModuleOperate($param)
    {
        $res = [
        'template'=>'template/admin/module_operate.html',
        ];
        return $res;
    }

    /**
     * 时间 2022-06-29
     * @title 前台产品内页输出
     * @author hh
     * @version v1
     */
    public function clientArea()
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('clientarea_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_detail.html"
            ];
        }else{ // pc端
            $clientareaTheme = configuration('clientarea_theme');
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_detail.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_detail.html"
            ];
        }

        return $res;
    }

    /**
     * 时间 2022-10-13
     * @title 产品列表
     * @author hh
     * @version v1
     */
    public function hostList($param)
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('clientarea_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_list.html"
            ];
        }else{ // pc端
            $clientareaTheme = configuration('clientarea_theme');
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_list.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_list.html"
            ];
        }

        return $res;
    }

    /**
     * 时间 2022-10-13
     * @title 前台商品购买页面输出
     * @author hh
     * @version v1
     */
    public function clientProductConfigOption($param)
    {
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('cart_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/cart/mobile/{$mobileTheme}/goods.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/cart/{$type}/{$mobileTheme}/goods.html"
            ];
        }else{ // pc端
            $cartTheme = configuration('cart_theme');
            if (!file_exists(__DIR__."/template/cart/pc/{$cartTheme}/goods.html")){
                $cartTheme = "default";
            }
            $res = [
                'template' => "template/cart/pc/{$cartTheme}/goods.html"
            ];
        }

        return $res;
    }

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,增加验证
	 * @author hh
	 * @version v1
	 */
	public function afterSettle($params)
	{
		$RouteLogic = new RouteLogic();
		$RouteLogic->routeByProduct($params['product']['id']);

		$result = $RouteLogic->curl( sprintf('console/v1/product/%d/remf_dcim/validate_settle', $RouteLogic->upstream_product_id), ['custom'=>$params['custom']], 'POST');
		if($result['status'] != 200){
			throw new \Exception($result['msg']);
		}
		$hostData = [
            'client_notes' => $custom['notes'] ?? '',
        ];
        HostModel::where('id', $params['host_id'])->update($hostData);
        $hostId = $params['host_id']??0;
        $modify = false;
		// 处理custom下的参数,有些参数不能带到上游
		if(isset($custom['auto_renew']) && $custom['auto_renew'] == 1){
			$enableIdcsmartRenewAddon = PluginModel::where('name', 'IdcsmartRenew')->where('module', 'addon')->where('status',1)->find();
            if($enableIdcsmartRenewAddon && class_exists('addon\idcsmart_renew\model\IdcsmartRenewAutoModel')){
                IdcsmartRenewAutoModel::where('host_id', $hostId)->delete();
                IdcsmartRenewAutoModel::create([
                    'host_id' => $hostId,
                    'status'  => 1,
                ]);
            }
            unset($params['custom']['auto_renew']);
            $modify = true;
		}
		// 修改参数
		if($modify){
			UpstreamHostModel::where('host_id', $hostId)->update(['upstream_configoption'=>json_encode($params['custom'])]);
		}
	}


}


