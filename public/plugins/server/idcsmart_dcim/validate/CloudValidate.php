<?php
namespace server\idcsmart_dcim\validate;

use think\Validate;
use server\common_cloud\logic\ToolLogic;

/**
 * 云参数验证
 */
class CloudValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'password'      => 'require|checkPassword:thinkphp',
        'type'          => 'require|in:1,2',
        'image_id'      => 'integer|require',
        'port'          => 'require|between:1,65535',
        'package_id'    => 'require|number',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'password.require'          => 'please_input_password',
        'password.checkPassword'    => 'password_format_error',
        'password.requireWithout'   => 'password_format_error',
        'type.require'              => 'please_select_rescue_type',
        'type.in'                   => 'please_select_rescue_type',
        'image_id.require'   => 'please_select_image',
        'image_id.requireWithout'   => 'please_select_image',
        'image_id.integer'          => 'please_select_image',
        'port.require'              => 'please_input_port',
        'port.between'              => 'port_format_error',
        'package_id.require'        => 'please_select_package',
        'package_id.number'         => 'please_select_package',
    ];

    protected $scene = [
        'reset_password'  => ['id','password'],
        'rescue'          => ['id','type'],
        'upgrade_package' => ['id','package_id'],
        'buy_disk'        => ['id', 'disk_size'],
        'reinstall'       => ['id','password','image_id','port'],
    ];

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