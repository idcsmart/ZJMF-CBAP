<?php 
namespace reserver\whmcs_dcim;

use think\facade\Db;
use reserver\whmcs_dcim\logic\RouteLogic;
use app\admin\model\PluginModel;
use app\common\model\UpstreamHostModel;
use app\common\model\HostModel;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

/**
 * DCIM模块
 */
class WhmcsDcim{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'DCIM代理(WHMCS)', 'version'=>'2.0.0'];
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params){
		$hostId = $params['host']['id'];
		$custom = $params['custom'];

		// 去掉代金券/优惠码参数
		if(isset($custom['param']['customfield'])){
			unset($custom['param']['customfield']);
		}

		if($custom['type'] == 'upgrade_common_config'){
			// 先在上游创建订单
			try{
				$RouteLogic = new RouteLogic();
				$RouteLogic->routeByHost($hostId);

				$result = $RouteLogic->curl('host_changePackage', $custom['param'], 'POST');
				if($result['status'] == 200){
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
		$custom = $params['custom'] ?? [];
        $hostId = $params['host_id'];
		UpstreamHostModel::where('host_id', $hostId)->update(['upstream_configoption'=>json_encode($params['custom'])]);
	}


}


