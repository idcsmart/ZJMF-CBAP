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
