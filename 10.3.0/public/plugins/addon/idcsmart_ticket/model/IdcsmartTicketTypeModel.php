<?php
namespace addon\idcsmart_ticket\model;

use app\admin\model\AdminRoleModel;
use think\db\Query;
use think\Model;
use app\admin\model\AdminModel;

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
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    public $isAdmin = false;

    # 工单部门
    public function typeDepartment()
    {
        $departments = $this
            ->field('id,name')
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
        if ($this->isAdmin){
            $ticketTypes = $this->alias('tt')
                ->field('tt.id,tt.name')
                // ->where($where)
                ->select()
                ->toArray();

            $admin = IdcsmartTicketTypeAdminLinkModel::alias('al')
                ->field('al.ticket_type_id,a.id,a.name')
                ->join('admin a', 'al.admin_id=a.id')
                ->select()
                ->toArray();
            $adminArr = [];
            foreach($admin as $v){
                $tid = $v['ticket_type_id'];
                unset($v['ticket_type_id']);
                $adminArr[$tid][] = $v;
            }

            foreach($ticketTypes as $k=>$v){
                $ticketTypes[$k]['admin'] = $adminArr[$v['id']] ?? [];
            }
        }else{
            $ticketTypes = $this->alias('tt')
                ->field('tt.id,tt.name')
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
        $ticketType = $this->field('id,name')->find($id);
        if (empty($ticketType)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_type_is_not_exist')];
        }

        $admin = IdcsmartTicketTypeAdminLinkModel::alias('al')
                ->field('a.id,a.name')
                ->join('admin a', 'al.admin_id=a.id')
                ->where('al.ticket_type_id', $id)
                ->select()
                ->toArray();

        $ticketType->admin = $admin;

        $data = [
            'ticket_type' => $ticketType
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 创建工单类型
    public function createTicketType($param)
    {
        $IdcsmartTicketTypeAdminLinkModel = new IdcsmartTicketTypeAdminLinkModel();
        $this->startTrans();

        try{

            $ticketType = $this->create([
                'name' => $param['name'],
                'create_time' => time()
            ]);

            $link = [];
            foreach($param['admin_id'] as $v){
                $link[] = [
                    'ticket_type_id' => $ticketType->id,
                    'admin_id'  => $v
                ];
            }
            $IdcsmartTicketTypeAdminLinkModel->insertAll($link);

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
        $IdcsmartTicketTypeAdminLinkModel = new IdcsmartTicketTypeAdminLinkModel();
        $this->startTrans();

        try{

            $ticketType = $this->find($param['id']);
            if (empty($ticketType)){
                throw new \Exception(lang_plugins('ticket_type_is_not_exist'));
            }

            $ticketType->save([
                'name' => $param['name'],
                'update_time' =>time()
            ]);

            $link = [];
            foreach($param['admin_id'] as $v){
                $link[] = [
                    'ticket_type_id' => $ticketType->id,
                    'admin_id'  => $v
                ];
            }
            IdcsmartTicketTypeAdminLinkModel::where('ticket_type_id', $param['id'])->delete();
            $IdcsmartTicketTypeAdminLinkModel->insertAll($link);

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

            $ticketType = $this->field('id,name')->find($id);
            if (empty($ticketType)){
                throw new \Exception(lang_plugins('ticket_type_is_not_exist'));
            }

            # TODO 是否可以随意删除
            $ticketType->delete();
            IdcsmartTicketTypeAdminLinkModel::where('ticket_type_id', $id)->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('delete_success')];
    }

}
