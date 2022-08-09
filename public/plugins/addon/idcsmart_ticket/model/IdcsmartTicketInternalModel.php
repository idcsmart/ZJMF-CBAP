<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\admin\model\AdminRoleLinkModel;
use app\common\logic\UploadLogic;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-22
 */
class IdcsmartTicketInternalModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_internal';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_id'                        => 'int',
        'ticket_num'                       => 'string',
        'num'                              => 'int',
        'admin_role_id'                    => 'int',
        'admin_id'                         => 'int',
        'client_id'                        => 'int',
        'title'                            => 'string',
        'ticket_type_id'                   => 'int',
        'priority'                         => 'string',
        'content'                          => 'string',
        'status'                           => 'string',
        'attachment'                       => 'string',
        'last_reply_time'                  => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'post_time'                        => 'int',
    ];

    # 检查是否工单所属部门的管理人员
    private function checkAdmin($id)
    {
        # 超级管理员查看所有?目前
        if (get_admin_id() == 1){
            return true;
        }

        $ticketInternal = $this->find($id);
        if (empty($ticketInternal)){
            return false;
        }

        $AdminRoleLinkModel = new AdminRoleLinkModel();
        $allowAdmin = $AdminRoleLinkModel->where('admin_role_id',$ticketInternal->admin_role_id)->column('admin_id');
        if (!in_array(get_admin_id(),$allowAdmin)){
            return false;
        }

        return true;
    }

    # 内部工单列表
    public function ticketInternalList($param)
    {
        $where = function (Query $query) use ($param){
            if (isset($param['status']) && $param['status'] && in_array($param['status'],IdcsmartTicketLogic::getDefaultConfig('ticket_status'))){
                $query->where('t.status',$param['status']);
            }

            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('t.title|tt.name|t.content','like',"%{$param['keywords']}%");
            }

            if (get_admin_id() != 1){ # 超级管理员查看所有?目前
                $AdminRoleLinkModel = new AdminRoleLinkModel();
                $adminRoleIds = $AdminRoleLinkModel->where('admin_id',get_admin_id())->column('admin_role_id');

                $query->whereIn('t.admin_role_id',$adminRoleIds);
            }

        };
        $tickets = $this->alias('t')
            ->field('t.id,t.ticket_num,t.title,tt.name,t.post_time,c.username,GROUP_CONCAT(p.name Separator \'^#@^\') as hosts,t.last_reply_time,t.status,ticket.status as ticket_status,t.priority')
            ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
            ->leftJoin('client c','c.id=t.client_id')
            ->leftJoin('addon_idcsmart_ticket ticket','ticket.id=t.ticket_id')
            ->leftJoin('addon_idcsmart_ticket_internal_host_link thl','t.id=thl.ticket_internal_id')
            ->leftJoin('host h','h.id=thl.host_id')
            ->leftJoin('product p','h.product_id=p.id')
            ->where($where)
            ->withAttr('hosts',function ($value){
                if (!is_null($value)){
                    return explode('^#@^',$value);
                }
                return [];
            })
            ->group('t.id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('t.status','desc')
            ->order('t.last_reply_time','desc')
            ->select()
            ->toArray();
        $count = $this->alias('t')
            ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
            ->leftJoin('client c','c.id=t.client_id')
            ->leftJoin('addon_idcsmart_ticket_internal_host_link thl','t.id=thl.ticket_internal_id')
            ->leftJoin('host h','h.id=thl.host_id')
            ->leftJoin('product p','h.product_id=p.id')
            ->where($where)
            ->group('t.id')
            ->count();

        $data = [
            'list' => $tickets,
            'count' => $count
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 内部工单详情
    public function indexTicketInternal($id)
    {
        if (!$this->checkAdmin($id)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_current_admin_cannot_operate')];
        }

        $ticketInternal = $this->alias('ti')
            ->field('ti.id,ti.title,ti.content,ti.ticket_type_id,ti.status,ti.create_time,ti.attachment,a.name as admin_name,ti.last_reply_time,ti.client_id,ti.post_admin_id,ti.admin_id,ti.priority')
            ->leftJoin('admin a','a.id=ti.admin_id')
            ->where('ti.id',$id)
            ->find();
        if (empty($ticketInternal)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }
        $IdcsmartTicketLogic = new IdcsmartTicketLogic();
        $config = $IdcsmartTicketLogic->getDefaultConfig();

        $attachments = !empty($ticketInternal->attachment)?explode(',',$ticketInternal->attachment):[];
        foreach ($attachments as &$attachment){
            $attachment = $config['get_ticket_upload'] . $attachment;
        }
        $ticketInternal->attachment = $attachments;

        $IdcsmartTicketInternalHostLinkModel = new IdcsmartTicketInternalHostLinkModel();
        $ticketInternal['host_ids'] = $IdcsmartTicketInternalHostLinkModel->where('ticket_internal_id',$id)->column('host_id');

        $IdcsmartTicketInternalReplyModel = new IdcsmartTicketInternalReplyModel();
        $ticketInternalReplies = $IdcsmartTicketInternalReplyModel->alias('tr')
            ->field('tr.content,tr.attachment,tr.create_time,a.name as admin_name')
            ->leftJoin('admin a','a.id=tr.admin_id')
            ->withAttr('attachment',function ($value) use ($config){
                if (!empty($value)){
                    $attachments = explode(',',$value);
                }else{
                    $attachments = [];
                }
                if (!empty($attachments)){
                    foreach ($attachments as &$attachment){
                        $attachment = $config['get_ticket_upload'] . $attachment;
                    }
                }
                return $attachments;
            })
            ->where('tr.ticket_internal_id',$id)
            ->order('tr.create_time','desc')
            ->select()->toArray();

        array_push($ticketInternalReplies,['content'=>$ticketInternal->content,'attachment'=>$ticketInternal->attachment,'create_time'=>$ticketInternal->create_time,'admin_name'=>$ticketInternal->admin_name]);

        $ticketInternal['replies'] = $ticketInternalReplies;

        $data = [
            'ticket_internal' => $ticketInternal
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 创建内部工单(转内部工单(需要传ticket_id):调工单详情,再调此接口)
    public function createTicketInternal($param)
    {
        $this->startTrans();

        try{
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $ticketNum = $IdcsmartTicketLogic->ticketNum('NBGD');

            $ticketInternal = $this->create([
                'ticket_id' => $param['ticket_id']??0,
                'post_admin_id' => get_admin_id(),
                'ticket_num' => $ticketNum[0],
                'num' => $ticketNum[1],
                'admin_role_id' => intval($param['admin_role_id']),
                'admin_id' => $param['admin_id']??0,
                'client_id' => $param['client_id']??0,
                'title' => $param['title'],
                'priority' => $param['priority']??'medium',
                'ticket_type_id' => $param['ticket_type_id'],
                'content' => $param['content']??'',
                'status' => 'Pending',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'last_reply_time' => 0,
                'create_time' => time(),
                'post_time' => time(),
            ]);

            $IdcsmartTicketInternalHostLinkModel = new IdcsmartTicketInternalHostLinkModel();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_internal_id' => $ticketInternal->id,
                    'host_id' => $item
                ];
            }
            $IdcsmartTicketInternalHostLinkModel->insertAll($insert);

            # 移动附件
            $newNames = [];
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $ticketUpload = $IdcsmartTicketLogic->getDefaultConfig('ticket_upload');
                $UploadLogic = new UploadLogic($ticketUpload);
                if (isset($param['ticket_id']) && $param['ticket_id']){
                    $IdcsmartTicketModel = new IdcsmartTicketModel();
                    $ticket = $IdcsmartTicketModel->find($param['ticket_id']);
                    $ticketAttachments = explode(',',$ticket->attachment??"")[0]?explode(',',$ticket->attachment):[];
                    # 复制原工单附件
                    $oldAttachments = array_intersect($param['attachment'],$ticketAttachments);
                    foreach ($oldAttachments as $oldAttachment){
                        $orginName = explode('^',$oldAttachment)[1]?explode('^',$oldAttachment)[1]:'';
                        $newName = md5(uniqid()) . time() . '^' . $orginName;
                        @copy($ticketUpload . $oldAttachment,$ticketUpload . $newName);
                        $newNames[] = $newName;
                    }
                }
                # 新上传附件
                $param['attachment'] = array_diff($param['attachment'],$oldAttachments??[]);

                $newAttachments = array_merge($param['attachment'],$newNames);
                $ticketInternal->save([
                    'attachment' => implode(',',$newAttachments)
                ]);

                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            # 记录日志
            active_log(lang_plugins('ticket_log_create_ticket_internal', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>$ticketInternal->ticket_num]), 'addon_idcsmart_ticket_internal', $ticketInternal->id);

            $this->commit();

        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    # 接收内部工单
    public function receiveTicketInternal($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticketInternal = $this->where('id',$id)->find();
            if (empty($ticketInternal)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticketInternal->status != 'Pending'){
                throw new \Exception(lang_plugins('ticket_is_pending_can_handling'));
            }

            $ticketInternal->save([
                'status' => 'Handling',
                'update_time' => $time
            ]);

            # 记录日志
            active_log(lang_plugins('ticket_log_admin_receive_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>$ticketInternal->ticket_num]), 'addon_idcsmart_ticket_internal', $ticketInternal->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_handle_success')];
    }

    # 已解决内部工单
    public function resolvedTicketInternal($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticketInternal = $this->where('id',$id)->find();
            if (empty($ticketInternal)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticketInternal->status == 'Pending'){
                throw new \Exception(lang_plugins('ticket_is_pending_cannot_resolved'));
            }

            if ($ticketInternal->status == 'Resolved'){
                throw new \Exception(lang_plugins('cannot_repeat_opreate'));
            }

            $ticketInternal->save([
                'status' => 'Resolved',
                'update_time' => $time
            ]);

            # 记录日志
            active_log(lang_plugins('ticket_log_admin_resolved_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>$ticketInternal->ticket_num]), 'addon_idcsmart_ticket_internal', $ticketInternal->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_resolved_success')];
    }

    # 回复内部工单
    public function replyTicketInternal($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $currentAdminId = get_admin_id();

            $id = intval($param['id']);
            $ticketInternal = $this->find($id);
            if (empty($ticketInternal)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $AdminRoleLinkModel = new AdminRoleLinkModel();
            $allowAdmin = $AdminRoleLinkModel->where('admin_role_id',$ticketInternal->admin_role_id)->column('admin_id');

            $status = $ticketInternal->status;
            if ($ticketInternal->post_admin_id == $currentAdminId){  # 提交人回复
                if (!in_array($ticketInternal->status,['Pending','Handling'])){ # 待接收(处理中)工单提交人回复,不改变状态
                    $status = 'Reply';
                }
            }elseif(in_array($currentAdminId,$allowAdmin)){ # 其他管理员(内部工单指定部门)
                if ($ticketInternal->status != 'Pending'){ # 后台可以不接收就回复,不改变状态
                    $status = 'Replied';
                }
            }else{
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_reply'));
            }

            # 移动附件
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $UploadLogic = new UploadLogic($IdcsmartTicketLogic->getDefaultConfig('ticket_upload'));
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            $IdcsmartTicketInternalReplyModel = new IdcsmartTicketInternalReplyModel();
            $tickeInternaltReply = $IdcsmartTicketInternalReplyModel->create([
                'ticket_internal_id' => $id,
                'admin_id' => $currentAdminId,
                'content' => $param['content'],
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'create_time' => $time
            ]);

            $ticketInternal->save([
                'last_reply_time' => $time,
                'status' => $status
            ]);

            # 记录日志
            active_log(lang_plugins('ticket_log_admin_reply_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>$ticketInternal->ticket_num,'content'=>$tickeInternaltReply->content]), 'addon_idcsmart_ticket_internal', $ticketInternal->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['ticket_internal_reply_id'=>$tickeInternaltReply->id]];
    }

    # 转发内部工单
    public function forwardTicketInternal($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticketInternal = $this->where('id',$id)->find();
            if (empty($ticketInternal)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticketInternal->status == 'Pending'){
                throw new \Exception(lang_plugins('ticket_is_pending_cannot_forward'));
            }

            $ticketInternal->save([
                'admin_role_id' => $param['admin_role_id'],
                'admin_id' => $param['admin_id']??0,
                'update_time' => $time
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_forward_success')];
    }

}
