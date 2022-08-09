<?php
namespace addon\idcsmart_ssh_key\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ClientModel;
use addon\idcsmart_ssh_key\IdcsmartSshKey;

/**
 * @title 新闻模型
 * @desc 新闻模型
 * @use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel
 */
class IdcsmartSshKeyModel extends Model
{
    protected $name = 'addon_idcsmart_ssh_key';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'client_id'     => 'int',
        'name'     	    => 'string',
        'public_key'    => 'string',
        'finger_print'  => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    # SSH密钥列表
    public function idcsmartSshKeyList($param, $app = '')
    {
        $param['client_id'] = get_client_id();
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aisk.'.$param['orderby'] : 'aisk.id';

    	$count = $this->alias('aisk')
            ->field('aisk.id')
            ->where(function ($query) use($param, $app) {
                if($app=='home'){
                    $query->where('aisk.client_id', $param['client_id']);
                }
            })
            ->count();
        $list = $this->alias('aisk')
            ->field('aisk.id,aisk.name,aisk.public_key,aisk.finger_print,c.username client')
            ->leftJoin('client c', 'c.id=aisk.client_id')
            ->where(function ($query) use($param, $app) {
                if($app=='home'){
                    $query->where('aisk.client_id', $param['client_id']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($app=='home'){
                unset($list[$key]['client']);
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    # 创建SSH密钥
    public function createIdcsmartSshKey($param)
    {
        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        $count = $this->where('client_id', $clientId)->count();
        if($count>=20){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_ssh_key_create_max')];
        }

        $this->startTrans();
        try {
            $this->create([
                'client_id' => $clientId,
                'name' => $param['name'] ?? '',
                'public_key' => $param['public_key'] ?? '',
                'finger_print' => getPublicKeyFingerprint($param['public_key']),
                'create_time' => time()
            ]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 编辑SSH密钥
    public function updateIdcsmartSshKey($param)
    {
        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        // 验证SSH密钥ID
        $idcsmartSshKey = $this->find($param['id']);
        if(empty($idcsmartSshKey)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_ssh_key_is_not_exist')];
        }

        if($idcsmartSshKey['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_ssh_key_is_not_exist')];
        }


        $this->startTrans();
        try {
            $this->update([
                'name' => $param['name'] ?? '',
                'public_key' => $param['public_key'] ?? '',
                'finger_print' => getPublicKeyFingerprint($param['public_key']),
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('update_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除SSH密钥
    public function deleteIdcsmartSshKey($id)
    {
        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        // 验证SSH密钥ID
        $idcsmartSshKey = $this->find($id);
        if(empty($idcsmartSshKey)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_ssh_key_is_not_exist')];
        }

        if($idcsmartSshKey['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_ssh_key_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }
}