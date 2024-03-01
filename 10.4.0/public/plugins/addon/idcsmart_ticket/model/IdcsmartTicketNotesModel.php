<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketNotesModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_notes';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_id'                        => 'int',
        'content'                          => 'string',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'admin_id'                         => 'int',
    ];

    /**
     * 时间 2022-10-21
     * @title 工单备注列表
     * @desc 工单备注列表
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID
     * @return array list - 工单备注列表
     * @return int list[].id - ID
     * @return string list[].content - 工单备注
     * @return int list[].create_time - 创建时间
     * @return int list[].update_time - 更新时间
     * @return string list[].name - 管理员名称
     */
    public function ticketNotesList($param)
    {
        $ticketId = $param['ticket_id']??0;

        $list = $this->alias('tn')
            ->field('tn.id,tn.ticket_id,tn.content,tn.create_time,tn.update_time,a.name')
            ->leftJoin('admin a','a.id=tn.admin_id')
            ->where('tn.ticket_id',$ticketId)
            ->select()
            ->toArray();
        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$list]];
    }

    /**
     * 时间 2022-10-21
     * @title 工单备注详情
     * @desc 工单备注详情
     * @author wyh
     * @version v1
     * @param int id - 工单备注ID
     * @return int id - ID
     * @return string content - 工单备注
     * @return int create_time - 创建时间
     * @return int update_time - 更新时间
     * @return string name - 管理员名称
     */
    public function ticketNotesIndex($param)
    {
        $ticketNotes = $this->alias('tn')
            ->field('tn.id,tn.ticket_id,tn.content,tn.create_time,tn.update_time,a.name')
            ->leftJoin('admin a','a.id=tn.admin_id')
            ->where('tn.id',intval($param['id']))
            ->find();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'ticket_status' => $ticketNotes?:(object)[]
            ],
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 创建工单备注
     * @desc 创建工单备注
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID
     * @param string content - 工单备注
     */
    public function ticketNotesCreate($param)
    {
        $this->startTrans();

        try{
            $ticketId = $param['ticket_id']??0;
            $IdcsmartTicketModel = new IdcsmartTicketModel();
            $ticket = $IdcsmartTicketModel->find($ticketId);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $this->insert([
                'ticket_id' => $param['ticket_id']??0,
                'content' => $param['content']??'',
                'admin_id' => get_admin_id(),
                'create_time' =>time()
            ]);

            active_log(lang_plugins('ticket_log_admin_create_ticket_notes', ['{admin}'=>'admin#'.get_admin_id().'#' .request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','{content}'=>$param['content']??'']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ];

    }

    /**
     * 时间 2022-10-21
     * @title 编辑工单备注
     * @desc 编辑工单备注
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID
     * @param int id - 工单备注ID
     * @param string content - 工单备注
     */
    public function ticketNotesUpdate($param)
    {
        $this->startTrans();

        try{
            $ticketId = $param['ticket_id']??0;
            $IdcsmartTicketModel = new IdcsmartTicketModel();
            $ticket = $IdcsmartTicketModel->find($ticketId);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $ticketNotes = $this->find($param['id']);

            if (empty($ticketNotes)){
                throw new \Exception(lang_plugins('ticket_ticket_notes_is_not_exist'));
            }

            $ticketNotes->save([
                'ticket_id' => $param['ticket_id']??0,
                'content' => $param['content']??'',
                'admin_id' => get_admin_id(),
                'update_time' =>time()
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 删除工单备注
     * @desc 删除工单备注
     * @author wyh
     * @version v1
     * @param int ticket_id - 工单ID
     * @param int id - 工单备注ID
     */
    public function ticketNotesDelete($param)
    {
        $this->startTrans();

        try{
            $ticketId = $param['ticket_id']??0;
            $IdcsmartTicketModel = new IdcsmartTicketModel();
            $ticket = $IdcsmartTicketModel->find($ticketId);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $ticketNotes = $this->find($param['id']);

            if (empty($ticketNotes)){
                throw new \Exception(lang_plugins('ticket_ticket_notes_is_not_exist'));
            }

            $ticketNotes->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ];
    }

}
