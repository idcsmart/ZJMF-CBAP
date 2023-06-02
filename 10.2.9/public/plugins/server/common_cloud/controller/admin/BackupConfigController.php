<?php
namespace server\common_cloud\controller\admin;

use server\common_cloud\model\BackupConfigModel;
use server\common_cloud\validate\BackupConfigValidate;

/**
 * @title 通用云备份管理设置
 * @desc 通用云备份管理设置
 * @use server\common_cloud\controller\admin\BackupConfigController
 */
class BackupConfigController{

	/**
	 * 时间 2022-06-17
	 * @title 创建备份/快照设置
	 * @desc 创建备份/快照设置
	 * @url /admin/v1/common_cloud/backup_config
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @param   int num - 允许数量 require
	 * @param   string type - 类型(snap=快照,backup=备份) require
	 * @param   float price - 价格 require
	 * @return  int data.id - 创建的ID
	 */
	public function create(){
		$param = request()->param();

		$BackupConfigValidate = new BackupConfigValidate();
		if (!$BackupConfigValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
        }
		$BackupConfigModel = new BackupConfigModel();

		$result = $BackupConfigModel->createBackupConfig($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-16
	 * @title 备份/快照设置列表
	 * @desc 备份/快照设置列表
	 * @url /admin/v1/common_cloud/backup_config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string type - 类型(snap=快照,backup=备份) require
     * @return  array list - 列表数据
     * @return  int list[].id - 设置ID
     * @return  int list[].num - 数量
     * @return  string list[].price - 价格
     * @return  int count - 总条数
	 */
	public function list(){
		$param = request()->param();

		$BackupConfigModel = new BackupConfigModel();

		$result = $BackupConfigModel->backupConfigList($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 修改备份/快照设置
	 * @desc 修改备份/快照设置
	 * @url /admin/v1/common_cloud/backup_config/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
	 * @param   int id - 设置ID require
	 * @param   int num - 允许的数量 require
	 * @param   float price - 价格 require
	 */
	public function update(){
		$param = request()->param();

		$BackupConfigValidate = new BackupConfigValidate();
		if (!$BackupConfigValidate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
        }
		$BackupConfigModel = new BackupConfigModel();

		$result = $BackupConfigModel->updateBackupConfig($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 删除备份/快照设置
	 * @desc 删除备份/快照设置
	 * @url /admin/v1/common_cloud/backup_config/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 设置ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function delete(){
		$param = request()->param();

		$BackupConfigModel = new BackupConfigModel();

		$result = $BackupConfigModel->deleteBackupConfig((int)$param['id']);
		return json($result);
	}




}


