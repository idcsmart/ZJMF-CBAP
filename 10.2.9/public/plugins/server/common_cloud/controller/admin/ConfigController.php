<?php
namespace server\common_cloud\controller\admin;

use server\common_cloud\model\ConfigModel;
use server\common_cloud\validate\ConfigValidate;
use server\common_cloud\validate\BackupConfigValidate;

/**
 * @title 通用云设置
 * @desc 通用云设置
 * @use server\common_cloud\controller\admin\ConfigController
 */
class ConfigController{

	/**
	 * 时间 2022-06-20
	 * @title 获取设置
	 * @desc 获取设置
	 * @url /admin/v1/common_cloud/config
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @return  int product_type - 产品模式(0=固定配置,1=自定义配置)
     * @return  int support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @return  float price - 每10G价格
     * @return  string disk_min_size - 最小容量
     * @return  string disk_max_size - 最大容量
     * @return  int disk_max_num - 最大附加数量
     * @return  string disk_store_id - 储存ID
     * @return  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int snap_enable - 是否启用快照(0=不启用,1=启用)
	 */
	public function index(){
		$param = request()->param();

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->indexConfig($param);
		return json($result);
	}

	/**
	 * 时间 2022-06-20
	 * @title 保存设置
	 * @desc 保存设置
	 * @url /admin/v1/common_cloud/config
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param  int product_id - 商品ID require
     * @param  int product_type - 产品模式(0=固定配置,1=自定义配置)
     * @param  int support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @param  int buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @param  float price - 每10G价格 buy_data_disk=1时require
     * @param  string disk_min_size - 最小容量 buy_data_disk=1时require
     * @param  string disk_max_size - 最大容量 buy_data_disk=1时require
     * @param  int disk_max_num - 最大附加数量 buy_data_disk=1时require
     * @param  string disk_store_id - 储存ID
     * @param  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @param  int snap_enable - 是否启用快照(0=不启用,1=启用)
     * @param  int backup_data[].num - 备份数量
     * @param  float backup_data[].float - 备份价格
     * @param  int snap_data[].num - 快照数量
     * @param  float snap_data[].float - 快照价格
	 */
	public function save(){
		$param = request()->param();

		$ConfigValidate = new ConfigValidate();
		if (!$ConfigValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($ConfigValidate->getError())]);
        }
        $BackupConfigValidate = new BackupConfigValidate();
        if(isset($param['backup_data']) && is_array($param['backup_data'])){
        	foreach($param['backup_data'] as $v){
        		if (!$BackupConfigValidate->scene('save')->check($v)){
		            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
		        }
        	}
        }else{
        	$param['backup_data'] = null;
        }
        if(isset($param['snap_data']) && is_array($param['snap_data'])){
        	foreach($param['snap_data'] as $v){
        		if (!$BackupConfigValidate->scene('save')->check($v)){
		            return json(['status' => 400 , 'msg' => lang_plugins($BackupConfigValidate->getError())]);
		        }
        	}
        }else{
        	$param['snap_data'] = null;
        }

		$ConfigModel = new ConfigModel();

		$result = $ConfigModel->saveConfig($param);
		return json($result);
	}



}