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
		'id'  => 'require|integer|checkAuth:thinkphp',

		'remove_disk_id'    => 'array',
        'add_disk'          => 'array|checkAddDisk:thinkphp',

        'resize_data_disk'	=> 'array|checkResizeDataDisk:thinkphp',

        'image_id'			=> 'require|integer',

        'num'           => 'require|number|between:1,999',
        'type'          => 'require|in:snap,backup',

        'ip_num'            => 'require|integer|between:1,99999',

        'name'  =>              'require|max:255',
	];

	protected $message  =   [
		'id.require' => 'ID错误',
		'id.integer' => 'ID错误',
		'id.checkAuth' => 'host_is_not_exist',

		'remove_disk_id.array'      => '参数错误',
        'add_disk.array'        => '参数错误',
    	'add_disk.checkAddDisk'     	=> '参数错误',
    	'resize_data_disk.array'     	=> '参数错误',
    	'resize_data_disk.checkResizeDataDisk'     	=> '参数错误',
    	'image_id.require'	=> '请选择操作系统',
    	'image_id.integer'	=> '请选择操作系统',

    	'num.require'           => 'please_input_backup_config_num',
        'num.number'            => 'num_must_between_1_999',
        'num.between'           => 'num_must_between_1_999',
        'type.require'          => 'backup_config_type_error',
        'type.in'               => 'backup_config_type_error',

        'ip_num.require'                => '请选择附加IP数量',
        'ip_num.integer'                => '附加IP数量只能是1-99999的整数',
        'ip_num.between'                => '附加IP数量只能是1-99999的整数',

        'name.require'                    => '请输入网络名称',
        'name.max'                    => '网络名称不能超过255个字',
	];

	protected $scene = [
		'auth' 			=> ['id'],
		'buy_disk' 		=> ['id','remove_disk_id','add_disk'],
		'resize_disk' 	=> ['id','resize_data_disk'],
		'buy_image'		=> ['id','image_id'],
		'buy_backup'	=> ['id','num','type'],
		'buy_ip'		=> ['id','ip_num'],
		'create_vpc'	=> ['id','name']
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

	public function checkAddDisk($value){
        foreach($value as $v){
            if(!isset($v['size']) || !isset($v['type'])){
                return '参数错误';
            }
            if(!is_numeric($v['size']) || $v['size'] < 0 || $v['size'] > 1048576){
                return '磁盘大小错误';
            }
        }
        return true;
    }

    public function checkResizeDataDisk($value){
        foreach($value as $v){
            if(!isset($v['id']) || !isset($v['size'])){
                return '参数错误';
            }
            if(!is_numeric($v['size']) || $v['size'] < 0 || $v['size'] > 1048576){
                return '磁盘大小错误';
            }
        }
        return true;
    }


}