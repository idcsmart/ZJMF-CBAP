<?php
namespace server\common_cloud\validate;

use think\Validate;
use server\common_cloud\logic\ToolLogic;

/**
 * 云参数验证
 */
class CloudValidate extends Validate
{
	protected $rule = [
		'id' 		        => 'require|integer',
        'password'          => 'require|checkPassword:thinkphp',
        'type'              => 'require|in:1,2',
        'image_id'          => 'integer|require',
        'port'              => 'require|between:1,65535',
        'package_id'        => 'require|number',
        'ssh_key_id'        => 'requireWithout:password|number',
        'remove_disk_id'    => 'requireWithout:add_disk|array',
        'add_disk'          =>  'requireWithout:remove_disk_id|array',
        'resize_data_disk'  => 'require|array',
    ];

    protected $message  =   [
    	'id.require'     			   => 'id_error',
        'id.integer'                    => 'id_error',
        'password.require'              => 'please_input_password',
        'password.checkPassword'        => 'password_format_error',
        'type.require'                  => 'please_select_rescue_type',
        'type.in'                       => 'please_select_rescue_type',
        'image_id.require'              => 'please_select_image',
        'image_id.integer'              => 'please_select_image',
        'port.require'                  => 'please_input_port',
        'port.between'                  => 'port_format_error',
        'package_id.require'            => 'please_select_package',
        'package_id.number'             => 'please_select_package',
        'ssh_key_id.requireWithout'     => 'password_format_error',
        'ssh_key_id.number'             => 'ssh_key_error',
        'remove_disk_id.requireWithout' => 'cancel_disk_and_add_disk_must_have_one',
        'remove_disk_id.array'          => 'cancel_disk_param_error',
        'add_disk.requireWithout'       => 'cancel_disk_and_add_disk_must_have_one',
        'add_disk.array'                => 'add_disk_param_error',
        'resize_data_disk.require'      => 'resize_disk_param_error',
        'resize_data_disk.array'        => 'resize_disk_param_error',
    ];

    protected $scene = [
        'reset_password' => ['id','password'],
        'rescue'         => ['id','type','password'],
        //'reinstall'      => ['id','password','image_id','port','template_id','ssh_key_id'],
        'upgrade_package'=> ['id','package_id'],
        'buy_disk'       => ['id', 'disk_size'],
        'resize_disk'        => ['id','resize_data_disk'],
        'resize_disk_param'=>['id'],
    ];


    public function sceneReinstall(){
        return $this->only(['id','password','image_id','port','ssh_key_id'])
                    ->remove('password', 'require')
                    ->append('password', 'requireWithout:ssh_key_id');
    }

    // 验证密码
    public function checkPassword($value){
        if(is_null($value)){
            return true;
        }
        return ToolLogic::checkPassword($value);
    }

    public function checkDiskSize($value){
        if(!is_int($value)){
            return false;
        }
        if($value % 10 != 0){
            return false;
        }
        return true;
    }


}