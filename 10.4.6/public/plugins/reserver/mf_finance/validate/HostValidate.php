<?php
namespace reserver\mf_finance\validate;

use think\Validate;
use app\common\model\HostModel;

/**
 * @title 产品参数验证
 * @use  reserver\mf_finance\validate\HostValidate
 */
class HostValidate extends Validate{

	protected $rule = [
		'id'  		=> 'require|integer|checkAuth:thinkphp',
		'action'	=> 'require|in:on,off,reboot,hard_off,hard_reboot',
	];

	protected $message  =   [
		'id.require' 		=> 'res_mf_finance_id_error',
		'id.integer' 		=> 'res_mf_finance_id_error',
		'id.checkAuth' 		=> 'res_mf_finance_host_not_found',
		'id.array'         	=> 'res_mf_finance_id_error',
        'action.require'	=> 'res_mf_finance_param_error',
        'action.in'       	=> 'res_mf_finance_param_error',
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