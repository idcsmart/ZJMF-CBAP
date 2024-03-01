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

    /**
     * 时间 2023-6-8
     * @title 服务器分组列表
     * @desc 服务器分组列表
     * @author wyh
     * @version v1
     * @param string modules - 模块，非必传
     * @return array list - 服务器分组列表
     * @return int list[].id - 服务器分组ID
     * @return int list[].name - 服务器分组名称
     * @return int list[].num - 总数量
     * @return int list[].used - 使用
     * @return int list[].mode - 分配方式(1:平均分配;2:满一个算一个(这两个分配方式写死))
     * @return int count - 数量
     */
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

    /**
     * 时间 2023-6-8
     * @title 添加服务器分组
     * @desc 添加服务器分组
     * @author wyh
     * @version v1
     * @param string name - 服务器分组名称 required
     * @param string mode - 分配方式(1:平均分配;2:满一个算一个(这两个分配方式写死)) required
     * @param array server_ids - 选择的服务器ID,数组
     */
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
                    throw new \Exception(lang_plugins("idcsmart_common_server_same"));
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

    /**
     * 时间 2023-6-8
     * @title 服务器分组页面
     * @desc 服务器分组页面
     * @author wyh
     * @version v1
     * @param int id - 服务器分组ID required
     * @return object server_group - 服务器分组
     * @return int server_group.id - 服务器分组ID
     * @return string server_group.name - 服务器分组名称
     * @return array servers - 服务器
     * @return int servers[].id - 服务器ID
     * @return string servers[].name - 服务器名称
     * @return string servers[].type - 服务器类型
     * @return int servers[].gid - 服务器分组ID
     * @return array select_servers - 当前分组已选择的服务器ID
     */
    public function indexServerGroup($param){
        $serverGroup = $this->find($param['id']);
        if (empty($serverGroup)){
            return [
                'status' => 400,
                'msg' => lang_plugins("idcsmart_common_server_group_not_exist"),
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

    /**
     * 时间 2023-6-8
     * @title 更新服务器分组
     * @desc 更新服务器分组
     * @author wyh
     * @version v1
     * @param string name - 服务器分组名称 required
     * @param string mode - 分配方式(1:平均分配;2:满一个算一个(这两个分配方式写死)) required
     * @param array server_ids - 选择的服务器ID,数组
     */
    public function updateServerGroup($param){
        $serverGroup = $this->find($param['id']);
        if (empty($serverGroup)){
            return [
                'status' => 400,
                'msg' => lang_plugins("idcsmart_common_server_group_not_exist"),
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
                    throw new \Exception(lang_plugins("idcsmart_common_server_same"));
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

    /**
     * 时间 2023-6-8
     * @title 删除服务器分组
     * @desc 删除服务器分组
     * @author wyh
     * @version v1
     * @param int id - 服务器分组ID required
     */
    public function deleteServerGroup($param){
        $serverGroup = $this->find($param['id']);
        if (empty($serverGroup)){
            return [
                'status' => 400,
                'msg' => lang_plugins("idcsmart_common_server_group_not_exist"),
            ];
        }
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $existServer = $IdcsmartCommonServerModel->where('gid',$param['id'])->find();

        if (!empty($existServer)){
            return [
                'status' => 400,
                'msg' => lang_plugins("idcsmart_common_server_group_has_server"),
            ];
        }

        $serverGroup->delete();

        return [
            'status'=>200,
            'msg' => lang_plugins("success_message"),
        ];
    }

}