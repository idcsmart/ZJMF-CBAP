<?php 
namespace reserver\mf_finance_common;

use app\common\model\SupplierModel;
use app\common\model\UpstreamProductModel;
use reserver\mf_finance_common\logic\RouteLogic;
use app\admin\model\PluginModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

/**
 * 对接魔方财务通用商品模块
 */
class MfFinanceCommon{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'魔方财务通用商品代理', 'version'=>'2.1.0'];
	}

    /**
     * 时间 2023-02-13
     * @title 升降级后调用
     * @author hh
     * @version v1
     */
    public function changePackage($params){
        $hostId = $params['host']['id'];

        $data = $params['custom']['configoption']??[];

        $RouteLogic = new \reserver\mf_finance_common\logic\RouteLogic();
        $RouteLogic->routeByHost($hostId);

        // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $hostId)->find();
        $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
        $HostModel = new HostModel();
        $host = $HostModel->find($hostId);
        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($RouteLogic->supplier_id);
        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id'])->find();
        if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
            $result = $RouteLogic->curl( "console/v1/mf_finance_common/{$upstreamHost['upstream_host_id']}/upgrade_config", [
            ], 'POST');
            // 生成订单ID，调支付
            if ($result['status']==200){
                $payData = [
                    'id' => $result['data']['id']??0,
                    'gateway' => 'credit'
                ];
                # 支付
                $result = $RouteLogic->curl('console/v1/pay',$payData,'POST');
            }
        }else{
            $postData = [
                'hid' => $RouteLogic->upstream_host_id,
                'configoption' => $data
            ];

            $result = $RouteLogic->curl( 'upgrade/upgrade_config_post', $postData,'POST');
            if ($result['status']==200){
                $result = $RouteLogic->curl( 'upgrade/checkout_config_upgrade', ['hid' => $RouteLogic->upstream_host_id],'POST');
                if ($result['status']==200){
                    // 使用余额支付
                    $creditData = [
                        'invoiceid' => $result['data']['invoiceid'],
                        'use_credit' => 1,
                        'enough' =>1
                    ];
                    $result = $RouteLogic->curl( 'apply_credit', $creditData,'POST');
                    if($result['status'] == 1001){
                        $result['status'] = 200;
                    }else if($result['status'] == 200){
                        $result['status'] = 400;
                    }
                }
            }
        }

        return $result;
    }

    // 升降级产品后调用
    public function changeProduct($params){
        $hostId = $params['host']['id'];

        $RouteLogic = new \reserver\mf_finance_dcim\logic\RouteLogic();
        $RouteLogic->routeByHost($hostId);

        $postData = [
            'hid' => $RouteLogic->upstream_host_id,
            'pid' => $params['custom']['new_pid']??0,
            'billingcycle' => $params['custom']['cycle']??""
        ];

        $result = $RouteLogic->curl( 'upgrade/upgrade_product_post', $postData,'POST');
        if ($result['status']==200){
            // 结算
            $result = $RouteLogic->curl( 'upgrade/checkout_upgrade_product', ['hid' => $RouteLogic->upstream_host_id],'POST');
            if ($result['status']==200){
                // 使用余额支付
                $creditData = [
                    'invoiceid' => $result['data']['invoiceid'],
                    'use_credit' => 1,
                    'enough' =>1
                ];
                $result = $RouteLogic->curl( 'apply_credit', $creditData,'POST');
                if($result['status'] == 1001){
                    $result['status'] = 200;
                }else if($result['status'] == 200){
                    $result['status'] = 400;
                }
            }
        }

        return $result;
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
     * @param   int param.custom.data_center_id - 数据中心ID require
     * @param   int param.custom.package_id - 套餐ID require
     * @param   int param.custom.image_id - 镜像ID require
     * @param   string param.custom.password - 密码 和SSHKEYID一起2个之中必须传一个
     * @param   string param.custom.ssh_key_id - SSHKEYID 和密码一起2个之中必须传一个
	 */
	public function afterSettle($params){
		return true;
	}


}


