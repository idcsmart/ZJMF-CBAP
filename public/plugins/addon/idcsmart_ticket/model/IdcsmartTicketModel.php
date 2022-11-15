<?php
namespace addon\idcsmart_ticket\model;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use app\admin\model\AdminRoleLinkModel;
use app\admin\model\PluginModel;
use app\common\logic\UploadLogic;
use app\common\model\SystemLogModel;
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
        'status'                           => 'int',
        'attachment'                       => 'string',
        'last_reply_time'                  => 'int',
        'post_time'                        => 'int',
        'notes'                            => 'string',
        'admin_id'                         => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
        'last_reply_admin_id'              => 'int',
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

            if (isset($param['status']) && $param['status'] && is_array($param['status'])){
                $query->whereIn('t.status',$param['status']);
            }else{
                if ($this->isAdmin){
                    $query->whereNotIn('t.status',[3,4]);
                }
            }

            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('t.ticket_num|t.title','like',"%{$param['keywords']}%");
            }

            if (isset($param['ticket_type_id']) && !empty($param['ticket_type_id'])){
                $query->where('t.ticket_type_id',$param['ticket_type_id']);
            }

            if ($this->isAdmin){
                if (isset($param['client_id']) && !empty($param['client_id'])){
                    $query->where('t.client_id',$param['client_id']);
                }
                if (isset($param['admin_id']) && !empty($param['admin_id'])){
                    $query->where('t.last_reply_admin_id',$param['admin_id']);
                }
            }

        };

        if ($this->isAdmin){

            $PluginModel = new PluginModel();
            $plugin = $PluginModel->where('status',1)
                ->where('name','IdcsmartTicketInternal')
                ->where('module','addon')
                ->find();

            $tickets = $this->alias('t')
                ->field('t.id,t.client_id,t.ticket_num,t.title,tt.name,t.post_time,c.username,GROUP_CONCAT(p.name Separator \'^#@^\') as hosts,GROUP_CONCAT(h.id Separator \'^#@^\') as host_ids,t.last_reply_time,ts.name as status,ts.color,a.name as admin_name,c.id as client_level,tt.id as ticket_internal')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->leftJoin('admin a','a.id=t.last_reply_admin_id')
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
                ->withAttr('host_ids',function ($value){
                    if (!is_null($value)){
                        return explode('^#@^',$value);
                    }
                    return [];
                })
                ->withAttr('client_level',function ($value){
                    $hookResults = hook_one('client_level',['id'=>$value]);
                    if (!empty($hookResults)){
                        return $hookResults->background_color??"";
                    }else{
                        return '';
                    }
                })
                ->withAttr('ticket_internal',function ($value) use ($plugin){
                    if (!empty($plugin)){
                        return 1;
                    }
                    return 0;
                })
                ->group('t.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order('t.last_reply_time','desc')
                ->order('t.id','desc')
                ->select()
                ->toArray();

            $count = $this->alias('t')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
                ->leftJoin('admin a','a.id=t.last_reply_admin_id')
                ->leftJoin('client c','c.id=t.client_id')
                ->leftJoin('addon_idcsmart_ticket_host_link thl','t.id=thl.ticket_id')
                ->leftJoin('host h','h.id=thl.host_id')
                ->leftJoin('product p','h.product_id=p.id')
                ->where($where)
                ->group('t.id')
                ->count();

        }else{
            $tickets = $this->alias('t')
                ->field('t.id,t.ticket_num,t.title,tt.name,t.post_time,t.last_reply_time,ts.name as status,ts.color')
                ->leftJoin('addon_idcsmart_ticket_type tt','t.ticket_type_id=tt.id')
                ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
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
        $status = [1,2,3];

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
            ->field('t.id,t.ticket_num,t.client_id,t.title,t.content,t.ticket_type_id,ts.name as status,ts.color,t.create_time,t.attachment,t.last_reply_time,c.username')
            ->leftJoin('client c','c.id=t.client_id')
            ->leftJoin('addon_idcsmart_ticket_status ts','ts.id=t.status')
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
            ->field('tr.id,tr.content,tr.attachment,tr.create_time,tr.type,c.username as client_name,a.name as admin_name')
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

        array_push($ticketReplies,['id'=>0,'content'=>$ticket->content,'attachment'=>$ticket->attachment,'create_time'=>$ticket->create_time,'type'=>'Client','client_name'=>$ticket->username,'admin_name'=>'']);

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

            $clientId = $this->isAdmin?$param['client_id']:get_client_id();

            $ticket = $this->create([
                'ticket_num' => $ticketNum[0],
                'num' => $ticketNum[1],
                'admin_role_id' => $idcsmartTicketType->admin_role_id,
                'client_id' => $clientId,
                'title' => $param['title'],
                'ticket_type_id' => $param['ticket_type_id'],
                'content' => $param['content']??'',
                'status' => 1,
                'attachment' => (isset($param['attachment']) && !empty($param['attachment']))?implode(',',$param['attachment']):'',
                'last_reply_time' => 0,
                'create_time' => time(),
                'post_time' => time(),
                'notes' => $param['notes']??'',
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

            if (!$this->isAdmin){
                # 记录日志
                active_log(lang_plugins('ticket_log_client_create_ticket', ['{client}'=>'client#'.$clientId.'#' .request()->client_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
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
            }else{
                # 管理员创建工单日志
                active_log(lang_plugins('ticket_log_admin_create_ticket', ['{admin}'=>'client#'.get_admin_id().'#' .request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
            }

            $this->commit();

        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['id'=>$ticket->id]];
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
                $status = 3;
            }else{
                if (!in_array($ticket->status,[1,2])){ # 待接收(处理中)工单用户回复,不改变状态
                    $status = 2;
                }
            }

            $update = [
                'last_reply_time' => $time,
                'status' => $status
            ];

            if ($this->isAdmin){
                $update['last_reply_admin_id'] = get_admin_id(); # 最近一次回复管理员ID(跟进人)
            }

            $ticket->save($update);

            # 记录日志
            if ($this->isAdmin){
                active_log(lang_plugins('ticket_log_admin_reply_ticket_admin', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{ticket_id}'=>'ticket#'.$ticket->id .'#'.$ticket->ticket_num .'#']), 'addon_idcsmart_ticket', $ticket->id);
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

            if ($ticket->status == 1){
                $ticket->save([
                    'post_time' => $time,
                    'update_time' => $time
                ]);
            }elseif (in_array($ticket->status,[2,3])){
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

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('ticket_is_closed'));
            }

            $ticket->save([
                'status' => 4,
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

            if ($ticket->status != 1){
                throw new \Exception(lang_plugins('ticket_is_pending_can_handling'));
            }

            $ticket->save([
                'status' => 2,
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

            if ($ticket->status == 1){
                throw new \Exception(lang_plugins('ticket_is_pending_cannot_resolved'));
            }

            if ($ticket->status == 4){
                throw new \Exception(lang_plugins('cannot_repeat_opreate'));
            }

            $ticket->save([
                'status' => 4,
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

    # 转内部工单
    public function convert($param)
    {
        $ticket = $this->find($param['ticket_id']);

        $ticketAttachments = explode(',',$ticket->attachment??"")[0]?explode(',',$ticket->attachment):[];

        $param['attachment_old'] = $ticketAttachments;

        $IdcsmartTicketLogic = new IdcsmartTicketLogic();

        $param['ticket_upload'] = $IdcsmartTicketLogic->getDefaultConfig('ticket_upload');

        $result = plugin_api('idcsmart_ticket_internal','TicketInternal','create',$param);

        return $result;
    }

    # 内部工单类型
    public function ticketInternalType()
    {
        $result = plugin_api('idcsmart_ticket_internal','TicketInternalType','ticketTypeList',[]);

        return $result;
    }

    # 转交工单
    public function forward($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $ticket->save([
                'admin_role_id' => $param['admin_role_id'],
                'admin_id' => $param['admin_id'],
                'notes' => $param['notes']??'',
                'ticket_type_id' => $param['ticket_type_id'],
                'update_time' => time()
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    public function status($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();
            $ticketStatus = $IdcsmartTicketStatusModel->where('id',$param['status']??0)->find();
            if (empty($ticketStatus)){
                throw new \Exception(lang_plugins('ticket_status_is_not_exist'));
            }

            $ticket->save([
                'status' => $param['status'],
                'ticket_type_id' => $param['ticket_type_id']??0,
                'update_time' => time()
            ]);

            $IdcsmartTicketHostLinkModel = new IdcsmartTicketHostLinkModel();
            $IdcsmartTicketHostLinkModel->where('ticket_id',$id)->delete();
            $hostIds = $param['host_ids']?:[];
            $insert = [];
            foreach ($hostIds as $item){
                $insert[] = [
                    'ticket_id' => $ticket->id,
                    'host_id' => $item
                ];
            }
            $IdcsmartTicketHostLinkModel->insertAll($insert);

            active_log(lang_plugins('ticket_log_admin_update_ticket_status', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{status}'=>$ticketStatus['name']]), 'addon_idcsmart_ticket', $id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }


    public function ticketLog($param)
    {
        $ticketId = $param['id']??0;
        $SystemLogModel = new SystemLogModel();
        $list = $SystemLogModel->field('id,description,create_time')
            ->where('type','addon_idcsmart_ticket')
            ->where('rel_id',$ticketId)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order('id','desc')
            ->select()->toArray();
        $count = $SystemLogModel->field('id,description,create_time')
            ->where('type','addon_idcsmart_ticket')
            ->where('rel_id',$ticketId)
            ->count();
        return [
            'status' => 200,
            'msg' =>lang_plugins('success_message'),
            'data' => [
                'list' => $list,
                'count' => $count
            ]
        ];
    }

    public function updateContent($param)
    {
        $this->startTrans();

        try{
            $id = $param['id']??'';

            $ticket = $this->find($id);
            if (empty($ticket)){
                throw new \Exception(lang_plugins('ticket_is_not_exist'));
            }

            $ticket->save([
                'content' => $param['content'],
                'update_time' => time()
            ]);

            active_log(lang_plugins('ticket_log_admin_update_ticket_content', ['{admin}'=>'admin#'.request()->admin_id.'#' .request()->admin_name.'#','{content}'=>$param['content']]), 'addon_idcsmart_ticket', $id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }
}
