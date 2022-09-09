<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\DurationPriceModel;

/**
 * @title 配置周期价格
 * @desc 配置周期价格
 * @use server\idcsmart_cloud\controller\home\DurationPriceController
 */
class DurationPriceController{

	/**
	 * 时间 2022-06-22
	 * @title 获取配置周期价格
	 * @desc 获取配置周期价格
	 * @url /console/v1/idcsmart_cloud/duration_price
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int package_id - 套餐ID require
     * @param   int image_id 0 镜像ID
     * @param   int backup_enable 0 启用自动备份(0=不启用,1=启用)
     * @param   int panel_enable 0 启用独立控制面板(0=不启用,1=启用)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  int list[].id - 带宽类型ID
	 * @return  string list[].name - 名称
	 * @return  int list[].order - 排序
	 * @return  string list[].description - 描述
	 * @return  int count - 总条数
	 */
	public function getConfigDurationPrice(){
		$param = request()->param();
		
		$DurationPriceModel = new DurationPriceModel();

		$result = $DurationPriceModel->configDurationPrice($param);
		return json($result);
	}


}


