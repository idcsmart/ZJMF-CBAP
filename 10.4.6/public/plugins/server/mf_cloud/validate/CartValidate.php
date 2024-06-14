<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\logic\ToolLogic;
use server\mf_cloud\model\DataCenterModel;

/**
 * @title 下单参数验证
 * @use  server\mf_cloud\validate\CartValidate
 */
class CartValidate extends Validate
{
	protected $rule = [
        'recommend_config_id'       => 'requireWithout:cpu|integer',
        'data_center_id'            => 'requireWithout:recommend_config_id|integer',
        'cpu'                       => 'requireWithout:recommend_config_id|integer',
        'memory'                    => 'requireWithout:recommend_config_id|integer',
        'image_id'                  => 'require|integer',   // 镜像ID,暂时必须
        'system_disk'               => 'requireWithout:recommend_config_id|array|checkDisk:thinkphp',
        'data_disk'                 => 'array|checkDataDisk:thinkphp',
        'size'                      => 'require|integer',  // 磁盘大小
        'disk_type'                 => 'max:50',  // 磁盘大小
        'backup_num'                => 'integer',
        'snap_num'                  => 'integer',
        'duration_id'               => 'require|integer',
        'password'                  => 'requireWithout:ssh_key_id|checkPassword:thinkphp',
        'ssh_key_id'                => 'requireWithout:password|number',
        'notes'                     => 'length:0,1000',
        'network_type'              => 'require|in:normal,vpc',
        'bw'                        => 'integer',
        'security_group_protocol'   => 'array|checkSecurityGroupProtocol:thinkphp',
        'line_id'                   => 'integer',
        'flow'                      => 'integer',
        'peak_defence'              => 'integer',
        'ip_num'                    => 'integer',
        'gpu_num'                   => 'integer',
        'resource_package_id'       => 'integer',
        'port'                      => 'integer|checkPort:thinkphp',
        'ipv6_num'                  => 'integer',
    ];

    protected $message  =   [
        'recommend_config_id.requireWithout'    => 'mf_cloud_recommend_config_id_require',
        'recommend_config_id.integer'           => 'mf_cloud_recommend_config_id_require',
    	'data_center_id.requireWithout'     	=> 'data_center_id_error',
        'data_center_id.integer'                => 'data_center_id_error',
        'cpu.requireWithout'                    => 'please_select_cpu_config',
        'cpu.integer'                           => 'please_select_cpu_config',
        'memory.requireWithout'                 => 'please_select_memory_config',
        'memory.integer'                        => 'please_select_memory_config',
        'image_id.require'                      => 'please_select_os',
        'image_id.integer'                      => 'please_select_os',
        'system_disk.requireWithout'            => 'please_select_system_disk_config',
        'system_disk.array'                     => 'please_select_system_disk_config',
        'data_disk.array'                       => 'please_select_data_disk_config',
        'size.require'                          => 'please_select_disk_size',
        'size.integer'                          => 'please_select_disk_size',
        'backup_num.integer'                    => 'backup_num_error',
        'snap_num.integer'                      => 'snap_num_error',
        'duration_id.require'                   => 'please_select_pay_duration',
        'duration_id.integer'                   => 'please_select_pay_duration',
        'password.requireWithout'               => 'please_set_login_password',
        'password.checkPassword'                => 'mf_cloud_password_format_error',
        'ssh_key_id.requireWithout'             => 'password_and_ssh_key_must_have_one',
        'ssh_key_id.number'                     => 'ssh_key_format_error',
        'notes.length'                          => 'instance_name_length_error',
        'network_type.require'                  => 'please_select_network_type',
        'network_type.in'                       => 'please_select_network_type',
        'bw.integer'                            => 'bw_error',
        'line_id.integer'                       => 'param_error',
        'flow.integer'                          => 'param_error',
        'peak_defence.integer'                  => 'param_error',
        'ip_num.integer'                        => 'param_error',
    	'gpu_num.integer'     	                => 'param_error',
        'disk_type.max'                         => 'disk_type_format_error',
        'resource_package_id'                   => 'param_error',
        'port.integer'                          => 'mf_cloud_ssh_port_format_error',
        'port.checkPort'                        => 'mf_cloud_ssh_port_format_error',
        'ipv6_num.integer'                      => 'param_error',
    ];

    protected $scene = [
        // 下单验证
        'cal'               => ['data_center_id','cpu','memory','image_id','system_disk','data_disk','backup_num','snap_num','duration_id','password','ssh_key_id','notes','network_type','bw','recommend_config_id','resource_package_id','flow','peak_defence','ip_num','gpu_num','port','ipv6_num'],
        'upgrade_config'    => ['cpu','memory','bw'],
        'check_disk'        => ['size','disk_type'],
        'all_duration_price'=> ['recommend_config_id','cpu','memory','system_disk','data_disk','line_id','bw','flow','peak_defence','ip_num','gpu_num','backup_num','snap_num','ipv6_num'],
    ];

    public function sceneCalPrice(){
        return $this->only(['data_center_id','cpu','memory','image_id','system_disk','data_disk','backup_num','snap_num','duration_id','recommend_config_id']);
    }

    // 验证密码
    public function checkPassword($value){
        if(is_null($value)){
            return true;
        }
        return ToolLogic::checkPassword($value);
    }

    public function checkDisk($value){
        $CartValidate = new CartValidate();
            
        if(!$CartValidate->scene('check_disk')->check($value)){
            return $CartValidate->getError();
        }
        return true;
    }

    public function checkDataDisk($value){
        $CartValidate = new CartValidate();

        foreach($value as $v){
            if(!$CartValidate->scene('check_disk')->check($v)){
                return $CartValidate->getError();
            }
        }
        return true;
    }

    // 验证主机名 英文大小写字母开头+数字及"_"、"-"、“.”组成，6位及以上
    public function checkHostname($value){
        return preg_match('/^[a-zA-Z][0-9a-zA-Z_\-.]{5,254}$/', $value) ? true : false;
    }

    public function checkSecurityGroupProtocol($value){
        $allow = ['icmp','ssh','telnet','http','https','mssql','oracle','mysql','rdp','postgresql','redis'];
        foreach($value as $v){
            if(!in_array($v, $allow)){
                return 'security_group_rule_error';
            }
        }
        return true;
    }

    // 验证指定端口
    public function checkPort($value)
    {
        return $value == 22 || ($value >= 100 && $value <= 65535);
    }



}