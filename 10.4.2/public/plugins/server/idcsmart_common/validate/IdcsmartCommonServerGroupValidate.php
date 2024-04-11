<?php
namespace server\idcsmart_common\validate;

use server\idcsmart_common\model\IdcsmartCommonServerGroupModel;
use think\Validate;

/**
 * 自定义周期验证
 */
class IdcsmartCommonServerGroupValidate extends Validate
{
    protected $rule = [
        'name'                      => 'require|max:50|checkName:thinkphp',
        'mode'                      => 'require|in:1,2',
        'server_ids'                => 'array',
    ];

    protected $scene = [
        'create' => ['name','mode','server_ids'],
        'update' => ['name','mode','server_ids'],
    ];

    protected function checkName($value,$rule,$data){
        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();
        if (isset($data['id'])){
            $exist = $IdcsmartCommonServerGroupModel ->where('system_type','normal')->where('name', $value)->where('id','<>',$data['id'])->find();
        }else{
            $exist = $IdcsmartCommonServerGroupModel ->where('system_type','normal')->where('name', $value)->find();
        }
        if (!empty($exist)){
            return "服务器分组已存在";
        }
        return true;
    }

}