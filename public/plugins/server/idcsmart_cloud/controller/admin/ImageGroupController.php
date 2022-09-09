<?php
namespace server\idcsmart_cloud\controller\admin;

use server\idcsmart_cloud\model\ImageGroupModel;
use server\idcsmart_cloud\validate\ImageGroupValidate;

/**
 * @title 镜像分组管理
 * @desc 镜像分组管理
 * @use server\idcsmart_cloud\controller\admin\ImageGroupController
 */
class ImageGroupController{

	/**
	 * 时间 2022-06-20
	 * @title 创建镜像分组
	 * @desc 创建镜像分组
	 * @url /admin/v1/idcsmart_cloud/image_group
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   string name - 名称 require
	 * @param   int enable - 是否启用(0=不启用,1=启用) require
	 * @param   int order 0 排序
	 * @param   string description - 描述
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  int data.id - 创建的镜像分组ID
	 */
	public function create(){
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->createImageGroup($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 镜像分组列表
	 * @desc 镜像分组列表
	 * @url /admin/v1/idcsmart_cloud/image_group
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 镜像分组ID
	 * @return  string data.list[].name - 名称
	 * @return  int data.list[].order - 排序
	 * @return  string data.list[].description - 描述
	 * @return  int data.list[].enable - 是否启用(0=禁用,1=启用)
	 * @return  int data.count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->imageGroupList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改镜像分组
	 * @desc 修改镜像分组
	 * @url /admin/v1/idcsmart_cloud/image_group/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像分组ID require
	 * @param   string name - 名称 require
	 * @param   int order - 排序
	 * @param   string description - 描述
	 * @param   int enable - 是否启用
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function update(){
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->updateImageGroup($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 删除镜像分组
	 * @desc 删除镜像分组
	 * @url /admin/v1/idcsmart_cloud/image_group/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像分组ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->deleteImageGroup((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改镜像分组排序
	 * @desc 修改镜像分组排序
	 * @url /admin/v1/idcsmart_cloud/image_group/:id/order
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像分组ID require
	 * @param   int order - 排序 require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateOrder(){
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->updateOrder($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改镜像分组是否启用
	 * @desc 修改镜像分组是否启用
	 * @url /admin/v1/idcsmart_cloud/image_group/:id/enable
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 镜像分组ID require
	 * @param   int enable - 是否启用(0=不启用,1=启用) require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function enable(){
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('enable')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->enable($param);
		return json($result);
	}



}


