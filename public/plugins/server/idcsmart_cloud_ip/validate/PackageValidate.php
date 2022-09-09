<?php
namespace server\idcsmart_cloud_ip\validate;

use think\Validate;

/**
 * 套餐验证
 */
class PackageValidate extends Validate
{
	protected $rule = [
        'id'            => 'require|integer|gt:0',
		'product_id' 	=> 'require|integer|gt:0',
        'ip_enable'     => 'require|in:0,1',
        'ip_price'      => 'requireIf:ip_enable,1|float|egt:0',
        'ip_max'        => 'requireIf:ip_enable,1|integer|gt:0',
        'bw_enable'     => 'requireIf:ip_enable,1|in:0,1',
        'bw_precision'  => 'requireIf:bw_enable,1|integer|gt:0',
        'bw_price'      => 'requireIf:bw_enable,1|checkBwPrice:thinkphp',
    ];

    protected $message  =   [
    	'id.require'     	    => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'product_id.require'    => 'package_product_id_error',
    	'product_id.integer'    => 'package_product_id_error',
        'product_id.gt'         => 'package_product_id_error',
        'ip_enable.require'     => 'please_select_package_ip_enable',
        'ip_enable.in'          => 'param_error',
        'ip_price.requireIf'    => 'please_enter_package_ip_price',
        'ip_price.float'        => 'package_ip_price_format_error',
        'ip_price.egt'          => 'package_ip_price_format_error',
        'ip_max.requireIf'      => 'please_enter_package_ip_max',
        'ip_max.integer'        => 'package_ip_max_format_error',
        'ip_max.gt'             => 'package_ip_max_format_error',
        'bw_enable.requireIf'   => 'please_select_package_bw_enable',
        'bw_enable.in'          => 'param_error',
        'bw_precision.require'  => 'please_enter_package_bw_precision',
        'bw_precision.integer'  => 'package_bw_precision_format_error',
        'bw_precision.gt'       => 'package_bw_precision_format_error',
        'bw_price.require'      => 'please_enter_package_bw_price',
        'bw_price.integer'      => 'param_error',
        'bw_price.checkBwPrice' => 'param_error',
    ];

    protected $scene = [
        'save' => ['id', 'product_id', 'ip_enable', 'ip_price', 'ip_max', 'bw_enable', 'bw_precision', 'bw_price'],
        //'ip_enable' => ['id', 'ip_enable'],
        //'bw_enable' => ['id', 'bw_enable'],
    ];

    public function checkBwPrice($value)
    {
        $min = 0;
        if(empty($value)){
            return false;
        }
        if(!is_array($value)){
            return false;
        }
        foreach ($value as $k => $v) {
            if(!isset($v['min'])){
                return false;
            }
            if(!is_integer($v['min'])){
                return false;
            }
            if($v['min']!=$min){
                return false;
            }
            if(!isset($v['max'])){
                return false;
            }
            if(!is_integer($v['max'])){
                return false;
            }
            if($v['max']<=$v['min']){
                return false;
            }
            if(!isset($v['price'])){
                return false;
            }
            if(!is_float($v['price']) && !is_integer($v['price'])){
                return false;
            }
            if($v['price']<0){
                return false;
            }
            $min = $v['max'];
        }
        return true;
    }

}