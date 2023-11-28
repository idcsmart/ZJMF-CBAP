<?php
namespace server\mf_dcim\controller\admin;

use server\mf_dcim\model\OptionModel;
use server\mf_dcim\validate\CpuValidate;
use server\mf_dcim\validate\MemoryValidate;
use server\mf_dcim\validate\DiskValidate;

/**
 * @title DCIM(自定义配置)-硬件配置
 * @desc DCIM(自定义配置)-硬件配置
 * @use server\mf_dcim\controller\admin\OptionController
 */
class OptionController{

	/**
	 * 时间 2023-11-08
	 * @title 处理器配置列表
	 * @desc 处理器配置列表
	 * @url /admin/v1/mf_dcim/cpu
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 通用配置ID
     * @return  string list[].value - 处理器
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  int count - 总数量
	 */
	public function cpuList(){
		$param = request()->param();
		$param['rel_type'] = OptionModel::CPU;
		$field = 'id,value';

		$OptionModel = new OptionModel();

		$data = $OptionModel->optionList($param, $field);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 处理器配置详情
	 * @desc 处理器配置详情
	 * @url /admin/v1/mf_dcim/cpu/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 处理器
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 */
	public function cpuIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->cpuIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 添加处理器配置
	 * @desc 添加处理器配置
	 * @url /admin/v1/mf_dcim/cpu
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
     * @param   string value - 处理器
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 通用配置ID
	 */
	public function cpuCreate(){
		$param = request()->param();

		$CpuValidate = new CpuValidate();
		if (!$CpuValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CpuValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::CPU;
        $param['rel_id'] = 0;

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 修改处理器配置
	 * @desc 修改处理器配置
	 * @url /admin/v1/mf_dcim/cpu/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   string value - 处理器
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function cpuUpdate(){
		$param = request()->param();

		$CpuValidate = new CpuValidate();
		if (!$CpuValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($CpuValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 删除处理器配置
	 * @desc 删除处理器配置
	 * @url /admin/v1/mf_dcim/cpu/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function cpuDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::CPU);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 内存配置列表
	 * @desc 内存配置列表
	 * @url /admin/v1/mf_dcim/memory
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 通用配置ID
     * @return  string list[].value - 内存
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  int count - 总数量
	 */
	public function memoryList(){
		$param = request()->param();
		$param['rel_type'] = OptionModel::MEMORY;
		$param['orderby'] = 'order';
		$field = 'id,value';

		$OptionModel = new OptionModel();

		$data = $OptionModel->optionList($param, $field);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 内存配置详情
	 * @desc 内存配置详情
	 * @url /admin/v1/mf_dcim/memory/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 内存
     * @return  int order - 排序
     * @return  int other_config.memory_slot - 内存槽位
     * @return  int other_config.memory - 内存容量(GB)
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 */
	public function memoryIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->memoryIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 添加内存配置
	 * @desc 添加内存配置
	 * @url /admin/v1/mf_dcim/memory
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
     * @param   string value - 内存 require
     * @param   int order - 排序 require
     * @param   int other_config.memory_slot - 内存槽位 require
     * @param   int other_config.memory - 内存容量(GB) require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 通用配置ID
	 */
	public function memoryCreate(){
		$param = request()->param();

		$MemoryValidate = new MemoryValidate();
		if (!$MemoryValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($MemoryValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::MEMORY;
        $param['rel_id'] = 0;

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 修改内存配置
	 * @desc 修改内存配置
	 * @url /admin/v1/mf_dcim/memory/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   string value - 内存 require
     * @param   int order - 排序 require
     * @param   int other_config.memory_slot - 内存槽位 require
     * @param   int other_config.memory - 内存容量(GB) require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function memoryUpdate(){
		$param = request()->param();

		$MemoryValidate = new MemoryValidate();
		if (!$MemoryValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($MemoryValidate->getError())]);
        }

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 删除内存配置
	 * @desc 删除内存配置
	 * @url /admin/v1/mf_dcim/memory/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function memoryDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::MEMORY);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 硬盘配置列表
	 * @desc 硬盘配置列表
	 * @url /admin/v1/mf_dcim/disk
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID
     * @return  int list[].id - 通用配置ID
     * @return  string list[].value - 硬盘
     * @return  string list[].price - 价格
     * @return  string list[].duration - 周期
     * @return  int count - 总数量
	 */
	public function diskList(){
		$param = request()->param();
		$param['rel_type'] = OptionModel::DISK;
		$param['orderby'] = 'order';
		$field = 'id,value';

		$OptionModel = new OptionModel();

		$data = $OptionModel->optionList($param, $field);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 硬盘配置详情
	 * @desc 硬盘配置详情
	 * @url /admin/v1/mf_dcim/disk/:id
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string value - 硬盘
     * @return  int order - 排序
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
	 */
	public function diskIndex(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$data = $OptionModel->diskIndex((int)$param['id']);

		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'	 => $data,
		];
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 添加硬盘配置
	 * @desc 添加硬盘配置
	 * @url /admin/v1/mf_dcim/disk
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
     * @param   string value - 硬盘 require
     * @param   int order - 排序 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
     * @return  int id - 通用配置ID
	 */
	public function diskCreate(){
		$param = request()->param();

		$DiskValidate = new DiskValidate();
		if (!$DiskValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DiskValidate->getError())]);
        }
        $param['rel_type'] = OptionModel::DISK;
        $param['rel_id'] = 0;

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-11-08
	 * @title 修改硬盘配置
	 * @desc 修改硬盘配置
	 * @url /admin/v1/mf_dcim/disk/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
     * @param   string value - 硬盘 require
     * @param   int order - 排序 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格)
	 */
	public function diskUpdate(){
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
	 * 时间 2023-11-08
	 * @title 删除硬盘配置
	 * @desc 删除硬盘配置
	 * @url /admin/v1/mf_dcim/disk/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 通用配置ID require
	 */
	public function diskDelete(){
		$param = request()->param();

		$OptionModel = new OptionModel();

		$result = $OptionModel->optionDelete((int)$param['id'], OptionModel::DISK);
		return json($result);
	}



}