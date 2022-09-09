<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\DurationPriceModel;
use server\idcsmart_cloud\validate\DurationPriceValidate;

/**
 * @title 周期价格管理
 * @desc 周期价格管理
 * @use server\idcsmart_cloud\controller\admin\DurationPriceController
 */
class DurationPriceController{

	/**
	 * 时间 2022-06-17
	 * @title 周期价格显示
	 * @desc 周期价格显示
	 * @url /admin/v1/idcsmart_cloud/duration_price
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 周期价格ID
     * @return  int data.list[].duration - 时长(天)
     * @return  string data.list[].display_name - 时长显示名称
     * @return  float data.list[].cal_ratio - 计算型号比例
     * @return  float data.list[].bw_ratio - 带宽比例
	 */
	public function list(){
		$param = request()->param();

		$DurationPriceModel = new DurationPriceModel();

		$result = $DurationPriceModel->durationPriceList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存周期价格
	 * @desc 保存周期价格
	 * @url /admin/v1/idcsmart_cloud/duration_price
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param array data - 所有周期价格数据 require
     * @param int   data[].id - 周期价格ID require
     * @param string   data[].display_name - 显示值
     * @param float   data[].cal_ratio - 计算型号比例
     * @param float   data[].bw_ratio - 带宽比例
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
	 */
	public function save(){
		$param = request()->param();

		$DurationPriceValidate = new DurationPriceValidate();

		if(!isset($param['data']) || empty($param['data'])){
			return ['status'=>400, 'msg'=>lang_plugins('param_error')];
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