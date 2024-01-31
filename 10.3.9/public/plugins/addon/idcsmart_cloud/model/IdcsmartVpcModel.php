<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use server\idcsmart_cloud\model\DataCenterModel;

/**
 * @title VPC模型
 * @desc VPC模型
 * @use addon\idcsmart_cloud\model\IdcsmartVpcModel
 */
class IdcsmartVpcModel extends Model
{
    protected $name = 'addon_idcsmart_vpc';

    // 设置字段信息
    protected $schema = [
        'id'      		                        => 'int',
        'client_id'                             => 'int',
        'module_idcsmart_cloud_data_center_id'  => 'int',
        'name'     		                        => 'string',
        'ip'                                    => 'string',
        'create_time'                           => 'int',
        'update_time'                           => 'int',
    ];

    /**
     * 时间 2022-06-10
     * @title VPC列表
     * @desc VPC列表
     * @author theworld
     * @version v1
     * @return array list - 数据中心列表
     * @return string list[].country - 国家
     * @return string list[].country_code - 国家代码
     * @return string list[].city - 城市
     * @return string list[].area - 区域
     * @return array list[].vpc - VPC
     * @return string list[].vpc[].id - VPCID 
     * @return string list[].vpc[].name - VPC名称 
     * @return string list[].vpc[].ip - IP 
     * @return int list[].vpc[].host_num - 实例数量 
     * @return int list[].vpc[].create_time - 创建时间 
     */
    public function idcsmartVpcList($param)
    {
        $clientId = get_client_id();
        $list = $this->alias('aiv')
            ->field('aiv.id,aiv.module_idcsmart_cloud_data_center_id,aiv.name,aiv.ip,aiv.create_time,count(aivhl.host_id) host_num')
            ->leftjoin('addon_idcsmart_vpc_host_link aivhl', 'aivhl.addon_idcsmart_vpc_id=aiv.id')
            ->where(function ($query) use($clientId) {
                if(!empty($clientId)){
                    $query->where('aiv.client_id', $clientId);
                }
            })
            ->group('aiv.id')
            ->select()
            ->toArray();
        $dataCenter = DataCenterModel::field('id,country,country_code,city,area')->select()->toArray();
        $vpc = [];
        foreach ($list as $key => $value) {
            $vpc[$value['module_idcsmart_cloud_data_center_id']][] = ['id' => $value['id'], 'name' => $value['name'], 'ip' => $value['ip'], 'host_num' => $value['host_num'], 'create_time' => $value['create_time']];
        }

        foreach ($dataCenter as $key => $value) {
            $dataCenter[$key]['vpc'] = $vpc[$value['id']] ?? [];
        }

        return ['list' => $dataCenter];
    }

    /**
     * 时间 2022-06-10
     * @title 添加VPC
     * @desc 添加VPC
     * @author theworld
     * @version v1
     * @param int param.data_center_id - 云模块数据中心ID required
     * @param string param.name - 名称 required
     * @param string param.ip - IP 不是自动创建IP时需要传
     * @param int param.auto_create_ip - 是否自动创建IP,0:否1:是 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createIdcsmartVpc($param)
    {
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_is_not_exist')];
        }
        $this->startTrans();
        try {
            $clientId = get_client_id();
            if(!empty($param['auto_create_ip'])){
                $ip = get_auto_vpc_ip($param['data_center_id'], $clientId);
            }else{
                $ip = $param['ip'] ?? '';
            }
            $clientId = get_client_id();
            $idcsmartVpc = $this->create([
                'client_id' => $clientId,
                'module_idcsmart_cloud_data_center_id' => $param['data_center_id'],
                'name' => $param['name'] ?? '',
                'ip' => $ip,
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

    /**
     * 时间 2022-06-10
     * @title 修改VPC
     * @desc 修改VPC
     * @author theworld
     * @version v1
     * @param int param.id - VPC ID required
     * @param string param.name - 名称 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateIdcsmartVpc($param)
    {
        $idcsmartVpc = $this->find($param['id']);
        if(empty($idcsmartVpc)){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_is_not_exist')];
        }

        $clientId = get_client_id();
        if($idcsmartVpc['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_is_not_exist')];
        }

        $this->startTrans();
        try {
            $clientId = get_client_id();

            $this->update([
                'name' => $param['name'] ?? '',
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

    /**
     * 时间 2022-06-10
     * @title 删除VPC
     * @desc 删除VPC
     * @author theworld
     * @version v1
     * @param int id - VPC ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteIdcsmartVpc($id)
    {
        $idcsmartVpc = $this->find($id);
        if(empty($idcsmartVpc)){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_is_not_exist')];
        }

        $clientId = get_client_id();
        if($idcsmartVpc['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('vpc_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->destroy($id);
            IdcsmartVpcHostLinkModel::destroy(function($query) use($id){
                $query->where('addon_idcsmart_vpc_id', $id);
            });
            IdcsmartVpcLinkModel::destroy(function($query) use($id){
                $query->where('addon_idcsmart_vpc_id', $id);
            });
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }
}