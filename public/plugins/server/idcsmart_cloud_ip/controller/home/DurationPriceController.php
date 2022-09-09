<?php
namespace server\idcsmart_cloud_ip\controller\home;

use server\idcsmart_cloud_ip\model\DurationPriceModel;

/**
 * @title 魔方云IP配置周期价格
 * @desc 魔方云IP配置周期价格
 * @use server\idcsmart_cloud_ip\controller\home\DurationPriceController
 */
class DurationPriceController{

	/**
	 * 时间 2022-06-28
	 * @title 获取配置周期价格
	 * @desc 获取配置周期价格
	 * @url /console/v1/idcsmart_cloud_ip/duration_price
	 * @method  GET
	 * @author theworld
	 * @version v1
	 * @param   int package_id - 套餐ID require
     * @param   int size - 带宽大小
	 * @return  array list - 列表数据
	 * @return  int list[].id - 配置周期价格ID
	 * @return  string list[].name - 名称
	 * @return  int list[].order - 排序
	 * @return  string list[].description - 描述
	 * @return  int count - 总条数
	 */
	public function getConfigDurationPrice()
	{
		$param = request()->param();
		
		$DurationPriceModel = new DurationPriceModel();

		$data = $DurationPriceModel->configDurationPrice($param);

		$result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
		return json($result);
	}


}


