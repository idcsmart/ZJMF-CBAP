<?php
namespace server\idcsmart_cloud_ip\controller\admin;

use server\idcsmart_cloud_ip\model\DurationPriceModel;
use server\idcsmart_cloud_ip\validate\DurationPriceValidate;

/**
 * @title 魔方云IP配置周期价格
 * @desc 魔方云IP配置周期价格
 * @use server\idcsmart_cloud_ip\controller\admin\DurationPriceController
 */
class DurationPriceController{

	/**
	 * 时间 2022-06-17
	 * @title 周期价格显示
	 * @desc 周期价格显示
	 * @url /admin/v1/idcsmart_cloud_ip/duration_price
	 * @method  GET
	 * @author theworld
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  array list - 列表数据
     * @return  int list[].id - 周期价格ID
     * @return  int list[].duration - 时长(天)
     * @return  string list[].display_name - 时长显示名称
     * @return  float list[].ip_ratio - IP比例
     * @return  float list[].bw_ratio - 带宽比例
	 */
	public function list()
	{
		$param = request()->param();

		$DurationPriceModel = new DurationPriceModel();

		$data = $DurationPriceModel->durationPriceList($param);

		$result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data
        ];
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存周期价格
	 * @desc 保存周期价格
	 * @url /admin/v1/idcsmart_cloud_ip/duration_price
	 * @method  PUT
	 * @author theworld
	 * @version v1
     * @param array data - 所有周期价格数据 require
     * @param int data[].id - 周期价格ID require
     * @param float data[].ip_ratio - IP比例 require
     * @param float data[].bw_ratio - 带宽比例 require
	 */
	public function save()
	{
		$param = request()->param();

		$DurationPriceValidate = new DurationPriceValidate();

		if(!isset($param['data']) || empty($param['data'])){
			return ['status'=>400, 'msg'=>lang_plugins('参数错误')];
		}
		foreach($param['data'] as $v){
			if (!$DurationPriceValidate->scene('save')->check($v)){
	            return json(['status' => 400 , 'msg' => lang_plugins($DurationPriceValidate->getError())]);
	        }
		}
		$DurationPriceModel = new DurationPriceModel();

		$result = $DurationPriceModel->saveDurationPrice($param);
		return json($result);
	}



}