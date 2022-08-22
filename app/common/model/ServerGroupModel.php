<?php
namespace app\common\model;

use think\db\Query;
use think\Model;

/**
 * @title 接口分组模型
 * @desc 接口分组模型
 * @use app\admin\model\ServerGroupModel
 */
class ServerGroupModel extends Model
{
    protected $name = 'server_group';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'name'            => 'string',
        'create_time'     => 'int',
        'update_time'     => 'int',
    ];

    /**
     * 时间 2022-05-27
     * @title 接口分组列表
     * @desc 接口分组列表
     * @author hh
     * @version v1
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 接口分组列表
     * @return int list[].id - 接口分组ID
     * @return string list[].name - 分组名称
     * @return int list[].create_time - 创建时间
     * @return array list[].server - 接口列表
     * @return int list[].server[].id - 接口ID
     * @return string list[].server[].name - 接口名称
     * @return int count - 接口分组总数
     */
    public function serverGroupList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','name'])){
            $param['orderby'] = 'id';
        }

        $where = function (Query $query) use($param) {
            if(isset($param['keywords']) && trim($param['keywords']) !== ''){
                $query->where('name', 'like', "%{$param['keywords']}%");
            }
        };

        $serverGroup = $this
                    ->field('id,name,create_time')
                    ->where($where)
                    ->limit($param['limit'])
                    ->page($param['page'])
                    ->order($param['orderby'], $param['sort'])
                    ->select()
                    ->toArray();

        $count = $this->where($where)->count();

        if(!empty($serverGroup)){
            $serverGroupId = array_column($serverGroup, 'id');

            $server = ServerModel::field('id,server_group_id,name')
                    ->whereIn('server_group_id', $serverGroupId)
                    ->select()
                    ->toArray();

            $serverArr = [];
            foreach($server as $v){
                $serverArr[$v['server_group_id']][] = [
                    'id'=>$v['id'],
                    'name'=>$v['name'],
                ];
            }

            foreach($serverGroup as $k=>$v){
                $serverGroup[$k]['server'] = $serverArr[$v['id']] ?? [];
            }
        }
        return ['list'=>$serverGroup, 'count'=>$count];
    }

    /**
     * 时间 2022-05-27
     * @title 接口分组详情
     * @desc 接口分组详情
     * @author hh
     * @version v1
     * @param   int id - 接口分组ID
     * @return  int id - 接口分组ID
     * @return  string name - 分组名称
     * @return  int create_time - 创建时间
     * @return  int update_time - 修改时间
     * @return  array server - 接口列表
     * @return  int server[].id - 接口ID
     * @return  string server[].name - 接口名称
     */
    public function indexServerGroup($id)
    {
        $serverGroup = serverGroup::find($id);

        if(!empty($serverGroup)){
            $serverGroup = $serverGroup->toArray();

            $serverGroup['server'] = Server::field('id,name')
                                    ->where('server_group_id', $id)
                                    ->select()
                                    ->toArray();
        }
        return $serverGroup ?: (object)[];
    }

    /**
     * 时间 2022-05-27
     * @title 添加接口分组
     * @desc 添加接口分组
     * @author hh
     * @version v1
     * @param string param.name - 分组名称 required
     * @param array  param.server_id - 接口ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return int data.id - 接口分组ID
     */
    public function createServerGroup($param)
    {
        $server = ServerModel::whereIn('id', $param['server_id'])
                            ->where('server_group_id', 0)
                            ->select()
                            ->toArray();
        if(count($server) != count($param['server_id'])){
            return ['status'=>400, 'msg'=>lang('select_server_used_or_not_found')];
        }
        $serverModule = array_unique(array_column($server, 'module'));
        if(count($serverModule) > 1){
            return ['status'=>400, 'msg'=>lang('select_server_module_is_different')];
        }

        $this->startTrans();
        try{
            $serverGroup = $this->create([
                'name'          => $param['name'],
                'create_time'   => time(),
            ]);

            ServerModel::whereIn('id', $param['server_id'])
                ->where('server_group_id', 0)
                ->where('module', $server[0]['module'])
                ->update(['server_group_id'=>$serverGroup->id]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        hook('after_server_group_create',['id'=>$serverGroup->id,'customfield'=>$param['customfield']??[]]);

        $result = [
            'status'=>200,
            'msg'=>lang('create_success'),
            'data'=>[
                'id'=>$serverGroup->id,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-27
     * @title 修改接口分组
     * @desc 修改接口分组
     * @author hh
     * @version v1
     * @param string param.name - 分组名称 required
     * @param array  param.server_id - 接口ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateServerGroup($param)
    {
        $serverGroup = $this->find($param['id']);
        if(empty($serverGroup)){
            return ['status'=>400, 'msg'=>lang('server_group_not_found')];
        }

        $server = ServerModel::whereIn('id', $param['server_id'])
                            ->whereIn('server_group_id', [0, $serverGroup['id']])
                            ->select()
                            ->toArray();
        if(count($server) != count($param['server_id'])){
            return ['status'=>400, 'msg'=>lang('select_server_used_or_not_found')];
        }
        $serverModule = array_unique(array_column($server, 'module'));
        if(count($serverModule) > 1){
            return ['status'=>400, 'msg'=>lang('select_server_module_is_different')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name'          => $param['name'],
                'update_time'   => time(),
            ], ['id'=>$serverGroup['id']]);

            ServerModel::where('server_group_id', $serverGroup['id'])->update(['server_group_id'=>0]);

            ServerModel::whereIn('id', $param['server_id'])
                // ->whereIn('server_group_id', [0, $serverGroup['id']])
                ->where('module', $server[0]['module'])
                ->update(['server_group_id'=>$serverGroup->id]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('update_fail')];
        }

        hook('after_server_group_edit',['id'=>$serverGroup->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-05-27
     * @title 删除接口分组
     * @desc 删除接口分组
     * @author hh
     * @version v1
     * @param   int $id - 接口分组ID
     * @return  int status - 状态码,200=成功,400=失败
     */
    public function deleteServerGroup($id)
    {
        $serverGroup = $this->find($id);
        if (empty($serverGroup)){
            return ['status'=>400, 'msg'=>lang('server_group_not_found')];
        }
        $product = ProductModel::where('type', 'server_group')
                    ->where('rel_id', $id)
                    ->find();
        if(!empty($product)){
            return ['status'=>400, 'msg'=>lang('server_group_is_used_for_product_cannot_delete')];
        }
        $server = ServerModel::where('server_group_id', $id)->find();
        if(!empty($server)){
            return ['status'=>400, 'msg'=>lang('server_group_have_server_cannot_delete')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);

            ServerModel::where('server_group_id', $id)->update(['server_group_id'=>0]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('delete_fail')];
        }

        hook('after_server_group_delete',['id'=>$id]);

        return ['status'=>200, 'msg'=>lang('delete_success')];
    }





}