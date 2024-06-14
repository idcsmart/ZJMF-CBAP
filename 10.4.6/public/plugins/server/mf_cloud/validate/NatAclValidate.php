<?php 
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title NAT转发验证
 * @use  server\mf_cloud\validate\NatAclValidate
 */
class NatAclValidate extends Validate
{
    protected $rule = [
        'name'		=> 'require|length:1,100',
        'int_port'	=> 'require|integer|between:1,65535',
        'protocol' 	=> 'require|in:1,2,3',
    ];

    protected $message = [
    	'name.require'		=> 'mf_cloud_nat_acl_name_require',
    	'name.length'		=> 'mf_cloud_nat_acl_name_length_error',
    	'int_port.require'	=> 'mf_cloud_int_port_require',
        'int_port.between'	=> 'mf_cloud_int_port_format_error',
    	'int_port.number'	=> 'mf_cloud_int_port_format_error',
    	'protocol.require'	=> 'mf_cloud_nat_acl_protocol_require',
    	'protocol.in'		=> 'mf_cloud_nat_acl_protocol_require',
    ];

   	protected $scene = [
   		'create' => ['name','int_port','protocol'],
   	];



}