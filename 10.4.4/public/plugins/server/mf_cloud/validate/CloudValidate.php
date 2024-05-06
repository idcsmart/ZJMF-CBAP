<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 云参数验证
 * @use   server\mf_cloud\validate\CloudValidate
 */
class CloudValidate extends Validate
{
	protected $rule = [
		'id' 		        => 'require|integer',
        'password'          => 'require|checkPassword:thinkphp',
        'type'              => 'require|in:1,2',
        'image_id'          => 'integer|require',
        'port'              => 'require|between:1,65535',
        'ssh_key_id'        => 'requireWithout:password|number',
        'remove_disk_id'    => 'requireWithout:add_disk|array',
        'add_disk'          => 'requireWithout:remove_disk_id|array|checkAddDisk:thinkphp',
        'resize_data_disk'  => 'require|array',
        'ip_num'            => 'require|integer|between:1,99999',
        'disk_id'           => 'require|integer',
        'snapshot_id'       => 'require|integer',
        'backup_id'         => 'require|integer',
        'name'              => 'require|max:100',  // 快照/备份名称
    ];

    protected $message  =   [
    	'id.require'     			    => 'id_error',
        'id.integer'                    => 'id_error',
        'password.require'              => 'please_input_password',
        'password.checkPassword'        => 'mf_cloud_password_format_error',
        'type.require'                  => 'please_select_rescue_type',
        'type.in'                       => 'please_select_rescue_type',
        'image_id.require'              => 'please_select_os',
        'image_id.integer'              => 'please_select_os',
        'port.require'                  => 'please_input_port',
        'port.between'                  => 'port_format_error',
        'ssh_key_id.requireWithout'     => 'mf_cloud_password_format_error',
        'ssh_key_id.number'             => 'ssh_key_error',
        'remove_disk_id.requireWithout' => 'cancel_disk_and_add_disk_must_have_one',
        'remove_disk_id.array'          => 'cancel_disk_param_error',
        'add_disk.requireWithout'       => 'cancel_disk_and_add_disk_must_have_one',
        'add_disk.array'                => 'add_disk_param_error',
        'resize_data_disk.require'      => 'resize_disk_param_error',
        'resize_data_disk.array'        => 'resize_disk_param_error',
        'ip_num.require'                => 'please_select_append_ip_num',
        'ip_num.integer'                => 'append_ip_num_format_error',
        'ip_num.between'                => 'append_ip_num_format_error',
        'disk_id.require'               => 'mf_cloud_disk_id_require',
        'disk_id.integer'               => 'mf_cloud_disk_id_require',
        'snapshot_id.require'           => 'mf_cloud_snapshot_id_require',
        'snapshot_id.integer'           => 'mf_cloud_snapshot_id_require',
        'backup_id.require'             => 'mf_cloud_backup_id_require',
        'backup_id.integer'             => 'mf_cloud_backup_id_require',
        'name.require'                  => 'mf_cloud_snapshop_name_require',
        'name.max'                      => 'mf_cloud_snapshop_name_length_error',
    ];

    protected $scene = [
        'reset_password'    => ['id','password'],
        'rescue'            => ['id','type','password'],
        'buy_disk'          => ['id','remove_disk_id','add_disk'],
        'resize_disk'       => ['id','resize_data_disk'],
        'upgrade_ip_num'    => ['id','ip_num'],
        'unmount_disk'      => ['id','disk_id'],
        'mount_disk'        => ['id','disk_id'],
        'snapshot'          => ['id','snapshot_id'],
        'backup'            => ['id','backup_id'],
        'create_snapshot'   => ['id','disk_id','name'],
    ];


    public function sceneReinstall(){
        return $this->only(['id','password','image_id','port','ssh_key_id'])
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

    public function checkDiskSize($value){
        if(!is_int($value)){
            return false;
        }
        if($value % 10 != 0){
            return false;
        }
        return true;
    }

    public function checkAddDisk($value){
        foreach($value as $v){
            if(!isset($v['size']) || !is_numeric($v['size'])){
                return 'data_disk_size_error';
            }
            if(!isset($v['type']) || !is_string($v['type'])){
                return 'data_disk_type_error';
            }
        }
        return true;
    }


}