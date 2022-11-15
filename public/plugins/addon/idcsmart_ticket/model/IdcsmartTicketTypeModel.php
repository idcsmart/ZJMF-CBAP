<?php
namespace addon\idcsmart_ticket\model;

use app\admin\model\AdminRoleModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketTypeModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_type';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'name'                             => 'string',
        'admin_role_id'                    => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    public $isAdmin = false;

    # 工单部门
    public function typeDepartment()
    {
        $departments = $this->alias('tt')
            ->field('tt.admin_role_id,ar.name')
            ->leftJoin('admin_role ar','ar.id=tt.admin_role_id')
            ->group('tt.admin_role_id')
            ->select()
            ->toArray();

        $data = [
            'list' => $departments
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 工单类型
    public function typeTicket($param)
    {
        $where = function (Query $query) use($param){
            if (isset($param['admin_role_id'])){
                $query->where('tt.admin_role_id',$param['admin_role_id']);
            }
        };
        if ($this->isAdmin){
            $ticketTypes = $this->alias('tt')
                ->field('tt.id,tt.name,ar.name as role_name,ar.description')
                ->leftJoin('admin_role ar','tt.admin_role_id=ar.id')
                ->where($where)
                ->select()
                ->toArray();
        }else{
            $ticketTypes = $this->alias('tt')
                ->field('tt.id,tt.name,ar.description')
                ->leftJoin('admin_role ar','tt.admin_role_id=ar.id')
                ->where($where)
                ->select()
                ->toArray();
        }

        $data = [
            'list' => $ticketTypes
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 工单类型详情
    public function indexTicketType($id)
    {
        $ticketType = $this->field('id,name,admin_role_id')->find($id);
        if (empty($ticketType)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_type_is_not_exist')];
        }

        $AdminRoleModel = new AdminRoleModel();
        $adminRole = $AdminRoleModel->find($ticketType->admin_role_id);
        $ticketType->role_name = $adminRole['name'];
        unset($ticketType['admin_role_id']);

        $data = [
            'ticket_type' => $ticketType
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 创建工单类型
    public function createTicketType($param)
    {
        $this->startTrans();

        try{

            $this->create([
                'name' => $param['name'],
                'admin_role_id' => $param['admin_role_id'],
                'create_time' => time()
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    # 编辑工单类型
    public function updateTicketType($param)
    {
        $this->startTrans();

        try{

            $ticketType = $this->find($param['id']);
            if (empty($ticketType)){
                throw new \Exception(lang_plugins('ticket_type_is_not_exist'));
            }

            $ticketType->save([
                'name' => $param['name'],
                'admin_role_id' => $param['admin_role_id'],
                'update_time' =>time()
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('update_success')];
    }

    # 删除工单类型
    public function deleteTicketType($id)
    {
        $this->startTrans();

        try{

            $ticketType = $this->field('id,name,admin_role_id')->find($id);
            if (empty($ticketType)){
                throw new \Exception(lang_plugins('ticket_type_is_not_exist'));
            }

            # TODO 是否可以随意删除

            $ticketType->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('delete_success')];
    }

}
