<?php
namespace reserver\whmcs_dcim\validate;

use think\Validate;
use app\common\model\HostModel;

/**
 * @title 产品参数验证
 * @use  reserver\whmcs_dcim\validate\HostValidate
 */
class HostValidate extends Validate{

	protected $rule = [
		'id'  		=> 'require|integer|checkAuth:thinkphp',
		'action'	=> 'require|in:on,off,reboot',
	];

	protected $message  =   [
		'id.require' 		=> 'res_whmcs_dcim_id_error',
		'id.integer' 		=> 'res_whmcs_dcim_id_error',
		'id.checkAuth'		=> 'res_whmcs_dcim_host_not_found',
		'id.array'         	=> 'res_whmcs_dcim_id_error',
        'action.require'	=> 'res_whmcs_dcim_param_error',
        'action.in'       	=> 'res_whmcs_dcim_param_error',
	];

	protected $scene = [
		'auth' 			=> ['id'],
	];

	public function checkAuth($hostId){
		$HostModel = HostModel::find($hostId);
		if(empty($HostModel) || $HostModel['is_delete']){
			return false;
		}
		// 前台用户验证
		$app = app('http')->getName();
        if($app == 'home'){
        	if($HostModel['client_id'] != get_client_id()){
        		return false;
        	}
        }
        return true;
	}

	public function sceneBatch(){
        return $this->only(['id','action'])
                    ->remove('id', 'integer|checkAuth')
                    ->append('id', 'array');
    }
}