<?php
namespace server\idcsmart_dcim\validate;

use think\Validate;
use server\idcsmart_dcim\logic\ToolLogic;

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
        'password'           => 'require|checkPassword:thinkphp',
    ];

    protected $message  =   [
    	// 'data_center_id.require'     	=> 'data_center_id_error',
    	'data_center_id.integer'     	=> 'data_center_id_error',
        'package_id.require'            => 'please_select_package',
        'package_id.integer'            => 'please_select_package',
        'image_id.require'              => 'please_select_image',
        'image_id.integer'              => 'please_select_image',
        'duration.require'              => 'please_select_duration',
        'duration.in'                   => 'please_select_duration',
        'password.require'              => 'please_input_password',
        'password.checkPassword'        => 'password_format_error',
    ];

    protected $scene = [
        'cal' => ['data_center_id','package_id','image_id','duration','password'],
    ];

    // 验证密码
    public function checkPassword($value){
        if(is_null($value)){
            return true;
        }
        return ToolLogic::checkPassword($value);
    }

}