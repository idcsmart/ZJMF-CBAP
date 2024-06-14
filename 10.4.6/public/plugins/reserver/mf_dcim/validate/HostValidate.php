<?php
namespace reserver\mf_dcim\validate;

use think\Validate;
use app\common\model\HostModel;

/**
 * @title 产品参数验证
 * @use  reserver\mf_dcim\validate\HostValidate
 */
class HostValidate extends Validate
{
	protected $rule = [
		'id'  				=> 'require|integer|checkAuth:thinkphp',
        'image_id'			=> 'require|integer',
        'action'            => 'require|in:on,off,reboot',
	];

	protected $message  =   [
		'id.require' 			=> 'res_mf_dcim_id_error',
		'id.integer' 			=> 'res_mf_dcim_id_error',
		'id.checkAuth' 			=> 'res_mf_dcim_host_not_found',
    	'image_id.require'		=> 'res_mf_dcim_image_id_require',
    	'image_id.integer'		=> 'res_mf_dcim_image_id_require',
    	'id.array'           	=> 'res_mf_dcim_id_error',
        'action.require'     	=> 'res_mf_dcim_param_error',
        'action.in'           	=> 'res_mf_dcim_param_error',
	];

	protected $scene = [
		'auth' 			=> ['id'],
		'buy_image'		=> ['id','image_id'],
	];

	/**
     * 时间 2024-02-20
     * @title 验证产品是否属于用户
     * @desc  验证产品是否属于用户
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  bool
     */
	public function checkAuth($hostId)
	{
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