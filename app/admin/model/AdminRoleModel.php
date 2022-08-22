<?php
namespace app\admin\model;

use think\Model;
use think\db\Query;

/**
 * @title 管理员分组模型
 * @desc 管理员分组模型
 * @use app\admin\model\AdminRoleModel
 */
class AdminRoleModel extends Model
{
    protected $name = 'admin_role';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'status'          => 'int',
        'name'            => 'string',
        'description'     => 'string',
        'create_time'     => 'int',
        'update_time'     => 'int',
    ];

    /**
     * 时间 2022-5-10
     * @title 管理员分组列表
     * @desc 管理员分组列表
     * @author wyh
     * @version v1
     * @param string param.keywords - 关键字
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,name,description
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 管理员分组列表
     * @return int list[].id - ID
     * @return string list[].name - 分组名称
     * @return string list[].description - 分组说明
     * @return string list[].admins - 分组下管理员,逗号分隔
     * @return int count - 管理员分组总数
     */
    public function adminRoleList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','name','description'])){
            $param['orderby'] = 'ar.id';
        }else{
            $param['orderby'] = 'ar.'.$param['orderby'];
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('ar.id|ar.name|ar.description|a.name', 'like', "%{$param['keywords']}%");
            }
        };

        $adminRoles = $this->alias('ar')
            ->field('ar.id,ar.name,ar.description,group_concat(a.name) as admins')
            ->leftjoin('admin_role_link arl','ar.id=arl.admin_role_id')
            ->leftjoin('admin a','arl.admin_id=a.id')
            ->group('ar.id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        $count = $this->alias('ar')
            ->leftjoin('admin_role_link arl','ar.id=arl.admin_role_id')
            ->leftjoin('admin a','arl.admin_id=a.id')
            ->group('ar.id')
            ->where($where)->count();

        return ['list'=>$adminRoles,'count'=>$count];
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员分组
     * @desc 获取单个管理员分组
     * @author wyh
     * @version v1
     * @param int id - 管理员分组ID required
     * @return int id - ID
     * @return string name - 分组名称
     * @return string description - 分组描述
     * @return string admins - 分组下管理员,逗号分隔
     * @return array auth - 权限ID数组
     */
    public function indexAdminRole($id)
    {
        $adminRole = $this->alias('ar')
            ->field('ar.id,ar.name,ar.description,group_concat(a.name) as admins')
            ->leftJoin('admin_role_link arl','ar.id=arl.admin_role_id')
            ->leftjoin('admin a','arl.admin_id=a.id')
            ->where('ar.id',$id)
            ->group('ar.id')
            ->find($id);

        if(!empty($adminRole)){
            $adminRole['auth'] = AuthLinkModel::where('admin_role_id', $id)->column('auth_id');
        }

        return $adminRole?:(object)[];
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员分组
     * @desc 添加管理员分组
     * @author wyh
     * @version v1
     * @param string param.name 超级管理员 分组名称 required
     * @param string param.description 拥有所有权限 分组说明 required
     * @param array param.auth - 权限ID数组
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createAdminRole($param)
    {
        $param['auth'] = $param['auth'] ?? [];
        $auth = AuthModel::select($param['auth'])->toArray();
        if(count($auth)!=count($param['auth'])){
            return ['status' => 400, 'msg' => lang('auth_error')];
        }
        $this->startTrans();
        try{
            $adminRole = $this->create([
                'name' => $param['name']?:'',
                'description' => $param['description']?:'',
                'create_time' => time(),
                'status' => isset($param['status'])?intval($param['status']):1
            ]);

            // 新增权限
            $AuthLinkModel = new AuthLinkModel();
            $list = [];
            foreach ($param['auth'] as $key => $value) {
                $list[] = [
                    'auth_id' => intval($value),
                    'admin_role_id' => $adminRole->id
                ];
            }

            $AuthLinkModel->saveAll($list);

            # 记录日志
            active_log(lang('admin_create_admin_role',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$param['name']]),'admin',get_admin_id());

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        hook('after_admin_role_create',['id'=>$adminRole->id,'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('admin_role_create_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员分组
     * @desc 修改管理员分组
     * @author wyh
     * @version v1
     * @param string param.id 1 分组ID required
     * @param string param.name 超级管理员 分组名称 required
     * @param string param.description 拥有所有权限 分组说明 required
     * @param array param.auth - 权限ID数组
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateAdminRole($param)
    {
        $admin = $this->find(intval($param['id']));
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_role_is_not_exist')];
        }

        if ($param['id'] == 1){
            return ['status'=>400,'msg'=>lang('default_admin_role_cannot_update')];
        }

        $param['auth'] = $param['auth'] ?? [];
        $auth = AuthModel::select($param['auth'])->toArray();
        if(count($auth)!=count($param['auth'])){
            return ['status' => 400, 'msg' => lang('auth_error')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name']?:'',
                'description' => $param['description']?:'',
                'update_time' => time(),
                'status' => isset($param['status'])?intval($param['status']):1
            ],['id'=>intval($param['id'])]);

            // 删除原权限
            $AuthLinkModel = new AuthLinkModel();
            $AuthLinkModel->where('admin_role_id', intval($param['id']))->delete();

            // 新增权限
            $list = [];
            foreach ($param['auth'] as $key => $value) {
                $list[] = [
                    'auth_id' => intval($value),
                    'admin_role_id' => intval($param['id'])
                ];
            }
            
            $AuthLinkModel->saveAll($list);

            # 记录日志
            active_log(lang('admin_update_admin_role',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',get_admin_id());

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        hook('after_admin_role_edit',['id'=>$param['id'],'customfield'=>$param['customfield']??[]]);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员分组
     * @desc 删除管理员分组
     * @author wyh
     * @version v1
     * @param int id 1 管理员分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteAdminRole($param)
    {
        $id = $param['id']??0;

        if ($id == 1){
            return ['status'=>400,'msg'=>lang('super_admin_role_cannot_delete')];
        }

        $admin = $this->find($id);
        if (empty($admin)){
            return ['status'=>400,'msg'=>lang('admin_role_is_not_exist')];
        }

        $hasAdmin = AdminRoleLinkModel::where('admin_role_id','=',$id)->find();
        if (!empty($hasAdmin)){
            return ['status'=>400,'msg'=>lang('admin_role_has_admin_cannot_delete')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);
            // 删除权限
            $AuthLinkModel = new AuthLinkModel();
            $AuthLinkModel->where('admin_role_id', $id)->delete();

            # 记录日志
            active_log(lang('admin_delete_admin_role',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{name}'=>$admin['name']]),'admin',get_admin_id());

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        hook('after_admin_role_delete',['id'=>$id]);

        return ['status'=>200,'msg'=>lang('delete_success')];
    }

}