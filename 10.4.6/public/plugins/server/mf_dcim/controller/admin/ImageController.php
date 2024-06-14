<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\ImageGroupModel;
use server\mf_dcim\model\ImageModel;
use server\mf_dcim\validate\ImageGroupValidate;
use server\mf_dcim\validate\ImageValidate;

/**
 * @title DCIM(自定义配置)-操作系统
 * @desc DCIM(自定义配置)-操作系统
 * @use server\mf_dcim\controller\admin\ImageController
 */
class ImageController
{
	/**
	 * 时间 2023-02-01
	 * @title 添加操作系统分类
	 * @desc 添加操作系统分类
	 * @url /admin/v1/mf_dcim/image_group
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 分类名称 require
     * @param   string icon - 系统图标 require
     * @return  int id - 操作系统分类ID
	 */
	public function imageGroupCreate()
	{
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->imageGroupCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 操作系统分类列表
	 * @desc 操作系统分类列表
	 * @url /admin/v1/mf_dcim/image_group
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 操作系统分类ID
     * @return  string list[].name - 操作系统分类名称
     * @return  string list[].icon - 图标
     * @return  int count - 总条数
	 */
	public function imageGroupList()
	{
		$param = request()->param();

		$ImageGroupModel = new ImageGroupModel();

		$data = $ImageGroupModel->imageGroupList($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 修改操作系统分类
	 * @desc 修改操作系统分类
	 * @url /admin/v1/mf_dcim/image_group/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 操作系统分类ID require
     * @param   string name - 分类名称 require
     * @param   string icon - 系统图标 require
	 */
	public function imageGroupUpdate()
	{
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->imageGroupUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 删除操作系统分类
	 * @desc 删除操作系统分类
	 * @url /admin/v1/mf_dcim/image_group/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 操作系统分类ID require
	 */
	public function imageGroupDelete()
	{
		$param = request()->param();

		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->imageGroupDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2023-05-06
	 * @title 镜像分组排序
	 * @desc  镜像分组排序
	 * @url /admin/v1/mf_dcim/image_group/order
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   array image_group_order - 镜像分组ID(排好序的ID) require
	 */
	public function imageGroupOrder()
	{
		$param = request()->param();

		$ImageGroupValidate = new ImageGroupValidate();
		if (!$ImageGroupValidate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageGroupValidate->getError())]);
        }
		$ImageGroupModel = new ImageGroupModel();

		$result = $ImageGroupModel->imageGroupOrder($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 添加操作系统
	 * @desc 添加操作系统
	 * @url /admin/v1/mf_dcim/image
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int image_group_id - 操作系统分类ID require
     * @param   string name - 系统名称 require
     * @param   int charge - 是否收费(0=不收费,1=收费) require
     * @param   float price - 价格 requireIf,charge=1
     * @param   int enable - 是否可用(0=禁用,1=启用) require
     * @param   int rel_image_id - 操作系统ID require
     * @return  int id - 操作系统ID
	 */
	public function imageCreate()
	{
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }
		$ImageModel = new ImageModel();

		$result = $ImageModel->imageCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 操作系统列表
	 * @desc 操作系统列表
	 * @url /admin/v1/mf_dcim/image
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @param   int image_group_id - 搜索操作系统分类ID
     * @param   string keywords - 搜索:操作系统名称
     * @return  int list[].id - 操作系统分类ID
     * @return  int list[].image_group_id - 操作系统分类ID
     * @return  string list[].name - 操作系统名称
     * @return  int list[].charge - 是否收费(0=否,1=是)
     * @return  string list[].price - 价格
     * @return  int list[].enable - 是否启用(0=否,1=是)
     * @return  int list[].rel_image_id - 魔方云操作系统ID
     * @return  string list[].image_group_name - 操作系统分类名称
     * @return  string list[].icon - 操作系统分类图标
     * @return  int count - 总条数
	 */
	public function imageList()
	{
		$param = request()->param();

		$ImageModel = new ImageModel();

		$data = $ImageModel->imageList($param);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 修改操作系统
	 * @desc 修改操作系统
	 * @url /admin/v1/mf_dcim/image/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 操作系统ID require
     * @param   int image_group_id - 操作系统分类ID require
     * @param   string name - 系统名称 require
     * @param   int charge - 是否收费(0=不收费,1=收费) require
     * @param   float price - 价格 requireIf,charge=1
     * @param   int enable - 是否可用(0=禁用,1=启用) require
     * @param   int rel_image_id - 操作系统ID require
	 */
	public function imageUpdate()
	{
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }
		$ImageModel = new ImageModel();

		$result = $ImageModel->imageUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 删除操作系统
	 * @desc 删除操作系统
	 * @url /admin/v1/mf_dcim/image/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 操作系统ID require
	 */
	public function imageDelete()
	{
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->imageDelete((int)$param['id']);
		return json($result);
	}

	/**
	 * 时间 2022-09-23
	 * @title 拉取操作系统
	 * @desc 拉取操作系统
	 * @url /admin/v1/mf_dcim/image/sync
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 */
	public function imageSync()
	{
		$param = request()->param();

		$ImageModel = new ImageModel();

		$result = $ImageModel->imageSync($param['product_id'] ?? 0);
		return json($result);
	}

	/**
	 * 时间 2023-02-06
	 * @title 切换是否可用
	 * @desc 切换是否可用
	 * @url /admin/v1/mf_dcim/image/:id/enable
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 操作系统ID require
     * @param   int enable - 是否启用(0=禁用,1=启用) require
	 */
	public function toggleImageEnable()
	{
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('enable')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }
		$ImageModel = new ImageModel();

		$result = $ImageModel->toggleImageEnable($param);
		return json($result);
	}

	/**
	 * 时间 2024-04-30
	 * @title 批量删除操作系统
	 * @desc  批量删除操作系统
	 * @url /admin/v1/mf_cloud/image
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   array id - 操作系统ID require
	 */
	public function imageBatchDelete()
	{
		$param = request()->param();

		$ImageValidate = new ImageValidate();
		if (!$ImageValidate->scene('BatchDelete')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ImageValidate->getError())]);
        }
		$ImageModel = new ImageModel();

		$result = $ImageModel->imageBatchDelete($param['id']);
		return json($result);
	}





}