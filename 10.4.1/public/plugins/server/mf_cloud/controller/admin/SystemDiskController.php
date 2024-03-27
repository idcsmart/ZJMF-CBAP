<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\OptionModel;
use server\mf_cloud\model\DiskLimitModel;
use server\mf_cloud\validate\DiskValidate;
use server\mf_cloud\validate\DiskLimitValidate;

/**
 * @title 魔方云(自定义配置)-系统盘配置
 * @desc 魔方云(自定义配置)-系统盘配置
 * @use server\mf_cloud\controller\admin\SystemDiskController
 */
class SystemDiskController
{
	/**
	 * 时间 2023-01-31
	 * @title 添加系统盘配置
	 * @desc 添加系统盘配置
	 * @url /admin/v1/mf_cloud/system_disk
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int value - 容量(GB) requireIf,type=radio
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   string other_config.disk_type - 硬盘类型
     * @param   string other_config.store_id - 储存ID
     * @return  int id - 通用配置ID
	 */
	public function create()
	{
		$param = request()->param();

		$DiskValidate = new DiskValidate();
		if (!$DiskValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DiskValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::SYSTEM_DISK;

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 系统盘配置列表
	 * @desc 系统盘配置列表
	 * @url /admin/v1/mf_cloud/system_disk
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 周期ID
     * @return  int list[].value - 容量
     * @return  string list[].type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int list[].min_value - 最小值
     * @return  int list[].max_value - 最大值
     * @return  int list[].product_id - 商品ID
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  string list[].other_config.disk_type - 磁盘类型
     * @return  string list[].other_config.store_id - 储存ID
     * @return  int count - 总条数
	 */
	public function list()
	{
		$param = request()->param();

		$param['rel_type'] = OptionModel::SYSTEM_DISK;
        $param['rel_id'] = 0;
        $param['orderby'] = 'value,min_value';
        $param['sort'] = 'asc';

        $field = 'id,value,type,min_value,max_value,other_config';

		$OptionModel = new OptionModel();

		$data = $OptionModel->optionList($param, $field);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 修改系统盘配置
	 * @desc 修改系统盘配置
	 * @url /admin/v1/mf_cloud/system_disk/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 配置ID require
     * @param   int value - 容量(GB)
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   string other_config.disk_type - 硬盘类型
     * @param   string other_config.store_id - 储存ID
	 */
	public function update()
	{
		$param = request()->param();

		$DiskValidate = new DiskValidate();
		if (!$DiskValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DiskValidate->getError())]);
        }        
		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 删除系统盘配置
	 * @desc 删除系统盘配置
	 * @url /admin/v1/mf_cloud/system_disk/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 配置ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::SYSTEM_DISK);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 系统盘配置详情
	 * @desc 系统盘配置详情
	 * @url /admin/v1/mf_cloud/system_disk/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 配置ID require
     * @return  int id - 配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - 容量
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 步长
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.disk_type - 磁盘类型
     * @return  string other_config.store_id - 储存ID
	 */
	public function index()
	{
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->diskIndex((int)$param['id'], OptionModel::SYSTEM_DISK);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 添加系统盘性能限制
	 * @desc 添加系统盘性能限制
	 * @url /admin/v1/mf_cloud/system_disk_limit
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   int min_value - 最小值 require
     * @param   int max_value - 最大值 require
     * @param   int read_bytes - 随机读 require
     * @param   int write_bytes - 随机写 require
     * @param   int read_iops - IOPS读 require
     * @param   int write_iops - IOPS写 require
     * @return  int id - 性能限制ID
	 */
	public function diskLimitCreate()
	{
		$param = request()->param();

		$DiskLimitValidate = new DiskLimitValidate();
		if (!$DiskLimitValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DiskLimitValidate->getError())]);
        }
		$DiskLimitModel = new DiskLimitModel();

		$result = $DiskLimitModel->diskLimitCreate($param, DiskLimitModel::SYSTEM_DISK);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 系统盘性能限制列表
	 * @desc 系统盘性能限制列表
	 * @url /admin/v1/mf_cloud/system_disk_limit
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 性能限制ID
     * @return  int list[].min_value - 最小值
     * @return  int list[].max_value - 最大值
     * @return  int list[].read_bytes - 随机读
     * @return  int list[].write_bytes - 随机写
     * @return  int list[].read_iops - IOPS读
     * @return  int list[].write_iops - IOPS写
     * @return  int count - 总条数
	 */
	public function diskLimitList()
	{
		$param = request()->param();

		$DiskLimitModel = new DiskLimitModel();

		$data = $DiskLimitModel->diskLimitList($param, DiskLimitModel::SYSTEM_DISK);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 修改系统盘性能限制
	 * @desc 修改系统盘性能限制
	 * @url /admin/v1/mf_cloud/system_disk_limit/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 配置ID require
     * @param   int min_value - 最小值 require
     * @param   int max_value - 最大值 require
     * @param   int read_bytes - 随机读 require
     * @param   int write_bytes - 随机写 require
     * @param   int read_iops - IOPS读 require
     * @param   int write_iops - IOPS写 require
     * @return  int id - 性能限制ID
	 */
	public function diskLimitUpdate()
	{
		$param = request()->param();

		$DiskLimitValidate = new DiskLimitValidate();
		if (!$DiskLimitValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DiskLimitValidate->getError())]);
        }        
		$DiskLimitModel = new DiskLimitModel();

		$result = $DiskLimitModel->diskLimitUpdate($param, DiskLimitModel::SYSTEM_DISK);
		return json($result);
	}

	/**
	 * 时间 2023-02-01
	 * @title 删除系统盘性能限制
	 * @desc 删除系统盘性能限制
	 * @url /admin/v1/mf_cloud/system_disk_limit/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 性能限制ID require
	 */
	public function diskLimitDelete()
	{
		$param = request()->param();

		$DiskLimitModel = new DiskLimitModel();

		$result = $DiskLimitModel->diskLimitDelete((int)$param['id'], DiskLimitModel::SYSTEM_DISK);
		return json($result);
	}

	/**
	 * 时间 2023-02-08
	 * @title 获取系统盘类型
	 * @desc 获取系统盘类型
	 * @url /admin/v1/mf_cloud/system_disk/type
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  string list[].name - 名称
     * @return  string list[].value - 值
     * @return  int count - 总条数
	 */
	public function diskTypeList()
	{
		$param = request()->param();
		$param['rel_type'] = OptionModel::SYSTEM_DISK;

		$OptionModel = new OptionModel();

		$data = $OptionModel->getDiskType($param);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('message_success'),
			'data'	 => $data,
		];
		return json($result);
	}

}