<?php 
namespace server\mf_cloud\validate;

use think\Validate;

class NatWebValidate extends Validate{

    protected $rule = [
        'domain'    => 'require|length:1,255|checkDomain:thinkphp',
        'int_port'	=> 'require|integer|between:1,65535',
    ];

    protected $message = [
        'domain.require'       => 'mf_cloud_nat_web_domain_require',
    	'domain.length'	       => 'mf_cloud_nat_web_domain_length_error',
    	'domain.checkDomain'   => 'mf_cloud_nat_web_domain_format_error',
    	'int_port.require'	   => 'mf_cloud_int_port_require',
        'int_port.between'	   => 'mf_cloud_int_port_format_error',
    	'int_port.number'      => 'mf_cloud_int_port_format_error',
    ];

   	protected $scene = [
   		'create' => ['domain','int_port'],
   	];

    public function checkDomain($value){
        if(preg_match('/[^0-9a-zA-Z.\-]/', $value)){
            return false;
        }
        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if($first == '.' || $first == '-' || $last == '.' || $last == '-'){
            return false;
        }
        return true;
    }

}