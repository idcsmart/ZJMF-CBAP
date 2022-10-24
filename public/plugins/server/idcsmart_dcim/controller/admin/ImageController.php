<?php
namespace server\idcsmart_dcim\controller\admin;

use server\idcsmart_dcim\model\ImageModel;
use server\idcsmart_dcim\validate\ImageValidate;
use server\idcsmart_dcim\logic\ImageLogic;

/**
 * @title DCIM镜像管理
 * @desc DCIM镜像管理
 * @use server\idcsmart_dcim\controller\admin\ImageController
 */
class ImageController{

	/**
	 * 时间 2022-06-21
	 * @title 镜像列表
	 * @desc 镜像列表
	 * @url /admin/v1/idcsmart_dcim/image
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   int image_group_id - 镜像分组ID
     * @return  array list - 列表数据
     * @return  int list[].id - 镜像ID
     * @return  string list[].name - 镜像名称
     * @return  int list[].enable - 是否启用(0=禁用,1=启用)
     * @return  int list[].charge - 是否付费(0=不付费,1=付费)
     * @return  int list[].image_group_id - 镜像分组ID
     * @return  string list[].image_group_name - 分组名称
     * @return  string list[].price - 价格
     * @return  int image_group[].id - 镜像分组ID
     * @return  string image_group[].name - 镜像分组名称
	 */
	public function list(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->imageList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-21
	 * @title 修改镜像
	 * @desc 修改镜像
	 * @url /admin/v1/idcsmart_dcim/image
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int [].id - 镜像ID require
	 * @param   int [].charge - 是否付费(0=不付费,1=付费)
	 * @param   float [].price - 金额
	 * @param   int [].enable - 是否启用(0=禁用,1=启用)
	 */
	public function batchSave(){
		$param = request()->param();

		if(empty($param) || !is_array($param)){
			return ['status'=>400, 'msg'=>lang_plugins('参数错误')];
		}
		$ImageValidate = new ImageValidate();

		foreach($param as $v){
			if (!$ImageValidate->scene('edit')->check($v)){
	            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
	        }
		}
		$ImageModel = new ImageModel();

		$result = $ImageModel->saveImage($param);
		return json($result);
	}

	/**
	 * 时间 2022-09-23
	 * @title 拉取操作系统
	 * @desc 拉取操作系统
	 * @url /admin/v1/idcsmart_dcim/image/sync
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID
	 */
	public function getImage(){
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->getProductImage($param['product_id'] ?? 0);
		return json($result);
	}



}


