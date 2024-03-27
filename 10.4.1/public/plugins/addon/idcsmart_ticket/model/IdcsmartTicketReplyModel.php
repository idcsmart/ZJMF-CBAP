<?php
namespace addon\idcsmart_ticket\model;

use app\admin\model\AdminModel;
use app\common\model\ClientModel;
use think\Exception;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketReplyModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_reply';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_id'                        => 'int',
        'type'                             => 'string',
        'rel_id'                           => 'int',
        'content'                          => 'string',
        'attachment'                       => 'string',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    /**
     * 时间 2022-09-23
     * @title 修改工单回复
     * @desc 修改工单回复
     * @author wyh
     * @version v1
     * @param int id - 工单回复ID required
     * @param int content - 内容 required
     */
    public function ticketReplyUpdate($param)
    {
        $this->startTrans();

        try{
            $ticketReply = $this->where('id',$param['id'])->find();
            if (empty($ticketReply)){
                throw new \Exception(lang_plugins('ticket_reply_is_not_exist'));
            }
            $ticketReply->save([
                'content'=>$param['content']??'',
                'update_time'=>time()
            ]);

            # 记录日志
            $ticketId = $ticketReply['ticket_id'];
            if ($ticketReply['type']=='Admin'){
                $AdminModel = new AdminModel();
                $admin = $AdminModel->find($ticketReply['rel_id']);
                $name = $admin['name'];
            }else{
                $ClientModel = new ClientModel();
                $client = $ClientModel->find($ticketReply['rel_id']);
                $name = $client['username'];
            }

            active_log(lang_plugins('ticket_log_admin_update_ticket_reply', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{name}'=>$name]), 'addon_idcsmart_ticket', $ticketId);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-23
     * @title 删除工单回复
     * @desc 删除工单回复
     * @author wyh
     * @version v1
     * @param int id - 工单回复ID required
     */
    public function ticketReplyDelete($param)
    {
        $this->startTrans();

        try{
            $ticketReply = $this->where('id',$param['id'])->find();
            if (empty($ticketReply)){
                throw new \Exception(lang_plugins('ticket_reply_is_not_exist'));
            }
            $ticketReply->delete();
            # 记录日志
            $ticketId = $ticketReply['ticket_id'];
            if ($ticketReply['type']=='Admin'){
                $AdminModel = new AdminModel();
                $admin = $AdminModel->find($ticketReply['rel_id']);
                $name = $admin['name'];
            }else{
                $ClientModel = new ClientModel();
                $client = $ClientModel->find($ticketReply['rel_id']);
                $name = $client['username'];
            }
            active_log(lang_plugins('ticket_log_admin_delete_ticket_reply', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{name}'=>$name]), 'addon_idcsmart_ticket', $ticketId);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

}
