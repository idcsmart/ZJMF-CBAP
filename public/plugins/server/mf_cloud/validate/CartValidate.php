<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\logic\ToolLogic;
use server\mf_cloud\model\DataCenterModel;
use server\mf_cloud\model\ConfigLimitModel;

/**
 * @title 下单参数验证
 * @use  server\mf_cloud\validate\CartValidate
 */
class CartValidate extends Validate
{
	protected $rule = [
        'data_center_id'            => 'require|integer',
        'cpu'                       => 'require|integer',
        'memory'                    => 'require|integer',
        'image_id'                  => 'require|integer',   // 镜像ID,暂时必须
        'system_disk'               => 'require|array|checkDisk:thinkphp',
        'data_disk'                 => 'array|checkDataDisk:thinkphp',
        'size'                      => 'require|integer',
        'backup_num'                => 'integer',
        'snap_num'                  => 'integer',
        'duration_id'               => 'require|integer',
        'password'                  => 'requireWithout:ssh_key_id|checkPassword:thinkphp',
        'ssh_key_id'                => 'requireWithout:password|number',
        'notes'                     => 'length:0,1000',
        'network_type'              => 'require|in:normal,vpc|checkConfigLimit:thinkphp',
        'bw'                        => 'integer',
        'security_group_protocol'   => 'array|checkSecurityGroupProtocol:thinkphp',
    ];

    protected $message  =   [
    	'data_center_id.require'     	=> 'data_center_id_error',
        'data_center_id.integer'        => 'data_center_id_error',
        'cpu.require'                   => 'please_select_cpu_config',
        'cpu.integer'                   => 'please_select_cpu_config',
        'memory.require'                => 'please_select_memory_config',
        'memory.integer'                => 'please_select_memory_config',
        'image_id.require'              => 'please_select_os',
        'image_id.integer'              => 'please_select_os',
        'system_disk.require'           => 'please_select_system_disk_config',
        'system_disk.array'             => 'please_select_system_disk_config',
        'data_disk.array'               => 'please_select_data_disk_config',
        'size.require'                  => 'please_select_disk_size',
        'size.integer'                  => 'please_select_disk_size',
        'backup_num.integer'            => 'backup_num_error',
        'snap_num.integer'              => 'snap_num_error',
        'duration_id.require'           => 'please_select_pay_duration',
        'duration_id.integer'           => 'please_select_pay_duration',
        'password.requireWithout'       => 'please_set_login_password',
        'password.checkPassword'        => 'mf_cloud_password_format_error',
        'ssh_key_id.requireWithout'     => 'password_and_ssh_key_must_have_one',
        'ssh_key_id.number'             => 'ssh_key_format_error',
        'notes.length'                  => 'instance_name_length_error',
        'network_type.require'          => 'please_select_network_type',
        'network_type.in'               => 'please_select_network_type',
    	'bw.integer'     	            => 'bw_error',
    ];

    protected $scene = [
        // 下单验证
        'cal' => ['data_center_id','cpu','memory','image_id','system_disk','data_disk','backup_num','snap_num','duration_id','password','ssh_key_id','notes','network_type','bw'],
        'upgrade_config' => ['cpu','memory'],
        'check_disk' => ['size'],
    ];

    public function sceneCalPrice(){
        return $this->only(['data_center_id','cpu','memory','image_id','system_disk','data_disk','backup_num','snap_num','duration_id']);
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

    // 验证配置限制,下单时验证
    public function checkConfigLimit($value, $type, $param){
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            return 'data_center_not_found';
        }
        $ConfigLimitModel = new ConfigLimitModel();
        $checkConfigLimit  = $ConfigLimitModel->checkConfigLimit($dataCenter['product_id'], $param);
        if($checkConfigLimit['status'] == 400){
            return $checkConfigLimit['msg'];
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



}