<?php 
namespace server\idcsmart_common\model;

use server\idcsmart_common\logic\ProvisionLogic;
use think\db\Query;
use think\Model;

class IdcsmartCommonServerGroupModel extends Model
{
    protected $name = 'module_idcsmart_common_server_group';

    // 设置字段信息
    protected $schema = [
        'id'                       => 'int',
        'name'                     => 'string',
        'type'                     => 'string',
        'system_type'              => 'string',
        'mode'                     => 'int',
    ];

    public function serverGroupList($param){

        $where = function (Query $query) use ($param){
            $query->where('system_type','normal');
            if (isset($param['modules'])){
                $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
                $gids = $IdcsmartCommonServerModel->where('server_type', 'normal') ->where('type', $param['modules']) ->column('gid');
                $groups = $this->where('system_type','normal') ->select()->toArray();
                $groupsFliter = array_filter($groups, function($v) use ($gids,$IdcsmartCommonServerModel) {
                    if(in_array($v['id'], $gids))
                    {
                        return true;
                    }
                    $isNull = $IdcsmartCommonServerModel->where('gid', $v['id']) ->find();
                    if(empty($isNull))
                    {
                        return true;
                    }
                    return false;
                });
                $query->whereIn('id',array_column($groupsFliter,'id'));
            }
        };

        $count = $this->where($where)->count();

        $list = $this->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->select()
            ->toArray();

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        foreach ($list as &$item){
            $serversModel = $IdcsmartCommonServerModel->where('gid', $item['id'])->select()->toArray();
            if(!empty($serversModel)) {
                $serversIds = array_column($serversModel, 'id');
                $num = array_sum(array_column($serversModel, 'max_accounts'));
            }else{
                $num = 0;
                $serversIds = [];
            }

            $IdcsmartCommonServerHostLinkModel = new IdcsmartCommonServerHostLinkModel();
            $used = $IdcsmartCommonServerHostLinkModel->whereIn('server_id',$serversIds)->count();
            $item['num'] = $num;
            $item['used'] = $used;
        }
        return [
            'status' => 200,
            'msg' => lang_plugins("success_message"),
            'data' => [
                'list' => $list,
                'count' => $count
            ]
        ];
    }

    public function createServerGroup($param){
        $this->startTrans();
        try{
            $id = $this->insertGetId([
                'name' => $param['name'],
                'mode' => $param['mode']
            ]);

            if (isset($param['server_ids']) && is_array($param['server_ids'])){

                $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

                $serversType = $IdcsmartCommonServerModel->whereIn('id', $param['server_ids'])->column('type');
                if(count(array_unique($serversType)) > 1)
                {
                    throw new \Exception(lang('同一个接口分组下的接口，服务器模块类型应保持一致！'));
                }

                $param['server_ids'] && $IdcsmartCommonServerModel->whereIn('id', $param['server_ids']) ->update(['gid' => $id]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return [
                'status' => 400,
                'msg' => $e->getMessage(),
            ];
        }
        return [
            'status' => 200,
            'msg' => lang_plugins("success_message"),
        ];
    }

    public function indexServerGroup($param){
        $serverGroup = $this->find($param['id']);
        if (empty($serverGroup)){
            return [
                'status' => 400,
                'msg' => '服务器分组不存在',
            ];
        }

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $servers = $IdcsmartCommonServerModel->field('id,name,gid,type')
            ->whereIn('gid',[0,$param['id']])
            ->select()
            ->toArray();

        $modules = (new ProvisionLogic())->getModules();

        if ($modules){
            $modules = array_column($modules,'name','value');
        }

        foreach ($servers as &$server){
            $server['name']  .= '(' . ($modules[$server['type']] ?? $server['type']) . ')';
        }

        $selectServers = array_filter($servers, function($v) {
            if($v['gid'])
            {
                return true;
            }
            return false;
        });

        return [
            'status'=>200,
            'msg' => lang_plugins("success_message"),
            'data' => [
                'server_group' => $serverGroup,
                'servers' => $servers,
                'select_servers' => $selectServers?array_column($selectServers,"id"):[]
            ]
        ];
    }

    public function updateServerGroup($param){
        $serverGroup = $this->find($param['id']);
        if (empty($serverGroup)){
            return [
                'status' => 400,
                'msg' => '服务器分组不存在',
            ];
        }
        $this->startTrans();
        try{
            $serverGroup->save([
                'name' => $param['name'],
                'mode' => $param['mode']
            ]);

            if (isset($param['server_ids']) && is_array($param['server_ids'])){

                $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

                $serversType = $IdcsmartCommonServerModel->whereIn('id', $param['server_ids'])->column('type');
                if($param['server_ids'] && count(array_unique($serversType)) != 1)
                {
                    throw new \Exception(lang('同一个接口分组下的接口，服务器模块类型应保持一致！'));
                }

                $IdcsmartCommonServerModel->where('gid', $param['id'])->update([
                    'gid' => 0
                ]);

                $param['server_ids'] && $IdcsmartCommonServerModel->whereIn('id', $param['server_ids']) ->update(['gid' => $param['id']]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return [
                'status' => 400,
                'msg' => $e->getMessage(),
            ];
        }

        return [
            'status'=>200,
            'msg' => lang_plugins("success_message"),
        ];
    }

    public function deleteServerGroup($param){
        $serverGroup = $this->find($param['id']);
        if (empty($serverGroup)){
            return [
                'status' => 400,
                'msg' => '服务器分组不存在',
            ];
        }
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $existServer = $IdcsmartCommonServerModel->where('gid',$param['id'])->find();

        if (!empty($existServer)){
            return [
                'status' => 400,
                'msg' => '此接口分组中已有接口，不能删除',
            ];
        }

        $serverGroup->delete();

        return [
            'status'=>200,
            'msg' => lang_plugins("success_message"),
        ];
    }

}