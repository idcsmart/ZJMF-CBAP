<?php
namespace reserver\mf_dcim\validate;

use think\Validate;
use app\common\model\HostModel;

/**
 * @title 产品参数验证
 * @use  reserver\mf_dcim\validate\HostValidate
 */
class HostValidate extends Validate{

	protected $rule = [
		'id'  				=> 'require|integer|checkAuth:thinkphp',
        'image_id'			=> 'require|integer',
        'ip_num'            => 'require|integer|between:1,99999',
	];

	protected $message  =   [
		'id.require' 			=> 'ID错误',
		'id.integer' 			=> 'ID错误',
		'id.checkAuth' 			=> 'host_is_not_exist',
    	'image_id.require'		=> '请选择操作系统',
    	'image_id.integer'		=> '请选择操作系统',
        'ip_num.require'        => '请选择附加IP数量',
        'ip_num.integer'        => '附加IP数量只能是1-99999的整数',
        'ip_num.between'        => '附加IP数量只能是1-99999的整数',
	];

	protected $scene = [
		'auth' 			=> ['id'],
		'buy_image'		=> ['id','image_id'],
		'buy_ip'		=> ['id','ip_num'],
	];

	public function checkAuth($hostId){
		$HostModel = HostModel::find($hostId);
		if(empty($HostModel)){
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


}