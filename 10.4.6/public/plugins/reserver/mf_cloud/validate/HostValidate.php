<?php
namespace reserver\mf_cloud\validate;

use think\Validate;
use app\common\model\HostModel;

/**
 * @title 产品参数验证
 * @use  reserver\mf_cloud\validate\HostValidate
 */
class HostValidate extends Validate
{
	protected $rule = [
		'id'                => 'require|integer|checkAuth:thinkphp',
		'remove_disk_id'    => 'array',
        'add_disk'          => 'array|checkAddDisk:thinkphp',
        'resize_data_disk'	=> 'array|checkResizeDataDisk:thinkphp',
        'image_id'			=> 'require|integer',
        'num'               => 'require|integer|between:1,999',
        'type'              => 'require|in:snap,backup',
        'ip_num'            => 'integer|between:0,99999',
        'name'              => 'require|max:255',
        'action'            => 'require|in:on,off,reboot,hard_off,hard_reboot',
        'ipv6_num'          => 'integer|between:0,1000',
	];

	protected $message  =   [
		'id.require'                              => 'res_mf_cloud_id_error',
		'id.integer'                              => 'res_mf_cloud_id_error',
		'id.checkAuth'                            => 'res_mf_cloud_host_not_found',
		'remove_disk_id.array'                    => 'res_mf_cloud_param_error',
        'add_disk.array'                          => 'res_mf_cloud_param_error',
    	'add_disk.checkAddDisk'                   => 'res_mf_cloud_param_error',
    	'resize_data_disk.array'     	          => 'res_mf_cloud_param_error',
    	'resize_data_disk.checkResizeDataDisk'    => 'res_mf_cloud_param_error',
    	'image_id.require'	                      => 'res_mf_cloud_image_id_require',
    	'image_id.integer'	                      => 'res_mf_cloud_image_id_require',
    	'num.require'                             => 'res_mf_cloud_num_require',
        'num.integer'                             => 'res_mf_cloud_backup_num_format_error',
        'num.between'                             => 'res_mf_cloud_backup_num_format_error',
        'type.require'                            => 'res_mf_cloud_param_error',
        'type.in'                                 => 'res_mf_cloud_param_error',
        // 'ip_num.require'                          => 'res_mf_cloud_ip_num_require',
        'ip_num.integer'                          => 'res_mf_cloud_ip_num_format_error',
        'ip_num.between'                          => 'res_mf_cloud_ip_num_format_error',
        'name.require'                            => 'res_mf_cloud_vpc_name_require',
        'name.max'                                => 'res_mf_cloud_vpc_name_length_error',
        'id.array'                                => 'res_mf_cloud_id_error',
        'action.require'                          => 'res_mf_cloud_param_error',
        'action.in'                               => 'res_mf_cloud_param_error',
        'ipv6_num.integer'                        => 'res_mf_cloud_ipv6_num_format_error',
        'ipv6_num.between'                        => 'res_mf_cloud_ipv6_num_format_error',
	];

	protected $scene = [
		'auth' 			=> ['id'],
		'buy_disk' 		=> ['id','remove_disk_id','add_disk'],
		'resize_disk' 	=> ['id','resize_data_disk'],
		'buy_image'		=> ['id','image_id'],
		'buy_backup'	=> ['id','num','type'],
		'buy_ip'		=> ['id','ip_num'],
		'create_vpc'	=> ['id','name'],
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

    /**
     * 时间 2024-02-20
     * @title 验证add_disk参数
     * @desc  验证add_disk参数,如:[['size'=>1,'type'=>'']]
     * @author hh
     * @version v1
     * @param   array value - add_disk参数 require
     * @return  bool|string
     */
	public function checkAddDisk($value)
    {
        foreach($value as $v){
            if(!isset($v['size']) || !isset($v['type'])){
                return 'res_mf_cloud_param_error';
            }
            if(!is_numeric($v['size']) || $v['size'] < 0 || $v['size'] > 1048576){
                return 'res_mf_cloud_disk_size_error';
            }
        }
        return true;
    }

    /**
     * 时间 2024-02-20
     * @title 验证resize_data_disk参数
     * @desc  验证resize_data_disk参数,如:[['size'=>1,'id'=>1]]
     * @author hh
     * @version v1
     * @param   array value - resize_data_disk参数 require
     * @return  bool|string
     */
    public function checkResizeDataDisk($value)
    {
        foreach($value as $v){
            if(!isset($v['id']) || !isset($v['size'])){
                return 'res_mf_cloud_param_error';
            }
            if(!is_numeric($v['size']) || $v['size'] < 0 || $v['size'] > 1048576){
                return 'res_mf_cloud_disk_size_error';
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