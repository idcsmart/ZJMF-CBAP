<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketPrereplyModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_prereply';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'content'                          => 'string',
    ];

    public function ticketPrereplyList()
    {
        $list = $this->select()
            ->toArray();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function ticketPrereplyIndex($param)
    {
        $ticketPrereply = $this->find($param['id']);

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'ticket_prereply' => $ticketPrereply?:(object)[]
            ]
        ];
    }

    public function ticketPrereplyCreate($param)
    {
        $this->startTrans();

        try{
            $this->insert([
                'content' => $param['content']??''
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function ticketPrereplyUpdate($param)
    {
        $this->startTrans();

        try{
            $ticketPrereply = $this->find($param['id']);

            if (empty($ticketPrereply)){
                throw new \Exception(lang_plugins('ticket_ticket_prereply_is_not_exist'));
            }
            $ticketPrereply->save([
                'content' => $param['content']??''
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function ticketPrereplyDelete($param)
    {
        $this->startTrans();

        try{
            $ticketPrereply = $this->find($param['id']);

            if (empty($ticketPrereply)){
                throw new \Exception(lang_plugins('ticket_ticket_prereply_is_not_exist'));
            }
            $ticketPrereply->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }


}
