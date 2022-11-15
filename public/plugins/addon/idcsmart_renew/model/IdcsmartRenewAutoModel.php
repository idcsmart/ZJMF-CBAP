<?php
namespace addon\idcsmart_renew\model;

use app\common\logic\ModuleLogic;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-02
 */
class IdcsmartRenewAutoModel extends Model
{
    protected $name = 'addon_idcsmart_renew_auto';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'status'    => 'int',
    ];

    public $isAdmin = false;

    // 获取自动续费设置
    public function getStatus($id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($id);
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $clientId = get_client_id();
        if ($this->isAdmin===false && $host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $renewAuto = $this->where('host_id', $id)->find();
        $status = $renewAuto['status'] ?? 0;

        return ['status'=>200,'msg'=>lang_plugins('success_message'), 'data' => ['status' => $status]];
    }

    // 自动续费设置
    public function updateStatus($param)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($param['id']);
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $clientId = get_client_id();
        if ($this->isAdmin===false && $host->client_id != $clientId){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->where('host_id', $param['id'])->delete();
            $this->create([
                'host_id' => $param['id'],
                'status' => $param['status'],
            ]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }   
}
