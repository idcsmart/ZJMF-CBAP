<?php
namespace server\common_cloud\validate;

use think\Validate;
use server\common_cloud\logic\ToolLogic;

/**
 * 下单参数验证
 */
class CartValidate extends Validate
{
	protected $rule = [
        'data_center_id'     => 'integer',
        'package_id'         => 'require|integer',
        'image_id'           => 'require|integer',   // 镜像ID,暂时必须
        'duration'           => 'require|in:free,onetime_fee,month_fee,quarter_fee,half_year_fee,year_fee,two_year,three_year',
        'data_disk'          => 'array',
        'backup_num_id'         => 'integer',
        'snap_num_id'           => 'integer',
        'password'           => 'requireWithout:ssh_key_id|checkPassword:thinkphp',
        'ssh_key_id'         => 'requireWithout:password|number',
    ];

    protected $message  =   [
    	'data_center_id.require'     	=> 'data_center_id_error',
    	'data_center_id.integer'     	=> 'data_center_id_error',
        'package_id.require'            => 'please_select_package',
        'package_id.integer'            => 'please_select_package',
        'image_id.require'              => 'please_select_image',
        'image_id.integer'              => 'please_select_image',
        'duration.require'              => 'please_select_duration',
        'duration.in'                   => 'duration_error',
        'data_disk.array'               => 'other_data_disk_param_error',
        'backup_num_id.integer'         => 'backup_num_error',
        'snap_num_id.integer'           => 'snap_num_error',
        'password.requireWithout'       => 'please_input_password',
        'password.checkPassword'        => 'password_format_error',
        'ssh_key_id.requireWithout'     => 'password_and_ssh_key_must_have_one',
        'ssh_key_id.number'             => 'ssh_key_format_error',
    ];

    protected $scene = [
        'cal' => ['data_center_id','package_id','image_id','duration','data_disk','backup_num_id','snap_num_id','password','ssh_key_id'],
    ];

    // 验证密码
    public function checkPassword($value){
        if(is_null($value)){
            return true;
        }
        return ToolLogic::checkPassword($value);
    }

}