<?php
namespace reserver\mf_finance\validate;

use think\Validate;
use reserver\mf_finance\logic\ToolLogic;
use reserver\mf_finance\model\DataCenterModel;
use reserver\mf_finance\model\ConfigLimitModel;

/**
 * @title 下单参数验证
 * @use  reserver\mf_finance\validate\CartValidate
 */
class CartValidate extends Validate
{
	protected $rule = [
        'data_center_id'     => 'require|integer',
        'cpu'                => 'require|integer',
        'memory'             => 'require|integer',
        'image_id'           => 'require|integer',   // 镜像ID,暂时必须
        'system_disk'        => 'require|array|checkDisk:thinkphp',
        'data_disk'          => 'array|checkDataDisk:thinkphp',
        'size'               => 'require|integer',
        // 'type'               => '',
        'backup_num'         => 'integer',
        'snap_num'         => 'integer',
        'duration_id'        => 'require|integer',
        'password'           => 'requireWithout:ssh_key_id|checkPassword:thinkphp',
        'ssh_key_id'         => 'requireWithout:password|number',
        'notes'             => 'length:0,1000',
        // 'name'              => 'checkHostname:thinkphp',
        'network_type'      => 'require|in:normal,vpc|checkConfigLimit:thinkphp',
        'bw'                => 'integer',
        'security_group_protocol' => 'array|checkSecurityGroupProtocol:thinkphp',

        'remove_disk_id'    => 'array',
        'add_disk'          => 'array|checkAddDisk:thinkphp',
    ];

    protected $message  =   [
    	'data_center_id.require'     	=> 'data_center_id_error',
        'data_center_id.integer'        => 'data_center_id_error',
        'cpu.require'       => '请选择CPU配置',
        'cpu.integer'       => '请选择CPU配置',
        'memory.require'        => '请选择内存配置',
        'memory.integer'        => '请选择内存配置',
        'image_id.require'      => '请选择操作系统',
        'image_id.integer'      => '请选择操作系统',
        'system_disk.require'       => '请选择系统盘配置',
        'system_disk.array'         => '请选择系统盘配置',
        'data_disk.array'       => '请选择数据盘配置',
        'size.require'      => '请选择磁盘大小',
        'size.integer'      => '请选择磁盘大小',
        'backup_num.integer'        => '备份数量错误',
        'snap_num.integer'      => '快照数量错误',
        'duration_id.require'       => '请选择付款周期',
        'duration_id.integer'       => '请选择付款周期',
        'password.requireWithout'       => '请设置登录密码',
        'password.checkPassword'        => 'password_format_error',
        'ssh_key_id.requireWithout'         => 'password_and_ssh_key_must_have_one',
        'ssh_key_id.number'         => 'ssh_key_format_error',
        'notes.length'      => '实例名称不能超过1000个字',
        // 'name.require'      => '请输入主机名称',
        'name.checkHostname'      => '主机名格式错误,主机名必须英文大小写字母开头+数字及"_"、"-"、"."组成,6位及以上',
        'network_type.require'      => '请选择网络类型',
        'network_type.in'       => '请选择网络类型',
        'bw.integer'        => '带宽错误',

        'remove_disk_id.array'      => '参数错误',
        'add_disk.array'        => '参数错误',
    	'add_disk.checkAddDisk'     	=> '参数错误',
    ];

    protected $scene = [
        // 下单验证
        'cal' => ['data_center_id','cpu','memory','image_id','system_disk','data_disk','backup_num','snap_num','duration_id','password','ssh_key_id','notes','name','network_type','bw'],
        'upgrade_config' => ['cpu','memory'], // 1
        'check_disk' => ['size'],
        'buy_disk'   => ['remove_disk_id', 'add_disk'],
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

    public function checkSecurityGroupProtocol($value){
        $allow = ['icmp','ssh','telnet','http','https','mssql','oracle','mysql','rdp','postgresql','redis'];
        foreach($value as $v){
            if(!in_array($v, $allow)){
                return '安全组规则错误';
            }
        }
        return true;
    }

    public function checkAddDisk($value){
        foreach($value as $v){
            if(!isset($v['size']) || !isset($v['type'])){
                return '参数错误';
            }
            if(!is_numeric($v['size']) || $v['size'] < 0 || $v['size'] > 1048576){
                return '磁盘大小错误';
            }
        }
        return true;
    }



}