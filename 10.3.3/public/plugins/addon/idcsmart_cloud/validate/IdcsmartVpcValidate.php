<?php
namespace addon\idcsmart_cloud\validate;

use think\Validate;
use addon\idcsmart_cloud\IdcsmartCloud;

/**
 * VPC管理验证
 */
class IdcsmartVpcValidate extends Validate
{
    protected $rule = [
        'id'                => 'require|integer|gt:0',
        'data_center_id'    => 'require|integer|gt:0',
        'name'              => 'require|max:255',
        'ip'                => 'requireIf:auto_create_ip,0|checkVpcIp:thinkphp',
        'auto_create_ip'    => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'data_center_id.require'    => 'vpc_network_data_center_require',
        'data_center_id.integer'    => 'id_error',
        'data_center_id.gt'         => 'id_error',
        'name.require'              => 'vpc_network_name_require',
        'name.max'                  => 'vpc_network_name_max',
        'ip.requireIf'              => 'vpc_network_ip_require',
        'ip.checkVpcIp'             => 'vpc_network_ip_format',
        'auto_create_ip.require'    => 'param_error',
        'auto_create_ip.in'         => 'param_error',
    ];

    protected $scene = [
        'create' => ['data_center_id', 'name', 'ip', 'auto_create_ip'],
        'update' => ['id', 'name'],
    ];

    // edit 验证场景定义
    public function sceneEdit(){
        return $this->only(['name']);
    }

    // 验证vpc网段规则
    public function checkVpcIp($value){
        return check_vpc_ip($value);
    }
}