<?php
namespace server\idcsmart_common\validate;

use server\idcsmart_common\logic\ProvisionLogic;
use server\idcsmart_common\model\IdcsmartCommonServerGroupModel;
use server\idcsmart_common\model\IdcsmartCommonServerModel;
use think\db\Query;
use think\Validate;

/**
 * 自定义周期验证
 */
class IdcsmartCommonServerValidate extends Validate
{
    protected $rule = [
        'name'                      => 'require|max:50|checkName:thinkphp',
        'ip_address'                => 'require|max:50',
        'hostname'                  => 'max:50',
        'status_address'            => 'max:100',
        'assigned_ips'              => 'max:500',
        'username'                  => 'max:256',
        'password'                  => 'max:256',
        'disabled'                  => 'in:0,1',
        'accesshash'                => 'max:5000',
        'noc'                       => 'max:1000',
        'secure'                    => 'in:0,1',
        'port'                      => 'number|max:50',
        'gid'                       => 'require|checkGid:thinkphp',
        'file'                      => 'require|image|fileExt:png,jpg,jpeg,gif|fileMime:image/jpeg,image/png,image/gif|fileSize:10485760',
        'group_name'                => 'require|max:256',
        'type'                      => 'require|checkType:thinkphp',
    ];

    protected $message = [
        'port.number' => 'idcsmart_common_server_port_number',
    ];
    /*protected $message = [

        'name.require'              => '{%SERVER_NAME_REQUIRE}',
        'name.max'                  => '{%SERVER_NAME_MAX}',
        'ip_address.require'        => '{%SERVER_IP_ADDRESS_REQUIRE}',
        'ip_address.max'            => '{%SERVER_IP_ADDRESS_MAX}',
        //'hostname.require'          => '{%SERVER_HOSTNAME_REQUIRE}',
        'hostname.max'              => '{%SERVER_HOSTNAME_MAX}',
        'status_address.max'        => '{%SERVER_STATUS_ADDRESS_MAX}',
        'assigned_ips.max'          => '{%SERVER_ASSIGNED_IPS_MAX}',
        //'username.require'          => '{%SERVER_USERNAME_REQUIRE}',
        'username.max'              => '{%SERVER_USERNAME_MAX}',
        //'password.require'          => '{%SERVER_PASSWORD_REQUIRE}',
        'password.max'              => '{%SERVER_PASSWORD_MAX}',
        'accesshash.max'            => '{%SERVER_ACCESSHASH_MAX}',
        'port.max'                  => '{%SERVER_PORT_MAX}',
        'gid.require'               => '{%SERVER_GROUP_REQUIRE}',

        'group_name.require'        => '{%SERVER_GROUPS_NAME_REQUIRE}',
        'group_name.max'            => '{%SERVER_GROUPS_NAME_MAX}',
        'type.require'              => '{%SERVER_GROUPS_MODULE_REQUIRE}',
        'type.max'                  => '{%SERVER_GROUPS_MODULE_MAX}',

        'file.require'              => '{%IMAGE_REQUIRE}',
        'file.image'                => '{%IMAGE}',
        'file.fileExt'              => '{%IMAGE_TYPE}',
        'file.fileMime'             => '{%IMAGE_IMME}',
        'file.fileSize'             => '{%IMAGE_MAX_10}',
    ];*/

    protected $scene = [
        'create' => ['name','ip_address','hostname','status_address','assigned_ips','username','password','accesshash','port','secure','noc','disabled','type'],
    ];

    protected function checkName($value,$rule,$data){
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
        if (isset($data['id'])){
            $exist = $IdcsmartCommonServerModel->where('server_type', 'normal')->where('name',$value)->where('id','<>',$data['id'])->find();
        }else{
            $exist = $IdcsmartCommonServerModel->where('server_type', 'normal')->where('name',$value)->find();
        }
        if (!empty($exist)){
            return "接口已存在";
        }

        if (isset($data['gid']) && !empty($data['gid'])){

            $where = function (Query $query) use ($data){
                $query->where('server_type', 'normal')->where('gid', $data['gid']);
                if (isset($data['id'])){
                    $query->where('id','<>',$data['id']);
                }
            };

            $typeArray = $IdcsmartCommonServerModel->where($where)->column('type');
            $typeArray[] = $data['type'];
            if (count(array_unique($typeArray))!=1){
                return "同一个接口分组下的接口，服务器模块类型应保持一致";
            }
        }

        return true;
    }

    protected function checkGid($value,$rule,$data){
        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();
        $exist = $IdcsmartCommonServerGroupModel->where('id',$value)->where('system_type', 'normal')->find();
        if (empty($exist)){
            return "服务器组不存在";
        }

        return true;
    }

    protected function checkType($value,$rule,$data){
        $modules = (new ProvisionLogic())->getModules();
        $modulesValue = array_column($modules,'value');
        if (!in_array($value,$modulesValue)){
            return "模块不存在";
        }

        return true;
    }

}