<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 线路IPv6验证
 * @use  server\mf_cloud\validate\LineIpValidate
 */
class LineIpv6Validate extends Validate
{
	protected $rule = [
        'id'                => 'require|integer',
        'value'             => 'require|integer|between:0,1000',
        'price'             => 'checkPrice:thinkphp',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'value.require'                 => 'mf_cloud_ipv6_num_require',
        'value.integer'                 => 'mf_cloud_ipv6_num_format_error',
        'value.between'                 => 'mf_cloud_ipv6_num_format_error',
        'price.checkPrice'              => 'price_cannot_lt_zero',
    ];

    protected $scene = [
        'create'        => ['id','value','price'],
        'update'        => ['id','value','price'],
        'line_create'   => ['value','price'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return 'price_must_between_0_999999';
            }
        }
        return true;
    }


}