<?php 
namespace reserver\mf_finance_dcim;

use reserver\mf_finance_dcim\logic\RouteLogic;
use app\admin\model\PluginModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

/**
 * 魔方财务模块
 */
class MfFinanceDcim{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'魔方财务DCIM代理', 'version'=>'2.0.0'];
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

        $RouteLogic = new \reserver\mf_finance_dcim\logic\RouteLogic();
        $RouteLogic->routeByHost($hostId);

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
	public function clientArea(){
		$res = [
			'template'=>'template/clientarea/product_detail.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 产品列表
	 * @author hh
	 * @version v1
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function hostList($params){
		$res = [
			'template'=>'template/clientarea/product_list.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 前台购买
	 * @author hh
	 * @version v1
	 * @param   string x       -             x
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function clientProductConfigOption($params){
		$res = [
			'template'=>'template/clientarea/goods.html',
		];

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


