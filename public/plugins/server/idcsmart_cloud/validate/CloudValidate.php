<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;
use server\idcsmart_cloud\logic\ToolLogic;

/**
 * 云参数验证
 */
class CloudValidate extends Validate
{
	protected $rule = [
		'id' 		    => 'require|integer',
        'password'      => 'require|checkPassword:thinkphp',
        'type'          => 'require|in:1,2',
        'image_id'      => 'integer|requireWithout:template_id',
        'template_id'   => 'integer|requireWithout:image_id',
        'port'          => 'require|between:1,65535',
        'package_id'    => 'require|number',
        'ssh_key_id'    => 'requireWithout:password|number',
    ];

    protected $message  =   [
    	'id.require'     			=> 'id_error',
        'id.integer'                => 'id_error',
        'password.require'          => 'please_input_password',
        'password.checkPassword'    => 'password_format_error',
        'password.requireWithout'   => 'password_format_error',
        'type.require'              => 'please_select_rescue_type',
        'type.in'                   => 'please_select_rescue_type',
        'image_id.requireWithout'   => 'please_select_image',
        'image_id.integer'          => 'please_select_image',
        'port.require'              => 'please_input_port',
        'port.between'              => 'port_format_error',
        'package_id.require'        => '请选择套餐',
        'package_id.number'         => '请选择套餐',
        'template_id.integer'       => '请选择模板',
        'template_id.requireWithout'=> 'please_select_image',
        'ssh_key_id.requireWithout' => 'password_format_error',
        'ssh_key_id.number'         => 'SSH密钥错误',
    ];

    protected $scene = [
        'reset_password' => ['id','password'],
        'rescue'         => ['id','type','password'],
        //'reinstall'      => ['id','password','image_id','port','template_id','ssh_key_id'],
        'upgrade_package'=> ['id','package_id'],
    ];


    public function sceneReinstall(){
        return $this->only(['id','password','image_id','port','template_id','ssh_key_id'])
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


}