<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\admin\model\AdminRoleLinkModel;
use app\common\logic\UploadLogic;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketModel extends Model
{
    protected $name = 'addon_idcsmart_ticket';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'ticket_num'                       => 'string',
        'num'                              => 'int',
        'admin_role_id'                    => 'int',
        'client_id'                        => 'int',
        'title'                            => 'string',
        'ticket_type_id'                   => 'int',
        'content'                          => 'string',
        'status'                           => 'string',
        'attachment'                       => 'string',
        'last_reply_time'                  => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'post_time'                        => 'int',
    ];

    # 是否后台
    public $isAdmin = false;

    # 检查是否工单所属部门的管理人员
    private function checkAdmin($id)
    {
        if (!$this->isAdmin){
            return true;
        }

        # 超级管理员查看所有?目前
        if (get_admin_id() == 1){
            return true;
        }

        $ticket = $this->find($id);
        if (empty($ticket)){
            return false;
        }

        $AdminRoleLinkModel = new AdminRoleLinkModel();
        $allowAdmin = $AdminRoleLinkModel->where('admin_role_id',$ticket->admin_role_id)->column('admin_id');
        if (!in_array(get_admin_id(),$allowAdmin)){
            return false;
        }

        return true;
    }

    # 工单列表
    public function ticketList($param)
    {
        $where = function (Query $query) use ($param){

            if (!$this->isAdmin){
                $query->where('t.client_id',get_client_id());
            }else{
                if (get_admin_id() != 1){ # 超级管理员查看所有?目前
                    $AdminRoleLinkModel = new AdminRoleLinkModel();
                    $adminRoleIds = $AdminRoleLinkModel->where('admin_id',get_admin_id())->column('admin_role_id');

                    $query->whereIn('t.admin_role_id',$adminRoleIds);
                }
            }

            if (isset($param['status']) && $param['status'] && in_array($param['status'],IdcsmartTicketLogic::getDefaultConfig('ticket_status'))){
                $query->where('t.status',$param['status']);
            }

            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('t.title|tt.name|t.content','like',"%{$param['keywords']}%");
            }

        };

        if ($this->isAdmin){
            $tickets = $this->alias('t')
                ->field('t.id,t.ticket_num,t.title,tt.name,t.post_time,c.username,GROUP_CONCAT(p.name Separator \'^#@^\') as hosts,t.last_reply_time,t.status')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
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
                ->order('t.id','desc')
                ->select()
                ->toArray();
            $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();
            foreach ($tickets as &$ticket){
                $ticket['internal_status'] = '';
                $ticketInternal = $IdcsmartTicketInternalModel->where('ticket_id',$ticket['id'])->order('id','asc')->find();
                if (!empty($ticketInternal)){
                    $ticket['internal_status'] = $ticketInternal['status'];
                }
            }
            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
                ->leftJoin('host h','h.id=thl.host_id')
                ->leftJoin('product p','h.product_id=p.id')
                ->where($where)
                ->group('t.id')
                ->count();

        }else{
            $tickets = $this->alias('t')
                ->field('t.id,t.ticket_num,t.title,tt.name,t.post_time,t.last_reply_time,t.status')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->where($where)
                ->group('t.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order('t.status','desc')
                ->order('t.last_reply_time','desc')
                ->select()
                ->toArray();

            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->where($where)
                ->group('t.id')
                ->count();
        }

        $data = [
            'list' => $tickets,
            'count' => $count
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 工单统计
    public function statisticTicket()
    {
        $status = ['Pending','Handling','Reply','Replied'];

        $tickets = $this->where('client_id',get_client_id())
            ->whereIn('status',$status)
            ->column('status');

        $data = [];

        $statistics = array_count_values($tickets);
        foreach ($status as $item){
            $data[strtolower($item)] = $statistics[$item]??0;
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 工单详情
    public function indexTicket($id)
    {
        if (!$this->checkAdmin($id)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_current_admin_cannot_operate')];
        }

        $ticket = $this->alias('t')
            ->field('t.id,t.client_id,t.title,t.content,t.ticket_type_id,t.status,t.create_time,t.attachment,t.last_reply_time,c.username')
            ->leftJoin('client c','c.id=t.client_id')
            ->where('t.id',$id)
            ->find();
        if (empty($ticket)){
            return ['status'=>400,'msg'=>lang_plugins('ticket_is_not_exist')];
        }
        $IdcsmartTicketLogic = new IdcsmartTicketLogic();
        $config = $IdcsmartTicketLogic->getDefaultConfig();

        $attachments = !empty($ticket->attachment)?explode(',',$ticket->attachment):[];
        foreach ($attachments as &$attachment){
            $attachment = $config['get_ticket_upload'] . $attachment;
        }
        $ticket->attachment = $attachments;

        $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
        $ticket['host_ids'] = $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->column('host_id');

        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
        $ticketReplies = $IdcsmartTicketReplyModel->alias('tr')
            ->field('tr.content,tr.attachment,tr.create_time,tr.type,c.username as client_name,a.name as admin_name')
            ->leftJoin('client c','c.id=tr.rel_id AND tr.type=\'Client\'')
            ->leftJoin('admin a','a.id=tr.rel_id AND tr.type=\'Admin\'')
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
            ->withAttr('admin_name',function ($value){
                if (is_null($value)){
                    return '';
                }

                return $value;
            })
            ->withAttr('client_name',function ($value){
                if (is_null($value)){
                    return '';
                }

                return $value;
            })
            ->where('tr.ticket_id',$id)
            ->order('tr.create_time','desc')
            ->select()->toArray();

        array_push($ticketReplies,['content'=>$ticket->content,'attachment'=>$ticket->attachment,'create_time'=>$ticket->create_time,'type'=>'Client','client_name'=>$ticket->username,'admin_name'=>'']);

        $ticket['replies'] = $ticketReplies;
        $data = [
            'ticket' => $ticket
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    # 创建工单
    public function createTicket($param)
    {
        $this->startTrans();

        try{
            $IdcsmartTicketLogic = new IdcsmartTicketLogic();
            $ticketNum = $IdcsmartTicketLogic->ticketNum();

            $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();
            $idcsmartTicketType = $IdcsmartTicketTypeModel->find($param['ticket_type_id']);

            $ticket = $this->create([
                'ticket_num' => $ticketNum[0],
                'num' => $ticketNum[1],
                'admin_role_id' => $idcsmartTicketType->admin_role_id,
                'client_id' => get_client_id(),
                'title' => $param['title'],
                'ticket_type_id' => $param['ticket_type_id'],
                'content' => $param['content']??'',
                'status' => 'Pending',
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'last_reply_time' => 0,
                'create_time' => time(),
                'post_time' => time(),
            ]);

            $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_id' => $ticket->id,
                    'host_id' => $item
                ];
            }
            $IdcsmartTicketHostLinkModel->insertAll($insert);

            # 移动附件
            $UploadLogic = new UploadLogic($IdcsmartTicketLogic->getDefaultConfig('ticket_upload'));
            if (isset($param['attachment']) && !empty($param['attachment'])){
                $result = $UploadLogic->moveTo($param['attachment']);
                if (isset($result['error'])){
                    throw new \Exception($result['error']);
                }
            }

            # 记录日志
            active_log(lang_plugins('ticket_log_client_create_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
			//客户新增工单短信添加到任务队列
			add_task([
				'type' => 'sms',
				'description' => '客户新增工单,发送短信',
				'task_data' => [
					'name'=>'client_create_ticket',//发送动作名称
					'client_id'=>get_client_id(),//客户ID
					'template_param'=>[
						'subject' => $param['title'],//工单名称
					],
				],		
			]);
			//客户新增工单邮件添加到任务队列
			add_task([
				'type' => 'email',
				'description' => '客户新增工单,发送邮件',
				'task_data' => [
					'name'=>'client_create_ticket',//发送动作名称
					'client_id'=>get_client_id(),//客户ID
					'template_param'=>[
						'subject' => $param['title'],//工单名称
					],
				],		
			]);
            $this->commit();

        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    # 回复工单
    public function replyTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($this->isAdmin){
                $clientId = $ticket->client_id;
            }else{
                $clientId = get_client_id();
                if ($clientId != $ticket->client_id){
                    throw new \Exception(lang_plugins('ticket_is_not_exist'));
                }
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

            $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();
            $ticketReply = $IdcsmartTicketReplyModel->create([
                'ticket_id' => $id,
                'type' => $this->isAdmin?'Admin':'Client',
                'rel_id' => $this->isAdmin?get_admin_id():$clientId,
                'content' => $param['content'],
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'create_time' => $time
            ]);

            # 状态逻辑
            $status = $ticket->status;
            if ($this->isAdmin){
                $status = 'Replied';
            }else{
                if (!in_array($ticket->status,['Pending','Handling'])){ # 待接收(处理中)工单用户回复,不改变状态
                    $status = 'Reply';
                }
            }

            $ticket->save([
                'last_reply_time' => $time,
                'status' => $status
            ]);

            # 记录日志
            if ($this->isAdmin){
                active_log(lang_plugins('ticket_log_admin_reply_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','{content}'=>$ticketReply->content]), 'addon_idcsmart_ticket', $ticket->id);
				//管理员回复工单短信添加到任务队列
				add_task([
					'type' => 'sms',
					'description' => '管理员回复工单,发送短信',
					'task_data' => [
						'name'=>'admin_reply_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
				//管理员回复工单邮件添加到任务队列
				add_task([
					'type' => 'email',
					'description' => '管理员回复工单,发送邮件',
					'task_data' => [
						'name'=>'admin_reply_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
            }else{
                active_log(lang_plugins('ticket_log_client_reply_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#','{content}'=>$ticketReply->content]), 'addon_idcsmart_ticket', $ticket->id);
            }
			
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['ticket_reply_id'=>$ticketReply->id]];
    }

    # 催单
    public function urgeTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status == 'Pending'){
                $ticket->save([
                    'post_time' => $time,
                    'update_time' => $time
                ]);
            }elseif (in_array($ticket->status,['Handling','Reply','Replied'])){
                # 发送站内通知

            }else{ # 已解决或已关闭不可催单
                throw new \Exception(lang_plugins('ticket_status_is_not_allowed_urge'));
            }

            active_log(lang_plugins('ticket_log_client_urge_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_urge_success')];
    }

    # 关闭工单
    public function closeTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();
            $clientId = get_client_id();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->where('client_id',$clientId)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status == 'Closed'){
                throw new \Exception(lang_plugins('ticket_is_closed'));
            }

            $ticket->save([
                'status' => 'Closed',
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_client_close_ticket', ['{client}'=>'client#'.get_client_id() .'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
			//客户关闭工单短信添加到任务队列
				add_task([
					'type' => 'sms',
					'description' => '客户关闭工单,发送短信',
					'task_data' => [
						'name'=>'client_close_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
				//客户关闭工单邮件添加到任务队列
				add_task([
					'type' => 'email',
					'description' => '客户关闭工单,发送邮件',
					'task_data' => [
						'name'=>'client_close_ticket',//发送动作名称
						'client_id'=>$clientId,//客户ID
						'template_param'=>[
							'subject' => $ticket['title'],//工单名称
						],
					],		
				]);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_close_success')];
    }

    # 接收工单
    public function receiveTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status != 'Pending'){
                throw new \Exception(lang_plugins('ticket_is_pending_can_handling'));
            }

            $ticket->save([
                'status' => 'Handling',
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_admin_receive_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_handle_success')];
    }

    # 已解决工单
    public function resolvedTicket($param)
    {
        $this->startTrans();

        try{
            $time = time();

            $id = intval($param['id']);

            if (!$this->checkAdmin($id)){
                throw new \Exception(lang_plugins('ticket_current_admin_cannot_operate'));
            }

            $ticket = $this->where('id',$id)->find();
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            if ($ticket->status == 'Pending'){
                throw new \Exception(lang_plugins('ticket_is_pending_cannot_resolved'));
            }

            if ($ticket->status == 'Resolved'){
                throw new \Exception(lang_plugins('cannot_repeat_opreate'));
            }

            $ticket->save([
                'status' => 'Resolved',
                'update_time' => $time
            ]);

            active_log(lang_plugins('ticket_log_admin_resolved_ticket', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('ticket_resolved_success')];
    }

    # 工单附件下载
    public function download($param)
    {
        if (!isset($param['name']) || empty($param['name'])){
            return json(['status'=>400,'msg'=>lang_plugins('ticket_attachment_name_require')]);
        }
        $filename = $param['name'];

        $address = IdcsmartTicketLogic::getDefaultConfig('ticket_upload');

        $file = $address . $filename;
        if (!file_exists($file)){
            return json(['status'=>400,'msg'=>lang_plugins('ticket_attachment_is_not_exist')]);
        }

        $orginName = explode('^',$filename)[1]?explode('^',$filename)[1]:$filename;

        return download($file,$orginName);
    }

}
