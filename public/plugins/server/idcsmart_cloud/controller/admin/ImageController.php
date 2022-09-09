<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\ImageModel;
use server\idcsmart_cloud\validate\ImageValidate;
use server\idcsmart_cloud\logic\ImageLogic;

/**
 * @title 镜像管理
 * @desc 镜像管理
 * @use server\idcsmart_cloud\controller\admin\ImageController
 */
class ImageController{

	/**
	 * 时间 2022-06-21
	 * @title 镜像列表
	 * @desc 镜像列表
	 * @url /admin/v1/idcsmart_cloud/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   string image_type - 镜像类型(system=官方镜像,app=应用镜像)
	 * @param   string module_idcsmart_cloud_image_group_id - 镜像分组ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 镜像ID
	 * @return  string data.list[].name - 镜像名称
	 * @return  int data.list[].enable - 是否启用(0=禁用,1=启用)
	 * @return  int data.list[].charge - 是否付费(0=不付费,1=付费)
	 * @return  string data.list[].price - 价格
	 * @return  int data.list[].module_idcsmart_cloud_image_group_id - 镜像分组ID
	 * @return  string data.list[].icon - 图标
	 * @return  array data.list[].data_center - 镜像数据中心数据
	 * @return  int data.list[].data_center[].module_idcsmart_cloud_data_center_id - 数据中心ID
	 * @return  int data.list[].data_center[].enable - 是否启用(0=禁用,1=启用)
	 * @return  int data.list[].data_center[].is_exist - 是否存在(0=不存在,1=存在)
	 * @return  array data.data_center - 数据中心数据
	 * @return  int data.data_center[].id - 数据中心ID
	 * @return  string data.data_center[].country - 国家
	 * @return  string data.data_center[].city - 城市
	 * @return  string data.data_center[].area - 区域
	 */
	public function list(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->imageList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-21
	 * @title 修改镜像是否启用
	 * @desc 修改镜像是否启用
	 * @url /admin/v1/idcsmart_cloud/image/:id/enable
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像分组ID require
	 * @param   int enable - 是否启用(0=不启用,1=启用) require
	 * @param   int module_idcsmart_cloud_data_center_id 0 数据中心ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function enable(){
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('enable')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }
		$ImageModel = new ImageModel();

		$result = $ImageModel->enableImage($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-21
	 * @title 修改镜像
	 * @desc 修改镜像
	 * @url /admin/v1/idcsmart_cloud/image/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像ID require
	 * @param   int charge - 是否付费(0=不付费,1=付费)
	 * @param   float price - 金额
	 * @param   string icon - 图标(应用镜像生效)
	 */
	public function update(){
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }
		$ImageModel = new ImageModel();

		$result = $ImageModel->updateImage($param);
		return json($result);
	}

	/**
	 * 时间 2022-07-01
	 * @title 获取接口镜像
	 * @desc 获取接口镜像
	 * @url /admin/v1/idcsmart_cloud/image/sync
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID
	 */
	public function autoGetImage(){
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('sync')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }

		$result = ImageLogic::getProductImage($param['product_id'] ?? 0);
		return json($result);
	}

	/**
	 * 时间 2022-07-22
	 * @title 刷新镜像存在状态
	 * @desc 刷新镜像存在状态
	 * @url /admin/v1/idcsmart_cloud/image/status
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID
	 */
	public function refreshImageStatus(){
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('sync')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }

		$result = ImageLogic::refreshStatus($param['product_id'] ?? 0);
		return json($result);
	}

	/**
	 * 时间 2022-07-22
	 * @title 镜像对比列表
	 * @desc 镜像对比列表
	 * @url /admin/v1/idcsmart_cloud/image/compare
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int productId - 商品ID
	 * @return  string list[].server_param - 接口参数
	 * @return  int list[].server_id - 接口ID
	 * @return  string list[].server_name - 接口名称
	 * @return  int list[].data_center_id - 数据中心ID
	 * @return  string list[].country - 国家
	 * @return  string list[].city - 城市
	 * @return  string list[].area - 区域
	 * @return  int list[].system_image_num - 系统镜像数量
	 * @return  int list[].app_image_num - 应用镜像数量
	 * @return  int list[].image[].id - 镜像ID
	 * @return  string list[].image[].name - 镜像名称
	 * @return  int list[].image[].is_exist - 是否存在(0=不存在,1=存在)
	 */
	public function imageCompare(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->imageCompare($param['product_id'] ?? 0);
		return json($result);
	}



}


