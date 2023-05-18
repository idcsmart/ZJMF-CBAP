<?php
namespace app\home\validate;

use think\Validate;

/**
 * API密钥验证
 */
class ApiValidate extends Validate
{
	protected $rule = [
        'id'        => 'require|integer|gt:0',
        'name'      => 'require|max:10',
        'status'    => 'require|in:0,1',
        'ip'        => 'requireIf:status,1|checkIp:thinkphp',
    ];

    protected $message  =   [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'name.require'      => 'please_enter_api_name', 
        'name.max'          => 'api_name_cannot_exceed_10_chars',
        'status.require'    => 'please_select_api_status',
        'status.in'         => 'api_status_error', 
        'ip.requireIf'      => 'please_enter_api_ip',
        'ip.checkIp'        => 'api_ip_error',
    ];

    protected $scene = [
        'create' => ['name'],
        'white_list' => ['id', 'status', 'ip'],
    ];

    public function checkIp($value)
    {
        $value = explode("\n", str_replace("\r\n", "\n", $value));
        if(empty($value) || !is_array($value)){
            return false;
        }
        foreach ($value as $k => $v) {
            if(filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_IPV4)===false){
                return false;
            }
        }
        return true;
    }
}